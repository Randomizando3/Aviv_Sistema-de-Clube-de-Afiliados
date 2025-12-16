<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';
require_once __DIR__ . '/../../services/Asaas.php';

class SubscriptionsController
{
  private function currentDb(PDO $pdo): string {
    return (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
  }

  private function tableExists(PDO $pdo, string $db, string $table): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$db, $table]);
    return (int)$q->fetchColumn() > 0;
  }

  private function colExists(PDO $pdo, string $db, string $table, string $col): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?");
    $q->execute([$db, $table, $col]);
    return (int)$q->fetchColumn() > 0;
  }

  private function addColumnIfMissing(PDO $pdo, string $db, string $table, string $col, string $ddl): void {
    if (!$this->colExists($pdo, $db, $table, $col)) {
      $pdo->exec($ddl);
    }
  }

  private function planCols(PDO $pdo): array {
    return $pdo->query("
      SELECT LOWER(COLUMN_NAME)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
    ")->fetchAll(PDO::FETCH_COLUMN) ?: [];
  }

  private function clampInt($v, int $min, int $max = PHP_INT_MAX): int {
    $n = (int)$v;
    if ($n < $min) $n = $min;
    if ($max !== PHP_INT_MAX && $n > $max) $n = $max;
    return $n;
  }

  private function parseDateOrDefault($v, string $defaultYmd): string {
    $s = trim((string)$v);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
    return $defaultYmd;
  }

  private function digitsOnly(string $s): string {
    return preg_replace('/\D+/', '', $s);
  }

  private function ensureSchema(PDO $pdo): void {
    $db = $this->currentDb($pdo);

    // plans (mantém compatível com seu schema atual)
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS plans (
        id varchar(32) NOT NULL,
        name varchar(80) NOT NULL,
        description text,
        monthly_price decimal(10,2) DEFAULT '0.00',
        yearly_monthly_price decimal(10,2) DEFAULT NULL,
        status enum('active','inactive') NOT NULL DEFAULT 'active',
        features json DEFAULT NULL,
        price_monthly decimal(10,2) DEFAULT '0.00',
        price_yearly decimal(10,2) DEFAULT '0.00',
        sort_order int DEFAULT '0',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        is_family tinyint(1) DEFAULT '0',
        min_users int DEFAULT '1',
        max_users int DEFAULT '0',
        add_user_monthly decimal(10,2) DEFAULT '0.00',
        add_user_yearly decimal(10,2) DEFAULT '0.00',
        PRIMARY KEY (id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // subscriptions (BIGINT)
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS subscriptions (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        plan_id VARCHAR(50) NOT NULL,
        cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
        status ENUM('ativa','suspensa','cancelada') NOT NULL DEFAULT 'suspensa',
        started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        renew_at DATE DEFAULT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        qty_users INT NOT NULL DEFAULT 1,
        asaas_customer_id VARCHAR(80) DEFAULT NULL,
        asaas_subscription_id VARCHAR(80) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_idx (user_id),
        KEY plan_idx (plan_id),
        KEY status_idx (status),
        KEY asaas_sub_idx (asaas_subscription_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Migração value -> amount se existir
    $hasValue  = $this->colExists($pdo, $db, 'subscriptions', 'value');
    $hasAmount = $this->colExists($pdo, $db, 'subscriptions', 'amount');
    if ($hasValue && !$hasAmount) {
      $pdo->exec("ALTER TABLE subscriptions CHANGE COLUMN value amount DECIMAL(10,2) NOT NULL DEFAULT 0");
    } elseif ($hasValue && $hasAmount) {
      $pdo->exec("UPDATE subscriptions SET amount = COALESCE(amount, value, 0) WHERE amount IS NULL");
      try { $pdo->exec("ALTER TABLE subscriptions DROP COLUMN value"); } catch (\Throwable $e) {}
    }

    // invoices (compatível)
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS invoices (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        subscription_id BIGINT UNSIGNED DEFAULT NULL,
        asaas_invoice_id VARCHAR(64) DEFAULT NULL,
        value DECIMAL(10,2) NOT NULL,
        status ENUM('pending','paid','overdue','canceled') NOT NULL DEFAULT 'pending',
        due_date DATE DEFAULT NULL,
        paid_at DATETIME DEFAULT NULL,
        raw JSON DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_inv_sub (subscription_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // FK invoices -> subscriptions (tenta)
    try {
      if ($this->tableExists($pdo, $db, 'subscriptions')) {
        $pdo->exec("
          ALTER TABLE invoices
            ADD CONSTRAINT fk_inv_sub
            FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)
            ON DELETE SET NULL
        ");
      }
    } catch (\Throwable $e) {}

    // webhooks_log
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS webhooks_log (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        provider VARCHAR(40) NOT NULL,
        event VARCHAR(80) NOT NULL,
        signature TEXT NULL,
        payload JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    /**
     * subscription_people (EXATAMENTE como seu schema atual)
     * Obs: se já existe, não altera.
     */
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS subscription_people (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        subscription_id bigint unsigned NOT NULL,
        role enum('holder','dependent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dependent',
        full_name varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
        doc_type enum('CPF','CNPJ','RG','CN','MATRICULA') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CPF',
        doc_value varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
        created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_sub (subscription_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
  }

  private function calcPlanAmount(PDO $pdo, string $planId, string $cycleUi, int &$qtyUsersOut): float {
    $cols = $this->planCols($pdo);

    $colPm = in_array('price_monthly', $cols) ? 'price_monthly' : (in_array('monthly_price', $cols) ? 'monthly_price' : null);
    $colPy = in_array('price_yearly',  $cols) ? 'price_yearly'  : (in_array('yearly_price',  $cols) ? 'yearly_price'  : null);

    $colIsFam = in_array('is_family', $cols) ? 'is_family' : null;
    $colMinU  = in_array('min_users', $cols) ? 'min_users' : null;
    $colMaxU  = in_array('max_users', $cols) ? 'max_users' : null;
    $colAddM  = in_array('add_user_monthly', $cols) ? 'add_user_monthly' : null;
    $colAddY  = in_array('add_user_yearly',  $cols) ? 'add_user_yearly'  : null;

    $select = "id, status";
    $select .= $colPm ? ", $colPm AS pm" : ", NULL AS pm";
    $select .= $colPy ? ", $colPy AS py" : ", NULL AS py";

    $select .= $colIsFam ? ", $colIsFam AS is_family" : ", 0 AS is_family";
    $select .= $colMinU  ? ", $colMinU  AS min_users" : ", 1 AS min_users";
    $select .= $colMaxU  ? ", $colMaxU  AS max_users" : ", 0 AS max_users";
    $select .= $colAddM  ? ", $colAddM  AS add_m"     : ", 0 AS add_m";
    $select .= $colAddY  ? ", $colAddY  AS add_y"     : ", 0 AS add_y";

    $st = $pdo->prepare("SELECT $select FROM plans WHERE id=? LIMIT 1");
    $st->execute([$planId]);
    $p = $st->fetch(PDO::FETCH_ASSOC);

    if (!$p) \Json::fail('invalid_plan_id', 422);
    if (($p['status'] ?? 'inactive') !== 'active') \Json::fail('invalid_plan_id', 422);

    $pm = isset($p['pm']) ? (float)$p['pm'] : 0.0;
    $py = isset($p['py']) ? (float)$p['py'] : null;
    if ($py === null) $py = $pm * 12 * 0.85;

    $isFam = ((int)($p['is_family'] ?? 0) === 1);
    $minU  = max(1, (int)($p['min_users'] ?? 1));
    $maxU  = max(0, (int)($p['max_users'] ?? 0));
    $addM  = max(0.0, (float)($p['add_m'] ?? 0));
    $addY  = max(0.0, (float)($p['add_y'] ?? 0));

    if (!$isFam) {
      $qtyUsersOut = 1;
      return $cycleUi === 'yearly' ? (float)$py : (float)$pm;
    }

    if ($minU < 2) $minU = 2;
    if ($maxU > 0 && $maxU < $minU) $maxU = $minU;

    $qtyUsersOut = $this->clampInt($qtyUsersOut, $minU, $maxU > 0 ? $maxU : PHP_INT_MAX);

    $base = $cycleUi === 'yearly' ? (float)$py : (float)$pm;
    $add  = $cycleUi === 'yearly' ? $addY : $addM;

    $extra = max(0, $qtyUsersOut - $minU);
    return (float)($base + ($extra * $add));
  }

  /**
   * Salva titular + dependentes em subscription_people (SEMPRE no schema atual)
   * - Titular: role=holder, full_name, doc_type (CPF/CNPJ), doc_value
   * - Dependentes: role=dependent, full_name, doc_type (CPF/RG/CN/MATRICULA), doc_value
   */
  private function savePeople(PDO $pdo, int $subscriptionId, string $holderName, string $holderDocDigits, string $dependentsJson): void {
    if ($subscriptionId <= 0) return;

    $db = $this->currentDb($pdo);
    if (!$this->tableExists($pdo, $db, 'subscription_people')) return;

    // 1) Garante titular
    $holderName = trim($holderName);
    if ($holderName === '') $holderName = 'Titular';

    $docDigits = $this->digitsOnly($holderDocDigits);
    $holderType = (strlen($docDigits) === 14) ? 'CNPJ' : 'CPF';
    if (!in_array(strlen($docDigits), [11,14], true)) {
      // se por algum motivo não vier válido, guarda como CPF mesmo (mas não quebra o fluxo)
      $docDigits = substr($docDigits, 0, 64);
      $holderType = 'CPF';
    }

    $ck = $pdo->prepare("SELECT COUNT(*) FROM subscription_people WHERE subscription_id=? AND role='holder'");
    $ck->execute([$subscriptionId]);
    if ((int)$ck->fetchColumn() === 0) {
      $insH = $pdo->prepare("
        INSERT INTO subscription_people (subscription_id, role, full_name, doc_type, doc_value)
        VALUES (?, 'holder', ?, ?, ?)
      ");
      $insH->execute([$subscriptionId, $holderName, $holderType, $docDigits]);
    }

    // 2) Dependentes: limpa e recria para evitar duplicação em re-tentativas
    $pdo->prepare("DELETE FROM subscription_people WHERE subscription_id=? AND role='dependent'")
        ->execute([$subscriptionId]);

    $dependentsJson = trim((string)$dependentsJson);
    if ($dependentsJson === '') return;

    $arr = json_decode($dependentsJson, true);
    if (!is_array($arr) || empty($arr)) return;

    $allowedTypes = ['CPF','CNPJ','RG','CN','MATRICULA'];

    $insD = $pdo->prepare("
      INSERT INTO subscription_people (subscription_id, role, full_name, doc_type, doc_value)
      VALUES (?, 'dependent', ?, ?, ?)
    ");

    foreach ($arr as $d) {
      if (!is_array($d)) continue;

      // seu front manda: {name, doc_type, doc_value}
      $fullName = trim((string)($d['full_name'] ?? $d['name'] ?? ''));
      $docType  = strtoupper(trim((string)($d['doc_type'] ?? 'CPF')));
      $docValue = trim((string)($d['doc_value'] ?? ''));

      if ($fullName === '' || mb_strlen($fullName) < 3) continue;
      if (!in_array($docType, $allowedTypes, true)) continue;

      // Normaliza CPF/CNPJ
      if ($docType === 'CPF' || $docType === 'CNPJ') {
        $digits = $this->digitsOnly($docValue);
        if ($docType === 'CPF' && strlen($digits) !== 11) continue;
        if ($docType === 'CNPJ' && strlen($digits) !== 14) continue;
        $docValue = $digits;
      } else {
        // RG/CN/MATRICULA: aceita texto, mínimo 3 chars
        if (mb_strlen($docValue) < 3) continue;
        if (mb_strlen($docValue) > 64) $docValue = mb_substr($docValue, 0, 64);
      }

      $insD->execute([$subscriptionId, $fullName, $docType, $docValue]);
    }
  }

  // POST /?r=api/subscriptions/create
  public function create(): void {
    \Auth::requireRole(['member','admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);

      $u = \Auth::user();
      $userId = (int)($u['id'] ?? 0);
      if ($userId <= 0) \Json::fail('unauthorized', 401);

      $planId  = trim($_POST['plan_id'] ?? '');
      if ($planId === '') \Json::fail('invalid_payload', 422);

      $cycleUi = in_array(($_POST['cycle'] ?? 'monthly'), ['monthly','yearly'], true) ? $_POST['cycle'] : 'monthly';

      // FORÇA BOLETO
      $billingType = 'BOLETO';

      $qtyUsers = (int)($_POST['qty_users'] ?? 1);
      if ($qtyUsers < 1) $qtyUsers = 1;

      $dependentsJson = (string)($_POST['dependents_json'] ?? '');

      $nextDueDate = $this->parseDateOrDefault($_POST['nextDueDate'] ?? null, date('Y-m-d', strtotime('+1 day')));

      $ps = $pdo->prepare("SELECT name, email, document, phone FROM users WHERE id=? LIMIT 1");
      $ps->execute([$userId]);
      $prof = $ps->fetch(PDO::FETCH_ASSOC) ?: [];

      $cpfCnpjPost = $this->digitsOnly((string)($_POST['cpfCnpj'] ?? ''));
      $cpfCnpjDb   = $this->digitsOnly((string)($prof['document'] ?? ''));
      $cpfCnpj     = $cpfCnpjPost ?: $cpfCnpjDb;

      if (!in_array(strlen($cpfCnpj), [11,14], true)) {
        \Json::fail('É necessário informar um CPF/CNPJ válido (perfil ou formulário).', 422);
      }

      $mobilePost  = $this->digitsOnly((string)($_POST['mobilePhone'] ?? ''));
      $mobileDb    = $this->digitsOnly((string)($prof['phone'] ?? ''));
      $mobile      = $mobilePost ?: $mobileDb;
      $mobileValid = (strlen($mobile) >= 10 && strlen($mobile) <= 11);

      $email = trim((string)($u['email'] ?? $prof['email'] ?? ''));
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) \Json::fail('E-mail do usuário inválido.', 422);

      $name  = trim((string)($_POST['name'] ?? $u['name'] ?? $prof['name'] ?? 'Cliente'));

      $amount = $this->calcPlanAmount($pdo, $planId, $cycleUi, $qtyUsers);

      $customerPayload = ['name'=>$name,'email'=>$email,'cpfCnpj'=>$cpfCnpj];
      if ($mobileValid) $customerPayload['mobilePhone'] = $mobile;

      $asaas = new \App\Services\Asaas();
      $cust = $asaas->findCustomerByEmail($email);
      if (!$cust) {
        $cust = $asaas->createCustomer($customerPayload);
      } else {
        if (empty($cust['cpfCnpj'])) {
          $upd = ['cpfCnpj'=>$cpfCnpj];
          if ($mobileValid && empty($cust['mobilePhone'])) $upd['mobilePhone'] = $mobile;
          if (!empty($name) && empty($cust['name'])) $upd['name'] = $name;
          $cust = $asaas->updateCustomer($cust['id'], $upd);
        }
      }

      $cycleAsaas = $cycleUi === 'yearly' ? 'YEARLY' : 'MONTHLY';

      $payload = [
        'customer'    => $cust['id'],
        'billingType' => $billingType,
        'value'       => $amount,
        'cycle'       => $cycleAsaas,
        'nextDueDate' => $nextDueDate,
      ];

      $sub = $asaas->createSubscription($payload);

      $statusStart = 'suspensa';

      $ins = $pdo->prepare("
        INSERT INTO subscriptions
          (user_id, plan_id, cycle, status, started_at, renew_at, amount, qty_users, asaas_customer_id, asaas_subscription_id, created_at)
        VALUES
          (?,       ?,      ?,     ?,      NOW(),     ?,        ?,      ?,        ?,                 ?,                    NOW())
      ");
      $ins->execute([
        $userId,
        $planId,
        $cycleUi,
        $statusStart,
        $nextDueDate,
        $amount,
        $qtyUsers,
        $cust['id'],
        $sub['id']
      ]);

      $localSubId = (int)$pdo->lastInsertId();

      // >>> AQUI É O PONTO QUE ESTAVA FALTANDO: grava titular + dependentes <<<
      try {
        $this->savePeople($pdo, $localSubId, $name, $cpfCnpj, $dependentsJson);
      } catch (\Throwable $e) {}

      // primeira fatura pendente
      $pays = $asaas->getPayments(['subscription' => $sub['id'], 'limit' => 1]);
      $payment = ($pays['totalCount'] ?? 0) ? ($pays['data'][0] ?? null) : null;

      if ($payment) {
        $mapStatus = 'pending';
        if (in_array(strtoupper((string)($payment['status'] ?? '')), ['RECEIVED','CONFIRMED'], true)) $mapStatus = 'paid';

        $pdo->prepare("
          INSERT INTO invoices (subscription_id, asaas_invoice_id, value, status, due_date, raw)
          VALUES (?, ?, ?, ?, ?, ?)
        ")->execute([
          $localSubId > 0 ? $localSubId : null,
          $payment['id'] ?? null,
          $payment['value'] ?? $amount,
          $mapStatus,
          $payment['dueDate'] ?? $nextDueDate,
          json_encode($payment, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
      }

      \Json::ok([
        'ok' => true,
        'subscription' => ['id' => $sub['id'], 'local_id' => $localSubId],
        'qty_users' => $qtyUsers,
        'amount' => $amount,
        'payment' => [
          'id'          => $payment['id']          ?? null,
          'invoiceUrl'  => $payment['invoiceUrl']  ?? null,
          'bankSlipUrl' => $payment['bankSlipUrl'] ?? null,
          'pix' => [
            'qrCode'  => $payment['pixQrCode']     ?? null,
            'payload' => $payment['pixQrCodeText'] ?? null,
            'expires' => $payment['pixExpirationDate'] ?? null,
          ],
        ],
      ]);
    } catch (\Throwable $e) {
      $msg = $e->getMessage();

      if (strpos($msg, 'Asaas HTTP 400') !== false) {
        if (preg_match('/\{.*\}$/', $msg, $m)) {
          $j = json_decode($m[0], true);
          $desc = $j['errors'][0]['description'] ?? 'asaas_bad_request';
          \Json::fail($desc, 422);
        }
        \Json::fail('asaas_bad_request', 422);
      }

      @file_put_contents(BASE_PATH . '/storage/logs/subscriptions_member_err.log', date('c')." [create] ".$msg."\n", FILE_APPEND);
      \Json::fail('internal_error', 500);
    }
  }
}
