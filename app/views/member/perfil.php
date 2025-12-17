<?php
// Member • Perfil — sem sidebar, largura igual ao Header, formulário compacto e responsivo
Auth::start();

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function fmtDateBR($d){
  if(!$d) return '—';
  $s = explode('T', (string)$d)[0] ?: '';
  if(!$s) return '—';
  [$y,$m,$dd] = array_pad(explode('-', $s), 3, null);
  return ($y && $m && $dd) ? sprintf('%02d/%02d/%04d', $dd, $m, $y) : '—';
}
?>
<section class="container member" style="margin-top:18px">
  <section class="member-main">
    <!-- “Carteirinha” simplificada do perfil -->
    <div class="glass-card card-card" id="card-wrap" aria-live="polite">
      <div class="card-row">
        <div class="card-left">
          <div class="brand">AVIV Club</div>
          <div class="plan" id="card-plan">Plano —</div>
          <div class="user" id="card-user">Nome do membro</div>
          <div class="meta">
            <span id="card-id">ID —</span>
            <span id="card-valid">Válido —</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Formulário de perfil -->
    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub" style="margin-bottom:6px">Meus dados</h2>

      <form id="pf-form" class="pf-grid" onsubmit="return false;">
        <div class="input-wrap span-2">
          <label for="pf-name">Nome completo</label>
          <input id="pf-name" class="field" type="text" placeholder="Seu nome" required autocomplete="name">
        </div>

        <div class="input-wrap">
          <label for="pf-email">E-mail</label>
          <input id="pf-email" class="field field--ro" type="email" placeholder="email@exemplo.com" disabled autocomplete="email">
        </div>

        <div class="input-wrap">
          <label for="pf-phone">Telefone</label>
          <input id="pf-phone" class="field" type="tel" placeholder="(DDD) 90000-0000" autocomplete="tel">
        </div>

        <div class="input-wrap">
          <label for="pf-doc">Documento</label>
          <input id="pf-doc" class="field" type="text" placeholder="CPF / Doc.">
        </div>

        <div class="input-wrap">
          <label for="pf-birth">Nascimento</label>
          <input id="pf-birth" class="field" type="date">
        </div>

        <div class="input-wrap span-3">
          <label for="pf-address">Endereço</label>
          <input id="pf-address" class="field" type="text" placeholder="Rua, número, complemento" autocomplete="street-address">
        </div>

        <div class="input-wrap">
          <label for="pf-city">Cidade</label>
          <input id="pf-city" class="field" type="text" placeholder="Sua cidade" autocomplete="address-level2">
        </div>

        <div class="input-wrap">
          <label for="pf-state">UF</label>
          <input id="pf-state" class="field" type="text" maxlength="2" placeholder="UF" autocomplete="address-level1">
        </div>

        <div class="input-wrap">
          <label for="pf-zip">CEP</label>
          <input id="pf-zip" class="field" type="text" placeholder="00000-000" autocomplete="postal-code">
        </div>

        <div class="form-actions span-3">
          <div class="email-note">
            O e-mail não pode ser alterado por aqui.
          </div>
          <button class="btn" id="pf-save" type="submit">Salvar alterações</button>
        </div>
      </form>

      <div id="pf-alert" class="alert" style="display:none"></div>
    </div>
  </section>
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

    // evita bind duplicado (interval + mutation observer)
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
const fmtDateBR = (d)=> {
  if(!d) return '—';
  const s = String(d).split('T')[0]||'';
  const [y,m,dd] = s.split('-'); if(!y) return '—';
  return `${dd}/${m}/${y}`;
};

/* ===== DOM ===== */
const cardPlan  = document.getElementById('card-plan');
const cardUser  = document.getElementById('card-user');
const cardId    = document.getElementById('card-id');
const cardValid = document.getElementById('card-valid');

const f = {
  name:    document.getElementById('pf-name'),
  email:   document.getElementById('pf-email'),
  phone:   document.getElementById('pf-phone'),
  doc:     document.getElementById('pf-doc'),
  birth:   document.getElementById('pf-birth'),
  address: document.getElementById('pf-address'),
  city:    document.getElementById('pf-city'),
  state:   document.getElementById('pf-state'),
  zip:     document.getElementById('pf-zip'),
  save:    document.getElementById('pf-save'),
  alert:   document.getElementById('pf-alert'),
  form:    document.getElementById('pf-form'),
};

function setAlert(msg, ok=true){
  f.alert.style.display='block';
  f.alert.textContent = msg;
  f.alert.style.borderColor = ok ? 'rgba(34,197,94,.55)' : 'rgba(248,113,113,.7)';
  f.alert.style.background  = ok ? 'rgba(22,163,74,.08)' : 'rgba(248,113,113,.08)';
  f.alert.style.color       = ok ? '#065f46' : '#7f1d1d';
  clearTimeout(f.alert._t);
  f.alert._t = setTimeout(()=>{ f.alert.style.display='none'; }, 2200);
}

/* ===== Carteirinha (topo) ===== */
async function loadOverview(){
  try{
    const r = await fetch('/?r=api/member/overview');
    if(!r.ok) return;
    const j = await r.json();
    const sub = j?.subscription||{};
    cardPlan.textContent  = 'Plano ' + (sub.plan_name || sub.plan_id || '—');
    cardUser.textContent  = esc(j?.user?.name || '—'); // mantém como estava
    cardId.textContent    = 'ID #' + (j?.user?.id || '—');
    cardValid.textContent = sub.renew_at ? ('Válido até ' + fmtDateBR(sub.renew_at)) : 'Sem renovação';
  }catch(e){}
}

