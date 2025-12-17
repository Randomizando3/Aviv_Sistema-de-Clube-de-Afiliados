<?php
// Member • Faturas — desktop igual; no mobile vira 3 cards (próxima, atual paga, anterior paga)
?>
<section class="container member" style="margin-top:18px">
  <main class="member-main">
    <div class="glass-card">
      <h1 class="sect-title">Faturas</h1>

      <!-- Filtros (desktop) -->
      <div class="filters-inline">
        <label class="sr-only" for="inv-status">Status</label>
        <select id="inv-status" aria-label="Status">
          <option value="">Todos os status</option>
          <option value="pago">Pago</option>
          <option value="pendente">Pendente</option>
          <option value="atraso">Em atraso</option>
          <option value="cancelado">Cancelado</option>
          <option value="agendada">Próxima</option>
        </select>
      </div>

      <!-- Cards mobile simplificados -->
      <div id="inv-cards" class="mobile-cards" aria-live="polite"></div>

      <!-- Tabela (desktop) -->
      <div class="table-wrap" role="region" aria-label="Tabela de faturas">
        <table id="inv-table" class="tbl-invoices">
          <thead>
            <tr>
              <th>#</th>
              <th>Período</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Vencimento</th>
              <th>Pagamento</th>
            </tr>
          </thead>
          <tbody id="inv-tbody">
            <tr><td colspan="6" class="muted">Carregando…</td></tr>
          </tbody>
        </table>
      </div>

      <div id="inv-alert" class="alert" style="display:none"></div>
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

    if (btn.dataset.dashMenuBound === '1') return true;
    btn.dataset.dashMenuBound = '1';

    function isOpen(){
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
const fmtBRL = v => 'R$ ' + (Number(v||0)).toFixed(2).replace('.',',');
const fmtDateBR = d => { if(!d) return '—'; const s = String(d).split('T')[0]||''; const [y,m,dd] = s.split('-'); return y ? `${dd}/${m}/${y}` : '—'; };
const toDate = d => { if(!d) return null; try{ return new Date(String(d).replace(' ','T')); }catch(_){ return null; } };
const cmpAsc = (a,b)=> (a<b?-1:a>b?1:0);
const cmpDesc= (a,b)=> (a>b?-1:a<b?1:0);

function setAlert(msg, ok=true){
  const el = document.getElementById('inv-alert');
  el.style.display='block';
  el.textContent = msg;
  el.style.borderColor = ok ? 'rgba(76,175,80,.35)' : 'rgba(255,77,79,.45)';
  el.style.background  = ok ? 'rgba(76,175,80,.12)' : 'rgba(255,77,79,.12)';
  clearTimeout(el._t); el._t = setTimeout(()=>{ el.style.display='none'; }, 2200);
}

// Normaliza status para: pago | pendente | atraso | cancelado | agendada
function normStatus(s){
  const v = String(s||'').toLowerCase();
  if (['paid','pago','received','recebido','confirmed'].includes(v)) return 'pago';
  if (['pending','pendente'].includes(v)) return 'pendente';
  if (['overdue','vencido','em_atraso','late','atraso'].includes(v)) return 'atraso';
  if (['canceled','cancelado','deleted','refunded','chargedback'].includes(v)) return 'cancelado';
  if (['agendada','upcoming','proxima','próxima'].includes(v)) return 'agendada';
  return v || 'pendente';
}
const statusChip = st => {
  const map = {
    pago:      '<span class="chip chip-success">Pago</span>',
    pendente:  '<span class="chip chip-pending">Pendente</span>',
    atraso:    '<span class="chip chip-failed">Em atraso</span>',
    cancelado: '<span class="chip chip-failed">Cancelado</span>',
    agendada:  '<span class="chip chip-info">Próxima</span>',
  };
  return map[st] || esc(st||'—');
};

/* ===== Estado ===== */
let ALL = [];
const tbody = document.getElementById('inv-tbody');
const sel   = document.getElementById('inv-status');
const cards = document.getElementById('inv-cards');

/* ===== Carregar ===== */
async function loadInvoices(){
  tbody.innerHTML = `<tr><td colspan="6" class="muted">Carregando…</td></tr>`;
  cards.innerHTML = `<div class="mcard skeleton"></div><div class="mcard skeleton"></div><div class="mcard skeleton"></div>`;
  try{
    const r = await fetch('/?r=api/member/invoices');
    const ct = (r.headers.get('content-type')||'').toLowerCase();
    const raw = await r.text();
    const j = ct.includes('application/json') ? JSON.parse(raw) : { error: raw };
    if(!r.ok){
      tbody.innerHTML = `<tr><td colspan="6" class="muted">${esc(j.error||'Falha ao carregar')}</td></tr>`;
      cards.innerHTML = `<div class="muted">Falha ao carregar</div>`;
      return;
    }
    ALL = (j.invoices||[]).map(inv => ({ ...inv, _norm: normStatus(inv.status), _due: toDate(inv.due_date) }));
    render();
    renderCards();
  }catch(e){
    tbody.innerHTML = `<tr><td colspan="6" class="muted">Erro ao carregar suas faturas.</td></tr>`;
    cards.innerHTML = `<div class="muted">Erro ao carregar suas faturas.</div>`;
  }
}

/* ===== Render tabela (desktop) ===== */
function render(){
  const v = sel.value;
  const rows = ALL.filter(inv => !v || inv._norm===v);
  if (!rows.length){
    tbody.innerHTML = `<tr><td colspan="6" class="muted">Nenhuma fatura encontrada.</td></tr>`;
    return;
  }
  tbody.innerHTML = rows.map(inv => {
    const st = inv._norm;

    let payCell = '';
    if (st==='pendente' || st==='atraso'){
      payCell = `
        <button class="btn btn-sm" type="button" data-act="pay" data-id="${esc(inv.id)}">Pagar agora</button>
        ${inv.boleto_url ? `<a class="icon-link" href="${esc(inv.boleto_url)}" target="_blank" rel="noopener" title="Boleto (PDF)" aria-label="Boleto (PDF)">📄</a>` : ''}
        ${inv.pix_copy   ? `<button class="icon-link" type="button" data-act="pix" data-code="${esc(inv.pix_copy)}" title="Copiar PIX copia e cola" aria-label="Copiar PIX copia e cola">⚡</button>` : ''}
      `;
    } else if (st==='pago'){
      payCell = `<a class="btn btn-sm btn--ghost" href="${inv.receipt_url?esc(inv.receipt_url):'#'}" target="${inv.receipt_url?'_blank':'_self'}" rel="${inv.receipt_url?'noopener':''}" ${inv.receipt_url?'':'aria-disabled="true"'}>Ver recibo</a>`;
    } else if (st==='agendada'){
      payCell = `<span class="muted">Ainda não gerada</span>`;
    } else {
      payCell = `<span class="muted">—</span>`;
    }

    return `
      <tr data-status="${esc(st)}" data-id="${esc(inv.id)}">
        <td data-th="#">${esc(inv.id)}</td>
        <td data-th="Período">${esc(inv.period || '')}</td>
        <td data-th="Valor">${fmtBRL(inv.amount)}</td>
        <td data-th="Status">${statusChip(st)}</td>
        <td data-th="Vencimento">${fmtDateBR(inv.due_date)}</td>
        <td data-th="Pagamento" class="pay-cell">${payCell}</td>
      </tr>
    `;
  }).join('');
}

/* ===== Render cards (mobile) ===== */
function renderCards(){
  const next = [...ALL]
    .filter(i => ['agendada','pendente','atraso'].includes(i._norm))
    .sort((a,b)=>cmpAsc(a._due?.getTime()||Infinity, b._due?.getTime()||Infinity))[0] || null;

  const paids = [...ALL].filter(i=>i._norm==='pago')
    .sort((a,b)=>cmpDesc(a._due?.getTime()||0, b._due?.getTime()||0));

  const currentPaid  = paids[0] || null;
  const previousPaid = paids[1] || null;

  const html = `
    ${cardHTML('Próxima fatura', next, true)}
    ${cardHTML('Atual (paga)', currentPaid, false)}
    ${cardHTML('Anterior (paga)', previousPaid, false)}
  `;
  cards.innerHTML = html || `<div class="muted">Nenhuma fatura encontrada.</div>`;
}

function cardHTML(title, inv, allowPay){
  if(!inv){
    return `<div class="mcard">
      <div class="mcard-head"><div class="mcard-title">${esc(title)}</div></div>
      <div class="mcard-amt">—</div>
      <div class="mcard-meta muted">Sem dados disponíveis</div>
    </div>`;
  }
  const st = inv._norm;
  const canPay = allowPay && (st==='pendente' || st==='atraso');

  const payBtns = canPay ? `
    <div class="mcard-actions">
      <button class="btn btn-sm" type="button" data-act="pay" data-id="${esc(inv.id)}">Pagar agora</button>
      ${inv.boleto_url ? `<a class="icon-link" href="${esc(inv.boleto_url)}" target="_blank" rel="noopener" title="Boleto (PDF)" aria-label="Boleto (PDF)">📄</a>` : ''}
      ${inv.pix_copy   ? `<button class="icon-link" type="button" data-act="pix" data-code="${esc(inv.pix_copy)}" title="Copiar PIX copia e cola" aria-label="Copiar PIX copia e cola">⚡</button>` : ''}
    </div>`
    : (st==='pago' && inv.receipt_url ? `<div class="mcard-actions"><a class="btn btn-sm btn--ghost" href="${esc(inv.receipt_url)}" target="_blank" rel="noopener">Ver recibo</a></div>` : '');

  const sub = st==='agendada' ? `Agendada p/ ${fmtDateBR(inv.due_date)}`
            :                  `Venc.: ${fmtDateBR(inv.due_date)}`;

  return `<div class="mcard" data-id="${esc(inv.id)}">
    <div class="mcard-head">
      <div class="mcard-title">${esc(title)}</div>
      <div>${statusChip(st)}</div>
    </div>
    <div class="mcard-amt">${fmtBRL(inv.amount)}</div>
    <div class="mcard-meta">${esc(inv.period||'')}</div>
    <div class="mcard-meta">${sub}</div>
    ${payBtns}
  </div>`;
}

/* ===== Ações ===== */
sel?.addEventListener('change', render);

function handleClick(e, root){
  const pay = e.target.closest('button[data-act="pay"]');
  if (pay && root.contains(pay)){
    const id = pay.dataset.id;
    pay.disabled = true;
    (async ()=>{
      try{
        const r = await fetch('/?r=api/member/invoices/pay', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({ id })
        });
        const j = await r.json();
        if(!r.ok){ setAlert(j.error||'Falha ao iniciar pagamento', false); pay.disabled=false; return; }

        if (j.checkout_url){ window.open(j.checkout_url, '_blank', 'noopener'); }
        else if (j.boleto_url){ window.open(j.boleto_url, '_blank', 'noopener'); }
        else if (j.pix_copy){ await navigator.clipboard?.writeText(j.pix_copy); setAlert('PIX copia e cola copiado!'); }
        else if (j.receipt_url){ window.open(j.receipt_url, '_blank', 'noopener'); }
        else { setAlert('Pagamento iniciado.', true); }
      }catch(err){
        setAlert('Erro ao iniciar pagamento', false);
      }finally{
        pay.disabled = false;
      }
    })();
    return;
  }

  const pix = e.target.closest('button[data-act="pix"]');
  if (pix && root.contains(pix)){
    (async ()=>{
      try{
        await navigator.clipboard?.writeText(pix.dataset.code||'');
        setAlert('PIX copia e cola copiado!');
      }catch(err){ setAlert('Não foi possível copiar o PIX', false); }
    })();
  }
}

