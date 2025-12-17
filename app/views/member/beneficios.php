<?php
// Member • Benefícios — sem sidebar, largura igual ao Header, cards responsivos filtrados pelo plano do usuário
?>
<section class="container member" style="margin-top:18px">
  <main class="member-main">
    <div class="glass-card">

      <!-- Título + plano (pill pequeno) -->
      <div class="title-row">
        <h1 class="sect-title">Benefícios</h1>
        <div class="pill pill--sm" id="my-plan-pill" style="display:none">Plano: —</div>
      </div>

      <!-- Toolbar: Filtro (esquerda, maior) + Busca (direita, menor) -->
      <div class="bens-toolbar">
        <div id="specialty-filter" class="toolbar-filter"><!-- render via JS --></div>
        <div class="search-wrap">
          <label class="sr-only" for="bq">Buscar</label>
          <input id="bq" type="search" placeholder="Buscar benefícios ou parceiros…">
        </div>
      </div>

      <!-- Aviso/CTA para quem está no Free -->
      <div id="free-cta" class="glass-card muted" style="display:none; margin-top:10px">
        Você está no plano <strong>Free</strong>. Alguns benefícios podem estar indisponíveis.
        <a class="cta-up" href="/?r=site/planos">Conheça os planos</a>
      </div>

      <!-- Grid -->
      <div id="benef-grid" class="benef-grid"></div>
    </div>
  </main>
</section>

<script>
/* =========================================================
   MENU (DASHBOARD) — IGUAL AO DASHBOARD (sem tirar nem por)
   CORREÇÃO:
   1) não abre sozinho ao carregar
   2) se estiver aberto, FECHA corretamente (evita duplo bind)
   ========================================================= */
