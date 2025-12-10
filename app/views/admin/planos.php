<?php
// Admin • Planos — sem sidebar, largura igual ao Header, tabela (desktop) + cards (mobile)
?>
<section class="container admin planos-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Admin • Planos</h1>
      <p class="muted">
        Gerencie os planos (id usado nas assinaturas, ex.: <code>start</code>, <code>plus</code>, <code>prime</code>).
      </p>
    </div>

    <!-- Novo plano -->
    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub">Novo plano</h2>
      <form id="plan-new" class="form-grid" action="#" onsubmit="return false;">
        <div class="input-wrap">
          <input class="field" id="pn-id"   type="text"   placeholder="ID (ex.: start)" required>
        </div>
        <div class="input-wrap">
          <input class="field" id="pn-name" type="text"   placeholder="Nome (ex.: Start)" required>
        </div>
        <div class="input-wrap">
          <input class="field" id="pn-pm"   type="number" step="0.01" min="0" placeholder="Preço mensal">
        </div>
        <div class="input-wrap">
          <input class="field" id="pn-py"   type="number" step="0.01" min="0" placeholder="Preço anual">
        </div>
        <div class="input-wrap">
          <select class="field" id="pn-status">
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
          </select>
        </div>
        <div class="form-actions">
          <button class="btn btn-sm" id="btn-create">Criar</button>
        </div>
      </form>
    </div>

    <!-- Lista (desktop: tabela) -->
    <div class="glass-card only-desktop" style="margin-top:12px">
      <h2 class="sect-sub">Lista de planos</h2>
      <div class="table-wrap" id="plans-table-wrap" role="region" aria-label="Tabela de planos">
        <table class="tbl-plans">
          <colgroup>
            <col style="width:160px" />  <!-- ID -->
            <col style="width:28%"   />  <!-- Nome -->
            <col style="width:130px" />  <!-- Mensal -->
            <col style="width:130px" />  <!-- Anual -->
            <col style="width:140px" />  <!-- Status -->
            <col style="width:90px"  />  <!-- Ordem -->
            <col style="width:180px" />  <!-- Ações -->
          </colgroup>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th class="ta-r">Mensal</th>
              <th class="ta-r">Anual</th>
              <th>Status</th>
              <th class="ta-c">Ordem</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="plans-body">
            <tr class="muted"><td colspan="7">Carregando…</td></tr>
          </tbody>
        </table>
      </div>
      <div class="inline-actions" style="margin-top:10px">
        <button class="btn btn-sm" id="btn-save-order">Salvar ordem</button>
      </div>
      <div id="plans-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
    </div>

    <!-- Lista (mobile: cards) -->
    <div class="glass-card only-mobile" style="margin-top:12px; display:none" id="plans-cards-box">
      <h2 class="sect-sub">Lista de planos</h2>
      <div id="plans-cards" class="plans-cards" role="list" aria-label="Lista de planos"></div>
      <div class="inline-actions" style="margin-top:10px">
        <button class="btn btn-sm" id="btn-save-order-m">Salvar ordem</button>
      </div>
      <div id="plans-alert-m" class="alert" role="status" aria-live="polite" style="display:none"></div>
    </div>
  </section>
</section>

<script>
const alertBox     = document.getElementById('plans-alert');
const alertBoxM    = document.getElementById('plans-alert-m');
const tableWrap    = document.getElementById('plans-table-wrap');
const tbody        = document.getElementById('plans-body');
const cardsBox     = document.getElementById('plans-cards-box');
const cardsEl      = document.getElementById('plans-cards');

