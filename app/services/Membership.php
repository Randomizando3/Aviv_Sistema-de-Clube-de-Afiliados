<?php
namespace App\Services;

final class Membership
{
  /**
   * Retorna true se a assinatura tem ao menos UMA fatura PAGA
   * cuja cobertura (derivada do due_date) ainda vale hoje.
   */
  public static function isSubscriptionActive(int $subscriptionId): bool {
    $pdo = \DB::pdo();
    $st = $pdo->prepare("
      SELECT due_date
      FROM invoices
      WHERE subscription_id = ?
        AND status = 'paid'
      ORDER BY due_date DESC, id DESC
      LIMIT 1
    ");
    $st->execute([$subscriptionId]);
    $row = $st->fetch();
    if (!$row || empty($row['due_date'])) return false;

    $dueDate = (string)$row['due_date'];
    return (date('Y-m-d') <= $dueDate);
  }

  /**
   * Procura a fatura PAGA mais recente entre TODAS as assinaturas do usuário
   * e ativa o plano correspondente; caso contrário, coloca o usuário em Free.
   */
  public static function recomputeUserPlan(int $userId): array {
    $pdo = \DB::pdo();

    // 1) Assinatura com a fatura paga mais recente
    $q = $pdo->prepare("
      SELECT s.id, s.plan_id, s.cycle,
             MAX(i.due_date) AS last_due
      FROM subscriptions s
      JOIN invoices i ON i.subscription_id = s.id
      WHERE s.user_id = ?
        AND i.status = 'paid'
      GROUP BY s.id, s.plan_id, s.cycle
      ORDER BY last_due DESC
      LIMIT 1
    ");
    $q->execute([$userId]);
    $best = $q->fetch();

    if ($best && !empty($best['last_due']) && date('Y-m-d') <= (string)$best['last_due']) {
      // Tem cobertura vigente → ativa essa assinatura e seta o plano no usuário
      $pdo->prepare("UPDATE users SET current_plan_id=? WHERE id=?")
          ->execute([$best['plan_id'], $userId]);

      $pdo->prepare("UPDATE subscriptions SET status='ativa' WHERE id=?")
          ->execute([(int)$best['id']]);

      return [
        'active'          => true,
        'plan_id'         => $best['plan_id'],
        'subscription_id' => (int)$best['id'],
      ];
    }

    // 2) Sem fatura paga vigente → manter última assinatura como suspensa e cair pra Free
    $lastSub = $pdo->prepare("
      SELECT id, plan_id
      FROM subscriptions
      WHERE user_id=?
      ORDER BY started_at DESC, id DESC
      LIMIT 1
    ");
    $lastSub->execute([$userId]);
    $s = $lastSub->fetch();

    if ($s) {
      $pdo->prepare("UPDATE subscriptions SET status='suspensa' WHERE id=?")
          ->execute([(int)$s['id']]);
    }

    $pdo->prepare("UPDATE users SET current_plan_id='Free' WHERE id=?")
        ->execute([$userId]);

    return [
      'active'          => false,
      'plan_id'         => 'Free',
      'subscription_id' => $s ? (int)$s['id'] : null,
    ];
  }
}
