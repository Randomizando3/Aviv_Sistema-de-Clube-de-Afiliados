<?php
// Member • Meu plano — sem sidebar, largura igual ao Header, responsivo e com criação de assinatura (API + Checkout)
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

<!-- Modal confirmação -->
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

function setAlert(msg){
  alertBox.style.display='block';
  alertBox.textContent = msg;
  setTimeout(()=>alertBox.style.display='none', 3000);
}

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
let ACTIVE_PLAN_ID   = null;   // plano efetivo (status=ativa)
let PENDING_PLAN_ID  = null;   // plano com fatura pendente (status=suspensa)
let PENDING_WATCHER  = null;   // interval id

async function refreshOverview(reloadPlans = true){
  const r = await fetch('/?r=api/member/overview', { cache: 'no-store' });
  if (!r.ok) return;
  const j = await r.json();

  const active  = j.activeSubscription || j.subscription || null;
  const pending = (j.pendingHasInvoice ? (j.pendingSubscription || null) : null);

  ACTIVE_PLAN_ID  = active?.plan_id  || null;
  PENDING_PLAN_ID = pending?.plan_id || null;

  // bloco "plano atual"
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

  // controla o poll de pendência (para sumir o semáforo assim que pagar)
  if (PENDING_PLAN_ID && !PENDING_WATCHER) {
    PENDING_WATCHER = setInterval(async () => {
      const before = PENDING_PLAN_ID;
      await refreshOverview(true);
      if (!PENDING_PLAN_ID || PENDING_PLAN_ID === ACTIVE_PLAN_ID) {
        clearInterval(PENDING_WATCHER); PENDING_WATCHER = null;
      } else if (before !== PENDING_PLAN_ID) {
        // mudou de pendência -> re-render já foi chamado
      }
    }, 10000);
  }
}

// carrega estado ao abrir
refreshOverview().catch(()=>{});

/* ===== Carregar e renderizar planos ===== */
let PLANS = [];
function moneyBR(v){ return 'R$ ' + (+v||0).toFixed(2).replace('.',','); }
function escapeHtml(s){ return (s||'').replace(/[&<>]/g, m=> ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m])); }
function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }

function descriptionToHtml(desc){
  const clean = String(desc||'').replace(/<\s*br\s*\/?>/gi, '\n').trim();
  const parts = clean.split(/[\r\n;•]+/u).map(s => s.trim()).filter(Boolean);
  if (parts.length === 0) return '<em class="muted">Sem descrição</em>';
  if (parts.length === 1) return `<p class="desc">${escapeHtml(parts[0])}</p>`;
  return `<ul class="feat">` + parts.map(li=>`<li class="ok">${escapeHtml(li)}</li>`).join('') + `</ul>`;
}

async function loadPlans(){
  const r = await fetch('/?r=api/plans/list', { cache: 'no-store' });
  let j; try{ j = await r.json(); }catch(e){ setAlert('Erro ao carregar planos'); return; }
  if(!r.ok){ setAlert(j.error||'Falha ao carregar'); return; }
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

/* ===== Abrir/fechar modal ===== */
function openModal(){
  planModal.style.display='grid';
  requestAnimationFrame(()=>{ planModal.classList.add('is-open'); btnCard?.focus(); });
}
function closeModal(){
  planModal.classList.remove('is-open');
  setTimeout(()=>{ planModal.style.display='none'; }, 120);
}

/* ===== Fluxo de confirmação / criação ===== */
btnContinue?.addEventListener('click', async ()=>{
  const val = plansHolder.querySelector('input[name="plan"]:checked')?.value;
  if (!val) return;
  const p = PLANS.find(x=>String(x.id)===String(val));
  if (!p) return;

  const {pm, py} = pickPrice(p);
  const amount = (BILLING==='anual') ? py : pm;

  planResumo.innerHTML = `
    Você selecionou <strong>${escapeHtml(p.name || p.id)}</strong> —
    cobrança <strong>${BILLING==='anual'?'anual':'mensal'}</strong>.<br>
    Valor: <strong>${moneyBR(amount)}</strong>.
  `;
  btnBoleto.disabled = false;
  btnCard.disabled   = false;
  openModal();
});

planCancel?.addEventListener('click', closeModal);
planModal?.addEventListener('click', (e)=>{ if(e.target===planModal) closeModal(); });
document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });

