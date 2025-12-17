<?php
// Admin • Usuários — sem sidebar, largura igual ao Header, tabela (desktop) + cards (mobile)
?>
<section class="container admin users-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Admin • Usuários</h1>

      <div class="adm-toolbar">
        <input id="adm-q" class="field" type="search" placeholder="Buscar por nome ou e-mail">
        <div class="tool-actions">
          <button class="btn btn-sm" id="adm-search" type="button">Buscar</button>
          <button class="btn btn-sm btn--ghost" id="adm-clear" type="button">Limpar</button>
        </div>
      </div>
    </div>

    <!-- Lista (desktop) -->
    <div class="glass-card only-desktop" style="margin-top:12px">
      <div class="table-wrap" role="region" aria-label="Tabela de usuários">
        <table class="tbl-users">
          <thead>
            <tr>
              <th>#</th>
              <th>Nome</th>
              <th>E-mail</th>
              <th>Papel</th>
              <th>Criado em</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody id="adm-users">
            <tr><td colspan="6" class="muted" style="text-align:center;padding:16px">Carregando…</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Lista mobile -->
    <div class="glass-card only-mobile" style="margin-top:12px; display:none">
      <div id="adm-users-cards" class="users-cards" role="list"></div>
    </div>

    <div id="adm-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
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

/* =========================
   Admin • Usuários
========================= */
const tbody    = document.getElementById('adm-users');
const cardsEl  = document.getElementById('adm-users-cards');
const q        = document.getElementById('adm-q');
const alertBox = document.getElementById('adm-alert');

const esc = s => String(s||'').replace(/[&<>"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
function roleOption(v, label, cur){ return `<option value="${v}" ${cur===v?'selected':''}>${label}</option>`; }
function roleChip(role){
  const map = { admin:'Admin', member:'Membro', partner:'Parceiro', affiliate:'Afiliado' };
  return `<span class="chip chip--${esc(role)}">${esc(map[role] || role)}</span>`;
}

function showAlert(msg){
  alertBox.style.display='block';
  alertBox.textContent = msg;
  setTimeout(()=> alertBox.style.display='none', 1800);
}
function showError(msg){
  tbody.innerHTML = `<tr><td colspan="6" class="muted" style="text-align:center;padding:16px">${esc(msg)}</td></tr>`;
  cardsEl.innerHTML = `<p class="muted">${esc(msg)}</p>`;
}

async function loadUsers() {
  tbody.innerHTML = `<tr><td colspan="6" class="muted" style="text-align:center;padding:16px">Carregando…</td></tr>`;
  cardsEl.innerHTML = `<p class="muted">Carregando…</p>`;

  const term = q.value.trim();

  // evita problemas quando a app roda em subpasta/domínio com path
  const url = new URL(window.location.href);
  url.search = '';
  url.searchParams.set('r', 'api/admin/users/list');
  if (term) url.searchParams.set('q', term);

  const r = await fetch(url.toString());
  let j; try { j = await r.json(); } catch(e) { j = {error:'Erro de resposta'}; }
  if(!r.ok){ showError(j.error||'Falha ao listar'); return; }

  const list = j.users || [];
  if (!list.length){
    tbody.innerHTML = `<tr><td colspan="6" class="muted" style="text-align:center;padding:16px">Nenhum usuário encontrado.</td></tr>`;
    cardsEl.innerHTML = `<p class="muted">Nenhum usuário encontrado.</p>`;
    return;
  }

  // DESKTOP
  tbody.innerHTML = list.map(u => {
    const role = (u.role || 'member').toLowerCase();
    return `
      <tr>
        <td>${u.id}</td>
        <td>${esc(u.name||'')}</td>
        <td>${esc(u.email||'')}</td>
        <td>
          <div class="role-cell">
            ${roleChip(role)}
            <select class="role-select" data-id="${u.id}">
              ${roleOption('member','Membro',role)}
              ${roleOption('admin','Admin',role)}
              ${roleOption('partner','Parceiro',role)}
              ${roleOption('affiliate','Afiliado',role)}
            </select>
          </div>
        </td>
        <td>${esc(u.created_at||'')}</td>
        <td class="row-actions">
          <button class="btn btn-sm btn--ghost" disabled>Entrar como</button>
        </td>
      </tr>`;
  }).join('');

  // MOBILE
  cardsEl.innerHTML = list.map(u=>{
    const role = (u.role || 'member').toLowerCase();
    return `
      <article class="user-card" role="listitem">
        <header class="uc-head">
          <strong>#${u.id}</strong>
          ${roleChip(role)}
        </header>

        <p><strong>Nome:</strong> ${esc(u.name||'')}</p>
        <p><strong>Email:</strong> ${esc(u.email||'')}</p>
        <p><strong>Criado:</strong> ${esc(u.created_at||'')}</p>

        <div class="uc-role">
          <label>Papel:</label>
          <select class="field role-select" data-id="${u.id}">
            ${roleOption('member','Membro',role)}
            ${roleOption('admin','Admin',role)}
            ${roleOption('partner','Parceiro',role)}
            ${roleOption('affiliate','Afiliado',role)}
          </select>
        </div>

        <div class="uc-actions">
          <button class="btn btn-sm btn--ghost" disabled>Entrar como</button>
        </div>
      </article>`;
  }).join('');
}

async function setRole(id, role){
  const r = await fetch('/?r=api/admin/users/set-role', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:new URLSearchParams({id, role})
  });
  let j; try { j = await r.json(); } catch(e){ j={error:'Erro na resposta'}; }

  if(!r.ok){ showAlert(j.error||'Falha ao alterar papel'); return; }
  showAlert('Papel atualizado!');
  await loadUsers();
}

/* eventos */
document.getElementById('adm-search')?.addEventListener('click', loadUsers);
document.getElementById('adm-clear')?.addEventListener('click', ()=> { q.value=''; q.focus(); loadUsers(); });
q?.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); loadUsers(); }});