function setAlert(msg, mobile=false){
  const el = mobile ? (alertBoxM||alertBox) : alertBox;
  if(!el) return;
  el.style.display='block';
  el.textContent=msg;
  setTimeout(()=>el.style.display='none',1800);
}
function escHtml(s){ return String(s ?? '').replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m])); }
function escAttr(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

async function loadPlans(){
  try{
    const r = await fetch('/?r=api/admin/plans/list');
    const j = await r.json();
    if(!r.ok){ setAlert(j.error||'Falha ao carregar'); return; }

    // ===== Desktop (tabela)
    tbody.innerHTML = (j.plans||[]).map(p => {
      const id   = escAttr(p.id ?? '');
      const name = escAttr(p.name ?? '');
      const pm   = escAttr(p.price_monthly ?? 0);
      const py   = escAttr(p.price_yearly  ?? 0);
      const st   = (p.status === 'active') ? 'active' : 'inactive';
      const ord  = escAttr(p.sort_order ?? 0);
      const desc = escHtml(p.description ?? '');

      return `
      <tr class="data-row" data-id="${id}">
        <td><code>${escHtml(p.id ?? '')}</code></td>
        <td><input class="cell-field" type="text" value="${name}" data-f="name"></td>
        <td><input class="cell-field ta-r" type="number" step="0.01" min="0" value="${pm}" data-f="price_monthly"></td>
        <td><input class="cell-field ta-r" type="number" step="0.01" min="0" value="${py}" data-f="price_yearly"></td>
        <td>
          <select class="cell-field" data-f="status">
            <option value="active" ${st==='active'?'selected':''}>Ativo</option>
            <option value="inactive" ${st!=='active'?'selected':''}>Inativo</option>
          </select>
        </td>
        <td><input class="cell-field ta-c" type="number" value="${ord}" data-f="sort_order"></td>
        <td class="actions">
          <button class="icon-btn" data-act="up" title="Mover para cima" aria-label="Mover para cima">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
            <span class="sr-only">Subir</span>
          </button>
          <button class="icon-btn" data-act="down" title="Mover para baixo" aria-label="Mover para baixo">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M19 12l-7 7-7-7"/></svg>
            <span class="sr-only">Descer</span>
          </button>
          <button class="icon-btn" data-act="save" title="Salvar" aria-label="Salvar">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21V8H7v13"/><path d="M7 3v5h8"/></svg>
            <span class="sr-only">Salvar</span>
          </button>
          <button class="icon-btn danger" data-act="del" title="Excluir" aria-label="Excluir">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
            <span class="sr-only">Excluir</span>
          </button>
        </td>
      </tr>
      <tr class="desc-row" data-id="${id}">
        <td colspan="7">
          <div class="desc-grid">
            <textarea class="field" rows="2" data-f="description" placeholder="Descrição / benefícios (uma por linha; aceita • e ;)">${desc}</textarea>
          </div>
        </td>
      </tr>`;
    }).join('');
    updateTableShadows();

    // ===== Mobile (cards)
    cardsEl.innerHTML = (j.plans||[]).map(p => {
      const id   = escAttr(p.id ?? '');
      const name = escAttr(p.name ?? '');
      const pm   = escAttr(p.price_monthly ?? 0);
      const py   = escAttr(p.price_yearly  ?? 0);
      const st   = (p.status === 'active') ? 'active' : 'inactive';
      const desc = escHtml(p.description ?? '');
      return `
        <article class="plan-card" data-id="${id}" role="listitem" aria-label="Plano ${id}">
          <div class="pc-head">
            <strong class="pc-id"><code>${escHtml(p.id ?? '')}</code></strong>
            <div class="pc-actions">
              <button class="icon-btn" data-act="up" title="Mover para cima" aria-label="Mover para cima">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5"/><path d="M5 12l7-7 7 7"/></svg>
              </button>
              <button class="icon-btn" data-act="down" title="Mover para baixo" aria-label="Mover para baixo">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M19 12l-7 7-7-7"/></svg>
              </button>
            </div>
          </div>

          <div class="grid-2">
            <input class="field" type="text" value="${name}" data-f="name" placeholder="Nome">
            <select class="field" data-f="status">
              <option value="active" ${st==='active'?'selected':''}>Ativo</option>
              <option value="inactive" ${st!=='active'?'selected':''}>Inativo</option>
            </select>
          </div>

          <div class="grid-2">
            <input class="field" type="number" step="0.01" min="0" value="${pm}" data-f="price_monthly" placeholder="Preço mensal">
            <input class="field" type="number" step="0.01" min="0" value="${py}" data-f="price_yearly"  placeholder="Preço anual">
          </div>

          <textarea class="field" rows="2" data-f="description" placeholder="Descrição / benefícios (uma por linha; aceita • e ;)">${desc}</textarea>

          <div class="pc-foot">
            <button class="btn btn-sm" data-act="save">Salvar</button>
            <button class="btn btn-sm btn--ghost danger" data-act="del">Excluir</button>
          </div>
        </article>
      `;
    }).join('');
  }catch(_){
    setAlert('Não foi possível carregar os planos agora.');
  }
}

/* ===== Reordenar (desktop) — move par: data-row + desc-row */
function movePair(tr, dir){
  if(!tr || !tr.classList.contains('data-row')) return;
  const desc = tr.nextElementSibling;
  if(!desc || !desc.classList.contains('desc-row')) return;

  if(dir==='up'){
    const prevDesc = tr.previousElementSibling;
    const prevData = prevDesc ? prevDesc.previousElementSibling : null;
    if(prevData && prevData.classList.contains('data-row')){
      tbody.insertBefore(tr, prevData);
      tbody.insertBefore(desc, prevData);
    }
  } else if(dir==='down'){
    const nextData = desc.nextElementSibling;
    const nextDesc = nextData ? nextData.nextElementSibling : null;
    if(nextData && nextDesc && nextData.classList.contains('data-row') && nextDesc.classList.contains('desc-row')){
      tbody.insertBefore(nextData, tr);
      tbody.insertBefore(nextDesc, tr);
    }
  }
}

/* ===== Reordenar (mobile) — move card */
function moveCard(card, dir){
  if(!card) return;
  if(dir==='up'){
    const prev = card.previousElementSibling;
    if(prev) cardsEl.insertBefore(card, prev);
  }else{
    const next = card.nextElementSibling;
    if(next) cardsEl.insertBefore(next, card);
  }
}

/* ===== Coleta de dados (escopo: TR da tabela OU CARD mobile) */
function collectData(scope){
  const data = {};
  scope.querySelectorAll('[data-f]').forEach(el => data[el.dataset.f] = (el.value||'').trim());
  if(scope.classList.contains('data-row')){
    const descRow = scope.nextElementSibling;
    if(descRow && descRow.classList.contains('desc-row')){
      const txt = descRow.querySelector('[data-f="description"]')?.value || '';
      data.description = txt.trim();
    }
  }
  return data;
}

/* ===== Handlers desktop (tbody) */
tbody.addEventListener('click', async (e)=>{
  const btn = e.target.closest('button[data-act]');
  if(!btn) return;
  const tr = btn.closest('tr.data-row');
  const id = tr?.dataset.id;
  const act = btn.dataset.act;

  if(act==='up'){ movePair(tr,'up'); return; }
  if(act==='down'){ movePair(tr,'down'); return; }

  if(act==='save'){
    const data = collectData(tr); data.id = id;
    const r = await fetch('/?r=api/admin/plans/save', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams(data)
    });
    const j = await r.json();
    if(!r.ok) { setAlert(j.error||'Erro ao salvar'); return; }
    setAlert('Plano salvo');
    loadPlans();
  }

  if(act==='del'){
    if(!confirm('Excluir este plano?')) return;
    const r = await fetch('/?r=api/admin/plans/delete', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({id})
    });
    const j = await r.json();
    if(!r.ok) { setAlert(j.error||'Erro ao excluir'); return; }
    setAlert('Plano excluído');
    loadPlans();
  }
});