/* ===== Perfil (form) ===== */
async function loadProfile(){
  try{
    const r = await fetch('/?r=api/member/profile');
    const j = await r.json();
    if(!r.ok){ setAlert(j.error||'Falha ao carregar perfil', false); return; }
    const u = j.user||{};
    f.name.value    = u.name || '';
    f.email.value   = u.email || '';
    f.phone.value   = u.phone || u.telefone || '';
    f.doc.value     = u.document || u.cpf || '';
    f.birth.value   = (u.birth_date || u.birthday || '').split('T')[0] || '';
    f.address.value = u.address || u.address_line || '';
    f.city.value    = u.city || '';
    f.state.value   = u.state || u.uf || '';
    f.zip.value     = u.zip || u.zipcode || u.postal_code || '';
  }catch(e){
    setAlert('Erro ao carregar seu perfil', false);
  }
}

/* ===== Salvar ===== */
f.form.addEventListener('submit', async ()=>{
  f.save.disabled = true;
  const payload = new URLSearchParams({
    name: f.name.value.trim(),
    phone: f.phone.value.trim(),
    document: f.doc.value.trim(),
    birth_date: f.birth.value.trim(),
    address: f.address.value.trim(),
    city: f.city.value.trim(),
    state: f.state.value.trim(),
    zip: f.zip.value.trim()
  });

  try{
    const r = await fetch('/?r=api/member/profile/save', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: payload
    });
    const j = await r.json();
    if(!r.ok){ setAlert(j.error||'Falha ao salvar', false); f.save.disabled=false; return; }
    setAlert('Dados atualizados!');
    await loadProfile();
    await loadOverview();
  }catch(e){
    setAlert('Erro ao salvar', false);
  }finally{
    f.save.disabled = false;
  }
});

/* init */
(async function(){
  await loadOverview();
  await loadProfile();
})();
</script>

<style>
/* ===== FIX: impede scroll lateral (menu) ===== */
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

/* Cartões base — alinhados ao tema claro */
.member-main .glass-card{
  background:#ffffff;
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:16px;
  color:var(--ink);
  box-shadow:0 12px 30px rgba(15,23,42,.06);
}

.member-main .sect-sub{
  margin:0 0 8px;
  font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight:800;
  color:var(--ink);
}

.member-main .alert{
  margin-top:10px;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid rgba(148,163,184,.7);
  background:#fee2e2;
  color:#7f1d1d;
  font-weight:600;
}

/* ===== “Carteirinha” do perfil ===== */
.card-card{
  padding:16px;
  border-radius:18px;
  background:
    radial-gradient(900px 450px at 0% 0%, rgba(93,173,226,.22), transparent 60%),
    linear-gradient(135deg, #ffffff, #e5f3ff);
  border:1px solid rgba(148,163,184,.7);
  box-shadow:0 14px 40px rgba(15,23,42,.10);
}

.card-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
}
@media (max-width:620px){
  .card-row{
    flex-direction:column;
    align-items:flex-start;
  }
}

.card-left{
  display:grid;
  gap:4px;
}

.card-left .brand{
  font-weight:900;
  letter-spacing:.08em;
  font-size:.82rem;
  text-transform:uppercase;
  color:#64748b;
}
.card-left .plan{
  font-size:.95rem;
  font-weight:800;
  color:#0f172a;
}
.card-left .user{
  font-size:1.1rem;
  font-weight:900;
  color:#0b1120;
}
.card-left .meta{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  font-size:.86rem;
  color:#64748b;
}

/* ===== Form enxuto e responsivo ===== */
.pf-grid{
  display:grid;
  gap:10px;
  grid-template-columns: repeat(3, minmax(0,1fr));
}
@media (max-width:900px){
  .pf-grid{ grid-template-columns:1fr 1fr; }
}
@media (max-width:620px){
  .pf-grid{
    grid-template-columns:1fr;
  }
  .pf-grid .input-wrap,
  .pf-grid .span-2,
  .pf-grid .span-3{
    grid-column:1 / -1 !important;
  }
}

.member-main .input-wrap{
  display:grid;
  gap:4px;
  margin-top:0;
}

.member-main .input-wrap label{
  font-size:.86rem;
  color:#475569;
  line-height:1.2;
  font-weight:600;
}

.member-main .field,
.member-main select.field,
.member-main textarea.field{
  width:100%;
  box-sizing:border-box;
  padding:8px 11px;
  border-radius:999px;
  border:1px solid rgba(148,163,184,.7);
  background:#ffffff;
  color:#111827;
  outline:none;
  line-height:1.25;
  min-height:38px;
  font-size:.95rem;
  transition:border-color .18s ease, box-shadow .18s ease, background .18s ease;
}

.member-main .field::placeholder{ color:#9ca3af; }

.member-main .field:focus{
  border-color:var(--blue);
  box-shadow:0 0 0 3px color-mix(in oklab, var(--blue) 18%, transparent);
  background:#f9fafb;
}

.member-main .field--ro{
  background:#f3f4ff;
  color:#6b7280;
  cursor:not-allowed;
}

.span-2{ grid-column: span 2; }
.span-3{ grid-column: 1 / -1; }

.member-main .form-actions{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-top:4px;
  flex-wrap:wrap;
  gap:10px;
}

.email-note{
  font-size:.8rem;
  color:#64748b;
}

/* Botão principal */
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
  box-shadow:0 12px 24px rgba(15,23,42,.14);
  transition:transform .06s ease, filter .18s ease, box-shadow .18s ease;
}
.member-main .btn:hover{
  filter:brightness(1.05);
  box-shadow:0 16px 30px rgba(15,23,42,.16);
}
.member-main .btn:active{
  transform:translateY(1px);
  box-shadow:0 8px 18px rgba(15,23,42,.16);
}
.member-main .btn[disabled]{
  opacity:.7;
  cursor:not-allowed;
  box-shadow:none;
}
</style>
