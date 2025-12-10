<?php
$u = Auth::user();
if (!$u || ($u['role'] ?? 'member') !== 'admin') {
  http_response_code(403);
  echo "<p style='padding:16px'>Acesso negado.</p>";
  return;
}
?>
<section class="container admin ads-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Admin • Publicidade</h1>
      <p class="muted">Gerencie os planos, visualize as campanhas com imagens e confirme pagamentos para ativá-las.</p>
    </div>

    <!-- FLASH -->
    <div id="flash" style="margin-top:10px"></div>

    <!-- ===== Planos ===== -->
    <div class="glass-card" style="margin-top:12px">
      <header class="card-head">
        <h2 class="sect-sub">Planos de Publicidade</h2>
        <div class="tools tools--left">
          <label class="muted chk">
            <input type="checkbox" id="toggle-inactive" />
            Mostrar inativos
          </label>
        </div>
      </header>

      <div class="board board--plans">
        <!-- Form (E) -->
        <form id="plan-form" onsubmit="return false;" class="form-grid form-card">
          <input class="field" name="id" type="hidden">
          <div class="input-wrap">
            <label>Nome*</label>
            <input class="field" name="name" placeholder="Nome do plano" required>
          </div>
          <div class="input-wrap">
            <label>Visualizações*</label>
            <input class="field" name="view_quota" type="number" min="1" placeholder="ex.: 10.000" required>
          </div>
          <div class="input-wrap">
            <label>Preço (R$)</label>
            <input class="field" name="price" type="number" step="0.01" placeholder="ex.: 199.90">
          </div>
          <div class="input-wrap">
            <label>Ordem</label>
            <input class="field" name="sort_order" type="number" placeholder="ex.: 1">
          </div>
          <div class="input-wrap">
            <label>Status</label>
            <select class="field" name="status">
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
            </select>
          </div>
          <div class="input-wrap span-2">
            <label>Descrição (opcional)</label>
            <textarea class="field" name="description" rows="4" placeholder="Observações / detalhes do plano"></textarea>
          </div>
          <div class="actions span-2">
            <button id="plan-save" class="btn btn-sm" type="button">Salvar/Atualizar</button>
            <button id="plan-reset" class="btn btn-sm btn--ghost" type="button">Limpar</button>
          </div>
        </form>

        <!-- Lista (D) -->
        <section class="list-card">
          <div class="list-tools">
            <input id="plan-q" class="field" placeholder="Buscar plano pelo nome..." />
          </div>
          <div id="plans" class="table-wrap slim" aria-live="polite"></div>
        </section>
      </div>
    </div>

    <!-- ===== Campanhas (cards com imagens) ===== -->
    <div class="glass-card" style="margin-top:12px">
      <header class="card-head">
        <h2 class="sect-sub">Campanhas cadastradas</h2>
        <!-- alinhados à direita -->
        <div class="tools tools--right">
          <input id="camp-q" class="field" placeholder="Buscar por título, parceiro ou usuário..." />
          <div class="combo combo--thick" data-single id="camp-status-combo">
            <button type="button" class="combo-btn" aria-expanded="false">
              <span class="combo-label">Status: Todos</span>
              <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="combo-menu">
              <div class="combo-list">
                <label class="combo-opt"><input type="radio" name="campst" value=""> <span>Todos</span></label>
                <label class="combo-opt"><input type="radio" name="campst" value="active"> <span>Ativa</span></label>
                <label class="combo-opt"><input type="radio" name="campst" value="pending_payment"> <span>Pendente</span></label>
                <label class="combo-opt"><input type="radio" name="campst" value="exhausted"> <span>Exaurida</span></label>
                <label class="combo-opt"><input type="radio" name="campst" value="inactive"> <span>Inativa</span></label>
                <label class="combo-opt"><input type="radio" name="campst" value="canceled"> <span>Cancelada</span></label>
              </div>
            </div>
            <input type="hidden" id="camp-status" value="">
          </div>
        </div>
      </header>

      <div id="camps" class="cards-grid" aria-live="polite"></div>
    </div>

    <!-- ===== Pedidos ====== -->
    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub">Pedidos de Campanha</h2>
      <div id="orders" class="table-wrap"></div>
    </div>

    <!-- Rodapé admin -->
    <footer class="admin-footer">
      <p class="muted">Área de publicidade — controle de planos, campanhas e pedidos de anúncio. As ativações efetivas dependem da confirmação de pagamento.</p>
      <span class="admin-footer-tag">Admin • Publicidade</span>
    </footer>
  </section>