(function(){
  function init(){
    var header =
      document.querySelector('header.topnav[data-topnav]') ||
      document.querySelector('header.topnav') ||
      document.querySelector('header.header') ||
      document.querySelector('header');

    if (!header) return false;

    var nav =
      header.querySelector('#menu') ||
      header.querySelector('nav') ||
      document.getElementById('menu');

    if (!nav) return false;

    // evita bind duplicado (interval + mutation observer podem "ganhar corrida")
    if (nav.dataset.dashMenuBound === '1') {
      // ainda força estado inicial fechado para não nascer aberto
      nav.setAttribute('data-open','false');
      nav.classList.remove('is-open','open','show','active');
      document.documentElement.classList.remove('menu-open');
      document.body.classList.remove('menu-open');
      var bd = document.querySelector('.menu-backdrop');
      if (bd) bd.classList.remove('is-on');
      return true;
    }
    nav.dataset.dashMenuBound = '1';

    if (!nav.id) nav.id = 'dash-menu';

    var btn =
      header.querySelector('.nav-toggle') ||
      header.querySelector('[data-nav-toggle]');

    if (!btn){
      btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'nav-toggle';
      btn.setAttribute('aria-controls', nav.id);
      btn.setAttribute('aria-expanded', 'false');
      btn.innerHTML = '<span class="nav-toggle__bar"></span><span class="sr-only">Abrir menu</span>';

      var slot =
        header.querySelector('.header__actions') ||
        header.querySelector('.topnav__actions') ||
        header.querySelector('.actions') ||
        header.querySelector('.right') ||
        header;
      slot.appendChild(btn);
    } else {
      btn.setAttribute('aria-controls', nav.id);
      if (!btn.getAttribute('aria-expanded')) btn.setAttribute('aria-expanded', 'false');
    }

    var backdrop = document.querySelector('.menu-backdrop');
    if (!backdrop){
      backdrop = document.createElement('div');
      backdrop.className = 'menu-backdrop';
      document.body.appendChild(backdrop);
    }

    // guard extra (se o header re-renderizar, impede listeners duplicados no mesmo btn)
    if (btn.dataset.dashMenuBound === '1') return true;
    btn.dataset.dashMenuBound = '1';

    function isOpen(){
      // considera aberto se html tem classe OU nav está data-open=true
      return document.documentElement.classList.contains('menu-open') || nav.getAttribute('data-open') === 'true';
    }

    function open(){
      nav.setAttribute('data-open','true');
      btn.setAttribute('aria-expanded','true');
      document.documentElement.classList.add('menu-open');
      backdrop.classList.add('is-on');
    }

    function close(){
      nav.setAttribute('data-open','false');
      nav.classList.remove('is-open','open','show','active');
      btn.setAttribute('aria-expanded','false');
      document.documentElement.classList.remove('menu-open');
      document.body.classList.remove('menu-open');
      backdrop.classList.remove('is-on');
    }

    // estado inicial: sempre fechado
    close();

    btn.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      if (isOpen()) close();
      else open();
    });

    backdrop.addEventListener('click', function(e){
      e.preventDefault();
      close();
    });

    nav.addEventListener('click', function(e){
      var a = e.target.closest && e.target.closest('a');
      if (a) close();
    });

    window.addEventListener('keydown', function(e){
      if (e.key === 'Escape') close();
    });

    window.addEventListener('resize', function(){
      if (window.matchMedia('(min-width: 901px)').matches) close();
    });

    return true;
  }

  function boot(){
    if (init()) return;

    var tries = 0;
    var t = setInterval(function(){
      tries++;
      if (init() || tries >= 25) clearInterval(t);
    }, 120);

    var mo = new MutationObserver(function(){
      if (init()){
        try { mo.disconnect(); } catch(_){}
        clearInterval(t);
      }
    });
    try{ mo.observe(document.documentElement, {subtree:true, childList:true}); }catch(_){}
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();

/* ===== Helpers ===== */
const esc = s => (s||'').replace(/[&<>"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
const norm = s => String(s||'').trim().toLowerCase();
const fmtDateBR = d => { if(!d) return ''; const [y,m,day]=(String(d).split('T')[0]||'').split('-'); return y?`${day}/${m}/${y}`:''; };
const uniq = arr => Array.from(new Set(arr));

/* ===== Estado ===== */
const myPlanPill = document.getElementById('my-plan-pill');
const grid = document.getElementById('benef-grid');
const bq   = document.getElementById('bq');
const freeCta = document.getElementById('free-cta');

let MY_PLAN_ID = null;
let MY_PLAN_NAME = null;
let TOKENS = [];
let ALL = [];
let VISIBLE = [];
let SPECIALTIES = [];

/* ===== Combo helpers ===== */
function renderSingleComboHTML(field, options, current){
  const group = `${field}-${Math.random().toString(36).slice(2)}`;
  const currentLabel = (options.find(o=>o.v===current)?.t) || (options[0]?.t||'—');
  const radios = options.map(o => `
    <label class="combo-opt">
      <input type="radio" name="${group}" value="${o.v}" ${o.v===current?'checked':''}>
      <span>${esc(o.t)}</span>
    </label>
  `).join('');
  return `
    <div class="combo" data-single>
      <button type="button" class="combo-btn" aria-expanded="false">
        <span class="combo-label">${esc(currentLabel)}</span>
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
      </button>
      <div class="combo-menu"><div class="combo-list">${radios}</div></div>
      <input type="hidden" data-f="${field}" value="${esc(current)}">
    </div>
  `;
}
function updateComboSummary(combo){
  const list = combo.querySelector('.combo-list');
  const sel = list.querySelector('input[type="radio"]:checked');
  const label = sel ? sel.closest('label').querySelector('span').textContent : '—';
  const val   = sel ? sel.value : '';
  combo.querySelector('.combo-label').textContent = label;
  combo.querySelector('input[data-f]')?.setAttribute('value', val);
}
function closeAllCombos(except=null){
  document.querySelectorAll('.combo[data-open]').forEach(c=>{
    if (except && c===except) return;
    c.removeAttribute('data-open');
    c.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');
  });
}
document.addEventListener('click', (e)=>{
  const insideAnyCombo = e.target.closest('.combo');
  if (!insideAnyCombo) closeAllCombos();

  const btn = e.target.closest('.combo-btn');
  if (btn){
    const combo = btn.closest('.combo');
    const open = combo.hasAttribute('data-open');
    if (!open){ closeAllCombos(combo); combo.setAttribute('data-open',''); btn.setAttribute('aria-expanded','true'); }
    else { combo.removeAttribute('data-open'); btn.setAttribute('aria-expanded','false'); }
    return;
  }

  const labelOpt = e.target.closest('.combo[data-single] .combo-opt');
  if (labelOpt){
    const radio = labelOpt.querySelector('input[type="radio"]');
    if (radio){ radio.checked = true; radio.dispatchEvent(new Event('change', { bubbles:true })); }
  }
});
document.addEventListener('change', (e)=>{
  if (!e.target.matches('.combo[data-single] input[type="radio"]')) return;
  const combo = e.target.closest('.combo[data-single]');
  updateComboSummary(combo);
  combo.removeAttribute('data-open');
  combo.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');

  if (combo.closest('#specialty-filter')) loadBenefits();
});
document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape'){ closeAllCombos(); } });

/* ===== Plano atual ===== */
async function loadCurrentPlan(){
  try{
    const r = await fetch('/?r=api/member/overview');
    if (!r.ok) return;
    const j = await r.json();

    MY_PLAN_ID = norm(j?.activePlan?.id || j?.plan?.id || j?.subscription?.plan_id || '');
    MY_PLAN_NAME = j?.activePlan?.name || j?.plan?.name || '';

    const label = (MY_PLAN_NAME || j?.subscription?.plan_id || '—');
    myPlanPill.textContent = 'Plano: ' + label;
    myPlanPill.style.display = 'inline-flex';

    if (MY_PLAN_ID === 'free') freeCta.style.display = 'block';

    TOKENS = uniq([MY_PLAN_ID, norm(MY_PLAN_NAME)]).filter(Boolean);
  }catch(e){}
}

/* ===== Render filtro ===== */
function renderSpecialtyFilter(){
  const holder = document.getElementById('specialty-filter');
  const current = holder.querySelector('input[data-f="specialty"]')?.value || '';
  const opts = [{v:'', t:'Filtrar por especialidade'}].concat(SPECIALTIES.map(s => ({v:s, t:s})));
  holder.innerHTML = renderSingleComboHTML('specialty', opts, current);
}

/* ===== Benefícios ===== */
async function loadBenefits(){
  grid.innerHTML = `<div class="glass-card muted" style="grid-column:1/-1">Carregando…</div>`;
  try{
    const currentSpec = document.querySelector('#specialty-filter input[data-f="specialty"]')?.value || '';
    const qs = new URLSearchParams();
    if (currentSpec) qs.set('specialty', currentSpec);

    const r = await fetch('/?r=api/benefits/list' + (qs.toString()? '&'+qs.toString() : ''));
    const j = await r.json();
    if(!r.ok){
      grid.innerHTML = `<div class="glass-card muted" style="grid-column:1/-1">${esc(j.error||'Falha ao carregar benefícios')}</div>`;
      return;
    }

    ALL = (j.benefits||[]);
    SPECIALTIES = Array.isArray(j.specialties) ? j.specialties : [];
    renderSpecialtyFilter();

    if (!TOKENS.length) {
      const pid = norm(j.user_plan_id||'');
      const pn  = norm(j.user_plan_name||'');
      TOKENS = uniq([pid, pn]).filter(Boolean);
      if (pn || pid) {
        myPlanPill.textContent = 'Plano: ' + (j.user_plan_name || j.user_plan_id || '—');
        myPlanPill.style.display = 'inline-flex';
      }
    }

    applyFilters();
  }catch(e){
    grid.innerHTML = `<div class="glass-card muted" style="grid-column:1/-1">Erro ao carregar.</div>`;
  }
}

/* ===== Busca local ===== */
function applyFilters(){
  const term = norm(bq?.value||'');

  if (!TOKENS.length){
    grid.innerHTML = `<div class="glass-card muted" style="grid-column:1/-1">Não foi possível identificar seu plano. Entre em “Meu plano” e verifique sua assinatura.</div>`;
    return;
  }

  VISIBLE = ALL.filter(b => {
    if (!term) return true;
    const txt = norm(`${b.title||''} ${b.partner||''} ${b.description||''}`);
    return txt.includes(term);
  });

  render();
}

/* ===== Render ===== */
function render(){
  if (!VISIBLE.length){
    const pretty = (MY_PLAN_NAME || MY_PLAN_ID || '').toString();
    grid.innerHTML = `<div class="glass-card muted" style="grid-column:1/-1">Nenhum benefício disponível para o plano ${esc(pretty)}.</div>`;
    return;
  }
  grid.innerHTML = VISIBLE.map(renderCard).join('');
  bindCopyButtons();
}

function renderCard(b){
  const type = String(b.type||'coupon').toLowerCase();
  const partnerLine = [b.partner, b.specialty].filter(Boolean).join(' • ');
  const valid = b.valid_until ? `<span class="expires muted">Válido até ${fmtDateBR(b.valid_until)}</span>` : '';
  const typePill = type==='coupon'?'CUPOM':(type==='link'?'LINK':'SERVIÇO');

  const cover = b.image_url
    ? `<img class="cover-img" src="${esc(b.image_url)}" alt="${esc(b.title||'Benefício')}">`
    : `<div class="cover-ph">🏷️</div>`;

  const codeRow = (type==='coupon' && b.code)
    ? `<div class="coupon-row"><div class="coupon-code" title="${esc(b.code)}">${esc(b.code)}</div></div>`
    : '';

  const copyIcon = `
    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
      <path fill="currentColor" d="M16 1H4c-1.1 0-2 .9-2 2v12h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/>
    </svg>`;
  const linkIcon = `
    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
      <path fill="currentColor" d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3z"/>
      <path fill="currentColor" d="M5 5h6V3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-6h-2v6H5V5z"/>
    </svg>`;

  return `
    <article class="benef-card glass-card" data-id="${Number(b.id)||0}">
      <div class="cover">${cover}</div>
      <div class="card-body">
        <div class="meta-top">
          <span class="type-pill">${esc(typePill)}</span>
          ${valid}
        </div>

        <h3 class="b-title">${esc(b.title||'Benefício')}</h3>
        ${partnerLine ? `<div class="b-sub muted">${esc(partnerLine)}</div>` : ''}

        ${b.description ? `<p class="muted b-desc">${esc(b.description)}</p>` : ''}

        ${codeRow}

        <div class="cta-icons">
          ${b.code ? `<button class="icon-btn" type="button" data-copy="${esc(b.code)}" title="Copiar código" aria-label="Copiar código">${copyIcon}</button>` : ``}
          ${b.link ? `<a class="icon-btn" href="${esc(b.link)}" target="_blank" rel="noopener" title="Abrir link do parceiro" aria-label="Abrir link do parceiro">${linkIcon}</a>` : ``}
        </div>
      </div>
    </article>
  `;
}

function bindCopyButtons(){
  grid.querySelectorAll('[data-copy]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const code = btn.getAttribute('data-copy')||'';
      navigator.clipboard?.writeText(code);
      btn.classList.add('ok');
      const prev = btn.title;
      btn.title = 'Copiado!';
      setTimeout(()=>{ btn.classList.remove('ok'); btn.title = prev; }, 1200);
    });
  });
}

