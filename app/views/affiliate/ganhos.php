<?php
// Afiliado • Ganhos — glass style igual ao admin, SEM fetch (evita 403)
// Lemos percent/min diretamente do serviço para refletir o BD em tempo real.

$stats = array_merge([
  'n_approved'     => 0,
  'n_pending'      => 0,
  'n_rejected'     => 0,
  'sum_commission' => 0.0,
  'available'      => 0.0,
  'locked'         => 0.0,
  'min_payout'     => null,   // vamos calcular já-já
  'percent'        => null,   // idem
], is_array($stats ?? null) ? $stats : []);

$list = is_array($list ?? null) ? $list : [];

// Puxa do service se não vier preenchido (ou se vier inválido)
$percent = is_numeric($stats['percent']) ? (float)$stats['percent'] : \App\services\Affiliate::percent();
$minPay  = is_numeric($stats['min_payout']) ? (float)$stats['min_payout'] : \App\services\Affiliate::minPayout();

// helpers
$moeda = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
$pc    = fn($v) => number_format((float)$v, 1, ',', '.') . '%';
?>
<section class="container admin affiliate-page" style="margin-top:18px">
  <section class="admin-main">

    <!-- Cabeçalho -->
    <div class="glass-card card-head">
      <div class="head-row">
        <div>
          <h1 class="sect-title" style="margin:0">Afiliados • Ganhos</h1>
          <p class="muted" style="margin:6px 0 0">
            Você ganha <strong><?= $pc($percent) ?></strong> nas assinaturas pagas que vierem pelo seu link.
          </p>
        </div>
      </div>
    </div>

    <!-- KPIs -->
    <div class="kpis" style="margin-top:12px;">
      <article class="glass-card kpi">
        <div class="kpi-top">Aprovadas</div>
        <div class="kpi-num"><?= (int)$stats['n_approved'] ?></div>
        <div class="kpi-foot muted">Pendentes: <?= (int)$stats['n_pending'] ?> • Rejeitadas: <?= (int)$stats['n_rejected'] ?></div>
      </article>

      <article class="glass-card kpi">
        <div class="kpi-top">Comissão aprovada</div>
        <div class="kpi-num"><?= $moeda($stats['sum_commission']) ?></div>
      </article>

      <article class="glass-card kpi">
        <div class="kpi-top">Disponível p/ saque</div>
        <div class="kpi-num"><?= $moeda($stats['available']) ?></div>
        <div class="kpi-foot muted">Mín. saque: <strong><?= $moeda($minPay) ?></strong></div>
      </article>

      <article class="glass-card kpi">
        <div class="kpi-top">Em processamento</div>
        <div class="kpi-num"><?= $moeda($stats['locked']) ?></div>
        <div class="kpi-foot muted">% atual: <strong><?= $pc($percent) ?></strong></div>
      </article>
    </div>

    <!-- ===== Saques: Solicitar + Histórico ===== -->
    <div class="glass-card" style="margin-top:12px">
      <div class="sect-head" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
        <h2 class="sect-sub" style="margin:0">Solicitar saque</h2>
        <small class="muted">Mínimo: <strong><?= $moeda($minPay) ?></strong> • Disponível: <strong><?= $moeda($stats['available']) ?></strong></small>
      </div>

      <form id="payout-form" onsubmit="return false;" class="payout-form">
        <div class="row-3">
          <div class="input-wrap">
            <label>Valor do saque</label>
            <input id="payout-amount" class="field" type="number" step="0.01"
                   min="<?= number_format((float)$minPay, 2, '.', '') ?>"
                   max="<?= number_format((float)$stats['available'], 2, '.', '') ?>"
                   placeholder="Ex.: 100,00" required>
            <small class="muted">Mínimo: <?= $moeda($minPay) ?></small>
          </div>

          <div class="input-wrap">
            <label>Tipo da chave PIX</label>
            <select id="payout-pixtype" class="field" required>
              <option value="" selected disabled>Selecione</option>
              <option value="cpf">CPF</option>
              <option value="cnpj">CNPJ</option>
              <option value="email">E-mail</option>
              <option value="phone">Telefone</option>
              <option value="evp">Chave aleatória (EVP)</option>
            </select>
          </div>

          <div class="input-wrap">
            <label>Chave PIX</label>
            <input id="payout-pixkey" class="field" type="text" placeholder="Digite sua chave PIX" required>
          </div>
        </div>

        <div class="actions">
          <?php $canRequest = (float)$stats['available'] >= (float)$minPay; ?>
          <button id="payout-submit" class="btn" type="submit" <?= $canRequest ? '' : 'disabled' ?>>Solicitar saque</button>
          <?php if (!$canRequest): ?>
            <span class="muted">Você ainda não atingiu o mínimo para sacar.</span>
          <?php endif; ?>
        </div>

        <div id="payout-flash" class="flash" role="status" aria-live="polite"></div>
      </form>
    </div>

    <div class="glass-card" style="margin-top:12px">
      <div class="sect-head" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
        <h2 class="sect-sub" style="margin:0">Histórico de saques</h2>
      </div>

      <div class="table-wrap table-glass" role="region" aria-label="Tabela de saques">
        <table class="aff-table">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th>Data</th>
              <th class="num">Valor</th>
              <th>Status</th>
              <th>Chave PIX</th>
            </tr>
          </thead>
          <tbody id="payouts-body">
            <tr><td colspan="5" class="muted">Carregando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- ===== /Saques ===== -->

    <!-- Lista de conversões -->
    <div class="glass-card" style="margin-top:12px">
      <div class="sect-head" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
        <h2 class="sect-sub" style="margin:0">Conversões</h2>
      </div>

      <div class="table-wrap table-glass">
        <?php if (!$list): ?>
          <p class="muted" style="margin:4px 0 0">Sem conversões ainda.</p>
        <?php else: ?>
          <table class="aff-table">
            <thead>
              <tr>
                <th class="col-id">#</th>
                <th>Indicado</th>
                <th class="num">Valor</th>
                <th class="num">Comissão</th>
                <th>Status</th>
                <th>Criado</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $r): ?>
              <?php
                $id   = (int)($r['id'] ?? 0);
                $name = (string)($r['member_name'] ?? ($r['member_email'] ?? ('#'.$r['user_id'] ?? '')));
                $amt  = (float)($r['amount'] ?? $r['amount_gross'] ?? 0);
                $com  = (float)($r['commission'] ?? $r['amount_commission'] ?? 0);
                $st   = (string)($r['status'] ?? 'pending');
                $dt   = (string)($r['created_at'] ?? '');
                $chip = ($st==='approved' ? 'chip-success' : ($st==='rejected'?'chip-failed' : ($st==='paid'?'chip-success':'chip-pending')));
              ?>
              <tr>
                <td class="col-id"><?= $id ?></td>
                <td><?= htmlspecialchars($name) ?></td>
                <td class="num"><?= $moeda($amt) ?></td>
                <td class="num"><strong><?= $moeda($com) ?></strong></td>
                <td><span class="chip <?= $chip ?>"><?= htmlspecialchars($st) ?></span></td>
                <td><?= htmlspecialchars($dt) ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </section>
