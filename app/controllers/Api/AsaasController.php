<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';
require_once __DIR__ . '/../../services/Asaas.php';
require_once __DIR__ . '/../../services/Membership.php';

class AsaasController
{
  private function ensureSchema(PDO $pdo): void
  {
    // Tabelas mínimas usadas aqui (se já existem, não altera)
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS invoices (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        subscription_id INT NULL,
        asaas_invoice_id VARCHAR(80) UNIQUE,
        value DECIMAL(10,2) NOT NULL DEFAULT 0,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        due_date DATE NULL,
        paid_at DATETIME NULL,
        raw JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY sub_idx (subscription_id),
        KEY status_idx (status)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
      CREATE TABLE IF NOT EXISTS webhooks_log (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        provider VARCHAR(40) NOT NULL,
        event VARCHAR(80) NOT NULL,
        signature TEXT NULL,
        payload JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
  }

  private function h(string $k): string
  {
    $kk = 'HTTP_' . strtoupper(str_replace('-', '_', $k));
    return (string)($_SERVER[$kk] ?? '');
  }

  private function normStatus(?string $asaasStatus): string
  {
    $s = strtoupper(trim((string)$asaasStatus));

    // Principais estados (Asaas)
    if (in_array($s, ['RECEIVED', 'CONFIRMED'], true)) return 'paid';
    if (in_array($s, ['PENDING'], true)) return 'pending';
    if (in_array($s, ['OVERDUE'], true)) return 'overdue';
    if (in_array($s, ['REFUNDED'], true)) return 'refunded';
    if (in_array($s, ['CANCELED', 'DELETED'], true)) return 'canceled';

    // fallback conservador
    return 'pending';
  }

  private function parseYmd(?string $s): ?string
  {
    $v = trim((string)$s);
    if ($v === '') return null;
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) return $v;
    return null;
  }

  /**
   * POST /?r=api/asaas/webhook  (Content-Type text/plain no dispatcher)
   * Espera JSON do Asaas.
   */
  public function webhook(): void
  {
    $pdo = \DB::pdo();
    $this->ensureSchema($pdo);

    $raw = file_get_contents('php://input');
    $sig = $this->h('asaas-signature') ?: $this->h('x-asaas-signature') ?: $this->h('signature');

    // Asaas geralmente manda: { event: "...", payment: {...} } (ou subscription)
    $payload = json_decode((string)$raw, true);
    if (!is_array($payload)) {
      http_response_code(200);
      echo "OK"; // não re-tenta eternamente
      return;
    }

    $event = (string)($payload['event'] ?? 'unknown');

    // Loga webhook
    try {
      $st = $pdo->prepare("INSERT INTO webhooks_log (provider, event, signature, payload) VALUES ('asaas', ?, ?, ?)");
      $st->execute([
        $event,
        $sig ?: null,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      ]);
    } catch (\Throwable $e) {
      // não derruba o webhook por falha de log
    }

    // Pagamento (assinatura)
    $payment = $payload['payment'] ?? null;
    if (!is_array($payment)) {
      // Alguns eventos são de subscription; por ora só reconhecemos pagamento.
      http_response_code(200);
      echo "OK";
      return;
    }

    $paymentId  = (string)($payment['id'] ?? '');
    $payStatus  = (string)($payment['status'] ?? '');
    $payValue   = (float)($payment['value'] ?? 0);
    $payDueDate = $this->parseYmd($payment['dueDate'] ?? null);

    // Assinatura do Asaas associada ao pagamento
    $asaasSubId = (string)(
      $payment['subscription'] ??
      $payment['subscriptionId'] ??
      ''
    );

    if ($paymentId === '' || $asaasSubId === '') {
      http_response_code(200);
      echo "OK";
      return;
    }

    // Localiza assinatura local
    $stSub = $pdo->prepare("
      SELECT id, user_id, plan_id, cycle, qty_users, amount
      FROM subscriptions
      WHERE asaas_subscription_id = ?
      ORDER BY id DESC
      LIMIT 1
    ");
    $stSub->execute([$asaasSubId]);
    $subLocal = $stSub->fetch(PDO::FETCH_ASSOC);

    // Mesmo que não exista localmente, ainda registramos invoice para auditoria
    $subscriptionLocalId = $subLocal ? (int)$subLocal['id'] : null;

    // Status local normalizado
    $localInvoiceStatus = $this->normStatus($payStatus);

    // Cobertura: pega nextDueDate da assinatura no Asaas (melhor que dueDate da cobrança atual)
    $coverageUntil = $payDueDate; // fallback
    try {
      $asaas = new \App\Services\Asaas();
      $asaasSub = $asaas->getSubscription($asaasSubId);
      $nd = $this->parseYmd($asaasSub['nextDueDate'] ?? null);
      if ($nd) $coverageUntil = $nd;
    } catch (\Throwable $e) {
      // mantém fallback
    }

    // paid_at: usa paymentDate se houver, senão NOW()
    $paidAt = null;
    $paymentDate = (string)($payment['paymentDate'] ?? '');
    if ($paymentDate !== '') {
      // Asaas costuma mandar YYYY-MM-DD; guardamos como datetime no início do dia
      $paidAt = preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDate) ? ($paymentDate . ' 00:00:00') : null;
    }

    // Upsert invoice
    try {
      $pdo->prepare("
        INSERT INTO invoices (subscription_id, asaas_invoice_id, value, status, due_date, paid_at, raw)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
          subscription_id = VALUES(subscription_id),
          value = VALUES(value),
          status = VALUES(status),
          due_date = VALUES(due_date),
          paid_at = COALESCE(VALUES(paid_at), paid_at),
          raw = VALUES(raw)
      ")->execute([
        $subscriptionLocalId,
        $paymentId,
        $payValue > 0 ? $payValue : 0,
        $localInvoiceStatus,
        $coverageUntil,
        $localInvoiceStatus === 'paid' ? ($paidAt ?: date('Y-m-d H:i:s')) : null,
        json_encode($payment, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
      ]);
    } catch (\Throwable $e) {
      // não força retry do Asaas
    }

    // Se foi pago/confirmado/recebido, promove assinatura local e usuário
    if ($localInvoiceStatus === 'paid' && $subLocal) {
      $userId = (int)$subLocal['user_id'];
      $planId = (string)$subLocal['plan_id'];

      try {
        $pdo->beginTransaction();

        // Ativa esta assinatura
        $pdo->prepare("
          UPDATE subscriptions
          SET status='ativa',
              renew_at=?,
              amount=CASE WHEN ? > 0 THEN ? ELSE amount END
          WHERE id=?
        ")->execute([
          $coverageUntil,
          $payValue,
          $payValue,
          (int)$subLocal['id'],
        ]);

        // Suspende outras (evita concorrência no overview/badges)
        $pdo->prepare("
          UPDATE subscriptions
          SET status='suspensa'
          WHERE user_id=? AND id<>? AND status<>'cancelada'
        ")->execute([$userId, (int)$subLocal['id']]);

        // Atualiza plano atual do usuário (isso alimenta carteirinha/overview)
        $pdo->prepare("UPDATE users SET current_plan_id=? WHERE id=?")
            ->execute([$planId, $userId]);

        $pdo->commit();
      } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
      }
    }

    http_response_code(200);
    echo "OK";
  }

