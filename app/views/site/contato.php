<?php $PAGE_BARE = true; ?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Contato • Aviv+</title>
  <meta name="description" content="Fale com o time Aviv+. WhatsApp, e-mail e formulário de contato." />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">

  <style>
    :root{
      --green:#A8E6CF; --blue:#5DADE2; --blue-dark:#3B8FC6; --brand-deep:#006400;
      --white:#FFFFFF; --ink:#2C3E50; --ink-70: color-mix(in oklab, var(--ink), #0000 30%);
      --shadow: 0 8px 30px rgba(0,0,0,.08); --radius: 16px; --radius-lg: 24px; --container: 1120px;
    }
    *{box-sizing:border-box} html,body{height:100%}
    body{margin:0;font-family:"Open Sans",system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial,"Noto Sans","Apple Color Emoji","Segoe UI Emoji",sans-serif;color:var(--ink);background:#fff;line-height:1.6;text-rendering:optimizeLegibility}
    body.site-page{min-height:100dvh;margin:0;color:#6B7784;background:white;position:relative;isolation:isolate}
    body.site-page::before{content:"";position:absolute;inset:0;z-index:-1;background:white}
    h1,h2,h3,h4{font-family:"Poppins",ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif;line-height:1.2;margin:0 0 .4rem;color:white;}
    h1{font-weight:700;font-size:clamp(1.8rem,2.4rem + 1vw,3.2rem)} h2{font-weight:700;font-size:clamp(1.4rem,1.2rem + 1vw,2rem)} h3{font-weight:700;font-size:1.125rem}
    p{margin:.25rem 0 .75rem}
    .container{width:100%;max-width:var(--container);margin:0 auto;padding:0 20px}
    .center{text-align:center} .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
    :focus-visible{outline:3px solid color-mix(in oklab, var(--blue), #000 15%);outline-offset:2px}

    .btn{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:.875rem 1.1rem;border-radius:999px;text-decoration:none;font-weight:800;box-shadow:var(--shadow);transition:.22s transform ease, .22s background ease, .22s color ease;border:none}
    .btn:hover{transform:translateY(-2px)}
    .btn--lg{padding:1rem 1.25rem;font-size:1.05rem}
    .btn--white{background:#fff;color:var(--ink)}
    .btn--ghost-white{background:transparent;border:2px solid #fff;color:#fff}
    .btn--blueGrad{background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff}

    .topbar{position:fixed;left:0;right:0;bottom:0;z-index:100;background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff;font-size:.95rem;transform:translateY(0);transition:transform .22s ease, opacity .22s ease;box-shadow:0 -8px 20px rgba(0,0,0,.08)}
    .topbar.is-hidden{transform:translateY(110%);opacity:0}
    .topbar .container{display:flex;align-items:center;justify-content:space-between;padding:1rem 20px}
    .topbar .container span{font-weight:800}
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
    .nav__login{white-space:nowrap}
    .nav-toggle{display:none;position:relative;width:42px;height:42px;border:0;background:#0000;border-radius:8px}
    .nav-toggle__bar,.nav-toggle__bar::before,.nav-toggle__bar::after{content:"";display:block;height:2px;background:var(--ink);width:22px;margin:auto;transition:.2s;position:relative}
    .nav-toggle__bar::before{position:absolute;inset:-6px 0 0 0}
    .nav-toggle__bar::after{position:absolute;inset:6px 0 0 0}
    @media (max-width: 900px){
      .nav{position:fixed;inset:72px 0 auto 0;flex-direction:column;gap:16px;background:#fff;padding:20px;transform:translateY(-120%);transition:.28s;box-shadow:0 12px 24px rgba(0,0,0,.1)}
      .nav[data-open="true"]{transform:translateY(0)}
      .nav-toggle{display:inline-grid;place-items:center}
    }

    .hero{position:relative;isolation:isolate;display:grid}
    .hero__bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2}
    .hero__overlay{position:absolute;inset:0;z-index:-1;background:rgba(0,0,0,.5)}
    .hero__grid{position:relative;z-index:1;display:grid;grid-template-columns:1fr;min-height:64svh;align-items:center;padding:64px 0 0;color:#fff}
    .hero__col--text{max-width:66.666%}
    @media (max-width: 960px){ .hero__col--text{max-width:100%} }
    .hero__title,.hero__subtitle{color:#fff;text-shadow:0 2px 18px rgba(0,0,0,.25)}
    .hero__wave{position:absolute;left:0;right:0;bottom:-1px;height:100px;z-index:1;pointer-events:none}
    .hero__wave svg{display:block;width:100%;height:100%}

    .section{position:relative; z-index:1; padding:56px 0}
    .section__head{margin-bottom:18px}
    .section__title{margin-bottom:.25rem}
    .section__desc{color:var(--ink-70)}
    .grid{display:grid;gap:16px}
    .card{background:#fff;border:1px solid #eaf1f6;border-radius:16px;box-shadow:var(--shadow);padding:18px}

    .form-card{position:relative;padding:26px;border-radius:var(--radius);background:color-mix(in oklab, #fff 92%, transparent);border:1px solid color-mix(in oklab, #fff 70%, #0000);box-shadow:var(--shadow);backdrop-filter:blur(8px)}
    .form-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(260px,1fr));gap:16px}
    .field{display:grid;gap:8px}
    .field label{font-weight:600;font-size:.95rem}
    .control{width:100%;border:1px solid rgba(0,0,0,.12);background:#fff;border-radius:12px;padding:14px 14px;font:600 0.95rem/1.25 "Open Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;color:var(--ink);transition: box-shadow .2s ease, border-color .2s ease, transform .05s ease;box-shadow: inset 0 1px 0 rgba(14,17,22,.04)}
    .control::placeholder{color:#8891a0;font-weight:400}
    .control:focus{outline:none;border-color: color-mix(in oklab, var(--blue) 70%, var(--blue-dark));box-shadow:0 0 0 4px color-mix(in oklab, var(--blue) 18%, transparent), inset 0 1px 0 rgba(14,17,22,.05)}
    textarea.control{min-height:132px;resize:vertical}
    .btn--modern{border-radius:999px;padding:14px 22px;font-weight:800;letter-spacing:.2px;border:0;cursor:pointer;background:linear-gradient(90deg, var(--blue), var(--blue-dark));color:#fff;box-shadow:0 10px 20px color-mix(in oklab, var(--blue) 20%, transparent);transition:transform .06s ease, box-shadow .2s ease, filter .2s ease}
    .btn--modern:hover{filter:saturate(1.1) brightness(1.02)}
    .btn--modern:active{transform:translateY(1px)}

    /* feedback de validação */
    .control[aria-invalid="true"]{ border-color:#d33; box-shadow:0 0 0 4px rgba(221,51,51,.12), inset 0 1px 0 rgba(14,17,22,.05) }
    .error-text{ color:#d33; font-size:.85rem }

    .form-status{margin-top:10px; padding:10px 12px; border-radius:12px; font-weight:700;border:1px solid #e9eef2; background:#fff; box-shadow: var(--shadow)}
    .form-status.success{ border-color:#caeeda; background:#f5fff9 }
    .form-status.error{ border-color:#f1d3d3; background:#fff6f6 }
    .form-status[hidden]{ display:none !important }

    .contact-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:20px;align-items:start}
    @media (max-width: 900px){ .contact-grid{grid-template-columns:1fr} }

    .cta--bar{position:relative; isolation:isolate; z-index:3; background:transparent; overflow:visible}
    .cta--bar::before{content:""; position:absolute; left:0; right:0; top:150px; bottom:0; background:var(--brand-deep); z-index:0}
    .cta__wave{position:absolute;left:0;right:0;top:0;height:270px;z-index:1;pointer-events:none}
    .cta__wave svg{display:block;width:100%;height:100%}
    .cta__wave path{fill:var(--brand-deep)}
    .cta__wrap{position:relative; z-index:3; display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap; color:#fff; margin-top:55px}

    .footer{position:relative; z-index:3; background:var(--brand-deep);color:#e8f3fb}
    .footer__grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:18px;padding:28px 0}
    .footer__brand img{display:block;margin-bottom:8px;height:6vh;max-height:56px;width:auto;filter:brightness(1.05)}
    .footer__links,.footer__contact{display:flex;flex-direction:column;gap:8px}
    .footer__links a,.footer__contact a{color:#e8f3fb;text-decoration:none}
    .footer__links a:hover,.footer__contact a:hover{color:#fff}
    .footer__bottom{border-top:1px solid rgba(255,255,255,.2);padding:12px 0;background:transparent}
    .footer__bottom small{opacity:.95}

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
<body class="site-page">

  <!-- Topbar -->
  <div class="topbar" id="topbar">
    <div class="container">
      <span>Clínico Geral 24h/7 • Especialidades seg–sex 09h–18h</span>
      <a class="topbar__cta" href="/?r=site/planos" aria-label="Assine Agora">Assine Agora</a>
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
  <section class="hero" aria-label="Cabeçalho">
    <img class="hero__bg" src="/img/hero.jpg" alt="Região dos Lagos - RJ" loading="eager">
    <div class="hero__overlay"></div>

    <div class="container hero__grid">
      <div class="hero__col hero__col--text">
        <h1 class="hero__title">Fale com a gente</h1>
        <p class="hero__subtitle">Tire dúvidas, peça ajuda ou envie uma proposta. Estamos por aqui!</p>
      </div>
    </div>

    <div class="hero__wave" aria-hidden="true">
      <svg viewBox="0 0 1440 120" preserveAspectRatio="none" focusable="false">
        <path d="M0,64 C240,120 480,0 720,48 C960,96 1200,96 1440,48 L1440,120 L0,120 Z" fill="#ffffff"></path>
      </svg>
    </div>
  </section>

  <!-- Conteúdo -->
  <section class="section" aria-label="Contato">
    <div class="container">
      <div class="contact-grid">
        <!-- Form -->
        <div class="form-card">
          <form id="contactForm" action="/?r=api/forms/contact" method="post" class="form-grid" novalidate>
            <div class="field">
              <label for="nome">Nome</label>
              <input class="control" id="nome" type="text" name="nome" placeholder="Seu nome" required>
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
              <label for="assunto">Assunto</label>
              <select class="control" id="assunto" name="assunto" required>
                <option value="">Selecione…</option>
                <option>Dúvida sobre planos</option>
                <option>Parcerias comerciais</option>
                <option>Suporte / Assinatura</option>
                <option>Outro</option>
              </select>
            </div>

            <div class="field" style="grid-column:1/-1">
              <label for="mensagem">Mensagem</label>
              <textarea class="control" id="mensagem" name="mensagem" rows="5" placeholder="Como podemos ajudar?" required></textarea>
            </div>

            <div style="grid-column:1/-1; display:flex; gap:12px; align-items:center; flex-wrap:wrap; justify-content:center">
              <button class="btn--modern" type="submit">Enviar mensagem</button>
              <a class="btn btn--blueGrad" href="/?r=site/planos">Ver Planos</a>
            </div>

            <div id="contactStatus" class="form-status" role="status" aria-live="polite" hidden></div>
          </form>
        </div>

        <!-- Canais -->
        <aside class="grid" style="grid-template-columns:1fr;gap:16px">
          <article class="card">
            <h3 style="display:flex;align-items:center;gap:8px">💬 WhatsApp</h3>
            <p class="section__desc">Atendimento rápido em horário comercial.</p>
            <a class="btn btn--blueGrad" href="/?r=site/contato">Abrir conversa</a>
          </article>

          <article class="card">
            <h3 style="display:flex;align-items:center;gap:8px">✉️ E-mail</h3>
            <p class="section__desc">Respostas em até 1 dia útil.</p>
            <a class="btn btn--blueGrad" href="mailto:contato@avivmais.com">contato@avivmais.com</a>
          </article>

          <article class="card">
            <h3 style="display:flex;align-items:center;gap:8px">🕒 Horários</h3>
            <p class="section__desc">Seg–Sex: 09h–18h<br>Telemedicina (Clínico): 24h/7</p>
          </article>
        </aside>
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
        <p style="color:white;">Assine agora e receba seu acesso ao clube.</p>
      </div>
      <div class="cta__actions">
        <a class="btn btn--white btn--lg" href="/?r=site/planos">Assinar</a>
        <a class="btn btn--ghost-white btn--lg" href="/?r=site/contato">Falar no WhatsApp</a>
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
        <a href="/?r=site/sobre">Sobre nós</a>
        <a href="/?r=site/parceiros">Parceiros</a>
        <a href="/?r=site/planos">Planos</a>
        <a href="/?r=site/contato" aria-current="page">Contato</a>
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

    // Topbar hide/show
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

    // Ano rodapé
    (function(){
      var y = document.getElementById('year');
      if (y) y.textContent = String(new Date().getFullYear());
    })();

    // ===== Envio do formulário de CONTATO via API =====
    (function(){
      var form   = document.getElementById('contactForm');
      if (!form) return;
      var status = document.getElementById('contactStatus');
      var submit = form.querySelector('button[type="submit"]');

      function setStatus(type, msg){
        if (!status) return;
        status.className = 'form-status ' + type;
        status.textContent = msg;
        status.hidden = false;
      }
      function clearStatus(){ if (status){ status.hidden = true; status.textContent=''; } }

      function setFieldError(id, msg){
        var input = form.querySelector('#' + id);
        if (!input) return;
        input.setAttribute('aria-invalid', 'true');
        var holder = input.closest('.field') || input.parentElement;
        var small = holder.querySelector('.error-text');
        if (!small){
          small = document.createElement('small');
          small.className = 'error-text';
          holder.appendChild(small);
        }
        small.textContent = String(msg || 'Campo inválido');
      }
      function clearFieldErrors(){
        form.querySelectorAll('[aria-invalid="true"]').forEach(function(el){ el.removeAttribute('aria-invalid'); });
        form.querySelectorAll('.error-text').forEach(function(el){ el.remove(); });
      }

      form.addEventListener('submit', function(e){
        e.preventDefault();
        clearStatus();
        clearFieldErrors();

        submit.disabled = true;
        submit.textContent = 'Enviando...';

        var fd = new FormData(form);

        fetch(form.action, {
          method: 'POST',
          body: fd,
          headers: { 'Accept': 'application/json' }
        })
        .then(function(r){ return r.json().catch(function(){ return {}; }).then(function(j){ return { ok:r.ok, status:r.status, data:j }; }); })
        .then(function(resp){
          if (!resp.ok){
            // aceita tanto {fields:{}} quanto {errors:{}}
            var errs = (resp.data && (resp.data.fields || resp.data.errors)) || null;
            if (errs && typeof errs === 'object'){
              Object.keys(errs).forEach(function(k){ setFieldError(k, errs[k]); });
              setStatus('error', resp.data.message || 'Corrija os campos destacados e tente novamente.');
            } else {
              setStatus('error', resp.data.message || 'Não foi possível enviar agora. Tente novamente.');
            }
            return;
          }
          setStatus('success', resp.data.message || 'Mensagem enviada com sucesso!');
          form.reset();
        })
        .catch(function(){
          setStatus('error', 'Falha de rede. Confira sua conexão e tente novamente.');
        })
        .finally(function(){
          submit.disabled = false;
          submit.textContent = 'Enviar mensagem';
        });
      });
    })();
  </script>

  <!-- Script para ocultar topnav/ad do tema base, se houver -->
  <script>
  (function () {
    var css = `
      header.topnav[data-topnav],
      .adbar-wrap#ad-header468-outer,
      #ad-header468 { display:none!important; visibility:hidden!important; pointer-events:none!important; }
    `;
    var style = document.createElement('style'); style.type='text/css'; style.appendChild(document.createTextNode(css)); document.head.appendChild(style);
    var selectors = ['header.topnav[data-topnav]','.adbar-wrap#ad-header468-outer','#ad-header468'];
    function hideAll(root) {
      (root||document).querySelectorAll(selectors.join(',')).forEach(function (el) {
        el.setAttribute('hidden',''); el.setAttribute('aria-hidden','true');
        el.style.setProperty('display','none','important');
        el.style.setProperty('visibility','hidden','important');
        el.style.setProperty('pointer-events','none','important');
        el.querySelectorAll('a,button,input,select,textarea,[tabindex]').forEach(function (n) { n.tabIndex=-1; n.setAttribute('aria-hidden','true'); });
      });
    }
    if (document.readyState==='loading') document.addEventListener('DOMContentLoaded', function(){ hideAll(); }); else hideAll();
    var mo=new MutationObserver(function(muts){ var needs=false; for (var i=0;i<muts.length;i++){ var m=muts[i]; if (m.type==='childList'){ m.addedNodes.forEach(function(n){ if(n.nodeType===1) hideAll(n); }); } else if (m.type==='attributes'){ needs=true; } } if (needs) hideAll(); });
    mo.observe(document.documentElement,{subtree:true,childList:true,attributes:true,attributeFilter:['class','style']});
  })();
  </script>
</body>
</html>
