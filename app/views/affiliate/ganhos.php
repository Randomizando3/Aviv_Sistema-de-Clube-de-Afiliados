<?php
// Afiliado • Ganhos — layout clean (cards brancos), sem fetch extra de overview.
// Lemos percent/min diretamente do serviço para refletir o BD em tempo real.

$stats = array_merge([
  'n_approved'     => 0,
  'n_pending'      => 0,
  'n_rejected'     => 0,
  'sum_commission' => 0.0,
  'available'      => 0.0,
  'locked'         => 0.0,
  'min_payout'     => null,
  'percent'        => null,
], is_array($stats ?? null) ? $stats : []);

$list = is_array($list ?? null) ? $list : [];

// Puxa do service se não vier preenchido (ou se vier inválido)
$percent = is_numeric($stats['percent']) ? (float)$stats['percent'] : \App\services\Affiliate::percent();
$minPay  = is_numeric($stats['min_payout']) ? (float)$stats['min_payout'] : \App\services\Affiliate::minPayout();

// helpers
$moeda = fn($v) => 'R$ ' . number_format((float)$v, 2, ',', '.');
$pc    = fn($v) => number_format((float)$v, 1, ',', '.') . '%';
?>

<section class="affiliate-gains-page">
  <div class="affiliate-gains-inner container admin affiliate-page">
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
      <div class="kpis">
        <article class="glass-card kpi">
          <div class="kpi-top">Aprovadas</div>
          <div class="kpi-num"><?= (int)$stats['n_approved'] ?></div>
          <div class="kpi-foot muted">
            Pendentes: <?= (int)$stats['n_pending'] ?> • Rejeitadas: <?= (int)$stats['n_rejected'] ?>
          </div>
        </article>

        <article class="glass-card kpi">
          <div class="kpi-top">Comissão aprovada</div>
          <div class="kpi-num"><?= $moeda($stats['sum_commission']) ?></div>
        </article>

        <article class="glass-card kpi">
          <div class="kpi-top">Disponível p/ saque</div>
          <div class="kpi-num"><?= $moeda($stats['available']) ?></div>
          <div class="kpi-foot muted">
            Mín. saque: <strong><?= $moeda($minPay) ?></strong>
          </div>
        </article>

        <article class="glass-card kpi">
          <div class="kpi-top">Em processamento</div>
          <div class="kpi-num"><?= $moeda($stats['locked']) ?></div>
          <div class="kpi-foot muted">
            % atual: <strong><?= $pc($percent) ?></strong>
          </div>
        </article>
      </div>

      <!-- ===== Saques: Solicitar + Histórico ===== -->
      <div class="glass-card">
        <div class="sect-head">
          <h2 class="sect-sub" style="margin:0">Solicitar saque</h2>
          <small class="muted">
            Mínimo: <strong><?= $moeda($minPay) ?></strong> • Disponível: <strong><?= $moeda($stats['available']) ?></strong>
          </small>
        </div>

        <form id="payout-form" onsubmit="return false;" class="payout-form">
          <div class="row-3">
            <div class="input-wrap">
              <label>Valor do saque</label>
              <input
                id="payout-amount"
                class="field"
                type="number"
                step="0.01"
                min="<?= number_format((float)$minPay, 2, '.', '') ?>"
                max="<?= number_format((float)$stats['available'], 2, '.', '') ?>"
                placeholder="Ex.: 100,00"
                required
              >
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
            <button
              id="payout-submit"
              class="btn"
              type="submit"
              <?= $canRequest ? '' : 'disabled' ?>
            >
              Solicitar saque
            </button>
            <?php if (!$canRequest): ?>
              <span class="muted">Você ainda não atingiu o mínimo para sacar.</span>
            <?php endif; ?>
          </div>

          <div id="payout-flash" class="flash" role="status" aria-live="polite"></div>
        </form>
      </div>

      <div class="glass-card">
        <div class="sect-head">
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
      <div class="glass-card">
        <div class="sect-head">
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
                  $chip = ($st==='approved'
                    ? 'chip-success'
                    : ($st==='rejected'
                      ? 'chip-failed'
                      : ($st==='paid' ? 'chip-success' : 'chip-pending')));
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
  </div>
</section>

<style>
/* ===== Shell geral alinhado ao layout clean ===== */
.affiliate-gains-page{
  width:100%;
  padding:24px 0 48px;
}
.affiliate-gains-inner{
  width:min(92vw, 1120px);
  margin-inline:auto;
}
.affiliate-gains-page .admin-main{
  display:flex;
  flex-direction:column;
  gap:16px;
}