/* ===== Confirmar BOLETO ===== */
btnBoleto?.addEventListener('click', async ()=>{
  const planId = plansHolder.querySelector('input[name="plan"]:checked')?.value;
  if (!planId) return;
  const boletoWin = window.open('about:blank', '_blank'); if(!boletoWin){ setAlert('Permita pop-ups.'); return; }
  btnBoleto.disabled = true; btnCard.disabled = true;

  const cycle = (BILLING==='anual') ? 'yearly' : 'monthly';
  try{
    const r = await fetch('/?r=api/subscriptions/create', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ plan_id: planId, cycle, billingType: 'BOLETO' }),
      cache: 'no-store'
    });
    const j = await r.json().catch(()=> ({}));
    if(!r.ok){
      setAlert(j.error || 'Falha ao criar assinatura (boleto).');
      btnBoleto.disabled=false; btnCard.disabled=false;
      boletoWin.close(); return;
    }
    const url = j?.payment?.bankSlipUrl || j?.payment?.invoiceUrl || null;
    if (url) boletoWin.location.href = url; else boletoWin.document.write('<p>Abra suas faturas.</p>');
    setAlert('Assinatura criada. O plano será ativado após o pagamento.');
    closeModal();

    // atualiza estado (mostra semáforo no plano escolhido)
    await refreshOverview(true);
  }catch(e){
    setAlert('Erro ao criar assinatura.');
    btnBoleto.disabled=false; btnCard.disabled=false;
    boletoWin.close();
  }
});

/* ===== Confirmar CARTÃO ===== */
btnCard?.addEventListener('click', async ()=>{
  const planId = plansHolder.querySelector('input[name="plan"]:checked')?.value;
  if (!planId) return;
  const checkoutWin = window.open('about:blank', '_blank'); if(!checkoutWin){ setAlert('Permita pop-ups.'); return; }
  btnBoleto.disabled = true; btnCard.disabled = true;
  planResumo.innerHTML += '<br><em class="muted">Abrindo checkout…</em>';

  const cycle = (BILLING==='anual') ? 'yearly' : 'monthly';
  try{
    const r = await fetch('/?r=api/asaas/checkout-link', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ plan_id: planId, cycle, billingType: 'CREDIT_CARD' }),
      cache: 'no-store'
    });
    const j = await r.json().catch(()=> ({}));
    if(!r.ok || !j.url){
      setAlert(j.error || 'Falha ao abrir checkout.');
      btnBoleto.disabled=false; btnCard.disabled=false;
      checkoutWin.close(); return;
    }
    checkoutWin.location.href = j.url;
    closeModal();

    // atualiza estado (habilita semáforo se gerar fatura pendente antes do pagamento)
    await refreshOverview(true);
  }catch(e){
    setAlert('Erro ao abrir checkout.');
    btnBoleto.disabled=false; btnCard.disabled=false;
    checkoutWin.close();
  }
});

/* ===== Atualiza ao voltar o foco para a aba (user retorna do Asaas) ===== */
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

.member-main{
  display:grid;
  gap:16px;
}

/* Card base deste módulo (seguindo tema claro) */
.member-main .glass-card{
  background:#ffffff;
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:16px;
  color:var(--ink);
  box-shadow:0 12px 30px rgba(15,23,42,.06);
}

/* Título da página */
.member-main .sect-title{
  margin:0 0 8px;
  font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight:800;
  color:var(--ink);
  font-size: clamp(1.3rem, 1rem + 1vw, 1.7rem);
}

/* Texto auxiliar */
.member-main .muted{
  opacity:.9;
  font-size:.88rem;
  color:#64748b;
}

/* Alert de feedback */
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
.member-main .btn.btn-sm{
  padding:8px 14px;
  font-size:.9rem;
}
.member-main .btn--ghost{
  background:#e5e7eb;
  color:#111827;
  box-shadow:none;
}
.member-main .btn:hover{
  filter:brightness(1.05);
  box-shadow:0 16px 30px rgba(15,23,42,.18);
}
.member-main .btn:active{
  transform:translateY(1px);
  box-shadow:0 8px 18px rgba(15,23,42,.18);
}
.member-main .btn[disabled]{
  opacity:.7;
  cursor:not-allowed;
  box-shadow:none;
}

/* Chip do “–15%” */
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

/* ===== Bloco status do plano ===== */
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
.member-plans .cp-title{
  font-size:.9rem;
  color:#64748b;
  font-weight:600;
}
.member-plans .cp-name{
  font-weight:800;
  font-size:1.1rem;
  color:#0f172a;
}
.member-plans .cp-meta{
  font-size:.86rem;
}
.member-plans .cp-right{
  display:flex;
  align-items:center;
  gap:10px;
}
.member-plans .cp-price strong{
  font-size:1.1rem;
  color:#0f172a;
}

