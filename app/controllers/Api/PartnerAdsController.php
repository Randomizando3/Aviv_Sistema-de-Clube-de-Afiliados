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
  /* ------------ helpers ------------ */

  private function colExists(\PDO $pdo, string $table, string $col): bool {
    $q = $pdo->prepare("
      SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
    ");
    $q->execute([$table, $col]);
    return (bool)$q->fetchColumn();
  }

  /** Retorna APP_PUBLIC_URL (sem barra final), lendo de constante ou .env, sem referenciar diretamente a constante */
  private function appPublicUrl(): string {
    static $cached = null;
    if ($cached !== null) return $cached;

    $val = \defined('APP_PUBLIC_URL')
      ? (string)\constant('APP_PUBLIC_URL') // evita warning do Intelephense
      : (string)(getenv('APP_PUBLIC_URL') ?: '');

    $cached = rtrim(trim($val), '/');
    return $cached;
  }

  /** URLs de retorno (exigem https público) */
  private function getAppPublicUrls(): array {
    $pub = $this->appPublicUrl();
    if ($pub === '' || !preg_match('~^https://~i', $pub)) {
      return [null, null];
    }
    $success = $pub . '/?r=partner/dashboard&paid=1';
    $cancel  = $pub . '/?r=partner/dashboard&canceled=1';
    return [$success, $cancel];
  }

  /* ------------ PLANS ------------ */

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

  /* ------------ CAMPAIGNS ------------ */

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

  /* ------------ ORDER ------------ */

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
    $hasCampaignCol = $this->colExists($pdo, 'partner_ad_orders', 'campaign_id');

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
    $hasCampaignCol = $this->colExists($pdo, 'partner_ad_orders', 'campaign_id');

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

  /* ------------ UPLOAD (retro-compatível) ------------ */

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

  /* ------------ PAGAMENTO (ONE-TIME) ------------ */

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

    // cliente
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

    // Tipo de cobrança: default BOLETO; pode enviar via POST billing=PIX
    $billing = strtoupper((string)($_POST['billing'] ?? 'BOLETO'));
    if (!in_array($billing, ['BOLETO','PIX'], true)) $billing = 'BOLETO';

    // Para BOLETO o Asaas exige dueDate — padrão: +3 dias
    $dueInDays = max(1, (int)($_POST['due_in_days'] ?? 3));
    $dueDate   = ($billing === 'BOLETO') ? date('Y-m-d', strtotime("+{$dueInDays} days")) : null;

    // URLs de retorno (apenas para fallback de checkout)
    [$successUrl, $cancelUrl] = $this->getAppPublicUrls();

    $extRef = 'ad|' . $orderId . '|' . $me['id'];

    try {
      $asaas = new \App\Services\Asaas();

      // 1) Cria pagamento avulso (payments)
      $payload = [
        'customerData'      => [
          'name'        => $name,
          'email'       => $email,
          'cpfCnpj'     => ($doc ?: null),
          'mobilePhone' => ($mobile ?: null),
        ],
        'value'             => $amount,
        'description'       => "Publicidade {$o['plan_name']} • Pedido #{$orderId}",
        'billingType'       => $billing, // 'BOLETO' ou 'PIX'
        'externalReference' => $extRef,
      ];
      if ($dueDate) $payload['dueDate'] = $dueDate;

      $payment = $asaas->createOneTimePayment($payload);

      $id   = $payment['id'] ?? null;
      $open = $payment['bankSlipUrl']
           ?? ($payment['invoiceUrl'] ?? ($payment['transactionReceiptUrl'] ?? null));

      // salva asaas_payment_id se houver coluna
      if ($id && $this->colExists($pdo, 'partner_ad_orders', 'asaas_payment_id')) {
        $pdo->prepare("UPDATE partner_ad_orders SET asaas_payment_id=? WHERE id=?")->execute([$id, $orderId]);
      }

      // 2) Fallback: checkoutSession (apenas se não veio URL abrível)
      if (!$open && \method_exists($asaas, 'createCheckout')) {
        $payloadCheckout = [
          'billingTypes' => ['BOLETO','PIX','CREDIT_CARD'],
          'chargeTypes'  => ['ONE_TIME'],
          'customerData' => [
            'name'        => $name,
            'email'       => $email,
            'cpfCnpj'     => ($doc ?: null),
            'mobilePhone' => ($mobile ?: null),
          ],
          'payment' => [
            'value'             => $amount,
            'description'       => "Publicidade {$o['plan_name']} • Pedido #{$orderId}",
            'externalReference' => $extRef,
          ],
          'externalReference' => $extRef,
        ];
        if ($dueDate)   $payloadCheckout['payment']['dueDate'] = $dueDate;
        if ($successUrl) $payloadCheckout['successUrl'] = $successUrl;
        if ($cancelUrl)  $payloadCheckout['cancelUrl']  = $cancelUrl;

        $res2 = $asaas->createCheckout($payloadCheckout);
        $id2  = $res2['id'] ?? null;
        $open = $res2['url'] ?? ($res2['redirectUrl'] ?? ($res2['bankSlipUrl'] ?? ($res2['invoiceUrl'] ?? null)));

        if ($id2 && $this->colExists($pdo, 'partner_ad_orders', 'asaas_checkout_id')) {
          $pdo->prepare("UPDATE partner_ad_orders SET asaas_checkout_id=? WHERE id=?")->execute([$id2, $orderId]);
        }

        if (!$open) {
          $base = getenv('ASAAS_BASE') ?: 'https://api-sandbox.asaas.com/v3';
          $isSb = stripos($base, 'sandbox') !== false;
          $open = ($isSb ? 'https://sandbox.asaas.com/checkout?id=' : 'https://asaas.com/checkout?id=')
                  . urlencode((string)$id2);
        }
      }

      \Json::ok(['ok'=>true, 'data'=>[
        'payment_id' => $id,
        'openUrl'    => $open,
        'billing'    => $billing,
        'dueDate'    => $dueDate,
      ]]);

    } catch (\Throwable $e) {
      $msg = $e->getMessage();
      if (stripos($msg, 'URL rejected') !== false || stripos($msg, 'Port number') !== false) {
        \Json::fail('O Asaas rejeitou as URLs de retorno. Defina APP_PUBLIC_URL com um endereço HTTPS público (ex.: ngrok) e sem porta não padrão.', 502);
      }
      \Json::fail('Asaas HTTP error: '.$msg, 502);
    }
  }

  /* ------------ CONCILIAÇÃO (manual) ------------ */

  // POST /?r=api/partner/ads/reconcile
  public function reconcile(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $orderId = (int)($_POST['order_id'] ?? 0);
    if ($orderId <= 0) \Json::fail('Pedido inválido');

    $pdo = \DB::pdo();

    // carrega pedido
    $st = $pdo->prepare("SELECT * FROM partner_ad_orders WHERE id=? AND user_id=?");
    $st->execute([$orderId, $me['id']]);
    $o = $st->fetch(\PDO::FETCH_ASSOC);
    if (!$o) \Json::fail('Pedido não encontrado', 404);

    try {
      $asaas = new \App\Services\Asaas();
      $pay   = null;

      $hasPayCol = $this->colExists($pdo, 'partner_ad_orders', 'asaas_payment_id');
      $knownId   = $hasPayCol ? (string)($o['asaas_payment_id'] ?? '') : '';

      if ($knownId) {
        $pay = $asaas->getPaymentById($knownId);
      } else {
        $ext  = 'ad|' . $orderId . '|' . $me['id'];
        $list = $asaas->getPayments(['externalReference' => $ext, 'limit' => 1]);
        if (!empty($list['data'][0])) $pay = $list['data'][0];
      }

      if (!$pay) {
        \Json::ok(['ok'=>true, 'data'=>['status'=>$o['status'], 'message'=>'Pagamento não localizado ainda']]);
      }

      $status = strtoupper((string)($pay['status'] ?? ''));
      $paidOk = in_array($status, ['RECEIVED','CONFIRMED'], true);

      if ($hasPayCol && !empty($pay['id'])) {
        $pdo->prepare("UPDATE partner_ad_orders SET asaas_payment_id=? WHERE id=?")->execute([$pay['id'], $orderId]);
      }

      if ($paidOk) {
        $sql = "UPDATE partner_ad_orders SET status='active'";
        $args = [];
        if ($this->colExists($pdo, 'partner_ad_orders', 'paid_at')) {
          $sql .= ", paid_at=NOW()";
        }
        $sql .= " WHERE id=?";
        $args[] = $orderId;
        $pdo->prepare($sql)->execute($args);

        if ($this->colExists($pdo, 'partner_ad_orders', 'campaign_id')) {
          $cid = $pdo->query("SELECT campaign_id FROM partner_ad_orders WHERE id=".(int)$orderId)->fetchColumn();
          if ($cid) {
            $pdo->prepare("UPDATE partner_ad_campaigns SET status='active' WHERE id=?")->execute([(int)$cid]);
          }
        }

        \Json::ok(['ok'=>true, 'data'=>['status'=>'active']]);
      } else {
        $map = [
          'PENDING'   => 'pending_payment',
          'OVERDUE'   => 'overdue',
          'CANCELLED' => 'canceled',
          'REFUNDED'  => 'refunded',
        ];
        $loc = $map[$status] ?? $o['status'];
        $pdo->prepare("UPDATE partner_ad_orders SET status=? WHERE id=?")->execute([$loc, $orderId]);
        \Json::ok(['ok'=>true, 'data'=>['status'=>$loc]]);
      }
    } catch (\Throwable $e) {
      \Json::fail('Asaas HTTP error: '.$e->getMessage(), 502);
    }
  }

  /* ------------ POOL PÚBLICO (para widgets) ------------ */

  // GET /?r=api/partner/ads/public-pool
  public function publicPool(): void
  {
    $pdo   = \DB::pdo();
    $type  = strtolower(trim($_GET['type'] ?? ''));
    $limit = max(1, min(10, (int)($_GET['limit'] ?? 1)));

    // filtros opcionais
    $forceCamp  = isset($_GET['id'])    ? (int)$_GET['id']    : null; // campanha
    $forceOrder = isset($_GET['order']) ? (int)$_GET['order'] : null; // pedido

    // mapa de slots (tipo) -> colunas de imagem
    $map = [
      'sky'       => ['img_sky_1','img_sky_2'],
      'sky_1'     => ['img_sky_1'],
      'sky_2'     => ['img_sky_2'],
      'top_468'   => ['img_top_468'],
      'square'    => ['img_square_1','img_square_2'],
      'square_1'  => ['img_square_1'],
      'square_2'  => ['img_square_2'],
    ];
    $cols = $map[$type] ?? ['img_square_1','img_square_2','img_sky_1','img_sky_2','img_top_468'];

    // tamanhos por slot
    $sizeMap = [
      'img_sky_1'     => [168, 600],
      'img_sky_2'     => [168, 600],
      'img_top_468'   => [468, 60],
      'img_square_1'  => [250, 250],
      'img_square_2'  => [250, 250],
    ];

    // pelo menos uma imagem válida
    $condImgs = implode(' OR ', array_map(fn($c) => "c.$c IS NOT NULL AND c.$c<>''", $cols));

    // ORDERS ativas, com saldo + campanha ativa
    $sql = "SELECT 
              o.id AS order_id,
              c.id AS campaign_id,
              c.title, c.target_url,
              c.img_sky_1, c.img_sky_2, c.img_top_468, c.img_square_1, c.img_square_2
            FROM partner_ad_orders o
            JOIN partner_ad_campaigns c ON c.id = o.campaign_id
            WHERE o.status='active'
              AND c.status='active'
              AND (o.quota_used < o.quota_total)
              AND ($condImgs)";
    $args = [];
    if ($forceCamp)  { $sql .= " AND c.id=?"; $args[] = $forceCamp; }
    if ($forceOrder) { $sql .= " AND o.id=?"; $args[] = $forceOrder; }
    $sql .= " ORDER BY RAND() LIMIT 50";

    $st = $pdo->prepare($sql);
    $st->execute($args);
    $rows = $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];

    // monta itens por imagem, com tamanhos e pixel usando ad=<order_id>
    $items = [];
    foreach ($rows as $r) {
      foreach ($cols as $slotCol) {
        $img = (string)($r[$slotCol] ?? '');
        if ($img === '') continue;

        [$w, $h] = $sizeMap[$slotCol] ?? [250, 250];

        $items[] = [
          'order_id'    => (int)$r['order_id'],
          'campaign_id' => (int)$r['campaign_id'],
          'title'       => (string)($r['title'] ?? ''),
          'img'         => $img,
          'slot'        => $slotCol,
          'w'           => $w,
          'h'           => $h,
          'target_url'  => (string)($r['target_url'] ?? ''),
          'pixel'       => '/?r=api/ads/track&ad='.(int)$r['order_id'].'&slot='.$slotCol,
        ];
      }
    }

    if (count($items) > 1) shuffle($items);

    \Json::ok(['items' => array_slice($items, 0, $limit)]);
  }

  /* ------------ PIXEL ------------ */

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

    header('Content-Type: image/gif');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
    exit;
  }
}
