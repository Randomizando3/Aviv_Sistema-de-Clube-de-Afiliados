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

<section class="affiliate-page">
  <div class="affiliate-inner container">
    <section class="admin-main">

      <!-- CABEÇALHO -->
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

      <!-- KPIs -->
      <div class="kpis">
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

      <!-- LINK / CÓDIGO -->
      <div class="glass-card">
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

      <!-- TABELA DE CONVERSÕES -->
      <div class="glass-card">
        <div class="sect-head">
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
  </div>
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
    try{
      var el = document.querySelector(sel);
      if(!el) return;
      el.select();
      el.setSelectionRange(0,99999);
      document.execCommand('copy');
    } catch(e){
      try{
        navigator.clipboard && navigator.clipboard.writeText(document.querySelector(sel).value);
      }catch(_){}
    }
  }
  document.addEventListener('click', function(ev){
    var btn = ev.target.closest('[data-copy]');
    if(!btn) return;
    ev.preventDefault();
    copySelector(btn.getAttribute('data-copy'));
    var old = btn.textContent;
    btn.textContent = 'Copiado!';
    setTimeout(()=>btn.textContent = old, 900);
  });
})();
</script>

<style>
/* Shell geral, alinhado ao layout clean dos planos */
.affiliate-page{
  width:100%;
  padding:24px 0 48px;
}
.affiliate-inner{
  width:min(92vw, 1120px);
  margin-inline:auto;
}
.affiliate-page .admin-main{
  display:flex;
  flex-direction:column;
  gap:16px;
}

/* Cards brancos com sombra suave (mesmo mood do plans/login) */
.affiliate-page .glass-card{
  background:#ffffff;
  border-radius:22px;
  border:1px solid #e2e8f0;
  box-shadow:
    0 22px 60px rgba(15,23,42,.10),
    0 0 0 1px rgba(148,163,184,.06);
  padding:18px 20px;
}
.affiliate-page .card-head{
  padding:18px 20px;
}

/* Títulos / cabeçalho */
.affiliate-page .sect-title{
  font-family:"Poppins", system-ui, -apple-system, "Segoe UI", sans-serif;
  font-weight:800;
  font-size:1.3rem;
  color:#0f172a;
}
.affiliate-page .sect-sub{
  font-family:"Poppins", system-ui, -apple-system, "Segoe UI", sans-serif;
  font-weight:700;
  font-size:1rem;
  color:#0f172a;
}
.affiliate-page .muted{
  color:#64748b;
}

/* KPIs em grid */
.affiliate-page .kpis{
  display:grid;
  grid-template-columns:repeat(4, minmax(0,1fr));
  gap:12px;
}
.affiliate-page .kpi{
  padding:14px 16px;
}
.affiliate-page .kpi-top{
  font-size:.78rem;
  letter-spacing:.06em;
  text-transform:uppercase;
  color:#64748b;
  font-weight:700;
}
.affiliate-page .kpi-num{
  margin-top:4px;
  font-size:1.4rem;
  font-weight:800;
  color:#0f172a;
}
.affiliate-page .kpi-foot{
  margin-top:4px;
  font-size:.8rem;
}

/* Grid do código/link */
.affiliate-page .ref-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0,1fr));
  gap:12px;
}
.affiliate-page .ref-item{
  display:grid;
  gap:6px;
}
.affiliate-page .ref-label{
  font-size:.82rem;
  font-weight:800;
  color:#475569;
}
.affiliate-page .ref-input{
  display:flex;
  gap:10px;
}
.affiliate-page .ref-input input{
  flex:1;
  border-radius:999px;
  border:1px solid #e2e8f0;
  background:#f8fafc;
  color:#0f172a;
  padding:10px 14px;
  font-weight:600;
  font-size:.9rem;
}
.affiliate-page .ref-input input:read-only{
  cursor:default;
}

/* Botões pequenos (copiar / ver tudo) no estilo clean */
.affiliate-page .btn.btn-sm{
  border-radius:999px;
  font-size:.82rem;
  font-weight:700;
  padding:8px 12px;
}
.affiliate-page .btn.btn-sm:not(.btn--ghost){
  background:#0f172a;
  border:1px solid #0f172a;
  color:#f9fafb;
}
.affiliate-page .btn.btn-sm.btn--ghost{
  background:#ffffff;
  border:1px solid #e2e8f0;
  color:#0f172a;
}

/* Cabeçalho da seção da tabela */
.affiliate-page .sect-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  flex-wrap:wrap;
  margin-bottom:8px;
}

/* Tabela clean */
.affiliate-page .table-wrap{
  width:100%;
  overflow:auto;
}
.affiliate-page .table-glass{
  border-radius:18px;
  border:1px solid #e2e8f0;
  background:#ffffff;
  box-shadow:0 12px 32px rgba(15,23,42,.08);
}
.affiliate-page .aff-table{
  width:100%;
  border-collapse:collapse;
  min-width:720px;
}
.affiliate-page .aff-table thead th{
  font-size:.8rem;
  text-transform:uppercase;
  letter-spacing:.06em;
  font-weight:700;
  padding:10px 12px;
  text-align:left;
  color:#64748b;
  background:#f9fafb;
  border-bottom:1px solid #e2e8f0;
}
.affiliate-page .aff-table td{
  padding:10px 12px;
  font-size:.9rem;
  color:#0f172a;
  border-bottom:1px solid #edf2f7;
}
.affiliate-page .aff-table tr:last-child td{
  border-bottom:none;
}
.affiliate-page .aff-table .num{
  text-align:right;
}
.affiliate-page .aff-table .col-id{
  width:56px;
}

/* Chips de status adaptadas para o fundo claro */
.affiliate-page .chip{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:4px 10px;
  border-radius:999px;
  font-size:.78rem;
  font-weight:700;
}
.affiliate-page .chip-success{
  background:rgba(22,163,74,.10);
  color:#166534;
}
.affiliate-page .chip-pending{
  background:rgba(234,179,8,.12);
  color:#854d0e;
}
.affiliate-page .chip-failed{
  background:rgba(239,68,68,.12);
  color:#b91c1c;
}

/* Responsivo */
@media (max-width:1024px){
  .affiliate-page .kpis{
    grid-template-columns:repeat(2, minmax(0,1fr));
  }
}
@media (max-width:768px){
  .affiliate-page .kpis{
    grid-template-columns:1fr;
  }
  .affiliate-page .ref-grid{
    grid-template-columns:1fr;
  }
}
</style>
