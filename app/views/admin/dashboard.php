<?php
// Admin • Dashboard — sem sidebar, largura igual ao Header, KPIs responsivos
?>
<section class="container admin dash-page" style="margin-top:18px">
  <section class="admin-main">
    <div class="glass-card">
      <h1 class="sect-title">Admin • Dashboard</h1>
      <p class="muted">Resumo rápido da operação.</p>
    </div>

    <!-- KPIs -->
    <div class="kpis-grid" style="margin-top:12px">
      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Usuários</div>
        <div class="kpi" id="kpi-users">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Assinaturas ativas</div>
        <div class="kpi" id="kpi-subs">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Planos</div>
        <div class="kpi" id="kpi-plans">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">Benefícios</div>
        <div class="kpi" id="kpi-benefits">—</div>
      </article>

      <article class="glass-card kpi-card">
        <div class="kpi-label muted">MRR (30d)</div>
        <div class="kpi" id="kpi-mrr">R$ —</div>
      </article>
    </div>

    <div id="dash-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
  </section>
</section>

<script>
(async function(){
  const moneyBR = (v)=> 'R$ ' + (Number(v||0)).toFixed(2).replace('.', ',');
  try{
    const r = await fetch('/?r=api/admin/stats/overview');
    let j; try { j = await r.json(); } catch(e){ j = { error:'Erro de resposta' }; }
    if(!r.ok){ throw new Error(j.error || 'Falha ao carregar'); }

    document.getElementById('kpi-users').textContent    = j.users ?? '—';
    document.getElementById('kpi-subs').textContent     = j.active_subs ?? '—';
    document.getElementById('kpi-plans').textContent    = j.plans ?? '—';
    document.getElementById('kpi-benefits').textContent = j.benefits ?? '—';
    document.getElementById('kpi-mrr').textContent      = moneyBR(j.mrr_30d);
  }catch(e){
    const box = document.getElementById('dash-alert');
    box.textContent = 'Erro: ' + (e.message || e);
    box.style.display = 'block';
    setTimeout(()=> box.style.display='none', 2000);
  }
})();
</script>

<style>
/* ===== Largura igual ao Header ===== */
.container.admin{
  width: min(92vw, var(--container)) !important;
  margin-inline: auto;
  padding-inline: 0;
}

/* ===== Cartões/Tipografia base (coerente com as demais telas) ===== */
.glass-card{
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.10);
  padding: 14px;
  border-radius: 14px;
  color: #fff;
}
.sect-title{ margin:0 0 10px; font-weight:800; }
.muted{ opacity:.86; font-size:.9rem; color:#cfe1ff; }
.alert{
  margin-top:12px; padding:10px 12px; border-radius:10px;
  background: rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.18); color:#fff;
}

/* ===== KPIs ===== */
.kpis-grid{
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 12px;
}
.kpi-card{ display:grid; gap:4px; }
.kpi-label{ font-weight:700; }
.kpi{
  font-weight: 800;
  font-size: clamp(1.2rem, 1rem + 1vw, 1.6rem);
  line-height: 1.15;
}

/* Quebras responsivas harmoniosas */
@media (max-width: 1200px){
  .kpis-grid{ grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 820px){
  .kpis-grid{ grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 520px){
  .kpis-grid{ grid-template-columns: 1fr; }
}
</style>
