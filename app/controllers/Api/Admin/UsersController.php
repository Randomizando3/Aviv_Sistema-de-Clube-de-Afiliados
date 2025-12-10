<?php
namespace Api\Admin;

use PDO;

require_once __DIR__ . '/../../../core/DB.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Json.php';

class UsersController {
  public function index(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    $q = trim($_GET['q'] ?? '');
    $args = [];
    $sql = "SELECT id, name, email, role, created_at FROM users";
    if ($q !== '') {
      $sql .= " WHERE name LIKE ? OR email LIKE ?";
      $like = "%$q%";
      $args = [$like, $like];
    }
    $sql .= " ORDER BY id DESC LIMIT 100";

    $st = $pdo->prepare($sql);
    $st->execute($args);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    \Json::ok(['users' => $rows]);
  }

  public function setRole(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    $id   = (int)($_POST['id'] ?? 0);
    $role = trim($_POST['role'] ?? '');

    $allowed = ['member','admin','partner','affiliate'];
    if ($id <= 0 || !in_array($role, $allowed, true)) {
      \Json::fail('invalid_payload', 422);
    }

    $st = $pdo->prepare("UPDATE users SET role=? WHERE id=?");
    $ok = $st->execute([$role, $id]);
    if (!$ok) \Json::fail('db_error', 500);

    \Json::ok(['ok'=>true, 'id'=>$id, 'role'=>$role]);
  }
}
