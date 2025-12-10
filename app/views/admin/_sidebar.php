<?php
$r = $_GET['r'] ?? '';
$active = fn($slug) => $r === $slug ? 'is-active' : '';
?>
<aside class="glass-card" style="margin-bottom:12px">
  <nav class="member-nav" style="display:grid;gap:6px">
    <a class="<?= $active('admin/dashboard') ?>"   href="/?r=admin/dashboard">📊 Dashboard</a>
    <a class="<?= $active('admin/usuarios') ?>"    href="/?r=admin/usuarios">👥 Usuários</a>
    <a class="<?= $active('admin/planos') ?>"      href="/?r=admin/planos">📦 Planos</a>
    <a class="<?= $active('admin/beneficios') ?>"  href="/?r=admin/beneficios">🏷️ Benefícios</a>
    <a class="<?= $active('admin/assinaturas') ?>" href="/?r=admin/assinaturas">🧾 Assinaturas</a>
    <a class="<?= $active('admin/config') ?>"      href="/?r=admin/config">⚙️ Config</a>
  </nav>
</aside>