/* ===== Handlers mobile (cards) */
cardsEl.addEventListener('click', async (e)=>{
  const btn = e.target.closest('button[data-act]');
  if(!btn) return;
  const card = btn.closest('.plan-card');
  const id   = card?.dataset.id;
  const act  = btn.dataset.act;

  if(act==='up'){ moveCard(card,'up'); return; }
  if(act==='down'){ moveCard(card,'down'); return; }

  if(act==='save'){
    const data = collectData(card); data.id = id;
    const r = await fetch('/?r=api/admin/plans/save', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams(data)
    });
    const j = await r.json();
    if(!r.ok) { setAlert(j.error||'Erro ao salvar', true); return; }
    setAlert('Plano salvo', true);
    loadPlans();
  }

  if(act==='del'){
    if(!confirm('Excluir este plano?')) return;
    const r = await fetch('/?r=api/admin/plans/delete', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({id})
    });
    const j = await r.json();
    if(!r.ok) { setAlert(j.error||'Erro ao excluir', true); return; }
    setAlert('Plano excluído', true);
    loadPlans();
  }
});

/* ===== Salvar Ordem (desktop + mobile) */
async function saveCurrentOrder(){
  let ids = [];
  const isMobile = window.matchMedia('(max-width: 720px)').matches;
  if(isMobile){
    ids = Array.from(cardsEl.querySelectorAll('.plan-card')).map(el => el.dataset.id);
  }else{
    ids = Array.from(tbody.querySelectorAll('tr.data-row')).map(tr => tr.dataset.id);
  }
  const r = await fetch('/?r=api/admin/plans/reorder', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ order: ids.join(',') })
  });
  const j = await r.json();
  if(!r.ok){ setAlert(j.error||'Erro ao salvar ordem', isMobile); return; }
  setAlert('Ordem salva', isMobile);
  loadPlans();
}
document.getElementById('btn-save-order')  .addEventListener('click', saveCurrentOrder);
document.getElementById('btn-save-order-m')?.addEventListener('click', saveCurrentOrder);

