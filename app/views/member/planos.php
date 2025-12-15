<?php
// Member • Meu plano — com Termo de Aceite (texto via API) + Assinatura + Checkout/BOLETO
?>
<section class="container member" style="margin-top:18px">
  <main class="member-main">
    <div class="glass-card member-plans">
      <h1 class="sect-title">Meu plano</h1>

      <!-- Status atual -->
      <div class="current-plan" id="cur-wrap" style="display:none">
        <div class="cp-info">
          <div class="cp-title">Plano atual</div>
          <div class="cp-name" id="cur-name">—</div>
          <div class="cp-meta muted" id="cur-meta">—</div>
        </div>
        <div class="cp-right">
          <div class="cp-price"><strong id="cur-amount">—</strong></div>
          <a id="cur-status-pill" class="status-pill" href="/?r=member/faturas" title="Ver faturas" style="display:none">—</a>
        </div>
      </div>

      <!-- Toggle cobrança -->
      <div class="billing-switch mtop" role="group" aria-label="Frequência de cobrança">
        <button class="billing-btn is-active" data-billing="mensal" type="button">Mensal</button>
        <button class="billing-btn" data-billing="anual" type="button">Anual <span class="chip">–15%</span></button>
      </div>

      <!-- Planos -->
      <div id="plans-holder" class="plans-select mtop"></div>

      <div class="form-actions">
        <button id="btn-continue" class="btn" type="button" disabled>Continuar</button>
        <span class="muted">Cobraremos via ASAAS. Você poderá cancelar quando quiser.</span>
      </div>

      <div id="mp-alert" class="alert" style="display:none"></div>
    </div>
  </main>
</section>

<!-- Modal TERMO (antes do pagamento) -->
<div class="modal" id="terms-modal" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title" style="display:none">
  <div class="modal-box glass-card terms-box" role="document">
    <div class="terms-head">
      <div>
        <h3 id="terms-modal-title" style="margin:0 0 4px">Termo de Aceite Digital</h3>
        <p class="muted" style="margin:0">Leia e assine digitalmente para prosseguir.</p>
      </div>
      <button class="icon-x" id="terms-close" type="button" aria-label="Fechar">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 6L6 18"/><path d="M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <div class="terms-body">
      <!-- Termo em texto (API) -->
      <div class="terms-doc" id="terms-doc">
        <div class="terms-docbar">
          <span class="tlabel" style="margin:0">Termo</span>
          <button class="btn btn-sm btn--ghost" id="terms-toggle" type="button" aria-expanded="true">Recolher</button>
        </div>
        <div class="terms-text" id="terms-text" aria-label="Texto do termo"></div>
      </div>

      <div class="terms-form">
        <div class="terms-grid">
          <div class="input-wrap">
            <label class="tlabel" for="ts-name">Nome para assinatura</label>
            <input class="field" id="ts-name" type="text" placeholder="Ex.: João da Silva" autocomplete="name" required>
          </div>

          <div class="input-wrap">
            <label class="tlabel" for="ts-doc">Documento (opcional)</label>
            <input class="field" id="ts-doc" type="text" placeholder="CPF/CNPJ (opcional)">
          </div>
        </div>

        <div class="sig-wrap">
          <div class="sig-head">
            <span class="tlabel">Assinatura (desenhe com o dedo/mouse)</span>
            <button class="btn btn-sm btn--ghost" id="sig-clear" type="button">Limpar</button>
          </div>
          <div class="sig-pad">
            <canvas id="sig-canvas" width="900" height="260" aria-label="Área de assinatura"></canvas>
          </div>
          <p class="muted sig-hint" style="margin:.4rem 0 0">
            Dica: no celular, assine com o dedo. Se ficar pequeno, gire o aparelho.
          </p>
        </div>

        <label class="terms-check">
          <input type="checkbox" id="ts-agree">
          <span>Li e aceito integralmente o Termo e o Regulamento.</span>
        </label>

        <div class="terms-actions">
          <button class="btn btn--ghost" id="terms-cancel" type="button">Cancelar</button>
          <button class="btn" id="terms-accept" type="button" disabled>Li e Aceito</button>
        </div>

        <div id="terms-alert" class="alert" style="display:none"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal confirmação pagamento -->
<div class="modal" id="plan-modal" role="dialog" aria-modal="true" aria-labelledby="plan-modal-title" style="display:none">
  <div class="modal-box glass-card" role="document">
    <h3 id="plan-modal-title" style="margin:0 0 8px">Confirmar assinatura</h3>
    <p id="plan-modal-resumo" class="muted">Resumo…</p>
    <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap">
      <button class="btn btn-sm" id="plan-confirm-boleto" type="button" disabled>Boleto (recorrente)</button>
      <button class="btn btn-sm" id="plan-confirm-card"   type="button" disabled>Cartão (Checkout)</button>
      <button class="btn btn-sm btn--ghost" id="plan-cancel" type="button">Cancelar</button>
    </div>
    <p class="muted" style="margin:.5rem 0 0">Obs.: Assinaturas via API aceitam Boleto/Cartão. PIX não é suportado para recorrência.</p>
  </div>
