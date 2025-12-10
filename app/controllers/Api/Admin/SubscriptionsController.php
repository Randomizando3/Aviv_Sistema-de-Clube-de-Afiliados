<?php
namespace Api\Admin;

use PDO;

require_once __DIR__ . '/../../../core/DB.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Json.php';

class SubscriptionsController
{
  /* ===== helpers de schema ===== */
  private function currentDb(PDO $pdo): string {
    return (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
  }
  private function colExists(PDO $pdo, string $db, string $table, string $col): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?");
    $q->execute([$db, $table, $col]);
    return (int)$q->fetchColumn() > 0;
  }
  private function tableExists(PDO $pdo, string $db, string $table): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$db, $table]);
    return (int)$q->fetchColumn() > 0;
  }
  private function addColumnIfMissing(PDO $pdo, string $db, string $table, string $col, string $ddl): void {
    if (!$this->colExists($pdo, $db, $table, $col)) $pdo->exec($ddl);
  }

  private function ensureSchema(PDO $pdo): void {
    $db = $this->currentDb($pdo);

    // Tabela minimal de planos (para join de nome)
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS plans (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(120) NOT NULL,
        price_monthly DECIMAL(10,2) DEFAULT 0,
        price_yearly  DECIMAL(10,2) DEFAULT 0,
        status ENUM('active','inactive') DEFAULT 'active',
        sort_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Tabela subscriptions
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_id VARCHAR(50) NOT NULL,
        cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
        status ENUM('ativa','suspensa','cancelada') NOT NULL DEFAULT 'ativa',
        started_at DATETIME DEFAULT NULL,
        renew_at DATE DEFAULT NULL,
        amount DECIMAL(10,2) NOT NULL DEFAULT 0,
        asaas_subscription_id VARCHAR(80) DEFAULT NULL,
        asaas_customer_id VARCHAR(80) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY (user_id), KEY (plan_id), KEY (status)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Migração suave: garante colunas caso a tabela já exista antiga
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'cycle',
      "ALTER TABLE subscriptions ADD COLUMN cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly' AFTER plan_id");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'status',
      "ALTER TABLE subscriptions ADD COLUMN status ENUM('ativa','suspensa','cancelada') NOT NULL DEFAULT 'ativa' AFTER cycle");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'started_at',
      "ALTER TABLE subscriptions ADD COLUMN started_at DATETIME DEFAULT NULL AFTER status");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'renew_at',
      "ALTER TABLE subscriptions ADD COLUMN renew_at DATE DEFAULT NULL AFTER started_at");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'amount',
      "ALTER TABLE subscriptions ADD COLUMN amount DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER renew_at");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'asaas_subscription_id',
      "ALTER TABLE subscriptions ADD COLUMN asaas_subscription_id VARCHAR(80) DEFAULT NULL AFTER amount");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'asaas_customer_id',
      "ALTER TABLE subscriptions ADD COLUMN asaas_customer_id VARCHAR(80) DEFAULT NULL AFTER asaas_subscription_id");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'created_at',
      "ALTER TABLE subscriptions ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER asaas_customer_id");
    $this->addColumnIfMissing($pdo, $db, 'subscriptions', 'updated_at',
      "ALTER TABLE subscriptions ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
  }

  /* ===== GET /?r=api/admin/subscriptions/list ===== */
  public function index(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);

      $db = $this->currentDb($pdo);
      $hasUsers = $this->tableExists($pdo, $db, 'users');
      $hasPlans = $this->tableExists($pdo, $db, 'plans');

      $status = trim($_GET['status'] ?? '');
      $plan   = trim($_GET['plan'] ?? '');
      $q      = trim($_GET['q'] ?? '');

      $select = "s.id,s.user_id,s.plan_id,s.cycle,s.status,s.started_at,s.renew_at,s.amount,s.asaas_subscription_id";
      $select .= $hasUsers ? ",u.name AS user_name,u.email AS user_email" : ",NULL AS user_name,NULL AS user_email";
      $select .= $hasPlans ? ",p.name AS plan_name" : ",NULL AS plan_name";

      $sql = "SELECT $select FROM subscriptions s";
      if ($hasUsers) $sql .= " LEFT JOIN users u ON u.id = s.user_id";
      if ($hasPlans) $sql .= " LEFT JOIN plans p ON p.id = s.plan_id";
      $sql .= " WHERE 1";
      $params = [];

      if ($status !== '') { $sql .= " AND s.status = ?";  $params[] = $status; }
      if ($plan   !== '') { $sql .= " AND s.plan_id = ?"; $params[] = $plan;   }

      if ($q !== '') {
        if (ctype_digit($q)) {
          $sql .= " AND (s.user_id = ? OR s.id = ?)"; $params[] = (int)$q; $params[] = (int)$q;
        } elseif ($hasUsers) {
          $like = "%$q%";
          $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)"; $params[] = $like; $params[] = $like;
        }
      }

      $sql .= " ORDER BY s.id DESC LIMIT 1000";
      $st = $pdo->prepare($sql);
      $st->execute($params);
      $rows = $st->fetchAll(PDO::FETCH_ASSOC);

      \Json::ok(['subscriptions' => $rows]);
    } catch (\Throwable $e) {
      @file_put_contents(BASE_PATH . '/storage/logs/subscriptions_err.log', date('c')." [index] ".$e->getMessage()."\n", FILE_APPEND);
      \Json::fail($e->getMessage(), 500);
    }
  }

  /* ===== POST /?r=api/admin/subscriptions/save ===== */
  public function save(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);

      $id     = (int)($_POST['id'] ?? 0);
      $planId = trim($_POST['plan_id'] ?? '');
      $status = trim($_POST['status'] ?? '');
      $cycle  = trim($_POST['cycle']  ?? '');

      if ($id <= 0) \Json::fail('invalid_id', 422);
      if ($status !== '' && !in_array($status, ['ativa','suspensa','cancelada'], true)) \Json::fail('invalid_status', 422);
      if ($cycle  !== '' && !in_array($cycle,  ['monthly','yearly'], true))          \Json::fail('invalid_cycle',  422);

      // Busca sub atual para decisões
      $st = $pdo->prepare("SELECT * FROM subscriptions WHERE id=?");
      $st->execute([$id]);
      $cur = $st->fetch(PDO::FETCH_ASSOC);
      if (!$cur) \Json::fail('not_found', 404);

      $fields = []; $params = [];

      if ($planId !== '' && $planId !== $cur['plan_id']) {
        $fields[] = "plan_id=?"; $params[] = $planId;

        // Ajusta amount conforme cycle
        try {
          $sp = $pdo->prepare("SELECT price_monthly, price_yearly FROM plans WHERE id=?");
          $sp->execute([$planId]);
          if ($p = $sp->fetch(PDO::FETCH_ASSOC)) {
            $useCycle = $cycle !== '' ? $cycle : ($cur['cycle'] ?? 'monthly');
            $amount   = $useCycle === 'yearly' ? (float)$p['price_yearly'] : (float)$p['price_monthly'];
            $fields[] = "amount=?"; $params[] = $amount;
          }
        } catch (\Throwable $e) {}
      }
      if ($status !== '' && $status !== $cur['status']) { $fields[] = "status=?"; $params[] = $status; }
      if ($cycle  !== '' && $cycle  !== $cur['cycle'])  { $fields[] = "cycle=?";  $params[] = $cycle;  }

      if (!$fields) \Json::ok(['ok'=>true, 'noop'=>true]);

      $params[] = $id;
      $sql = "UPDATE subscriptions SET ".implode(',', $fields).", updated_at=NOW() WHERE id=?";
      $ok  = $pdo->prepare($sql)->execute($params);
      if (!$ok) \Json::fail('db_error', 500);

      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      @file_put_contents(BASE_PATH . '/storage/logs/subscriptions_err.log', date('c')." [save] ".$e->getMessage()."\n", FILE_APPEND);
      \Json::fail($e->getMessage(), 500);
    }
  }

  /* ===== POST /?r=api/admin/subscriptions/create ===== */
  public function create(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);

      $userId = (int)($_POST['user_id'] ?? 0);
      $planId = trim($_POST['plan_id'] ?? '');
      $cycle  = in_array(($_POST['cycle'] ?? 'monthly'), ['monthly','yearly'], true) ? $_POST['cycle'] : 'monthly';
      $amount = (float)($_POST['amount'] ?? 0);

      if ($userId <= 0 || $planId === '') \Json::fail('invalid_payload', 422);

      $days = $cycle === 'yearly' ? 365 : 30;
      $st = $pdo->prepare("
        INSERT INTO subscriptions (user_id, plan_id, cycle, status, started_at, renew_at, amount)
        VALUES (?, ?, ?, 'ativa', NOW(), DATE_ADD(CURDATE(), INTERVAL ? DAY), ?)
      ");
      $st->execute([$userId, $planId, $cycle, $days, $amount]);

      \Json::ok(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
    } catch (\Throwable $e) {
      @file_put_contents(BASE_PATH . '/storage/logs/subscriptions_err.log', date('c')." [create] ".$e->getMessage()."\n", FILE_APPEND);
      \Json::fail($e->getMessage(), 500);
    }
  }
}