tbody.addEventListener('click', e=>handleClick(e, tbody));
cards.addEventListener('click', e=>handleClick(e, cards));

/* init */
loadInvoices();
</script>

<style>
/* ===== FIX: impede scroll lateral (menu + tabela) ===== */
html, body{ max-width:100%; overflow-x:hidden; }

/* ===== MENU responsivo no header do dashboard (abre como site) ===== */
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

.member-main{
  display:grid;
  gap:16px;
}

/* Card principal da página Faturas */
.member-main > .glass-card{
  background:#ffffff;
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:16px;
  color:var(--ink, #0f172a);
  box-shadow:0 12px 30px rgba(15,23,42,.06);
}

/* Título */
.sect-title{
  margin:0 0 8px;
  font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight:800;
  color:var(--ink, #0f172a);
  font-size: clamp(1.3rem, 1rem + 1vw, 1.7rem);
}

/* Texto auxiliar / mensagens */
.muted{
  opacity:.9;
  color:#64748b;
  font-size:.88rem;
}

/* Alert de feedback */
.alert{
  margin-top:10px;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid #e2e8f0;
  background:#f9fafb;
  color:#0f172a;
  font-size:.85rem;
}

/* Acessibilidade */
.sr-only{
  position:absolute;
  width:1px;
  height:1px;
  padding:0;
  margin:-1px;
  overflow:hidden;
  clip:rect(0,0,0,0);
  white-space:nowrap;
  border:0;
}

/* ===== Filtros ===== */
.filters-inline{
  display:flex;
  gap:8px;
  align-items:center;
  margin:8px 0 10px;
}
.filters-inline select{
  appearance:none;
  padding:9px 12px;
  border-radius:999px;
  outline:none;
  border:1px solid #e2e8f0;
  background:#f9fafb;
  color:#0f172a;
  font-size:.9rem;
}

/* ===== Tabela (desktop) ===== */
.table-wrap{
  overflow:auto;
  -webkit-overflow-scrolling:touch;
  border-radius:12px;
  border:1px solid #e2e8f0;
}
.tbl-invoices{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
}
.tbl-invoices thead th{
  position: sticky;
  top: 0;
  z-index: 1;
  text-align:left;
  white-space:nowrap;
  padding:10px 10px;
  background:#f8fafc;
  color:#0f172a;
  font-weight:700;
  font-size:.9rem;
  border-bottom:1px solid #e2e8f0;
}
.tbl-invoices tbody td{
  padding:10px 10px;
  vertical-align:middle;
  border-bottom:1px solid #e2e8f0;
  white-space:nowrap;
  font-size:.9rem;
  color:#0f172a;
}

/* Ações na coluna Pagamento */
.pay-cell{
  display:flex;
  align-items:center;
  gap:8px;
  flex-wrap:wrap;
}

/* Links/ícones auxiliares (boleto, pix) */
.icon-link{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:32px;
  height:32px;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#f9fafb;
  color:#0f172a;
  cursor:pointer;
  text-decoration:none;
  font-size:1rem;
}

/* Chips de status */
.chip{
  display:inline-block;
  padding:5px 10px;
  border-radius:999px;
  font-size:.82rem;
  font-weight:700;
  border:1px solid transparent;
}
.chip-success{
  background:#dcfce7;
  border-color:#4ade80;
  color:#166534;
}
.chip-pending{
  background:#fef9c3;
  border-color:#facc15;
  color:#854d0e;
}
.chip-failed{
  background:#fee2e2;
  border-color:#f97373;
  color:#991b1b;
}
.chip-info{
  background:#dbeafe;
  border-color:#60a5fa;
  color:#1d4ed8;
}

/* Botões */
.btn{
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#0f172a;
  color:#ffffff;
  cursor:pointer;
  font-size:.85rem;
  font-weight:600;
}
.btn.btn-sm{
  padding:7px 11px;
}
.btn--ghost{
  background:#ffffff;
  color:#0f172a;
}

/* ===== MOBILE CARDS (<= 680px) ===== */
.mobile-cards{ display:none; }

@media (max-width:680px){
  .filters-inline{ display:none; }
  .table-wrap{ display:none; }

  .mobile-cards{
    display:grid;
    gap:10px;
    margin-top:4px;
  }

  .mcard{
    border:1px solid #e2e8f0;
    background:#ffffff;
    border-radius:12px;
    padding:12px;
    box-shadow:0 8px 20px rgba(15,23,42,.06);
    color:#0f172a;
  }
  .mcard.skeleton{
    min-height:90px;
    background:linear-gradient(90deg,#f1f5f9,#e5e7eb,#f1f5f9);
    background-size:200% 100%;
    animation:sh 1.2s linear infinite;
  }
  @keyframes sh{
    0%{background-position:0 0}
    100%{background-position:-200% 0}
  }

  .mcard-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
  }
  .mcard-title{
    font-weight:800;
    font-size:.9rem;
  }
  .mcard-amt{
    font-size:1.3rem;
    font-weight:900;
    margin:6px 0 2px;
  }
  .mcard-meta{
    opacity:.9;
    font-size:.86rem;
    color:#64748b;
  }
  .mcard-actions{
    display:flex;
    gap:8px;
    margin-top:8px;
    flex-wrap:wrap;
  }

  .mcard .btn{
    background:#0f172a;
    color:#fff;
  }
}
</style>
