<?php
// Admin • Planos — sem sidebar, largura igual ao Header, tabela (desktop) + cards (mobile)
// Ajustes visuais: legenda + placeholders + espaçamento ENTRE planos com LINHA VERDE separadora (sem mudar funcionalidade)
?>
<section class="container admin planos-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Admin • Planos</h1>
      <p class="muted" style="margin:0">
        Gerencie os planos (id usado nas assinaturas, ex.: <code>start</code>, <code>plus</code>, <code>prime</code>).
        <br>
        <strong>Familiar:</strong> o preço mensal/anual é o valor base para o <strong>mínimo de usuários</strong>. O adicional por usuário soma acima do mínimo.
      </p>

      <div class="legend-box" style="margin-top:12px">
        <div class="legend-title">Legenda (campos do plano familiar)</div>
        <div class="legend-grid" role="list" aria-label="Legenda dos campos familiares">
          <div class="legend-item" role="listitem"><strong>Tipo</strong>: Individual ou Familiar</div>
          <div class="legend-item" role="listitem"><strong>Mín. usuários</strong>: mínimo permitido (recomendado ≥ 2)</div>
          <div class="legend-item" role="listitem"><strong>Máx. usuários</strong>: 0 = sem limite</div>
          <div class="legend-item" role="listitem"><strong>+ por usuário (mensal)</strong>: valor extra por usuário acima do mínimo</div>
          <div class="legend-item" role="listitem"><strong>+ por usuário (anual)</strong>: valor extra por usuário acima do mínimo no ciclo anual</div>
        </div>
      </div>
    </div>

    <!-- Novo plano -->
    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub">Novo plano</h2>

      <form id="plan-new" class="form-grid" action="#" onsubmit="return false;">
        <div class="input-wrap span-2">
          <input class="field" id="pn-id" type="text" placeholder="ID do plano (ex.: start, plus, prime)" required aria-label="ID do plano">
        </div>

        <div class="input-wrap span-3">
          <input class="field" id="pn-name" type="text" placeholder="Nome do plano (ex.: Start)" required aria-label="Nome do plano">
        </div>

        <div class="input-wrap span-2">
          <select class="field" id="pn-type" aria-label="Tipo de plano">
            <option value="0">Individual</option>
            <option value="1">Familiar</option>
          </select>
        </div>

        <div class="input-wrap span-2">
          <input class="field" id="pn-pm" type="number" step="0.01" min="0" placeholder="Preço mensal base (ex.: 49.90)" aria-label="Preço mensal base">
        </div>

        <div class="input-wrap span-2">
          <input class="field" id="pn-py" type="number" step="0.01" min="0" placeholder="Preço anual base (ex.: 598.80)" aria-label="Preço anual base">
        </div>

        <div class="input-wrap span-2">
          <input class="field" id="pn-min" type="number" step="1" min="1" placeholder="Mín. usuários (familiar) (ex.: 2)" value="1" aria-label="Mínimo de usuários">
        </div>

        <div class="input-wrap span-2">
          <input class="field" id="pn-max" type="number" step="1" min="0" placeholder="Máx. usuários (0 = livre)" value="0" aria-label="Máximo de usuários">
        </div>

        <div class="input-wrap span-2">
          <input class="field" id="pn-addm" type="number" step="0.01" min="0" placeholder="+ por usuário (mensal) (ex.: 9.90)" value="0" aria-label="Adicional por usuário mensal">
        </div>

        <div class="input-wrap span-2">
          <input class="field" id="pn-addy" type="number" step="0.01" min="0" placeholder="+ por usuário (anual) (ex.: 99.00)" value="0" aria-label="Adicional por usuário anual">
        </div>

        <div class="input-wrap span-2">
          <select class="field" id="pn-status" aria-label="Status do plano">
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
          </select>
        </div>

        <div class="form-actions span-all">
          <button class="btn btn-sm" id="btn-create">Criar</button>
        </div>
      </form>

      <div class="muted" style="margin-top:10px" id="pn-hint">
        Dica: Se for Individual, deixe “mín/máx/+ por usuário” como padrão (min=1, max=0, adicional=0).
      </div>
    </div>

    <!-- Lista (desktop: tabela) -->
    <div class="glass-card only-desktop" style="margin-top:12px">
      <h2 class="sect-sub">Lista de planos</h2>

      <div class="table-wrap" id="plans-table-wrap" role="region" aria-label="Tabela de planos">
        <table class="tbl-plans">
          <colgroup>
            <col style="width:150px" />  <!-- ID -->
            <col style="width:28%"   />  <!-- Nome -->
            <col style="width:140px" />  <!-- Mensal -->
            <col style="width:140px" />  <!-- Anual -->
            <col style="width:150px" />  <!-- Status -->
            <col style="width:100px" />  <!-- Ordem -->
            <col style="width:190px" />  <!-- Ações -->
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
/* =========================
   FIX: Menu do Header/Admin (abre/fecha)
   - Funciona mesmo se o header estiver fora deste arquivo
   - Fecha fora / ESC / clique em link / resize
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

  function toggleMenu(){
    isOpen() ? close() : open();
  }

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

const alertBox     = document.getElementById('plans-alert');
const alertBoxM    = document.getElementById('plans-alert-m');
const tableWrap    = document.getElementById('plans-table-wrap');
const tbody        = document.getElementById('plans-body');
const cardsBox     = document.getElementById('plans-cards-box');
const cardsEl      = document.getElementById('plans-cards');

const pnType = document.getElementById('pn-type');
const pnMin  = document.getElementById('pn-min');
const pnMax  = document.getElementById('pn-max');
const pnAddM = document.getElementById('pn-addm');
const pnAddY = document.getElementById('pn-addy');

function setAlert(msg, mobile=false){
  const el = mobile ? (alertBoxM||alertBox) : alertBox;
  if(!el) return;
  el.style.display='block';
  el.textContent=msg;
  setTimeout(()=>el.style.display='none',1800);
}
function escHtml(s){ return String(s ?? '').replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m])); }
function escAttr(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

function normalizeInt(v, def=0){
  const n = parseInt(v, 10);
  return isFinite(n) ? n : def;
}
function normalizeNum(v, def=0){
  const n = Number(v);
  return isFinite(n) ? n : def;
}

function syncNewPlanFamilyUI(){
  const isFam = (pnType.value === '1');
  pnMin.disabled = !isFam;
  pnMax.disabled = !isFam;
  pnAddM.disabled= !isFam;
  pnAddY.disabled= !isFam;

  if(!isFam){
    pnMin.value = '1';
    pnMax.value = '0';
    pnAddM.value= '0';
    pnAddY.value= '0';
  }else{
    if (normalizeInt(pnMin.value,1) < 2) pnMin.value = '2';
  }
}
pnType.addEventListener('change', syncNewPlanFamilyUI);
syncNewPlanFamilyUI();

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

      const isFam = String(p.is_family ?? 0) === '1';
      const minU  = escAttr(p.min_users ?? 1);
      const maxU  = escAttr(p.max_users ?? 0);
      const addM  = escAttr(p.add_user_monthly ?? 0);
      const addY  = escAttr(p.add_user_yearly  ?? 0);

      return `
      <tr class="data-row" data-id="${id}">
        <td><code>${escHtml(p.id ?? '')}</code></td>
        <td>
          <input class="cell-field" type="text" value="${name}" data-f="name"
                 placeholder="Nome do plano" aria-label="Nome do plano">
        </td>
        <td>
          <input class="cell-field ta-r" type="number" step="0.01" min="0" value="${pm}" data-f="price_monthly"
                 placeholder="Mensal base" aria-label="Preço mensal base">
        </td>
        <td>
          <input class="cell-field ta-r" type="number" step="0.01" min="0" value="${py}" data-f="price_yearly"
                 placeholder="Anual base" aria-label="Preço anual base">
        </td>
        <td>
          <select class="cell-field" data-f="status" aria-label="Status do plano">
            <option value="active" ${st==='active'?'selected':''}>Ativo</option>
            <option value="inactive" ${st!=='active'?'selected':''}>Inativo</option>
          </select>
        </td>
        <td>
          <input class="cell-field ta-c" type="number" value="${ord}" data-f="sort_order"
                 placeholder="Ordem" aria-label="Ordem de exibição">
        </td>
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
            <div class="fam-header">
              <div class="fam-title">Configuração do plano</div>
              <div class="muted fam-help">Se for Individual, mantenha os campos familiares em 1/0/0.</div>
            </div>

            <div class="grid-fam" data-fam>
              <div class="fi">
                <div class="fi-lbl">Tipo</div>
                <select class="field" data-f="is_family" aria-label="Tipo do plano" title="Tipo do plano">
                  <option value="0" ${!isFam?'selected':''}>Individual</option>
                  <option value="1" ${isFam?'selected':''}>Familiar</option>
                </select>
              </div>

              <div class="fi">
                <div class="fi-lbl">Mín. usuários</div>
                <input class="field" type="number" step="1" min="1" value="${minU}" data-f="min_users"
                       placeholder="Ex.: 2" aria-label="Mínimo de usuários" title="Mínimo de usuários">
              </div>

              <div class="fi">
                <div class="fi-lbl">Máx. usuários</div>
                <input class="field" type="number" step="1" min="0" value="${maxU}" data-f="max_users"
                       placeholder="0 = livre" aria-label="Máximo de usuários" title="Máximo de usuários (0 = livre)">
              </div>

              <div class="fi">
                <div class="fi-lbl">+ por usuário (mensal)</div>
                <input class="field" type="number" step="0.01" min="0" value="${addM}" data-f="add_user_monthly"
                       placeholder="Ex.: 9.90" aria-label="Adicional por usuário mensal" title="Adicional por usuário mensal">
              </div>

              <div class="fi">
                <div class="fi-lbl">+ por usuário (anual)</div>
                <input class="field" type="number" step="0.01" min="0" value="${addY}" data-f="add_user_yearly"
                       placeholder="Ex.: 99.00" aria-label="Adicional por usuário anual" title="Adicional por usuário anual">
              </div>
            </div>

            <div class="fi">
              <div class="fi-lbl">Descrição / benefícios</div>
              <textarea class="field" rows="3" data-f="description"
                        placeholder="Escreva benefícios (um por linha). Ex.:&#10;• Suporte 24/7&#10;• Telemedicina&#10;• Descontos"
                        aria-label="Descrição / benefícios">${desc}</textarea>
            </div>

            <div class="muted fam-footnote">
              Familiar: o preço mensal/anual é base do mínimo. Adicionais somam acima do mínimo.
            </div>
          </div>
        </td>
      </tr>

      <!-- separador visual entre planos (linha verde) -->
      <tr class="plan-gap" aria-hidden="true"><td colspan="7"></td></tr>
      `;
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

      const isFam = String(p.is_family ?? 0) === '1';
      const minU  = escAttr(p.min_users ?? 1);
      const maxU  = escAttr(p.max_users ?? 0);
      const addM  = escAttr(p.add_user_monthly ?? 0);
      const addY  = escAttr(p.add_user_yearly  ?? 0);

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
            <input class="field" type="text" value="${name}" data-f="name" placeholder="Nome do plano" aria-label="Nome do plano">
            <select class="field" data-f="status" aria-label="Status do plano">
              <option value="active" ${st==='active'?'selected':''}>Ativo</option>
              <option value="inactive" ${st!=='active'?'selected':''}>Inativo</option>
            </select>
          </div>

          <div class="grid-2">
            <input class="field" type="number" step="0.01" min="0" value="${pm}" data-f="price_monthly" placeholder="Mensal base" aria-label="Preço mensal base">
            <input class="field" type="number" step="0.01" min="0" value="${py}" data-f="price_yearly"  placeholder="Anual base" aria-label="Preço anual base">
          </div>

          <div class="pc-subtitle">Configuração do plano</div>

          <div class="grid-2">
            <select class="field" data-f="is_family" aria-label="Tipo do plano">
              <option value="0" ${!isFam?'selected':''}>Individual</option>
              <option value="1" ${isFam?'selected':''}>Familiar</option>
            </select>
            <input class="field" type="number" step="1" min="1" value="${minU}" data-f="min_users" placeholder="Mín. usuários (ex.: 2)" aria-label="Mínimo de usuários">
          </div>

          <div class="grid-2">
            <input class="field" type="number" step="1" min="0" value="${maxU}" data-f="max_users" placeholder="Máx. usuários (0 = livre)" aria-label="Máximo de usuários">
            <input class="field" type="number" step="0.01" min="0" value="${addM}" data-f="add_user_monthly" placeholder="+ por usuário (mensal)" aria-label="Adicional por usuário mensal">
          </div>

          <div class="grid-2">
            <input class="field" type="number" step="0.01" min="0" value="${addY}" data-f="add_user_yearly" placeholder="+ por usuário (anual)" aria-label="Adicional por usuário anual">
            <div class="muted tiny-note">Familiar: base do mínimo + adicionais.</div>
          </div>

          <textarea class="field" rows="3" data-f="description" placeholder="Descrição / benefícios (um por linha)" aria-label="Descrição / benefícios">${desc}</textarea>

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

/* ===== Reordenar (desktop) — move par: data-row + desc-row (e mantém gap junto) */
function movePair(tr, dir){
  if(!tr || !tr.classList.contains('data-row')) return;

  const desc = tr.nextElementSibling;
  if(!desc || !desc.classList.contains('desc-row')) return;

  const gap = desc.nextElementSibling;
  const hasGap = gap && gap.classList.contains('plan-gap');

  const insertWithGap = (a, b, beforeNode) => {
    tbody.insertBefore(a, beforeNode);
    tbody.insertBefore(b, beforeNode);
    if(hasGap) tbody.insertBefore(gap, beforeNode);
  };

  if(dir==='up'){
    const prevGapOrDesc = tr.previousElementSibling;
    const prevDesc = prevGapOrDesc && prevGapOrDesc.classList.contains('plan-gap')
      ? prevGapOrDesc.previousElementSibling
      : prevGapOrDesc;
    const prevData = prevDesc ? prevDesc.previousElementSibling : null;

    if(prevData && prevData.classList.contains('data-row')){
      insertWithGap(tr, desc, prevData);
    }
  } else if(dir==='down'){
    const afterCurrent = hasGap ? gap.nextElementSibling : desc.nextElementSibling;
    const nextData = afterCurrent;
    const nextDesc = nextData ? nextData.nextElementSibling : null;
    const nextGap  = nextDesc ? nextDesc.nextElementSibling : null;

    if(nextData && nextDesc && nextData.classList.contains('data-row') && nextDesc.classList.contains('desc-row')){
      tbody.insertBefore(nextData, tr);
      tbody.insertBefore(nextDesc, tr);
      if(nextGap && nextGap.classList.contains('plan-gap')) tbody.insertBefore(nextGap, tr);
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
      descRow.querySelectorAll('[data-f]').forEach(el => data[el.dataset.f] = (el.value||'').trim());
    }
  }

  data.is_family = String(data.is_family ?? '0') === '1' ? '1' : '0';
  data.min_users = String(Math.max(1, normalizeInt(data.min_users, 1)));
  data.max_users = String(Math.max(0, normalizeInt(data.max_users, 0)));
  data.add_user_monthly = String(Math.max(0, normalizeNum(data.add_user_monthly, 0)));
  data.add_user_yearly  = String(Math.max(0, normalizeNum(data.add_user_yearly, 0)));

  if (data.is_family !== '1') {
    data.min_users = '1';
    data.max_users = '0';
    data.add_user_monthly = '0';
    data.add_user_yearly  = '0';
  } else {
    if (normalizeInt(data.min_users, 2) < 2) data.min_users = '2';
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

  const is_family = (document.getElementById('pn-type').value === '1') ? '1' : '0';
  let min_users = document.getElementById('pn-min').value || 1;
  let max_users = document.getElementById('pn-max').value || 0;
  let add_user_monthly = document.getElementById('pn-addm').value || 0;
  let add_user_yearly  = document.getElementById('pn-addy').value || 0;

  if(!id || !name) return setAlert('Preencha ID e Nome');

  min_users = String(Math.max(1, normalizeInt(min_users, 1)));
  max_users = String(Math.max(0, normalizeInt(max_users, 0)));
  add_user_monthly = String(Math.max(0, normalizeNum(add_user_monthly, 0)));
  add_user_yearly  = String(Math.max(0, normalizeNum(add_user_yearly, 0)));

  if (is_family !== '1') {
    min_users = '1'; max_users='0'; add_user_monthly='0'; add_user_yearly='0';
  } else {
    if (normalizeInt(min_users, 2) < 2) min_users = '2';
  }

  const r = await fetch('/?r=api/admin/plans/save', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({
      id, name,
      price_monthly: pm,
      price_yearly: py,
      status,
      is_family,
      min_users,
      max_users,
      add_user_monthly,
      add_user_yearly
    })
  });
  const j = await r.json();
  if(!r.ok){ setAlert(j.error||'Erro ao criar'); return; }
  setAlert('Plano criado');
  document.getElementById('plan-new').reset();
  pnType.value = '0';
  syncNewPlanFamilyUI();
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

/* evita cortar dropdowns/menus do header */
.container.admin,
.container.admin .admin-main{
  overflow:visible;
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
.sect-title{ margin:0 0 8px; font-weight:800; color:var(--text,#111322); }
.sect-sub  { margin:0 0 8px; font-weight:700; color:var(--text,#111322); }
.muted{ color:var(--muted,#6b7280); opacity:1; font-size:.9rem; }

/* ===== legenda ===== */
.legend-box{
  border:1px dashed rgba(15,23,42,.14);
  border-radius:14px;
  padding:12px;
  background:rgba(248,250,252,.8);
}
.legend-title{
  font-weight:700;
  font-size:.92rem;
  color:#111322;
  margin-bottom:8px;
}
.legend-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0,1fr));
  gap:8px 12px;
}
.legend-item{ font-size:.88rem; color:#374151; }
@media (max-width:720px){
  .legend-grid{ grid-template-columns:1fr; }
}

/* ===== form novo plano ===== */
.form-grid{
  display:grid;
  grid-template-columns:repeat(12,minmax(0,1fr));
  gap:10px;
}
.span-2{ grid-column: span 2; }
.span-3{ grid-column: span 3; }
.span-all{ grid-column: 1 / -1; }

@media (max-width:1100px){
  .form-grid{ grid-template-columns:1fr 1fr; }
  .span-2,.span-3{ grid-column: span 1; }
  .span-all{ grid-column: 1 / -1; }
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
.field:disabled{
  opacity:.6;
  background:#f8fafc;
}
.form-actions{
  display:flex;
  align-items:center;
  gap:8px;
  padding-top:2px;
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
.btn--ghost{ background:transparent; border:1px solid #d0d7e2; }
.btn.danger,
.btn.btn--ghost.danger{ border-color:#fecaca; color:#b91c1c; }

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
  display:block; left:0;
  background:linear-gradient(to right, rgba(148,163,184,.5), rgba(148,163,184,0));
}
.table-wrap.shadow-right::after{
  display:block; right:0;
  background:linear-gradient(to left, rgba(148,163,184,.5), rgba(148,163,184,0));
}

.tbl-plans{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  table-layout:fixed;
  min-width:1080px;
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
.tbl-plans td:nth-child(2){ white-space:normal; }

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

/* ===== descrição + config ===== */
.desc-row td{
  white-space:normal;
  padding-top:10px;
  padding-bottom:12px;
  background:#fbfdff;
}
.desc-grid{ display:grid; grid-template-columns:1fr; gap:12px; }

.fam-header{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}
.fam-title{ font-weight:700; color:#111322; }
.fam-help{ font-size:.86rem; }
.fam-footnote{ font-size:.86rem; }

.grid-fam{
  display:grid;
  grid-template-columns: repeat(5, minmax(160px, 1fr));
  gap:12px;
  align-items:end;
}
.fi{ display:flex; flex-direction:column; gap:6px; }
.fi-lbl{
  font-size:.82rem;
  color:#475569;
  font-weight:600;
  padding-left:2px;
}
@media (max-width:1200px){
  .grid-fam{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width:860px){
  .grid-fam{ grid-template-columns: 1fr; }
}

/* ações */
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

/* ===== separador ENTRE planos (linha verde simples) ===== */
.plan-gap td{
  padding:0 !important;
  border:0 !important;
  background:transparent !important;
}
.plan-gap td::before{
  content:"";
  display:block;
  height:18px;                       /* espaço entre planos */
  border-top:4px solid #22c55e;      /* LINHA VERDE */
  border-radius:999px;
  margin:6px 0 0;                    /* afasta da tabela/linha anterior */
}

/* ===== mobile (≤ 720px): cards ===== */
@media (max-width:720px){
  .only-desktop{ display:none; }
  .only-mobile{ display:block !important; }

  .plans-cards{ display:grid; gap:14px; }

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
  .pc-subtitle{
    margin-top:10px;
    font-weight:700;
    color:#111322;
    font-size:.9rem;
  }
  .tiny-note{
    display:flex;
    align-items:center;
    padding:0 4px;
    font-size:.85rem;
  }
  .pc-foot{
    display:flex;
    gap:8px;
    justify-content:flex-end;
    margin-top:10px;
  }
}
</style>