</div>

<script>
const plansHolder   = document.getElementById('plans-holder');
const btnContinue   = document.getElementById('btn-continue');
const alertBox      = document.getElementById('mp-alert');

const planModal     = document.getElementById('plan-modal');
const planResumo    = document.getElementById('plan-modal-resumo');
const planCancel    = document.getElementById('plan-cancel');
const btnBoleto     = document.getElementById('plan-confirm-boleto');
const btnCard       = document.getElementById('plan-confirm-card');
const statusPill    = document.getElementById('cur-status-pill');

// Termo modal refs
const termsModal     = document.getElementById('terms-modal');
const termsClose     = document.getElementById('terms-close');
const termsCancel    = document.getElementById('terms-cancel');
const termsAcceptBtn = document.getElementById('terms-accept');
const termsAlert     = document.getElementById('terms-alert');
const termsTextEl    = document.getElementById('terms-text');

const tsName         = document.getElementById('ts-name');
const tsDoc          = document.getElementById('ts-doc');
const tsAgree        = document.getElementById('ts-agree');

const sigCanvas      = document.getElementById('sig-canvas');
const sigClear       = document.getElementById('sig-clear');

// Toggle visual do termo (mobile)
const termsDoc       = document.getElementById('terms-doc');
const termsToggleBtn = document.getElementById('terms-toggle');

function setAlert(msg){
  alertBox.style.display='block';
  alertBox.textContent = msg;
  setTimeout(()=>alertBox.style.display='none', 4500);
}
function setTermsAlert(msg){
  termsAlert.style.display='block';
  termsAlert.textContent = msg;
  setTimeout(()=>termsAlert.style.display='none', 6000);
}

function moneyBR(v){ return 'R$ ' + (+v||0).toFixed(2).replace('.',','); }
function escapeHtml(s){ return (s||'').replace(/[&<>]/g, m=> ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m])); }
function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

async function fetchJsonOrText(url, opts){
  const r = await fetch(url, opts);
  const ct = (r.headers.get('content-type') || '').toLowerCase();
  const isJson = ct.includes('application/json');
  if (isJson) {
    const j = await r.json().catch(()=> ({}));
    return { ok: r.ok, status: r.status, json: j, text: null };
  }
  const t = await r.text().catch(()=> '');
  return { ok: r.ok, status: r.status, json: null, text: t };
}

