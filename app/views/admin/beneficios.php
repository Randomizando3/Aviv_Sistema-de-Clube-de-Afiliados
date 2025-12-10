<?php
// Admin • Benefícios — sem sidebar (menu no header)
// Largura idêntica ao Header: container.admin segue width:min(92vw, var(--container)) sem padding lateral
?>
<section class="container admin bens-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Admin • Benefícios</h1>
      <p class="muted">Cadastre cupons/links/serviços, com imagem, especialidade e limitação por plano.</p>
    </div>

    <!-- Novo benefício -->
    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub">Novo benefício</h2>

      <form id="bn-new" class="form-grid" onsubmit="return false;">
        <!-- Linha 1: Título | Especialidade | Parceiro -->
        <div class="input-wrap">
          <input class="field" id="b-title" type="text" placeholder="Título (ex.: 15% OFF na Loja X)" required>
        </div>
        <div class="input-wrap">
          <input class="field" id="b-specialty" type="text" placeholder="Especialidade">
        </div>
        <div class="input-wrap">
          <input class="field" id="b-partner" type="text" placeholder="Parceiro (ex.: Loja X)">
        </div>

        <!-- Linha 2: Tipo | Código | Validade -->
        <div class="input-wrap">
          <div id="b-type-combo" class="combo" data-single>
            <button type="button" class="combo-btn" aria-expanded="false">
              <span class="combo-label">Cupom</span>
              <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="combo-menu">
              <div class="combo-list">
                <label class="combo-opt"><input type="radio" name="btype" value="coupon" checked><span>Cupom</span></label>
                <label class="combo-opt"><input type="radio" name="btype" value="link"><span>Link</span></label>
                <label class="combo-opt"><input type="radio" name="btype" value="service"><span>Serviço</span></label>
              </div>
            </div>
            <input type="hidden" id="b-type" value="coupon">
          </div>
        </div>

        <div class="input-wrap">
          <input class="field" id="b-code" type="text" placeholder="Código do cupom (se aplicável)">
        </div>

        <div class="input-wrap">
          <input class="field" id="b-valid" type="date" placeholder="Validade (opcional)">
        </div>

        <!-- Linha 3: Status | Link (span-2) -->
        <div class="input-wrap">
          <div id="b-active-combo" class="combo" data-single>
            <button type="button" class="combo-btn" aria-expanded="false">
              <span class="combo-label">Ativo</span>
              <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="combo-menu">
              <div class="combo-list">
                <label class="combo-opt"><input type="radio" name="bactive" value="1" checked><span>Ativo</span></label>
                <label class="combo-opt"><input type="radio" name="bactive" value="0"><span>Inativo</span></label>
              </div>
            </div>
            <input type="hidden" id="b-active" value="1">
          </div>
        </div>

        <div class="input-wrap span-2">
          <input class="field" id="b-link" type="url" placeholder="URL do parceiro (se aplicável)">
        </div>

        <!-- Descrição + URL da imagem + Prévia (igual aos cards) -->
        <div class="glass-sub span-3">
          <div class="desc-grid">
            <textarea class="field ta-min--create" id="b-desc" rows="3" placeholder="Descrição (opcional)"></textarea>
            <div class="img-url-edit">
              <input class="field" id="b-image-url" type="url" placeholder="URL da imagem">
              <div class="img-preview">
                <img id="img-thumb" class="img-mini" src="" alt="Prévia" hidden>
                <div id="img-ph" class="img-mini ph">Prévia</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Planos -->
        <div class="glass-sub span-3">
          <strong style="font-size:.98rem">Disponível nos planos:</strong>
          <div id="plans-select" class="plans-chips" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px"></div>
        </div>

        <div class="form-actions span-3">
          <button class="btn btn-sm" id="btn-create">Criar benefício</button>
        </div>
      </form>
      <div id="bn-alert" class="alert" style="display:none"></div>
    </div>

    <!-- Lista — Cards (com paginação) -->
    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub">Lista de benefícios</h2>
      <div id="bens-cards" class="bens-cards" role="list" aria-label="Lista de benefícios"></div>
      <nav id="pager" class="pager" aria-label="Paginação" style="margin-top:10px"></nav>
    </div>
  </section>
