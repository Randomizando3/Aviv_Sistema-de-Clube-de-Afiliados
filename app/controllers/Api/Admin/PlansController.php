<?php
namespace Api\Admin;

use PDO;

require_once __DIR__ . '/../../../core/DB.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Json.php';

class PlansController {

  /* ========= Helpers de schema (compatível 5.7/MariaDB) ========= */

  private function ensurePlansSchema(PDO $pdo): void {
    $db = $this->currentDb($pdo);

    // tabela existe?
    if (!$this->tableExists($pdo, $db, 'plans')) {
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
    }

    // colunas existentes
    $this->addColumnIfMissing($pdo, $db, 'plans', 'description',   "ALTER TABLE plans ADD COLUMN description TEXT NULL AFTER name");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'price_monthly', "ALTER TABLE plans ADD COLUMN price_monthly DECIMAL(10,2) DEFAULT 0");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'price_yearly',  "ALTER TABLE plans ADD COLUMN price_yearly  DECIMAL(10,2) DEFAULT 0");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'status',        "ALTER TABLE plans ADD COLUMN status ENUM('active','inactive') DEFAULT 'active'");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'sort_order',    "ALTER TABLE plans ADD COLUMN sort_order INT DEFAULT 0");

    // familiar (novas)
    $this->addColumnIfMissing($pdo, $db, 'plans', 'is_family',        "ALTER TABLE plans ADD COLUMN is_family TINYINT(1) DEFAULT 0");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'min_users',        "ALTER TABLE plans ADD COLUMN min_users INT DEFAULT 1");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'max_users',        "ALTER TABLE plans ADD COLUMN max_users INT DEFAULT 0");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'add_user_monthly', "ALTER TABLE plans ADD COLUMN add_user_monthly DECIMAL(10,2) DEFAULT 0");
    $this->addColumnIfMissing($pdo, $db, 'plans', 'add_user_yearly',  "ALTER TABLE plans ADD COLUMN add_user_yearly  DECIMAL(10,2) DEFAULT 0");

    $this->addColumnIfMissing($pdo, $db, 'plans', 'created_at',    "ALTER TABLE plans ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");

    // 👇 Tratamento para colunas legadas 'monthly_price' e 'yearly_price'
    if ($this->colExists($pdo, $db, 'plans', 'monthly_price')) {
      $pdo->exec("ALTER TABLE plans MODIFY COLUMN monthly_price DECIMAL(10,2) NULL DEFAULT 0");
      $pdo->exec("UPDATE plans SET price_monthly = COALESCE(NULLIF(price_monthly,0), monthly_price) WHERE monthly_price IS NOT NULL");
    }
    if ($this->colExists($pdo, $db, 'plans', 'yearly_price')) {
      $pdo->exec("ALTER TABLE plans MODIFY COLUMN yearly_price DECIMAL(10,2) NULL DEFAULT 0");
      $pdo->exec("UPDATE plans SET price_yearly = COALESCE(NULLIF(price_yearly,0), yearly_price) WHERE yearly_price IS NOT NULL");
    }

    // sane defaults para familiar (se coluna foi criada agora)
    try {
      $pdo->exec("UPDATE plans SET min_users=1 WHERE min_users IS NULL OR min_users<1");
      $pdo->exec("UPDATE plans SET max_users=0 WHERE max_users IS NULL OR max_users<0");
      $pdo->exec("UPDATE plans SET add_user_monthly=0 WHERE add_user_monthly IS NULL OR add_user_monthly<0");
      $pdo->exec("UPDATE plans SET add_user_yearly=0 WHERE add_user_yearly IS NULL OR add_user_yearly<0");
      $pdo->exec("UPDATE plans SET is_family=0 WHERE is_family IS NULL");
    } catch (\Throwable $e) {}

    // Semeia se vazio
    $count = (int)$pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn();
    if ($count === 0) {
      $seed = $pdo->prepare("INSERT INTO plans (id,name,description,price_monthly,price_yearly,status,sort_order,is_family,min_users,max_users,add_user_monthly,add_user_yearly,created_at) VALUES
        ('start','Start',NULL,29.90,299.00,'active',1,0,1,0,0,0,NOW()),
        ('plus','Plus',NULL,59.90,599.00,'active',2,0,1,0,0,0,NOW()),
        ('prime','Prime',NULL,99.90,999.00,'active',3,0,1,0,0,0,NOW())");
      $seed->execute();
    }
  }

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

  /* ====================== Endpoints ====================== */

  public function index(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensurePlansSchema($pdo);

      $st = $pdo->query("SELECT
          id, name, description, price_monthly, price_yearly, status, sort_order,
          is_family, min_users, max_users, add_user_monthly, add_user_yearly,
          created_at
        FROM plans
        ORDER BY sort_order ASC, id ASC");
      $rows = $st->fetchAll(PDO::FETCH_ASSOC);
      \Json::ok(['plans' => $rows]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }

  public function save(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensurePlansSchema($pdo);

      $id     = trim($_POST['id'] ?? '');
      $name   = trim($_POST['name'] ?? '');
      $desc   = isset($_POST['description']) ? trim((string)$_POST['description']) : null; // permite limpar
      $pm     = (float)($_POST['price_monthly'] ?? 0);
      $py     = (float)($_POST['price_yearly']  ?? 0);
      $status = in_array(($_POST['status'] ?? 'inactive'), ['active','inactive'], true) ? $_POST['status'] : 'inactive';
      $sort   = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : null;

      // familiar
      $isFam  = (string)($_POST['is_family'] ?? '0') === '1' ? 1 : 0;
      $minU   = (int)($_POST['min_users'] ?? 1);
      $maxU   = (int)($_POST['max_users'] ?? 0);
      $addM   = (float)($_POST['add_user_monthly'] ?? 0);
      $addY   = (float)($_POST['add_user_yearly']  ?? 0);

      if ($id === '' || $name === '') {
        \Json::fail('invalid_payload', 422);
      }

      if ($minU < 1) $minU = 1;
      if ($maxU < 0) $maxU = 0;
      if ($addM < 0) $addM = 0;
      if ($addY < 0) $addY = 0;

      // se não for familiar, zera config para não acumular “lixo”
      if (!$isFam) {
        $minU = 1; $maxU = 0; $addM = 0; $addY = 0;
      } else {
        if ($minU < 2) $minU = 2; // familiar mínimo recomendado
        if ($maxU > 0 && $maxU < $minU) $maxU = $minU;
      }

      // existe?
      $chk = $pdo->prepare("SELECT COUNT(*) FROM plans WHERE id=?");
      $chk->execute([$id]);
      $exists = (int)$chk->fetchColumn() > 0;

      if ($exists) {
        $set = [
          "name=?", "price_monthly=?", "price_yearly=?", "status=?",
          "is_family=?", "min_users=?", "max_users=?", "add_user_monthly=?", "add_user_yearly=?"
        ];
        $pms = [$name, $pm, $py, $status, $isFam, $minU, $maxU, $addM, $addY];

        if ($desc !== null) { // só atualiza se veio no POST (permite string vazia)
          $set[] = "description=?";
          $pms[] = $desc;
        }
        if ($sort !== null) {
          $set[] = "sort_order=?";
          $pms[] = $sort;
        }

        $pms[] = $id;
        $sql = "UPDATE plans SET ".implode(',', $set)." WHERE id=?";
        $ok = $pdo->prepare($sql)->execute($pms);
        if (!$ok) \Json::fail('db_error', 500);
      } else {
        if ($sort === null) {
          $sort = (int)$pdo->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM plans")->fetchColumn();
        }
        $sql = "INSERT INTO plans
          (id,name,description,price_monthly,price_yearly,status,sort_order,is_family,min_users,max_users,add_user_monthly,add_user_yearly,created_at)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())";
        $ok = $pdo->prepare($sql)->execute([
          $id, $name, ($desc ?? null), $pm, $py, $status, $sort,
          $isFam, $minU, $maxU, $addM, $addY
        ]);
        if (!$ok) \Json::fail('db_error', 500);
      }

      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }

  public function delete(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensurePlansSchema($pdo);

      $id = trim($_POST['id'] ?? '');
      if ($id === '') \Json::fail('invalid_payload', 422);

      // Não apaga se houver subscriptions referenciando
      try {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE plan_id=?");
        $chk->execute([$id]);
        if ((int)$chk->fetchColumn() > 0) {
          \Json::fail('plan_in_use', 409);
        }
      } catch (\Throwable $e) {
        // se a tabela subscriptions não existir ainda, ignora bloqueio
      }

      $st = $pdo->prepare("DELETE FROM plans WHERE id=?");
      $ok = $st->execute([$id]);
      if (!$ok) \Json::fail('db_error', 500);

      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }

  public function reorder(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensurePlansSchema($pdo);

      $ids = $_POST['ids'] ?? null;
      if (!$ids) {
        $csv = trim((string)($_POST['order'] ?? ''));
        $ids = $csv ? array_filter(array_map('trim', explode(',', $csv))) : [];
      }
      if (!is_array($ids) || empty($ids)) \Json::fail('invalid_payload', 422);

      $pdo->beginTransaction();
      $i = 1;
      $st = $pdo->prepare("UPDATE plans SET sort_order=? WHERE id=?");
      foreach ($ids as $id) {
        $st->execute([$i++, $id]);
      }
      $pdo->commit();

      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      \Json::fail($e->getMessage(), 500);
    }
  }
}
