<?php
// Admin • Assinaturas — sem sidebar (menu está no header)
// Desktop: tabela com overflow otimizado
// Mobile: cards em blocos (sem horizontal scroll)
// Observação: alterações reais são aplicadas no backend (ASAAS).
?>
<section class="container admin subs-page" style="margin-top:18px">
  <section class="admin-main">
    <!-- Cabeçalho + filtros -->
    <header class="sect-head">
      <div class="glass-card">
        <h1 class="sect-title">Assinaturas</h1>
        <div class="filters-inline" role="group" aria-label="Filtros de assinaturas">
          <select id="s-status" class="field" aria-label="Filtrar por status">
            <option value="">Todas</option>
            <option value="ativa">Ativa</option>
            <option value="suspensa">Suspensa</option>
            <option value="cancelada">Cancelada</option>
          </select>
          <select id="s-plan" class="field" aria-label="Filtrar por plano">
            <option value="">Plano</option>
            <!-- opções via JS -->
          </select>
          <input id="s-q" class="field" type="search" placeholder="Buscar por usuário (id, e-mail ou nome)…" aria-label="Buscar">
        </div>
      </div>
    </header>

    <!-- Tabela (desktop) -->
    <div class="glass-card" style="margin-top:12px">
      <div class="table-wrap" role="region" aria-label="Tabela de assinaturas" id="subs-table-wrap">
        <table id="s-table" class="tbl-subs">
          <colgroup>
            <col style="width:80px" />    <!-- ID -->
            <col style="width:28%" />     <!-- Usuário -->
            <col style="width:12%" />     <!-- Plano -->
            <col style="width:130px" />   <!-- Status -->
            <col style="width:140px" />   <!-- Início -->
            <col style="width:140px" />   <!-- Renova -->
            <col style="width:120px" />   <!-- Valor -->
            <col style="width:120px" />   <!-- Ações -->
          </colgroup>
          <thead>
            <tr>
              <th>ID</th>
              <th>Usuário</th>
              <th>Plano</th>
              <th>Status</th>
              <th>Início</th>
              <th>Renova</th>
              <th>Valor</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="s-body">
            <tr class="muted"><td colspan="8">Carregando…</td></tr>
          </tbody>
        </table>
      </div>
      <p class="muted" style="margin-top:8px">Alterações (upgrade, downgrade, suspensão, cancelamento) são aplicadas no ASAAS via backend.</p>
    </div>

    <!-- Cards (mobile) -->
    <div class="glass-card only-mobile" style="margin-top:12px; display:none" id="subs-cards-box">
      <div id="subs-cards" class="subs-cards" role="list" aria-label="Lista de assinaturas"></div>
    </div>

    <div id="s-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
  </section>
</section>