/* ===== Eventos ===== */
let _dbTimer;
bq?.addEventListener('input', ()=>{
  clearTimeout(_dbTimer);
  _dbTimer = setTimeout(applyFilters, 250);
});

/* ===== Init ===== */
(async function(){
  await loadCurrentPlan();
  await loadBenefits();
})();
</script>

<style>
:root{
  --rail-w: 168px;
  --rail-gap: 12px;
  --rail-top: calc(var(--topnav-h, 52px) + 12px);
}

/* ===== FIX: impede scroll lateral ===== */
html, body{ max-width:100%; overflow-x:hidden; }

/* ===== MENU responsivo no header do dashboard ===== */
.menu-backdrop{
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.35);
  opacity:0;
  pointer-events:none;
  transition:opacity .18s ease;
  z-index: 220;
}
.menu-backdrop.is-on{
  opacity:1;
  pointer-events:auto;
}
html.menu-open{ overflow:hidden; }

header.topnav[data-topnav] .nav-toggle,
header.topnav .nav-toggle{
  display:none;
  position:relative;
  width:42px;height:42px;
  border:0;
  background:#0000;
  border-radius:10px;
  cursor:pointer;
}
header.topnav[data-topnav] .nav-toggle__bar,
header.topnav[data-topnav] .nav-toggle__bar::before,
header.topnav[data-topnav] .nav-toggle__bar::after,
header.topnav .nav-toggle__bar,
header.topnav .nav-toggle__bar::before,
header.topnav .nav-toggle__bar::after{
  content:"";
  display:block;
  height:2px;
  width:22px;
  margin:auto;
  background: currentColor;
  transition:.2s;
  position:relative;
}
header.topnav[data-topnav] .nav-toggle__bar::before,
header.topnav .nav-toggle__bar::before{ position:absolute; inset:-6px 0 0 0; }
header.topnav[data-topnav] .nav-toggle__bar::after,
header.topnav .nav-toggle__bar::after{ position:absolute; inset: 6px 0 0 0; }

