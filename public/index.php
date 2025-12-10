<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/core/DB.php';
require_once dirname(__DIR__) . '/app/core/Auth.php';
require_once dirname(__DIR__) . '/app/core/Json.php';

define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/app/views');

$__autoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($__autoload)) {
  require_once $__autoload;
}


// Inicia a sessão ANTES de qualquer saída
Auth::start();

/** ===========================
 *  Affiliate: capturar ?ref=
 *  =========================== */
require_once BASE_PATH . '/app/services/Affiliate.php'; // [AFFILIATE]
\App\services\Affiliate::captureRefFromQuery();         // [AFFILIATE]

// Rota solicitada
$route = $_GET['r'] ?? 'site/home';
$route = trim(str_replace(['..', '\\'], ['', '/'], $route), '/');

// Aliases amigáveis (PT/EN e curtos)
$aliases = [
  // curtos (sem "site/")
  'planos'     => 'site/plans',
  'plans'      => 'site/plans',
  'parceiros'  => 'site/parceiros',
  'partners'   => 'site/parceiros',
  'contato'    => 'site/contato',
  'contact'    => 'site/contato',
  'sobre'      => 'site/about',  // quando criar app/views/site/about.php
  'about'      => 'site/about',
  'termos'      => 'site/termos',

  // versões com "site/" (compat)
  'site/planos'    => 'site/plans',
  'site/partners'  => 'site/parceiros',
  'site/contact'   => 'site/contato',
  'site/sobre'     => 'site/about',
  'site/termos'     => 'site/termos',
];

if (isset($aliases[$route])) {
  $route = $aliases[$route];
}

/** =====================================================
 *  SAFETY REDIRECTS (se cair no dashboard errado, corrige)
 *  ===================================================== */
$__u = Auth::user();
if ($__u) {
  $role = strtolower(trim((string)($__u['role'] ?? 'member')));
  $role = in_array($role, ['member','partner','affiliate','admin'], true) ? $role : 'member';

  $dashByRole = [
    'admin'     => 'admin/dashboard',
    'partner'   => 'partner/dashboard',
    'affiliate' => 'affiliate/dashboard',
    'member'    => 'member/dashboard'
  ];

  if (preg_match('~^(admin|partner|affiliate|member)/dashboard$~', $route)) {
    $dest = $dashByRole[$role] ?? 'member/dashboard';
    if ($route !== $dest) {
      header('Location: /?r=' . $dest);
      exit;
    }
  }
}

/** ===========================
 *  API Dispatcher
 *  =========================== */