<!-- Modal: editar assinatura -->
<div class="modal" id="s-modal" role="dialog" aria-modal="true" aria-labelledby="s-modal-title" style="display:none">
  <div class="modal-box glass-card">
    <h3 id="s-modal-title" style="margin:0 0 8px">Alterar assinatura</h3>
    <form class="form-grid" id="s-form" onsubmit="return false">
      <div class="grid-2">
        <div class="input-wrap">
          <label class="muted" for="sf_plan">Plano</label>
          <select id="sf_plan" class="field" aria-label="Selecionar plano"></select>
        </div>
        <div class="input-wrap">
          <label class="muted" for="sf_status">Status</label>
          <select id="sf_status" class="field" aria-label="Selecionar status">
            <option value="ativa">Ativa</option>
            <option value="suspensa">Suspensa</option>
            <option value="cancelada">Cancelada</option>
          </select>
        </div>
      </div>
      <div class="form-actions" style="margin-top:12px;display:flex;gap:8px;justify-content:flex-end">
        <button class="btn btn-sm" id="sf_save" type="submit">Salvar</button>
        <button class="btn btn-sm btn--ghost" id="sf_cancel" type="button">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const tbody   = document.getElementById('s-body');
  const alertEl = document.getElementById('s-alert');
  const modal   = document.getElementById('s-modal');
  const fStatus = document.getElementById('s-status');
  const fPlan   = document.getElementById('s-plan');
  const fQuery  = document.getElementById('s-q');
  const sfPlan  = document.getElementById('sf_plan');
  const sfStat  = document.getElementById('sf_status');
  const sfSave  = document.getElementById('sf_save');
  const sfCancel= document.getElementById('sf_cancel');

  const cardsBox = document.getElementById('subs-cards-box');
  const cardsEl  = document.getElementById('subs-cards');

  let planMap = {}; // {id: name}
  let lastSubs = [];
  let editingId = null;

  function setAlert(msg){
    alertEl.style.display='block';
    alertEl.textContent = msg;
    setTimeout(()=>alertEl.style.display='none', 2200);
  }

  // utils
  const escHtml = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  const escAttr = s => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  const money   = v => 'R$ ' + (Number(v||0)).toFixed(2).replace('.', ',');
  function statusChip(s){
    if (s==='ativa')     return '<span class="chip chip-success">Ativa</span>';
    if (s==='suspensa')  return '<span class="chip chip-pending">Suspensa</span>';
    return '<span class="chip chip-failed">Cancelada</span>';
  }
  function fmtDate(s){ if(!s) return '-'; return escHtml(String(s).replace('T',' ').slice(0,16)); }
  function debounce(fn, ms=300){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }

  async function loadPlans(){
    try{
      const r = await fetch('/?r=api/admin/plans/list');
      const j = await r.json();
      if(!r.ok) throw new Error(j.error||'Falha');
      planMap = {};
      const opts = ['<option value="">Plano</option>']
        .concat((j.plans||[]).map(p => {
          planMap[p.id] = p.name||p.id;
          return `<option value="${escAttr(p.id)}">${escHtml(p.name||p.id)}</option>`;
        }));
      fPlan.innerHTML = opts.join('');
      sfPlan.innerHTML = (j.plans||[]).map(p=>`<option value="${escAttr(p.id)}">${escHtml(p.name||p.id)}</option>`).join('');
    }catch(_){
      // fallback
      ['start','plus','prime'].forEach(id => planMap[id] = id[0].toUpperCase()+id.slice(1));
      fPlan.innerHTML = `<option value="">Plano</option><option value="start">Start</option><option value="plus">Plus</option><option value="prime">Prime</option>`;
      sfPlan.innerHTML = `<option value="start">Start</option><option value="plus">Plus</option><option value="prime">Prime</option>`;
    }
  }

  async function fetchSubs(){
    const st = fStatus.value || '';
    const pl = fPlan.value   || '';
    const q  = fQuery.value.trim();
    const qs = new URLSearchParams({status:st, plan:pl, q}).toString();
    const r  = await fetch('/?r=api/admin/subscriptions/list&' + qs);
    let j; try{ j = await r.json(); }catch(_){ setAlert('Erro ao carregar'); return {subscriptions:[]}; }
    if(!r.ok){ setAlert(j.error || 'Falha ao carregar'); return {subscriptions:[]}; }
    return j;
  }

  function renderTable(subs){
    if(!subs || subs.length===0){
      tbody.innerHTML = `<tr class="muted"><td colspan="8">Nenhuma assinatura encontrada.</td></tr>`;
      return;
    }
    tbody.innerHTML = subs.map(s => {
      const pid  = escAttr(s.plan_id || '');
      const pnm  = escHtml(s.plan_name || planMap[s.plan_id] || s.plan_id || '-');
      const uidL = s.user_name ? `<strong>${escHtml(s.user_name)}</strong>` : `#${escHtml(s.user_id)}`;
      const uem  = escHtml(s.user_email || '');
      return `
      <tr data-id="${escAttr(s.id)}">
        <td>${escHtml(s.id)}</td>
        <td>
          ${uidL}
          <div class="muted email-ellipsis" title="${uem}">${uem}</div>
        </td>
        <td>${pnm}</td>
        <td>${statusChip(s.status)}</td>
        <td>${fmtDate(s.started_at)}</td>
        <td>${fmtDate(s.renew_at)}</td>
        <td>${money(s.amount)}</td>
        <td><button class="btn btn-sm" data-edit="${escAttr(s.id)}" data-plan="${pid}" data-status="${escAttr(s.status||'')}">Editar</button></td>
      </tr>`;
    }).join('');
  }

  function renderCards(subs){
    if(!subs || subs.length===0){
      cardsEl.innerHTML = `<div class="muted">Nenhuma assinatura encontrada.</div>`;
      return;
    }
    cardsEl.innerHTML = subs.map(s => {
      const pid  = escAttr(s.plan_id || '');
      const pnm  = escHtml(s.plan_name || planMap[s.plan_id] || s.plan_id || '-');
      const uname= escHtml(s.user_name || ('#'+s.user_id));
      const uem  = escHtml(s.user_email || '');
      return `
      <article class="sub-card" data-id="${escAttr(s.id)}" role="listitem" aria-label="Assinatura #${escHtml(s.id)}">
        <header class="sub-head">
          <div class="sub-user">
            <strong class="sub-name">${uname}</strong>
            <span class="sub-email">${uem}</span>
          </div>
          <div class="sub-id">#${escHtml(s.id)}</div>
        </header>
        <div class="sub-grid">
          <div class="sub-field"><span class="lbl">Plano</span><span class="val">${pnm}</span></div>
          <div class="sub-field"><span class="lbl">Status</span><span class="val">${statusChip(s.status)}</span></div>
          <div class="sub-field"><span class="lbl">Início</span><span class="val">${fmtDate(s.started_at)}</span></div>
          <div class="sub-field"><span class="lbl">Renova</span><span class="val">${fmtDate(s.renew_at)}</span></div>
          <div class="sub-field"><span class="lbl">Valor</span><span class="val">${money(s.amount)}</span></div>
        </div>
        <footer class="sub-actions">
          <button class="btn btn-sm" data-edit="${escAttr(s.id)}" data-plan="${pid}" data-status="${escAttr(s.status||'')}">Editar</button>
        </footer>
      </article>`;
    }).join('');
  }

  async function render(){
    // skeletons
    tbody.innerHTML = `<tr class="muted"><td colspan="8">Carregando…</td></tr>`;
    cardsEl.innerHTML = `<div class="muted">Carregando…</div>`;

    const {subscriptions} = await fetchSubs();
    lastSubs = subscriptions || [];
    renderTable(lastSubs);
    renderCards(lastSubs);
    updateTableShadows(); // após render
  }

  // Filtros
  const renderDebounced = debounce(render, 300);
  fStatus.addEventListener('change', renderDebounced);
  fPlan  .addEventListener('change', renderDebounced);
  fQuery .addEventListener('input',  renderDebounced);
  fQuery .addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); render(); } });

  // Modal helpers
  const openM = ()=>{ modal.style.display='block'; };
  const closeM= ()=>{ modal.style.display='none'; editingId=null; };
  document.getElementById('sf_cancel').addEventListener('click', closeM);
  modal.addEventListener('click', e=>{ if(e.target===modal) closeM(); });
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeM(); });

  // Ações (tabela + cards)
  document.addEventListener('click', (e)=>{
    const b = e.target.closest('button[data-edit]');
    if(!b) return;
    editingId = b.dataset.edit;
    sfPlan.value = b.dataset.plan || '';
    sfStat.value = b.dataset.status || 'ativa';
    // se o plano não consta, adiciona opção ad hoc
    if(sfPlan && editingId && b.dataset.plan && !Array.from(sfPlan.options).some(o => o.value === b.dataset.plan)){
      const opt = document.createElement('option'); opt.value=b.dataset.plan; opt.textContent=b.dataset.plan; sfPlan.appendChild(opt);
      sfPlan.value = b.dataset.plan;
    }
    openM();
  });

  // Salvar
  sfSave.addEventListener('click', async ()=>{
    if(!editingId) return;
    const plan   = sfPlan.value;
    const status = sfStat.value;
    const r = await fetch('/?r=api/admin/subscriptions/save', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ id: editingId, plan_id: plan, status })
    });
    let j; try { j = await r.json(); } catch(_){ setAlert('Erro na resposta'); return; }
    if(!r.ok){ setAlert(j.error || 'Falha ao salvar'); return; }
    setAlert('Assinatura atualizada');
    closeM();
    render();
  });

  // ===== sombras do overflow da tabela (UX) =====
  const tw = document.getElementById('subs-table-wrap');
  function updateTableShadows(){
    if(!tw) return;
    const L = tw.scrollLeft;
    const W = tw.scrollWidth;
    const C = tw.clientWidth;
    tw.classList.toggle('shadow-left', L > 2);
    tw.classList.toggle('shadow-right', L + C < W - 2);
  }
  if(tw){
    tw.addEventListener('scroll', updateTableShadows, {passive:true});
    window.addEventListener('resize', updateTableShadows, {passive:true});
  }

  // init
  (async function init(){
    await loadPlans();
    await render();
  })();
})();
</script>

