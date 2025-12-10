<?php
namespace Api\Admin;

use PDO;

require_once __DIR__ . '/../../../core/DB.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Json.php';

class BenefitsController {

  /* ================= Helpers de schema ================= */

  private function ensureSchema(PDO $pdo): void {
    $db = $this->currentDb($pdo);

    // Tabela benefits (TEXT sem DEFAULT)
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS `benefits` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(150) NOT NULL,
        `partner` VARCHAR(120) DEFAULT NULL,
        `type` ENUM('coupon','link','service') NOT NULL DEFAULT 'coupon',
        `specialty` VARCHAR(80) DEFAULT NULL,
        `code` VARCHAR(80) DEFAULT NULL,
        `link` VARCHAR(255) DEFAULT NULL,
        `valid_until` DATE DEFAULT NULL,
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `description` TEXT NULL,
        `image_url` VARCHAR(255) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Relação benefício <-> planos
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS `benefit_plans` (
        `benefit_id` INT UNSIGNED NOT NULL,
        `plan_id` VARCHAR(50) NOT NULL,
        PRIMARY KEY (`benefit_id`, `plan_id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // ---- Migração suave: colunas que possam faltar/estar divergentes ----

    // 1) Renomeia is_active -> active, se ainda existir
    if (!$this->colExists($pdo, $db, 'benefits', 'active') && $this->colExists($pdo, $db, 'benefits', 'is_active')) {
      $pdo->exec("ALTER TABLE `benefits` CHANGE COLUMN `is_active` `active` TINYINT(1) NOT NULL DEFAULT 1");
    }

    // 2) Garante todas as colunas usadas pela API
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'partner',     "ALTER TABLE `benefits` ADD COLUMN `partner` VARCHAR(120) DEFAULT NULL AFTER `title`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'type',        "ALTER TABLE `benefits` ADD COLUMN `type` ENUM('coupon','link','service') NOT NULL DEFAULT 'coupon' AFTER `partner`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'specialty',   "ALTER TABLE `benefits` ADD COLUMN `specialty` VARCHAR(80) DEFAULT NULL AFTER `type`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'code',        "ALTER TABLE `benefits` ADD COLUMN `code` VARCHAR(80) DEFAULT NULL AFTER `specialty`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'link',        "ALTER TABLE `benefits` ADD COLUMN `link` VARCHAR(255) DEFAULT NULL AFTER `code`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'valid_until', "ALTER TABLE `benefits` ADD COLUMN `valid_until` DATE DEFAULT NULL AFTER `link`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'active',      "ALTER TABLE `benefits` ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `valid_until`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'description', "ALTER TABLE `benefits` ADD COLUMN `description` TEXT NULL AFTER `active`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'image_url',   "ALTER TABLE `benefits` ADD COLUMN `image_url` VARCHAR(255) DEFAULT NULL AFTER `description`");
    $this->addColumnIfMissing($pdo, $db, 'benefits', 'created_at',  "ALTER TABLE `benefits` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `image_url`");

    // 3) Caso 'type' exista mas com tipo diferente, normaliza para ENUM esperado
    if ($this->colExists($pdo, $db, 'benefits', 'type')) {
      // força ENUM correto
      $pdo->exec("ALTER TABLE `benefits` MODIFY COLUMN `type` ENUM('coupon','link','service') NOT NULL DEFAULT 'coupon'");
    }

    // 4) Remove a coluna "category" se sobrou de schema antigo (causava ERROR 1364)
    if ($this->colExists($pdo, $db, 'benefits', 'category')) {
      $pdo->exec("ALTER TABLE `benefits` DROP COLUMN `category`");
    }

    // Garante tabela de planos mínima para a seleção
    if (!$this->tableExists($pdo, $db, 'plans')) {
      $pdo->exec("
        CREATE TABLE IF NOT EXISTS `plans` (
          `id` VARCHAR(50) PRIMARY KEY,
          `name` VARCHAR(120) NOT NULL,
          `price_monthly` DECIMAL(10,2) DEFAULT 0,
          `price_yearly`  DECIMAL(10,2) DEFAULT 0,
          `status` ENUM('active','inactive') DEFAULT 'active',
          `sort_order` INT DEFAULT 0,
          `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
      ");
      $count = (int)$pdo->query("SELECT COUNT(*) FROM `plans`")->fetchColumn();
      if ($count === 0) {
        $pdo->exec("INSERT INTO `plans` (`id`,`name`,`price_monthly`,`price_yearly`,`status`,`sort_order`,`created_at`) VALUES
          ('start','Start',29.90,299.00,'active',1,NOW()),
          ('plus','Plus',59.90,599.00,'active',2,NOW()),
          ('prime','Prime',99.90,999.00,'active',3,NOW())");
      }
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

  /* ===================== Endpoints ===================== */

  public function index(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);

      $rows = $pdo->query("
        SELECT `id`,`title`,`partner`,`type`,`specialty`,`code`,`link`,`valid_until`,`active`,`description`,`image_url`,`created_at`
        FROM `benefits`
        ORDER BY `id` DESC
      ")->fetchAll(PDO::FETCH_ASSOC);

      // agrega planos
      $map = [];
      if ($rows) {
        $ids = implode(',', array_map('intval', array_column($rows, 'id')));
        if ($ids !== '') {
          $q = $pdo->query("SELECT `benefit_id`,`plan_id` FROM `benefit_plans` WHERE `benefit_id` IN ($ids)");
          foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $map[$r['benefit_id']][] = $r['plan_id'];
          }
        }
      }
      foreach ($rows as &$r) {
        $r['plans'] = $map[$r['id']] ?? [];
      }

      \Json::ok(['benefits' => $rows]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }

  public function save(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);

      $id        = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
      $title     = trim($_POST['title'] ?? '');
      $partner   = trim($_POST['partner'] ?? '');
      $type      = in_array(($_POST['type'] ?? 'coupon'), ['coupon','link','service'], true) ? $_POST['type'] : 'coupon';
      $specialty = trim($_POST['specialty'] ?? '');
      $code      = trim($_POST['code'] ?? '');
      $link      = trim($_POST['link'] ?? '');
      $valid     = trim($_POST['valid_until'] ?? '');
      $active    = (int)($_POST['active'] ?? 1);
      $desc      = trim($_POST['description'] ?? '');
      $img       = trim($_POST['image_url'] ?? '');

      // planos (csv ou array)
      $plans = $_POST['plans'] ?? '';
      if (is_string($plans)) {
        $plans = array_filter(array_map('trim', explode(',', $plans)));
      } elseif (!is_array($plans)) {
        $plans = [];
      }
      $plans = array_values(array_filter($plans, fn($p) => (bool)preg_match('/^[a-z0-9_-]{2,50}$/i', $p)));

      if ($title === '') \Json::fail('title_required', 422);

      if ($id) {
        $st = $pdo->prepare("
          UPDATE `benefits`
             SET `title`=?, `partner`=?, `type`=?, `specialty`=?, `code`=?, `link`=?, `valid_until`=?, `active`=?, `description`=?, `image_url`=?
           WHERE `id`=?
        ");
        $ok = $st->execute([
          $title,
          $partner,
          $type,
          ($specialty ?: null),
          $code,
          $link,
          ($valid ?: null),
          $active,
          ($desc ?: null),
          ($img ?: null),
          $id
        ]);
        if (!$ok) \Json::fail('db_error', 500);

        // sync planos
        $pdo->prepare("DELETE FROM `benefit_plans` WHERE `benefit_id`=?")->execute([$id]);
        if (!empty($plans)) {
          $ins = $pdo->prepare("INSERT INTO `benefit_plans` (`benefit_id`,`plan_id`) VALUES (?,?)");
          foreach ($plans as $p) $ins->execute([$id, $p]);
        }

        \Json::ok(['ok'=>true, 'id'=>$id]);
      } else {
        $st = $pdo->prepare("
          INSERT INTO `benefits`
          (`title`,`partner`,`type`,`specialty`,`code`,`link`,`valid_until`,`active`,`description`,`image_url`,`created_at`)
          VALUES (?,?,?,?,?,?,?,?,?,?,NOW())
        ");
        $ok = $st->execute([
          $title,
          $partner,
          $type,
          ($specialty ?: null),
          $code,
          $link,
          ($valid ?: null),
          $active,
          ($desc ?: null),
          ($img ?: null)
        ]);
        if (!$ok) \Json::fail('db_error', 500);
        $newId = (int)$pdo->lastInsertId();

        if (!empty($plans)) {
          $ins = $pdo->prepare("INSERT INTO `benefit_plans` (`benefit_id`,`plan_id`) VALUES (?,?)");
          foreach ($plans as $p) $ins->execute([$newId, $p]);
        }

        \Json::ok(['ok'=>true, 'id'=>$newId]);
      }
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }

  public function delete(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    try {
      $this->ensureSchema($pdo);
      $id = (int)($_POST['id'] ?? 0);
      if ($id <= 0) \Json::fail('invalid_payload', 422);

      $pdo->prepare("DELETE FROM `benefit_plans` WHERE `benefit_id`=?")->execute([$id]);
      $ok = $pdo->prepare("DELETE FROM `benefits` WHERE `id`=?")->execute([$id]);
      if (!$ok) \Json::fail('db_error', 500);

      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }

  // Upload de imagem -> retorna URL pública
  public function upload(): void {
    \Auth::requireRole(['admin']);

    try {
      if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        \Json::fail('upload_failed', 400);
      }
      $f = $_FILES['file'];
      $mime = mime_content_type($f['tmp_name']);
      $allowed = ['image/jpeg','image/png','image/webp'];
      if (!in_array($mime, $allowed, true)) \Json::fail('invalid_mime', 415);

      $ext = match($mime){
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'bin'
      };

      $dir = BASE_PATH . '/public/uploads/benefits';
      if (!is_dir($dir)) @mkdir($dir, 0775, true);

      $name = 'benefit_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $dest = $dir . '/' . $name;

      if (!move_uploaded_file($f['tmp_name'], $dest)) \Json::fail('move_failed', 500);

      $publicUrl = '/uploads/benefits/' . $name;
      \Json::ok(['url' => $publicUrl]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }
}