@media (max-width: 900px){
  header.topnav[data-topnav] .nav-toggle,
  header.topnav .nav-toggle{
    display:inline-grid;
    place-items:center;
  }

  header.topnav[data-topnav] nav,
  header.topnav nav,
  #menu{
    position:fixed;
    left:12px !important;
    right:12px !important;
    top: calc(var(--topnav-h, 68px) + 10px) !important;
    z-index: 230 !important;

    display:flex !important;
    flex-direction:column;
    gap:14px;

    background:#fff !important;
    border:1px solid #e9eef2 !important;
    border-radius:16px !important;
    padding:16px !important;

    box-shadow:0 18px 40px rgba(15,23,42,.18) !important;

    transform: translateY(-140%) !important;
    opacity:0 !important;
    pointer-events:none !important;

    transition: transform .22s ease, opacity .22s ease;
  }

  html.menu-open header.topnav[data-topnav] nav[data-open="true"],
  html.menu-open header.topnav nav[data-open="true"],
  html.menu-open #menu[data-open="true"]{
    transform: translateY(0) !important;
    opacity:1 !important;
    pointer-events:auto !important;
  }

  header.topnav[data-topnav] nav a,
  header.topnav nav a,
  #menu a{
    color: var(--ink, #2C3E50) !important;
    text-decoration:none;
    font-weight:800;
  }
}

