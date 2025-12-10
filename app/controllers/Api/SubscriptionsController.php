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

  private function ensureSchema(PDO $pdo): void {
    $db = $this->currentDb($pdo);

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
        asaas_customer_id VARCHAR(80) DEFAULT NULL,
        asaas_subscription_id VARCHAR(80) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY user_idx (user_id),
        KEY plan_idx (plan_id),
        KEY status_idx (status)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $hasValue  = $this->colExists($pdo, $db, 'subscriptions', 'value');
    $hasAmount = $this->colExists($pdo, $db, 'subscriptions', 'amount');
    if ($hasValue && !$hasAmount) {
      $pdo->exec("ALTER TABLE subscriptions CHANGE COLUMN value amount DECIMAL(10,2) NOT NULL DEFAULT 0");
    } elseif ($hasValue && $hasAmount) {
      $pdo->exec("UPDATE subscriptions SET amount = COALESCE(amount, value, 0) WHERE amount IS NULL");
      try { $pdo->exec("ALTER TABLE subscriptions DROP COLUMN value"); } catch (\Throwable $e) {}
    }

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
    $pdo->exec("ALTER TABLE subscriptions CHANGE COLUMN cycle cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly'");

    if (!$this->colExists($pdo, $db, 'subscriptions', 'asaas_customer_id')) {
      $pdo->exec("ALTER TABLE subscriptions ADD COLUMN asaas_customer_id VARCHAR(80) NULL AFTER amount");
    }
    if (!$this->colExists($pdo, $db, 'subscriptions', 'asaas_subscription_id')) {
      $pdo->exec("ALTER TABLE subscriptions ADD COLUMN asaas_subscription_id VARCHAR(80) NULL AFTER asaas_customer_id");
    }

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

  // POST /?r=api/subscriptions/create
  public function create(): void {
    \Auth::requireRole(['member','admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);

      $u = \Auth::user();
      $userId = (int)($u['id'] ?? 0);
      if ($userId <= 0) \Json::fail('unauthorized', 401);

      $planId      = trim($_POST['plan_id'] ?? '');
      $cycleUi     = in_array(($_POST['cycle'] ?? 'monthly'), ['monthly','yearly'], true) ? $_POST['cycle'] : 'monthly';
      $billingType = strtoupper($_POST['billingType'] ?? 'BOLETO'); // PIX não é permitido em subscriptions
      $nextDueDate = $_POST['nextDueDate'] ?? date('Y-m-d', strtotime('+1 day'));
      if ($planId === '') \Json::fail('invalid_payload', 422);

      // valida plano
      $chk = $pdo->prepare("SELECT 1 FROM plans WHERE id=? AND status='active' LIMIT 1");
      $chk->execute([$planId]);
      if (!$chk->fetch()) \Json::fail('invalid_plan_id', 422);

      // perfil (fallbacks)
      $ps = $pdo->prepare("SELECT name, email, document, phone FROM users WHERE id=? LIMIT 1");
      $ps->execute([$userId]);
      $prof = $ps->fetch(PDO::FETCH_ASSOC) ?: [];

      $cpfCnpjPost = preg_replace('/\D+/', '', $_POST['cpfCnpj']     ?? '');
      $cpfCnpjDb   = preg_replace('/\D+/', '', $prof['document']     ?? '');
      $cpfCnpj     = $cpfCnpjPost ?: $cpfCnpjDb;
      if (!in_array(strlen($cpfCnpj), [11,14], true)) \Json::fail('É necessário informar um CPF/CNPJ válido (perfil ou formulário).', 422);

      $mobilePost  = preg_replace('/\D+/', '', $_POST['mobilePhone'] ?? '');
      $mobileDb    = preg_replace('/\D+/', '', $prof['phone']        ?? '');
      $mobile      = $mobilePost ?: $mobileDb;
      $mobileValid = (strlen($mobile) >= 10 && strlen($mobile) <= 11);

      if (!in_array($billingType, ['BOLETO','CREDIT_CARD'], true)) $billingType = 'BOLETO';

      // usa preço do plano, se necessário
      $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : null;
      $cols = $pdo->query("
        SELECT LOWER(COLUMN_NAME)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
      ")->fetchAll(PDO::FETCH_COLUMN);
      if ($amount === null) {
        $colM = in_array('price_monthly', $cols) ? 'price_monthly' : (in_array('monthly_price', $cols) ? 'monthly_price' : null);
        $colY = in_array('price_yearly',  $cols) ? 'price_yearly'  : (in_array('yearly_price',  $cols) ? 'yearly_price'  : null);
        if ($colM || $colY) {
          $st = $pdo->prepare("SELECT ".($colM ?: 'NULL')." AS pm, ".($colY ?: 'NULL')." AS py FROM plans WHERE id=? LIMIT 1");
          $st->execute([$planId]);
          if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $pm = isset($row['pm']) ? (float)$row['pm'] : null;
            $py = isset($row['py']) ? (float)$row['py'] : null;
            if ($py === null && $pm !== null) $py = $pm * 12 * 0.85;
            $amount = $cycleUi === 'yearly' ? ($py ?? 0.0) : ($pm ?? 0.0);
          }
        }
      }
      if ($amount === null) $amount = 0.0;

      // ==== Asaas
      $email = trim((string)($u['email'] ?? $prof['email'] ?? ''));
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) \Json::fail('E-mail do usuário inválido.', 422);
      $name = trim((string)($_POST['name'] ?? $u['name'] ?? $prof['name'] ?? 'Cliente'));

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

      $creditCardToken = null;
      if ($billingType === 'CREDIT_CARD' && !empty($_POST['cardNumber'])) {
        $tok = $asaas->tokenizeCard([
          'creditCardNumber' => preg_replace('/\D+/', '', $_POST['cardNumber']),
          'creditCardBrand'  => $_POST['brand'] ?? null,
          'holderName'       => $_POST['holderName'] ?? null,
          'expirationMonth'  => $_POST['expMonth'] ?? null,
          'expirationYear'   => $_POST['expYear'] ?? null,
          'securityCode'     => $_POST['cvv'] ?? null,
          'customer'         => $cust['id'],
        ]);
        $creditCardToken = $tok['creditCardToken'] ?? null;
      }

      $cycleAsaas = $cycleUi === 'yearly' ? 'YEARLY' : 'MONTHLY';
      $payload = [
        'customer'    => $cust['id'],
        'billingType' => $billingType,
        'value'       => $amount,
        'cycle'       => $cycleAsaas,
        'nextDueDate' => $nextDueDate,
      ];
      if ($creditCardToken) $payload['creditCardToken'] = $creditCardToken;

      $sub = $asaas->createSubscription($payload);

      // >>>> IMPORTANTE: assinatura nasce como 'suspensa' (aguardando pagamento)
      $statusStart = 'suspensa';

      $ins = $pdo->prepare("
        INSERT INTO subscriptions
          (user_id, plan_id, cycle, status, started_at, renew_at, amount, asaas_customer_id, asaas_subscription_id, created_at)
        VALUES
          (?,       ?,      ?,     ?,      NOW(),     ?,        ?,      ?,                 ?,                    NOW())
      ");
      $ins->execute([$userId, $planId, $cycleUi, $statusStart, $nextDueDate, $amount, $cust['id'], $sub['id']]);

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
        'payment' => [
          'id'         => $payment['id']          ?? null,
          'invoiceUrl' => $payment['invoiceUrl']  ?? null,
          'bankSlipUrl'=> $payment['bankSlipUrl'] ?? null,
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
