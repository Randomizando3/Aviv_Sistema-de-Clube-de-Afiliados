<?php
$stats = array_merge([
  'n_regs'       => 0,
  'sum_approved' => 0.0,
  'sum_paid'     => 0.0,
  'balance'      => 0.0,
  'min_payout'   => 50.0,
  'percent'      => 0.0
], is_array($stats ?? null) ? $stats : []);

$list  = is_array($list ?? null) ? $list : [];
$code  = (string)($code ?? '');
$link  = (string)($link ?? '');
?>
<section class="container admin affiliate-page" style="margin-top:18px">
  <section class="admin-main">

    <div class="glass-card card-head">
      <div class="head-row">
        <div>
          <h1 class="sect-title" style="margin:0">Afiliados • Dashboard</h1>
          <p class="muted" style="margin:6px 0 0">
            Indique e ganhe
            <strong><span id="aff-percent"><?= number_format((float)$stats['percent'], 1, ',', '.') ?></span>%</strong>
            nas assinaturas pagas que vierem pelo seu link.
          </p>
        </div>
      </div>
    </div>

    <div class="kpis" style="margin-top:12px;">
      <article class="glass-card kpi">
        <div class="kpi-top">Indicações</div>
        <div class="kpi-num" id="kpi-indicacoes"><?= (int)$stats['n_regs'] ?></div>
      </article>
      <article class="glass-card kpi">
        <div class="kpi-top">Aprovado</div>
        <div class="kpi-num" id="kpi-aprovado">R$ <?= number_format((float)$stats['sum_approved'], 2, ',', '.') ?></div>
      </article>
      <article class="glass-card kpi">
        <div class="kpi-top">Pago</div>
        <div class="kpi-num" id="kpi-pago">R$ <?= number_format((float)$stats['sum_paid'], 2, ',', '.') ?></div>
      </article>
      <article class="glass-card kpi">
        <div class="kpi-top">Saldo</div>
        <div class="kpi-num" id="kpi-saldo">R$ <?= number_format((float)$stats['balance'], 2, ',', '.') ?></div>
        <div class="kpi-foot muted">
          Mín. saque:
          <span id="aff-min">R$ <?= number_format((float)$stats['min_payout'], 2, ',', '.') ?></span>
        </div>
      </article>
    </div>

    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub" style="margin-bottom:8px">Seu link & código</h2>
      <div class="ref-grid">
        <div class="ref-item">
          <label class="ref-label">Código</label>
          <div class="ref-input">
            <input id="aff-code" value="<?= htmlspecialchars($code) ?>" readonly>
            <button class="btn btn-sm" data-copy="#aff-code">Copiar</button>
          </div>
        </div>
        <div class="ref-item">
          <label class="ref-label">Link</label>
          <div class="ref-input">
            <input id="aff-link" value="<?= htmlspecialchars($link) ?>" readonly>
            <button class="btn btn-sm" data-copy="#aff-link">Copiar</button>
          </div>
        </div>
      </div>
    </div>

    <div class="glass-card" style="margin-top:12px">
      <div class="sect-head" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
        <h2 class="sect-sub" style="margin:0">Últimas conversões</h2>
        <a class="btn btn-sm btn--ghost" href="/?r=affiliate/ganhos">Ver tudo</a>
      </div>

      <div class="table-wrap table-glass">
        <table class="aff-table">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th>Indicado</th>
              <th>Status</th>
              <th class="num">Valor</th>
              <th class="num">Comissão</th>
              <th>Criado</th>
            </tr>
          </thead>
          <tbody id="conv-body">
          <?php if ($list): foreach ($list as $row): ?>
            <?php
              $id   = (int)($row['id'] ?? 0);
              $name = (string)($row['member_name'] ?? ($row['member_email'] ?? ''));
              $st   = (string)($row['status'] ?? 'pending');
              $amt  = (float)($row['amount'] ?? $row['amount_gross'] ?? 0);
              $com  = (float)($row['commission'] ?? $row['amount_commission'] ?? 0);
              $dt   = (string)($row['created_at'] ?? '');
              $chip = ($st==='approved' ? 'chip-success' : ($st==='rejected'?'chip-failed' : ($st==='paid'?'chip-success':'chip-pending')));
            ?>
            <tr>
              <td class="col-id"><?= $id ?></td>
              <td><?= htmlspecialchars($name) ?></td>
              <td><span class="chip <?= $chip ?>"><?= htmlspecialchars($st) ?></span></td>
              <td class="num">R$ <?= number_format($amt, 2, ',', '.') ?></td>
              <td class="num"><strong>R$ <?= number_format($com, 2, ',', '.') ?></strong></td>
              <td><?= htmlspecialchars($dt) ?></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="muted" style="padding:12px;">Sem conversões ainda.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</section>

