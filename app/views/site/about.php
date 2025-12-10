<?php $PAGE_BARE = true; ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Quem Somos • Aviv+</title>
  <meta name="description" content="Conheça a missão, visão e valores do Aviv+, e a história por trás do nosso clube de benefícios." />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

  <style>
    /* ======= TOKENS / BASE ======= */
    :root{
      --green:#A8E6CF;
      --blue:#5DADE2;
      --blue-dark:#3B8FC6;
      --brand-deep:#006400;
      --white:#FFFFFF;
      --ink:#2C3E50;

      --ink-70: color-mix(in oklab, var(--ink), #0000 30%);
      --shadow: 0 8px 30px rgba(0,0,0,.08);
      --radius: 16px;
      --radius-lg: 24px;
      --container: 1120px;
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family:"Open Sans", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji",sans-serif;
      color:var(--ink); background:#fff; line-height:1.6; text-rendering:optimizeLegibility;
    }

    /* Fundo padrão das páginas públicas (igual ao login) + neutralizações */
    body.site-page{
      min-height:100dvh;
      margin:0;
      color:#6B7784;
      background:white;
      position:relative; isolation:isolate;
    }
    body.site-page::before{
      content:""; position:absolute; inset:0; z-index:-1;
      background:white;
    }

    h1,h2,h3,h4{
      font-family:"Poppins", ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
      line-height:1.2; margin:0 0 .4rem; color:var(--ink);
    }
    h1{font-weight:700;font-size:clamp(1.8rem,2.4rem + 1vw,3.2rem)}
    h2{font-weight:700;font-size:clamp(1.4rem,1.2rem + 1vw,2rem)}
    h3{font-weight:700;font-size:1.125rem}
    p{margin:.25rem 0 .75rem}
    .container{width:100%;max-width:var(--container);margin:0 auto;padding:0 20px}
    .center{text-align:center}
    .section{position:relative; z-index:1; padding:56px 0}
    .section__head{margin-bottom:18px}
    .section__title{margin-bottom:.25rem}
    .section__desc{color:var(--ink-70)}
    .grid{display:grid;gap:16px}
    .card{background:#fff;border:1px solid #eaf1f6;border-radius:16px;box-shadow:var(--shadow);padding:18px}
    :focus-visible{outline:3px solid color-mix(in oklab, var(--blue), #000 15%);outline-offset:2px}

    /* ======= BOTÕES ======= */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;
      gap:.5rem;padding:.875rem 1.1rem;border-radius:999px;
      text-decoration:none;font-weight:800;box-shadow: var(--shadow);
      transition:.22s transform ease, .22s background ease, .22s color ease; border:none;
    }
    .btn:hover{transform:translateY(-2px)}
    .btn--lg{padding:1rem 1.25rem;font-size:1.05rem}
    .btn--white{background:#fff;color:var(--ink)}
    .btn--ghost-white{background:transparent;border:2px solid #fff;color:#fff}
    .btn--blueGrad{background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff}

    /* ======= TOPBAR (fixa inferior) ======= */
    .topbar{
      position:fixed;left:0;right:0;bottom:0;z-index:100;
      background:linear-gradient(90deg, var(--blue), var(--blue-dark));
      color:#fff;font-size:.95rem;
      transform:translateY(0);transition:transform .22s ease, opacity .22s ease;
      box-shadow:0 -8px 20px rgba(0,0,0,.08);
    }
    .topbar.is-hidden{transform:translateY(110%);opacity:0}
    .topbar .container{display:flex;align-items:center;justify-content:space-between;padding:1rem 20px}
    .topbar .container span{font-weight:800}
    .topbar__cta{color:#fff;border:2px solid #ffffff66;border-radius:999px;padding:.5rem 1rem;text-decoration:none}
    .topbar__cta:hover{background:#ffffff14}

    /* ======= HEADER ======= */
    .header{
      position:sticky;top:0;z-index:90;
      backdrop-filter:saturate(140%) blur(8px);
      background:linear-gradient(180deg, #ffffffee, #ffffffcc 70%, #ffffff00);
      border-bottom:1px solid #e9eef2;
    }
    .header__wrap{display:flex;align-items:center;gap:16px;justify-content:space-between;height:72px}
    .brand img{display:block;height:auto;max-height:52px}
    .header__right{display:flex;align-items:center;gap:16px}
    .nav{display:flex;gap:20px;align-items:center}
    .nav a{color:var(--ink);text-decoration:none;font-weight:700}
    .nav a:hover{color:#0b6aa1}
    .header__actions{display:flex;align-items:center;gap:10px}
    .nav__login{white-space:nowrap}
    .nav-toggle{display:none;position:relative;width:42px;height:42px;border:0;background:#0000;border-radius:8px}
    .nav-toggle__bar, .nav-toggle__bar::before, .nav-toggle__bar::after{
      content:"";display:block;height:2px;background:var(--ink);width:22px;margin:auto;transition:.2s;position:relative
    }
    .nav-toggle__bar::before{position:absolute;inset:-6px 0 0 0}
    .nav-toggle__bar::after{position:absolute;inset: 6px 0 0 0}
    @media (max-width: 900px){
      .nav{position:fixed;inset:72px 0 auto 0;flex-direction:column;gap:16px;background:#fff;
          padding:20px;transform:translateY(-120%);transition:.28s;box-shadow:0 12px 24px rgba(0,0,0,.1)}
      .nav[data-open="true"]{transform:translateY(0)}
      .nav-toggle{display:inline-grid;place-items:center}
    }

    /* ======= HERO ======= */
    .hero{position:relative;isolation:isolate;display:grid}
    .hero__bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2}
    .hero__overlay{position:absolute;inset:0;z-index:-1;background:rgba(0,0,0,.5)}
    .hero__grid{
      position:relative;z-index:1;display:grid;grid-template-columns:1fr;
      min-height:64svh;align-items:center;padding:64px 0 0;color:#fff;
    }
    .hero__col--text{max-width:66.666%}
    @media (max-width: 960px){ .hero__col--text{max-width:100%} }
    .hero__title,.hero__subtitle{color:#fff;text-shadow:0 2px 18px rgba(0,0,0,.25)}
    .hero__wave{position:absolute;left:0;right:0;bottom:-1px;height:100px;z-index:1;pointer-events:none}
    .hero__wave svg{display:block;width:100%;height:100%}

    /* ======= CTA FINAL ======= */
    .cta--bar{position:relative; isolation:isolate; z-index:3; background:transparent; overflow:visible;}
    .cta--bar::before{
      content:""; position:absolute; left:0; right:0; top:150px; bottom:0;
      background:var(--brand-deep); z-index:0;
    }
    .cta__wave{position:absolute;left:0;right:0;top:0;height:270px;z-index:1;pointer-events:none}
    .cta__wave svg{display:block;width:100%;height:100%}
    .cta__wave path{fill:var(--brand-deep)}
    .cta__wrap{
      position:relative; z-index:3;
      display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; 
      color:#fff; margin-top:55px;
    }
    .section__title--light,.section__desc--light{color:#fff}

    /* ======= FOOTER ======= */
    .footer{position:relative; z-index:3; background:var(--brand-deep);color:#e8f3fb}
    .footer__grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:18px;padding:28px 0}
    .footer__brand img{display:block;margin-bottom:8px;height:6vh;max-height:56px;width:auto;filter:brightness(1.05)}
    .footer__links, .footer__contact{display:flex;flex-direction:column;gap:8px}
    .footer__links a, .footer__contact a{color:#e8f3fb;text-decoration:none}
    .footer__links a:hover, .footer__contact a:hover{color:#fff}
    .footer__bottom{border-top:1px solid rgba(255,255,255,.2);padding:12px 0;background:transparent}
    .footer__bottom small{opacity:.95}

    /* ======= MOBILE ajustes ======= */
    @media (max-width: 900px){
      .header__wrap{height:68px}
      .nav{inset:68px 0 auto 0}
      .section{padding:48px 0}
      .hero__grid{min-height:56svh;padding:48px 0 0}
    }
    @media (max-width: 560px){
      body{padding-bottom:88px}
      .section{padding:40px 0}
      .hero__grid{min-height:50svh;padding:36px 0 8px}
      .hero__col--text{max-width:100%;margin:0 16px}
      .footer__grid{grid-template-columns:1fr;gap:12px}
    }
  </style>
</head>
<body>

  <!-- Topbar -->
  <div class="topbar" id="topbar">
    <div class="container">
      <span>Clínico Geral 24h/7 • Especialidades seg–sex 09h–18h</span>
      <a class="topbar__cta" href="/?r=site/plans" aria-label="Assine Agora">Assine Agora</a>
    </div>
  </div>

  <!-- Header -->
  <header class="header" id="topo">
    <div class="container header__wrap">
      <a class="brand" href="/?r=site/home" aria-label="Página inicial">
        <img src="/img/logo.png" alt="Aviv+">
      </a>

      <div class="header__right">
        <nav class="nav" id="menu">
          <a href="/?r=site/sobre">Sobre nós</a>
          <a href="/?r=site/parceiros">Parceiros</a>
          <a href="/?r=site/planos">Planos</a>
          <a href="/?r=site/contato" aria-current="page">Contato</a>
        </nav>

        <div class="header__actions">
          <a class="btn btn--sm btn--blueGrad nav__login" href="/?r=auth/login">
            <svg class="icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M10 17l5-5-5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M15 12H3" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            Login
          </a>
          <button class="nav-toggle" aria-expanded="false" aria-controls="menu">
            <span class="nav-toggle__bar"></span>
            <span class="sr-only">Abrir menu</span>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section class="hero" aria-label="Cabeçalho da página">
    <img class="hero__bg" src="/img/hero.jpg" alt="Região dos Lagos - RJ" loading="eager">
    <div class="hero__overlay"></div>

    <div class="container hero__grid">
      <div class="hero__col hero__col--text">
        <h1 class="hero__title">Quem Somos</h1>
        <p class="hero__subtitle">Aviv+: renovando vidas com cuidado e proteção — saúde acessível, humana e transparente.</p>
      </div>
    </div>

    <div class="hero__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none" focusable="false">
        <path d="M0,64 C240,120 480,0 720,48 C960,96 1200,96 1440,48 L1440,120 L0,120 Z" fill="#ffffff"></path>
      </svg>
    </div>
  </section>

  <!-- Nossa história -->
  <section class="section" aria-label="Nossa história">
    <div class="container">
      <div class="card" style="display:grid;gap:18px;grid-template-columns:1.1fr 1fr;align-items:center;">
        <div>
          <h2>Nossa história</h2>
          <p>
            O Aviv+ nasceu com o propósito de aproximar pessoas dos cuidados que realmente importam.
            Percebemos que muitas famílias ainda enfrentam filas, burocracia e custos elevados para
            ter o básico. Decidimos transformar esse cenário com um clube simples, justo e humano.
          </p>
          <p>
            A Região dos Lagos é nosso berço e inspiração. Daqui, conectamos profissionais de saúde,
            parceiros e assistências para criar uma trilha de bem-estar que cabe no bolso e na rotina — 
            sem abrir mão da qualidade e do acolhimento.
          </p>
        </div>
        <img src="/img/hero.jpg" alt="Time Aviv+ na Região dos Lagos" style="width:100%;height:auto;border-radius:16px">
      </div>
    </div>
  </section>

  <!-- Missão, Visão e Valores -->
  <section class="section" aria-label="Missão, Visão e Valores">
    <div class="container">
      <div class="section__head">
        <h2 class="section__title">Missão, Visão e Valores</h2>
        <p class="section__desc">O que nos move e para onde vamos</p>
      </div>

      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px">
        <article class="card">
          <h3>Missão</h3>
          <p>
            “Oferecer uma saúde inclusiva, acessível e de qualidade para toda a população,
            por meio de um clube de benefícios com serviços essenciais a baixo custo, sem filas,
            burocracia ou preços abusivos.”
          </p>
        </article>

        <article class="card">
          <h3>Visão</h3>
          <p>
            “Ser referência nacional em bem-estar e proteção social, levando soluções
            inovadoras e humanas para todas as famílias brasileiras, independentemente de
            sua renda ou localização.”
          </p>
        </article>

        <article class="card">
          <h3>Valores</h3>
          <ul>
            <li><strong>Acessibilidade:</strong> todo mundo merece acesso digno à saúde.</li>
            <li><strong>Humanização:</strong> cuidado com empatia e respeito.</li>
            <li><strong>Impacto social:</strong> promovendo qualidade de vida e gerando empregos.</li>
            <li><strong>Transparência:</strong> clareza de preços, processos e serviços.</li>
            <li><strong>Inovação:</strong> buscamos sempre formas mais acessíveis e inteligentes de cuidar das pessoas.</li>
          </ul>
        </article>
      </div>
    </div>
  </section>

  <!-- CTA final -->
  <section class="section cta--bar" aria-label="Assine agora">
    <div class="cta__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 270" preserveAspectRatio="none" focusable="false">
        <path d="M0,270 L0,60 C 220,0 440,120 720,60 C 1000,0 1220,120 1440,60 L1440,270 Z"></path>
      </svg>
    </div>

    <div class="container cta__wrap">
      <div>
        <h2 class="section__title section__title--light">Pronto para começar?</h2>
        <p class="section__desc section__desc--light">Assine agora e receba seu acesso ao clube.</p>
      </div>
      <div class="cta__actions">
        <a class="btn btn--white btn--lg" href="/?r=site/plans">Assinar</a>
        <a class="btn btn--ghost-white btn--lg" href="/?r=site/contato">Fale no WhatsApp</a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer" aria-label="Rodapé">
    <div class="container footer__grid">
      <div class="footer__brand">
        <img src="/img/logowhite.png" alt="Aviv+">
        <p>Bem-estar e proteção para todas as famílias.</p>
      </div>

      <nav class="footer__links" aria-label="Links rápidos">
        <strong>Links</strong>
        <a href="/?r=site/about" aria-current="page">Sobre nós</a>
        <a href="/?r=site/parceiros">Parceiros</a>
        <a href="/?r=site/plans">Planos</a>
        <a href="/?r=site/contato">Contato</a>
      </nav>

      <div class="footer__contact">
        <strong>Atendimento</strong>
        <a href="/?r=site/contato" rel="noopener">WhatsApp</a>
        <a href="mailto:contato@avivmais.com">contato@avivmais.com</a>
        <a href="/?r=site/contato">Políticas &amp; FAQ</a>
      </div>
    </div>

    <div class="footer__bottom">
      <div class="container center">
        <small>© <span id="year"></span> Aviv+. Todos os direitos reservados.</small>
      </div>
    </div>
  </footer>

  <script>
    // Menu mobile
    (function(){
      var nav = document.getElementById('menu');
      var toggle = document.querySelector('.nav-toggle');
      if (!toggle || !nav) return;
      toggle.addEventListener('click', function(){
        var open = nav.getAttribute('data-open') === 'true';
        nav.setAttribute('data-open', open ? 'false' : 'true');
        toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
      });
    })();

    // Topbar: esconde ao rolar p/ baixo, mostra ao rolar p/ cima
    (function(){
      var bar = document.getElementById('topbar');
      if (!bar) return;
      var lastY = window.scrollY, ticking = false;
      function onScroll(){
        var y = window.scrollY;
        var goingDown = y > lastY + 6;
        var goingUp   = y < lastY - 6;
        if (goingDown && y > 60) bar.classList.add('is-hidden');
        else if (goingUp) bar.classList.remove('is-hidden');
        lastY = y; ticking = false;
      }
      window.addEventListener('scroll', function(){
        if (!ticking){ window.requestAnimationFrame(onScroll); ticking = true; }
      }, {passive:true});
    })();

    // Ano do rodapé
    (function(){
      var y = document.getElementById('year');
      if (y) y.textContent = String(new Date().getFullYear());
    })();
  </script>

  <!-- Script “à prova de bala” para ocultar topnav/adbar da base -->
  <script>
  (function () {
    // 1) Reforço por CSS
    var css = `
      header.topnav[data-topnav],
      .adbar-wrap#ad-header468-outer,
      #ad-header468 {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
      }
    `;
    var style = document.createElement('style');
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);

    // 2) Oculta e tira do foco/árvore de acessibilidade
    var selectors = [
      'header.topnav[data-topnav]',
      '.adbar-wrap#ad-header468-outer',
      '#ad-header468'
    ];
    function hideAll(root) {
      (root || document).querySelectorAll(selectors.join(',')).forEach(function (el) {
        el.setAttribute('hidden', '');
        el.setAttribute('aria-hidden', 'true');
        el.style.setProperty('display', 'none', 'important');
        el.style.setProperty('visibility', 'hidden', 'important');
        el.style.setProperty('pointer-events', 'none', 'important');
        el.querySelectorAll('a,button,input,select,textarea,[tabindex]').forEach(function (n) {
          n.tabIndex = -1;
          n.setAttribute('aria-hidden', 'true');
        });
      });
    }

    // 3) Rodar agora e no DOMContentLoaded
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () { hideAll(); });
    } else {
      hideAll();
    }

    // 4) Observar mutações para elementos reinseridos/alterados
    var mo = new MutationObserver(function (mutations) {
      var needs = false;
      for (var i = 0; i < mutations.length; i++) {
        var m = mutations[i];
        if (m.type === 'childList') {
          m.addedNodes.forEach(function (n) {
            if (n.nodeType === 1) { hideAll(n); }
          });
        } else if (m.type === 'attributes') {
          needs = true;
        }
      }
      if (needs) hideAll();
    });
    mo.observe(document.documentElement, {
      subtree: true,
      childList: true,
      attributes: true,
      attributeFilter: ['class', 'style']
    });
  })();
  </script>
</body>
</html>