</section>

<script>
const alertBox  = document.getElementById('bn-alert');
const cardsEl   = document.getElementById('bens-cards');
const pagerEl   = document.getElementById('pager');
const plansWrap = document.getElementById('plans-select');

function setAlert(msg){ alertBox.style.display='block'; alertBox.textContent=msg; setTimeout(()=>alertBox.style.display='none',2000); }
function escapeHtml(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

/* preview (form) */
function showFormPreview(url){
  const img = document.getElementById('img-thumb');
  const ph  = document.getElementById('img-ph');
  if(!url){ img.hidden = true; img.src=''; ph.style.display='flex'; return; }
  img.src = url; img.hidden = false; ph.style.display='none';
}
document.getElementById('b-image-url').addEventListener('input', (e)=> showFormPreview(e.target.value.trim()));

/* preview (lista) */
function showRowPreview(input){
  const wrap = input.closest('.img-url-edit');
  const img = wrap.querySelector('.img-mini:not(.ph)');
  const ph  = wrap.querySelector('.img-mini.ph');
  const url = input.value.trim();
  if (url){ img.src = url; img.style.display='block'; if (ph) ph.style.display='none'; }
  else { img.src=''; img.style.display='none'; if (ph) ph.style.display='flex'; }
}

let PLAN_OPTIONS = [];
let ALL = [];
let PAGE = 1;
const PER_PAGE = 10;

/* planos (chips) */
async function loadPlansOptions(){
  const r = await fetch('/?r=api/admin/plans/list');
  let j; try { j = await r.json(); } catch(e){ return setAlert('Erro de resposta (plans/list)'); }
  if(!r.ok){ setAlert(j.error||'Falha ao carregar planos'); return; }
  PLAN_OPTIONS = (j.plans||[]).map(p => ({id:p.id, name:p.name}));
  plansWrap.innerHTML = PLAN_OPTIONS.map(p => `
    <label class="chip"><input type="checkbox" value="${p.id}"><span>${escapeHtml(p.name)}</span></label>
  `).join('');
}

/* combos */
function renderSingleComboHTML(field, options, current){
  const group = `${field}-${Math.random().toString(36).slice(2)}`;
  const currentLabel = (options.find(o=>o.v===current)?.t) || (options[0]?.t||'—');
  const radios = options.map(o => `
    <label class="combo-opt">
      <input type="radio" name="${group}" value="${o.v}" ${o.v===current?'checked':''}>
      <span>${escapeHtml(o.t)}</span>
    </label>
  `).join('');
  return `
    <div class="combo" data-single>
      <button type="button" class="combo-btn" aria-expanded="false">
        <span class="combo-label">${escapeHtml(currentLabel)}</span>
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
      </button>
      <div class="combo-menu"><div class="combo-list">${radios}</div></div>
      <input type="hidden" data-f="${field}" value="${escapeAttr(current)}">
    </div>
  `;
}
function renderPlansComboHTML(b){
  const selected = Array.isArray(b.plans) ? b.plans : [];
  const summary = selected.length
    ? selected.map(pid => (PLAN_OPTIONS.find(pp=>pp.id===pid)?.name || pid)).join(', ')
    : 'Sem planos';
  const opts = PLAN_OPTIONS.map(p=>`
    <label class="combo-opt">
      <input type="checkbox" value="${p.id}" ${selected.includes(p.id)?'checked':''}>
      <span>${escapeHtml(p.name)}</span>
    </label>
  `).join('');
  return `
    <div class="combo">
      <button type="button" class="combo-btn" aria-expanded="false">
        <span class="combo-label">${escapeHtml(summary)}</span>
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
      </button>
      <div class="combo-menu">
        <div class="combo-list">${opts}</div>
        <div class="combo-actions">
          <button type="button" class="icon-mini" title="Aplicar" data-act="combo-apply">
            <svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M9 16.2l-3.5-3.5-1.4 1.4L9 19 20.3 7.7l-1.4-1.4z"/></svg>
          </button>
          <button type="button" class="icon-mini ghost" title="Limpar" data-act="combo-clear">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6L18 18M6 18L18 6"/></svg>
          </button>
        </div>
      </div>
      <input type="hidden" data-f="plans" value="${selected.join(',')}">
    </div>
  `;
}
function updateComboSummary(combo){
  const isSingle = combo.hasAttribute('data-single');
  const list = combo.querySelector('.combo-list');
  if (isSingle){
    const sel = list.querySelector('input[type="radio"]');
    if (!sel) return;
    const label = sel.closest('label').querySelector('span').textContent;
    const val   = sel.value;
    combo.querySelector('.combo-label').textContent = label;
    const hiddenDF  = combo.querySelector('input[data-f]');
    const hiddenAny = combo.querySelector('input[type="hidden"]');
    if (hiddenDF)  hiddenDF.value  = val;
    if (hiddenAny) hiddenAny.value = val;
  } else {
    const checks = list.querySelectorAll('input[type="checkbox"]');
    const selected = Array.from(checks).filter(c=>c.checked).map(c=>c.value);
    const names = selected.map(pid => (PLAN_OPTIONS.find(pp=>pp.id===pid)?.name || pid));
    combo.querySelector('.combo-label').textContent = names.length ? names.join(', ') : 'Sem planos';
    combo.querySelector('input[data-f="plans"]').value = selected.join(',');
  }
}

/* criar */
document.getElementById('btn-create').addEventListener('click', async ()=>{
  const title = document.getElementById('b-title').value.trim();
  if(!title) return setAlert('Informe o título');

  const partner   = document.getElementById('b-partner').value.trim();
  const type      = document.getElementById('b-type').value;
  const specialty = document.getElementById('b-specialty').value.trim();
  const code      = document.getElementById('b-code').value.trim();
  const link      = document.getElementById('b-link').value.trim();
  const valid     = document.getElementById('b-valid').value;
  const active    = document.getElementById('b-active').value;
  const desc      = document.getElementById('b-desc').value.trim();
  const imgUrl    = document.getElementById('b-image-url').value.trim();
  const plans     = Array.from(plansWrap.querySelectorAll('input[type="checkbox"]:checked')).map(i=>i.value);

  const r = await fetch('/?r=api/admin/benefits/save', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({
      title, partner, type, specialty, code, link,
      valid_until: valid, active, description: desc, image_url: imgUrl,
      plans: plans.join(',')
    })
  });
  let j; try { j = await r.json(); } catch(e){ return setAlert('Erro de resposta ao salvar'); }
  if(!r.ok){ setAlert(j.error||'Erro ao criar'); return; }
  setAlert('Benefício criado');
  document.getElementById('bn-new').reset();
  showFormPreview('');
  document.querySelector('#b-type-combo .combo-label').textContent='Cupom';
  document.getElementById('b-type').value='coupon';
  document.querySelector('#b-active-combo .combo-label').textContent='Ativo';
  document.getElementById('b-active').value='1';
  PAGE = 1;
  await loadBenefits();
});

