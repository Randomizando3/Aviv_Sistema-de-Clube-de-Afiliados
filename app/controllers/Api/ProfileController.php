<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';

class ProfileController
{
  private function currentDb(PDO $pdo): string {
    return (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
  }
  private function colExists(PDO $pdo, string $db, string $table, string $col): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?");
    $q->execute([$db, $table, $col]);
    return (int)$q->fetchColumn() > 0;
  }
  private function ensureUserColumns(PDO $pdo): void {
    $db = $this->currentDb($pdo);
    $adds = [
      'phone'      => "ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(32) NULL AFTER `email`",
      'document'   => "ALTER TABLE `users` ADD COLUMN `document` VARCHAR(32) NULL AFTER `phone`",
      'birth_date' => "ALTER TABLE `users` ADD COLUMN `birth_date` DATE NULL AFTER `document`",
      'address'    => "ALTER TABLE `users` ADD COLUMN `address` VARCHAR(255) NULL AFTER `birth_date`",
      'city'       => "ALTER TABLE `users` ADD COLUMN `city` VARCHAR(120) NULL AFTER `address`",
      'state'      => "ALTER TABLE `users` ADD COLUMN `state` CHAR(2) NULL AFTER `city`",
      'zip'        => "ALTER TABLE `users` ADD COLUMN `zip` VARCHAR(20) NULL AFTER `state`",
      'avatar_url' => "ALTER TABLE `users` ADD COLUMN `avatar_url` VARCHAR(255) NULL AFTER `zip`",
    ];
    foreach ($adds as $col => $ddl) {
      if (!$this->colExists($pdo, $db, 'users', $col)) {
        try { $pdo->exec($ddl); } catch (\Throwable $e) {}
      }
    }
  }

  /** GET /?r=api/member/profile */
  public function index(): void {
    \Auth::start();
    $me = \Auth::user(); if (!$me) \Json::fail('unauthorized', 401);

    $pdo = \DB::pdo();
    $this->ensureUserColumns($pdo);

    $cols = [
      'id','name','email','role','status','created_at','updated_at',
      'phone','document','birth_date','address','city','state','zip','avatar_url'
    ];
    $sel = implode(',', array_map(fn($c)=>"`$c`", $cols));

    try {
      $st = $pdo->prepare("SELECT $sel FROM `users` WHERE `id`=? LIMIT 1");
      $st->execute([$me['id']]);
      $u = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (\Throwable $e) {
      $st = $pdo->prepare("SELECT `id`,`name`,`email`,`role`,`status`,`created_at`,`updated_at` FROM `users` WHERE `id`=? LIMIT 1");
      $st->execute([$me['id']]);
      $u = $st->fetch(PDO::FETCH_ASSOC) ?: [];
      foreach (['phone','document','birth_date','address','city','state','zip','avatar_url'] as $k) {
        if (!array_key_exists($k, $u)) $u[$k] = null;
      }
    }

    $plan = null;
    try {
      $ps = $pdo->prepare("SELECT plan_id, status, cycle, created_at FROM subscriptions WHERE user_id=? AND status IN ('ativa','active') ORDER BY id DESC LIMIT 1");
      $ps->execute([$me['id']]);
      $plan = $ps->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (\Throwable $e) {}

    \Json::ok(['user'=>$u, 'subscription'=>$plan]);
  }

  /** POST /?r=api/member/profile/save */
  public function save(): void {
    \Auth::start();
    $me = \Auth::user(); if (!$me) \Json::fail('unauthorized', 401);
    // \Auth::checkCsrf();

    $pdo = \DB::pdo();
    $this->ensureUserColumns($pdo);

    $allowed = ['name','phone','document','birth_date','address','city','state','zip','avatar_url'];
    $data = [];
    foreach ($allowed as $k) if (isset($_POST[$k])) $data[$k] = trim((string)$_POST[$k]);

    if (isset($data['state']) && $data['state']!=='') $data['state'] = strtoupper(substr($data['state'],0,2));
    if (isset($data['birth_date'])) {
      $bd = $data['birth_date'];
      if ($bd==='') $data['birth_date'] = null;
      elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$bd)) \Json::fail('invalid_birth_date', 422);
    }

    if (empty($data)) \Json::ok(['ok'=>true]);

    $sets=[]; $params=[];
    foreach ($data as $k=>$v){ $sets[]="`$k`=?"; $params[] = ($v===''?null:$v); }
    $params[] = $me['id'];

    $ok = $pdo->prepare("UPDATE `users` SET ".implode(', ',$sets)." WHERE `id`=?")->execute($params);
    if (!$ok) \Json::fail('db_error', 500);

    \Json::ok(['ok'=>true]);
  }
}
