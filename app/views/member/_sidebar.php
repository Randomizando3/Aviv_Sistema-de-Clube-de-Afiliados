<?php
$r = $_GET['r'] ?? '';
$active = function(string $slug) use ($r) {
  return $r === $slug ? 'is-active' : '';
};
?>
<aside class="member-aside glass-card" aria-label="Menu do associado">
  <nav class="member-nav">
    <a href="/?r=member/dashboard" class="<?= $active('member/dashboard') ?>">🏠 Dashboard</a>
    <a href="/?r=member/beneficios" class="<?= $active('member/beneficios') ?>">🏷️ Benefícios</a>
    <a href="/?r=member/planos" class="<?= $active('member/planos') ?>">📦 Meu plano</a>
    <a href="/?r=member/faturas" class="<?= $active('member/faturas') ?>">🧾 Faturas</a>
    <a href="/?r=member/perfil" class="<?= $active('member/perfil') ?>">👤 Perfil</a>
    <a href="/?r=affiliate/dashboard" class="<?= $active('affiliate/dashboard') ?>">🔗 Afiliado</a>
    <button class="btn btn-sm btn--ghost" id="btn-logout" type="button" style="margin-top:8px">Sair</button>
  </nav>
</aside>

<script>
document.getElementById('btn-logout')?.addEventListener('click', async ()=>{
  try{ await fetch('/?r=api/auth/logout', { method:'POST' }); }catch(_){}
  location.href='/?r=site/home';
});
</script>