/* ========= LISTA (cards + paginação) ========= */

function renderOneCard(b){
  const TYPE_OPTS = [{v:'coupon',t:'Cupom'},{v:'link',t:'Link'},{v:'service',t:'Serviço'}];
  const activeVal = (+b.active)===1 ? '1' : '0';
  const statusCombo = renderSingleComboHTML('active', [{v:'1',t:'Ativo'},{v:'0',t:'Inativo'}], activeVal);
  const typeCombo   = renderSingleComboHTML('type', TYPE_OPTS, (b.type||'coupon'));

  return `
    <article class="benef-card" data-id="${b.id}" role="listitem" aria-label="Benefício #${b.id}">
      <header class="benef-head">
        <label class="cell-label">Título</label>
        <input class="field field--title" type="text" value="${escapeAttr(b.title||'')}" data-f="title" placeholder="Título">
      </header>

      <section class="grid-2">
        <div>
          <label class="cell-label">Tipo</label>
          ${typeCombo}
        </div>
        <div>
          <label class="cell-label">Especialidade</label>
          <input class="field" type="text" value="${escapeAttr(b.specialty||'')}" data-f="specialty" placeholder="Especialidade">
        </div>
      </section>

      <section class="grid-3">
        <div>
          <label class="cell-label">Código</label>
          <input class="field" type="text" value="${escapeAttr(b.code||'')}" data-f="code" placeholder="Código">
        </div>
        <div>
          <label class="cell-label">Status</label>
          ${statusCombo}
        </div>
        <div>
          <label class="cell-label">Validade</label>
          <input class="field" type="date" value="${b.valid_until||''}" data-f="valid_until">
        </div>
      </section>

      <section class="grid-2">
        <div>
          <label class="cell-label">Parceiro</label>
          <input class="field" type="text" value="${escapeAttr(b.partner||'')}" data-f="partner" placeholder="Parceiro">
        </div>
        <div>
          <label class="cell-label">Link</label>
          <input class="field" type="url" value="${escapeAttr(b.link||'')}" data-f="link" placeholder="https://...">
        </div>
      </section>

      <section>
        <label class="cell-label">Planos</label>
        ${renderPlansComboHTML(b)}
      </section>

      <section class="grid-2 desc-grid">
        <div>
          <label class="cell-label">Descrição</label>
          <textarea class="field ta-min--list" data-f="description" rows="4" placeholder="Descrição (opcional)">${escapeHtml(b.description||'')}</textarea>
        </div>
        <div class="img-url-edit">
          <label class="cell-label">URL da imagem</label>
          <input class="field" type="url" data-f="image_url" value="${escapeAttr(b.image_url||'')}" placeholder="URL da imagem" oninput="showRowPreview(this)">
          <div class="img-preview">
            <img class="img-mini" src="${escapeAttr(b.image_url||'')}" alt="Preview" style="${b.image_url?'display:block':'display:none'}">
            <div class="img-mini ph" style="${b.image_url?'display:none':'display:flex'}">Prévia</div>
          </div>
        </div>
      </section>

      <footer class="actions">
        <button class="icon-btn" data-act="save" title="Salvar" aria-label="Salvar">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21V8H7v13"/><path d="M7 3v5h8"/></svg>
        </button>
        <button class="icon-btn danger" data-act="del" title="Excluir" aria-label="Excluir">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
        </button>
      </footer>
    </article>
  `;
}