</section>

<style>
/* ——— visual admin/glass, igual às outras telas ——— */
.glass-card{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); padding:14px; border-radius:14px; color:#fff; }
.muted{ color:#cfe1ff; opacity:.88; }
.sect-title{ font-weight:800; color:#fff; }
.sect-sub{ font-weight:800; color:#fff; }

.card-head{ padding:16px 18px; }
.head-row{ display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }

/* KPIs */
.kpis{ display:grid; gap:12px; grid-template-columns: repeat(4, minmax(0,1fr)); }
@media (max-width: 1100px){ .kpis{ grid-template-columns:1fr 1fr; } }
@media (max-width: 560px){ .kpis{ grid-template-columns:1fr; } }
.kpi{ display:grid; gap:6px; }
.kpi-top{ font-size:.9rem; color:#cfe1ff; opacity:.9; }
.kpi-num{ font-size:1.6rem; font-weight:800; }
.kpi-foot{ font-size:.9rem; }

/* Tabela glass */
.table-glass{ border:1px solid rgba(255,255,255,.14); border-radius:14px; overflow:hidden; box-shadow: inset 0 1px 0 rgba(255,255,255,.06); }
.aff-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:820px; color:#fff; background: rgba(255,255,255,.04); }
.aff-table thead th{
  position:sticky; top:0;
  background: linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,.10));
  color:#fff; font-weight:800;
  padding:12px 14px; border-bottom:1px solid rgba(255,255,255,.14);
}
.aff-table td{ padding:12px 14px; border-bottom:1px solid rgba(255,255,255,.10); }
.aff-table .num{ text-align:right; } .aff-table .col-id{ width:56px; }
.aff-table tbody tr{ background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.03)); }
.aff-table tbody tr + tr td{ border-top:1px solid rgba(255,255,255,.06); }

