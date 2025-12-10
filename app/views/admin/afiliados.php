<?php
// Página simples de Admin para gerir Afiliados
?>
<section style="max-width:1100px;margin:0 auto;padding:16px;">
  <h1 style="margin:0 0 12px;">Afiliados • Sistema</h1>

  <div style="display:grid;gap:12px;grid-template-columns: repeat(2,minmax(0,1fr));">
    <div style="border:1px solid #eee;border-radius:12px;padding:12px;">
      <h2 style="margin:0 0 8px;font-size:18px;">Configurações</h2>
      <form id="aff-settings" onsubmit="return saveSettings(event)">
        <div style="display:grid;gap:10px;">
          <label>Percentual de comissão (%)
            <input type="number" step="0.1" min="0" id="st_percent" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:8px;">
          </label>
          <label>Valor mínimo para saque (R$)
            <input type="number" step="0.01" min="0" id="st_min" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:8px;">
          </label>
          <label>Validade do cookie de indicação (dias)
            <input type="number" step="1" min="1" id="st_cookie" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:8px;">
          </label>
          <div>
            <button class="btn" style="padding:10px 14px;border:1px solid #ddd;border-radius:8px;background:#fff;cursor:pointer;">Salvar</button>
            <span id="st_msg" style="margin-left:8px;color:#090;"></span>
          </div>
        </div>
      </form>
    </div>

    <div style="border:1px solid #eee;border-radius:12px;padding:12px;">
      <h2 style="margin:0 0 8px;font-size:18px;">Saques (payouts)</h2>
      <div style="margin-bottom:8px;">
        <label>Status:
          <select id="payout_filter" onchange="loadPayouts()" style="padding:6px;border:1px solid #ddd;border-radius:8px;">
            <option value="">Todos</option>
            <option>requested</option>
            <option>approved</option>
            <option>paid</option>
            <option>rejected</option>
          </select>
        </label>
      </div>
      <div style="overflow:auto;max-height:360px;" id="payout_list">Carregando...</div>
    </div>
  </div>

  <div style="border:1px solid #eee;border-radius:12px;padding:12px;margin-top:12px;">
    <h2 style="margin:0 0 8px;font-size:18px;">Conversões</h2>
    <div style="margin-bottom:8px;">
      <label>Status:
        <select id="conv_filter" onchange="loadConversions()" style="padding:6px;border:1px solid #ddd;border-radius:8px;">
          <option value="all">Todos</option>
          <option>pending</option>
          <option>approved</option>
          <option>rejected</option>
        </select>
      </label>
    </div>
    <div style="overflow:auto;max-height:420px;" id="conv_list">Carregando...</div>
  </div>
</section>

<script>
async function jfetch(url, opts) {
  const res = await fetch(url, Object.assign({headers: {'Content-Type':'application/x-www-form-urlencoded'}}, opts||{}));
  const txt = await res.text();
  try { return JSON.parse(txt); } catch(e) { return {error:txt}; }
}

async function loadSettings() {
  const r = await jfetch('/?r=api/admin/affiliate/settings/get');
  if (r && r.data) {
    document.getElementById('st_percent').value = (r.data.percent ?? 10);
    document.getElementById('st_min').value     = (r.data.min_payout ?? 50);
    document.getElementById('st_cookie').value  = (r.data.cookie_days ?? 30);
  }
}
async function saveSettings(ev) {
  ev.preventDefault();
  const p = new URLSearchParams();
  p.set('percent', document.getElementById('st_percent').value);
  p.set('min_payout', document.getElementById('st_min').value);
  p.set('cookie_days', document.getElementById('st_cookie').value);
  const r = await jfetch('/?r=api/admin/affiliate/settings/save',{method:'POST', body:p});
  document.getElementById('st_msg').textContent = r && r.ok ? 'Salvo!' : 'Erro';
  setTimeout(()=>document.getElementById('st_msg').textContent='', 1500);
  return false;
}