/* ===== Largura igual ao Header (sem sidebar) ===== */
.container.member{
  width: min(92vw, var(--container)) !important;
  margin-inline: auto;
  padding-inline: 0;
}
.member-main{ display:grid; gap:16px; }

.member-main > .glass-card{
  background:#ffffff;
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:16px;
  color:var(--ink);
  box-shadow:0 12px 30px rgba(15,23,42,.06);
}
.member-main .glass-card.muted{
  background:#fef3c7;
  border-color:#fed7aa;
  color:#92400e;
  box-shadow:none;
}
.sect-title{
  margin:0;
  font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight:800;
  color:var(--ink);
  line-height:1.15;
  font-size: clamp(1.3rem, 1rem + 1vw, 1.7rem);
}
.muted{
  opacity:.9;
  font-size:.86rem;
  color:#64748b;
}
.cta-up{
  margin-left:8px;
  font-weight:800;
  color:#1d4ed8;
  text-decoration:underline;
}
.title-row{
  display:flex;
  align-items:center;
  gap:10px;
  margin-bottom:10px;
}
.pill{
  font-size:.8rem;
  font-weight:800;
  padding:6px 10px;
  border-radius:999px;
  background:#eff6ff;
  border:1px solid #bfdbfe;
  white-space:nowrap;
  color:#1d4ed8;
}
.pill--sm{
  font-size:.74rem;
  padding:4px 8px;
}