/* Chips de status (mesmas classes usadas no resto do admin) */
.chip{ display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:999px; font-size:.85rem; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.10); color:#fff; }
.chip-success{ background:rgba(86,207,139,.20); border-color:rgba(86,207,139,.35); }
.chip-failed{  background:rgba(255,77,79,.18);  border-color:rgba(255,255,255,.35); }
.chip-pending{ background:rgba(255,255,255,.10); border-color:rgba(255,255,255,.25); }

/* ====== Saques (apenas novas regras, sem alterar as existentes) ====== */
.payout-form .row-3{ display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:12px; }
.payout-form .input-wrap{ display:flex; flex-direction:column; gap:6px; }
.payout-form .field{ width:100%; padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.10); color:#fff; }
.payout-form .actions{ margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.flash{ display:none; margin-top:10px; padding:10px 12px; border-radius:12px; font-weight:600; }
.flash.is-ok{ background:rgba(86,207,139,.18); border:1px solid rgba(86,207,139,.35); color:#eaffef; }
.flash.is-err{ background:rgba(255,77,79,.18);  border:1px solid rgba(255,77,79,.35); color:#ffecec; }
@media (max-width: 860px){
  .payout-form .row-3{ grid-template-columns: 1fr; }
}

/* ====== MOBILE TWEAKS (<= 720px) — tabelas viram "cards" ====== */
@media (max-width: 720px){
  .glass-card{ padding:12px; }
  .sect-head small{ width:100%; margin-top:4px; }

  .payout-form .actions{ justify-content:stretch; }
  .payout-form .actions .btn{ flex:1; }

  /* Tabela -> Card */
  .table-glass{ border:0; border-radius:0; overflow:visible; box-shadow:none; }
  .aff-table{ min-width:0; background: transparent; }
  .aff-table thead{ display:none; }
  .aff-table, .aff-table tbody, .aff-table tr, .aff-table td{ display:block; width:100%; }
  .aff-table tbody tr{
    background: rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.14);
    border-radius:12px;
    padding:10px 12px;
    margin:10px 0;
  }
  .aff-table td{
    border:0;
    padding:6px 0;
    display:flex; align-items:flex-start; justify-content:space-between; gap:10px;
  }
  .aff-table td.num{ text-align:left; }
  .aff-table .col-id{ width:auto; }

  /* Rótulos por coluna — HISTÓRICO DE SAQUES (tbody #payouts-body tem 5 colunas) */
  #payouts-body td:nth-child(1)::before{ content:"#"; font-weight:800; color:#dfeaff; }
  #payouts-body td:nth-child(2)::before{ content:"Data"; font-weight:800; color:#dfeaff; }
  #payouts-body td:nth-child(3)::before{ content:"Valor"; font-weight:800; color:#dfeaff; }
  #payouts-body td:nth-child(4)::before{ content:"Status"; font-weight:800; color:#dfeaff; }
  #payouts-body td:nth-child(5)::before{ content:"Chave PIX"; font-weight:800; color:#dfeaff; }

  /* Rótulos por coluna — CONVERSÕES (qualquer tbody que não seja #payouts-body; 6 colunas) */
  .aff-table tbody:not(#payouts-body) td:nth-child(1)::before{ content:"#"; font-weight:800; color:#dfeaff; }
  .aff-table tbody:not(#payouts-body) td:nth-child(2)::before{ content:"Indicado"; font-weight:800; color:#dfeaff; }
  .aff-table tbody:not(#payouts-body) td:nth-child(3)::before{ content:"Valor"; font-weight:800; color:#dfeaff; }
  .aff-table tbody:not(#payouts-body) td:nth-child(4)::before{ content:"Comissão"; font-weight:800; color:#dfeaff; }
  .aff-table tbody:not(#payouts-body) td:nth-child(5)::before{ content:"Status"; font-weight:800; color:#dfeaff; }
  .aff-table tbody:not(#payouts-body) td:nth-child(6)::before{ content:"Criado"; font-weight:800; color:#dfeaff; }
}
</style>

<script>
(function(){
  const moneyBR = v => (new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'})).format(v||0);
  const qs = s => document.querySelector(s);

  // ====== Histórico de saques ======
  async function loadPayouts(){
    const tbody = qs('#payouts-body');
    if(!tbody) return;
    try{
      const r = await fetch('/?r=api/affiliate/payout/mine',{cache:'no-store'});
      const j = await r.json();
      const items = Array.isArray(j?.items) ? j.items : [];
      const rows = items.map(p=>{
        const dt = p.created_at ? new Date(String(p.created_at).replace(' ','T')) : null;
        return `
          <tr>
            <td class="col-id">#${p.id ?? '-'}</td>
            <td>${dt? dt.toLocaleString('pt-BR'): '-'}</td>
            <td class="num">${moneyBR(p.amount || 0)}</td>
            <td>${statusChip(p.status)}</td>
            <td><span class="muted">${String(p.pix_type||'-').toUpperCase()}</span>${p.pix_key ? ' • ' + escapeHtml(String(p.pix_key)) : ''}</td>
          </tr>`;
      }).join('');
      tbody.innerHTML = rows || `<tr><td colspan="5" class="muted">Nenhum saque solicitado ainda.</td></tr>`;
    }catch(e){
      tbody.innerHTML = `<tr><td colspan="5" class="muted">Falha ao carregar histórico.</td></tr>`;
    }
  }

  // ====== Solicitação de saque ======
  qs('#payout-form')?.addEventListener('submit', async ()=>{
    const flash  = qs('#payout-flash');
    const btn    = qs('#payout-submit');
    const $amt   = qs('#payout-amount');
    const amount = parseFloat(($amt.value||'').replace(',','.'));
    const min    = parseFloat($amt.getAttribute('min')||'0');
    const max    = parseFloat($amt.getAttribute('max')||'0');
    const pixType= (qs('#payout-pixtype').value||'').trim();
    const pixKey = (qs('#payout-pixkey').value||'').trim();

    if(isNaN(amount) || amount<=0){ return flashMsg(flash,'Informe um valor válido.','err'); }
    if(amount < min){               return flashMsg(flash,`Valor abaixo do mínimo (${moneyBR(min)}).`,'err'); }
    if(amount > max){               return flashMsg(flash,`Valor maior que o disponível (${moneyBR(max)}).`,'err'); }
    if(!pixType || !pixKey){        return flashMsg(flash,'Informe o tipo e a chave PIX.','err'); }

    btn.disabled = true;
    try{
      const body = new URLSearchParams({amount:String(amount), pix_type:pixType, pix_key:pixKey});
      const r = await fetch('/?r=api/affiliate/payout/request',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body
      });
      const j = await r.json();
      if(!r.ok || j?.error){
        const code = String(j?.error||'').toLowerCase();
        const msg = ({
          'payouts_unavailable': 'Saques indisponíveis no momento.',
          'invalid_amount':      'Valor inválido.',
          'below_minimum':       'Valor abaixo do mínimo permitido.',
          'insufficient_balance':'Saldo insuficiente para saque.'
        })[code] || 'Não foi possível solicitar o saque.';
        throw new Error(msg);
      }
      flashMsg(flash,'Solicitação enviada com sucesso! 🎉','ok');
      qs('#payout-form').reset();
      loadPayouts();
    }catch(e){
      flashMsg(flash, e.message || 'Falha ao solicitar o saque.','err');
    }finally{
      btn.disabled = false;
    }
  });

  function statusChip(s){
    const map = {
      'requested':'chip chip-pending',
      'approved':'chip chip-success',
      'paid':'chip chip-success',
      'rejected':'chip chip-failed'
    };
    const cls = map[String(s||'').toLowerCase()] || 'chip';
    const txt = ({
      'requested':'Pendente',
      'approved':'Aprovado',
      'paid':'Pago',
      'rejected':'Rejeitado'
    })[String(s||'').toLowerCase()] || '—';
    return `<span class="${cls}"><span style="width:8px;height:8px;border-radius:50%;background:currentColor;display:inline-block;opacity:.7"></span>${txt}</span>`;
  }

  function flashMsg(node, msg, type){
    if(!node) return;
    node.className = 'flash ' + (type==='ok'?'is-ok':'is-err');
    node.textContent = msg;
    node.style.display = 'block';
    clearTimeout(node._t);
    node._t = setTimeout(()=> node.style.display='none', 6000);
  }
  function escapeHtml(s){ return (s||'').replace(/[&<>"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

  // inicia
  loadPayouts();
})();
</script>
