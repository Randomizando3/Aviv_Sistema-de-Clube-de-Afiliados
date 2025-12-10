<?php
namespace Api\Admin;

use PDO;

require_once __DIR__ . '/../../../core/DB.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Json.php';

class StatsController {
  public function overview(): void {
    \Auth::requireRole(['admin']);
    $pdo = \DB::pdo();

    $out = [
      'users' => 0,
      'active_subs' => 0,
      'plans' => 0,
      'benefits' => 0,
      'mrr_30d' => 0.0,
    ];

    // usuários
    $out['users'] = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // assinaturas ativas
    try {
      $out['active_subs'] = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status IN ('active','paid')")->fetchColumn();
    } catch (\Throwable $e) { $out['active_subs'] = 0; }

    // planos
    try {
      $out['plans'] = (int)$pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn();
    } catch (\Throwable $e) { $out['plans'] = 0; }

    // benefícios
    try {
      $out['benefits'] = (int)$pdo->query("SELECT COUNT(*) FROM benefits")->fetchColumn();
    } catch (\Throwable $e) { $out['benefits'] = 0; }

    // MRR simples: soma de invoices pagas nos últimos 30 dias
    try {
      $st = $pdo->query("SELECT SUM(value) FROM invoices WHERE status='paid' AND paid_at >= (NOW() - INTERVAL 30 DAY)");
      $sum = (float)($st->fetchColumn() ?: 0);
      $out['mrr_30d'] = $sum;
    } catch (\Throwable $e) { $out['mrr_30d'] = 0.0; }

    \Json::ok($out);
  }
}
