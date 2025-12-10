<?php
// app/controllers/Api/PartnerAdsController.php
declare(strict_types=1);

namespace Api;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';
require_once __DIR__ . '/../../services/Asaas.php';

final class PartnerAdsController
{
  // ======== PLANS (parceiro enxerga só ativos) ========
  // GET /?r=api/partner/ads/plans
  public function listPlans(): void
  {
    $rows = \DB::pdo()
      ->query("SELECT id,name,description,view_quota,price,status,sort_order
               FROM ad_plans
               WHERE status='active'
               ORDER BY sort_order, id")
      ->fetchAll(\PDO::FETCH_ASSOC);

    \Json::ok(['ok' => true, 'data' => $rows]);
  }

  // ======== CAMPAIGNS ========
  // POST /?r=api/partner/ads/campaign/save
  public function saveCampaign(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $pdo = \DB::pdo();
    $pdo->beginTransaction();

    try {
      // garante partner_id (cria se não existir)
      $stp = $pdo->prepare("SELECT id FROM partners WHERE user_id=? LIMIT 1");
      $stp->execute([$me['id']]);
      $p = $stp->fetch(\PDO::FETCH_ASSOC);
      if (!$p) {
        $ins = $pdo->prepare("INSERT INTO partners (user_id, business_name, status) VALUES (?,?, 'pending')");
        $ins->execute([$me['id'], $me['name'] ?? 'Parceiro']);
        $p = ['id' => (int)$pdo->lastInsertId()];
      }

      $id         = (int)($_POST['id'] ?? 0);
      $title      = trim($_POST['title'] ?? '');
      $target_url = trim($_POST['target_url'] ?? '');

      $img_sky_1    = trim($_POST['img_sky_1'] ?? '');
      $img_sky_2    = trim($_POST['img_sky_2'] ?? '');
      $img_top_468  = trim($_POST['img_top_468'] ?? '');
      $img_square_1 = trim($_POST['img_square_1'] ?? '');
      $img_square_2 = trim($_POST['img_square_2'] ?? '');

      if ($title === '') { \Json::fail('Título é obrigatório'); }

      if ($id > 0) {
        // update do dono
        $chk = $pdo->prepare("SELECT id FROM partner_ad_campaigns WHERE id=? AND user_id=?");
        $chk->execute([$id, $me['id']]);
        if (!$chk->fetch()) { \Json::fail('Campanha não encontrada', 404); }

        $sql = "UPDATE partner_ad_campaigns
                SET title=?, target_url=?, img_sky_1=?, img_sky_2=?,
                    img_top_468=?, img_square_1=?, img_square_2=?
                WHERE id=?";
        $pdo->prepare($sql)->execute([
          $title, $target_url, $img_sky_1, $img_sky_2, $img_top_468, $img_square_1, $img_square_2, $id
        ]);

        $pdo->commit();
        \Json::ok(['ok'=>true, 'data'=>['id'=>$id]]);
      } else {
        // create — nasce inativa
        $sql = "INSERT INTO partner_ad_campaigns
                (partner_id, user_id, title, target_url,
                 img_sky_1, img_sky_2, img_top_468, img_square_1, img_square_2, status)
                VALUES (?,?,?,?,?,?,?,?,?, 'inactive')";
        $pdo->prepare($sql)->execute([
          $p['id'], $me['id'], $title, $target_url,
          $img_sky_1, $img_sky_2, $img_top_468, $img_square_1, $img_square_2
        ]);

        $cid = (int)$pdo->lastInsertId();
        $pdo->commit();
        \Json::ok(['ok'=>true, 'data'=>['id'=>$cid, 'status'=>'inactive']]);
      }
    } catch (\Throwable $e) {
      $pdo->rollBack();
      \Json::fail($e->getMessage(), 500);
    }
  }

  // GET /?r=api/partner/ads/campaigns
  public function myCampaigns(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $sql = "SELECT id, title, target_url,
                   img_sky_1, img_sky_2, img_top_468, img_square_1, img_square_2,
                   status, created_at
            FROM partner_ad_campaigns
            WHERE user_id=?
            ORDER BY created_at DESC";
    $st  = \DB::pdo()->prepare($sql);
    $st->execute([$me['id']]);
    $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

    \Json::ok(['ok'=>true, 'data'=>$rows]);
  }

  // ======== ORDER (compra do plano) ========
  // POST /?r=api/partner/ads/order
  public function createOrder(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $planId     = (int)($_POST['plan_id'] ?? 0);
    $campaignId = (int)($_POST['campaign_id'] ?? 0);
    if ($planId <= 0 || $campaignId <= 0) {
      \Json::fail('Plano e campanha são obrigatórios');
    }

    $pdo = \DB::pdo();

    // 1) Plano ativo
    $stPlan = $pdo->prepare("SELECT id, view_quota, price FROM ad_plans WHERE id=? AND status='active'");
    $stPlan->execute([$planId]);
    $plan = $stPlan->fetch(\PDO::FETCH_ASSOC);
    if (!$plan) { \Json::fail('Plano inválido ou inativo'); }

    // 2) Campanha do usuário
    $stCamp = $pdo->prepare("SELECT c.id, c.partner_id, c.user_id, c.title
                             FROM partner_ad_campaigns c
                             WHERE c.id=? AND c.user_id=?");
    $stCamp->execute([$campaignId, $me['id']]);
    $camp = $stCamp->fetch(\PDO::FETCH_ASSOC);
    if (!$camp) { \Json::fail('Campanha não encontrada'); }

    // 3) Garante partner_id
    $partnerId = (int)($camp['partner_id'] ?? 0);
    if ($partnerId <= 0) {
      $stP = $pdo->prepare("SELECT id FROM partners WHERE user_id=? LIMIT 1");
      $stP->execute([$me['id']]);
      $p = $stP->fetch(\PDO::FETCH_ASSOC);

      if (!$p) {
        $ins = $pdo->prepare("INSERT INTO partners (user_id, business_name, status) VALUES (?,?, 'pending')");
        $ins->execute([$me['id'], $me['name'] ?? 'Parceiro']);
        $partnerId = (int)$pdo->lastInsertId();
      } else {
        $partnerId = (int)$p['id'];
      }

      $pdo->prepare("UPDATE partner_ad_campaigns SET partner_id=? WHERE id=?")
          ->execute([$partnerId, $campaignId]);
    }

    // 4) Detecta se a tabela tem campaign_id
    $hasCampaignCol = (bool)$pdo->query(
      "SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME   = 'partner_ad_orders'
         AND COLUMN_NAME  = 'campaign_id'"
    )->fetchColumn();

    // 5) Cria pedido pendente
    if ($hasCampaignCol) {
      $sql = "INSERT INTO partner_ad_orders
              (partner_id, user_id, plan_id, campaign_id, title,
               banner_image, target_url, placements, specialties,
               status, quota_total, quota_used, amount)
              VALUES (?,?,?,?,?, NULL, NULL, '[]', '[]',
                      'pending_payment', ?, 0, ?)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        $partnerId,
        $me['id'],
        $plan['id'],
        $campaignId,
        (string)($camp['title'] ?? ''),
        (int)$plan['view_quota'],
        (float)$plan['price'],
      ]);
    } else {
      $sql = "INSERT INTO partner_ad_orders
              (partner_id, user_id, plan_id, title,
               banner_image, target_url, placements, specialties,
               status, quota_total, quota_used, amount)
              VALUES (?,?,?,?,
                      NULL, NULL, '[]', '[]',
                      'pending_payment', ?, 0, ?)";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        $partnerId,
        $me['id'],
        $plan['id'],
        (string)($camp['title'] ?? ''),
        (int)$plan['view_quota'],
        (float)$plan['price'],
      ]);
    }

    \Json::ok([
      'ok'   => true,
      'data' => ['order_id' => $pdo->lastInsertId(), 'status' => 'pending_payment']
    ]);
  }

  // GET /?r=api/partner/ads/my
  public function myOrders(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $pdo = \DB::pdo();

    // Verifica se partner_ad_orders.campaign_id existe para montar SQL correto
    $hasCampaignCol = (bool)$pdo->query(
      "SELECT COUNT(*) FROM information_schema.COLUMNS
       WHERE TABLE_SCHEMA = DATABASE()
         AND TABLE_NAME   = 'partner_ad_orders'
         AND COLUMN_NAME  = 'campaign_id'"
    )->fetchColumn();

    if ($hasCampaignCol) {
      $sql = "SELECT o.*, p.name AS plan_name,
                     c.title AS campaign_title, c.status AS campaign_status
              FROM partner_ad_orders o
              JOIN ad_plans p ON p.id=o.plan_id
              LEFT JOIN partner_ad_campaigns c ON c.id=o.campaign_id
              WHERE o.user_id=?
              ORDER BY o.created_at DESC";
    } else {
      $sql = "SELECT o.*, p.name AS plan_name,
                     NULL AS campaign_title, NULL AS campaign_status
              FROM partner_ad_orders o
              JOIN ad_plans p ON p.id=o.plan_id
              WHERE o.user_id=?
              ORDER BY o.created_at DESC";
    }

    $st  = $pdo->prepare($sql);
    $st->execute([$me['id']]);
    $rows = $st->fetchAll(\PDO::FETCH_ASSOC);

    \Json::ok(['ok' => true, 'data' => $rows]);
  }

  // ======== UPLOAD (retro-compatível) ========
  // POST /?r=api/partner/ads/upload
  public function uploadBanner(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $pdo        = \DB::pdo();
    $orderId    = (int)($_POST['order_id'] ?? 0);
    $campaignId = (int)($_POST['campaign_id'] ?? 0);
    $slot       = trim($_POST['slot'] ?? ''); // img_sky_1|img_sky_2|img_top_468|img_square_1|img_square_2
    $bannerUrl  = trim($_POST['banner_image'] ?? '');

    if ($bannerUrl === '') {
      \Json::fail('URL do banner é obrigatória');
    }

    if ($campaignId > 0 && $slot !== '') {
      $allowed = ['img_sky_1','img_sky_2','img_top_468','img_square_1','img_square_2'];
      if (!in_array($slot, $allowed, true)) {
        \Json::fail('Slot inválido');
      }

      $chk = $pdo->prepare("SELECT id FROM partner_ad_campaigns WHERE id=? AND user_id=?");
      $chk->execute([$campaignId, $me['id']]);
      if (!$chk->fetch()) {
        \Json::fail('Campanha não encontrada', 404);
      }

      $sql = "UPDATE partner_ad_campaigns SET {$slot}=? WHERE id=?";
      $st  = $pdo->prepare($sql);
      $st->execute([$bannerUrl, $campaignId]);

      \Json::ok(['ok'=>true, 'data'=>['campaign_id'=>$campaignId, 'slot'=>$slot, 'url'=>$bannerUrl]]);
    } else {
      if ($orderId <= 0) {
        \Json::fail('Pedido inválido');
      }
      $st = $pdo->prepare("UPDATE partner_ad_orders SET banner_image=? WHERE id=? AND user_id=?");
      $st->execute([$bannerUrl, $orderId, $me['id']]);
      \Json::ok(['ok'=>true, 'data'=>['order_id'=>$orderId, 'banner_image'=>$bannerUrl]]);
    }
  }

  // ======== PAGAMENTO via ASAAS (one-time) ========
  // POST /?r=api/partner/ads/pay
  public function pay(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $orderId = (int)($_POST['order_id'] ?? 0);
    if ($orderId <= 0) \Json::fail('Pedido inválido');

    $pdo = \DB::pdo();

    // carrega o pedido
    $st = $pdo->prepare("SELECT o.*, p.name AS plan_name
                         FROM partner_ad_orders o
                         JOIN ad_plans p ON p.id=o.plan_id
                         WHERE o.id=? AND o.user_id=?");
    $st->execute([$orderId, $me['id']]);
    $o = $st->fetch(\PDO::FETCH_ASSOC);
    if (!$o) \Json::fail('Pedido não encontrado', 404);

    if (in_array(($o['status'] ?? ''), ['active','exhausted'], true)) {
      \Json::fail('Pedido já processado');
    }

    $amount = (float)($o['amount'] ?? 0.0);
    if ($amount <= 0) {
      \Json::fail('Valor do pedido inválido');
    }

    // dados do cliente
    $u = $pdo->prepare("SELECT name,email,document,phone FROM users WHERE id=? LIMIT 1");
    $u->execute([$me['id']]);
    $usr = $u->fetch(\PDO::FETCH_ASSOC) ?: [];
    $name   = $usr['name'] ?? ($me['name'] ?? 'Cliente');
    $email  = $usr['email'] ?? null;
    $doc    = preg_replace('/\D+/', '', (string)($usr['document'] ?? ''));
    $mobile = preg_replace('/\D+/', '', (string)($usr['phone'] ?? ''));

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      \Json::fail('E-mail do cliente inválido', 422);
    }

    // URLs de retorno (só se for HTTPS sem porta)
    [$successUrl, $cancelUrl] = $this->buildReturnUrls();

    $payload = [
      'billingTypes' => ['CREDIT_CARD','BOLETO'],
      'chargeTypes'  => ['ONE_TIME'],
      'customerData' => [
        'name'        => $name,
        'email'       => $email,
        'cpfCnpj'     => ($doc ?: null),
        'mobilePhone' => ($mobile ?: null),
      ],
      'payment' => [
        'value'       => $amount,
        'description' => "Publicidade {$o['plan_name']} • Pedido #{$orderId}",
      ],
      'externalReference' => 'ad|'.$orderId.'|'.$me['id'],
    ];
    if ($successUrl) $payload['successUrl'] = $successUrl;
    if ($cancelUrl)  $payload['cancelUrl']  = $cancelUrl;

    // DEBUG opcional: salvar payload (ativar definindo DEBUG_ASAAS=1 no env)
    if ((getenv('DEBUG_ASAAS') ?: '') === '1') {
      @file_put_contents(sys_get_temp_dir().'/asaas_checkout_payload.json', json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
    }

    try {
      $asaas = new \App\Services\Asaas();
      $res   = $asaas->createCheckout($payload);

      $id  = $res['id'] ?? null;
      $url = $res['url'] ?? ($res['redirectUrl'] ?? null);

      // salva checkout_id se a coluna existir
      $cols = $pdo->query("
        SELECT LOWER(COLUMN_NAME) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'partner_ad_orders'
      ")->fetchAll(\PDO::FETCH_COLUMN);
      if ($id && in_array('asaas_checkout_id', $cols, true)) {
        $pdo->prepare("UPDATE partner_ad_orders SET asaas_checkout_id=? WHERE id=?")->execute([$id, $orderId]);
      }

      if (!$url) {
        $base = getenv('ASAAS_BASE') ?: (defined('ASAAS_BASE') ? ASAAS_BASE : 'https://api-sandbox.asaas.com/v3');
        $isSb = stripos($base, 'sandbox') !== false;
        $url  = ($isSb ? 'https://sandbox.asaas.com/checkout?id='
                       : 'https://asaas.com/checkout?id=' ) . urlencode((string)$id);
      }

      \Json::ok(['ok'=>true, 'data'=>['checkout_id'=>$id, 'checkout_url'=>$url]]);
    } catch (\Throwable $e) {
      $msg = $e->getMessage();
      if (stripos($msg, 'URL rejected') !== false || stripos($msg, 'Port number') !== false) {
        \Json::fail('O Asaas rejeitou as URLs de retorno. Verifique APP_PUBLIC_URL (precisa ser HTTPS sem porta).', 502);
      }
      \Json::fail('Asaas HTTP error: '.$msg, 502);
    }
  }

  /**
   * Monta success/cancel apenas se APP_PUBLIC_URL for HTTPS e sem porta.
   * Evita o erro “Port number was not a decimal number between 0 and 65535”.
   */
  private function buildReturnUrls(): array
  {
    $raw = getenv('APP_PUBLIC_URL') ?: (defined('APP_PUBLIC_URL') ? APP_PUBLIC_URL : '');
    $raw = trim((string)$raw);
    if ($raw === '') return [null, null];

    $u = @parse_url($raw);
    if (!$u || !isset($u['scheme'], $u['host'])) return [null, null];
    if (strtolower($u['scheme']) !== 'https') return [null, null];
    if (isset($u['port'])) return [null, null]; // bloqueia porta explícita

    // reconstrói base sem porta
    $base = 'https://' . $u['host'] . (isset($u['path']) ? rtrim($u['path'], '/') : '');
    $success = $base . '/?r=partner/dashboard&paid=1';
    $cancel  = $base . '/?r=partner/dashboard&cancel=1';
    return [$success, $cancel];
  }

  // ======== PIXEL ========
  // GET /?r=api/ads/track&ad=ID
  public function track(): void
  {
    $adId = (int)($_GET['ad'] ?? 0);

    if ($adId > 0) {
      $pdo = \DB::pdo();
      $st  = $pdo->prepare("SELECT o.id, o.status, o.quota_total, o.quota_used,
                                   c.status AS camp_status, o.start_at, o.end_at
                            FROM partner_ad_orders o
                            LEFT JOIN partner_ad_campaigns c ON c.id=o.campaign_id
                            WHERE o.id=?");
      $st->execute([$adId]);

      if ($ad = $st->fetch(\PDO::FETCH_ASSOC)) {
        $nowOk = true;
        if (!empty($ad['start_at']) && time() < strtotime((string)$ad['start_at'])) $nowOk = false;
        if (!empty($ad['end_at'])   && time() > strtotime((string)$ad['end_at']))   $nowOk = false;

        if ($ad['status'] === 'active'
            && ($ad['camp_status'] ?? 'active') === 'active'
            && $nowOk
            && (int)$ad['quota_used'] < (int)$ad['quota_total']) {

          $ins = $pdo->prepare("INSERT INTO partner_ad_impressions (ad_order_id, ip, ua, referer)
                                VALUES (?,?,?,?)");
          $ins->execute([
            $ad['id'],
            $_SERVER['REMOTE_ADDR']      ?? null,
            $_SERVER['HTTP_USER_AGENT']  ?? null,
            $_SERVER['HTTP_REFERER']     ?? null
          ]);

          $pdo->prepare("UPDATE partner_ad_orders SET quota_used = quota_used + 1 WHERE id=?")
              ->execute([$ad['id']]);

          if (((int)$ad['quota_used'] + 1) >= (int)$ad['quota_total']) {
            $pdo->prepare("UPDATE partner_ad_orders SET status='exhausted' WHERE id=?")
                ->execute([$ad['id']]);
          }
        }
      }
    }

    // pixel 1x1
    header('Content-Type: image/gif');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
    exit;
  }
}
