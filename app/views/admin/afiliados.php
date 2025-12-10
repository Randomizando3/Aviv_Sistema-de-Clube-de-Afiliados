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
            <span id="st_msg" class="inline-msg"></span>
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
  const p = new URLSearchParams();
  p.set('percent',     document.getElementById('st_percent').value);
  p.set('min_payout',  document.getElementById('st_min').value);
  p.set('cookie_days', document.getElementById('st_cookie').value);
  const r = await jfetch('/?r=api/admin/affiliate/settings/save',{method:'POST', body:p});
  document.getElementById('st_msg').textContent = (r && r.ok) ? 'Salvo!' : 'Erro';
  setTimeout(()=>document.getElementById('st_msg').textContent='', 1500);
  return false;
}

/* ===== Conversões ===== */
async function loadConversions() {
  const s  = document.getElementById('conv_filter').value || 'all';
  const el = document.getElementById('conv_list');
  el.innerHTML = '<p class="muted">Carregando…</p>';

  const r = await jfetch('/?r=api/admin/affiliate/list&status='+encodeURIComponent(s));
  if (!r || !r.data) { el.innerHTML = '<p class="muted">Erro ao carregar conversões.</p>'; return; }

  const rows = r.data.items || [];
  if (!rows.length) { el.innerHTML = '<p class="muted">Sem dados.</p>'; return; }

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
async function approveConv(id){
  const p = new URLSearchParams(); p.set('id', id);
  await jfetch('/?r=api/admin/affiliate/mark-paid', {method:'POST', body:p});
  loadConversions();
}

/* ===== Saques (payouts) ===== */
async function loadPayouts() {
  const s  = document.getElementById('payout_filter').value || '';
  const el = document.getElementById('payout_list');
  el.innerHTML = '<p class="muted">Carregando…</p>';

  const r = await jfetch('/?r=api/admin/affiliate/payouts/list' + (s ? ('&status='+encodeURIComponent(s)) : ''));
  if (!r || !r.data) { el.innerHTML = '<p class="muted">Erro ao carregar saques.</p>'; return; }

  const rows = r.data.items || [];
  if (!rows.length) { el.innerHTML = '<p class="muted">Sem dados.</p>'; return; }

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
  if (e.target.classList && e.target.classList.contains('table-wrap')) {
    updateTableShadows();
  }
}, true);
window.addEventListener('resize', updateTableShadows, {passive:true});

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
  .aff-grid{
    grid-template-columns:1fr;
  }
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

/* Cabeçalho linha + filtro à direita */
.aff-head-inline{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:16px;
}
.aff-filter{
  min-width:200px;
}
@media (max-width: 640px){
  .aff-head-inline{
    flex-direction:column;
    align-items:flex-start;
  }
  .aff-filter{
    width:100%;
  }
}

/* Tabelas e região scroll */
.table-region{
  margin-top:8px;
}
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
  min-width:720px;
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

/* Ações na coluna final */
.aff-actions{
  display:flex;
  flex-wrap:wrap;
  gap:6px;
}

/* Botões / alertas */
.btn{
  padding:10px 14px;
  border-radius:10px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  cursor:pointer;
  font-size:.86rem;
}
.btn.btn-sm{
  padding:8px 12px;
}
.btn--ghost{
  background:transparent;
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
.admin-footer .muted{
  margin:0;
}
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