function popupWrite(pw, title, html){
  try {
    pw.document.open();
    pw.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${title||'Aviv+'}</title>
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial;margin:0;padding:18px;background:#f6f7fb;color:#0f172a}
        .card{max-width:820px;margin:20px auto;background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:16px;padding:16px;box-shadow:0 10px 26px rgba(15,23,42,.08)}
        .muted{color:#64748b}
        pre{white-space:pre-wrap;background:#0b1220;color:#e5e7eb;padding:12px;border-radius:12px;overflow:auto}
        button{border:0;border-radius:999px;padding:10px 14px;font-weight:800;background:#2563eb;color:#fff;cursor:pointer}
      </style>
    </head><body><div class="card">${html}</div></body></html>`);
    pw.document.close();
  } catch(e) {}
}

/* ===== Termo: recolher/expandir (somente visual) ===== */
function isMobileTerms(){
  return window.matchMedia && window.matchMedia('(max-width:720px)').matches;
}
function setTermsCollapsed(collapsed){
  if (!termsDoc || !termsToggleBtn) return;

  termsDoc.classList.toggle('is-collapsed', !!collapsed);
  termsDoc.classList.toggle('is-expanded', !collapsed);

  termsToggleBtn.setAttribute('aria-expanded', String(!collapsed));
  termsToggleBtn.textContent = collapsed ? 'Expandir' : 'Recolher';
}
termsToggleBtn?.addEventListener('click', ()=>{
  const collapsed = termsDoc.classList.contains('is-collapsed');
  setTermsCollapsed(!collapsed);
  try { termsToggleBtn.blur(); } catch(e){}
});

/* ===== Estado de UI ===== */
let BILLING = 'mensal';
document.querySelectorAll('.billing-btn').forEach(b=>{
  b.addEventListener('click', ()=>{
    BILLING = b.dataset.billing;
    document.querySelectorAll('.billing-btn').forEach(x=>x.classList.toggle('is-active', x===b));
    document.querySelectorAll('[data-price-mensal]').forEach(el=>{
      el.textContent = BILLING==='anual' ? el.getAttribute('data-price-anual') : el.getAttribute('data-price-mensal');
    });
  });
});

/* ===== Estado atual ===== */
let ACTIVE_PLAN_ID   = null;
let PENDING_PLAN_ID  = null;
let PENDING_WATCHER  = null;

async function refreshOverview(reloadPlans = true){
  const r = await fetch('/?r=api/member/overview', { cache: 'no-store' });
  if (!r.ok) return;
  const j = await r.json();

  const active  = j.activeSubscription || j.subscription || null;
  const pending = (j.pendingHasInvoice ? (j.pendingSubscription || null) : null);

  ACTIVE_PLAN_ID  = active?.plan_id  || null;
  PENDING_PLAN_ID = pending?.plan_id || null;

  if (active) {
    document.getElementById('cur-wrap').style.display='flex';
    document.getElementById('cur-name').textContent = (j.activePlan?.name || active.plan_id || '—');
    const amt = +(active.amount||0);
    document.getElementById('cur-amount').textContent = amt ? ('R$ ' + amt.toFixed(2).replace('.',',')) : '—';
    document.getElementById('cur-meta').textContent = (active.renew_at ? ('Renova em ' + active.renew_at) : 'Sem renovação');

    const st = (active.status || '').toLowerCase();
    statusPill.style.display = 'inline-flex';
    statusPill.className = 'status-pill';
    if (st === 'ativa') {
      statusPill.textContent = 'Pago (ativo)';
      statusPill.classList.add('st-ok');
      statusPill.title = 'Assinatura ativa';
    } else if (st === 'cancelada') {
      statusPill.textContent = 'Cancelado';
      statusPill.classList.add('st-cancel');
      statusPill.title = 'Assinatura cancelada';
    } else {
      statusPill.textContent = 'Aguardando pagamento';
      statusPill.classList.add('st-wait');
      statusPill.title = 'Clique para ver/pagar sua fatura';
    }
  }

  if (reloadPlans) renderPlans();

  if (PENDING_PLAN_ID && !PENDING_WATCHER) {
    PENDING_WATCHER = setInterval(async () => {
      const before = PENDING_PLAN_ID;
      await refreshOverview(true);
      if (!PENDING_PLAN_ID || PENDING_PLAN_ID === ACTIVE_PLAN_ID) {
        clearInterval(PENDING_WATCHER); PENDING_WATCHER = null;
      } else if (before !== PENDING_PLAN_ID) {}
    }, 10000);
  }
}
refreshOverview().catch(()=>{});

/* ===== Carregar e renderizar planos ===== */
let PLANS = [];

function descriptionToHtml(desc){
  const clean = String(desc||'').replace(/<\s*br\s*\/?>/gi, '\n').trim();
  const parts = clean.split(/[\r\n;•]+/u).map(s => s.trim()).filter(Boolean);
  if (parts.length === 0) return '<em class="muted">Sem descrição</em>';
  if (parts.length === 1) return `<p class="desc">${escapeHtml(parts[0])}</p>`;
  return `<ul class="feat">` + parts.map(li=>`<li class="ok">${escapeHtml(li)}</li>`).join('') + `</ul>`;
}

async function loadPlans(){
  const resp = await fetchJsonOrText('/?r=api/plans/list', { cache: 'no-store' });
  if(!resp.ok){
    setAlert((resp.json?.error) || resp.text || 'Falha ao carregar planos');
    return;
  }
  const j = resp.json || {};
  PLANS = (j.plans||[]).filter(p => (p.status||'active')==='active');
}

function pickPrice(p){
  const pm = +((p.price_monthly ?? p.monthly_price ?? p.price ?? 0) || 0);
  const pyRaw = (p.price_yearly ?? p.yearly_price);
  const py = (pyRaw != null) ? +pyRaw : (pm * 12 * 0.85);
  return { pm, py };
}

async function renderPlans(){
  if (!PLANS.length) await loadPlans();

  plansHolder.innerHTML = PLANS.map(p=>{
    const {pm, py} = pickPrice(p);
    const priceMensal = moneyBR(pm) + '/mês';
    const priceAnual  = moneyBR(py) + ' • anual';
    const isCurrent = (ACTIVE_PLAN_ID && String(ACTIVE_PLAN_ID) === String(p.id));
    const isPending = (!isCurrent && PENDING_PLAN_ID && String(PENDING_PLAN_ID) === String(p.id));

    return `
      <label class="plan-option glass-card ${isCurrent ? 'is-current' : ''} ${isPending ? 'is-pending' : ''}">
        ${isPending ? '<span class="pending-pill" title="Aguardando pagamento">Aguardando pagamento</span>' : ''}
        <input type="radio" name="plan" value="${escapeAttr(p.id)}" ${isCurrent?'checked':''}>
        <div class="po-head">
          <h3>${escapeHtml(p.name || p.id)}</h3>
          ${isCurrent ? '<span class="badge badge--hit">Plano atual</span>' : ''}
        </div>
        <div class="po-price" data-price-mensal="${priceMensal}" data-price-anual="${priceAnual}">
          ${BILLING==='anual' ? priceAnual : priceMensal}
        </div>
        ${descriptionToHtml(p.description)}
      </label>
    `;
  }).join('');

  const cards  = plansHolder.querySelectorAll('.plan-option');
  const radios = plansHolder.querySelectorAll('input[name="plan"]');

  function syncSelected(){
    cards.forEach(c => c.classList.toggle('is-selected', c.querySelector('input')?.checked));
    btnContinue.disabled = !plansHolder.querySelector('input[name="plan"]:checked');
  }
  radios.forEach(r => r.addEventListener('change', syncSelected));
  syncSelected();
}

/* ===== Modal helpers ===== */
function openModal(el){
  el.style.display='grid';
  requestAnimationFrame(()=> el.classList.add('is-open'));
}
function closeModal(el){
  el.classList.remove('is-open');
  setTimeout(()=>{ el.style.display='none'; }, 120);
}

/* ===== Assinatura (canvas) ===== */
const ctx = sigCanvas.getContext('2d');
let drawing = false;
let hasInk  = false;

function resizeSigCanvasToCss(){
  const rect = sigCanvas.getBoundingClientRect();
  const dpr  = window.devicePixelRatio || 1;
  const w = Math.max(1, Math.floor(rect.width * dpr));
  const h = Math.max(1, Math.floor(rect.height * dpr));
  const old = ctx.getImageData(0,0,sigCanvas.width,sigCanvas.height);
  sigCanvas.width  = w;
  sigCanvas.height = h;
  ctx.putImageData(old, 0, 0);
  ctx.lineWidth = 2.2 * dpr;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';
  ctx.strokeStyle = '#111827';
}
function getPos(e){
  const rect = sigCanvas.getBoundingClientRect();
  const touch = e.touches?.[0];
  const x = (touch ? touch.clientX : e.clientX) - rect.left;
  const y = (touch ? touch.clientY : e.clientY) - rect.top;
  const dpr = window.devicePixelRatio || 1;
  return { x: x * dpr, y: y * dpr };
}
function startDraw(e){
  drawing = true;
  const p = getPos(e);
  ctx.beginPath();
  ctx.moveTo(p.x, p.y);
  e.preventDefault?.();
}
function moveDraw(e){
  if(!drawing) return;
  const p = getPos(e);
  ctx.lineTo(p.x, p.y);
  ctx.stroke();
  hasInk = true;
  updateTermsBtnState();
  e.preventDefault?.();
}
function endDraw(){ drawing = false; }
function clearSig(){
  ctx.clearRect(0,0,sigCanvas.width,sigCanvas.height);
  hasInk = false;
  updateTermsBtnState();
}

sigCanvas.addEventListener('mousedown', startDraw);
sigCanvas.addEventListener('mousemove', moveDraw);
window.addEventListener('mouseup', endDraw);
sigCanvas.addEventListener('touchstart', startDraw, {passive:false});
sigCanvas.addEventListener('touchmove', moveDraw, {passive:false});
sigCanvas.addEventListener('touchend', endDraw);
sigClear.addEventListener('click', clearSig);
window.addEventListener('resize', ()=> { if (termsModal.style.display !== 'none') resizeSigCanvasToCss(); });

/* ===== Term state ===== */
let PENDING_SELECTION = null; // { planId, planName, cycle, amount }
let TERM_ACCEPTED_KEY = null; // "planId|cycle"
function selectionKey(planId, cycle){ return String(planId) + '|' + String(cycle); }

function updateTermsBtnState(){
  const okName = (tsName.value || '').trim().length >= 3;
  const okAgree = !!tsAgree.checked;
  const okSig = hasInk;
  termsAcceptBtn.disabled = !(okName && okAgree && okSig);
}
tsName.addEventListener('input', updateTermsBtnState);
tsAgree.addEventListener('change', updateTermsBtnState);

/* ===== Carrega termo quando abrir modal ===== */
async function loadTermsText(){
  termsTextEl.textContent = 'Carregando termo...';
  const resp = await fetchJsonOrText('/?r=api/terms/text', { cache:'no-store' });
  if (!resp.ok) {
    termsTextEl.textContent = resp.text || resp.json?.error || 'Não foi possível carregar o termo.';
    return;
  }
  // resp.text vem preenchido quando content-type é text/plain
  termsTextEl.textContent = resp.text || '';
}

/* ===== Abrir Termo antes do pagamento ===== */
btnContinue?.addEventListener('click', async ()=>{
  const val = plansHolder.querySelector('input[name="plan"]:checked')?.value;
  if (!val) return;

  const p = PLANS.find(x=>String(x.id)===String(val));
  if (!p) return;

  const {pm, py} = pickPrice(p);
  const cycle = (BILLING==='anual') ? 'yearly' : 'monthly';
  const amount = (BILLING==='anual') ? py : pm;

  PENDING_SELECTION = { planId: val, planName: (p.name || p.id), cycle, amount };

  // reset do termo
  tsAgree.checked = false;
  tsDoc.value = (tsDoc.value || '').trim();
  clearSig();
  updateTermsBtnState();

  await loadTermsText();

  // no celular: abre recolhido por padrão; no desktop: aberto
  setTermsCollapsed(isMobileTerms());

  openModal(termsModal);

  setTimeout(()=>{
    resizeSigCanvasToCss();
  }, 50);
});

/* Fechar termo */
function closeTerms(){ closeModal(termsModal); }
termsClose.addEventListener('click', closeTerms);
termsCancel.addEventListener('click', closeTerms);
termsModal.addEventListener('click', (e)=>{ if(e.target===termsModal) closeTerms(); });

/* ESC */
document.addEventListener('keydown', (e)=>{
  if(e.key!=='Escape') return;
  if (termsModal.classList.contains('is-open')) closeTerms();
  if (planModal.classList.contains('is-open')) closeModal(planModal);
});

/* ===== Enviar aceite (API) ===== */
async function submitTermsAcceptance(){
  if(!PENDING_SELECTION) return false;

  const sigDataUrl = sigCanvas.toDataURL('image/png');

  const payload = new URLSearchParams({
    plan_id: PENDING_SELECTION.planId,
    cycle: PENDING_SELECTION.cycle,
    signed_name: (tsName.value||'').trim(),
    signed_doc: (tsDoc.value||'').trim(),
    signature_png: sigDataUrl
  });

  const resp = await fetchJsonOrText('/?r=api/terms/accept', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: payload,
    cache: 'no-store'
  });

  if(!resp.ok){
    const msg = resp.json?.error || resp.json?.message || resp.text || 'Não foi possível registrar o aceite.';
    setTermsAlert(msg);
    return false;
  }

  TERM_ACCEPTED_KEY = selectionKey(PENDING_SELECTION.planId, PENDING_SELECTION.cycle);

  // opcional: se quiser ver no console se mail falhou
  if (resp.json?.mail) console.log('TERMS MAIL:', resp.json.mail);

  return true;
}

/* Ao clicar “Li e Aceito”: salva + abre modal de pagamento */
termsAcceptBtn.addEventListener('click', async ()=>{
  if(!PENDING_SELECTION) return;

  termsAcceptBtn.disabled = true;
  const oldTxt = termsAcceptBtn.textContent;
  termsAcceptBtn.textContent = 'Registrando…';

  const ok = await submitTermsAcceptance();

  termsAcceptBtn.textContent = oldTxt;
  updateTermsBtnState();

  if(!ok) return;

  closeTerms();

  planResumo.innerHTML = `
    Você selecionou <strong>${escapeHtml(PENDING_SELECTION.planName)}</strong> —
    cobrança <strong>${PENDING_SELECTION.cycle==='yearly'?'anual':'mensal'}</strong>.<br>
    Valor: <strong>${moneyBR(PENDING_SELECTION.amount)}</strong>.
  `;
  btnBoleto.disabled = false;
  btnCard.disabled   = false;
  openModal(planModal);
  requestAnimationFrame(()=>{ btnCard?.focus(); });
});

planCancel?.addEventListener('click', ()=> closeModal(planModal));
planModal?.addEventListener('click', (e)=>{ if(e.target===planModal) closeModal(planModal); });

function ensureTermAcceptedOrWarn(){
  if(!PENDING_SELECTION) { setAlert('Selecione um plano.'); return false; }
  const needKey = selectionKey(PENDING_SELECTION.planId, PENDING_SELECTION.cycle);
  if(TERM_ACCEPTED_KEY !== needKey){
    setAlert('Antes de prosseguir, aceite e assine o Termo.');
    closeModal(planModal);
    openModal(termsModal);
    setTermsCollapsed(isMobileTerms());
    setTimeout(()=>{ resizeSigCanvasToCss(); }, 50);
    return false;
  }
  return true;
}

/* ===== Confirmar BOLETO ===== */
btnBoleto?.addEventListener('click', async ()=>{
  if(!ensureTermAcceptedOrWarn()) return;

  const boletoWin = window.open('about:blank', '_blank');
  if(!boletoWin){ setAlert('Permita pop-ups.'); return; }

  popupWrite(boletoWin, 'Gerando boleto…', `
    <h2 style="margin:0 0 6px">Gerando boleto…</h2>
    <p class="muted">Aguarde. Se aparecer erro, esta tela vai mostrar o motivo.</p>
  `);

  btnBoleto.disabled = true; btnCard.disabled = true;

  try{
    const resp = await fetchJsonOrText('/?r=api/subscriptions/create', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        plan_id: PENDING_SELECTION.planId,
        cycle: PENDING_SELECTION.cycle,
        billingType: 'BOLETO'
      }),
      cache: 'no-store'
    });

    if(!resp.ok){
      const msg = resp.json?.error || resp.json?.message || resp.text || 'Falha ao criar assinatura (boleto).';
      setAlert(msg);

      popupWrite(boletoWin, 'Erro ao gerar boleto', `
        <h2 style="margin:0 0 6px">Não foi possível gerar o boleto</h2>
        <p class="muted">O servidor retornou erro. Detalhes abaixo:</p>
        <pre>${escapeHtml(String(msg))}</pre>
        <button onclick="window.close()">Fechar</button>
      `);

      btnBoleto.disabled=false; btnCard.disabled=false;
      return;
    }

    const j = resp.json || {};
    const url = j?.payment?.bankSlipUrl || j?.payment?.invoiceUrl || null;

    if (url) {
      boletoWin.location.href = url;
    } else {
      popupWrite(boletoWin, 'Boleto não retornou URL', `
        <h2 style="margin:0 0 6px">Assinatura criada, mas não recebemos a URL do boleto</h2>
        <p class="muted">Abra suas faturas para pagar.</p>
        <p><a href="/?r=member/faturas">Ir para Faturas</a></p>
      `);
    }

    setAlert('Assinatura criada. O plano será ativado após o pagamento.');
    closeModal(planModal);
    await refreshOverview(true);

  }catch(e){
    setAlert('Erro ao criar assinatura.');
    popupWrite(boletoWin, 'Erro inesperado', `
      <h2 style="margin:0 0 6px">Erro inesperado</h2>
      <p class="muted">Ocorreu uma exceção no navegador.</p>
      <pre>${escapeHtml(String(e?.message || e))}</pre>
      <button onclick="window.close()">Fechar</button>
    `);
    btnBoleto.disabled=false; btnCard.disabled=false;
  }
});

/* ===== Confirmar CARTÃO ===== */
btnCard?.addEventListener('click', async ()=>{
  if(!ensureTermAcceptedOrWarn()) return;

  const checkoutWin = window.open('about:blank', '_blank');
  if(!checkoutWin){ setAlert('Permita pop-ups.'); return; }

  popupWrite(checkoutWin, 'Abrindo checkout…', `
    <h2 style="margin:0 0 6px">Abrindo checkout…</h2>
    <p class="muted">Aguarde. Se aparecer erro, esta tela vai mostrar o motivo.</p>
  `);

  btnBoleto.disabled = true; btnCard.disabled = true;

  try{
    const resp = await fetchJsonOrText('/?r=api/asaas/checkout-link', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        plan_id: PENDING_SELECTION.planId,
        cycle: PENDING_SELECTION.cycle,
        billingType: 'CREDIT_CARD'
      }),
      cache: 'no-store'
    });

    if(!resp.ok){
      const msg = resp.json?.error || resp.json?.message || resp.text || 'Falha ao abrir checkout.';
      setAlert(msg);

      popupWrite(checkoutWin, 'Erro ao abrir checkout', `
        <h2 style="margin:0 0 6px">Não foi possível abrir o checkout</h2>
        <pre>${escapeHtml(String(msg))}</pre>
        <button onclick="window.close()">Fechar</button>
      `);

      btnBoleto.disabled=false; btnCard.disabled=false;
      return;
    }

    const j = resp.json || {};
    if (!j.url) {
      const msg = 'Checkout não retornou URL.';
      setAlert(msg);
      popupWrite(checkoutWin, 'Sem URL', `<pre>${escapeHtml(msg)}</pre><button onclick="window.close()">Fechar</button>`);
      btnBoleto.disabled=false; btnCard.disabled=false;
      return;
    }

    checkoutWin.location.href = j.url;
    closeModal(planModal);
    await refreshOverview(true);

  }catch(e){
    setAlert('Erro ao abrir checkout.');
    popupWrite(checkoutWin, 'Erro inesperado', `
      <h2 style="margin:0 0 6px">Erro inesperado</h2>
      <pre>${escapeHtml(String(e?.message || e))}</pre>
      <button onclick="window.close()">Fechar</button>
    `);
    btnBoleto.disabled=false; btnCard.disabled=false;
  }
});

/* ===== Atualiza ao voltar foco ===== */
document.addEventListener('visibilitychange', ()=>{ if (!document.hidden) refreshOverview(true); });
window.addEventListener('focus', ()=> refreshOverview(true));
</script>

<style>
/* ===== Largura igual ao Header (sem sidebar) ===== */
.container.member{
  width: min(92vw, var(--container)) !important;
  margin-inline: auto;
  padding-inline: 0;
}
.member-main{ display:grid; gap:16px; }

/* Card base */
.member-main .glass-card{
  background:#ffffff;
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:16px;
  color:var(--ink);
  box-shadow:0 12px 30px rgba(15,23,42,.06);
}

/* Título */
.member-main .sect-title{
  margin:0 0 8px;
  font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight:800;
  color:var(--ink);
  font-size: clamp(1.3rem, 1rem + 1vw, 1.7rem);
}
.member-main .muted{ opacity:.9; font-size:.88rem; color:#64748b; }

/* Alert */
.member-main .alert{
  margin-top:10px;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid rgba(148,163,184,.7);
  background:#fee2e2;
  color:#7f1d1d;
  font-weight:600;
}

/* Botões */
.member-main .btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  padding:10px 18px;
  border-radius:999px;
  border:0;
  background:var(--blue);
  color:#ffffff;
  font-weight:800;
  letter-spacing:.02em;
  cursor:pointer;
  box-shadow:0 12px 24px rgba(15,23,42,.16);
  transition:transform .06s ease, filter .18s ease, box-shadow .18s ease;
}
.member-main .btn.btn-sm{ padding:8px 14px; font-size:.9rem; }
.member-main .btn--ghost{ background:#e5e7eb; color:#111827; box-shadow:none; }
.member-main .btn:hover{ filter:brightness(1.05); box-shadow:0 16px 30px rgba(15,23,42,.18); }
.member-main .btn:active{ transform:translateY(1px); box-shadow:0 8px 18px rgba(15,23,42,.18); }
.member-main .btn[disabled]{ opacity:.7; cursor:not-allowed; box-shadow:none; }

/* Chip */
.member-main .chip{
  display:inline-block;
  padding:.2em .55em;
  border-radius:999px;
  font-weight:800;
  background:#e0f2fe;
  border:1px solid #93c5fd;
  color:#1d4ed8;
  font-size:.78rem;
}

/* Status atual */
.member-plans .current-plan{
  display:flex;
  align-items:center;
  justify-content:space-between;
  border:1px solid rgba(148,163,184,.5);
  background:#f8fafc;
  padding:12px 14px;
  border-radius:12px;
  gap:12px;
}
.member-plans .cp-title{ font-size:.9rem; color:#64748b; font-weight:600; }
.member-plans .cp-name{ font-weight:800; font-size:1.1rem; color:#0f172a; }
.member-plans .cp-meta{ font-size:.86rem; }
.member-plans .cp-right{ display:flex; align-items:center; gap:10px; }
.member-plans .cp-price strong{ font-size:1.1rem; color:#0f172a; }

/* Pill */
.status-pill{
  display:inline-flex; align-items:center; gap:8px;
  text-decoration:none; font-weight:700; font-size:.84rem;
  padding:6px 10px; border-radius:999px;
  border:1px solid rgba(148,163,184,.7);
  background:#f9fafb; color:#0f172a;
}
.status-pill::before{ content:""; width:10px; height:10px; border-radius:999px; display:inline-block; background:#9ca3af; }
.status-pill.st-ok{ border-color:rgba(34,197,94,.55); background:#dcfce7; color:#166534; }
.status-pill.st-ok::before{ background:#22c55e; }
.status-pill.st-wait{ border-color:rgba(234,179,8,.65); background:#fef9c3; color:#854d0e; }
.status-pill.st-wait::before{ background:#eab308; }
.status-pill.st-cancel{ border-color:rgba(248,113,113,.75); background:#fee2e2; color:#7f1d1d; }
.status-pill.st-cancel::before{ background:#f97373; }

/* Toggle cobrança */
.billing-switch{
  display:inline-flex; gap:8px;
  background:#f3f4ff; padding:6px;
  border-radius:999px; border:1px solid rgba(129,140,248,.4);
}
.billing-btn{
  border:none; background:transparent; color:#4b5563;
  padding:8px 12px; border-radius:999px;
  cursor:pointer; font-size:.9rem; font-weight:600;
}
.billing-btn.is-active{
  background:#ffffff; color:#1d4ed8;
  font-weight:700; box-shadow:0 2px 8px rgba(129,140,248,.35);
}

/* Planos */
.plans-select{
  display:grid; gap:12px;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}
.plan-option{
  position:relative; display:block; border-radius:16px;
  padding:14px; cursor:pointer;
  border:1px solid rgba(148,163,184,.5);
  background:#ffffff;
  transition: box-shadow .18s ease, transform .06s ease, border-color .18s ease, background .18s ease;
  color:#0f172a;
}
.plan-option:hover{ box-shadow:0 8px 24px rgba(15,23,42,.10); transform:translateY(-1px); }
.plan-option input{ position:absolute; opacity:0; pointer-events:none; }
.plan-option .po-head{ display:flex; align-items:center; justify-content:space-between; gap:10px; }
.plan-option .po-head h3{ margin:0; font-size:1rem; font-weight:800; }
.plan-option .badge.badge--hit{
  display:inline-flex; align-items:center;
  padding:4px 10px; border-radius:999px;
  font-size:.78rem; font-weight:800;
  background:#ecfeff; color:#0f766e;
  border:1px solid #5eead4;
}
.plan-option .po-price{ margin:8px 0 6px; font-weight:800; color:#0f172a; }
.plan-option .feat{ margin:6px 0 0; padding-left:0; }
.plan-option .feat li{
  margin:4px 0; list-style:none;
  position:relative; padding-left:18px;
  font-size:.9rem; color:#4b5563;
}
.plan-option .feat li.ok::before{
  content:"✓"; position:absolute; left:0; top:0;
  font-weight:800; font-size:.8rem; color:#16a34a;
}
.plan-option.is-selected{
  border-color:var(--blue);
  box-shadow: 0 0 0 1px rgba(59,130,246,.6), 0 10px 26px rgba(37,99,235,.20);
  background:linear-gradient(180deg,#ffffff,#eff6ff);
}
.plan-option.is-current{ outline:2px dashed rgba(129,140,248,.8); outline-offset:6px; }
.pending-pill{
  position:absolute; top:10px; right:10px;
  font-size:.78rem; font-weight:800; color:#854d0e;
  border-radius:999px; padding:5px 8px;
  border:1px solid rgba(234,179,8,.65);
  background:#fef9c3;
}

/* Ações */
.member-plans .form-actions{
  display:flex; align-items:center; gap:10px;
  flex-wrap:wrap; margin-top:20px;
}
.member-plans .form-actions .muted{ font-size:.82rem; }

/* Modal */
.modal{
  position:fixed; inset:0;
  background:rgba(15,23,42,.65);
  display:none; place-items:center;
  padding:16px;
  z-index:999999;
  transition: opacity .12s ease;
  opacity:0;
}
.modal.is-open{ opacity:1; }

.modal-box{
  width:min(540px, 96vw);
  border-radius:18px;
  transform: scale(.98);
  transition: transform .12s ease, opacity .12s ease;
  opacity:.98;

  max-height: min(700px, calc(100dvh - 32px));
  overflow:hidden;

  display:flex;
  flex-direction:column;
}
.modal.is-open .modal-box{ transform: scale(1); opacity:1; }

/* Modal termo */
.terms-box{ width:min(980px, 96vw); }
.terms-head{
  display:flex; align-items:flex-start;
  justify-content:space-between;
  gap:12px; margin-bottom:10px;
  flex:0 0 auto;
}
.icon-x{
  width:40px;height:38px;
  border-radius:12px;
  border:1px solid rgba(148,163,184,.6);
  background:#ffffff;
  cursor:pointer;
}
.icon-x:hover{ background:#f3f4ff; }

/* IMPORTANT: scroll do conteúdo do modal (evita encavalado) */
.terms-body{
  display:grid;
  grid-template-columns: 1.25fr .9fr;
  gap:12px;
  align-items:start;

  flex:1 1 auto;
  min-height:0;
  overflow:auto;
  padding-right:4px;
}

/* bloco do termo */
.terms-doc{
  border:1px solid rgba(148,163,184,.55);
  border-radius:14px;
  overflow:hidden;
  background:#f8fafc;

  display:flex;
  flex-direction:column;

  min-height:0; /* evita overflow “vazar” no grid */
}
.terms-docbar{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding:10px 12px;
  background:#f8fafc;
  border-bottom:1px solid rgba(148,163,184,.35);
}
.terms-text{
  flex:1 1 auto;      /* garante que ocupa espaço do termo */
  min-height:0;        /* essencial para overflow dentro de flex */
  padding:14px;
  overflow:auto;
  white-space:pre-wrap;
  font: 600 .92rem/1.45 ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
  color:#0f172a;
  background:#ffffff;
  max-height:520px;
}

/* recolhido: remove do fluxo (não encavala) */
.terms-doc.is-collapsed .terms-text{
  display:none;
}

/* form (dá fundo para não “misturar” visualmente com o termo) */
.terms-form{
  background:#ffffff;
  border:1px solid rgba(148,163,184,.35);
  border-radius:14px;
  padding:12px;
  min-height:0;
}
.terms-form .tlabel{
  display:block;
  font-weight:800;
  color:#111827;
  font-size:.86rem;
  margin:0 0 6px;
}
.terms-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:10px;
}
.sig-wrap{ margin-top:10px; }
.sig-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  margin-bottom:6px;
}
.sig-pad{
  border:1px dashed rgba(148,163,184,.8);
  border-radius:14px;
  background:#ffffff;
  overflow:hidden;
}
#sig-canvas{
  width:100%;
  height:180px;
  display:block;
  touch-action:none;
}
.terms-check{
  display:flex;
  gap:10px;
  align-items:flex-start;
  margin-top:10px;
  font-weight:700;
  color:#111827;
  font-size:.92rem;
}
.terms-check input{ margin-top:3px; }
.terms-actions{
  display:flex;
  justify-content:flex-end;
  gap:8px;
  margin-top:12px;
}

/* Fields */
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

/* Mobile */
@media (max-width:720px){
  .member-plans .current-plan{ flex-direction:column; align-items:flex-start; gap:8px; }
  .member-plans .cp-right{ width:100%; display:flex; justify-content:space-between; }

  .terms-body{ grid-template-columns: 1fr; }
  .terms-grid{ grid-template-columns:1fr; }
  #sig-canvas{ height:190px; }

  /* no mobile, quando expandir, termo vira caixa pequena com scroll */
  .terms-doc.is-expanded .terms-text{
    display:block;
    max-height:220px;
  }
}
</style>
