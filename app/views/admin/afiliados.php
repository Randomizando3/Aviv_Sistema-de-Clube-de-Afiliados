<?php
// Admin • Afiliados — visual unificado com as demais páginas (container + glass-card)
?>
<section class="container admin afiliados-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Afiliados • Sistema</h1>
      <p class="muted">Gerencie configurações, saques e conversões do programa de afiliados.</p>
    </div>

    <!-- GRID: Configurações + Saques -->
    <div class="aff-grid">
      <!-- Configurações -->
      <div class="glass-card">
        <h2 class="sect-sub">Configurações</h2>
        <form id="aff-settings" class="form-grid" onsubmit="return saveSettings(event)">
          <div class="input-wrap">
            <label class="field-label" for="st_percent">Percentual de comissão (%)</label>
            <input type="number" step="0.1" min="0" id="st_percent" class="field">
          </div>
          <div class="input-wrap">
            <label class="field-label" for="st_min">Valor mínimo para saque (R$)</label>
            <input type="number" step="0.01" min="0" id="st_min" class="field">
          </div>
          <div class="input-wrap">
            <label class="field-label" for="st_cookie">Validade do cookie de indicação (dias)</label>
            <input type="number" step="1" min="1" id="st_cookie" class="field">
          </div>
          <div class="form-actions">
            <button class="btn btn-sm" type="submit">Salvar configurações</button>
            <span id="st_msg" class="inline-msg" aria-live="polite"></span>
          </div>
        </form>
      </div>

      <!-- Saques (payouts) -->
      <div class="glass-card">
        <div class="aff-head-inline">
          <h2 class="sect-sub">Saques (payouts)</h2>
          <div class="aff-filter">
            <label class="field-label" for="payout_filter">Status</label>
            <select id="payout_filter" class="field" onchange="loadPayouts()">
              <option value="">Todos</option>
              <option>requested</option>
              <option>approved</option>
              <option>paid</option>
              <option>rejected</option>
            </select>
          </div>
        </div>
        <div id="payout_list" class="table-region" aria-label="Lista de saques">Carregando…</div>
      </div>
    </div>

    <!-- Conversões -->
    <div class="glass-card" style="margin-top:12px">
      <div class="aff-head-inline">
        <h2 class="sect-sub">Conversões</h2>
        <div class="aff-filter">
          <label class="field-label" for="conv_filter">Status</label>
          <select id="conv_filter" class="field" onchange="loadConversions()">
            <option value="all">Todos</option>
            <option>pending</option>
            <option>approved</option>
            <option>rejected</option>
          </select>
        </div>
      </div>
      <div id="conv_list" class="table-region" aria-label="Lista de conversões">Carregando…</div>
    </div>

    <!-- Rodapé do admin -->
    <footer class="admin-footer">
      <p class="muted">Programa de afiliados Aviv+ — acompanhe aqui apenas o controle; pagamentos reais devem ser efetivados nos meios financeiros oficiais.</p>
      <span class="admin-footer-tag">Admin • Afiliados</span>
    </footer>
  </section>
</section>

<script>
/* =========================
   FIX: Menu do Header/Admin (abre/fecha igual ao site)
   - Defensivo: não depende de markup exato
========================= */
(function initAdminMenuToggle(){
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

  if (!toggle || !menu) return;

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
  function toggleMenu(){ isOpen() ? close() : open(); }

  toggle.setAttribute('aria-expanded', isOpen() ? 'true' : 'false');

  toggle.addEventListener('click', (e)=>{
    e.preventDefault();
    e.stopPropagation();
    toggleMenu();
  });

  document.addEventListener('click', (e)=>{
    if (!isOpen()) return;
    if (menu.contains(e.target) || toggle.contains(e.target)) return;
    close();
  });

  document.addEventListener('keydown', (e)=>{
    if (e.key === 'Escape') close();
  });

  menu.addEventListener('click', (e)=>{
    const a = e.target.closest('a');
    if (!a) return;
    const href = (a.getAttribute('href') || '').trim();
    if (href && href !== '#') close();
  });

  window.addEventListener('resize', ()=>{
    if (window.innerWidth > 980) close();
  });
})();