/* Cards brancos e tipografia */
.affiliate-gains-page .glass-card{
  background:#ffffff;
  border-radius:22px;
  border:1px solid #e2e8f0;
  box-shadow:
    0 22px 60px rgba(15,23,42,.10),
    0 0 0 1px rgba(148,163,184,.06);
  padding:18px 20px;
  color:#0f172a;
}
.affiliate-gains-page .muted{
  color:#64748b;
}
.affiliate-gains-page .sect-title{
  font-family:"Poppins", system-ui, -apple-system, "Segoe UI", sans-serif;
  font-weight:800;
  font-size:1.3rem;
  color:#0f172a;
}
.affiliate-gains-page .sect-sub{
  font-family:"Poppins", system-ui, -apple-system, "Segoe UI", sans-serif;
  font-weight:700;
  font-size:1rem;
  color:#0f172a;
}
.affiliate-gains-page .card-head{
  padding:18px 20px;
}
.affiliate-gains-page .head-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  flex-wrap:wrap;
}
.affiliate-gains-page .sect-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  flex-wrap:wrap;
}

/* KPIs */
.affiliate-gains-page .kpis{
  display:grid;
  gap:12px;
  grid-template-columns: repeat(4, minmax(0,1fr));
}
@media (max-width: 1100px){
  .affiliate-gains-page .kpis{ grid-template-columns:1fr 1fr; }
}
@media (max-width: 560px){
  .affiliate-gains-page .kpis{ grid-template-columns:1fr; }
}
.affiliate-gains-page .kpi{
  display:grid;
  gap:6px;
}
.affiliate-gains-page .kpi-top{
  font-size:.85rem;
  color:#6b7280;
}
.affiliate-gains-page .kpi-num{
  font-size:1.6rem;
  font-weight:800;
  color:#0f172a;
}
.affiliate-gains-page .kpi-foot{
  font-size:.85rem;
}

/* Botões */
.affiliate-gains-page .btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  padding:9px 14px;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#0f172a;
  color:#f9fafb;
  font-weight:700;
  font-size:.9rem;
  text-decoration:none;
  cursor:pointer;
  transition:.12s ease;
}
.affiliate-gains-page .btn:hover{
  filter:brightness(1.03);
  box-shadow:0 10px 24px rgba(15,23,42,.16);
}
.affiliate-gains-page .btn:disabled{
  opacity:.55;
  cursor:not-allowed;
  box-shadow:none;
}
.affiliate-gains-page .btn--ghost{
  background:#ffffff;
  color:#0f172a;
}
.affiliate-gains-page .actions{
  display:flex;
  align-items:center;
  gap:10px;
  flex-wrap:wrap;
}