  /**
   * GET/POST /?r=api/asaas/checkout-link
   * Retorna URL (boleto/invoice) para uma fatura já existente.
   * Aceita: invoice_id (local) OU asaas_invoice_id/payment_id (asaas).
   */
  public function checkoutLink(): void
  {
    \Auth::requireRole(['member','admin']);
    $pdo = \DB::pdo();
    $this->ensureSchema($pdo);

    $invoiceId = (int)($_REQUEST['invoice_id'] ?? 0);
    $asaasId   = trim((string)($_REQUEST['asaas_invoice_id'] ?? ($_REQUEST['payment_id'] ?? '')));

    // 1) se veio invoice_id local, tenta extrair do raw
    if ($invoiceId > 0) {
      $st = $pdo->prepare("SELECT asaas_invoice_id, raw FROM invoices WHERE id=? LIMIT 1");
      $st->execute([$invoiceId]);
      $row = $st->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        $raw = json_decode((string)($row['raw'] ?? ''), true);
        $url = null;
        if (is_array($raw)) {
          $url = $raw['bankSlipUrl'] ?? $raw['invoiceUrl'] ?? null;
        }
        if ($url) {
          \Json::ok(['ok'=>true, 'url'=>$url]);
          return;
        }
        if (!$asaasId) $asaasId = (string)($row['asaas_invoice_id'] ?? '');
      }
    }

    // 2) fallback: busca no Asaas pelo paymentId
    if ($asaasId === '') \Json::fail('missing_invoice', 422);

    try {
      $asaas = new \App\Services\Asaas();
      $p = $asaas->getPaymentById($asaasId);
      $url = $p['bankSlipUrl'] ?? $p['invoiceUrl'] ?? null;
      if (!$url) \Json::fail('no_payment_url', 404);
      \Json::ok(['ok'=>true, 'url'=>$url, 'payment'=>$asaasId]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }
}
