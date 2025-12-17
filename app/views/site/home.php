<?php
// Forçar UTF-8 (evita caracteres quebrados)
header('Content-Type: text/html; charset=UTF-8');
if (function_exists('mb_internal_encoding')) { @mb_internal_encoding('UTF-8'); }
if (function_exists('mb_http_output')) { @mb_http_output('UTF-8'); }

$PAGE_BARE = true;
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Aviv+ • Renovando vidas com cuidado e proteção</title>
  <meta name="description" content="Clube de benefícios com telemedicina, consultas populares e assistências essenciais a baixo custo." />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

  <style>
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
    color:var(--ink);
    background:#fff;
    line-height:1.6;
    text-rendering:optimizeLegibility;
  }

  /* remove overflow horizontal */
  html, body { overflow-x: clip; }
  @supports not (overflow-x: clip) { html, body { overflow-x: hidden; } }
  img, svg { max-width: 100%; height: auto; }

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
  .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
  :focus-visible{outline:3px solid color-mix(in oklab, var(--blue), #000 15%);outline-offset:2px}

  /* ===== Botões ===== */
  .btn{
    display:inline-flex;align-items:center;justify-content:center;gap:.5rem;
    padding:.875rem 1.1rem;border-radius:999px;text-decoration:none;font-weight:800;
    box-shadow: var(--shadow);
    transition:.22s transform ease, .22s background ease, .22s color ease;
    border:none;
  }
  .btn:hover{transform:translateY(-2px)}
  .btn--lg{padding:1rem 1.25rem;font-size:1.05rem}
  .btn--sm{padding:.6rem .9rem;font-size:.95rem}
  .btn--blueGrad{background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff}
  .btn--white{background:#fff;color:var(--ink)}
  .btn--ghost{background:transparent;border:2px solid #fff;color:#fff}
  .btn--ghost-white{background:transparent;border:2px solid #fff;color:#fff}

  /* ===== Topbar ===== */
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

  /* ===== Header ===== */
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

  /* ===== Hero ===== */
  .hero{position:relative;isolation:isolate;display:grid}
  .hero__bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2}
  .hero__overlay{position:absolute;inset:0;z-index:-1;background:rgba(0,0,0,.5)}
  .hero__grid{
    position:relative;z-index:1;
    display:grid;grid-template-columns:1fr;
    min-height:80svh;align-items:center;padding:64px 0 0;color:#fff;
  }
  .hero__col--text{max-width:66.666%}
  @media (max-width: 960px){ .hero__col--text{max-width:100%} }
  .hero__title,.hero__subtitle{color:#fff;text-shadow:0 2px 18px rgba(0,0,0,.25)}
  .hero__subtitle{max-width:620px;opacity:.98}
  .hero__actions{display:flex;gap:12px;margin-top:16px;flex-wrap:wrap}
  .hero__wave{position:absolute;left:0;right:0;bottom:-1px;height:100px;z-index:1;pointer-events:none}
  .hero__wave svg{display:block;width:100%;height:100%}

  /* ===== Sections ===== */
  .section{position:relative; z-index:1; padding:56px 0}
  .section__head{margin-bottom:18px}
  .section__title{margin-bottom:.25rem}
  .section__desc{color:var(--ink-70)}

  /* Pattern (sem overflow lateral) */
  .section--pattern{background:#fff; overflow:hidden; isolation:isolate; z-index:1}
  .section--pattern::before{
    content:""; position:absolute; left:50%; transform:translateX(-50%);
    top:-220px; width:2200px; height:1400px; z-index:0; pointer-events:none;
    background:
      url("data:image/svg+xml;utf8,\
<svg xmlns='http://www.w3.org/2000/svg' width='2200' height='1400' viewBox='0 0 2200 1400'>\
<g fill='none' stroke='%23E3E9F1' stroke-opacity='0.18'>\
  <circle cx='360' cy='260' r='220' stroke-width='26'/>\
  <circle cx='1750' cy='340' r='190' stroke-width='22'/>\
  <circle cx='980' cy='1020' r='160' stroke-width='20'/>\
</g>\
</svg>") center/contain no-repeat;
  }
  .section--pattern > .container{position:relative; z-index:1}

  /* Benefits */
  .grid{display:grid;gap:22px}
  .grid--benefits{grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
  .benefit{
    background:#fff;border:1px solid #e9eef2;border-radius:20px;box-shadow:var(--shadow);
    padding:22px;text-align:center;
    transition:transform .18s ease, box-shadow .18s ease
  }
  .benefit:hover{transform:translateY(-4px) scale(1.02);box-shadow:0 10px 24px rgba(0,0,0,.10)}
  .benefit__icon{width:84px;height:84px;object-fit:contain;display:block;margin:0 auto 10px}

  /* Planos */
  .plans-grid{display:grid;grid-template-columns:1.2fr 1fr;gap:24px;align-items:center;margin-top:8px}
  .plans-left .lead{font-size:1.05rem}
  .ticklist{list-style:none;margin:12px 0 16px;padding:0;display:grid;gap:10px}
  .ticklist li{position:relative;padding-left:28px;font-weight:700}
  .ticklist li::before{content:"✓";position:absolute;left:0;top:0;font-weight:800;color:#2FB67F}
  .plans-image{width:100%;height:auto;border-radius:var(--radius-lg);box-shadow:var(--shadow);display:block}
  @media (max-width: 920px){
    .plans-grid{grid-template-columns:1fr}
    .plans-right{order:-1}
  }

  /* Especialidades */
  .specs{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
  .spec{
    border:2px solid color-mix(in oklab, var(--green), #000 15%);
    background:transparent;color:var(--ink);font-weight:700;
    padding:.55rem .9rem;border-radius:999px
  }
  .info-caps{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:12px;margin-top:14px}
  .info-cap{
    background:#fff;border:1px dashed #dfeee7;border-radius:14px;padding:.9rem 1rem;
    display:flex;align-items:center;gap:12px;
  }
  .cap-icon{width:48px;height:48px;border-radius:999px;object-fit:cover;border:2px solid var(--green);background:#fff}
  .info-cap strong{display:block}

  /* Slider */
  .slider{position:relative; overflow:hidden;}
  .slider__viewport{overflow:hidden}
  .slider__track{display:flex;gap:16px;will-change:transform;transition:transform .4s ease}
  .slider__btn{
    position:absolute;top:50%;transform:translateY(-50%);z-index:2;border:0;border-radius:999px;
    width:40px;height:40px;background:var(--blue);color:#fff;cursor:pointer;box-shadow:var(--shadow)
  }
  .slider__btn:hover{background:linear-gradient(90deg, var(--blue), var(--blue-dark))}
  .slider__btn.prev{left:8px}
  .slider__btn.next{right:8px}
  .slider__dots{display:flex;gap:8px;justify-content:center;margin-top:12px}
  .slider__dots button{width:8px;height:8px;border-radius:999px;border:0;background:#d9f2e8}
  .slider__dots button[aria-current="true"]{background:var(--green)}
  .quote{
    text-align:center;background:transparent;border:2px solid #A8E6CF;
    border-radius:24px;padding:18px 16px;min-width:260px;flex:0 0 calc(33.333% - 10.666px)
  }
  .quote blockquote{font-style:italic;margin:.5rem 0;color:var(--ink)}
  .quote__avatar{border-radius:999px;display:block;margin:0 auto 8px;width:80px;height:80px;object-fit:cover}
  .quote__name{display:block;color:var(--ink-70);font-weight:700}

  /* Parceiros */
  .logos-shelf--4{
    display:grid;grid-template-columns:repeat(4, minmax(160px,1fr));
    gap:18px 24px;align-items:center;justify-items:center;
  }
  .logos-shelf--4 img{
    width:100%;max-width:260px;height:auto;display:block;
    filter:grayscale(100%) contrast(85%) brightness(95%);
    transition:filter .2s ease, transform .2s ease;
  }
  .logos-shelf--4 img:hover{filter:none;transform:translateY(-2px)}
  @media (max-width: 860px){
    .logos-shelf--4{grid-template-columns:repeat(2, minmax(140px,1fr))}
  }

  /* imagem única ocupando a linha */
  .logos-shelf--4 img{
    grid-column: 1 / -1;
    width: 100%;
    max-width: 100%;
    height: auto;
  }

  /* ===== CTA com ondas (IGUAL AO CONTATO) ===== */
  .cta--bar{position:relative; isolation:isolate; z-index:3; background:transparent; overflow:visible}
  .cta--bar::before{
    content:"";
    position:absolute; left:0; right:0;
    top:150px; bottom:0;
    background:var(--brand-deep);
    z-index:0
  }
  .cta__wave{position:absolute;left:0;right:0;top:0;height:270px;z-index:1;pointer-events:none}
  .cta__wave svg{display:block;width:100%;height:100%}
  .cta__wave path{fill:var(--brand-deep)}
  .cta__wrap{
    position:relative; z-index:3;
    display:flex;align-items:center;justify-content:space-between;
    gap:16px;flex-wrap:wrap;
    color:#fff;
    margin-top:55px
  }
  .section__title--light{color:#fff}
  .section__desc--light{color:#ffffffcc}

  /* ===== Footer FULL-WIDTH ===== */
  .footer{
    position:relative; z-index:3;
    background:var(--brand-deep);
    color:#e8f3fb;
    width:100%;
  }
  .footer__grid{
    display:grid;
    grid-template-columns:2fr 1fr 1fr;
    gap:18px;
    padding:28px 0;
    align-items:start;
  }
  .footer__brand img{display:block;margin-bottom:8px;height:6vh;max-height:56px;width:auto;filter:brightness(1.05)}
  .footer__links,.footer__contact{display:flex;flex-direction:column;gap:8px}
  .footer__links a,.footer__contact a{color:#e8f3fb;text-decoration:none}
  .footer__links a:hover,.footer__contact a:hover{color:#fff}
  .footer__bottom{border-top:1px solid rgba(255,255,255,.2);padding:12px 0;background:transparent}
  .footer__bottom small{opacity:.95}

  /* ===== Mobile ===== */
  @media (max-width: 900px){
    .header__wrap{height:68px}

    .nav{
      display:none;
      position:fixed;
      left:0; right:0;
      top:68px;
      max-height:calc(100dvh - 68px);
      overflow:auto;
      -webkit-overflow-scrolling:touch;
      overscroll-behavior:contain;

      flex-direction:column;
      gap:14px;
      padding:16px 16px calc(18px + env(safe-area-inset-bottom));
      background:#fff;
      border-top:1px solid #e9eef2;
      box-shadow:0 12px 24px rgba(0,0,0,.12);
      z-index:95;
    }
    .nav[data-open="true"]{display:flex;}
    .nav-toggle{display:inline-grid;place-items:center}

    html.nav-open, body.nav-open{ overflow:hidden; }

    .section{padding:48px 0}
    .hero__grid{min-height:72svh;padding:48px 0 0}

    .section--pattern::before{content:none !important;display:none !important}
  }

  @media (max-width: 560px){
    body{padding-bottom:88px}

    .hero__grid{min-height:66svh;padding:40px 0 12px}
    .hero__col--text{max-width:100%;margin:0 16px}
    .hero__title{font-size:clamp(1.6rem, 6vw, 2.1rem)}
    .grid--benefits{grid-template-columns:1fr}
    .benefit{padding:18px;margin:0 12px}

    .plans-grid{grid-template-columns:1fr;gap:16px}
    .plans-left{margin:0 16px}
    .plans-right{order:2;margin:0 16px}

    .cta__wrap{justify-content:center;text-align:center;gap:12px}

    /* Footer 1 coluna, centralizado */
    .footer__grid{
      grid-template-columns:1fr;
      gap:12px;
      text-align:center;
      justify-items:center;
    }
    .footer__links,.footer__contact{align-items:center}
  }

  /* Reveal */
  .reveal{transform:translateY(12px);opacity:0}
  .reveal.is-in{transform:translateY(0);opacity:1;transition:transform .5s ease, opacity .5s ease}
  </style>
</head>

<body>

  <!-- Barra fixa inferior -->
  <div class="topbar" id="topbar">
    <div class="container">
      <span>Clínico Geral 24h/7 • Especialidades seg–sex 09h–18h</span>
      <a class="topbar__cta" href="#planos" aria-label="Assine Agora">Assine Agora</a>
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
          <a href="/?r=site/contato">Contato</a>
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
  <section class="hero" aria-label="Hero principal">
    <img class="hero__bg" src="/img/hero.jpg" alt="Fundo" loading="eager">
    <div class="hero__overlay"></div>

    <div class="container hero__grid">
      <div class="hero__col hero__col--text">
        <h1 class="hero__title">Aviv+: Renovando vidas com cuidado e proteção</h1>
        <p class="hero__subtitle">Saúde acessível, humana e transparente — do seu jeito.</p>
        <div class="hero__actions">
          <a class="btn btn--blueGrad btn--lg" href="/?r=site/planos">Assine Agora</a>
          <a class="btn btn--ghost btn--lg" href="#beneficios">Conheça os benefícios</a>
        </div>
      </div>
    </div>

    <div class="hero__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none" focusable="false">
        <path d="M0,64 C240,120 480,0 720,48 C960,96 1200,96 1440,48 L1440,120 L0,120 Z" fill="#ffffff"></path>
      </svg>
    </div>
  </section>

  <!-- Benefícios -->
  <section class="section" id="beneficios" aria-label="Benefícios">
    <div class="container">
      <div class="section__head center">
        <h2 class="section__title">Benefícios que importam</h2>
        <p class="section__desc">Tudo o que você precisa num só clube</p>
      </div>

      <div class="grid grid--benefits">
        <article class="benefit">
          <img class="benefit__icon" src="/img/telemedicina.png" alt="Telemedicina 24h" loading="lazy">
          <h3>Telemedicina 24h</h3>
          <p>Clínico Geral imediato, sem filas.</p>
        </article>

        <article class="benefit">
          <img class="benefit__icon" src="/img/search.png" alt="Consultas populares" loading="lazy">
          <h3>Consultas populares</h3>
          <p>Rede de parceiros com preço justo.</p>
        </article>

        <article class="benefit">
          <img class="benefit__icon" src="/img/funeral.png" alt="Assistência Funeral" loading="lazy">
          <h3>Assistência Funeral</h3>
          <p>Amparo completo em momentos delicados.</p>
        </article>

        <article class="benefit">
          <img class="benefit__icon" src="/img/drugs.png" alt="Desconto em medicamentos" loading="lazy">
          <h3>Desconto em medicamentos</h3>
          <p>Economia real na sua farmácia do mês.</p>
        </article>

        <article class="benefit">
          <img class="benefit__icon" src="/img/sorteio.png" alt="Sorteios mensais" loading="lazy">
          <h3>Sorteios mensais</h3>
          <p>Prêmios para assinantes ativos.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- Planos -->
  <section class="section section--pattern" id="planos" aria-label="Planos">
    <div class="container">
      <div class="section__head center">
        <h2 class="section__title">Planos para cada momento</h2>
        <p class="section__desc">Individual, Familiar e Empresarial — compare e assine online.</p>
      </div>

      <div class="plans-grid">
        <div class="plans-left">
          <p class="lead">
            Nosso clube foi pensado para ser simples, humano e acessível.
            Escolha o plano ideal e tenha acesso a <strong>telemedicina 24h</strong>, pedidos de exames,
            assistências essenciais e um clube de vantagens para economizar todos os meses.
          </p>

          <ul class="ticklist">
            <li>Atendimento 24h com Clínico Geral</li>
            <li>Sem burocracia para começar a usar</li>
          </ul>

          <a class="btn btn--blueGrad btn--lg" href="/?r=site/planos">Ver planos</a>
        </div>

        <div class="plans-right">
          <img class="plans-image" src="/img/tele.jpg" alt="Telemedicina pelo celular">
        </div>
      </div>
    </div>
  </section>

  <!-- Especialidades -->
  <section class="section" id="especialidades" aria-label="Especialidades disponíveis">
    <div class="container">
      <div class="section__head center">
        <h2 class="section__title">Especialidades na Telemedicina</h2>
        <p class="section__desc">Consultas orientativas nas principais áreas</p>
      </div>

      <div class="specs">
        <span class="spec">Clínico</span>
        <span class="spec">Pediatra</span>
        <span class="spec">Cardiologia</span>
        <span class="spec">Dermatologia</span>
        <span class="spec">Ginecologia</span>
        <span class="spec">Ortopedia</span>
        <span class="spec">Alergia e Imunologia</span>
        <span class="spec">Geriatra</span>
        <span class="spec">Gastrenterologia</span>
        <span class="spec">Psiquiatria</span>
        <span class="spec">Otorrinolaringologia</span>
        <span class="spec">Pneumologia</span>
        <span class="spec">Neurologia</span>
        <span class="spec">Urologia</span>
        <span class="spec">Endocrinologia</span>
        <span class="spec">Hepatologia</span>
        <span class="spec">Hematologia</span>
        <span class="spec">Mastologia</span>
        <span class="spec">Nefrologia</span>
        <span class="spec">Reumatologia</span>
        <span class="spec">Oftalmologia</span>
        <span class="spec">Cirurgia Geral</span>
        <span class="spec">Cirurgia Vascular</span>
        <span class="spec">Coloproctologia</span>
        <span class="spec">Neuropediatra</span>
        <span class="spec">Hemoterapia</span>
        <span class="spec">Infectologia</span>
      </div>

      <div class="info-caps">
        <div class="info-cap">
          <img class="cap-icon" src="/img/ico-clinico.png" alt="">
          <div>
            <strong>Clínico Geral</strong>
            <span>24 horas por dia, 7 dias por semana</span>
          </div>
        </div>
        <div class="info-cap">
          <img class="cap-icon" src="/img/ico-especialidades.png" alt="">
          <div>
            <strong>Especialidades</strong>
            <span>Seg–Sex, 09:00–18:00 (exceto feriados)</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Depoimentos -->
  <section class="section section--pattern" id="depoimentos" aria-label="Depoimentos de clientes">
    <div class="container">
      <div class="section__head center">
        <h2 class="section__title">Quem usa, recomenda</h2>
        <p class="section__desc">Histórias reais de quem confia no Aviv+</p>
      </div>

      <div class="slider">
        <button class="slider__btn prev" aria-label="Anterior">‹</button>
        <div class="slider__viewport">
          <div class="slider__track">
            <article class="quote">
              <img class="quote__avatar" src="/img/dep1.jpg" alt="Foto da Ana" loading="lazy">
              <blockquote>“Atendimento rápido para minha família. A assinatura compensa e nos deixa tranquilos.”</blockquote>
              <span class="quote__name">Ana Paula • Empreendedora</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep2.jpg" alt="Foto do Bruno" loading="lazy">
              <blockquote>“Resolvi dúvidas com o clínico em minutos, sem precisar sair de casa ou enfrentar fila.”</blockquote>
              <span class="quote__name">Bruno Silva • Designer</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep3.jpg" alt="Foto da Camila" loading="lazy">
              <blockquote>“O clube de vantagens ajudou muito na farmácia do mês. A diferença no bolso é grande.”</blockquote>
              <span class="quote__name">Camila Rocha • Mãe</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep4.jpg" alt="Foto do Diego" loading="lazy">
              <blockquote>“Fácil de usar, preço justo e atendimento humano. Hoje minha família inteira é assinante.”</blockquote>
              <span class="quote__name">Diego Martins • Autônomo</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep5.jpg" alt="Foto da Elisa" loading="lazy">
              <blockquote>“A telemedicina 24h salvou um domingo complicado aqui em casa. Fomos orientados na hora.”</blockquote>
              <span class="quote__name">Elisa Nunes • Professora</span>
            </article>
          </div>
        </div>
        <button class="slider__btn next" aria-label="Próximo">›</button>
        <div class="slider__dots" role="tablist" aria-label="Paginação de depoimentos"></div>
      </div>
    </div>
  </section>

  <!-- Parceiros -->
  <section class="section" id="parceiros" aria-label="Parceiros">
    <div class="container">
      <div class="section__head center">
        <h2 class="section__title">Parceiros</h2>
        <p class="section__desc">Algumas marcas que já fazem parte do ecossistema Aviv+</p>
      </div>

      <div class="logos-shelf--4">
        <img src="/img/partners.png" alt="Parceiros Aviv+" loading="lazy">
      </div>
    </div>
  </section>

  <!-- CTA final (ondas do contato) -->
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
        <a class="btn btn--white btn--lg" href="/?r=site/planos">Assinar</a>
        <a class="btn btn--ghost-white btn--lg" href="/?r=site/contato">Fale no WhatsApp</a>
      </div>
    </div>
  </section>

  <!-- Footer FULL -->
  <footer class="footer" aria-label="Rodapé">
    <div class="container footer__grid">
      <div class="footer__brand">
        <img src="/img/logo-aviv-plus.png" alt="Aviv+">
        <p>Bem-estar e proteção para todas as famílias.</p>
      </div>

      <nav class="footer__links" aria-label="Links rápidos">
        <strong>Links</strong>
        <a href="#beneficios">Benefícios</a>
        <a href="#planos">Planos</a>
        <a href="#especialidades">Especialidades</a>
        <a href="#depoimentos">Depoimentos</a>
        <a href="#parceiros">Parceiros</a>
      </nav>

      <div class="footer__contact">
        <strong>Atendimento</strong>
        <a href="/?r=site/contato" rel="noopener">WhatsApp</a>
        <a href="mailto:contato@avivmais.com.br">contato@avivmais.com.br</a>
        <a href="/?r=site/contato">Políticas &amp; FAQ</a>
      </div>
    </div>

    <div class="footer__bottom">
      <div class="container center">
        <small>© <?= date('Y') ?> Aviv+. Todos os direitos reservados.</small>
      </div>
    </div>
  </footer>

  <script>
  // Menu mobile (sem scroll fantasma)
  (function(){
    const nav = document.getElementById('menu');
    const toggle = document.querySelector('.nav-toggle');
    if (!nav || !toggle) return;

    const isMobile = () => window.matchMedia('(max-width: 900px)').matches;

    function openNav(){
      nav.setAttribute('data-open','true');
      toggle.setAttribute('aria-expanded','true');
      document.documentElement.classList.add('nav-open');
      document.body.classList.add('nav-open');
    }
    function closeNav(){
      nav.removeAttribute('data-open');
      toggle.setAttribute('aria-expanded','false');
      document.documentElement.classList.remove('nav-open');
      document.body.classList.remove('nav-open');
    }

    toggle.addEventListener('click', (e)=>{
      e.preventDefault();
      const open = nav.getAttribute('data-open') === 'true';
      open ? closeNav() : openNav();
    });

    document.addEventListener('click', (e)=>{
      if (!isMobile()) return;
      if (!nav.contains(e.target) && !toggle.contains(e.target)) closeNav();
    });

    nav.addEventListener('click', (e)=>{
      if (e.target.closest('a')) closeNav();
    });

    document.addEventListener('keydown', (e)=>{
      if (e.key === 'Escape') closeNav();
    });

    window.addEventListener('resize', ()=>{
      if (!isMobile()) closeNav();
    });
  })();

  // Reveal
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting) {
        e.target.classList.add('is-in');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.section .container, .benefit, .quote').forEach((el) => {
    el.classList.add('reveal');
    observer.observe(el);
  });

  // Slider
  (function initTestimonials(){
    const slider = document.querySelector('.slider');
    if (!slider) return;

    const viewport = slider.querySelector('.slider__viewport');
    const track = slider.querySelector('.slider__track');
    const prev = slider.querySelector('.slider__btn.prev');
    const next = slider.querySelector('.slider__btn.next');
    const dots = slider.querySelector('.slider__dots');
    const items = Array.from(track.children);

    let perView = 3;
    let index = 0;

    const computePerView = () => {
      const w = window.innerWidth;
      perView = w >= 1024 ? 3 : w >= 640 ? 2 : 1;

      items.forEach(el => {
        const basis = perView === 3 ? 'calc(33.333% - 10.666px)' :
                      perView === 2 ? 'calc(50% - 8px)' : '100%';
        el.style.flex = `0 0 ${basis}`;
      });

      index = Math.min(index, Math.max(0, Math.ceil(items.length / perView) - 1));
      update();
      buildDots();
    };

    const pages = () => Math.ceil(items.length / perView);

    const update = () => {
      const viewportWidth = viewport.clientWidth;
      track.style.transform = `translateX(-${index * viewportWidth}px)`;
      updateDots();
    };

    const buildDots = () => {
      dots.innerHTML = '';
      for (let i = 0; i < pages(); i++) {
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', `Ir para página ${i + 1}`);
        b.addEventListener('click', () => { index = i; update(); });
        dots.appendChild(b);
      }
      updateDots();
    };

    const updateDots = () => {
      dots.querySelectorAll('button').forEach((b, i) => b.setAttribute('aria-current', i === index ? 'true' : 'false'));
    };

    prev.addEventListener('click', () => { index = (index - 1 + pages()) % pages(); update(); });
    next.addEventListener('click', () => { index = (index + 1) % pages(); update(); });

    window.addEventListener('resize', computePerView);
    computePerView();
  })();

  // Topbar hide/show
  (function initBottomBar(){
    const bar = document.getElementById('topbar');
    if (!bar) return;

    let lastY = window.scrollY;
    let ticking = false;

    const onScroll = () => {
      const y = window.scrollY;
      const goingDown = y > lastY + 6;
      const goingUp   = y < lastY - 6;

      if (goingDown && y > 60) bar.classList.add('is-hidden');
      else if (goingUp) bar.classList.remove('is-hidden');

      lastY = y;
      ticking = false;
    };

    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(onScroll);
        ticking = true;
      }
    }, { passive: true });
  })();
  </script>

  <!-- Ocultar topnav/ad do tema base, se houver -->
  <script>
  (function () {
    var css = `
      header.topnav[data-topnav],
      .adbar-wrap#ad-header468-outer,
      #ad-header468 { display:none!important; visibility:hidden!important; pointer-events:none!important; }
    `;
    var style = document.createElement('style');
    style.type = 'text/css';
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);

    var selectors = ['header.topnav[data-topnav]','.adbar-wrap#ad-header468-outer','#ad-header468'];
    function hideAll(root) {
      (root||document).querySelectorAll(selectors.join(',')).forEach(function (el) {
        el.setAttribute('hidden',''); el.setAttribute('aria-hidden','true');
        el.style.setProperty('display','none','important');
        el.style.setProperty('visibility','hidden','important');
        el.style.setProperty('pointer-events','none','important');
        el.querySelectorAll('a,button,input,select,textarea,[tabindex]').forEach(function (n) {
          n.tabIndex=-1; n.setAttribute('aria-hidden','true');
        });
      });
    }
    if (document.readyState==='loading') document.addEventListener('DOMContentLoaded', function(){ hideAll(); }); else hideAll();
    var mo=new MutationObserver(function(muts){
      var needs=false;
      for (var i=0;i<muts.length;i++){
        var m=muts[i];
        if (m.type==='childList'){
          m.addedNodes.forEach(function(n){ if(n.nodeType===1) hideAll(n); });
        } else if (m.type==='attributes'){ needs=true; }
      }
      if (needs) hideAll();
    });
    mo.observe(document.documentElement,{subtree:true,childList:true,attributes:true,attributeFilter:['class','style']});
  })();
  </script>

</body>
</html>
