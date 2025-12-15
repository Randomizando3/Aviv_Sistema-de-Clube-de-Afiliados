<?php $PAGE_BARE = true; ?>
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
  /* ======= TOKENS GLOBAIS ======= */
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


  /* Fundo padrão das páginas públicas (igual ao login) */
body.site-page{
  min-height:100dvh;
  margin:0;
  color:#6B7784;
  font-family:"Open Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
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
  .mt-24{margin-top:24px}

  /* ======= BOTÕES ======= */
  .btn{
    display:inline-flex;align-items:center;justify-content:center;
    gap:.5rem;padding:.875rem 1.1rem;border-radius:999px;
    text-decoration:none;font-weight:800;box-shadow: var(--shadow);
    transition:.22s transform ease, .22s background ease, .22s color ease;
  }
  .btn:hover{transform:translateY(-2px)}
  .btn--blue{background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff;border:none}
  .btn--blueGrad{background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff;border:none}
  .btn--white{background:#fff;color:var(--ink);border:none}
  .btn--ghost{background:transparent;border:2px solid #fff;color:#fff}
  .btn--ghost-white{background:transparent;border:2px solid #fff;color:#fff}
  .btn--lg{padding:1rem 1.25rem;font-size:1.05rem}
  .btn--sm{padding:.6rem .9rem;font-size:.95rem}
  .btn .icon{display:block}

  /* ======= TOPBAR ======= */
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

    /* MOBILE: remover shapes/patterns e manter fundo branco */
    .section--pattern{background:#fff !important}
    .section--pattern::before{content:none !important;display:none !important}
  }

  /* ======= HERO ======= */
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

  /* ======= SEÇÕES & padrões ======= */
  .section{position:relative; z-index:1; padding:56px 0}
  .section__head{margin-bottom:18px}
  .section__title{margin-bottom:.25rem}
  .section__title--light{color:#fff}
  .section__desc{color:var(--ink-70)}
  .section__desc--light{color:#ffffffcc}

  .section__head--center{
    text-align:center;
    max-width:860px;
    margin:0 auto 28px;
  }
  .section__head--center .section__desc{margin:0 auto}

  .section--pattern{background:#fff; overflow:visible; isolation:isolate; z-index:1}
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

  /* ======= BENEFÍCIOS ======= */
  .grid{display:grid;gap:22px}
  .grid--benefits{grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
  .benefit{
    background:#fff;border:1px solid #e9eef2;border-radius:20px;box-shadow:var(--shadow);padding:22px;
    text-align:center;transition:transform .18s ease, box-shadow .18s ease
  }
  .benefit:hover{transform:translateY(-4px) scale(1.02);box-shadow:0 10px 24px rgba(0,0,0,.10)}
  .benefit__icon{width:84px;height:84px;object-fit:contain;display:block;margin:0 auto 10px}

  /* ======= PLANOS ======= */
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

  /* ======= ESPECIALIDADES ======= */
  .specs{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}
  .spec{border:2px solid color-mix(in oklab, var(--green), #000 15%);background:transparent;color:var(--ink);font-weight:700;
    padding:.55rem .9rem;border-radius:999px}
  .info-caps{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:12px;margin-top:14px}
  .info-cap{
    background:#fff;border:1px dashed #dfeee7;border-radius:14px;padding:.9rem 1rem;
    display:flex;align-items:center;gap:12px;
  }
  .cap-icon{
    width:48px;height:48px;border-radius:999px;object-fit:cover;
    border:2px solid var(--green);background:#fff;
  }
  .info-cap strong{display:block}

  /* ======= SLIDER ======= */
  .slider{position:relative}
  .slider--out{margin:0 -48px;padding:0 48px}
  .slider__viewport{overflow:hidden}
  .slider__track{display:flex;gap:16px;will-change:transform;transition:transform .4s ease}
  .slider__btn{
    position:absolute;top:50%;transform:translateY(-50%);z-index:2;border:0;border-radius:999px;
    width:40px;height:40px;background:var(--blue);color:#fff;cursor:pointer;box-shadow:var(--shadow)
  }
  .slider__btn:hover{background:linear-gradient(90deg, var(--blue), var(--blue-dark))}
  .slider__btn.prev{left:-36px}
  .slider__btn.next{right:-36px}
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

  /* ======= PARCEIROS ======= */
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

  /* ======= FORM PARCEIRO – moderno ======= */
  .form-card{
    position:relative;
    padding:26px;
    border-radius:16px;
    background: color-mix(in oklab, #fff 92%, #0000);
    border:1px solid color-mix(in oklab, #fff 70%, #0000);
    box-shadow: 0 8px 24px rgba(0,0,0,.10), 0 2px 8px rgba(0,0,0,.06);
    backdrop-filter: blur(8px);
  }
  .form-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(260px,1fr));gap:16px}
  .field{display:grid;gap:8px}
  .field label{font-weight:600;font-size:.95rem}
  .control{
    width:100%;
    border:1px solid rgba(0,0,0,.12);
    background:#fff;
    border-radius:12px;
    padding:14px 14px;
    font:600 0.95rem/1.25 "Open Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    color:var(--ink);
    transition: box-shadow .2s ease, border-color .2s ease, transform .05s ease;
    box-shadow: inset 0 1px 0 rgba(14,17,22,.04);
  }
  .control::placeholder{color:#8891a0;font-weight:400}
  .control:focus{
    outline:none;
    border-color: color-mix(in oklab, var(--blue) 70%, var(--blue-dark));
    box-shadow:0 0 0 4px color-mix(in oklab, var(--blue) 18%, transparent), inset 0 1px 0 rgba(14,17,22,.05);
  }
  textarea.control{min-height:132px;resize:vertical}
  .btn--modern{
    border-radius:999px;padding:14px 22px;font-weight:800;letter-spacing:.2px;border:0;cursor:pointer;
    background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff;
    box-shadow:0 10px 20px color-mix(in oklab, var(--blue) 20%, transparent);
    transition:transform .06s ease, box-shadow .2s ease, filter .2s ease;
  }
  .btn--modern:hover{filter:saturate(1.1) brightness(1.02)}
  .btn--modern:active{transform:translateY(1px)}

  /* ======= COMO FUNCIONA – steps ======= */
  .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}
  .step{padding:16px;border:1px solid rgba(0,0,0,.12);border-radius:14px;background:#fff;box-shadow:0 2px 8px rgba(14,17,22,.04)}
  .step h3{margin:0}
  .step-chip{
    width:40px;height:40px;border-radius:999px;display:grid;place-items:center;font-weight:800;color:#fff;
    background:linear-gradient(90deg, var(--blue), var(--blue-dark));
    box-shadow:0 6px 14px color-mix(in oklab, var(--blue-dark) 24%, transparent);
  }

  /* ======= CTA FINAL ======= */
  .cta--bar{
    position:relative; isolation:isolate; z-index:3;  
    background:transparent;
    overflow:visible;
  }
  .cta--bar::before{
    content:""; position:absolute; left:0; right:0; top:150px; bottom:0;
    background:var(--brand-deep); z-index:0;
  }
  .cta--bar::after{
    content:""; position:absolute; left:0; right:0; bottom:0; height:1px;
    background:rgba(255,255,255,.85); z-index:2;
  }
  .cta__wave{position:absolute;left:0;right:0;top:0;height:270px;z-index:1;pointer-events:none}
  .cta__wave svg{display:block;width:100%;height:100%}
  .cta__wave path{fill:var(--brand-deep)}
  .cta__wrap{
    position:relative; z-index:3;
    display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; 
    color:#fff;
    margin-top:55px;
  }
  .section__title--light, .section__desc--light{color:#fff}

  /* ======= FOOTER ======= */
  .footer{position:relative; z-index:3; background:var(--brand-deep);color:#e8f3fb}
  .footer__grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:18px;padding:28px 0}
  .footer__brand img{display:block;margin-bottom:8px;height:6vh;max-height:56px;width:auto;filter:brightness(1.05)}
  .footer__links, .footer__contact{display:flex;flex-direction:column;gap:8px}
  .footer__links a, .footer__contact a{color:#e8f3fb;text-decoration:none}
  .footer__links a:hover, .footer__contact a:hover{color:#fff}
  .footer__bottom{border-top:1px solid rgba(255,255,255,.2);padding:12px 0;background:transparent}
  .footer__bottom small{opacity:.95}

  /* ======= ANIMAÇÕES / ACESSO ======= */
  .reveal{transform:translateY(12px);opacity:0}
  .reveal.is-in{transform:translateY(0);opacity:1;transition:transform .5s ease, opacity .5s ease}
  .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
  :focus-visible{outline:3px solid color-mix(in oklab, var(--blue), #000 15%);outline-offset:2px}

  /* ======= MOBILE ======= */
  @media (max-width: 900px){
    .header__wrap{height:68px}
    .nav{inset:68px 0 auto 0}
    .hero__grid{min-height:72svh;padding:48px 0 0}
    .section{padding:48px 0}
  }
  @media (max-width: 560px){
    body{padding-bottom:88px}
    .hero__grid{min-height:66svh;padding:40px 0 12px}
    .hero__col--text{max-width:100%;margin:0 16px}
    .hero__title{font-size:clamp(1.6rem, 6vw, 2.1rem)}
    .hero__subtitle{margin-right:6px}
    .hero__actions{gap:10px}
    .grid--benefits{grid-template-columns:1fr}
    .benefit{padding:18px;margin:0 12px}
    .benefit__icon{width:92px;height:92px}
    .plans-grid{grid-template-columns:1fr;gap:16px}
    .plans-left{margin:0 16px}
    .plans-right{order:2;margin:0 16px}
    .ticklist{gap:8px}
    .specs{gap:8px;justify-content:flex-start;padding:0 12px}
    .slider--out{margin:0;padding:0 8px}
    .slider__btn{width:36px;height:36px}
    .slider__btn.prev{left:6px}
    .slider__btn.next{right:6px}
    .logos-shelf--4{grid-template-columns:repeat(2, minmax(120px,1fr))}
    .cta__wrap{justify-content:center;text-align:center;gap:12px}

    .section--pattern{background:#fff !important}
    .section--pattern::before{content:none !important;display:none !important}
  }


   .modal-backdrop{
    position:fixed;
    inset:0;
    z-index:999;
    background:rgba(15,23,42,.55);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:16px;
    opacity:0;
    pointer-events:none;
    transition:opacity .25s ease;
  }
  .modal-backdrop.is-open{
    opacity:1;
    pointer-events:auto;
  }
  .modal-promo{
    width:100%;
    max-width:480px;
    background:#ffffff;
    border-radius:24px;
    box-shadow:0 18px 40px rgba(15,23,42,.25);
    padding:22px 22px 20px;
    position:relative;
    overflow:hidden;
  }
  .modal-promo::before{
    content:"";
    position:absolute;
    inset:auto -40px 0 auto;
    width:180px;
    height:180px;
    background:radial-gradient(circle at 30% 10%, #A8E6CF 0, #A8E6CF33 40%, transparent 70%);
    opacity:.9;
    pointer-events:none;
  }
  .modal-promo__close{
    position:absolute;
    top:12px;
    right:12px;
    width:32px;
    height:32px;
    border-radius:999px;
    border:0;
    display:grid;
    place-items:center;
    background:rgba(148,163,184,.12);
    color:#475569;
    cursor:pointer;
    transition:background .18s ease, transform .08s ease;
  }
  .modal-promo__close:hover{
    background:rgba(148,163,184,.22);
    transform:translateY(-1px);
  }
      .modal-promo__badge{
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding:6px 14px;
      border-radius:999px;
      font-size:.78rem;
      text-transform:uppercase;
      letter-spacing:.08em;
      font-weight:800;
      background:#6a0dad; /* Roxo forte */
      color:#FFD700; /* Amarelo ouro */
      margin-bottom:12px;
    }

  .modal-promo__badge span{
    display:inline-block;
  }
  .modal-promo__title{
    font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size:1.35rem;
    margin:4px 0 6px;
    color:var(--ink);
  }
  .modal-promo__subtitle{
    font-size:.95rem;
    color:var(--ink-70);
    margin:0 0 14px;
  }
  .modal-promo__list{
    list-style:none;
    padding:0;
    margin:0 0 18px;
    display:grid;
    gap:6px;
    font-size:.9rem;
    color:var(--ink);
  }
  .modal-promo__list li{
    position:relative;
    padding-left:20px;
  }
  .modal-promo__list li::before{
    content:"•";
    position:absolute;
    left:6px;
    top:0;
    color:var(--blue-dark);
    font-weight:800;
  }

  .modal-promo__foot{
  display:flex;
  flex-direction:column;   /* Agora empilha verticalmente */
  align-items:center;      /* Centraliza horizontalmente */
  justify-content:center;
  gap:12px;
  margin-top:8px;
  text-align:center;
}

.modal-promo__note{
  text-align:center;
  max-width:260px;
}


  /* Botão verde específico da promoção */
  .btn--green{
    background:linear-gradient(90deg, #16a34a, #22c55e);
    color:#ffffff;
    border:none;
    box-shadow:0 10px 20px rgba(34,197,94,0.25);
  }
  .btn--green:hover{
    filter:brightness(1.03) saturate(1.05);
  }

  /* Parceiros - imagem única ocupando toda a linha */
.logos-shelf--4 img {
  grid-column: 1 / -1;   /* ocupa todas as colunas */
  width: 100%;
  max-width: 100%;
  height: auto;
}


  </style>
</head>
<body>

  <!-- Barra fixa inferior (sobe/some ao rolar) -->
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

    <!-- Wave inferior do hero -->
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

        <!--
        <article class="benefit">
          <img class="benefit__icon" src="/img/pet.png" alt="Assistência Pet" loading="lazy">
          <h3>Assistência Pet</h3>
          <p>Benefícios e apoio para seu melhor amigo.</p>
        </article> -->

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

  <!-- Planos (fundo branco com anéis) -->
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
   <!-- Depoimentos -->
  <section class="section section--pattern" id="depoimentos" aria-label="Depoimentos de clientes">
    <div class="container">
      <div class="section__head center">
        <h2 class="section__title">Quem usa, recomenda</h2>
        <p class="section__desc">Histórias reais de quem confia no Aviv+</p>
      </div>

      <div class="slider slider--out">
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

            <article class="quote">
              <img class="quote__avatar" src="/img/dep6.jpg" alt="Foto do Rafael" loading="lazy">
              <blockquote>“Como MEI, eu precisava de algo acessível. Com o plano empresarial cuido de mim e dos meus colaboradores.”</blockquote>
              <span class="quote__name">Rafael Costa • Microempreendedor</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep7.jpg" alt="Foto da Juliana" loading="lazy">
              <blockquote>“Usei a assistência residencial quando tive um problema elétrico. Atendimento rápido e organizado.”</blockquote>
              <span class="quote__name">Juliana Mello • Síndica</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep8.jpg" alt="Foto do Carlos" loading="lazy">
              <blockquote>“Consegui consulta popular com especialista por um valor que cabia no orçamento da família.”</blockquote>
              <span class="quote__name">Carlos Eduardo • Motorista de App</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep9.jpg" alt="Foto da Mariana" loading="lazy">
              <blockquote>“Ter um clube que junta telemedicina, assistência e descontos me deu mais segurança no dia a dia.”</blockquote>
              <span class="quote__name">Mariana Lopes • Consultora</span>
            </article>

            <article class="quote">
              <img class="quote__avatar" src="/img/dep10.jpg" alt="Foto do João" loading="lazy">
              <blockquote>“Coloquei o plano familiar para meus pais. Hoje sei que, se precisarem, terão atendimento 24h.”</blockquote>
              <span class="quote__name">João Batista • Aposentado</span>
            </article>
          </div>
        </div>
        <button class="slider__btn next" aria-label="Próximo">›</button>
        <div class="slider__dots" role="tablist" aria-label="Paginação de depoimentos"></div>
      </div>
    </div>
  </section>

  <!-- Parceiros -->
  <section class="section section--partners" id="parceiros" aria-label="Parceiros">
    <div class="container">
      <div class="section__head center">
        <h2 class="section__title">Parceiros</h2>
        <p class="section__desc">Algumas marcas que já fazem parte do ecossistema Aviv+</p>
      </div>

      <div class="logos-shelf logos-shelf--4">
        <img src="/img/partners.png" alt="Parceiros Aviv+" loading="lazy">
      </div>
    </div>
  </section>

  <!-- CTA final -->
  <section class="section cta--bar" id="contato" aria-label="Assine agora">
    <div class="cta__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 160" preserveAspectRatio="true" focusable="false">
        <path d="M0,0 C240,40 480,-40 720,0 C960,40 1200,-40 1440,0 L1440,160 L0,160 Z"></path>
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

  <!-- Footer -->
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
        <a href="mailto:contato@avivmais.com">contato@avivmais.com.br</a>
        <a href="/?r=site/contato">Políticas &amp; FAQ</a>
      </div>
    </div>

    <div class="footer__bottom">
      <div class="container center">
        <small>© <?= date('Y') ?> Aviv+. Todos os direitos reservados.</small>
      </div>
    </div>
  </footer>

  <!-- ======= MODAL (adicione antes de </body>) ======= -->
<div class="modal-backdrop" id="promo-modal" aria-hidden="true" role="dialog" aria-modal="true">
  <div class="modal-promo">
    <button class="modal-promo__close" type="button" aria-label="Fechar aviso" data-modal-close>
      ✕
    </button>

    <div class="modal-promo__badge">
      <span>Condição especial</span>
    </div>
    <center>
    <h2 class="modal-promo__title">Carências reduzidas para novos associados</h2></center>
    <p class="modal-promo__subtitle">
      Fale agora com nossa equipe e confira as condições de entrada com <strong>carências reduzidas</strong>* para o seu plano Aviv+.
    </p>

    <ul class="modal-promo__list">
      <li>Telemedicina 24h já nos primeiros dias de uso</li>
      <li>Condições diferenciadas para planos Familiar e Empresarial</li>
      <li>Atendimento humanizado para tirar todas as suas dúvidas</li>
    </ul>

    <div class="modal-promo__foot">
      <a
        class="btn btn--green btn--lg"
        href="https://wa.me/5511999999999?text=Ol%C3%A1%2C+gostaria+de+saber+mais+sobre+as+car%C3%AAncias+reduzidas+do+Aviv%2B."
        target="_blank"
        rel="noopener"
      >
        Entre em contato
      </a>

      <span class="modal-promo__note">
        *Carências e condições sujeitas à análise e confirmação em atendimento.
      </span>
    </div>
  </div>
</div>

<script>
  (function(){
    const modal = document.getElementById('promo-modal');
    if (!modal) return;

    const CLOSE_ATTR = 'data-modal-close';
    const STORAGE_KEY = 'aviv_promo_seen_v1';

    function openModal() {
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
    }

    function closeModal() {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      try {
        localStorage.setItem(STORAGE_KEY, '1');
      } catch(e) {}
    }

    // fecha ao clicar no X ou em qualquer elemento com data-modal-close
    modal.addEventListener('click', function(ev){
      const target = ev.target;
      if (!target) return;

      const isBackdrop = target === modal;
      const wantsClose = target.hasAttribute(CLOSE_ATTR);

      if (isBackdrop || wantsClose) {
        closeModal();
      }
    });

    // Esc tecla ESC
    document.addEventListener('keydown', function(ev){
      if (ev.key === 'Escape') {
        if (modal.classList.contains('is-open')) {
          closeModal();
        }
      }
    });

    // Abre automaticamente na entrada, se ainda não foi visto
    function maybeShow() {
      try {
        if (localStorage.getItem(STORAGE_KEY) === '1') return;
      } catch(e) {}

      // pequeno delay para não aparecer "seco"
      setTimeout(openModal, 1200);
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', maybeShow);
    } else {
      maybeShow();
    }
  })();
</script>

  <script>
  // Menu mobile
  const nav = document.getElementById('menu');
  const toggle = document.querySelector('.nav-toggle');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.getAttribute('data-open') === 'true';
      nav.setAttribute('data-open', String(!open));
      toggle.setAttribute('aria-expanded', String(!open));
    });
  }

  // Reveal on view
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

  // Slider Depoimentos
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
      const offset = index * viewportWidth;
      track.style.transform = `translateX(-${offset}px)`;
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

    prev.addEventListener('click', () => {
      index = (index - 1 + pages()) % pages();
      update();
    });
    next.addEventListener('click', () => {
      index = (index + 1) % pages();
      update();
    });

    window.addEventListener('resize', computePerView);
    computePerView();
  })();

  // Topbar: esconde ao rolar para baixo, mostra ao rolar para cima
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

  <script>
(function () {
  // 1) Reforço por CSS (caso scripts externos mexam no inline style)
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

  // 2) Função que oculta e tira do foco/árvore de acessibilidade
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
      // remove do tab order
      el.querySelectorAll('a,button,input,select,textarea,[tabindex]').forEach(function (n) {
        n.tabIndex = -1;
        n.setAttribute('aria-hidden', 'true');
      });
    });
  }

  // 3) Rodar agora e quando o DOM estiver pronto
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
