<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';
require_once __DIR__ . '/../../services/Membership.php';

final class MeController
{
  // GET /?r=api/member/overview
  public function overview(): void
  {
    \Auth::requireRole(['member','admin']);
    $pdo = \DB::pdo();

    $u = \Auth::user();
    $userId = (int)($u['id'] ?? 0);
    if ($userId <= 0) \Json::fail('unauthorized', 401);

    // Recarrega dados reais do usuário (inclui document/phone/current_plan_id)
    $stU = $pdo->prepare("SELECT id,name,email,document,phone,current_plan_id,role FROM users WHERE id=? LIMIT 1");
    $stU->execute([$userId]);
    $uDb = $stU->fetch(PDO::FETCH_ASSOC) ?: [];

    // (Opcional, mas recomendado) garante consistência por faturas pagas
    // Não falha o overview se der erro.
    try { \App\Services\Membership::recomputeUserPlan($userId); } catch (\Throwable $e) {}

    // ===== Detecta colunas de preço (compat: price_monthly/price_yearly OU monthly_price/yearly_price)
    $cols = $pdo->query("
      SELECT LOWER(COLUMN_NAME)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
    ")->fetchAll(PDO::FETCH_COLUMN) ?: [];

    $colM = in_array('price_monthly', $cols) ? 'price_monthly' : (in_array('monthly_price', $cols) ? 'monthly_price' : null);
    $colY = in_array('price_yearly',  $cols) ? 'price_yearly'  : (in_array('yearly_price',  $cols) ? 'yearly_price'  : null);

    // =====================================================================
    // ATIVA = assinatura com ÚLTIMA fatura PAGA cuja cobertura (due_date) >= hoje
    // (isso é o que evita o badge ficar desatualizado se status não foi promovido)
    // =====================================================================
    $activeSub = null;
    $activePlan = null;
    $activeInvoice = null;

    $st = $pdo->prepare("
      SELECT
        s.*,
        i.id              AS inv_db_id,
        i.asaas_invoice_id AS inv_asaas_id,
        i.value           AS inv_value,
        i.status          AS inv_status,
        i.due_date        AS inv_due_date,
        i.paid_at         AS inv_paid_at,
        i.raw             AS inv_raw
      FROM subscriptions s
      JOIN invoices i ON i.subscription_id = s.id
      WHERE s.user_id = ?
        AND i.status = 'paid'
        AND (i.due_date IS NULL OR i.due_date >= CURDATE())
      ORDER BY COALESCE(i.due_date, s.renew_at, DATE_ADD(s.started_at, INTERVAL 30 DAY)) DESC, i.id DESC
      LIMIT 1
    ");
    $st->execute([$userId]);
    if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $activeSub = $row;

      // normaliza campos para o front
      $activeSub['status']  = 'ativa';
      $activeSub['renew_at'] = $row['inv_due_date'] ?? ($row['renew_at'] ?? null);

      // amount preferencial: o da assinatura; se vazio, pega o valor da fatura
      $amt = isset($row['amount']) ? (float)$row['amount'] : 0.0;
      if ($amt <= 0 && isset($row['inv_value'])) $amt = (float)$row['inv_value'];
      $activeSub['amount'] = $amt;

      // Invoice ativa (informativa)
      $raw = [];
      if (!empty($row['inv_raw'])) {
        try { $raw = json_decode((string)$row['inv_raw'], true) ?: []; } catch (\Throwable $e) {}
      }
      $activeInvoice = [
        'invoice_id'  => $row['inv_asaas_id'] ?? null,
        'status'      => 'paid',
        'value'       => isset($row['inv_value']) ? (float)$row['inv_value'] : $amt,
        'due_date'    => $row['inv_due_date'] ?? null,
        'paid_at'     => $row['inv_paid_at'] ?? null,
        'invoiceUrl'  => $raw['invoiceUrl']  ?? null,
        'bankSlipUrl' => $raw['bankSlipUrl'] ?? null,
      ];

      // Se por algum motivo o banco ainda está como "suspensa", promove aqui (idempotente)
      try {
        $pdo->beginTransaction();

        $pdo->prepare("
          UPDATE subscriptions
          SET status='ativa', renew_at=COALESCE(?, renew_at)
          WHERE id=?
        ")->execute([
          $activeSub['renew_at'],
          (int)$row['id']
        ]);

        $pdo->prepare("
          UPDATE subscriptions
          SET status='suspensa'
          WHERE user_id=? AND id<>? AND status<>'cancelada'
        ")->execute([$userId, (int)$row['id']]);

        $pdo->prepare("UPDATE users SET current_plan_id=? WHERE id=?")
            ->execute([(string)$row['plan_id'], $userId]);

        $pdo->commit();
      } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
      }

      // Carrega plan
      $q = $pdo->prepare("
        SELECT id, name, ".($colM ?: 'NULL')." AS pm, ".($colY ?: 'NULL')." AS py, status
        FROM plans WHERE id=? LIMIT 1
      ");
      $q->execute([(string)$row['plan_id']]);
      $activePlan = $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // =====================================================================
    // PENDENTE = última fatura pending/overdue (exclui a assinatura ativa, se existir)
    // =====================================================================
    $pendingSub = null;
    $pendingPlan = null;
    $pendingHasInvoice = false;
    $pendingInvoice = null;

    $sql = "
      SELECT
        s.id   AS s_id,
        s.user_id, s.plan_id, s.cycle, s.status AS s_status,
        s.started_at, s.renew_at, s.amount, s.qty_users,
        s.asaas_customer_id, s.asaas_subscription_id,
        i.id   AS i_id,
        i.asaas_invoice_id, i.value AS i_value, i.status AS i_status,
        i.due_date AS i_due_date, i.paid_at AS i_paid_at, i.raw AS i_raw,
        p.name AS plan_name
      FROM subscriptions s
      JOIN invoices i ON i.subscription_id = s.id
      LEFT JOIN plans p ON p.id = s.plan_id
      WHERE s.user_id = :uid
        AND s.status <> 'cancelada'
        AND i.status IN ('pending','overdue')
    ";
    $params = [':uid' => $userId];

    if ($activeSub && !empty($activeSub['id'])) {
      $sql .= " AND s.id <> :activeId ";
      $params[':activeId'] = (int)$activeSub['id'];
    }
    $sql .= " ORDER BY i.id DESC LIMIT 1";

    $sp = $pdo->prepare($sql);
    $sp->execute($params);
    if ($row = $sp->fetch(PDO::FETCH_ASSOC)) {
      $pendingSub = [
        'id'                    => (int)$row['s_id'],
        'user_id'               => (int)$row['user_id'],
        'plan_id'               => (string)$row['plan_id'],
        'cycle'                 => (string)$row['cycle'],
        'status'                => (string)$row['s_status'],
        'started_at'            => $row['started_at'],
        'renew_at'              => $row['renew_at'],
        'amount'                => $row['amount'],
        'qty_users'             => isset($row['qty_users']) ? (int)$row['qty_users'] : 1,
        'asaas_customer_id'     => $row['asaas_customer_id'],
        'asaas_subscription_id' => $row['asaas_subscription_id'],
      ];

      $qp = $pdo->prepare("
        SELECT id, name, ".($colM ?: 'NULL')." AS pm, ".($colY ?: 'NULL')." AS py, status
        FROM plans WHERE id=? LIMIT 1
      ");
      $qp->execute([$pendingSub['plan_id']]);
      $pendingPlan = $qp->fetch(PDO::FETCH_ASSOC) ?: [
        'id' => $pendingSub['plan_id'],
        'name' => $row['plan_name'] ?? $pendingSub['plan_id']
      ];

      $raw = [];
      if (!empty($row['i_raw'])) {
        try { $raw = json_decode((string)$row['i_raw'], true) ?: []; } catch (\Throwable $e) {}
      }

      $pendingHasInvoice = true;
      $pendingInvoice = [
        'invoice_id'  => $row['asaas_invoice_id'],
        'status'      => $row['i_status'],
        'value'       => (float)$row['i_value'],
        'due_date'    => $row['i_due_date'],
        'paid_at'     => $row['i_paid_at'],
        'invoiceUrl'  => $raw['invoiceUrl']  ?? null,
        'bankSlipUrl' => $raw['bankSlipUrl'] ?? null,
      ];
    }

    // ===== Resposta
    \Json::ok([
      'user' => [
        'id' => $userId,
        'name' => $uDb['name'] ?? ($u['name'] ?? null),
        'email' => $uDb['email'] ?? ($u['email'] ?? null),
        'document' => $uDb['document'] ?? null,
        'phone' => $uDb['phone'] ?? null,
        'current_plan_id' => $uDb['current_plan_id'] ?? null,
        'role' => $uDb['role'] ?? ($u['role'] ?? null),
      ],

      // Back-compat
      'subscription'        => $activeSub,
      'plan'                => $activePlan,

      // Novos e explícitos
      'activeSubscription'  => $activeSub,
      'activePlan'          => $activePlan,
      'activeInvoice'       => $activeInvoice,

      'pendingSubscription' => $pendingSub,
      'pendingPlan'         => $pendingPlan,
      'pendingHasInvoice'   => $pendingHasInvoice,
      'pendingInvoice'      => $pendingInvoice,
    ]);
  }
}