async function loadConversions() {
  const s = document.getElementById('conv_filter').value || 'all';
  const r = await jfetch('/?r=api/admin/affiliate/list&status='+encodeURIComponent(s));
  const el = document.getElementById('conv_list');
  if (!r || !r.data) { el.textContent = 'Erro'; return; }
  const rows = r.data.items || [];
  if (!rows.length) { el.textContent = 'Sem dados.'; return; }
  el.innerHTML = `
    <table style="width:100%;border-collapse:collapse;min-width:800px;">
      <thead><tr style="background:#fafafa;">
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">ID</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Afiliado</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Indicado</th>
        <th style="text-align:right;padding:6px;border-bottom:1px solid #eee;">Valor</th>
        <th style="text-align:right;padding:6px;border-bottom:1px solid #eee;">Comissão</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Status</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Criado</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Ação</th>
      </tr></thead>
      <tbody>
        ${rows.map(r=>`
          <tr>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.id}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.affiliate_name||('#'+r.affiliate_user_id)}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.member_name||r.member_email}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;text-align:right;">R$ ${Number(r.amount||0).toFixed(2)}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;text-align:right;"><strong>R$ ${Number(r.commission||0).toFixed(2)}</strong></td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.status}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.created_at||''}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">
              ${r.status!=='approved' ? `<button onclick="approveConv(${r.id})" style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer;">Aprovar</button>` : ''}
            </td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}
async function approveConv(id){
  const p = new URLSearchParams(); p.set('id', id);
  await jfetch('/?r=api/admin/affiliate/mark-paid', {method:'POST', body:p});
  loadConversions();
}

async function loadPayouts() {
  const s = document.getElementById('payout_filter').value || '';
  const r = await jfetch('/?r=api/admin/affiliate/payouts/list'+(s?('&status='+encodeURIComponent(s)):''));
  const el = document.getElementById('payout_list');
  if (!r || !r.data) { el.textContent='Erro'; return; }
  const rows = r.data.items || [];
  if (!rows.length) { el.textContent = 'Sem dados.'; return; }
  el.innerHTML = `
    <table style="width:100%;border-collapse:collapse;min-width:700px;">
      <thead><tr style="background:#fafafa;">
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">ID</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Afiliado</th>
        <th style="text-align:right;padding:6px;border-bottom:1px solid #eee;">Valor</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">PIX</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Status</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Criado</th>
        <th style="text-align:left;padding:6px;border-bottom:1px solid #eee;">Ação</th>
      </tr></thead>
      <tbody>
        ${rows.map(r=>`
          <tr>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.id}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.affiliate_name||r.affiliate_email}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;text-align:right;"><strong>R$ ${Number(r.amount||0).toFixed(2)}</strong></td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.pix_type||'-'}: ${r.pix_key||''}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.status}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">${r.created_at||''}</td>
            <td style="padding:6px;border-bottom:1px solid #f0f0f0;">
              ${r.status==='requested' ? `
                <button onclick="payoutApprove(${r.id})" style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer;">Aprovar</button>
                <button onclick="payoutReject(${r.id})"  style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer;">Rejeitar</button>
              ` : ''}
              ${r.status==='approved' ? `
                <button onclick="payoutMarkPaid(${r.id})" style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer;">Marcar Pago</button>
              ` : ''}
            </td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}
async function payoutApprove(id){
  const p = new URLSearchParams(); p.set('id', id);
  await jfetch('/?r=api/admin/affiliate/payouts/approve', {method:'POST', body:p});
  loadPayouts();
}
async function payoutMarkPaid(id){
  const p = new URLSearchParams(); p.set('id', id);
  await jfetch('/?r=api/admin/affiliate/payouts/mark-paid', {method:'POST', body:p});
  loadPayouts();
}
async function payoutReject(id){
  const reason = prompt('Motivo (opcional):') || '';
  const p = new URLSearchParams(); p.set('id', id); p.set('reason', reason);
  await jfetch('/?r=api/admin/affiliate/payouts/reject', {method:'POST', body:p});
  loadPayouts();
}

loadSettings();
loadConversions();
loadPayouts();
</script>
