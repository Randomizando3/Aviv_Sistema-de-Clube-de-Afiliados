<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Planos • Aviv+</title>
  <meta name="description" content="Compare os planos Individual, Familiar e Empresarial e assine o que combina com você." />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

  <style>
    /* ======= TOKENS / BASE (mesmos da home) ======= */
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
    .mt-24{margin-top:24px}
    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
    :focus-visible{outline:3px solid color-mix(in oklab, var(--blue), #000 15%);outline-offset:2px}

    /* ======= BOTÕES ======= */
    .btn{
      display:inline-flex;align-items:center;justify-content:center;
      gap:.5rem;padding:.875rem 1.1rem;border-radius:999px;
      text-decoration:none;font-weight:800;box-shadow: var(--shadow);
      transition:.22s transform ease, .22s background ease, .22s color ease;
      border:none;
    }
    .btn:hover{transform:translateY(-2px)}
    .btn--blueGrad{background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff}
    .btn--lg{padding:1rem 1.25rem;font-size:1.05rem}
    .btn--sm{padding:.6rem .9rem;font-size:.95rem}
    .btn .icon{display:block}

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

    /* ======= SEÇÕES ======= */
    .section{position:relative; z-index:1; padding:56px 0}
    .section__head{margin-bottom:18px}
    .section__title{margin-bottom:.25rem}
    .section__desc{color:var(--ink-70)}
    .section__head--center{ text-align:center; max-width:860px; margin:0 auto 28px; }
    .plans-head{max-width:860px;margin:0 auto 6px}
    .section--alt{ background:#f8fbff; }

    /* ======= LISTA COM CHECK ======= */
    .ticklist{list-style:none;margin:12px 0 16px;padding:0;display:grid;gap:10px}
    .ticklist li{position:relative;padding-left:28px;font-weight:700}
    .ticklist li::before{content:"✓";position:absolute;left:0;top:0;font-weight:800;color:#2FB67F}

    /* ======= GRID CARDS DE PLANOS (específico) ======= */
    .plan-grid{
      display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
      gap:18px;align-items:stretch;
    }
    .plan-card{
      display:grid;
      grid-template-rows:auto 1fr auto;
      gap:12px;padding:20px;border-radius:var(--radius-lg);
      background:#fff;border:1px solid #e9eef2;box-shadow:var(--shadow);
      transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .plan-card:hover{transform:translateY(-4px);box-shadow:0 14px 30px rgba(0,0,0,.10)}
    .plan-card header{display:grid;gap:4px}
    .plan-card .price{font-weight:800;font-size:1.9rem;letter-spacing:-.02em}
    .plan-card .price small{font-size:.9rem;opacity:.8}
    .plan-card small.muted{color:var(--ink-70);font-weight:700}
    .plan-card .badge{
      display:inline-flex;align-items:center;gap:8px;margin-bottom:4px;
      background:linear-gradient(90deg, var(--blue), var(--blue-dark));
      color:#fff;font-weight:800;border-radius:999px;padding:.35rem .7rem;font-size:.78rem
    }
    .plan-card--featured{
      border-width:2px;border-color:color-mix(in oklab, var(--blue), #000 10%);
      position:relative;
    }
    .plan-card--featured::after{
      content:"";position:absolute;inset:-1px;pointer-events:none;border-radius:inherit;
      background:linear-gradient(90deg, var(--blue), var(--blue-dark));opacity:.08
    }
    .plan-card .btn{width:100%;justify-self:stretch;align-self:end;}

    /* ======= BANNER DE COMPARAÇÃO FAMILIAR ======= */
    .comparison-highlight{
      max-width:100%;
      margin:0 auto 12px;
      border-radius:24px;
      background:linear-gradient(135deg,#ecfdf5,#dbeafe);
      padding:22px 22px 20px;
      box-shadow:var(--shadow);
      display:grid;
      grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);
      gap:18px;
      align-items:center;
    }
    .comparison-highlight__tag{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:4px 10px;
      border-radius:999px;
      font-size:.78rem;
      text-transform:uppercase;
      letter-spacing:.08em;
      font-weight:800;
      background:#6a0dad;
      color:#FFD700;
      margin-bottom:6px;
    }
    .comparison-highlight__title{
      font-size:1.15rem;
      margin:4px 0 6px;
    }
    .comparison-highlight__desc{
      font-size:.95rem;
      color:var(--ink-70);
      margin:0;
    }
    .comparison-highlight__right{
      display:flex;
      flex-direction:column;
      align-items:flex-end;
      gap:6px;
      text-align:right;
    }
    .comparison-highlight__from{
      font-size:.9rem;
      color:#64748b;
      text-decoration:line-through;
      font-weight:700;
    }
    .comparison-highlight__to{
      font-size:1.4rem;
      font-weight:800;
      color:#166534;
    }
    .comparison-highlight__to small{
      font-size:.9rem;
      opacity:.8;
    }
    .comparison-highlight__note{
      font-size:.78rem;
      color:#6b7280;
      max-width:260px;
    }
    @media (max-width:720px){
      .comparison-highlight{
        grid-template-columns:1fr;
        text-align:center;
      }
      .comparison-highlight__right{
        align-items:center;
        text-align:center;
      }
      .comparison-highlight__note{
        max-width:none;
      }
    }

    /* ======= TABELA COMPARATIVA (específico) ======= */
    .table-wrap{overflow:auto;-webkit-overflow-scrolling:touch}
    .compare{
      width:100%;border-collapse:separate;border-spacing:0;
      border:1px solid #eaf1f6;border-radius:16px;overflow:hidden;background:#fff
    }
    .compare thead th{
      position:sticky;top:0;background:#f8fbff;z-index:1;
      padding:12px;text-align:center;font-weight:800
    }
    .compare th:first-child,.compare td:first-child{
      position:sticky;left:0;text-align:left;background:#fff;z-index:2
    }
    .compare tbody td{padding:12px;text-align:center;border-bottom:1px solid #eef3f8}
    .compare tbody td:first-child{font-weight:700;color:var(--ink);text-align:left}
    .compare tbody tr:nth-child(even){background:#fcfeff}
    .compare .yes{font-weight:900}
    .compare .yes::before{content:"✓";color:#2FB67F}
    .compare .no{opacity:.45;font-weight:900}
    .compare .no::before{content:"—"}

    /* ======= FOOTER ======= */
    .footer{position:relative; z-index:3; background:var(--brand-deep);color:#e8f3fb}
    .footer__grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:18px;padding:28px 0}
    .footer__brand img{display:block;margin-bottom:8px;height:6vh;max-height:56px;width:auto;filter:brightness(1.05)}
    .footer__links, .footer__contact{display:flex;flex-direction:column;gap:8px}
    .footer__links a, .footer__contact a{color:#e8f3fb;text-decoration:none}
    .footer__links a:hover, .footer__contact a:hover{color:#fff}
    .footer__bottom{border-top:1px solid rgba(255,255,255,.2);padding:12px 0;background:transparent}
    .footer__bottom small{opacity:.95}

    /* ======= REVEAL ======= */
    .reveal{transform:translateY(12px);opacity:0}
    .reveal.is-in{transform:translateY(0);opacity:1;transition:transform .5s ease, opacity .5s ease}

    /* ======= MOBILE ======= */
    @media (max-width: 900px){
      .header__wrap{height:68px}
      .nav{inset:68px 0 auto 0}
      .section{padding:48px 0}
    }
    @media (max-width: 560px){
      body{padding-bottom:88px}
      .section{padding:40px 0}
    }
  </style>
</head>
<body>

  <!-- Barra fixa inferior -->
  <div class="topbar" id="topbar">
    <div class="container">
      <span>Clínico Geral 24h/7 • Especialidades seg–sex 09h–18h</span>
      <a class="topbar__cta" href="?r=auth/register" aria-label="Assine Agora">Assine Agora</a>
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

  <!-- Cabeçalho de Planos + Destaque Promoção -->
  <section class="section" aria-label="Cabeçalho de Planos">
    <div class="container plans-head">
      <div class="section__head section__head--center">
        <h1 class="section__title">Planos para cada momento</h1>
        <p class="section__desc">Individual, Familiar e Empresarial — escolha o ideal e comece a usar sem burocracia.</p>
      </div>

      <!-- Destaque: Por que o plano familiar compensa -->
      <div class="comparison-highlight">
        <div>
          <div class="comparison-highlight__tag">Promoção Familiar</div>
          <h2 class="comparison-highlight__title">Pagar por 4 pessoas ficou muito mais acessível</h2>
          <p class="comparison-highlight__desc">
            No plano Individual você paga <strong>R$ 49,90</strong> por pessoa. Para 4 pessoas, isso seria <strong>R$ 199,60/mês</strong>.
            Com o plano <strong>Familiar</strong>, todos ficam protegidos por um valor único promocional.
          </p>
        </div>
        <div class="comparison-highlight__right">
          <span class="comparison-highlight__from">De R$ 199,60/mês</span>
          <div class="comparison-highlight__to">
            Por <small>R$</small> 99,90 <small>/mês no plano Familiar</small>
          </div>
          <span class="comparison-highlight__note">
            Valores e condições promocionais sujeitos a alteração. Consulte nossa equipe para mais detalhes.
          </span>
        </div>
      </div>
    </div>
  </section>

  <!-- Cards dos planos -->
  <section class="section" aria-label="Planos e preços">
    <div class="container">
      <div class="plan-grid">

        <!-- Individual -->
        <article class="plan-card reveal">
          <header>
            <h3>Individual</h3>
            <div class="price"><small>R$</small>49,90 <small>/mês</small></div>
            <small class="muted">Para 1 titular</small>
          </header>
          <ul class="ticklist" style="margin:6px 0 8px">
            <li>Clínico Geral 24h/7</li>
            <li>Pedidos de exames, receitas e encaminhamentos</li>
            <li>Consultas presenciais com desconto</li>
            <li>Seguro de Acidentes Pessoais</li>
            <li>Sorteio mensal</li>
            <li>Assistência funeral individual</li>
            <li>Assistência residencial</li>
            <li>Clube de vantagens</li>
            <li>Desconto em medicamentos</li>
          </ul>
          <a class="btn btn--blueGrad btn--lg" href="/?r=auth/register">Assine Agora</a>
        </article>

        <!-- Familiar (Destaque) -->
        <article class="plan-card plan-card--featured reveal">
          <span class="badge">Mais escolhido</span>
          <header>
            <h3>Familiar</h3>
            <div class="price"><small>R$</small>99,90 <small>/mês</small></div>
            <small class="muted">Titular + dependentes</small>
          </header>
          <ul class="ticklist" style="margin:6px 0 8px">
            <li>Clínico Geral 24h/7</li>
            <li>Pedidos de exames, receitas e encaminhamentos</li>
            <li>Consultas presenciais com desconto</li>
            <li>Seguro de Acidentes Pessoais</li>
            <li>Sorteio mensal</li>
            <li>Assistência funeral familiar</li>
            <li>Assistência residencial</li>
            <li>Clube de vantagens</li>
            <li>Desconto em medicamentos</li>
          </ul>
          <a class="btn btn--blueGrad btn--lg" href="/?r=auth/register">Assine Agora</a>
        </article>

        <!-- Empresarial Individual -->
        <article class="plan-card reveal">
          <header>
            <h3>Empresarial Individual <small>(a partir de 3 vidas)</small></h3>
            <div class="price"><small>R$</small>39,90 <small>/mês</small></div>
            <small class="muted">Por colaborador</small>
          </header>
          <ul class="ticklist" style="margin:6px 0 8px">
            <li>Clínico Geral 24h/7</li>
            <li>Pedidos de exames, receitas e encaminhamentos</li>
            <li>Consultas presenciais com desconto</li>
            <li>Seguro de Acidentes Pessoais</li>
            <li>Sorteio mensal</li>
            <li>Assistência funeral individual</li>
            <li>Assistência residencial</li>
            <li>Clube de vantagens</li>
            <li>Desconto em medicamentos</li>
          </ul>
          <a class="btn btn--blueGrad btn--lg" href="/?r=auth/register">Assine Agora</a>
        </article>

        <!-- Empresarial Familiar -->
        <article class="plan-card reveal">
          <header>
            <h3>Empresarial Familiar <small>(a partir de 3 vidas)</small></h3>
            <div class="price"><small>R$</small>59,90 <small>/mês</small></div>
            <small class="muted">Por colaborador</small>
          </header>
          <ul class="ticklist" style="margin:6px 0 8px">
            <li>Clínico Geral 24h/7</li>
            <li>Pedidos de exames, receitas e encaminhamentos</li>
            <li>Consultas presenciais com desconto</li>
            <li>Seguro de Acidentes Pessoais</li>
            <li>Sorteio mensal</li>
            <li>Assistência funeral familiar</li>
            <li>Assistência residencial</li>
            <li>Clube de vantagens</li>
            <li>Desconto em medicamentos</li>
          </ul>
          <a class="btn btn--blueGrad btn--lg" href="/?r=auth/register">Assine Agora</a>
        </article>

      </div>
    </div>
  </section>

  <!-- Tabela comparativa -->
  <section class="section section--alt" id="comparar" aria-label="Tabela comparativa">
    <div class="container">
      <div class="section__head section__head--center">
        <h2 class="section__title">Compare os benefícios</h2>
        <p class="section__desc">Transparência total para você decidir com segurança</p>
      </div>

      <div class="table-wrap">
        <table class="compare" aria-describedby="comparacao">
          <caption id="comparacao" class="sr-only">Tabela de comparação dos planos</caption>
          <thead>
            <tr>
              <th style="text-align:left">Benefícios</th>
              <th>Individual</th>
              <th>Familiar</th>
              <th>Emp. Individual</th>
              <th>Emp. Familiar</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Clínico Geral 24h/7</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Pedidos de exames, receitas e encaminhamentos</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Consultas presenciais com desconto</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Seguro de Acidentes Pessoais</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Sorteio mensal</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Assistência funeral individual</td>
              <td class="yes"></td><td class="no"></td><td class="yes"></td><td class="no"></td>
            </tr>
            <tr>
              <td>Assistência funeral familiar</td>
              <td class="no"></td><td class="yes"></td><td class="no"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Assistência residencial</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Clube de vantagens</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
            <tr>
              <td>Descontos em medicamentos</td>
              <td class="yes"></td><td class="yes"></td><td class="yes"></td><td class="yes"></td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="section__desc center mt-24">Planos empresariais a partir de 3 vidas. Fale com a equipe para condições especiais.</p>
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
        <a href="/?r=site/faq">Sobre nós</a>
        <a href="/?r=site/parceiros">Parceiros</a>
        <a href="/?r=site/plans" aria-current="page">Planos</a>
        <a href="/?r=site/contato">Contato</a>
      </nav>

      <div class="footer__contact">
        <strong>Atendimento</strong>
        <a href="/?r=site/contato" rel="noopener">WhatsApp</a>
        <a href="mailto:contato@avivmais.com">contato@avivmais.com</a>
        <a href="/?r=site/faq">Políticas &amp; FAQ</a>
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

    // Reveal on view (cards)
    (function(){
      var els = document.querySelectorAll('.reveal');
      if (!('IntersectionObserver' in window) || !els.length) return;
      var obs = new IntersectionObserver(function(entries){
        for (var i=0;i<entries.length;i++){
          var e = entries[i];
          if (e.isIntersecting){
            e.target.classList.add('is-in');
            obs.unobserve(e.target);
          }
        }
      }, {threshold:0.12});
      for (var j=0;j<els.length;j++){ obs.observe(els[j]); }
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
</body>
</html>
