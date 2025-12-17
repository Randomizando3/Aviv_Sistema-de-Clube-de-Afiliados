<?php
// Admin • Dashboard — sem sidebar, largura igual ao Header, KPIs responsivos
?>
<section class="container admin dash-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Admin • Dashboard</h1>
      <p class="muted">Resumo rápido da operação.</p>
    </div>

    <!-- KPIs -->
    <div class="kpis-grid" style="margin-top:12px">
      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Usuários</div>
        <div class="kpi" id="kpi-users">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Assinaturas ativas</div>
        <div class="kpi" id="kpi-subs">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Planos</div>
        <div class="kpi" id="kpi-plans">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Benefícios</div>
        <div class="kpi" id="kpi-benefits">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">MRR (30d)</div>
        <div class="kpi" id="kpi-mrr">R$ —</div>
      </article>
    </div>

    <div id="dash-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
  </section>
</section>

<script>
/* =========================
   KPIs (como estava)
========================= */
(async function(){
  const moneyBR = (v)=> 'R$ ' + (Number(v||0)).toFixed(2).replace('.', ',');

  try{
    const r = await fetch('/?r=api/admin/stats/overview');
    let j; try { j = await r.json(); } catch(e){ j = { error:'Erro de resposta' }; }

    if(!r.ok){ throw new Error(j.error || 'Falha ao carregar'); }

    document.getElementById('kpi-users').textContent    = j.users ?? '—';
    document.getElementById('kpi-subs').textContent     = j.active_subs ?? '—';
    document.getElementById('kpi-plans').textContent    = j.plans ?? '—';
    document.getElementById('kpi-benefits').textContent = j.benefits ?? '—';
    document.getElementById('kpi-mrr').textContent      = moneyBR(j.mrr_30d);

  }catch(e){
    const box = document.getElementById('dash-alert');
    box.textContent = 'Erro: ' + (e.message || e);
    box.style.display = 'block';
    setTimeout(()=> box.style.display='none', 2000);
  }
})();

/* =========================
   FIX: Menu do Header/Admin (abre/fecha)
   - Funciona mesmo se o header estiver fora deste arquivo
   - Não conflita com outros clicks (fecha fora / ESC)
========================= */
(function initAdminMenuToggle(){
  // tenta achar o botão do menu (hamburger) e o container do menu
  const toggle =
    document.querySelector(
      [
        '[data-menu-toggle]',
        '[data-nav-toggle]',
        '#menu-toggle',
        '#nav-toggle',
        '#btn-menu',
        '.menu-toggle',
        '.nav-toggle',
        '.hamburger',
        'button[aria-controls="site-menu"]',
        'button[aria-controls="site-nav"]'
      ].join(',')
    );

  const menu =
    document.getElementById('site-menu') ||
    document.getElementById('site-nav')  ||
    document.querySelector(
      [
        '[data-menu]',
        '[data-nav]',
        '.site-menu',
        '.site-nav',
        '.nav-menu',
        '.header-menu',
        '.nav-links',
        '.mobile-menu',
        '.mobile-nav'
      ].join(',')
    );

  if (!toggle || !menu) return; // se não existir header nessa view, não faz nada

  const body = document.body;

  function isOpen(){
    return (
      menu.classList.contains('is-open') ||
      menu.classList.contains('open') ||
      menu.hasAttribute('data-open') ||
      body.classList.contains('menu-open')
    );
  }

  function open(){
    menu.classList.add('is-open','open');
    menu.setAttribute('data-open','');
    body.classList.add('menu-open');
    toggle.classList.add('is-open','open');
    toggle.setAttribute('aria-expanded','true');
  }

  function close(){
    menu.classList.remove('is-open','open');
    menu.removeAttribute('data-open');
    body.classList.remove('menu-open');
    toggle.classList.remove('is-open','open');
    toggle.setAttribute('aria-expanded','false');
  }

  function toggleMenu(){
    isOpen() ? close() : open();
  }

  // estado inicial
  toggle.setAttribute('aria-expanded', isOpen() ? 'true' : 'false');

  // clique no botão
  toggle.addEventListener('click', (e)=>{
    e.preventDefault();
    e.stopPropagation();
    toggleMenu();
  });

  // fecha ao clicar fora
  document.addEventListener('click', (e)=>{
    if (!isOpen()) return;
    if (menu.contains(e.target) || toggle.contains(e.target)) return;
    close();
  });

  // fecha com ESC
  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape') close();
  });

  // fecha ao clicar em link dentro do menu (boa UX no mobile)
  menu.addEventListener('click', (e)=>{
    const a = e.target.closest('a');
    if (!a) return;
    const href = (a.getAttribute('href') || '').trim();
    if (href && href !== '#') close();
  });

  // se virar desktop, fecha para não ficar preso aberto
  window.addEventListener('resize', ()=>{
    if (window.innerWidth > 980) close();
  });
})();
</script>

<style>
/* ===== Largura igual ao Header ===== */
.container.admin{
  width:min(92vw, var(--container)) !important;
  margin-inline:auto;
  padding-inline:0;
}

/* IMPORTANTE: evita o menu/dropdown do header ser “cortado” por algum wrapper */
.container.admin,
.container.admin .admin-main{
  overflow:visible;
}

/* ===== Cards / tipografia alinhados ao resto do painel ===== */
.glass-card{
  background:rgba(255,255,255,.92);
  border:1px solid rgba(15,23,42,.06);
  padding:16px 18px;
  border-radius:18px;
  color:var(--text, #111322);
  box-shadow:0 18px 40px rgba(15,23,42,.06);
}

.sect-title{
  margin:0 0 8px;
  font-weight:800;
  color:var(--text, #111322);
}

.muted{
  font-size:.9rem;
  color:var(--muted, #6b7280);
  opacity:1;
}

.alert{
  margin-top:12px;
  padding:10px 12px;
  border-radius:12px;
  background:#fff;
  border:1px solid rgba(15,23,42,.08);
  color:var(--text, #111322);
}

/* ===== KPIs ===== */
.kpis-grid{
  display:grid;
  grid-template-columns:repeat(5,minmax(0,1fr));
  gap:14px;
}

.kpi-card{
  display:grid;
  gap:6px;
  padding:16px;
}

.kpi-label{
  font-weight:600;
  color:var(--muted, #6b7280);
}

.kpi{
  font-weight:800;
  font-size:clamp(1.25rem, 1rem + 1.2vw, 1.7rem);
  line-height:1.1;
  letter-spacing:-0.4px;
  color:var(--text, #111322);
}

/* ===== Responsivo ===== */
@media (max-width:1200px){
  .kpis-grid{ grid-template-columns:repeat(3,1fr); }
}
@media (max-width:820px){
  .kpis-grid{ grid-template-columns:repeat(2,1fr); }
}
@media (max-width:520px){
  .kpis-grid{ grid-template-columns:1fr; }
}
</style>