/* ===== Helpers ===== */
async function jfetch(url, opts) {
  const res = await fetch(url, Object.assign({
    headers: {'Content-Type':'application/x-www-form-urlencoded'}
  }, opts || {}));
  const txt = await res.text();
  try { return JSON.parse(txt); } catch(e) { return {error:txt}; }
}
function escHtml(s){
  return String(s ?? '')
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;');
}
function moneyBR(v){
  return 'R$ ' + (Number(v||0)).toFixed(2).replace('.', ',');
}
function isMobile(){ return window.matchMedia('(max-width: 720px)').matches; }
function debounce(fn, ms=250){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

/* Cache para re-render no resize sem refetch */
let LAST_CONV_ROWS = null;
let LAST_PAYOUT_ROWS = null;

/* ===== Configurações ===== */
async function loadSettings() {
  const r = await jfetch('/?r=api/admin/affiliate/settings/get');
  if (r && r.data) {
    document.getElementById('st_percent').value = (r.data.percent ?? 10);
    document.getElementById('st_min').value     = (r.data.min_payout ?? 50);
    document.getElementById('st_cookie').value  = (r.data.cookie_days ?? 30);
  }
}
async function saveSettings(ev) {
  ev.preventDefault();
  const msg = document.getElementById('st_msg');
  msg.textContent = 'Salvando…';
  msg.classList.remove('is-error');

  const p = new URLSearchParams();
  p.set('percent',     document.getElementById('st_percent').value);
  p.set('min_payout',  document.getElementById('st_min').value);
  p.set('cookie_days', document.getElementById('st_cookie').value);

  const r = await jfetch('/?r=api/admin/affiliate/settings/save',{method:'POST', body:p});
  if (r && r.ok){
    msg.textContent = 'Salvo!';
  } else {
    msg.textContent = 'Erro';
    msg.classList.add('is-error');
  }
  setTimeout(()=>msg.textContent='', 1600);
  return false;
}

/* ===== Render Conversões ===== */
function renderConversions(rows){
  const el = document.getElementById('conv_list');

  if (!rows || !rows.length){
    el.innerHTML = '<p class="muted">Sem dados.</p>';
    return;
  }

  if (isMobile()){
    el.innerHTML = `
      <div class="aff-cards" role="list" aria-label="Conversões (cards)">
        ${rows.map(r => {
          const id = Number(r.id);
          const aff = escHtml(r.affiliate_name || ('#'+r.affiliate_user_id));
          const mem = escHtml(r.member_name || r.member_email || '');
          const st  = escHtml(r.status || '');
          const cr  = escHtml(r.created_at || '');
          return `
            <article class="aff-card" role="listitem" aria-label="Conversão #${escHtml(r.id)}">
              <header class="aff-card__head">
                <div class="aff-card__title">
                  <strong>#${escHtml(r.id)}</strong>
                  <span class="muted">${st}</span>
                </div>
                <div class="aff-card__money">
                  <span class="muted">Comissão</span>
                  <strong>${moneyBR(r.commission)}</strong>
                </div>
              </header>

              <div class="aff-card__grid">
                <div class="kpi"><span class="lbl">Afiliado</span><span class="val">${aff}</span></div>
                <div class="kpi"><span class="lbl">Indicado</span><span class="val">${mem || '-'}</span></div>
                <div class="kpi"><span class="lbl">Valor</span><span class="val">${moneyBR(r.amount)}</span></div>
                <div class="kpi"><span class="lbl">Criado</span><span class="val">${cr || '-'}</span></div>
              </div>

              ${r.status !== 'approved'
                ? `<footer class="aff-card__actions">
                     <button class="btn btn-sm" onclick="approveConv(${id})">Aprovar</button>
                   </footer>`
                : ''
              }
            </article>
          `;
        }).join('')}
      </div>
    `;
    return;
  }

  // Desktop: tabela
  el.innerHTML = `
    <div class="table-wrap">
      <table class="tbl-aff tbl-aff--conv">
        <thead>
          <tr>
            <th>ID</th>
            <th>Afiliado</th>
            <th>Indicado</th>
            <th class="ta-r">Valor</th>
            <th class="ta-r">Comissão</th>
            <th>Status</th>
            <th>Criado</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => `
            <tr>
              <td>${escHtml(r.id)}</td>
              <td>${escHtml(r.affiliate_name || ('#'+r.affiliate_user_id))}</td>
              <td>${escHtml(r.member_name || r.member_email || '')}</td>
              <td class="ta-r">${moneyBR(r.amount)}</td>
              <td class="ta-r"><strong>${moneyBR(r.commission)}</strong></td>
              <td>${escHtml(r.status || '')}</td>
              <td>${escHtml(r.created_at || '')}</td>
              <td>
                ${r.status !== 'approved'
                  ? `<button class="btn btn-sm" onclick="approveConv(${Number(r.id)})">Aprovar</button>`
                  : ''
                }
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
  updateTableShadows();
}

/* ===== Conversões ===== */
async function loadConversions() {
  const s  = document.getElementById('conv_filter').value || 'all';
  const el = document.getElementById('conv_list');
  el.innerHTML = '<p class="muted">Carregando…</p>';

  const r = await jfetch('/?r=api/admin/affiliate/list&status='+encodeURIComponent(s));
  if (!r || !r.data) { el.innerHTML = '<p class="muted">Erro ao carregar conversões.</p>'; return; }

  const rows = r.data.items || [];
  LAST_CONV_ROWS = rows;
  renderConversions(rows);
}
async function approveConv(id){
  const p = new URLSearchParams(); p.set('id', id);

  // Mantido conforme seu backend atual (mesma rota que você já estava usando)
  await jfetch('/?r=api/admin/affiliate/mark-paid', {method:'POST', body:p});

  loadConversions();
}

/* ===== Render Saques ===== */
function renderPayouts(rows){
  const el = document.getElementById('payout_list');

  if (!rows || !rows.length){
    el.innerHTML = '<p class="muted">Sem dados.</p>';
    return;
  }

  if (isMobile()){
    el.innerHTML = `
      <div class="aff-cards" role="list" aria-label="Saques (cards)">
        ${rows.map(r => {
          const id = Number(r.id);
          const aff = escHtml(r.affiliate_name || r.affiliate_email || '');
          const st  = escHtml(r.status || '');
          const cr  = escHtml(r.created_at || '');
          const pix = `${escHtml(r.pix_type || '-')}${r.pix_key ? `: ${escHtml(r.pix_key)}` : ''}`;

          const actions = [];
          if (r.status === 'requested'){
            actions.push(`<button class="btn btn-sm" onclick="payoutApprove(${id})">Aprovar</button>`);
            actions.push(`<button class="btn btn-sm btn--ghost" onclick="payoutReject(${id})">Rejeitar</button>`);
          }
          if (r.status === 'approved'){
            actions.push(`<button class="btn btn-sm" onclick="payoutMarkPaid(${id})">Marcar pago</button>`);
          }

          return `
            <article class="aff-card" role="listitem" aria-label="Saque #${escHtml(r.id)}">
              <header class="aff-card__head">
                <div class="aff-card__title">
                  <strong>#${escHtml(r.id)}</strong>
                  <span class="muted">${st}</span>
                </div>
                <div class="aff-card__money">
                  <span class="muted">Valor</span>
                  <strong>${moneyBR(r.amount)}</strong>
                </div>
              </header>

              <div class="aff-card__grid">
                <div class="kpi"><span class="lbl">Afiliado</span><span class="val">${aff || '-'}</span></div>
                <div class="kpi"><span class="lbl">PIX</span><span class="val">${pix || '-'}</span></div>
                <div class="kpi"><span class="lbl">Criado</span><span class="val">${cr || '-'}</span></div>
              </div>

              ${actions.length ? `<footer class="aff-card__actions">${actions.join('')}</footer>` : ''}
            </article>
          `;
        }).join('')}
      </div>
    `;
    return;
  }

  // Desktop: tabela
  el.innerHTML = `
    <div class="table-wrap">
      <table class="tbl-aff tbl-aff--payouts">
        <thead>
          <tr>
            <th>ID</th>
            <th>Afiliado</th>
            <th class="ta-r">Valor</th>
            <th>PIX</th>
            <th>Status</th>
            <th>Criado</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map(r => `
            <tr>
              <td>${escHtml(r.id)}</td>
              <td>${escHtml(r.affiliate_name || r.affiliate_email || '')}</td>
              <td class="ta-r"><strong>${moneyBR(r.amount)}</strong></td>
              <td>${escHtml(r.pix_type || '-')}<span class="muted">: ${escHtml(r.pix_key || '')}</span></td>
              <td>${escHtml(r.status || '')}</td>
              <td>${escHtml(r.created_at || '')}</td>
              <td class="aff-actions">
                ${r.status === 'requested' ? `
                  <button class="btn btn-sm" onclick="payoutApprove(${Number(r.id)})">Aprovar</button>
                  <button class="btn btn-sm btn--ghost" onclick="payoutReject(${Number(r.id)})">Rejeitar</button>
                ` : ''}
                ${r.status === 'approved' ? `
                  <button class="btn btn-sm" onclick="payoutMarkPaid(${Number(r.id)})">Marcar pago</button>
                ` : ''}
              </td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
  updateTableShadows();
}

/* ===== Saques (payouts) ===== */
async function loadPayouts() {
  const s  = document.getElementById('payout_filter').value || '';
  const el = document.getElementById('payout_list');
  el.innerHTML = '<p class="muted">Carregando…</p>';

  const r = await jfetch('/?r=api/admin/affiliate/payouts/list' + (s ? ('&status='+encodeURIComponent(s)) : ''));
  if (!r || !r.data) { el.innerHTML = '<p class="muted">Erro ao carregar saques.</p>'; return; }

  const rows = r.data.items || [];
  LAST_PAYOUT_ROWS = rows;
  renderPayouts(rows);
}
async function payoutApprove(id){
  const p = new URLSearchParams(); p.set('id', id);
  await jfetch('/?r=api/admin/affiliate/payouts/approve', {method:'POST', body:p});
  loadPayouts();
}
async function payoutMarkPaid(id){
  const p = new URLSearchParams(); p.set('id', id);
  await jfetch('/?r=api/admin/affiliate/payouts/mark-paid', {method:'POST', body:p});
  loadPayouts();
}
async function payoutReject(id){
  const reason = prompt('Motivo (opcional):') || '';
  const p = new URLSearchParams(); p.set('id', id); p.set('reason', reason);
  await jfetch('/?r=api/admin/affiliate/payouts/reject', {method:'POST', body:p});
  loadPayouts();
}

/* ===== Sombras do overflow das tabelas ===== */
function updateTableShadows(){
  document.querySelectorAll('.table-wrap').forEach(tw => {
    const L = tw.scrollLeft;
    const W = tw.scrollWidth;
    const C = tw.clientWidth;
    tw.classList.toggle('shadow-left',  L > 2);
    tw.classList.toggle('shadow-right', L + C < W - 2);
  });
}
document.addEventListener('scroll', e => {
  if (e.target.classList && e.target.classList.contains('table-wrap')) updateTableShadows();
}, true);
window.addEventListener('resize', updateTableShadows, {passive:true});

/* Re-render responsivo (tabela <-> cards) sem refetch */
window.addEventListener('resize', debounce(()=>{
  if (LAST_CONV_ROWS)   renderConversions(LAST_CONV_ROWS);
  if (LAST_PAYOUT_ROWS) renderPayouts(LAST_PAYOUT_ROWS);
}, 200), {passive:true});

/* init */
loadSettings();
loadConversions();
loadPayouts();
</script>

<style>
/* Largura alinhada ao header global */
.container.admin{
  width: min(92vw, var(--container)) !important;
  margin-inline: auto;
  padding-inline: 0;
}

/* não cortar dropdown do header */
.container.admin,
.container.admin .admin-main,
.afiliados-page .glass-card{
  overflow: visible;
}

/* ===== Base visual (glass-cards claros) ===== */
.afiliados-page .glass-card{
  background:#ffffff;
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:18px;
  color:var(--text,#111322);
  box-shadow:0 18px 40px rgba(15,23,42,.06);
}
.afiliados-page .sect-title{
  margin:0 0 8px;
  font-weight:800;
  color:var(--text,#111322);
}
.afiliados-page .sect-sub{
  margin:0 0 8px;
  font-weight:800;
  color:var(--text,#111322);
}
.afiliados-page .muted{
  color:var(--muted,#6b7280);
  font-size:.9rem;
}

/* Layout principal */
.aff-grid{
  display:grid;
  grid-template-columns: minmax(0,1.1fr) minmax(0,1fr);
  gap:12px;
  margin-top:12px;
}
@media (max-width: 900px){
  .aff-grid{ grid-template-columns:1fr; }
}

/* Formulários */
.form-grid{
  display:grid;
  gap:10px;
}
.input-wrap{
  display:flex;
  flex-direction:column;
  gap:4px;
}
.field-label{
  font-size:.82rem;
  color:var(--muted,#6b7280);
}
.afiliados-page .field{
  width:100%;
  box-sizing:border-box;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  outline:none;
  font-size:.9rem;
}
.form-actions{
  display:flex;
  align-items:center;
  gap:8px;
  margin-top:4px;
}
.inline-msg{
  font-size:.86rem;
  color:#16a34a;
}
.inline-msg.is-error{ color:#b91c1c; }

/* Cabeçalho linha + filtro à direita */
.aff-head-inline{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:16px;
}
.aff-filter{ min-width:200px; }
@media (max-width: 640px){
  .aff-head-inline{
    flex-direction:column;
    align-items:flex-start;
  }
  .aff-filter{ width:100%; min-width:0; }
}

/* Região */
.table-region{ margin-top:8px; }

/* Tabelas (desktop) */
.table-wrap{
  position:relative;
  overflow:auto;
  -webkit-overflow-scrolling:touch;
  border-radius:12px;
}
.table-wrap::before,
.table-wrap::after{
  content:"";
  position:absolute;
  top:0;
  bottom:0;
  width:18px;
  pointer-events:none;
  z-index:2;
  display:none;
}
.table-wrap.shadow-left::before{
  display:block;
  left:0;
  background:linear-gradient(to right, rgba(255,255,255,1), rgba(255,255,255,0));
}
.table-wrap.shadow-right::after{
  display:block;
  right:0;
  background:linear-gradient(to left, rgba(255,255,255,1), rgba(255,255,255,0));
}

.tbl-aff{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  table-layout:fixed;
  min-width:860px;
  background:#f9fafb;
  font-size:.9rem;
}
.tbl-aff thead th{
  position:sticky;
  top:0;
  z-index:1;
  text-align:left;
  font-weight:700;
  color:#111827;
  background:#ffffff;
  padding:8px 8px;
  border-bottom:1px solid #e5e7eb;
}
.tbl-aff tbody td{
  padding:8px 8px;
  vertical-align:middle;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
  border-bottom:1px dashed #e5e7eb;
}
.ta-r{ text-align:right; }

/* Ações na coluna final (desktop) */
.aff-actions{
  display:flex;
  flex-wrap:wrap;
  gap:6px;
}

/* Botões */
.btn{
  padding:10px 14px;
  border-radius:10px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  cursor:pointer;
  font-size:.86rem;
}
.btn.btn-sm{ padding:8px 12px; }
.btn--ghost{ background:transparent; }

/* Cards (mobile) */
.aff-cards{
  display:grid;
  gap:10px;
}
.aff-card{
  border:1px solid #e5e7eb;
  border-radius:14px;
  background:#ffffff;
  padding:12px;
  box-shadow:0 10px 26px rgba(15,23,42,.06);
}
.aff-card__head{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
}
.aff-card__title{
  display:flex;
  flex-direction:column;
  gap:2px;
  min-width:0;
}
.aff-card__money{
  display:flex;
  flex-direction:column;
  gap:2px;
  text-align:right;
}
.aff-card__grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:8px;
  margin-top:10px;
}
@media (max-width: 480px){
  .aff-card__grid{ grid-template-columns:1fr; }
}
.kpi{ display:grid; gap:2px; min-width:0; }
.kpi .lbl{ font-size:.78rem; color:#6b7280; }
.kpi .val{
  font-weight:600;
  color:#111322;
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}

.aff-card__actions{
  margin-top:10px;
  display:flex;
  justify-content:flex-end;
  gap:8px;
  flex-wrap:wrap;
}

/* Rodapé Admin */
.admin-footer{
  margin-top:16px;
  padding:10px 2px 0;
  border-top:1px dashed rgba(148,163,184,.6);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  font-size:.78rem;
}
.admin-footer .muted{ margin:0; }
.admin-footer-tag{
  font-weight:600;
  color:#4b5563;
  white-space:nowrap;
}
@media (max-width:640px){
  .admin-footer{
    flex-direction:column;
    align-items:flex-start;
  }
}
</style>
