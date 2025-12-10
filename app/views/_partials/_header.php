<?php if (!empty($PAGE_BARE)) return; ?>

<?php
// Header adaptativo: Admin / Afiliado / Parceiro / Membro / Visitante (deslogado)
$r = $_GET['r'] ?? '';
$active = fn($slug) => $r === $slug ? 'is-active' : '';

$isAdmin     = strncmp($r, 'admin/', 6) === 0;
$isAffiliate = strncmp($r, 'affiliate/', 10) === 0;

$me        = class_exists('Auth') ? \Auth::user() : null;
$isGuest   = !$me;
$role      = strtolower((string)($me['role'] ?? ''));
$isPartner = ($role === 'partner');

$brandHref = $isAdmin    ? '/?r=admin/dashboard'
          : ($isAffiliate ? '/?r=affiliate/dashboard'
          : ($isPartner   ? '/?r=partner/dashboard'
          : '/?r=site/home'));

// ——— Banner 468×60 (fallback estático, usado só se o pool não retornar nada) ———
$adHeader468Img  = $adHeader468Img  ?? '/img/ads/468x60-default.png';
$adHeader468Href = $adHeader468Href ?? '#';
$adHeader468Alt  = $adHeader468Alt  ?? 'Publicidade';
?>
<header class="topnav" role="navigation" aria-label="Navegação principal" data-topnav>
  <div class="container topnav-inner">
    <a class="brand" href="<?= htmlspecialchars($brandHref) ?>" aria-label="Aviv+">
      <img src="/img/logo.png" alt="Aviv+" width="120" height="50" />
    </a>

    <?php if ($isGuest): ?>
      <a class="reg-cta" href="/?r=auth/register" aria-label="Registrar">
        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-3.33 0-10 1.67-10 5v1a1 1 0 0 0 1 1h12.5a.5.5 0 0 0 .36-.15l4.99-4.99a.5.5 0 0 0 .15-.36V15a1 1 0 0 0-1-1Zm8.5 1.5h-2v-2a1 1 0 0 0-2 0v2h-2a1 1 0 0 0 0 2h2v2a1 1 0 0 0 2 0v-2h2a1 1 0 0 0 0-2Z" fill="currentColor"/>
        </svg>
        Registrar
      </a>
    <?php else: ?>
      <button class="nav-toggle" data-nav-toggle aria-controls="site-nav" aria-expanded="false" aria-label="Abrir menu">
        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
        </svg>
      </button>

      <nav id="site-nav" class="nav" data-nav aria-hidden="true">
        <?php if ($isAdmin): ?>
          <a class="<?= $active('admin/dashboard') ?>"   href="/?r=admin/dashboard">📊 Dashboard</a>
          <a class="<?= $active('admin/usuarios') ?>"    href="/?r=admin/usuarios">👥 Usuários</a>
          <a class="<?= $active('admin/planos') ?>"      href="/?r=admin/planos">📦 Planos</a>
          <a class="<?= $active('admin/beneficios') ?>"  href="/?r=admin/beneficios">🏷️ Benefícios</a>
          <a class="<?= $active('admin/assinaturas') ?>" href="/?r=admin/assinaturas">🧾 Faturas</a>
          <a class="<?= $active('admin/afiliados') ?>"   href="/?r=admin/afiliados">🤝 Afiliados</a>
          <a class="<?= $active('admin/ads') ?>"         href="/?r=admin/ads">🪧 Publicidade</a>
          <a href="/?r=auth/login" data-logout>⬅️ Sair</a>

        <?php elseif ($isAffiliate): ?>
          <a class="<?= $active('affiliate/dashboard') ?>" href="/?r=affiliate/dashboard">📊 Dashboard</a>
          <a class="<?= $active('affiliate/links') ?>"     href="/?r=affiliate/links">🔗 Meus links</a>
          <a class="<?= $active('affiliate/ganhos') ?>"    href="/?r=affiliate/ganhos">💸 Ganhos</a>
          <a href="/?r=auth/login" data-logout>⬅️ Sair</a>

        <?php elseif ($isPartner): ?>
          <a class="<?= $active('partner/dashboard') ?>" href="/?r=partner/dashboard">🪧 Anúncios</a>
          <a class="<?= $active('partner/cupons') ?>"    href="/?r=partner/cupons">🏷️ Meus cupons</a>
          <a href="/?r=auth/login" data-logout>⬅️ Sair</a>

        <?php else: ?>
          <a class="<?= $active('member/dashboard') ?>"   href="/?r=member/dashboard">📊 Dashboard</a>
          <a class="<?= $active('member/perfil') ?>"      href="/?r=member/perfil">👥 Perfil</a>
          <a class="<?= $active('member/planos') ?>"      href="/?r=member/planos">📦 Planos</a>
          <a class="<?= $active('member/beneficios') ?>"  href="/?r=member/beneficios">🏷️ Benefícios</a>
          <a class="<?= $active('member/faturas') ?>"     href="/?r=member/faturas">🧾 Faturas</a>
          <a href="/?r=auth/login" data-logout>⬅️ Sair</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</header>