<script>
(function(){
  const fmtBRL = v => {
    const n = Number(v);
    try { return (isFinite(n)?n:0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}); }
    catch(_) { return 'R$ ' + (isFinite(n)?n:0).toFixed(2).replace('.', ','); }
  };
  const fmtPct = v => {
    const n = Number(v);
    return (isFinite(n)?n:0).toLocaleString('pt-BR',{minimumFractionDigits:1, maximumFractionDigits:1});
  };
  const esc = s => String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  const elPct   = document.getElementById('aff-percent');
  const elMin   = document.getElementById('aff-min');
  const elInd   = document.getElementById('kpi-indicacoes');
  const elApr   = document.getElementById('kpi-aprovado');
  const elPago  = document.getElementById('kpi-pago');
  const elSaldo = document.getElementById('kpi-saldo');
  const tbody   = document.getElementById('conv-body');

  function chipClass(st){
    st = String(st||'').toLowerCase();
    if (st === 'approved' || st === 'paid') return 'chip chip-success';
    if (st === 'rejected') return 'chip chip-failed';
    return 'chip chip-pending';
  }

  function renderStats(s){
    if (!s) return;
    if (elPct  && s.percent    != null) elPct.textContent   = fmtPct(s.percent);
    if (elMin  && s.min_payout != null) elMin.textContent   = fmtBRL(s.min_payout);
    if (elInd  && s.n_regs     != null) elInd.textContent   = Number(s.n_regs).toLocaleString('pt-BR');
    if (elApr  && s.sum_approved!=null) elApr.textContent   = fmtBRL(s.sum_approved);
    if (elPago && s.sum_paid   != null) elPago.textContent  = fmtBRL(s.sum_paid);
    if (elSaldo&& s.balance    != null) elSaldo.textContent = fmtBRL(s.balance);
  }

  function renderList(items){
    if (!tbody) return;
    if (!items || !items.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="muted" style="padding:12px;">Sem conversões ainda.</td></tr>';
      return;
    }
    tbody.innerHTML = items.map(r=>{
      const id   = Number(r.id||0);
      const name = (r.member_name || r.member_email || '').toString();
      const st   = (r.status || '').toString();
      const dt   = (r.created_at || '').toString();
      const amt  = Number(r.amount || r.amount_gross || 0);
      const com  = Number(r.commission || r.amount_commission || 0);
      return `
        <tr>
          <td class="col-id">${id}</td>
          <td>${esc(name)}</td>
          <td><span class="${chipClass(st)}">${esc(st)}</span></td>
          <td class="num">${fmtBRL(amt)}</td>
          <td class="num"><strong>${fmtBRL(com)}</strong></td>
          <td>${esc(dt)}</td>
        </tr>`;
    }).join('');
  }

  function fetchOverview(){
    return fetch('/?r=api/affiliate/overview', { cache:'no-store', credentials:'same-origin' })
      .then(r => r.ok ? r.json() : Promise.reject(r.status));
  }
  function fetchSettings(){
    return fetch('/?r=api/affiliate/settings', { cache:'no-store' })
      .then(r => r.ok ? r.json() : Promise.reject(r.status));
  }

  function refresh(){
    fetchOverview()
      .then(j => {
        const data = j && j.data ? j.data : {};
        renderStats(data.stats || {});
        renderList(data.list || []);
        // failsafe: se percent veio 0/indefinido, tenta settings pública
        const pct = data.stats && typeof data.stats.percent !== 'undefined' ? Number(data.stats.percent) : NaN;
        if (!(pct > 0)) {
          return fetchSettings().then(sj => {
            const d = sj && sj.data ? sj.data : {};
            if (typeof d.percent !== 'undefined') elPct.textContent = fmtPct(d.percent);
            if (typeof d.min_payout !== 'undefined') elMin.textContent = fmtBRL(d.min_payout);
          }).catch(()=>{});
        }
      })
      .catch(() => {
        // se overview falhar (ex: sessão), pelo menos atualiza percent/min
        fetchSettings().then(sj => {
          const d = sj && sj.data ? sj.data : {};
          if (typeof d.percent !== 'undefined') elPct.textContent = fmtPct(d.percent);
          if (typeof d.min_payout !== 'undefined') elMin.textContent = fmtBRL(d.min_payout);
        }).catch(()=>{});
      });
  }

  let timer;
  const start = () => { if (timer) clearInterval(timer); refresh(); timer = setInterval(refresh, 8000); };
  document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') refresh(); });
  window.addEventListener('focus', refresh);
  start();

  // copiar
  function copySelector(sel){
    try{ var el=document.querySelector(sel); if(!el) return; el.select(); el.setSelectionRange(0,99999); document.execCommand('copy'); }
    catch(e){ try{ navigator.clipboard && navigator.clipboard.writeText(document.querySelector(sel).value); }catch(_){ } }
  }
  document.addEventListener('click', function(ev){
    var btn=ev.target.closest('[data-copy]'); if(!btn) return;
    ev.preventDefault(); copySelector(btn.getAttribute('data-copy'));
    var old=btn.textContent; btn.textContent='Copiado!'; setTimeout(()=>btn.textContent=old,900);
  });
})();
</script>

<style>
.card-head{ padding:16px 18px; }
.head-row{ display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.ref-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
@media (max-width: 900px){ .ref-grid{ grid-template-columns:1fr; } }
.ref-item{ display:grid; gap:6px; }
.ref-label{ font-weight:800; opacity:.95 }
.ref-input{ display:flex; gap:10px; }
.ref-input input{ flex:1; border-radius:999px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.07); color:#fff; padding:10px 12px; font-weight:800; }
.table-glass{ border:1px solid rgba(255,255,255,.14); border-radius:14px; overflow:hidden; box-shadow: inset 0 1px 0 rgba(255,255,255,.06); }
.aff-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:820px; color:#fff; background: rgba(255,255,255,.04); }
.aff-table thead th{ position:sticky; top:0; background: linear-gradient(180deg, rgba(255,255,255,.18), rgba(255,255,255,.10)); color:#fff; font-weight:800; padding:12px 14px; border-bottom:1px solid rgba(255,255,255,.14); }
.aff-table td{ padding:12px 14px; border-bottom:1px solid rgba(255,255,255,.10); }
.aff-table .num{ text-align:right; }
.aff-table .col-id{ width:56px; }
.aff-table tbody tr{ background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.03)); }
.aff-table tbody tr + tr td{ border-top:1px solid rgba(255,255,255,.06); }
</style>
