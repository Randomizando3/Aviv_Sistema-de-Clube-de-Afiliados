<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';

class InvoicesController
{
  /** ===== Helpers ===== */
  private function needUser(): array {
    \Auth::requireRole(['member','admin']);
    $u = \Auth::user();
    if (!$u) \Json::fail('unauthorized', 401);
    return $u;
  }
  private function currentDb(PDO $pdo): string {
    return (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
  }
  private function tableExists(PDO $pdo, string $db, string $t): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$db,$t]);
    return (int)$q->fetchColumn() > 0;
  }
  private function mapStatusInv(string $s): string {
    $v = strtolower(trim($s));
    if (in_array($v, ['paid','recebido','received','confirmed'])) return 'pago';
    if (in_array($v, ['overdue','vencido']))                      return 'atraso';
    if (in_array($v, ['canceled','cancelado','refunded']))         return 'cancelado';
    return 'pendente';
  }
  private function monthPtShort(int $m): string {
    $names = [1=>'jan',2=>'fev',3=>'mar',4=>'abr',5=>'mai',6=>'jun',7=>'jul',8=>'ago',9=>'set',10=>'out',11=>'nov',12=>'dez'];
    return $names[$m] ?? '';
  }
  private function periodLabel(?string $date, string $cycle): string {
    if (!$date) return '';
    $d = strtotime($date);
    if (!$d) return '';
    $m = (int)date('n',$d); $y = date('Y',$d);
    if ($cycle === 'yearly') return (string)$y;
    return $this->monthPtShort($m) . '/' . $y;
  }
  private function jsonGet($raw, string $key) {
    if (is_string($raw)) {
      $j = json_decode($raw,true);
    } else {
      $j = is_array($raw) ? $raw : [];
    }
    return $j[$key] ?? null;
  }

  /** ===== GET /?r=api/member/invoices ===== */
  public function index(): void {
    $u   = $this->needUser();
    $pdo = \DB::pdo();
    $db  = $this->currentDb($pdo);

    if (!$this->tableExists($pdo,$db,'subscriptions') || !$this->tableExists($pdo,$db,'invoices')) {
      \Json::ok(['invoices'=>[]]); return;
    }

    // 1) Todas as faturas do usuário (join para garantir ownership)
    $st = $pdo->prepare("
      SELECT i.id, i.subscription_id, i.asaas_invoice_id, i.value, i.status, i.due_date, i.paid_at, i.raw,
             s.cycle, s.plan_id
      FROM invoices i
      JOIN subscriptions s ON s.id = i.subscription_id
      WHERE s.user_id = ?
      ORDER BY i.id DESC
    ");
    $st->execute([$u['id']]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    $out = [];
    foreach ($rows as $r) {
      $statusNorm = $this->mapStatusInv($r['status'] ?? '');
      $raw        = $r['raw'] ?? null;

      // URLs úteis a partir do payload do Asaas
      $invoiceUrl    = $this->jsonGet($raw, 'invoiceUrl');
      $bankSlipUrl   = $this->jsonGet($raw, 'bankSlipUrl');
      $receiptUrl    = $this->jsonGet($raw, 'transactionReceiptUrl');
      $pixCopy       = $this->jsonGet($raw, 'pixQrCodeText');

      $out[] = [
        'id'           => (string)$r['id'],
        'period'       => $this->periodLabel($r['due_date'], $r['cycle'] ?? 'monthly'),
        'amount'       => (float)($r['value'] ?? 0),
        'status'       => $statusNorm,
        'due_date'     => $r['due_date'] ?? null,
        'paid_at'      => $r['paid_at']  ?? null,
        'receipt_url'  => $receiptUrl ?: $invoiceUrl,
        'boleto_url'   => $bankSlipUrl ?: $invoiceUrl,
        'pix_copy'     => $pixCopy,
        'checkout_url' => null,
      ];
    }

    // 2) Linha “Próxima” (agendada) quando não houver pendente/atraso
    $act = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? AND status='ativa' ORDER BY id DESC LIMIT 1");
    $act->execute([$u['id']]);
    if ($sub = $act->fetch(PDO::FETCH_ASSOC)) {
      // existe fatura aberta para essa assinatura?
      $chk = $pdo->prepare("SELECT COUNT(*) FROM invoices WHERE subscription_id=? AND status IN ('pending','overdue')");
      $chk->execute([$sub['id']]);
      $hasOpen = ((int)$chk->fetchColumn() > 0);

      if (!$hasOpen) {
        $due = $sub['renew_at'] ?? null;
        // fallback de data
        if (!$due) {
          $days = ($sub['cycle'] === 'yearly') ? 365 : 30;
          $due  = date('Y-m-d', strtotime(($sub['started_at'] ?? 'now') . " +{$days} days"));
        }
        $out[] = [
          'id'           => 'up_'.$sub['id'].'_'.($due ?: 'na'),
          'period'       => $this->periodLabel($due, $sub['cycle'] ?? 'monthly'),
          'amount'       => (float)($sub['amount'] ?? 0),
          'status'       => 'agendada',
          'due_date'     => $due,
          'paid_at'      => null,
          'receipt_url'  => null,
          'boleto_url'   => null,
          'pix_copy'     => null,
          'checkout_url' => null,
        ];
      }
    }

    \Json::ok(['invoices'=>$out]);
  }

  /** ===== POST /?r=api/member/invoices/pay =====
   *  Recebe { id } de uma fatura existente e devolve links para pagar/ver.
   */
  public function pay(): void {
    $u   = $this->needUser();
    $pdo = \DB::pdo();

    $id = trim($_POST['id'] ?? '');
    if ($id === '') \Json::fail('invalid_id', 422);

    // Linha "agendada" não tem cobrança ainda
    if (str_starts_with($id, 'up_')) {
      \Json::fail('no_pending_invoice', 422);
    }

    // Busca a fatura garantindo que pertence ao usuário
    $q = $pdo->prepare("
      SELECT i.raw, i.status
      FROM invoices i
      JOIN subscriptions s ON s.id = i.subscription_id
      WHERE i.id = ? AND s.user_id = ?
      LIMIT 1
    ");
    $q->execute([$id, $u['id']]);
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) \Json::fail('invoice_not_found', 404);

    $raw = $row['raw'] ?? null;
    $invoiceUrl  = $this->jsonGet($raw, 'invoiceUrl');
    $bankSlipUrl = $this->jsonGet($raw, 'bankSlipUrl');
    $receiptUrl  = $this->jsonGet($raw, 'transactionReceiptUrl');
    $pixCopy     = $this->jsonGet($raw, 'pixQrCodeText');

    \Json::ok([
      'checkout_url' => null,
      'boleto_url'   => $bankSlipUrl ?: $invoiceUrl,
      'pix_copy'     => $pixCopy,
      'receipt_url'  => $receiptUrl ?: $invoiceUrl
    ]);
  }
}