if (str_starts_with($route, 'api/')) {

  // Sempre JSON (exceto webhook e o pixel de impressão)
  error_reporting(E_ALL);
  ini_set('display_errors', '0');
  set_exception_handler(function (Throwable $e) { Json::fail($e->getMessage(), 500); });
  set_error_handler(function ($severity, $message, $file, $line) { Json::fail("$message in $file:$line", 500); return true; });

  if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Vary: Origin');
  } else {
    header('Access-Control-Allow-Origin: *');
  }
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, X-CSRF');
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

  // Definição de Content-Type por rota API
  $ct = 'application/json; charset=utf-8';
  if ($route === 'api/asaas/webhook') $ct = 'text/plain; charset=utf-8';
  if ($route === 'api/ads/track')     $ct = null; // pixel define o seu próprio (image/gif)
  if ($ct) header('Content-Type: ' . $ct);

  

  switch ($route) {
    // ===== AUTH
    case 'api/auth/register': require BASE_PATH . '/app/controllers/Api/AuthController.php'; (new Api\AuthController())->register(); break;
    case 'api/auth/login':    require BASE_PATH . '/app/controllers/Api/AuthController.php'; (new Api\AuthController())->login();    break;
    case 'api/auth/logout':   require BASE_PATH . '/app/controllers/Api/AuthController.php'; (new Api\AuthController())->logout();   break;

    // ===== ADMIN: subscriptions
    case 'api/admin/subscriptions/list':
      require BASE_PATH . '/app/controllers/Api/Admin/SubscriptionsController.php';
      (new Api\Admin\SubscriptionsController())->index();
      break;

    case 'api/admin/subscriptions/save':
      require BASE_PATH . '/app/controllers/Api/Admin/SubscriptionsController.php';
      (new Api\Admin\SubscriptionsController())->save();
      break;

    case 'api/admin/subscriptions/create':
      require BASE_PATH . '/app/controllers/Api/Admin/SubscriptionsController.php';
      (new Api\Admin\SubscriptionsController())->create();
      break;

    // ===== COUPONS
    case 'api/coupons/generate':
      require BASE_PATH . '/app/controllers/Api/CouponsController.php';
      (new Api\CouponsController())->generate();
      break;

    // ===== ASAAS Webhook / Checkout
    case 'api/asaas/webhook':
      require BASE_PATH . '/app/controllers/Api/AsaasController.php';
      (new Api\AsaasController())->webhook();
      break;

    case 'api/asaas/checkout-link':
      require BASE_PATH . '/app/controllers/Api/AsaasController.php';
      (new Api\AsaasController())->checkoutLink();
      break;

    // ===== MEMBER: overview / profile / invoices / plans
    case 'api/member/overview':
      require BASE_PATH . '/app/controllers/Api/MeController.php';
      (new Api\MeController())->overview();
      break;

    case 'api/member/profile':
      require BASE_PATH . '/app/controllers/Api/ProfileController.php';
      (new Api\ProfileController())->index();
      break;

    case 'api/member/profile/save':
      require BASE_PATH . '/app/controllers/Api/ProfileController.php';
      (new Api\ProfileController())->save();
      break;

    case 'api/member/invoices':
      require BASE_PATH . '/app/controllers/Api/InvoicesController.php';
      (new Api\InvoicesController())->index();
      break;

    case 'api/member/invoices/pay':
      require BASE_PATH . '/app/controllers/Api/InvoicesController.php';
      (new Api\InvoicesController())->pay();
      break;

    case 'api/plans/list':
      require BASE_PATH . '/app/controllers/Api/PlansController.php';
      (new Api\PlansController())->index();
      break;

    case 'api/benefits/list':
      require BASE_PATH . '/app/controllers/Api/BenefitsController.php';
      (new Api\BenefitsController())->index();
      break;

    case 'api/member/benefits/list':
      require BASE_PATH . '/app/controllers/Api/MemberBenefitsController.php';
      (new Api\MemberBenefitsController())->index();
      break;

    case 'api/coupons/mine':
      require BASE_PATH . '/app/controllers/Api/CouponsController.php';
      (new Api\CouponsController())->mine();
      break;

    case 'api/subscriptions/create':
      require BASE_PATH . '/app/controllers/Api/SubscriptionsController.php';
      (new Api\SubscriptionsController())->create();
      break;

    // ===== ADMIN: stats & users
    case 'api/admin/stats/overview': require BASE_PATH . '/app/controllers/Api/Admin/StatsController.php'; (new Api\Admin\StatsController())->overview(); break;
    case 'api/admin/users/list':     require BASE_PATH . '/app/controllers/Api/Admin/UsersController.php'; (new Api\Admin\UsersController())->index(); break;
    case 'api/admin/users/set-role': require BASE_PATH . '/app/controllers/Api/Admin/UsersController.php'; (new Api\Admin\UsersController())->setRole(); break;

    // ===== ADMIN: plans
    case 'api/admin/plans/list':     require BASE_PATH . '/app/controllers/Api/Admin/PlansController.php'; (new Api\Admin\PlansController())->index(); break;
    case 'api/admin/plans/save':     require BASE_PATH . '/app/controllers/Api/Admin/PlansController.php'; (new Api\Admin\PlansController())->save(); break;
    case 'api/admin/plans/delete':   require BASE_PATH . '/app/controllers/Api/Admin/PlansController.php'; (new Api\Admin\PlansController())->delete(); break;
    case 'api/admin/plans/reorder':  require BASE_PATH . '/app/controllers/Api/Admin/PlansController.php'; (new Api\Admin\PlansController())->reorder(); break;

    // ===== ADMIN: benefits
    case 'api/admin/benefits/list':   require BASE_PATH . '/app/controllers/Api/Admin/BenefitsController.php'; (new Api\Admin\BenefitsController())->index(); break;
    case 'api/admin/benefits/save':   require BASE_PATH . '/app/controllers/Api/Admin/BenefitsController.php'; (new Api\Admin\BenefitsController())->save(); break;
    case 'api/admin/benefits/delete': require BASE_PATH . '/app/controllers/Api/Admin/BenefitsController.php'; (new Api\Admin\BenefitsController())->delete(); break;
    case 'api/admin/benefits/upload': require BASE_PATH . '/app/controllers/Api/Admin/BenefitsController.php'; (new Api\Admin\BenefitsController())->upload(); break;

    // ===== AFFILIATE: settings públicos + overview
    case 'api/affiliate/settings':
      require BASE_PATH . '/app/controllers/Api/AffiliateController.php';
      (new Api\AffiliateController())->publicSettings();
      break;

    case 'api/affiliate/overview':
      require BASE_PATH . '/app/controllers/Api/AffiliateController.php';
      (new Api\AffiliateController())->overviewApi();
      break;

    // ===== ADMIN: affiliate (config/relatórios/payouts)
    case 'api/admin/affiliate/settings/get':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->settingsGet();
      break;

    case 'api/admin/affiliate/settings/save':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->settingsSave();
      break;

    case 'api/admin/affiliate/list':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->listCommissions();
      break;

    case 'api/admin/affiliate/mark-paid':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->markPaid();
      break;

    case 'api/affiliate/payout/mine':
      require BASE_PATH . '/app/controllers/Api/AffiliatePayoutsController.php';
      (new Api\AffiliatePayoutsController())->mine();
      break;

    case 'api/affiliate/payout/request':
      require BASE_PATH . '/app/controllers/Api/AffiliatePayoutsController.php';
      (new Api\AffiliatePayoutsController())->request();
      break;

    case 'api/admin/affiliate/payouts/list':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->payoutsList();
      break;

    case 'api/admin/affiliate/payouts/approve':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->payoutsApprove();
      break;

    case 'api/admin/affiliate/payouts/mark-paid':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->payoutsMarkPaid();
      break;

    case 'api/admin/affiliate/payouts/reject':
      require BASE_PATH . '/app/controllers/Api/Admin/AffiliateController.php';
      (new Api\Admin\AffiliateController())->payoutsReject();
      break;

    // ===== PARTNER: ofertas
    case 'api/partner/offer':
      require BASE_PATH . '/app/controllers/Api/PartnerController.php';
      (new Api\PartnerController())->createOffer();
      break;

    // ==== PARTNER: campanhas & anúncios ====
    case 'api/partner/ads/campaign/save':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->saveCampaign();
      break;

    case 'api/partner/ads/campaigns':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->myCampaigns();
      break;

    // ===== PARTNER: publicidade (planos/pedido/upload/listagem)
    case 'api/partner/ads/plans':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->listPlans();
      break;

    case 'api/partner/ads/order':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->createOrder();
      break;

    case 'api/partner/ads/upload':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->uploadBanner();
      break;

    case 'api/partner/ads/my':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->myOrders();
      break;

    // ==== PARTNER: pagar & conciliar
    case 'api/partner/ads/pay':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->pay();
      break;

    case 'api/partner/ads/reconcile':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->reconcile();
      break;

    // ===== PUBLIC: pixel de impressão (image/gif)
    case 'api/ads/track':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->track();
      break;

    // ===== ADMIN: moderação de ofertas e gestão de anúncios
    case 'api/admin/partner/offers/pending':
      require BASE_PATH . '/app/controllers/Api/Admin/PartnerAdsController.php';
      (new Api\Admin\PartnerAdsController())->listOffersPending();
      break;

    case 'api/admin/partner/offers/moderate':
      require BASE_PATH . '/app/controllers/Api/Admin/PartnerAdsController.php';
      (new Api\Admin\PartnerAdsController())->moderateOffer();
      break;

    case 'api/admin/ads/orders':
      require BASE_PATH . '/app/controllers/Api/Admin/PartnerAdsController.php';
      (new Api\Admin\PartnerAdsController())->listOrders();
      break;

    case 'api/admin/ads/orders/confirm':
      require BASE_PATH . '/app/controllers/Api/Admin/PartnerAdsController.php';
      (new Api\Admin\PartnerAdsController())->confirmPayment();
      break;

    case 'api/admin/ads/plans/save':
      require BASE_PATH . '/app/controllers/Api/Admin/PartnerAdsController.php';
      (new Api\Admin\PartnerAdsController())->savePlan();
      break;

    case 'api/admin/ads/plans/list':
      require BASE_PATH . '/app/controllers/Api/Admin/PartnerAdsController.php';
      (new Api\Admin\PartnerAdsController())->listPlans();
      break;

    case 'api/admin/ads/campaigns':
      require BASE_PATH . '/app/controllers/Api/Admin/PartnerAdsController.php';
      (new Api\Admin\PartnerAdsController())->listCampaigns();
      break;

    case 'api/ads/pool':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->publicPool();
      break;

    // ==== PARTNER: pool público de banners (para widgets nas páginas) ====
    case 'api/partner/ads/public-pool':
      require BASE_PATH . '/app/controllers/Api/PartnerAdsController.php';
      (new Api\PartnerAdsController())->publicPool();
      break;

          // ===== FORMS (parceiros / contato)
    case 'api/forms/partner':
      require BASE_PATH . '/app/controllers/Api/FormController.php';
      (new Api\FormController())->partner();
      break;

    case 'api/forms/contact':
      require BASE_PATH . '/app/controllers/Api/FormController.php';
      (new Api\FormController())->contact();
      break;



    default:
      Json::fail('not_found', 404);
  }
  exit;
}