<!-- === BANNER 468×60 — FORA do header: header + 1em + banner + 1em === -->
<div class="adbar-wrap" role="complementary" aria-label="Publicidade" id="ad-header468-outer">
  <div class="adbox-468" id="ad-header468">
    <div class="ad-skeleton" aria-hidden="true"></div>
    <?php if (!empty($adHeader468Img)): ?>
      <a href="<?= htmlspecialchars($adHeader468Href) ?>" target="_blank" rel="noopener" class="ad-fallback" style="display:none">
        <img src="<?= htmlspecialchars($adHeader468Img) ?>"
             alt="<?= htmlspecialchars($adHeader468Alt) ?>" width="468" height="60" loading="lazy">
      </a>
    <?php endif; ?>
  </div>
</div>

<style>
:root{
  --site-nav-gap: 8px;
  --brand-green: #16a34a;
  --brand-green-dark: #15803d;
  --ink: #0f172a;

  /* Barra do header (faixa full-width) — cor sólida */
  --bar-bg: #ffffff;                       /* ajuste se o fundo do site for outro */
}

/* container do header com mesma largura do site */
.topnav .container{
  width:min(92vw, 1120px);
  margin-inline:auto;
}

/* Header fixo (faixa full-width, sem blur) */
.topnav{
  --topnav-h: 64px;
  position: sticky;
  top: 0;
  z-index: 1100;
  padding: 16px 0 max(6px, env(safe-area-inset-top));
  background: var(--bar-bg) !important;    /* mantém a mesma cor por trás */
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
  box-shadow: none !important;             /* evita qualquer traço/sombra da faixa */
  border: 0 !important;
}

/* Pílula interna — SEM contorno e SEM sombra */
.topnav-inner{
  min-height: var(--topnav-h);
  display:flex;
  align-items:center;
  gap:10px;
  padding:6px 14px;
  background:#ffffff;
  border-radius:999px;

  /* removendo contorno/“bordinha” */
  border: 0 !important;
  outline: none !important;
  box-shadow:none !important;
}

.brand{
  display:inline-flex;
  align-items:center;
  gap:8px;
}

