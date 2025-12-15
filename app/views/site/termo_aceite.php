<?php
$path = __DIR__ . '/../../storage/termos/termo_v1.txt';
$txt = is_file($path) ? file_get_contents($path) : 'Termo não encontrado.';
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Termo de Aceite – Aviv+</title>
  <style>
    body{font-family:Arial,sans-serif;margin:0;padding:14px;line-height:1.5;background:#fff;color:#111}
    pre{white-space:pre-wrap;margin:0}
  </style>
</head>
<body>
  <pre><?= htmlspecialchars($txt, ENT_QUOTES, 'UTF-8') ?></pre>
</body>
</html>