/** ==========================================
 *  ROTAS NÃO-API: Guards leves
 *  ========================================== */

// Afiliado: exige login
if (str_starts_with($route, 'affiliate/')) {
  if (!Auth::user()) { header('Location: /?r=auth/login'); exit; }
}

// Parceiro: exige login + role partner
if (str_starts_with($route, 'partner/')) {
  $u = Auth::user();
  if (!$u) { header('Location: /?r=auth/login'); exit; }
  if (strtolower((string)($u['role'] ?? 'member')) !== 'partner') {
    http_response_code(403);
    $route = 'site/403';
  }
}

// Admin: exige login + papel admin
if (str_starts_with($route, 'admin/')) {
  $u = Auth::user();
  if (!$u) { header('Location: /?r=auth/login'); exit; }

  try {
    $pdo = DB::pdo();
    $st = $pdo->prepare('SELECT name,email,role FROM users WHERE id=? LIMIT 1');
    $st->execute([$u['id']]);
    if ($row = $st->fetch()) {
      $u['name']  = $row['name'];
      $u['email'] = $row['email'];
      $u['role']  = $row['role'] ?: 'member';
      Auth::login($u);
    }
  } catch (Throwable $e) {}

  if (($u['role'] ?? 'member') !== 'admin') {
    http_response_code(403);
    $route = 'site/403';
  }
}