</section>

<script>
(function(){
  const $ = s => document.querySelector(s);

  function flash(type, msg, ms=6000){
    const el = document.createElement('div');
    el.className = 'flash ' + (type==='ok' ? 'flash--ok' : type==='warn' ? 'flash--warn' : 'flash--err');
    el.innerHTML = `<strong>${type==='ok'?'Sucesso': type==='warn'?'Atenção':'Erro'}:</strong> ${msg}`;
    document.getElementById('flash').appendChild(el);
    if (ms>0) setTimeout(()=> el.remove(), ms);
  }
  const esc = s => String(s||'').replace(/[&<>"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

  function badge(st){
    const map = {
      'active':{t:'Ativa',cls:'bdg--ok'},
      'pending_payment':{t:'Pendente',cls:'bdg--warn'},
      'exhausted':{t:'Exaurida',cls:'bdg--muted'},
      'inactive':{t:'Inativa',cls:'bdg--muted'},
      'canceled':{t:'Cancelada',cls:'bdg--err'},
      'overdue':{t:'Vencida',cls:'bdg--err'},
      'refunded':{t:'Estornada',cls:'bdg--muted'}
    };
    const it = map[st] || {t:(st||'-'), cls:'bdg--muted'};
    return `<span class="bdg ${it.cls}">${it.t}</span>`;
  }

  // ====== PLANOS ======
  let PLAN_ROWS = [];
  const planFilter = () => {
    const q = ($('#plan-q').value||'').toLowerCase();
    renderPlansTable(PLAN_ROWS.filter(p => !q || (String(p.name||'').toLowerCase().includes(q))));
  };
  async function loadPlans(){
    const showInactive = $('#toggle-inactive').checked;
    const url = showInactive ? '/?r=api/admin/ads/plans/list' : '/?r=api/partner/ads/plans';
    const res = await fetch(url);
    let j; try { j = await res.json(); } catch(e){ $('#plans').innerHTML='<p class="muted">Erro ao carregar.</p>'; return; }
    if(!j.ok && !Array.isArray(j)){ $('#plans').innerHTML='<p class="muted">Falha ao carregar planos.</p>'; return; }
    PLAN_ROWS = j.data || j || [];
    planFilter();
    updateTableShadows();
  }
  function renderPlansTable(rows){
    if(!rows.length){ $('#plans').innerHTML = '<p class="muted">Nenhum plano encontrado.</p>'; return; }
    let html = `<table class="tbl"><thead><tr>
      <th>ID</th><th>Nome</th><th>Views</th><th>Preço</th><th>Status</th><th>Ordem</th><th>Ações</th>
    </tr></thead><tbody>`;
    rows.forEach(p=>{
      html += `<tr>
        <td>${p.id}</td>
        <td>${esc(p.name)}</td>
        <td>${Number(p.view_quota||0).toLocaleString('pt-BR')}</td>
        <td>R$ ${Number(p.price||0).toFixed(2)}</td>
        <td>${esc(p.status||'-')}</td>
        <td>${p.sort_order||0}</td>
        <td><button class="btn btn-sm" data-edit='${esc(JSON.stringify(p)).replace(/"/g,'&quot;')}'>Editar</button></td>
      </tr>`;
    });
    html += `</tbody></table>`;
    $('#plans').innerHTML = html;

    $('#plans').querySelectorAll('[data-edit]').forEach(btn=>{
      btn.onclick = ()=>{
        const p = JSON.parse(btn.getAttribute('data-edit'));
        const f = $('#plan-form');
        f.id.value           = p.id;
        f.name.value         = p.name||'';
        f.view_quota.value   = p.view_quota||'';
        f.price.value        = p.price||'';
        f.sort_order.value   = p.sort_order||0;
        f.status.value       = p.status||'active';
        f.description.value  = p.description||'';
        window.scrollTo({top: f.getBoundingClientRect().top + window.scrollY - 80, behavior:'smooth'});
      };
    });
    updateTableShadows();
  }
  $('#plan-save').onclick = async ()=>{
    const f  = $('#plan-form');
    const fd = new FormData(f);
    const r  = await fetch('/?r=api/admin/ads/plans/save', {method:'POST', body:fd});
    let j; try { j = await r.json(); } catch(e){ return flash('err','Erro de resposta ao salvar'); }
    if(!j.ok){ return flash('err', j.error||'Falha ao salvar'); }
    flash('ok','Plano salvo/atualizado.');
    f.reset();
    loadPlans();
  };
  $('#plan-reset').onclick = ()=> $('#plan-form').reset();
  $('#toggle-inactive').onchange = loadPlans;
  $('#plan-q').addEventListener('input', planFilter);

  // ====== CAMPANHAS ======
  let CAMPS = [];
  const imgSlots = c => ([
    {k:'img_sky_1',label:'sky 1'},
    {k:'img_sky_2',label:'sky 2'},
    {k:'img_top_468',label:'top 468'},
    {k:'img_square_1',label:'square 1'},
    {k:'img_square_2',label:'square 2'},
  ].map(s=>({...s,url:(c[s.k]||'').trim()})));

  function renderCampCard(c){
    const imgs = imgSlots(c);
    return `
      <article class="camp-card admin" role="listitem">
        <header class="camp-head">
          <div class="camp-titles">
            <h3 class="c-title">${esc(c.title||'-')}</h3>
            <div class="c-meta">
              ${badge(c.status||'inactive')}
              ${c.target_url ? `<a class="chip-link" href="${esc(c.target_url)}" target="_blank" rel="noopener">Abrir</a>` : ''}
            </div>
          </div>
          <div class="camp-who">
            <div class="who-row"><span class="who-lbl">Parceiro:</span> <span class="who-val">${esc(c.business_name||'-')}</span></div>
            <div class="who-row"><span class="who-lbl">Usuário:</span> <span class="who-val">${esc(c.user_name||'-')}</span></div>
            <div class="who-row"><span class="who-lbl">Criado:</span> <span class="who-val">${esc((c.created_at||'').replace('T',' ').replace('Z',''))}</span></div>
          </div>
        </header>
        <section class="gal">
          ${imgs.map(s => s.url
            ? `<a class="tile" href="${esc(s.url)}" target="_blank" rel="noopener">
                 <img src="${esc(s.url)}" alt="${esc(s.label)}"><span class="gm-tag">${esc(s.label)}</span>
               </a>`
            : `<div class="tile tile--ph"><span class="gm-tag">${esc(s.label)}</span><span class="ph">sem imagem</span></div>`
          ).join('')}
        </section>
      </article>
    `;
  }
  function renderCamps(){
    const q = ($('#camp-q').value||'').toLowerCase();
    const st = $('#camp-status').value;
    const arr = CAMPS.filter(c=>{
      const hay = [c.title, c.business_name, c.user_name].map(x=>String(x||'').toLowerCase()).join(' ');
      const okQ = !q || hay.includes(q);
      const okS = !st || String(c.status||'')===st;
      return okQ && okS;
    });
    const box = $('#camps');
    if(!arr.length){ box.innerHTML = '<p class="muted">Nenhuma campanha encontrado.</p>'; return; }
    box.innerHTML = arr.map(renderCampCard).join('');
  }
  async function loadCampaigns(){
    const r = await fetch('/?r=api/admin/ads/campaigns');
    let j; try { j = await r.json(); } catch(e){ $('#camps').innerHTML='<p class="muted">Erro ao carregar.</p>'; return; }
    if(!j.ok){ $('#camps').innerHTML='<p class="muted">Falha ao carregar campanhas.</p>'; return; }
    CAMPS = j.data || [];
    renderCamps();
  }

  // combo de status
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('#camp-status-combo .combo-btn');
    if (btn){
      const combo = btn.closest('.combo');
      const open = combo.hasAttribute('data-open');
      document.querySelectorAll('.combo[data-open]')?.forEach(c=> c.removeAttribute('data-open'));
      if (!open){ combo.setAttribute('data-open',''); btn.setAttribute('aria-expanded','true'); }
      else { combo.removeAttribute('data-open'); btn.setAttribute('aria-expanded','false'); }
      return;
    }
    const opt = e.target.closest('#camp-status-combo .combo-opt');
    if (opt){
      const radio = opt.querySelector('input[type="radio"]'); if(!radio) return;
      radio.checked = true;
      const val = radio.value;
      $('#camp-status').value = val;
      const label = opt.querySelector('span')?.textContent || 'Todos';
      document.querySelector('#camp-status-combo .combo-label').textContent = 'Status: ' + label;
      const combo = opt.closest('.combo'); combo.removeAttribute('data-open'); combo.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');
      renderCamps();
      return;
    }
    if (!e.target.closest('.combo')) {
      document.querySelectorAll('.combo[data-open]')?.forEach(c=> c.removeAttribute('data-open'));
      document.querySelectorAll('.combo .combo-btn')?.forEach(b=> b.setAttribute('aria-expanded','false'));
    }
  });
  $('#camp-q').addEventListener('input', renderCamps);

  // ====== PEDIDOS ======
  async function loadOrders(){
    const r = await fetch('/?r=api/admin/ads/orders');
    let j; try { j = await r.json(); } catch(e){ $('#orders').innerHTML='<p class="muted">Erro ao carregar.</p>'; return; }
    if(!j.ok){ $('#orders').innerHTML='<p class="muted">Falha ao carregar pedidos.</p>'; return; }
    const rows = j.data || [];
    if(!rows.length){ $('#orders').innerHTML = '<p class="muted">Nenhum pedido até o momento.</p>'; return; }

    let html = `<table class="tbl"><thead><tr>
      <th>ID</th><th>Parceiro</th><th>Plano</th><th>Título campanha</th><th>Status</th>
      <th>Quota</th><th>Usadas</th><th>Valor</th><th>Criado em</th><th>Ações</th>
    </tr></thead><tbody>`;
    rows.forEach(o=>{
      html += `<tr>
        <td>${o.id}</td>
        <td>${esc(o.business_name||'-')}</td>
        <td>${esc(o.plan_name)}</td>
        <td>${esc(o.title)}</td>
        <td>${badge(o.status)}</td>
        <td>${o.quota_total}</td>
        <td>${o.quota_used}</td>
        <td>R$ ${Number(o.amount||0).toFixed(2)}</td>
        <td>${(o.created_at||'').replace('T',' ').replace('Z','')}</td>
        <td>${
          o.status==='pending_payment'
            ? `<button class="btn btn-sm" data-confirm="${o.id}">Confirmar pagamento</button>`
            : '—'
        }</td>
      </tr>`;
    });
    html += `</tbody></table>`;
    $('#orders').innerHTML = html;

    $('#orders').querySelectorAll('[data-confirm]').forEach(btn=>{
      btn.onclick = async ()=>{
        const fd = new FormData(); fd.set('id', btn.dataset.confirm);
        const r = await fetch('/?r=api/admin/ads/orders/confirm', {method:'POST', body:fd});
        let j; try { j = await r.json(); } catch(e){ return flash('err','Erro ao confirmar'); }
        if(!j.ok){ return flash('err', j.error||'Falha ao confirmar'); }
        flash('ok','Pagamento confirmado e campanha ativada.');
        loadOrders(); loadCampaigns();
      };
    });
    updateTableShadows();
  }

  /* ===== Sombras nas tabelas ===== */
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

  // init
  loadPlans();
  loadCampaigns();
  loadOrders();
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

/* ===== base visual clean (igual planos-page) ===== */
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

/* ===== formulário de planos ===== */
.form-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:10px;
}
.input-wrap{
  display:flex;
  flex-direction:column;
  gap:6px;
}
.input-wrap label{
  font-size:.86rem;
  color:var(--muted,#6b7280);
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
textarea.field{ min-height:120px; }
.span-2{ grid-column:1 / -1; }
.actions{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
}

/* ===== board de planos (form + lista) ===== */
.board--plans{
  display:grid;
  grid-template-columns:420px 1fr;
  gap:16px;
  align-items:flex-start;
}
.form-card{
  border:1px solid #e5e7eb;
  border-radius:16px;
  background:#ffffff;
  padding:14px;
}
.list-card{
  border:1px solid #e5e7eb;
  border-radius:16px;
  background:#ffffff;
  padding:14px;
}
.list-tools{
  display:flex;
  gap:8px;
  align-items:center;
  margin-bottom:8px;
}
@media (max-width:1100px){
  .board--plans{ grid-template-columns:1fr; }
}

/* ===== header card + ferramentas ===== */
.card-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}
.tools{
  display:flex;
  gap:10px;
  align-items:center;
  flex-wrap:wrap;
}
.tools--left{ margin-left:auto; }
.tools--right{
  margin-left:auto;
  justify-content:flex-end;
}
.tools--right input.field{
  flex:0 0 320px;
  max-width:320px;
}
#camp-status-combo{
  flex:0 0 220px;
  min-width:220px;
}
@media (max-width:820px){
  .tools--right{ justify-content:stretch; }
  .tools--right input.field{ flex:1 1 100%; max-width:none; }
  #camp-status-combo{ flex:1 1 100%; min-width:0; }
}

/* ===== table-wrap + sombras (igual planos) ===== */
.table-wrap{
  position:relative;
  overflow:auto;
  -webkit-overflow-scrolling:touch;
  border-radius:14px;
  background:#ffffff;
}
.table-wrap.slim .tbl td,
.table-wrap.slim .tbl th{
  padding:7px;
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
  background:linear-gradient(to right, rgba(148,163,184,.5), rgba(148,163,184,0));
}
.table-wrap.shadow-right::after{
  display:block;
  right:0;
  background:linear-gradient(to left, rgba(148,163,184,.5), rgba(148,163,184,0));
}

/* ===== tabelas genéricas ===== */
.tbl{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  table-layout:fixed;
  min-width:720px;
  background:#ffffff;
}
.tbl thead th{
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
.tbl td{
  padding:10px 8px;
  vertical-align:middle;
  white-space:nowrap;
  border-bottom:1px solid #eef2f7;
  font-size:.88rem;
}

/* ===== botões ===== */
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

/* ===== badges ===== */
.bdg{
  display:inline-block;
  padding:.15rem .5rem;
  border-radius:999px;
  font-size:.75rem;
  line-height:1;
  font-weight:700;
}
.bdg--ok{
  background:#e6f7ec;
  color:#0f7a2f;
  border:1px solid #b8ebc6;
}
.bdg--warn{
  background:#fff7e6;
  color:#8a5a00;
  border:1px solid #ffe1a8;
}
.bdg--err{
  background:#ffecec;
  color:#a10000;
  border:1px solid #ffc9c9;
}
.bdg--muted{
  background:#eef3f8;
  color:#3b556e;
  border:1px solid #d6e0ea;
}

/* ===== cards de campanhas (estilo claro) ===== */
.cards-grid{
  display:grid;
  gap:12px;
  grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
}
.camp-card{
  border:1px solid #d0d7e2;
  border-radius:16px;
  background:#ffffff;
  padding:14px;
  color:#111322;
  display:flex;
  flex-direction:column;
  gap:10px;
  box-shadow:0 10px 25px rgba(15,23,42,.04);
}
.camp-head{
  display:grid;
  grid-template-columns:1fr auto;
  gap:12px;
  align-items:start;
}
.camp-titles .c-title{
  margin:0 0 4px;
  font-weight:800;
}
.c-meta{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  align-items:center;
}
.camp-who{
  display:grid;
  gap:4px;
  min-width:240px;
  font-size:.85rem;
}
.who-row{
  display:flex;
  gap:6px;
  justify-content:flex-start;
}
.who-lbl{ opacity:.8; }
.who-val{ font-weight:600; }

/* galeria de imagens */
.gal{
  display:grid;
  grid-template-columns:repeat(5,minmax(0,1fr));
  gap:8px;
}
.tile{
  position:relative;
  border:1px solid #e5e7eb;
  border-radius:10px;
  overflow:hidden;
  background:#f9fafb;
  display:block;
}
.tile img{
  width:100%;
  height:86px;
  object-fit:cover;
  display:block;
}
.tile--ph{
  display:flex;
  align-items:center;
  justify-content:center;
  height:86px;
  color:#6b7280;
  font-size:.8rem;
}
.gm-tag{
  position:absolute;
  left:6px;
  bottom:6px;
  background:rgba(15,23,42,.75);
  color:#f9fafb;
  border-radius:6px;
  padding:2px 6px;
  font-size:.7rem;
}

/* ===== combo espesso (adaptado para tema claro) ===== */
.combo{ position:relative; }
.combo--thick .combo-btn{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  cursor:pointer;
  font-size:.9rem;
  font-weight:600;
}
.combo-btn:focus{
  outline:2px solid rgba(37,99,235,.45);
  outline-offset:2px;
}
.combo-menu{
  position:absolute;
  top:calc(100% + 6px);
  left:0;
  right:0;
  display:none;
  background:#ffffff;
  color:#111322;
  border:1px solid #d0d7e2;
  border-radius:12px;
  padding:8px;
  z-index:50;
  box-shadow:0 12px 25px rgba(15,23,42,.12);
}
.combo[data-open] .combo-menu{ display:block; }
.combo-list{
  max-height:260px;
  overflow:auto;
  display:grid;
  gap:6px;
  padding-right:4px;
}
.combo-opt{
  display:flex;
  align-items:center;
  gap:10px;
  padding:8px 10px;
  border-radius:10px;
  background:#f9fafb;
  border:1px solid #e5e7eb;
  cursor:pointer;
  font-size:.85rem;
}
.combo-opt:hover{
  background:#eef2ff;
}
.combo-opt input{ accent-color:#2563eb; }

/* ===== flash messages ===== */
.flash{
  padding:10px 12px;
  border-radius:10px;
  border:1px solid transparent;
  margin-bottom:8px;
  font-size:.86rem;
}
.flash--ok{
  background:#e6f7ec;
  border-color:#b8ebc6;
  color:#0f7a2f;
}
.flash--warn{
  background:#fff7e6;
  border-color:#ffe1a8;
  color:#8a5a00;
}
.flash--err{
  background:#ffecec;
  border-color:#ffc9c9;
  color:#a10000;
}

/* chip-link (abrir campanha) */
.chip-link{
  display:inline-flex;
  align-items:center;
  padding:4px 10px;
  border-radius:999px;
  border:1px solid #d0d7e2;
  font-size:.78rem;
  text-decoration:none;
  color:#111322;
  background:#ffffff;
}

/* ===== rodapé admin ===== */
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
