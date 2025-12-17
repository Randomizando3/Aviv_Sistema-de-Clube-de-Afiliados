<?php
// app/views/_partials/_head.php

// Detecta modo "bare" (páginas sem header/footer do tema base)
$isBare = !empty($PAGE_BARE);

// Força UTF-8 sem causar warning (só se ainda não enviou nada)
if (!headers_sent()) {
  header('Content-Type: text/html; charset=UTF-8');
}
ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) { @mb_internal_encoding('UTF-8'); }
if (function_exists('mb_http_output')) { @mb_http_output('UTF-8'); }
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($page_title ?? 'Aviv+', ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($page_desc ?? 'Clube de assinaturas com benefícios, cupons e carteirinha digital.', ENT_QUOTES, 'UTF-8') ?>" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/style.css" />
  <script src="/assets/js/ads-client.js" defer></script>
</head>