/* ===== Criar plano */
document.getElementById('btn-create').addEventListener('click', async ()=>{
  const id = document.getElementById('pn-id').value.trim();
  const name = document.getElementById('pn-name').value.trim();
  const pm = document.getElementById('pn-pm').value || 0;
  const py = document.getElementById('pn-py').value || 0;
  const status = document.getElementById('pn-status').value;
  if(!id || !name) return setAlert('Preencha ID e Nome');

  const r = await fetch('/?r=api/admin/plans/save', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ id, name, price_monthly: pm, price_yearly: py, status })
  });
  const j = await r.json();
  if(!r.ok){ setAlert(j.error||'Erro ao criar'); return; }
  setAlert('Plano criado');
  document.getElementById('plan-new').reset();
  loadPlans();
});

/* ===== Sombras de overflow na tabela ===== */
function updateTableShadows(){
  if(!tableWrap) return;
  const L = tableWrap.scrollLeft;
  const W = tableWrap.scrollWidth;
  const C = tableWrap.clientWidth;
  tableWrap.classList.toggle('shadow-left', L > 2);
  tableWrap.classList.toggle('shadow-right', L + C < W - 2);
}
tableWrap?.addEventListener('scroll', updateTableShadows, {passive:true});
window.addEventListener('resize', updateTableShadows, {passive:true});

/* init */
loadPlans();


let CUR_PLAN_ID = null;
let PENDING_BADGE_PLAN_ID = null;

(async function loadCurrent(){
  try{
    const r = await fetch('/?r=api/member/overview');
    if (!r.ok) return;
    const j = await r.json();
    if (!j) return;

    CUR_PLAN_ID = j.subscription?.plan_id || null;
    PENDING_BADGE_PLAN_ID = j.pendingBadge?.plan_id || null;

    document.getElementById('cur-wrap').style.display='flex';
    document.getElementById('cur-name').textContent =
      (j.plan?.name || j.subscription?.plan_id || '—');
    const amt = +(j.subscription?.amount||0);
    document.getElementById('cur-amount').textContent =
      amt ? ('R$ ' + amt.toFixed(2).replace('.',',')) : '—';
    document.getElementById('cur-meta').textContent =
      (j.subscription?.renew_at ? ('Renova em ' + j.subscription.renew_at) : 'Sem renovação');
  }catch(e){}
})();
</script>

<style>
/* ===== util ===== */
.sr-only{
  position:absolute;width:1px;height:1px;padding:0;margin:-1px;
  overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;
}
.ta-r{ text-align:right; }
.ta-c{ text-align:center; }

/* ===== largura idêntica ao Header ===== */
.container.admin{
  width:min(92vw, var(--container)) !important;
  margin-inline:auto;
  padding-inline:0;
}