/* CTA visitante */
.reg-cta{
  margin-left:auto;
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding:8px 14px;
  border-radius:999px;
  text-decoration:none;
  font-weight:800;
  background:var(--brand-green);
  border:1px solid var(--brand-green-dark);
  color:#f9fafb;
  box-shadow: 0 8px 20px rgba(22,163,74,.35);
  font-size:.9rem;
}
.reg-cta svg{ color:#ecfdf5; }
.reg-cta:hover{ background:var(--brand-green-dark); }

/* Menu (desktop) */
header.topnav .nav{
  display:flex;
  align-items:center;
  margin-left:auto;
  gap:var(--site-nav-gap);
}
header.topnav .nav a{
  color:var(--ink);
  text-decoration:none;
  font-weight:700;
  line-height:1.1;
  padding:6px 10px;
  border-radius:999px;
  margin:0;
  font-size:.88rem;
}
header.topnav .nav a:hover{
  background:rgba(15,23,42,.04);
}
header.topnav .nav a.is-active{
  background:rgba(22,163,74,.08);
  color:var(--brand-green-dark);
  box-shadow:0 0 0 1px rgba(22,163,74,.28);
}

/* Botão de menu (mobile) */
.nav-toggle{
  display:none;
  margin-left:auto;
  background:transparent;
  border:0;
  color:var(--ink);
  padding:4px;
}

/* === Banner 468×60 FORA do header === */
.adbar-wrap{
  width:min(92vw, 1120px);
  margin: 1em auto;
  display:flex;
  align-items:center;
  justify-content:center;
}
.adbox-468{
  width:468px;
  max-width:100%;
  aspect-ratio: 468/60;
  border:1px solid #e2e8f0;
  border-radius:12px;
  overflow:hidden;
  background:#ffffff;
  position:relative;
  box-shadow: 0 10px 26px rgba(15,23,42,.08);
}
.adbox-468 a{ display:block; width:100%; height:100%; }
.adbox-468 img{ display:block; width:100%; height:100%; object-fit:cover; }

/* Skeleton shimmer */
.adbox-468 .ad-skeleton{
  position:absolute; inset:0;
  background:linear-gradient(90deg, rgba(148,163,184,.25), rgba(203,213,225,.65), rgba(148,163,184,.25));
  background-size:200% 100%;
  animation: adsh 1.2s linear infinite;
}
@keyframes adsh{ to{ background-position:-200% 0; } }

/* Mobile / tablet */
@media (max-width: 860px){
  :root{
    --site-nav-gap:6px;
    --nav-item-min:40px;
    --nav-item-py:9px;
    --nav-item-px:14px;
  }

  .topnav-inner{
    border-radius:18px;
    padding-inline:10px;
  }

  .nav-toggle{ display:inline-flex; }

  header.topnav .nav{
    position:fixed;
    left:0; right:0;
    top:var(--topnav-h);
    z-index:1090;
    height:calc(100vh - var(--topnav-h));
    overflow:auto;
    -webkit-overflow-scrolling:touch;
    overscroll-behavior:contain;
    display:grid;
    grid-auto-rows:min-content;
    gap:var(--site-nav-gap);
    padding:14px 14px calc(18px + env(safe-area-inset-bottom));
    background: var(--bar-bg);        /* também sólido no mobile */
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
    transform:translateY(110%);
    transition:transform .18s ease, opacity .16s ease, visibility .16s ease;
    opacity:0; visibility:hidden; pointer-events:none;
  }
  @supports (height:100dvh){
    header.topnav .nav{ height:calc(100dvh - var(--topnav-h)); }
  }
  header.topnav .nav[data-open]{ transform:translateY(0); opacity:1; visibility:visible; pointer-events:auto; }
  header.topnav .nav a{
    display:flex; align-items:center; justify-content:flex-start;
    min-height:var(--nav-item-min);
    padding:var(--nav-item-py) var(--nav-item-px);
    background:#ffffff;
    border:1px solid #e2e8f0;
    border-radius:12px;
    font-size:.95rem;
  }
  header.topnav .nav a.is-active{
    background:#ecfdf3; border-color:rgba(22,163,74,.5); color:var(--brand-green-dark);
  }

  .adbox-468{ width:100%; max-width:468px; }

  html.nav-open, body.nav-open{ overflow:hidden; }
}
</style>

<script>
(function(){
  function onReady(fn){ if(document.readyState!=='loading'){ fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }
  onReady(function(){
    var header = document.querySelector('[data-topnav]'); if(!header) return;
    var container = header.querySelector('.topnav-inner');
    var btn = header.querySelector('[data-nav-toggle]');
    var nav = header.querySelector('[data-nav]');
    var brandImg = header.querySelector('img');

    function setTopnavHeight(){
      if(!container) return;
      var h = Math.max(56, Math.round(container.getBoundingClientRect().height));
      document.documentElement.style.setProperty('--topnav-h', h + 'px');
    }
    setTopnavHeight();
    window.addEventListener('resize', setTopnavHeight, {passive:true});
    window.addEventListener('orientationchange', setTopnavHeight);
    if(brandImg && !brandImg.complete){ brandImg.addEventListener('load', setTopnavHeight); }

    if(btn && nav){
      function openNav(){
        setTopnavHeight();
        nav.setAttribute('data-open','');
        nav.setAttribute('aria-hidden','false');
        btn.setAttribute('aria-expanded','true');
        document.documentElement.classList.add('nav-open');
        document.body.classList.add('nav-open');
      }
      function closeNav(){
        nav.removeAttribute('data-open');
        nav.setAttribute('aria-hidden','true');
        btn.setAttribute('aria-expanded','false');
        document.documentElement.classList.remove('nav-open');
        document.body.classList.remove('nav-open');
      }
      btn.addEventListener('click', function(e){
        e.preventDefault();
        nav.hasAttribute('data-open') ? closeNav() : openNav();
      });
      nav.addEventListener('click', function(e){
        if(e.target.closest('a')) closeNav();
      });
      document.addEventListener('keydown', function(e){
        if(e.key==='Escape') closeNav();
      });
    }

    // logout real via API
    header.querySelectorAll('[data-logout]').forEach(function(a){
      a.addEventListener('click', function(ev){
        ev.preventDefault();
        fetch('/?r=api/auth/logout', {method:'POST'}).finally(function(){
          location.href='/?r=auth/login';
        });
      });
    });

    // ===== Banner 468×60 — fora do header: busca do pool público com fallback =====
    var adOuter = document.getElementById('ad-header468-outer');
    var adBox   = document.getElementById('ad-header468');
    if(adBox){
      fetch('/?r=api/partner/ads/public-pool&type=top_468&limit=1', { credentials:'same-origin' })
        .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
        .then(function(j){
          var it = (j && j.items && j.items[0]) ? j.items[0] : null;
          var sk = adBox.querySelector('.ad-skeleton'); if(sk) sk.remove();

          if(!it){
            var fb = adBox.querySelector('.ad-fallback');
            if(fb){ fb.style.display='block'; }
            else if(adOuter){ adOuter.style.display='none'; }
            return;
          }

          adBox.innerHTML = '';
          var a = document.createElement('a');
          a.href = it.target_url || '#';
          a.target = '_blank';
          a.rel = 'noopener';
          a.title = it.title || 'Publicidade';

          var img = new Image();
          img.src = it.img;
          img.alt = it.title || 'Anúncio';
          img.width = Number(it.w || 468);
          img.height = Number(it.h || 60);
          img.loading = 'lazy';
          a.appendChild(img);
          adBox.appendChild(a);

          if(it.pixel){
            var px = new Image();
            px.decoding = 'async';
            px.referrerPolicy='no-referrer-when-downgrade';
            px.src = it.pixel + '&ts=' + Date.now();
            px.width=1; px.height=1; px.alt='';
            px.style.position='absolute';
            px.style.inset='auto auto 0 0';
            px.style.opacity='0';
            adBox.appendChild(px);
          }
        })
        .catch(function(){
          var sk = adBox.querySelector('.ad-skeleton'); if(sk) sk.remove();
          var fb = adBox.querySelector('.ad-fallback');
          if(fb){ fb.style.display='block'; }
          else if(adOuter){ adOuter.style.display='none'; }
        });
    }
  });
})();
</script>