/* Tabela clean */
.affiliate-gains-page .table-glass{
  border:1px solid #e2e8f0;
  border-radius:14px;
  overflow:hidden;
  box-shadow: inset 0 1px 0 rgba(148,163,184,.08);
}
.affiliate-gains-page .aff-table{
  width:100%;
  border-collapse:separate;
  border-spacing:0;
  min-width:820px;
  color:#0f172a;
  background:#ffffff;
}
.affiliate-gains-page .aff-table thead th{
  position:sticky;
  top:0;
  background:linear-gradient(180deg,#f8fafc,#e5edf8);
  color:#0f172a;
  font-weight:700;
  font-size:.82rem;
  padding:10px 12px;
  border-bottom:1px solid #e2e8f0;
}
.affiliate-gains-page .aff-table td{
  padding:10px 12px;
  border-bottom:1px solid #e5e7eb;
  font-size:.88rem;
}
.affiliate-gains-page .aff-table .num{
  text-align:right;
}
.affiliate-gains-page .aff-table .col-id{
  width:56px;
}
.affiliate-gains-page .aff-table tbody tr:nth-child(even){
  background:#f9fafb;
}

/* Chips de status */
.affiliate-gains-page .chip{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:4px 9px;
  border-radius:999px;
  font-size:.78rem;
  font-weight:600;
  border:1px solid #e2e8f0;
  background:#f9fafb;
  color:#0f172a;
}
.affiliate-gains-page .chip-success{
  background:rgba(16,185,129,.10);
  border-color:rgba(16,185,129,.35);
  color:#047857;
}
.affiliate-gains-page .chip-failed{
  background:rgba(239,68,68,.10);
  border-color:rgba(239,68,68,.35);
  color:#b91c1c;
}
.affiliate-gains-page .chip-pending{
  background:rgba(234,179,8,.10);
  border-color:rgba(234,179,8,.35);
  color:#92400e;
}

/* Saques: formulário */
.affiliate-gains-page .payout-form .row-3{
  display:grid;
  grid-template-columns: repeat(3, minmax(0,1fr));
  gap:12px;
}
.affiliate-gains-page .payout-form .input-wrap{
  display:flex;
  flex-direction:column;
  gap:6px;
}
.affiliate-gains-page .payout-form label{
  font-size:.82rem;
  font-weight:600;
  color:#4b5563;
}
.affiliate-gains-page .payout-form .field{
  width:100%;
  padding:9px 11px;
  border-radius:12px;
  border:1px solid #d1d5db;
  background:#f9fafb;
  color:#0f172a;
  font-size:.9rem;
  outline:none;
}
.affiliate-gains-page .payout-form .field:focus{
  border-color:#2563eb;
  box-shadow:0 0 0 1px rgba(37,99,235,.18);
}
.affiliate-gains-page .payout-form .flash{
  display:none;
  margin-top:10px;
  padding:10px 12px;
  border-radius:12px;
  font-weight:600;
  font-size:.85rem;
}
.affiliate-gains-page .payout-form .flash.is-ok{
  background:rgba(34,197,94,.08);
  border:1px solid rgba(34,197,94,.45);
  color:#166534;
}
.affiliate-gains-page .payout-form .flash.is-err{
  background:rgba(239,68,68,.08);
  border:1px solid rgba(239,68,68,.45);
  color:#b91c1c;
}

/* Mobile */
@media (max-width: 860px){
  .affiliate-gains-page .payout-form .row-3{
    grid-template-columns:1fr;
  }
}
@media (max-width: 720px){
  .affiliate-gains-page .glass-card{
    padding:14px;
  }
  .affiliate-gains-page .sect-head small{
    width:100%;
    margin-top:4px;
  }
  .affiliate-gains-page .payout-form .actions{
    justify-content:stretch;
  }
  .affiliate-gains-page .payout-form .actions .btn{
    flex:1;
  }

  /* Tabelas viram "cards" */
  .affiliate-gains-page .table-glass{
    border:0;
    border-radius:0;
    overflow:visible;
    box-shadow:none;
  }
  .affiliate-gains-page .aff-table{
    min-width:0;
    background:transparent;
  }
  .affiliate-gains-page .aff-table thead{
    display:none;
  }
  .affiliate-gains-page .aff-table,
  .affiliate-gains-page .aff-table tbody,
  .affiliate-gains-page .aff-table tr,
  .affiliate-gains-page .aff-table td{
    display:block;
    width:100%;
  }
  .affiliate-gains-page .aff-table tbody tr{
    background:#ffffff;
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:10px 12px;
    margin:10px 0;
  }
  .affiliate-gains-page .aff-table td{
    border:0;
    padding:6px 0;
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:10px;
  }
  .affiliate-gains-page .aff-table td.num{
    text-align:left;
  }
  .affiliate-gains-page .aff-table .col-id{
    width:auto;
  }

  /* Rótulos – histórico de saques (#payouts-body) */
  .affiliate-gains-page #payouts-body td:nth-child(1)::before{ content:"#"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page #payouts-body td:nth-child(2)::before{ content:"Data"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page #payouts-body td:nth-child(3)::before{ content:"Valor"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page #payouts-body td:nth-child(4)::before{ content:"Status"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page #payouts-body td:nth-child(5)::before{ content:"Chave PIX"; font-weight:700; color:#6b7280; }

  /* Rótulos – conversões (tbody diferente de #payouts-body) */
  .affiliate-gains-page .aff-table tbody:not(#payouts-body) td:nth-child(1)::before{ content:"#"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page .aff-table tbody:not(#payouts-body) td:nth-child(2)::before{ content:"Indicado"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page .aff-table tbody:not(#payouts-body) td:nth-child(3)::before{ content:"Valor"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page .aff-table tbody:not(#payouts-body) td:nth-child(4)::before{ content:"Comissão"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page .aff-table tbody:not(#payouts-body) td:nth-child(5)::before{ content:"Status"; font-weight:700; color:#6b7280; }
  .affiliate-gains-page .aff-table tbody:not(#payouts-body) td:nth-child(6)::before{ content:"Criado"; font-weight:700; color:#6b7280; }
}
</style>

<script>
(function(){
  const moneyBR = v => (new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'})).format(v||0);
  const qs = s => document.querySelector(s);

  // ===== Histórico de saques =====
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

  // ===== Solicitação de saque =====
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
      flashMsg(flash,'Solicitação enviada com sucesso!','ok');
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

  function escapeHtml(s){
    return (s||'').replace(/[&<>"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
  }

  // inicia
  loadPayouts();
})();
</script>