document.addEventListener('change', e=>{
  const sel = e.target.closest('.role-select');
  if(!sel) return;
  setRole(sel.dataset.id, sel.value);
});

loadUsers();
</script>

<style>
/* ===== container igual ao header ===== */
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

/* ===== cards claros, estilo clean ===== */
.glass-card{
  background:rgba(255,255,255,.92);
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:18px;
  color:var(--text, #111322);
  box-shadow:0 18px 40px rgba(15,23,42,.06);
}

.sect-title{
  margin:0 0 8px;
  font-weight:800;
  color:var(--text, #111322);
}

.muted{
  color:var(--muted,#6b7280);
  opacity:1;
  font-size:.9rem;
}

.alert{
  margin-top:12px;
  padding:10px 12px;
  border-radius:12px;
  background:#fff;
  border:1px solid rgba(15,23,42,.08);
  color:var(--text, #111322);
}

/* toolbar */
.adm-toolbar{ margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between; }
.tool-actions{ display:flex; gap:8px; flex-wrap:wrap; }

.field{
  width:min(520px,100%);
  padding:10px 12px;
  border-radius:12px;
  border:1px solid #d0d7e2;
  background:#fff;
  color:#111322;
  outline:none;
}
.field:focus{ border-color:#94a3b8; box-shadow:0 0 0 3px rgba(148,163,184,.18); }

/* botões */
.btn{
  padding:10px 14px;
  border-radius:12px;
  border:1px solid #d0d7e2;
  background:#fff;
  color:#111322;
  cursor:pointer;
  font-weight:600;
}
.btn-sm{ padding:8px 12px; font-size:.9rem; }
.btn--ghost{ background:transparent; border:1px solid #d0d7e2; }

/* tabela */
.table-wrap{ overflow:auto; }
.tbl-users{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  min-width:920px;
  background:#fff;
  border-radius:14px;
  overflow:hidden;
}
.tbl-users thead th{
  background:#f8fafc;
  color:#111322;
  font-weight:800;
  padding:10px;
  border-bottom:1px solid #e5e7eb;
}
.tbl-users tbody td{
  padding:10px;
  border-bottom:1px solid #f1f5f9;
  vertical-align:middle;
}

.role-cell{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}

/* chips */
.chip{
  padding:6px 12px;
  border-radius:999px;
  font-size:.8rem;
  font-weight:700;
  color:#111322;
  border:1px solid #e5e7eb;
  white-space:nowrap;
}
.chip--admin{ background:#ffe5e5; }
.chip--partner{ background:#fff4d6; }
.chip--affiliate{ background:#d6f6d6; }
.chip--member{ background:#eef2ff; }

/* select papel */
.role-select{
  padding:8px 12px;
  border-radius:12px;
  border:1px solid #d0d7e2;
  background:#fff;
  color:#111322;
  min-width:160px;
}

/* mobile cards */
.only-mobile{ display:none; }
@media (max-width:720px){
  .only-desktop{ display:none; }
  .only-mobile{ display:block !important; }

  .users-cards{ display:grid; gap:12px; }
  .user-card{
    background:#fff;
    border-radius:14px;
    padding:14px;
    border:1px solid #d0d7e2;
  }
  .uc-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-bottom:8px;
    font-weight:800;
  }
  .uc-role{ margin-top:10px; display:grid; gap:6px; }
  .uc-actions{ margin-top:10px; display:flex; justify-content:flex-end; }
}
</style>
