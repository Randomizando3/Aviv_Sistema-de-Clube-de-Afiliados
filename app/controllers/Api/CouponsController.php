<?php
namespace Api;
use PDO;
require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';

class CouponsController {
  private function ensureSchema(PDO $pdo): void {
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(64) NOT NULL,
        user_id INT NOT NULL,
        benefit_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY user_idx (user_id),
        KEY benefit_idx (benefit_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
  }

  public function generate(): void {
    \Auth::start();
    $me = \Auth::user(); if (!$me) \Json::fail('unauthorized', 401);

    $benefitId = (int)($_POST['benefit_id'] ?? 0);
    if ($benefitId <= 0) \Json::fail('invalid_benefit', 422);

    $pdo = \DB::pdo();
    $this->ensureSchema($pdo);

    // checa benefício ativo
    $b = $pdo->prepare("SELECT id FROM benefits WHERE id=? LIMIT 1");
    $b->execute([$benefitId]);
    if (!$b->fetch()) \Json::fail('benefit_unavailable', 409);

    $code = 'AVIV-'.strtoupper(bin2hex(random_bytes(3))).'-'.strtoupper(bin2hex(random_bytes(3)));
    $pdo->prepare("INSERT INTO coupons (code,user_id,benefit_id) VALUES (?,?,?)")->execute([$code,$me['id'],$benefitId]);
    \Json::ok(['code'=>$code]);
  }

  /** GET /?r=api/coupons/mine */
  public function mine(): void {
    \Auth::start();
    $me = \Auth::user(); if (!$me) \Json::fail('unauthorized', 401);

    $pdo = \DB::pdo();
    $this->ensureSchema($pdo);

    $st = $pdo->prepare("
      SELECT c.code, c.created_at, b.title AS benefit_title
      FROM coupons c
      LEFT JOIN benefits b ON b.id = c.benefit_id
      WHERE c.user_id=?
      ORDER BY c.id DESC
      LIMIT 500
    ");
    $st->execute([$me['id']]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    \Json::ok(['coupons'=>$rows]);
  }
}