/* Pill de status do plano atual */
.status-pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  text-decoration:none;
  font-weight:700;
  font-size:.84rem;
  padding:6px 10px;
  border-radius:999px;
  border:1px solid rgba(148,163,184,.7);
  background:#f9fafb;
  color:#0f172a;
}
.status-pill::before{
  content:"";
  width:10px;
  height:10px;
  border-radius:999px;
  display:inline-block;
  background:#9ca3af;
}
.status-pill.st-ok{
  border-color:rgba(34,197,94,.55);
  background:#dcfce7;
  color:#166534;
}
.status-pill.st-ok::before{ background:#22c55e; }
.status-pill.st-wait{
  border-color:rgba(234,179,8,.65);
  background:#fef9c3;
  color:#854d0e;
}
.status-pill.st-wait::before{ background:#eab308; }
.status-pill.st-cancel{
  border-color:rgba(248,113,113,.75);
  background:#fee2e2;
  color:#7f1d1d;
}
.status-pill.st-cancel::before{ background:#f97373; }

/* ===== Toggle de cobrança ===== */
.billing-switch{
  display:inline-flex;
  gap:8px;
  background:#f3f4ff;
  padding:6px;
  border-radius:999px;
  border:1px solid rgba(129,140,248,.4);
}
.billing-btn{
  border:none;
  background:transparent;
  color:#4b5563;
  padding:8px 12px;
  border-radius:999px;
  cursor:pointer;
  font-size:.9rem;
  font-weight:600;
}
.billing-btn.is-active{
  background:#ffffff;
  color:#1d4ed8;
  font-weight:700;
  box-shadow:0 2px 8px rgba(129,140,248,.35);
}

/* ===== Cards de planos ===== */
.plans-select{
  display:grid;
  gap:12px;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}
.plan-option{
  position:relative;
  display:block;
  border-radius:16px;
  padding:14px;
  cursor:pointer;
  border:1px solid rgba(148,163,184,.5);
  background:#ffffff;
  transition: box-shadow .18s ease, transform .06s ease, border-color .18s ease, background .18s ease;
  color:#0f172a;
}
.plan-option:hover{
  box-shadow:0 8px 24px rgba(15,23,42,.10);
  transform:translateY(-1px);
}
.plan-option input{
  position:absolute;
  opacity:0;
  pointer-events:none;
}

.plan-option .po-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
}
.plan-option .po-head h3{
  margin:0;
  font-size:1rem;
  font-weight:800;
}
.plan-option .badge.badge--hit{
  display:inline-flex;
  align-items:center;
  padding:4px 10px;
  border-radius:999px;
  font-size:.78rem;
  font-weight:800;
  background:#ecfeff;
  color:#0f766e;
  border:1px solid #5eead4;
}
.plan-option .po-price{
  margin:8px 0 6px;
  font-weight:800;
  color:#0f172a;
}

/* lista de features (quando description vira <ul>) */
.plan-option .feat{
  margin:6px 0 0;
  padding-left:0;
}
.plan-option .feat li{
  margin:4px 0;
  list-style:none;
  position:relative;
  padding-left:18px;
  font-size:.9rem;
  color:#4b5563;
}
.plan-option .feat li.ok::before{
  content:"✓";
  position:absolute;
  left:0;
  top:0;
  font-weight:800;
  font-size:.8rem;
  color:#16a34a;
}

/* Destaque mais forte no selecionado */
.plan-option.is-selected{
  border-color:var(--blue);
  box-shadow:
    0 0 0 1px rgba(59,130,246,.6),
    0 10px 26px rgba(37,99,235,.20);
  background:linear-gradient(180deg,#ffffff,#eff6ff);
}

/* Contorno no plano atual (ATIVO) */
.plan-option.is-current{
  outline:2px dashed rgba(129,140,248,.8);
  outline-offset:6px;
}

/* Pill de PENDÊNCIA (aguardando pagamento) */
.pending-pill{
  position:absolute;
  top:10px;
  right:10px;
  font-size:.78rem;
  font-weight:800;
  color:#854d0e;
  border-radius:999px;
  padding:5px 8px;
  border:1px solid rgba(234,179,8,.65);
  background:#fef9c3;
}

/* ===== Ações ===== */
.member-plans .form-actions{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
  margin-top:20px;
}
.member-plans .form-actions .muted{
  font-size:.82rem;
}

/* ===== Modal (overlay escuro + card claro) ===== */
.modal{
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.65);
  display:none;
  place-items:center;
  padding:16px;
  z-index:100;
  transition: opacity .12s ease;
  opacity:0;
}
.modal.is-open{
  opacity:1;
}
.modal-box{
  width:min(540px, 96vw);
  border-radius:18px;
  transform: scale(.98);
  transition: transform .12s ease, opacity .12s ease;
  opacity:.98;
}
.modal.is-open .modal-box{
  transform: scale(1);
  opacity:1;
}

/* ===== Mobile tweaks ===== */
@media (max-width:720px){
  .member-plans .current-plan{
    flex-direction:column;
    align-items:flex-start;
    gap:8px;
  }
  .member-plans .cp-right{
    width:100%;
    display:flex;
    justify-content:space-between;
  }
}
</style>
