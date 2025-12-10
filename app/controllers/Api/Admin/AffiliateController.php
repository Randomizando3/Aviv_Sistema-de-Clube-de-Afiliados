<?php
namespace Api\Admin;

require_once __DIR__ . '/../../../core/DB.php';
require_once __DIR__ . '/../../../core/Auth.php';
require_once __DIR__ . '/../../../core/Json.php';
require_once __DIR__ . '/../../../services/Affiliate.php';

use App\services\Affiliate;

final class AffiliateController
{
  /* ===== Utils ===== */
  private function requireAdmin(): void {
    \Auth::start();
    $u = \Auth::user();
    if (!$u || ($u['role'] ?? 'member') !== 'admin') {
      \Json::fail('forbidden', 403);
    }
  }

  // Checagem robusta que não depende de information_schema
  private function hasTable(string $name): bool {
    try {
      $pdo = \DB::pdo();
      $st  = $pdo->prepare("SHOW TABLES LIKE ?");
      $st->execute([$name]);
      return (bool)$st->fetchColumn();
    } catch (\Throwable $e) {
      return false;
    }
  }

  /* ===== Settings ===== */
  public function settingsGet(): void {
    $this->requireAdmin();
    // Lê em tempo real usando os getters públicos do serviço
    $data = [
      'percent'     => (float) Affiliate::percent(),
      'min_payout'  => (float) Affiliate::minPayout(),
      'cookie_days' => (int)   Affiliate::cookieDays(),
    ];
    \Json::ok(['data' => $data]);
  }

  public function settingsSave(): void {
    $this->requireAdmin();

    $percent     = (float)($_POST['percent'] ?? Affiliate::percent());
    $min_payout  = (float)($_POST['min_payout'] ?? Affiliate::minPayout());
    $cookie_days = (int)  ($_POST['cookie_days'] ?? Affiliate::cookieDays());

    try {
      // Usa os setters do serviço (salvam em settings ou arquivo, se necessário)
      Affiliate::setPercent($percent);
      Affiliate::setMinPayout($min_payout);
      Affiliate::setCookieDays($cookie_days);

      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      \Json::fail('db_error', 500);
    }
  }

  /* ===== Conversions/Commissions ===== */
  public function listCommissions(): void {
    $this->requireAdmin();
    $status = trim($_GET['status'] ?? 'all');
    $pdo = \DB::pdo();
    $items = [];

    try {
      if ($this->hasTable('affiliate_commissions')) {
        $where = ''; $params = [];
        if ($status && $status !== 'all') { $where = "WHERE c.status = ?"; $params[] = $status; }
        $sql = "
          SELECT c.id, c.status, c.created_at,
                 c.amount_gross AS amount, c.amount_commission AS commission,
                 a.name AS affiliate_name, a.email AS affiliate_email,
                 m.name AS member_name, m.email AS member_email
          FROM affiliate_commissions c
          LEFT JOIN users a ON a.id = c.affiliate_id
          LEFT JOIN users m ON m.id = c.member_id
          $where
          ORDER BY c.id DESC
          LIMIT 500
        ";
        $st = $pdo->prepare($sql); $st->execute($params);
        $items = $st->fetchAll(\PDO::FETCH_ASSOC);
      } elseif ($this->hasTable('affiliate_conversions')) {
        $where = ''; $params = [];
        if ($status && $status !== 'all') { $where = "AND c.status = ?"; $params[] = $status; }
        $sql = "
          SELECT c.id, c.status, c.created_at,
                 c.amount AS amount, c.commission AS commission,
                 a.name AS affiliate_name, a.email AS affiliate_email,
                 m.name AS member_name, m.email AS member_email
          FROM affiliate_conversions c
          JOIN affiliate_links l ON l.id = c.link_id
          LEFT JOIN users a ON a.id = l.user_id
          LEFT JOIN users m ON m.id = c.user_id
          WHERE 1=1
          $where
          ORDER BY c.id DESC
          LIMIT 500
        ";
        $st = $pdo->prepare($sql); $st->execute($params);
        $items = $st->fetchAll(\PDO::FETCH_ASSOC);
      } else {
        $items = [];
      }
      \Json::ok(['data' => ['items' => $items]]);
    } catch (\Throwable $e) {
      \Json::ok(['data' => ['items' => []], 'note' => 'commissions_query_error']); // evita 500
    }
  }

  /* Compat do botão “Aprovar/Marcar pago” da view */
  public function markPaid(): void {
    $this->requireAdmin();
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) \Json::fail('invalid_id', 422);

    $pdo = \DB::pdo();