/* Toolbar */
.bens-toolbar{
  display:flex;
  gap:10px;
  align-items:center;
  margin:8px 0 6px;
}
.toolbar-filter{ flex:1 1 auto; }
.search-wrap{ flex:0 0 300px; }
.search-wrap input[type="search"]{
  width:100%;
  padding:10px 12px;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#f9fafb;
  color:#0f172a;
  outline:none;
  font-size:.9rem;
}
.search-wrap input[type="search"]::placeholder{ color:#94a3b8; }

/* Combobox */
.combo{ position:relative; }
.combo-btn{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding:10px 14px;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#f9fafb;
  color:#0f172a;
  cursor:pointer;
  font-size:.9rem;
}
.combo-btn:focus{
  outline:2px solid #93c5fd;
  outline-offset:2px;
}
.combo-menu{
  position:absolute;
  top:calc(100% + 6px);
  left:0;
  right:0;
  background:#ffffff;
  color:#0f172a;
  border:1px solid #e2e8f0;
  border-radius:12px;
  padding:8px;
  z-index:50;
  box-shadow:0 12px 30px rgba(15,23,42,.18);
  display:none;
}
.combo[data-open] .combo-menu{ display:block; }
.combo-list{
  max-height:210px;
  overflow:auto;
  display:grid;
  gap:6px;
  padding-right:4px;
}
.combo-opt{
  display:flex;
  align-items:center;
  gap:8px;
  padding:6px 8px;
  border-radius:8px;
  background:#f9fafb;
  border:1px solid transparent;
  cursor:pointer;
  font-size:.9rem;
}
.combo-opt:hover{ border-color:#e2e8f0; background:#eef2ff; }
.combo-opt input{ accent-color:#4f46e5; }
.combo-label{
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
  text-align:left;
}

/* Grid */
.benef-grid{
  display:grid;
  gap:16px;
  margin-top:12px;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}
@media (max-width:1200px){
  .benef-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width:720px){
  .bens-toolbar{ flex-direction:column; align-items:stretch; gap:8px; }
  .search-wrap{ flex:unset; width:100%; }
  .benef-grid{ grid-template-columns: 1fr; }
}

/* Card benefício */
.benef-card.glass-card{
  display:flex;
  flex-direction:column;
  padding:0;
  overflow:hidden;
  border-radius:16px;
  border:1px solid #e2e8f0;
  background:#ffffff;
  box-shadow:0 10px 24px rgba(15,23,42,.08);
  color:#0f172a;
}
.cover{
  position:relative;
  width:100%;
  height:220px;
  background:#f1f5f9;
}
.cover-img{ width:100%; height:100%; object-fit:cover; display:block; }
.cover-ph{
  width:100%;
  height:100%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:48px;
  color:#94a3b8;
  background:linear-gradient(180deg,#eff6ff,#e2e8f0);
}
.card-body{
  position:relative;
  padding:12px 14px 54px 14px;
  display:grid;
  gap:8px;
  min-height:170px;
}
.meta-top{ display:flex; align-items:center; gap:10px; }
.type-pill{
  font-size:.68rem;
  font-weight:800;
  padding:4px 8px;
  border-radius:999px;
  background:#eef2ff;
  border:1px solid #c7d2fe;
  line-height:1;
  color:#3730a3;
}
.expires{ font-size:.72rem; opacity:.9; }
.b-title{ font-size:1.02rem; margin:0; font-weight:800; }
.b-sub{ font-size:.9rem; }
.b-desc{ margin:0; }
.coupon-row{ margin-top:2px; }
.coupon-code{
  display:block;
  text-align:center;
  white-space:nowrap;
  overflow-x:auto;
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
  font-size:.98rem;
  font-weight:800;
  letter-spacing:.5px;
  padding:9px 11px;
  border-radius:10px;
  background:#eff6ff;
  border:1px dashed #bfdbfe;
  color:#1d4ed8;
}
.cta-icons{
  position:absolute;
  right:10px;
  bottom:10px;
  display:flex;
  gap:8px;
}
.icon-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:36px;
  height:36px;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#f9fafb;
  color:#0f172a;
  cursor:pointer;
  text-decoration:none;
  transition:box-shadow .16s ease, transform .06s ease, background .16s ease;
}
.icon-btn svg{ opacity:.9; }
.icon-btn:hover{
  background:#eff6ff;
  box-shadow:0 6px 18px rgba(15,23,42,.12);
}
.icon-btn:active{
  transform:translateY(1px);
  box-shadow:0 3px 10px rgba(15,23,42,.14);
}
.icon-btn.ok{ box-shadow:0 0 0 2px rgba(59,130,246,.55); }

.sr-only{
  position:absolute;
  width:1px;
  height:1px;
  padding:0;
  margin:-1px;
  overflow:hidden;
  clip:rect(0,0,0,0);
  border:0;
}
</style>
