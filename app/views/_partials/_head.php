
<?php $isBare = !empty($PAGE_BARE); ?><?php
// app/views/_partials/_head.php
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($page_title ?? 'Aviv+') ?></title>
  <meta name="description" content="Clube de assinaturas com benefícios, cupons e carteirinha digital." />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/style.css" />
  <script src="/assets/js/ads-client.js" defer></script>
</head>
