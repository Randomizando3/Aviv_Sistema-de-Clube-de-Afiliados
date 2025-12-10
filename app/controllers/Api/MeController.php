<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';

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

    // ===== Detecta colunas de preço (compat: price_monthly/price_yearly OU monthly_price/yearly_price)
    $cols = $pdo->query("
      SELECT LOWER(COLUMN_NAME)
      FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans'
    ")->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $colM = in_array('price_monthly', $cols) ? 'price_monthly' : (in_array('monthly_price', $cols) ? 'monthly_price' : null);
    $colY = in_array('price_yearly',  $cols) ? 'price_yearly'  : (in_array('yearly_price',  $cols) ? 'yearly_price'  : null);

    // ===== Assinatura ATIVA (vigente)
    $st = $pdo->prepare("
      SELECT *
      FROM subscriptions
      WHERE user_id=? AND status='ativa'
      ORDER BY COALESCE(renew_at, DATE_ADD(started_at, INTERVAL 30 DAY)) DESC, id DESC
      LIMIT 1
    ");
    $st->execute([$userId]);
    $activeSub = $st->fetch(PDO::FETCH_ASSOC) ?: null;

    $activePlan = null;
    if ($activeSub) {
      $q = $pdo->prepare("
        SELECT id, name, ".($colM ?: 'NULL')." AS pm, ".($colY ?: 'NULL')." AS py, status
        FROM plans WHERE id=? LIMIT 1
      ");
      $q->execute([$activeSub['plan_id']]);
      $activePlan = $q->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ===== Assinatura PENDENTE (ex.: upgrade aguardando pagamento)
    // Procuramos a fatura mais recente PENDING/OVERDUE de uma assinatura do usuário
    // que NÃO esteja cancelada e NÃO seja a assinatura vigente (se houver).
    $pendingSub = null;
    $pendingPlan = null;
    $pendingHasInvoice = false;
    $pendingInvoice = null;

    $sql = "
      SELECT
        s.id   AS s_id,
        s.user_id, s.plan_id, s.cycle, s.status AS s_status,
        s.started_at, s.renew_at, s.amount, s.asaas_customer_id, s.asaas_subscription_id,
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
      // Monta pendingSubscription
      $pendingSub = [
        'id'                    => (int)$row['s_id'],
        'user_id'               => (int)$row['user_id'],
        'plan_id'               => $row['plan_id'],
        'cycle'                 => $row['cycle'],
        'status'                => $row['s_status'],
        'started_at'            => $row['started_at'],
        'renew_at'              => $row['renew_at'],
        'amount'                => $row['amount'],
        'asaas_customer_id'     => $row['asaas_customer_id'],
        'asaas_subscription_id' => $row['asaas_subscription_id'],
      ];

      // Plan do pendente (com preços)
      $qp = $pdo->prepare("
        SELECT id, name, ".($colM ?: 'NULL')." AS pm, ".($colY ?: 'NULL')." AS py, status
        FROM plans WHERE id=? LIMIT 1
      ");
      $qp->execute([$pendingSub['plan_id']]);
      $pendingPlan = $qp->fetch(PDO::FETCH_ASSOC) ?: ['id'=>$pendingSub['plan_id'],'name'=>$row['plan_name'] ?? $pendingSub['plan_id']];

      // Invoice pendente
      $raw = [];
      if (!empty($row['i_raw'])) {
        try { $raw = json_decode($row['i_raw'], true) ?: []; } catch (\Throwable $e) {}
      }
      $pendingHasInvoice = true; // a query já filtra por pending/overdue
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
      'user'                => ['id' => $userId, 'name' => $u['name'] ?? null, 'email' => $u['email'] ?? null],
      // Back-compat
      'subscription'        => $activeSub,
      'plan'                => $activePlan,
      // Nomes novos e explícitos
      'activeSubscription'  => $activeSub,
      'activePlan'          => $activePlan,
      'pendingSubscription' => $pendingSub,
      'pendingPlan'         => $pendingPlan,
      'pendingHasInvoice'   => $pendingHasInvoice,
      'pendingInvoice'      => $pendingInvoice,
    ]);
  }
}