/* paginação/lista */
function renderPage(){
  const total = ALL.length;
  const pages = Math.max(1, Math.ceil(total / PER_PAGE));
  if (PAGE > pages) PAGE = pages;

  const start = (PAGE - 1) * PER_PAGE;
  const slice = ALL.slice(start, start + PER_PAGE);

  if (!slice.length){
    cardsEl.innerHTML = `<p class="muted">Nenhum benefício cadastrado.</p>`;
    pagerEl.innerHTML = '';
    return;
  }

  cardsEl.innerHTML = slice.map(renderOneCard).join('');
  renderPager(pages);
}
function renderPager(pages){
  const btn = (p, txt, disabled=false, current=false) => `<button class="pg-btn ${current?'current':''}" data-page="${p}" ${disabled?'disabled':''}>${txt}</button>`;
  const parts = [];
  parts.push(btn(Math.max(1, PAGE-1), '‹ Anterior', PAGE===1));
  for (let p=1; p<=pages; p++){ parts.push(btn(p, p, false, p===PAGE)); }
  parts.push(btn(Math.min(pages, PAGE+1), 'Próxima ›', PAGE===pages));
  pagerEl.innerHTML = parts.join('');
}
pagerEl.addEventListener('click', (e)=>{
  const b = e.target.closest('.pg-btn'); if(!b) return;
  const p = parseInt(b.dataset.page,10);
  if (!isNaN(p) && p !== PAGE){ PAGE = p; renderPage(); window.scrollTo({top:0, behavior:'smooth'}); }
});

