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

  private function ensureSchema(PDO $pdo): void {
    $db = $this->currentDb($pdo);

    /**
     * Garante que a tabela plans exista (para validar e calcular preços).
     * Se já existir (com mais colunas), não altera nada aqui.
     */
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS plans (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        description TEXT NULL,
        price_monthly DECIMAL(10,2) DEFAULT 0,
        price_yearly  DECIMAL(10,2) DEFAULT 0,
        status ENUM('active','inactive') DEFAULT 'active',
        sort_order INT DEFAULT 0,

        -- Familiar
        is_family TINYINT(1) DEFAULT 0,
        min_users INT DEFAULT 1,
        max_users INT DEFAULT 0,
        add_user_monthly DECIMAL(10,2) DEFAULT 0,
        add_user_yearly  DECIMAL(10,2) DEFAULT 0,

        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    /**
     * Subscriptions + ajustes legados
     */
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_id VARCHAR(50) NOT NULL,
        cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
        status ENUM('ativa','suspensa','cancelada') NOT NULL DEFAULT 'suspensa',
        started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        renew_at DATE DEFAULT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0,

        -- quantidade para plano familiar
        qty_users INT NOT NULL DEFAULT 1,

        asaas_customer_id VARCHAR(80) DEFAULT NULL,
        asaas_subscription_id VARCHAR(80) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY user_idx (user_id),
        KEY plan_idx (plan_id),
        KEY status_idx (status)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Migração suave: value -> amount
    $hasValue  = $this->colExists($pdo, $db, 'subscriptions', 'value');
    $hasAmount = $this->colExists($pdo, $db, 'subscriptions', 'amount');
    if ($hasValue && !$hasAmount) {
      $pdo->exec("ALTER TABLE subscriptions CHANGE COLUMN value amount DECIMAL(10,2) NOT NULL DEFAULT 0");
    } elseif ($hasValue && $hasAmount) {
      $pdo->exec("UPDATE subscriptions SET amount = COALESCE(amount, value, 0) WHERE amount IS NULL");
      try { $pdo->exec("ALTER TABLE subscriptions DROP COLUMN value"); } catch (\Throwable $e) {}
    }

    // Corrige enum antigo de status
    $colType = $pdo->query("
      SELECT COLUMN_TYPE FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='subscriptions' AND COLUMN_NAME='status'
    ")->fetchColumn();

    if (is_string($colType) && stripos($colType, "enum('active','suspended','canceled')") !== false) {
      $pdo->exec("ALTER TABLE subscriptions CHANGE COLUMN status status VARCHAR(20) NOT NULL");
      $pdo->exec("UPDATE subscriptions SET status=LOWER(TRIM(status))");
      $pdo->exec("UPDATE subscriptions SET status='ativa'     WHERE status IN ('active','ativa','')");
      $pdo->exec("UPDATE subscriptions SET status='suspensa'  WHERE status IN ('suspended','suspensa')");
      $pdo->exec("UPDATE subscriptions SET status='cancelada' WHERE status IN ('canceled','cancelada')");
      $pdo->exec("ALTER TABLE subscriptions CHANGE COLUMN status status ENUM('ativa','suspensa','cancelada') NOT NULL DEFAULT 'suspensa'");
    }

    // Garante cycle enum correto
    $pdo->exec("ALTER TABLE subscriptions CHANGE COLUMN cycle cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly'");

    // Garante colunas Asaas
    if (!$this->colExists($pdo, $db, 'subscriptions', 'asaas_customer_id')) {
      $pdo->exec("ALTER TABLE subscriptions ADD COLUMN asaas_customer_id VARCHAR(80) NULL AFTER qty_users");
    }
    if (!$this->colExists($pdo, $db, 'subscriptions', 'asaas_subscription_id')) {
      $pdo->exec("ALTER TABLE subscriptions ADD COLUMN asaas_subscription_id VARCHAR(80) NULL AFTER asaas_customer_id");
    }

    // Garante qty_users (caso tabela já exista antiga)
    $this->addColumnIfMissing(
      $pdo,
      $db,
      'subscriptions',
      'qty_users',
      "ALTER TABLE subscriptions ADD COLUMN qty_users INT NOT NULL DEFAULT 1 AFTER amount"
    );

    /**
     * invoices e webhooks_log
     */
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS invoices (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        subscription_id INT NULL,
        asaas_invoice_id VARCHAR(80) UNIQUE,
        value DECIMAL(10,2) NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        due_date DATE NULL,
        paid_at DATETIME NULL,
        raw JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY sub_idx (subscription_id),
        KEY status_idx (status)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
      CREATE TABLE IF NOT EXISTS webhooks_log (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        provider VARCHAR(40) NOT NULL,
        event VARCHAR(80) NOT NULL,
        signature TEXT NULL,
        payload JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
  }

  /**
   * Calcula o valor final do plano:
   * - Individual: base do ciclo
   * - Familiar: base (para min_users) + (qty_users - min_users) * adicional (do ciclo)
   */
  private function calcPlanAmount(PDO $pdo, string $planId, string $cycleUi, int &$qtyUsersOut): float {
    $cols = $this->planCols($pdo);

    // Colunas tolerantes (caso existam nomes legados)
    $colPm = in_array('price_monthly', $cols) ? 'price_monthly' : (in_array('monthly_price', $cols) ? 'monthly_price' : null);
    $colPy = in_array('price_yearly',  $cols) ? 'price_yearly'  : (in_array('yearly_price',  $cols) ? 'yearly_price'  : null);

    $colIsFam = in_array('is_family', $cols) ? 'is_family' : null;
    $colMinU  = in_array('min_users', $cols) ? 'min_users' : null;
    $colMaxU  = in_array('max_users', $cols) ? 'max_users' : null;
    $colAddM  = in_array('add_user_monthly', $cols) ? 'add_user_monthly' : null;
    $colAddY  = in_array('add_user_yearly',  $cols) ? 'add_user_yearly'  : null;

    // Monta SELECT
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

    // fallback anual: 12x com desconto 15%
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

    // Familiar: mínimo recomendado >= 2
    if ($minU < 2) $minU = 2;
    if ($maxU > 0 && $maxU < $minU) $maxU = $minU;

    // clamp qty
    $qtyUsersOut = $this->clampInt($qtyUsersOut, $minU, $maxU > 0 ? $maxU : PHP_INT_MAX);

    $base = $cycleUi === 'yearly' ? (float)$py : (float)$pm;
    $add  = $cycleUi === 'yearly' ? $addY : $addM;

    $extra = max(0, $qtyUsersOut - $minU);
    $amount = $base + ($extra * $add);

    return (float)$amount;
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

      // >>> FORÇA SOMENTE BOLETO (checkout de cartão removido)
      $billingType = 'BOLETO';

      // quantidade (para familiar)
      $qtyUsers = (int)($_POST['qty_users'] ?? 1);
      if ($qtyUsers < 1) $qtyUsers = 1;

      // nextDueDate (mantém seu padrão: amanhã)
      $nextDueDate = $this->parseDateOrDefault($_POST['nextDueDate'] ?? null, date('Y-m-d', strtotime('+1 day')));

      // perfil (fallbacks)
      $ps = $pdo->prepare("SELECT name, email, document, phone FROM users WHERE id=? LIMIT 1");
      $ps->execute([$userId]);
      $prof = $ps->fetch(PDO::FETCH_ASSOC) ?: [];

      $cpfCnpjPost = preg_replace('/\D+/', '', $_POST['cpfCnpj'] ?? '');
      $cpfCnpjDb   = preg_replace('/\D+/', '', $prof['document'] ?? '');
      $cpfCnpj     = $cpfCnpjPost ?: $cpfCnpjDb;
      if (!in_array(strlen($cpfCnpj), [11,14], true)) {
        \Json::fail('É necessário informar um CPF/CNPJ válido (perfil ou formulário).', 422);
      }

      $mobilePost  = preg_replace('/\D+/', '', $_POST['mobilePhone'] ?? '');
      $mobileDb    = preg_replace('/\D+/', '', $prof['phone'] ?? '');
      $mobile      = $mobilePost ?: $mobileDb;
      $mobileValid = (strlen($mobile) >= 10 && strlen($mobile) <= 11);

      // Email e nome
      $email = trim((string)($u['email'] ?? $prof['email'] ?? ''));
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) \Json::fail('E-mail do usuário inválido.', 422);

      $name  = trim((string)($_POST['name'] ?? $u['name'] ?? $prof['name'] ?? 'Cliente'));

      // >>> Calcula amount no servidor (inclui familiar + qty_users)
      $amount = $this->calcPlanAmount($pdo, $planId, $cycleUi, $qtyUsers);

      // ==== Asaas
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

      // Asaas subscription cycle
      $cycleAsaas = $cycleUi === 'yearly' ? 'YEARLY' : 'MONTHLY';

      $payload = [
        'customer'    => $cust['id'],
        'billingType' => $billingType, // BOLETO
        'value'       => $amount,
        'cycle'       => $cycleAsaas,
        'nextDueDate' => $nextDueDate,
      ];

      $sub = $asaas->createSubscription($payload);

      // >>> assinatura local nasce como 'suspensa' (aguardando pagamento)
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

      // primeira fatura pendente
      $pays = $asaas->getPayments(['subscription' => $sub['id'], 'limit' => 1]);
      $payment = ($pays['totalCount'] ?? 0) ? ($pays['data'][0] ?? null) : null;

      if ($payment) {
        $pdo->prepare("
          INSERT INTO invoices (subscription_id, asaas_invoice_id, value, status, due_date, raw)
          VALUES (
            (SELECT id FROM subscriptions WHERE asaas_subscription_id=? LIMIT 1),
            ?, ?, ?, ?, ?
          )
        ")->execute([
          $sub['id'],
          $payment['id'],
          $payment['value'] ?? $amount,
          strtolower($payment['status'] ?? 'PENDING') === 'pending' ? 'pending'
            : (in_array(strtoupper($payment['status'] ?? ''), ['RECEIVED','CONFIRMED']) ? 'paid' : 'pending'),
          $payment['dueDate'] ?? $nextDueDate,
          json_encode($payment, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ]);
      }

      \Json::ok([
        'ok' => true,
        'subscription' => ['id' => $sub['id']],
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