    try {
      if ($this->hasTable('affiliate_commissions')) {
        $st = $pdo->prepare("SELECT status FROM affiliate_commissions WHERE id=?");
        $st->execute([$id]);
        $cur = $st->fetchColumn();
        if (!$cur) \Json::fail('not_found', 404);

        if (in_array($cur, ['pending','rejected'], true)) {
          $new = 'approved';
          $pdo->prepare("UPDATE affiliate_commissions SET status=?, approved_at=NOW() WHERE id=?")->execute([$new, $id]);
        } elseif ($cur === 'approved') {
          $new = 'paid';
          $pdo->prepare("UPDATE affiliate_commissions SET status=?, paid_at=NOW() WHERE id=?")->execute([$new, $id]);
        } else {
          $new = $cur;
        }
        \Json::ok(['ok'=>true, 'new_status'=>$new]); return;
      }

      if ($this->hasTable('affiliate_conversions')) {
        $st = $pdo->prepare("SELECT status FROM affiliate_conversions WHERE id=?");
        $st->execute([$id]);
        $cur = $st->fetchColumn();
        if (!$cur) \Json::fail('not_found', 404);

        if (in_array($cur, ['pending','rejected'], true)) {
          $new = 'approved';
          $pdo->prepare("UPDATE affiliate_conversions SET status=? WHERE id=?")->execute([$new, $id]);
        } else {
          $new = $cur;
        }
        \Json::ok(['ok'=>true, 'new_status'=>$new]); return;
      }

      \Json::ok(['ok'=>true, 'note'=>'no_commission_table']);
    } catch (\Throwable $e) {
      \Json::ok(['ok'=>true, 'note'=>'markpaid_error']); // evita 500
    }
  }

  /* ===== Payouts (saques) ===== */
  public function payoutsList(): void {
    $this->requireAdmin();

    if (!$this->hasTable('affiliate_payouts')) {
      \Json::ok(['data'=>['items'=>[]], 'note'=>'affiliate_payouts_missing']);
      return;
    }

    $status = trim($_GET['status'] ?? '');
    $pdo = \DB::pdo();
    $where = ''; $params = [];
    if ($status !== '') { $where = "WHERE p.status = ?"; $params[] = $status; }

    try {
      $sql = "
        SELECT
          p.id, p.affiliate_user_id, p.amount, p.status, p.pix_key, p.pix_type,
          p.created_at, p.paid_at,
          u.name  AS affiliate_name, u.email AS affiliate_email
        FROM affiliate_payouts p
        LEFT JOIN users u ON u.id = p.affiliate_user_id
        $where
        ORDER BY p.id DESC
        LIMIT 500
      ";
      $st = $pdo->prepare($sql);
      $st->execute($params);
      $rows = $st->fetchAll(\PDO::FETCH_ASSOC);
      \Json::ok(['data'=>['items'=>$rows]]);
    } catch (\Throwable $e) {
      \Json::ok(['data'=>['items'=>[]], 'note'=>'payouts_query_error']); // evita 500
    }
  }

  public function payoutsApprove(): void {
    $this->requireAdmin();
    if (!$this->hasTable('affiliate_payouts')) { \Json::fail('table_missing', 400); }
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) \Json::fail('invalid_id', 422);

    try {
      \DB::pdo()->prepare("UPDATE affiliate_payouts SET status='approved', approved_at=NOW() WHERE id=? AND status='requested'")
                ->execute([$id]);
      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      \Json::fail('db_error', 500);
    }
  }

  public function payoutsMarkPaid(): void {
    $this->requireAdmin();
    if (!$this->hasTable('affiliate_payouts')) { \Json::fail('table_missing', 400); }
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) \Json::fail('invalid_id', 422);

    try {
      \DB::pdo()->prepare("UPDATE affiliate_payouts SET status='paid', paid_at=NOW() WHERE id=? AND status IN ('approved','requested')")
                ->execute([$id]);
      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      \Json::fail('db_error', 500);
    }
  }

  public function payoutsReject(): void {
    $this->requireAdmin();
    if (!$this->hasTable('affiliate_payouts')) { \Json::fail('table_missing', 400); }
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) \Json::fail('invalid_id', 422);
    $reason = trim($_POST['reason'] ?? '');

    try {
      \DB::pdo()->prepare("UPDATE affiliate_payouts SET status='rejected', rejected_reason=?, rejected_at=NOW() WHERE id=? AND status IN ('requested','approved')")
                ->execute([$reason, $id]);
      \Json::ok(['ok'=>true]);
    } catch (\Throwable $e) {
      \Json::fail('db_error', 500);
    }
  }
}
