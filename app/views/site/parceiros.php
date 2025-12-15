<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Parceiros • Aviv+</title>
  <meta name="description" content="Conheça nossa rede de parceiros e cadastre seu negócio para oferecer benefícios aos assinantes." />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --green:#A8E6CF; --blue:#5DADE2; --blue-dark:#3B8FC6; --brand-deep:#006400;
      --white:#FFFFFF; --ink:#2C3E50; --ink-70: color-mix(in oklab, var(--ink), #0000 30%);
      --shadow: 0 8px 30px rgba(0,0,0,.08); --radius: 16px; --radius-lg: 24px; --container: 1120px;
      --whats:#25D366;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font-family:"Open Sans",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--ink);background:#fff;line-height:1.6}
    h1,h2,h3,h4{font-family:"Poppins",ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;line-height:1.2;margin:0 0 .4rem;color:var(--ink)}
    h1{font-weight:700;font-size:clamp(1.8rem,2.4rem + 1vw,3.2rem)}
    h2{font-weight:700;font-size:clamp(1.4rem,1.2rem + 1vw,2rem)}
    h3{font-weight:700;font-size:1.125rem}
    p{margin:.25rem 0 .75rem}
    .container{width:100%;max-width:var(--container);margin:0 auto;padding:0 20px}
    .center{text-align:center}
    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
    :focus-visible{outline:3px solid color-mix(in oklab, var(--blue), #000 15%);outline-offset:2px}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.875rem 1.1rem;border-radius:999px;text-decoration:none;font-weight:800;box-shadow:var(--shadow);transition:.22s transform ease, .22s background ease, .22s color ease;border:none}
    .btn:hover{transform:translateY(-2px)}
    .btn--blueGrad{background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff}
    .btn--white{background:#fff;color:var(--ink)}
    .btn--ghost-white{background:transparent;border:2px solid #fff;color:#fff}
    .btn--lg{padding:1rem 1.25rem;font-size:1.05rem}
    .btn--sm{padding:.6rem .9rem;font-size:.95rem}
    .topbar{position:fixed;left:0;right:0;bottom:0;z-index:100;background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff;font-size:.95rem;transform:translateY(0);transition:transform .22s ease, opacity .22s ease;box-shadow:0 -8px 20px rgba(0,0,0,.08)}
    .topbar.is-hidden{transform:translateY(110%);opacity:0}
    .topbar .container{display:flex;align-items:center;justify-content:space-between;padding:1rem 20px}
    .topbar__cta{color:#fff;border:2px solid #ffffff66;border-radius:999px;padding:.5rem 1rem;text-decoration:none}
    .topbar__cta:hover{background:#ffffff14}
    .header{position:sticky;top:0;z-index:90;backdrop-filter:saturate(140%) blur(8px);background:linear-gradient(180deg, #ffffffee, #ffffffcc 70%, #ffffff00);border-bottom:1px solid #e9eef2}
    .header__wrap{display:flex;align-items:center;gap:16px;justify-content:space-between;height:72px}
    .brand img{display:block;height:auto;max-height:52px}
    .header__right{display:flex;align-items:center;gap:16px}
    .nav{display:flex;gap:20px;align-items:center}
    .nav a{color:var(--ink);text-decoration:none;font-weight:700}
    .nav a:hover{color:#0b6aa1}
    .header__actions{display:flex;align-items:center;gap:10px}
    .nav-toggle{display:none;position:relative;width:42px;height:42px;border:0;background:#0000;border-radius:8px}
    .nav-toggle__bar,.nav-toggle__bar::before,.nav-toggle__bar::after{content:"";display:block;height:2px;background:var(--ink);width:22px;margin:auto;transition:.2s;position:relative}
    .nav-toggle__bar::before{position:absolute;inset:-6px 0 0 0}
    .nav-toggle__bar::after{position:absolute;inset: 6px 0 0 0}
    @media (max-width:900px){.nav{position:fixed;inset:72px 0 auto 0;flex-direction:column;gap:16px;background:#fff;padding:20px;transform:translateY(-120%);transition:.28s;box-shadow:0 12px 24px rgba(0,0,0,.1)}.nav[data-open="true"]{transform:translateY(0)}.nav-toggle{display:inline-grid;place-items:center}.section{padding:48px 0}}
    @media (max-width:560px){body{padding-bottom:88px}.section{padding:40px 0}}
    .hero{position:relative;isolation:isolate;display:grid}
    .hero__bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2}
    .hero__overlay{position:absolute;inset:0;z-index:-1;background:rgba(0,0,0,.5)}
    .hero__grid{position:relative;z-index:1;display:grid;grid-template-columns:1fr;min-height:56svh;align-items:center;padding:64px 0 0;color:#fff}
    .hero__col--text{max-width:66.666%}
    @media (max-width:960px){.hero__col--text{max-width:100%}}
    .hero__title,.hero__subtitle{color:#fff;text-shadow:0 2px 18px rgba(0,0,0,.25)}
    .hero__subtitle{max-width:720px;opacity:.98}
    .hero__wave{position:absolute;left:0;right:0;bottom:-1px;height:100px;z-index:1;pointer-events:none}
    .hero__wave svg{display:block;width:100%;height:100%}
    .section{position:relative;z-index:1;padding:56px 0}
    .section__head{margin-bottom:18px}
    .section__title{margin-bottom:.25rem}
    .section__desc{color:var(--ink-70)}
    .section__title--light{color:#fff}
    .section__desc--light{color:#ffffffcc}
    .section__head--center{text-align:center;max-width:860px;margin:0 auto 28px}
    .section--alt{background:#f8fbff}
    .ticklist{list-style:none;margin:12px 0 16px;padding:0;display:grid;gap:10px}
    .ticklist li{position:relative;padding-left:28px;font-weight:700}
    .ticklist li::before{content:"✓";position:absolute;left:0;top:0;font-weight:800;color:#2FB67F}

    /* ===== AJUSTE PARA PARTNERS.PNG OCUPAR A LINHA TODA ===== */
    .logos-shelf--4{
      display:grid;
      grid-template-columns:repeat(4, minmax(160px,1fr));
      gap:18px 24px;
      align-items:center;
      justify-items:center;
    }
    .logos-shelf--4 img{
      width:100%;
      height:auto;
      display:block;
      filter:grayscale(100%) contrast(85%) brightness(95%);
      transition:filter .2s ease, transform .2s ease;
      border-radius:12px;
    }
    .logos-shelf--4 img:hover{filter:none;transform:translateY(-2px)}
    .logos-shelf--4 img.partners-full{
      grid-column:1 / -1;
      max-width:100%;
      filter:none;
      transform:none;
    }
    .logos-shelf--4 img.partners-full:hover{transform:none}
    @media (max-width:860px){.logos-shelf--4{grid-template-columns:repeat(2, minmax(140px,1fr))}}

    .steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px}
    .step{padding:16px;border:1px solid rgba(14,17,22,.12);border-radius:14px;background:#fff;box-shadow:0 2px 8px rgba(14,17,22,.04)}
    .step h3{margin:0}
    .step-chip{width:40px;height:40px;border-radius:999px;display:grid;place-items:center;font-weight:800;color:#fff;background:linear-gradient(90deg,var(--blue),var(--blue-dark));box-shadow:0 6px 14px color-mix(in oklab, var(--blue-dark) 24%, transparent)}
    .form-card{position:relative;padding:26px;border-radius:16px;background:color-mix(in oklab,#fff 92%,#0000);border:1px solid color-mix(in oklab,#fff 70%,#0000);box-shadow:0 8px 24px rgba(0,0,0,.10), 0 2px 8px rgba(0,0,0,.06);backdrop-filter:blur(8px)}
    .form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px}
    .field{display:grid;gap:8px}
    .field label{font-weight:600;font-size:.95rem}
    .control{width:100%;border:1px solid rgba(0,0,0,.12);background:#fff;border-radius:12px;padding:14px 14px;font:600 .95rem/1.25 "Open Sans",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:var(--ink);transition: box-shadow .2s ease, border-color .2s ease, transform .05s ease;box-shadow: inset 0 1px 0 rgba(14,17,22,.04)}
    .control::placeholder{color:#8891a0;font-weight:400}
    .control:focus{outline:none;border-color: color-mix(in oklab, var(--blue) 70%, var(--blue-dark));box-shadow:0 0 0 4px color-mix(in oklab, var(--blue) 18%, transparent), inset 0 1px 0 rgba(14,17,22,.05)}
    textarea.control{min-height:132px;resize:vertical}
    .btn--modern{border-radius:999px;padding:14px 22px;font-weight:800;letter-spacing:.2px;border:0;cursor:pointer;background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff;box-shadow:0 10px 20px color-mix(in oklab, var(--blue) 20%, transparent);transition:transform .06s ease, box-shadow .2s ease, filter .2s ease}
    .btn--modern:hover{filter:saturate(1.1) brightness(1.02)}
    .btn--modern:active{transform:translateY(1px)}
    .control[aria-invalid="true"]{border-color:#d33;box-shadow:0 0 0 4px rgba(221,51,51,.12), inset 0 1px 0 rgba(14,17,22,.05)}
    .error-text{color:#d33;font-size:.85rem}
    .form-status{margin-top:10px;padding:10px 12px;border-radius:12px;font-weight:700;border:1px solid #e9eef2;background:#fff;box-shadow:var(--shadow)}
    .form-status.success{border-color:#caeeda;background:#f5fff9}
    .form-status.error{border-color:#f1d3d3;background:#fff6f6}
    .form-status[hidden]{display:none!important}
    .cta--bar{position:relative;isolation:isolate;z-index:3;background:transparent;overflow:visible}
    .cta--bar::before{content:"";position:absolute;left:0;right:0;top:150px;bottom:0;background:var(--brand-deep);z-index:0}
    .cta--bar::after{content:"";position:absolute;left:0;right:0;bottom:0;height:1px;background:rgba(255,255,255,.85);z-index:2}
    .cta__wave{position:absolute;left:0;right:0;top:0;height:270px;z-index:1;pointer-events:none}
    .cta__wave svg{display:block;width:100%;height:100%}
    .cta__wave path{fill:var(--brand-deep)}
    .cta__wrap{position:relative;z-index:3;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;color:#fff;margin-top:55px}
    .footer{position:relative;z-index:3;background:var(--brand-deep);color:#e8f3fb}
    .footer__grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:18px;padding:28px 0}
    .footer__brand img{display:block;margin-bottom:8px;height:6vh;max-height:56px;width:auto;filter:brightness(1.05)}
    .footer__links,.footer__contact{display:flex;flex-direction:column;gap:8px}
    .footer__links a,.footer__contact a{color:#e8f3fb;text-decoration:none}
    .footer__links a:hover,.footer__contact a:hover{color:#fff}
    .footer__bottom{border-top:1px solid rgba(255,255,255,.2);padding:12px 0;background:transparent}
    .footer__bottom small{opacity:.95}

    /* ===== CONTACT STRIP (PARCEIROS) ===== */
    .partner-contact{
      margin:10px auto 18px;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      flex-wrap:wrap;
      color:var(--ink);
    }
    .partner-contact strong{font-family:"Poppins",system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .partner-pill{
      display:inline-flex;
      align-items:center;
      gap:10px;
      padding:.6rem .9rem;
      border-radius:999px;
      border:1px solid rgba(14,17,22,.12);
      background:#fff;
      box-shadow:0 6px 16px rgba(14,17,22,.06);
      text-decoration:none;
      color:var(--ink);
      font-weight:800;
      transition:transform .15s ease, box-shadow .2s ease, border-color .2s ease;
      max-width:100%;
    }
    .partner-pill:hover{
      transform:translateY(-1px);
      border-color:color-mix(in oklab, var(--blue) 55%, #0000);
      box-shadow:0 10px 22px rgba(14,17,22,.10);
    }
    .partner-pill svg{flex:0 0 auto}
    .partner-pill__text{
      display:inline-block;
      line-height:1.1;
      white-space:nowrap;
    }
    @media (max-width:420px){
      .partner-pill__text{white-space:normal}
    }

    /* ===== WHATSAPP PILL VISUAL ===== */
    .partner-pill--whats{
      border-color:color-mix(in oklab, var(--whats) 30%, #0000);
    }
    .partner-pill--whats:hover{
      border-color:color-mix(in oklab, var(--whats) 55%, #0000);
      box-shadow:0 10px 22px color-mix(in oklab, var(--whats) 18%, transparent);
    }
  </style>
</head>
<body>
  <div class="topbar" id="topbar">
    <div class="container">
      <span>Clínico Geral 24h/7 • Especialidades seg–sex 09h–18h</span>
      <a class="topbar__cta" href="/?r=auth/register" aria-label="Assine Agora">Assine Agora</a>
    </div>
  </div>

  <header class="header" id="topo">
    <div class="container header__wrap">
      <a class="brand" href="/?r=site/home" aria-label="Página inicial">
        <img src="/img/logo.png" alt="Aviv+">
      </a>
      <div class="header__right">
        <nav class="nav" id="menu">
          <a href="/?r=site/sobre">Sobre nós</a>
          <a href="/?r=site/parceiros" aria-current="page">Parceiros</a>
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

  <section class="hero" aria-label="Cabeçalho de parceiros">
    <img class="hero__bg" src="/img/hero.jpg" alt="Região dos Lagos - RJ" loading="eager">
    <div class="hero__overlay"></div>
    <div class="container hero__grid">
      <div class="hero__col hero__col--text">
        <h1 class="hero__title">Parceiros</h1>
        <p class="hero__subtitle">Cresça com o Aviv+: mais fluxo qualificado, relacionamento contínuo e presença em uma rede em expansão.</p>
      </div>
    </div>
    <div class="hero__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none" focusable="false">
        <path d="M0,64 C240,120 480,0 720,48 C960,96 1200,96 1440,48 L1440,120 L0,120 Z" fill="#ffffff"></path>
      </svg>
    </div>
  </section>

  <section class="section" aria-label="Vantagens para parceiros">
    <div class="container">
      <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:28px;align-items:center">
        <div>
          <h2>Por que se conectar ao Aviv+</h2>
          <p class="section__desc" style="margin-bottom:12px">Parcerias que valorizam o seu negócio e entregam benefícios reais para nossos assinantes.</p>
          <ul class="ticklist" style="margin-top:8px">
            <li>Demanda recorrente com divulgação local</li>
            <li>Zero custo de adesão e suporte dedicado</li>
            <li>Presença em campanhas e materiais</li>
            <li>Relacionamento de longo prazo com a comunidade</li>
          </ul>
          <div style="margin-top:16px"><a class="btn btn--blueGrad" href="#cadastro-parceiro">Quero ser parceiro</a></div>
        </div>
        <figure style="margin:0"><img src="/img/hero.jpg" alt="Rede de parceiros Aviv+" style="width:100%;height:auto;border-radius:16px"></figure>
      </div>
    </div>
  </section>

  <section class="section section--alt" aria-label="Logomarcas de parceiros">
    <div class="container">
      <div class="section__head section__head--center">
        <h2 class="section__title">Rede de parceiros</h2>
        <p class="section__desc">Algumas marcas que já fazem parte do ecossistema Aviv+</p>
      </div>
      <div class="logos-shelf--4">
        <img class="partners-full" src="/img/partners.png" alt="Rede de parceiros Aviv+" loading="lazy">
      </div>
    </div>
  </section>

  <section class="section" aria-label="Como funciona">
    <div class="container">
      <div class="section__head section__head--center">
        <h2 class="section__title">Como funciona</h2>
        <p class="section__desc">Processo simples — sem burocracia.</p>
      </div>
      <div class="steps">
        <div class="step"><div style="display:flex;align-items:center;gap:12px;margin-bottom:10px"><span class="step-chip">1</span><h3>Cadastro</h3></div><p class="section__desc">Envie seus dados e condições comerciais. Validamos e ativamos seu perfil.</p></div>
        <div class="step"><div style="display:flex;align-items:center;gap:12px;margin-bottom:10px"><span class="step-chip">2</span><h3>Divulgação</h3></div><p class="section__desc">Sua marca passa a aparecer nas comunicações e na área do cliente.</p></div>
        <div class="step"><div style="display:flex;align-items:center;gap:12px;margin-bottom:10px"><span class="step-chip">3</span><h3>Atendimento</h3></div><p class="section__desc">Receba clientes com a carteirinha e acompanhe os resultados.</p></div>
      </div>
    </div>
  </section>

  <section class="section section--alt" id="cadastro-parceiro" aria-label="Cadastro de parceiro">
    <div class="container">
      <div class="section__head section__head--center">
        <h2 class="section__title">Seja nosso parceiro</h2>
        <p class="section__desc">Cadastre seu negócio e receba contato da nossa equipe</p>

        <!-- ===== CONTATO ABAIXO DO SUBTÍTULO (ANTES DO FORM) ===== -->
        <div class="partner-contact" aria-label="Contato comercial para empresas">
          <strong>Contato comercial:</strong>

          <!-- WhatsApp: abre chat -->
          <!-- Observação: o número recebido está truncado (+55 21 99943-8907). Assim que você tiver os dígitos finais, substitua no TEXTO e no href (wa.me) -->
          <a class="partner-pill partner-pill--whats" href="https://wa.me/5521999438907" target="_blank" rel="noopener" aria-label="Chamar no WhatsApp +55 21 99943-">
            <!-- WhatsApp SVG (verde) -->
            <svg width="18" height="18" viewBox="0 0 32 32" aria-hidden="true">
              <path fill="var(--whats)" d="M19.11 17.45c-.27-.14-1.59-.79-1.84-.88-.25-.09-.44-.14-.62.14-.18.27-.71.88-.87 1.06-.16.18-.32.2-.59.07-.27-.14-1.14-.42-2.17-1.35-.8-.71-1.35-1.6-1.5-1.87-.16-.27-.02-.42.12-.55.12-.12.27-.32.41-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.5-.85-2.06-.22-.53-.45-.46-.62-.47-.16-.01-.34-.01-.53-.01-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.66 1.12 2.84.14.18 1.93 2.95 4.69 4.14.66.29 1.17.46 1.57.59.66.21 1.26.18 1.74.11.53-.08 1.59-.65 1.81-1.28.22-.62.22-1.15.16-1.28-.07-.12-.25-.2-.52-.34z"/>
              <path fill="var(--whats)" d="M16.03 3.2c-7.08 0-12.83 5.75-12.83 12.83 0 2.25.59 4.36 1.62 6.2L3.2 28.8l6.72-1.58c1.75.95 3.76 1.49 5.9 1.49 7.08 0 12.83-5.75 12.83-12.83S23.11 3.2 16.03 3.2zm0 23.16c-2.06 0-3.97-.6-5.58-1.63l-.4-.25-3.99.94.97-3.88-.26-.4a10.32 10.32 0 0 1-1.67-5.61c0-5.7 4.64-10.34 10.34-10.34s10.34 4.64 10.34 10.34-4.64 10.34-10.34 10.34z"/>
            </svg>
            <span class="partner-pill__text">+55 21 99943-8907</span>
          </a>

          <!-- Email -->
          <a class="partner-pill" href="mailto:comercial@avivmais.com.br" aria-label="Enviar e-mail para comercial@avivmais.com.br">
            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 4h16v16H4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="m22 6-10 7L2 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span class="partner-pill__text">comercial@avivmais.com.br</span>
          </a>
        </div>
        <!-- ===== /CONTATO ===== -->

      </div>

      <div class="form-card">
        <form id="partnerForm" action="/?r=api/forms/partner" method="post" class="form-grid" novalidate>
          <div class="field">
            <label for="nome">Nome completo</label>
            <input class="control" id="nome" type="text" name="nome" placeholder="Seu nome" required>
          </div>
          <div class="field">
            <label for="empresa">Empresa / Estabelecimento</label>
            <input class="control" id="empresa" type="text" name="empresa" placeholder="Nome da empresa" required>
          </div>
          <div class="field">
            <label for="whats">WhatsApp</label>
            <input class="control" id="whats" type="tel" name="whats" inputmode="tel" placeholder="(00) 00000-0000" required>
          </div>
          <div class="field">
            <label for="email">E-mail</label>
            <input class="control" id="email" type="email" name="email" placeholder="voce@exemplo.com" required>
          </div>
          <div class="field">
            <label for="categoria">Categoria de parceria</label>
            <select class="control" id="categoria" name="categoria" required>
              <option value="">Selecione…</option>
              <option>Farmácia</option>
              <option>Clínica/Exames</option>
              <option>Assistência Residencial</option>
              <option>Seguros/Proteções</option>
              <option>Outros</option>
            </select>
          </div>
          <div class="field" style="grid-column:1/-1">
            <label for="mensagem">Mensagem (opcional)</label>
            <textarea class="control" id="mensagem" name="mensagem" rows="4" placeholder="Conte resumidamente sua proposta"></textarea>
          </div>
          <div style="grid-column:1/-1;display:flex;gap:12px;align-items:center;flex-wrap:wrap;justify-content:center">
            <button class="btn--modern" type="submit">Enviar cadastro</button>
          </div>
          <div id="partnerStatus" class="form-status" role="status" aria-live="polite" hidden></div>
        </form>
      </div>
    </div>
  </section>

  <section class="section cta--bar" aria-label="Assine agora">
    <div class="cta__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 270" preserveAspectRatio="none" focusable="false">
        <path d="M0,270 L0,60 C 220,0 440,120 720,60 C 1000,0 1220,120 1440,60 L1440,270 Z"></path>
      </svg>
    </div>
    <div class="container cta__wrap">
      <div>
        <h2 class="section__title section__title--light">Vamos crescer juntos?</h2>
        <p class="section__desc section__desc--light">Seja parte da rede que cuida das famílias da Região dos Lagos.</p>
      </div>
      <div class="cta__actions">
        <a class="btn btn--white btn--lg" href="#cadastro-parceiro">Quero ser parceiro</a>
        <a class="btn btn--ghost-white btn--lg" href="/?r=site/contato">Fale com a equipe</a>
      </div>
    </div>
  </section>

  <footer class="footer" aria-label="Rodapé">
    <div class="container footer__grid">
      <div class="footer__brand">
        <img src="/img/logowhite.png" alt="Aviv+">
        <p>Bem-estar e proteção para todas as famílias.</p>
      </div>
      <nav class="footer__links" aria-label="Links rápidos">
        <strong>Links</strong>
        <a href="/?r=site/sobre">Sobre nós</a>
        <a href="/?r=site/parceiros" aria-current="page">Parceiros</a>
        <a href="/?r=site/planos">Planos</a>
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
      var nav=document.getElementById('menu'); var toggle=document.querySelector('.nav-toggle');
      if(!toggle||!nav) return;
      toggle.addEventListener('click',function(){
        var open=nav.getAttribute('data-open')==='true';
        nav.setAttribute('data-open', open?'false':'true');
        toggle.setAttribute('aria-expanded', open?'false':'true');
      });
    })();

    // Topbar (hide/show)
    (function(){
      var bar=document.getElementById('topbar'); if(!bar) return;
      var lastY=window.scrollY, ticking=false;
      function onScroll(){
        var y=window.scrollY, goingDown=y>lastY+6, goingUp=y<lastY-6;
        if(goingDown && y>60) bar.classList.add('is-hidden'); else if(goingUp) bar.classList.remove('is-hidden');
        lastY=y; ticking=false;
      }
      window.addEventListener('scroll', function(){
        if(!ticking){ window.requestAnimationFrame(onScroll); ticking=true; }
      }, {passive:true});
    })();

    // Ano rodapé
    (function(){ var y=document.getElementById('year'); if(y) y.textContent=String(new Date().getFullYear()); })();

    // ===== Envio do formulário de PARCEIROS via API =====
    (function(){
      var form   = document.getElementById('partnerForm'); if(!form) return;
      var status = document.getElementById('partnerStatus');
      var submit = form.querySelector('button[type="submit"]');

      function setStatus(type,msg){
        if(!status) return;
        status.className='form-status '+type;
        status.textContent=msg;
        status.hidden=false;
        status.scrollIntoView({behavior:'smooth', block:'center'});
      }
      function clearStatus(){ if(status){ status.hidden=true; status.textContent=''; } }
      function setFieldError(id,msg){
        var input=form.querySelector('#'+id); if(!input) return;
        input.setAttribute('aria-invalid','true');
        var holder=input.closest('.field')||input.parentElement;
        var small=holder.querySelector('.error-text');
        if(!small){ small=document.createElement('small'); small.className='error-text'; holder.appendChild(small); }
        small.textContent=String(msg||'Campo inválido');
      }
      function clearFieldErrors(){
        form.querySelectorAll('[aria-invalid="true"]').forEach(function(el){ el.removeAttribute('aria-invalid'); });
        form.querySelectorAll('.error-text').forEach(function(el){ el.remove(); });
      }

      form.addEventListener('submit', function(e){
        e.preventDefault();
        clearStatus(); clearFieldErrors();
        submit.disabled=true; submit.textContent='Enviando...';

        var fd=new FormData(form);

        fetch(form.action, {
          method:'POST',
          body:fd,
          headers:{ 'Accept':'application/json' }
        })
        .then(function(r){
          return r.json().catch(function(){ return {}; })
            .then(function(j){ return { ok:r.ok, status:r.status, data:j }; });
        })
        .then(function(resp){
          if(!resp.ok){
            var errs=(resp.data && (resp.data.fields || resp.data.errors)) || null;
            if(errs && typeof errs==='object'){
              Object.keys(errs).forEach(function(k){ setFieldError(k, errs[k]); });
              setStatus('error', resp.data.error || resp.data.message || 'Corrija os campos destacados e tente novamente.');
            }else{
              setStatus('error', resp.data.error || resp.data.message || 'Não foi possível enviar agora. Tente novamente.');
            }
            return;
          }
          setStatus('success', resp.data.message || 'Cadastro enviado com sucesso! Em breve nossa equipe entra em contato.');
          form.reset();
        })
        .catch(function(){
          setStatus('error','Falha de rede. Confira sua conexão e tente novamente.');
        })
        .finally(function(){
          submit.disabled=false;
          submit.textContent='Enviar cadastro';
        });
      });
    })();
  </script>
</body>
</html>
