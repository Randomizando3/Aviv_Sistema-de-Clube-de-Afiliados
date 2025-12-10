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

    <!-- Lista (desktop: tabela) -->
    <div class="glass-card only-desktop" style="margin-top:12px">
      <div class="table-wrap" role="region" aria-label="Tabela de usuários">
        <table class="tbl-users">
          <colgroup>
            <col style="width:70px" />
            <col style="width:26%" />
            <col />
            <col style="width:200px" />
            <col style="width:170px" />
            <col style="width:160px" />
          </colgroup>
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

    <!-- Lista (mobile: cards) -->
    <div class="glass-card only-mobile" style="margin-top:12px; display:none">
      <div id="adm-users-cards" class="users-cards" role="list" aria-label="Lista de usuários"></div>
    </div>

    <div id="adm-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
  </section>
</section>

<script>
const tbody   = document.getElementById('adm-users');
const cardsEl = document.getElementById('adm-users-cards');
const q       = document.getElementById('adm-q');
const alertBox= document.getElementById('adm-alert');
const esc = s => (s||'').replace(/[&<>"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));

function roleOption(v, label, cur){ return `<option value="${v}" ${cur===v?'selected':''}>${label}</option>`; }
function roleChip(role){
  const map = { admin:'Admin', member:'Membro', partner:'Parceiro', affiliate:'Afiliado' };
  const lab = map[role] || role;
  return `<span class="chip chip--${role}">${esc(lab)}</span>`;
}

async function loadUsers() {
  tbody.innerHTML = `<tr><td colspan="6" class="muted" style="text-align:center;padding:16px">Carregando…</td></tr>`;
  cardsEl.innerHTML = `<p class="muted">Carregando…</p>`;

  const term = q.value.trim();
  const url = new URL(window.location.origin + '/');
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

  // Desktop
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

  // Mobile
  cardsEl.innerHTML = list.map(u=>{
    const role = (u.role || 'member').toLowerCase();
    return `
      <article class="user-card" role="listitem" data-id="${u.id}">
        <header class="uc-head">
          <strong>#${u.id}</strong>
          <span class="chip chip--${role}">${role}</span>
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
  showAlert('Papel atualizado: ' + role.toUpperCase());
  await loadUsers();
}

function showAlert(msg){
  alertBox.style.display='block';
  alertBox.textContent = msg;
  setTimeout(()=>{ alertBox.style.display='none'; }, 1800);
}
function showError(msg){
  tbody.innerHTML = `<tr><td colspan="6" class="muted" style="text-align:center;padding:16px">${esc(msg)}</td></tr>`;
  cardsEl.innerHTML = `<p class="muted">${esc(msg)}</p>`;
}

/* eventos */
document.getElementById('adm-search')?.addEventListener('click', loadUsers);
document.getElementById('adm-clear')?.addEventListener('click', ()=> { q.value=''; q.focus(); loadUsers(); });
q?.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); loadUsers(); }});

document.addEventListener('change', e=>{
  const sel = e.target.closest('.role-select');
  if(!sel) return;
  const id = sel.dataset.id;
  const role = sel.value;
  setRole(id, role);
});

loadUsers();
</script>

<style>
:root{
  --combo-bg:   #281B3E;           /* roxo primário (igual às outras combos) */
  --combo-bg-2: #201431;           /* roxo escuro */
  --combo-bd:   rgba(186,126,255,.35);
}

/* ===== largura igual Header ===== */
.container.admin{
  width:min(92vw, var(--container)) !important;
  margin-inline:auto;
  padding-inline:0;
}

/* base */
.glass-card{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); padding:14px; border-radius:14px; color:#fff; }
.sect-title{ margin:0 0 10px; font-weight:800; }
.muted{ opacity:.85; font-size:.9rem; color:#cfe1ff; }

/* toolbar */
.adm-toolbar{ margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.field{
  width:min(520px,100%);
  padding:10px 12px; border-radius:10px;
  border:1px solid rgba(255,255,255,.20); background:rgba(255,255,255,.08);
  color:#eaf3ff; outline:none;
}
.tool-actions{ display:flex; gap:8px }

/* tabela */
.only-desktop{ display:block; }
.only-mobile{ display:none; }
.table-wrap{ overflow:auto; -webkit-overflow-scrolling:touch; }
.tbl-users{ width:100%; border-collapse:separate; border-spacing:0; min-width:920px; background:rgba(255,255,255,.04); }
.tbl-users thead th{ background:#fff; color:#281B3E; font-weight:800; padding:10px 8px; text-align:left; white-space:nowrap; }
.tbl-users tbody td{ padding:10px 8px; border-bottom:1px dashed rgba(255,255,255,.18); vertical-align:middle; }

/* célula de papel: chip + combobox roxa */
.role-cell{ display:flex; align-items:center; gap:10px; }

/* chips (status) — mantém neutro para diferenciar do select roxo */
.chip{ display:inline-flex; align-items:center; padding:6px 10px; border-radius:999px; background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.18); font-size:.85rem; }
.chip--admin{ background:rgba(255,77,79,.15); border-color:rgba(255,77,79,.35) }
.chip--partner{ background:rgba(255,193,7,.18); border-color:rgba(255,193,7,.35) }
.chip--affiliate{ background:rgba(76,175,80,.18); border-color:rgba(76,175,80,.35) }
.chip--member{ background:rgba(255,255,255,.10) }

/* SELECT (combobox) com tema roxo e "botão" (seta) destacado */
.role-select{
  appearance:none; -webkit-appearance:none; -moz-appearance:none;
  padding:8px 38px 8px 12px;
  border-radius:10px;
  background:var(--combo-bg);
  color:#f1e9ff;
  border:1px solid var(--combo-bd);
  cursor:pointer;
  font: inherit;
  line-height:1.2;
  position:relative;

  /* seta (botão para acionar a combo) com destaque lilás */
  background-image:
    url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='%23d7c0ff' d='M7 10l5 5 5-5z'/></svg>");
  background-repeat:no-repeat;
  background-position:right 10px center;
}
.role-select:hover{ filter:brightness(1.03); }
.role-select:focus{
  outline:none;
  box-shadow:0 0 0 2px rgba(186,126,255,.55);
}

/* ações */
.row-actions{ display:flex; gap:8px; }
.btn{ padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.10); color:#fff; cursor:pointer }
.btn.btn-sm{ padding:8px 12px }
.btn--ghost{ background:transparent }
.alert{ margin-top:12px; padding:10px 12px; border-radius:10px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.18); color:#fff }

/* mobile cards */
@media (max-width:720px){
  .only-desktop{ display:none; }
  .only-mobile{ display:block !important; }

  .users-cards{ display:grid; gap:10px; }
  .user-card{ border:1px solid rgba(255,255,255,.12); border-radius:12px; background:rgba(255,255,255,.06); padding:12px; }
  .uc-head{ display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; text-transform:capitalize; }
  .uc-role{ margin-top:8px; }
  .uc-actions{ margin-top:8px; display:flex; justify-content:flex-end; }

  /* mantém o mesmo tema roxo no select do card */
  .user-card .role-select{ width:100%; }
}
</style>