/* ===== base visual clean ===== */
.glass-card{
  background:rgba(255,255,255,.92);
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:18px;
  color:var(--text,#111322);
  box-shadow:0 18px 40px rgba(15,23,42,.06);
}
.sect-title{
  margin:0 0 8px;
  font-weight:800;
  color:var(--text,#111322);
}
.sect-sub{
  margin:0 0 8px;
  font-weight:700;
  color:var(--text,#111322);
}
.muted{
  color:var(--muted,#6b7280);
  opacity:1;
  font-size:.9rem;
}

/* ===== form novo plano ===== */
.form-grid{
  display:grid;
  grid-template-columns:repeat(6,minmax(0,1fr));
  gap:10px;
}
@media (max-width:980px){
  .form-grid{ grid-template-columns:1fr 1fr; }
  .form-actions{ grid-column:1/-1; }
}
.field{
  width:100%;
  box-sizing:border-box;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  outline:none;
}
.form-actions{
  display:flex;
  align-items:center;
  gap:8px;
}

/* botões */
.btn{
  padding:10px 14px;
  border-radius:10px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  cursor:pointer;
}
.btn-sm{ padding:8px 12px; }
.btn--ghost{
  background:transparent;
  border:1px solid #d0d7e2;
}
.btn.danger,
.btn.btn--ghost.danger{
  border-color:#fecaca;
  color:#b91c1c;
}

/* alert */
.alert{
  margin-top:10px;
  padding:10px 12px;
  border-radius:10px;
  background:#f9fafb;
  border:1px solid #e5e7eb;
  color:#111322;
}

/* ===== tabela (desktop) ===== */
.only-desktop{ display:block; }
.only-mobile{ display:none; }

.table-wrap{
  position:relative;
  overflow:auto;
  -webkit-overflow-scrolling:touch;
  border-radius:14px;
  background:#ffffff;
}

/* sombras suaves nas bordas ao rolar */
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
  background:linear-gradient(to right, rgba(148,163,184,.5), rgba(148,163,184,0));
}
.table-wrap.shadow-right::after{
  display:block;
  right:0;
  background:linear-gradient(to left, rgba(148,163,184,.5), rgba(148,163,184,0));
}

.tbl-plans{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  table-layout:fixed;
  min-width:980px;
  background:#ffffff;
}
.tbl-plans thead th{
  position:sticky;
  top:0;
  z-index:1;
  text-align:left;
  font-weight:700;
  color:#111322;
  background:#f8fafc;
  padding:10px 8px;
  border-bottom:1px solid #e5e7eb;
}
.tbl-plans td{
  padding:10px 8px;
  vertical-align:middle;
  white-space:nowrap;
  border-bottom:1px solid #eef2f7;
}
.tbl-plans td:nth-child(2){ white-space:normal; } /* Nome pode quebrar */

/* inputs da tabela */
.cell-field{
  width:100%;
  box-sizing:border-box;
  padding:8px 10px;
  border-radius:10px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
}
.tbl-plans input[type="number"]{ appearance:textfield; }
.tbl-plans input[type="number"]::-webkit-outer-spin-button,
.tbl-plans input[type="number"]::-webkit-inner-spin-button{
  -webkit-appearance:none;
  margin:0;
}

/* linha extra de descrição */
.data-row + .desc-row td{ padding-bottom:14px; }
.desc-row td{ padding-top:0; border-bottom:1px solid #eef2f7; }
.desc-grid{ display:grid; grid-template-columns:1fr; gap:8px; }

/* ações (ícones) */
.tbl-plans td.actions{
  display:flex;
  align-items:center;
  gap:8px;
  flex-wrap:nowrap;
}
.icon-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:36px;
  height:34px;
  border-radius:10px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  cursor:pointer;
  transition:background .15s ease, transform .05s ease, box-shadow .15s ease;
}
.icon-btn:hover{
  background:#f3f4ff;
  box-shadow:0 2px 6px rgba(15,23,42,.08);
}
.icon-btn:active{ transform:translateY(1px); }
.icon-btn.danger{
  border-color:#fecaca;
  background:#fef2f2;
  color:#b91c1c;
}

/* ===== mobile (≤ 720px): cards ===== */
@media (max-width:720px){
  .only-desktop{ display:none; }
  .only-mobile{ display:block !important; }

  .plans-cards{
    display:grid;
    gap:12px;
  }
  .plan-card{
    border:1px solid #d0d7e2;
    border-radius:16px;
    background:#ffffff;
    padding:14px;
    box-shadow:0 10px 25px rgba(15,23,42,.04);
  }
  .pc-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-bottom:8px;
  }
  .pc-actions{ display:flex; gap:8px; }
  .grid-2{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
  }
  @media (max-width:480px){
    .grid-2{ grid-template-columns:1fr; }
  }
  .pc-foot{
    display:flex;
    gap:8px;
    justify-content:flex-end;
    margin-top:10px;
  }
}
</style>