/** ===========================
 *  Render de Views
 *  =========================== */

// Caminho físico da view
$viewFile = VIEW_PATH . '/' . $route . '.php';
if (!is_file($viewFile)) {
  http_response_code(404);
  $route = 'site/404';
  $viewFile = VIEW_PATH . '/site/404.php';
}

// Títulos padrão por rota
$titleMap = [
  'site/home'            => 'Início • Aviv+',
  'site/plans'           => 'Planos • Aviv+',
  'site/planos'          => 'Planos • Aviv+',
  'site/parceiros'       => 'Parceiros • Aviv+',
  'site/contato'         => 'Contato • Aviv+',
  'site/about'           => 'Sobre • Aviv+',
  'site/sobre'           => 'Sobre • Aviv+',
  'site/faq'             => 'FAQ • Aviv+',
  'site/403'             => 'Acesso negado • Aviv+',

  'auth/login'           => 'Login • Aviv+',
  'auth/register'        => 'Criar conta • Aviv+',
  'auth/forgot'          => 'Recuperar acesso • Aviv+',

  'member/dashboard'     => 'Área do associado • Aviv+',
  'member/perfil'        => 'Perfil • Aviv+',
  'member/beneficios'    => 'Benefícios • Aviv+',
  'member/faturas'       => 'Faturas • Aviv+',
  'member/planos'        => 'Meu plano • Aviv+',

  'affiliate/dashboard'  => 'Afiliados • Dashboard',
  'affiliate/links'      => 'Afiliados • Links',
  'affiliate/ganhos'     => 'Afiliados • Ganhos',

  'partner/dashboard'    => 'Parceiro • Aviv+',

  'admin/dashboard'      => 'Admin • Dashboard',
  'admin/planos'         => 'Admin • Planos',
  'admin/beneficios'     => 'Admin • Benefícios',
  'admin/usuarios'       => 'Admin • Usuários',
  'admin/assinaturas'    => 'Admin • Assinaturas',
  'admin/config'         => 'Admin • Config',
  'admin/afiliados'      => 'Admin • Afiliados',
  'admin/ads'            => 'Admin • Publicidade',
];

$page_title = $titleMap[$route] ?? 'Aviv+';

// Escolha de layout (auth usa layout próprio)
$layout = str_starts_with($route, 'auth/') ? 'auth' : 'site';

/**
 * SUPORTE A "PÁGINAS BARE"
 * Views que rendem o HTML completo por conta própria (inline),
 * sem header/footer globais. Liste as rotas aqui:
 */
$bareRoutes = [
  'site/home',
  'site/plans',
  'site/parceiros',
];

// Se for rota bare, apenas require a view e sai
if (in_array($route, $bareRoutes, true)) {
  // A view contém seu próprio <!doctype html> e <head> e <body>
  require $viewFile;
  exit;
}

// Layout AUTH (padrão do projeto)
require VIEW_PATH . '/_partials/_head.php';

if ($layout === 'auth'): ?>
  <body class="login-page">
    <?php require VIEW_PATH . '/' . $route . '.php'; ?>
  </body>
</html>
<?php exit; endif; ?>

<body class="site-page">
  <?php require VIEW_PATH . '/_partials/_header.php'; ?>
  <main id="app" class="page-wrap" aria-live="polite">
    <?php require VIEW_PATH . '/' . $route . '.php'; ?>
  </main>
  <?php require VIEW_PATH . '/_partials/_footer.php'; ?>
  <script>
    const btn = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-nav]');
    if (btn && nav) btn.addEventListener('click', () => nav.toggleAttribute('data-open'));
  </script>
</body>
</html>
