<?php
// app/controllers/Api/Admin/PartnerAdsController.php
declare(strict_types=1);

namespace Api\Admin;

final class PartnerAdsController
{
  /** Exige usuário admin logado */
  private function assertAdmin(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'admin') {
      \Json::fail('Unauthorized', 401);
    }
  }

  // ============================================================
  // 1) OFERTAS DE PARCEIROS (benefits pendentes)
  // ============================================================

  /** GET /?r=api/admin/partner/offers/pending */
  public function listOffersPending(): void
  {
    $this->assertAdmin();

    // Usa apenas as colunas existentes em `benefits`
    $sql = "SELECT id, title, partner, type, specialty, code, link, valid_until,
                   image_url, description, url, discount, terms, active, created_at
            FROM benefits
            WHERE active = 0
            ORDER BY created_at DESC";
    $rows = \DB::pdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

    \Json::ok(['ok' => true, 'data' => $rows]);
  }

  /** POST /?r=api/admin/partner/offers/moderate  (action: approve|reject) */
  public function moderateOffer(): void
  {
    $this->assertAdmin();

    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'approve';
    if ($id <= 0) {
      \Json::fail('ID inválido');
    }

    if ($action === 'approve') {
      \DB::pdo()->prepare("UPDATE benefits SET active=1 WHERE id=?")->execute([$id]);
      \Json::ok(['ok' => true, 'data' => ['id' => $id, 'status' => 'approved']]);
    } else {
      // Mantém inativo como "rejeitado"
      \Json::ok(['ok' => true, 'data' => ['id' => $id, 'status' => 'rejected_kept_inactive']]);
    }
  }

  // ============================================================
  // 2) PUBLICIDADE — ORDERS / PLANS / CAMPAIGNS
  // ============================================================

  /** GET /?r=api/admin/ads/orders */
  public function listOrders(): void
  {
    $this->assertAdmin();

    $sql = "SELECT o.*,
                   ap.name AS plan_name,
                   pr.business_name
            FROM partner_ad_orders o
            JOIN ad_plans   ap ON ap.id = o.plan_id
            JOIN partners   pr ON pr.id = o.partner_id
            ORDER BY o.created_at DESC";
    $rows = \DB::pdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

    \Json::ok(['ok' => true, 'data' => $rows]);
  }

  /** POST /?r=api/admin/ads/orders/confirm */
  public function confirmPayment(): void
  {
    $this->assertAdmin();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { \Json::fail('ID inválido'); }

    $pdo = \DB::pdo();
    $pdo->beginTransaction();
    try {
      // ativa o pedido
      $pdo->prepare("UPDATE partner_ad_orders SET status='active', paid_at=NOW() WHERE id=?")
          ->execute([$id]);

      // ativa a campanha vinculada (se houver)
      $campId = $pdo->prepare("SELECT campaign_id FROM partner_ad_orders WHERE id=?");
      $campId->execute([$id]);
      $cid = (int)($campId->fetchColumn() ?: 0);
      if ($cid > 0) {
        $pdo->prepare("UPDATE partner_ad_campaigns SET status='active' WHERE id=?")->execute([$cid]);
      }

      $pdo->commit();
      \Json::ok(['ok'=>true, 'data'=>['id'=>$id, 'status'=>'active']]);
    } catch (\Throwable $e) {
      $pdo->rollBack();
      \Json::fail($e->getMessage(), 500);
    }
  }

  /** GET /?r=api/admin/ads/plans/list  (lista todos, inclusive inativos) */
  public function listPlans(): void
  {
    $this->assertAdmin();
    $sql = "SELECT id,name,description,view_quota,price,status,sort_order,created_at
            FROM ad_plans
            ORDER BY sort_order, id";
    $rows = \DB::pdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    \Json::ok(['ok'=>true, 'data'=>$rows]);
  }

  /** POST /?r=api/admin/ads/plans/save  (create/update) */
  public function savePlan(): void
  {
    $this->assertAdmin();

    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $desc  = trim($_POST['description'] ?? '');
    $qtd   = (int)($_POST['view_quota'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stat  = $_POST['status'] ?? 'active';
    $ord   = (int)($_POST['sort_order'] ?? 0);

    if ($name === '' || $qtd <= 0) {
      \Json::fail('Nome e visualizações são obrigatórios');
    }

    $pdo = \DB::pdo();
    if ($id > 0) {
      $sql = "UPDATE ad_plans
              SET name=?, description=?, view_quota=?, price=?, status=?, sort_order=?
              WHERE id=?";
      $pdo->prepare($sql)->execute([$name, $desc, $qtd, $price, $stat, $ord, $id]);
    } else {
      $sql = "INSERT INTO ad_plans (name, description, view_quota, price, status, sort_order)
              VALUES (?,?,?,?,?,?)";
      $pdo->prepare($sql)->execute([$name, $desc, $qtd, $price, $stat, $ord]);
    }

    \Json::ok(['ok' => true]);
  }

  /** GET /?r=api/admin/ads/campaigns */
  public function listCampaigns(): void
  {
    $this->assertAdmin();
    $sql = "SELECT c.*, u.name AS user_name, p.business_name
            FROM partner_ad_campaigns c
            LEFT JOIN users u    ON u.id=c.user_id
            LEFT JOIN partners p ON p.id=c.partner_id
            ORDER BY c.created_at DESC";
    $rows = \DB::pdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    \Json::ok(['ok'=>true, 'data'=>$rows]);
  }
}