async function loadBenefits(){
  const r = await fetch('/?r=api/admin/benefits/list');
  let j; try { j = await r.json(); } catch(e){ cardsEl.innerHTML = `<p class="muted">Erro de resposta (benefits/list)</p>`; return; }
  if(!r.ok){ cardsEl.innerHTML = `<p class="muted">${escapeHtml(j.error||'Falha ao carregar')}</p>`; return; }
  ALL = j.benefits || [];
  renderPage();
}

/* combos + ações compartilhadas */
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
    return;
  }

  const apply = e.target.closest('[data-act="combo-apply"]');
  const clear = e.target.closest('[data-act="combo-clear"]');
  if (apply || clear){
    const c = e.target.closest('.combo'); if (!c) return;
    if (clear){ c.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false); }
    updateComboSummary(c);
    c.removeAttribute('data-open');
    c.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');
  }

  const actionBtn = e.target.closest('button[data-act]');
  if(actionBtn){
    const act = actionBtn.dataset.act;
    let scope = actionBtn.closest('article.benef-card');
    const id = scope?.dataset.id;
    if(!id) return;
    if (act==='save'){ saveBenefit(scope, id); }
    if (act==='del'){ delBenefit(id); }
  }
});
document.addEventListener('change', (e)=>{
  if (!e.target.matches('.combo[data-single] input[type="radio"]')) return;
  const combo = e.target.closest('.combo[data-single]');
  updateComboSummary(combo);
  combo.removeAttribute('data-open');
  combo.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');
});

function collectData(scope){
  const obj = {};
  scope.querySelectorAll('[data-f]').forEach(el => { obj[el.dataset.f] = (el.value||'').trim(); });
  return obj;
}
async function saveBenefit(scope, id){
  const data = collectData(scope); data.id = id;
  const r = await fetch('/?r=api/admin/benefits/save', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams(data)
  });
  let j; try { j = await r.json(); } catch(e){ return setAlert('Erro de resposta ao salvar'); }
  if(!r.ok){ setAlert(j.error||'Erro ao salvar'); return; }
  setAlert('Benefício salvo');
  await loadBenefits();
}
async function delBenefit(id){
  if(!confirm('Excluir este benefício?')) return;
  const r = await fetch('/?r=api/admin/benefits/delete', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({id})
  });
  let j; try { j = await r.json(); } catch(e){ return setAlert('Erro de resposta ao excluir'); }
  if(!r.ok){ setAlert(j.error||'Erro ao excluir'); return; }
  setAlert('Benefício excluído');
  const maxPage = Math.max(1, Math.ceil((ALL.length-1) / PER_PAGE));
  if (PAGE > maxPage) PAGE = maxPage;
  await loadBenefits();
}

/* init */
(async function(){
  await loadPlansOptions();
  await loadBenefits();
})();
</script>

<style>
:root{
  --combo-bg: #281B3E;
  --combo-bg-2: #201431;
  --combo-bd: rgba(186,126,255,.35);
  --txt: #eaf3ff;
}

.container.admin{
  width: min(92vw, var(--container)) !important;
  margin-inline: auto;
  padding-inline: 0;
}