<style>
/* ===== Base (visual unificado) ===== */
.subs-page .glass-card{
  background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.10);
  padding:14px; border-radius:14px; color:#fff;
}
.subs-page .muted{ opacity:.86; font-size:.88rem; color:#cfe1ff; }
.subs-page .sect-title{ margin:0 0 10px; font-weight:800; }

/* Filtros */
.subs-page .field{
  width:100%; box-sizing:border-box; padding:10px 12px; border-radius:10px;
  border:1px solid rgba(255,255,255,.20); background:rgba(255,255,255,.08); color:#eaf3ff; outline:none;
}
.subs-page .filters-inline{ display:grid; grid-template-columns:180px 180px 1fr; gap:10px; margin-top:8px; }

/* Tabela (desktop) */
.table-wrap{
  position:relative;
  overflow:auto; -webkit-overflow-scrolling:touch;
  border-radius:10px;
}
.table-wrap::before,
.table-wrap::after{
  content:""; position:absolute; top:0; bottom:0; width:18px; pointer-events:none; z-index:2; display:none;
}
.table-wrap.shadow-left::before{ display:block; left:0; background:linear-gradient(to right, rgba(6,26,43,.85), rgba(6,26,43,0)); }
.table-wrap.shadow-right::after{ display:block; right:0; background:linear-gradient(to left, rgba(6,26,43,.85), rgba(6,26,43,0)); }

.tbl-subs{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  table-layout:fixed;
  min-width:1020px;
  background: rgba(255,255,255,.04);
}
.tbl-subs thead th{
  position:sticky; top:0; z-index:1;
  text-align:left; font-weight:800; color:#1c1130; background:#fff; padding:10px 8px;
  border-bottom:1px solid rgba(0,0,0,.06);
}
.tbl-subs tbody td{
  padding:10px 8px; vertical-align:middle; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.email-ellipsis{ font-size:.86rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

/* Chips */
.chip{
  display:inline-block; padding:4px 10px; border-radius:999px; font-size:.88rem; line-height:1;
  border:1px solid rgba(255,255,255,.22); background:rgba(255,255,255,.08); color:#fff;
}
.chip-success{ background:rgba(56,178,106,.18); border-color:rgba(56,178,106,.4); }
.chip-pending{ background:rgba(255,193,7,.18);  border-color:rgba(255,193,7,.45); }
.chip-failed{  background:rgba(255,77,79,.18);  border-color:rgba(255,77,79,.45); }

/* Botões / alertas */
.btn{ padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.10); color:#fff; cursor:pointer; }
.btn.btn-sm{ padding:8px 12px; }
.btn--ghost{ background:transparent; }
.alert{
  margin-top:10px; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.18); color:#fff;
  background:rgba(255,255,255,.06);
}

/* Modal */
.modal{ position:fixed; inset:0; background:rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center; z-index:100; }
.modal-box{ width:min(560px,92vw); }
.form-grid .grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.input-wrap label{ display:block; margin:0 0 6px; }

/* ===== Mobile (até 720px) — cards bonitos ===== */
@media (max-width: 720px){
  .subs-page .filters-inline{ grid-template-columns:1fr; gap:8px; }
  .subs-page .field{ padding:10px 12px; }

  /* esconde tabela e mostra cards */
  #subs-table-wrap{ display:none; }
  #subs-cards-box{ display:block !important; }

  .subs-cards{ display:grid; gap:10px; }
  .sub-card{
    border:1px solid rgba(255,255,255,.12);
    border-radius:12px;
    background:rgba(255,255,255,.06);
    padding:12px;
  }
  .sub-head{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
  .sub-user{ display:flex; flex-direction:column; min-width:0; }
  .sub-name{ font-weight:800; }
  .sub-email{ font-size:.86rem; opacity:.9; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .sub-id{ font-weight:800; opacity:.85; }

  .sub-grid{
    display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px;
  }
  @media (max-width: 480px){ .sub-grid{ grid-template-columns:1fr; } }
  .sub-field{ display:grid; gap:2px; }
  .sub-field .lbl{ font-size:.78rem; opacity:.9; color:#cfe1ff; }
  .sub-field .val{ font-weight:700; }

  .sub-actions{ margin-top:10px; display:flex; justify-content:flex-end; }
  .btn.btn-sm{ padding:10px 12px; }

  /* modal ocupa mais */
  .modal-box{ width:96vw !important; }
  .form-grid .grid-2{ grid-template-columns:1fr !important; }
}
</style>