.glass-card{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); padding:14px; border-radius:14px; color:#fff; }
.muted{ color:#cfe1ff; opacity:.88; }
.sect-title{ margin:0 0 10px; font-weight:800; }
.sect-sub{ margin:0 0 8px; font-weight:800; color:#fff; }

/* Form novo */
.form-grid{ display:grid; gap:12px; grid-template-columns: repeat(3, minmax(0,1fr)); }
.input-wrap{ display:block; }
.span-2{ grid-column: span 2; }
.span-3{ grid-column: 1 / -1; }
.form-actions{ display:flex; justify-content:flex-end; }
.field{ width:100%; box-sizing:border-box; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.20); background:rgba(255,255,255,.08); color:var(--txt); outline:none; }
textarea.field{ resize:vertical; }

/* Classes pedidas para tamanho mínimo dos textareas */
.ta-min--create{ min-height: 140px; }  /* formulário de criação */
.ta-min--list{   min-height: 170px; }  /* cards da lista */

/* Cards (lista) */
.bens-cards{ display:grid; gap:12px; grid-template-columns: 1fr; }
@media (min-width: 1100px){ .bens-cards{ grid-template-columns: 1fr 1fr; } }

.benef-card{
  border:1px solid rgba(255,255,255,.12);
  border-radius:12px;
  background:rgba(255,255,255,.06);
  padding:12px;
  display:flex; flex-direction:column; gap:10px;
}

/* Título 100% nos cards */
.benef-head{ display:block; }
.bens-page .benef-card .benef-head .field--title{
  display:block;
  width:100% !important;
  max-width:none !important;
  min-width:0 !important;
}

/* Grids internos (cards) */
.grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.grid-3{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; }
@media (max-width: 900px){ .grid-3{ grid-template-columns:1fr 1fr; } }
@media (max-width: 540px){ .grid-2,.grid-3{ grid-template-columns:1fr; } }

/* Labels dos cards */
.cell-label{ display:block; font-size:.78rem; line-height:1.1; color:#cfe1ff; opacity:.9; margin:0 0 4px 2px; }

/* Descrição/preview (form e cards) */
.desc-grid{ display:grid; grid-template-columns:1fr 320px; gap:12px; align-items:start }
@media (max-width:900px){ .desc-grid{ grid-template-columns:1fr } }
.img-url-edit{ display:grid; gap:6px; align-items:start }
.img-preview{ position:relative }
.img-mini{ width:100%; height:120px; object-fit:cover; border-radius:10px; border:1px solid rgba(255,255,255,.15) }
.img-mini.ph{ display:flex; align-items:center; justify-content:center; font-size:.9rem; color:#c9b5ff; background:rgba(255,255,255,.06) }

/* Combobox */
.combo{ position:relative }
.combo-btn{ width:100%; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:10px 12px; border-radius:10px; border:1px solid var(--combo-bd); background:var(--combo-bg); color:#f1e9ff; cursor:pointer }
.combo-btn:focus{ outline:2px solid rgba(186,126,255,.55); outline-offset:2px }
.combo-menu{ position:absolute; top:calc(100% + 6px); left:0; right:0; background:var(--combo-bg-2); color:#f1e9ff; border:1px solid var(--combo-bd); border-radius:12px; padding:8px; z-index:50; box-shadow:0 8px 24px rgba(0,0,0,.35); display:none }
.combo[data-open] .combo-menu{ display:block }
.combo-list{ max-height:210px; overflow:auto; display:grid; gap:6px; padding-right:4px }
.combo-opt{ display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:8px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); cursor:pointer }
.combo-opt input{ accent-color:#b57bff }
.combo-actions{ display:flex; justify-content:space-between; gap:8px; margin-top:10px }
.combo-label{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; text-align:left }

/* Chips (form) */
.chip{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15) }
.chip input{ accent-color:#fff }

/* Ações */
.actions{ display:flex; align-items:center; gap:8px; justify-content:flex-end; }
.icon-btn{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:32px; border-radius:10px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.08); color:#fff; cursor:pointer; transition:filter .15s ease, transform .06s ease, background .2s ease }
.icon-btn:hover{ filter:brightness(1.05) }
.icon-btn:active{ transform:translateY(1px) }
.icon-btn.danger{ background:rgba(255,77,79,.12); border-color:rgba(255,77,79,.35); color:#fff }
.btn.btn-sm{ padding:8px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.10); color:#fff; cursor:pointer }
.btn--ghost{ background:transparent; }
.alert{ margin-top:8px; padding:8px 10px; border-radius:10px; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); color:#fff }

/* Paginação */
.pager{ display:flex; gap:6px; align-items:center; justify-content:flex-end; flex-wrap:wrap }
.pg-btn{ padding:6px 10px; border-radius:10px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.08); color:#fff; cursor:pointer }
.pg-btn.current{ font-weight:800; outline:2px solid rgba(186,126,255,.45); outline-offset:2px }
.pg-btn:disabled{ opacity:.55; cursor:not-allowed }
</style>
