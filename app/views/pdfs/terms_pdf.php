<?php
// views/pdfs/terms_pdf.php
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; font-size:12px; }
    .wrap { width:100%; }
    .card { border:1px solid #e5e7eb; border-radius:10px; padding:14px; }
    .title { text-align:center; font-size:18px; font-weight:bold; margin:0 0 10px; }
    .muted { color:#6b7280; }
    .row { margin:6px 0; }
    .hr { height:1px; background:#e5e7eb; margin:12px 0; }
    .center { text-align:center; }
    .sig-box { border:1px dashed #c7cdd9; border-radius:10px; padding:10px; }
    .small { font-size:10px; }
    ol { margin:0; padding-left:18px; }
    li { margin:6px 0; line-height:1.45; }
  </style>
</head>
<body>
  <div class="wrap">

    <div class="title">Termos de Uso e Aceite – Aviv+</div>
    <div class="center muted" style="margin-bottom:10px;">
      Documento gerado em <?= date('d/m/Y H:i:s'); ?>
    </div>

    <div class="card">
      <div class="row"><strong>Nome:</strong> <?= htmlspecialchars($userName ?? ''); ?></div>
      <div class="row"><strong>E-mail:</strong> <?= htmlspecialchars($userEmail ?? ''); ?></div>
      <div class="row"><strong>Plano:</strong> <?= htmlspecialchars($planName ?? ''); ?></div>

      <?php if (!empty($subscriptionId)): ?>
        <div class="row"><strong>Referência:</strong> <?= htmlspecialchars($subscriptionId); ?></div>
      <?php endif; ?>

      <div class="row"><strong>Aceite em:</strong> <?= htmlspecialchars($acceptedAt ?? ''); ?></div>
      <div class="row"><strong>IP:</strong> <?= htmlspecialchars($ip ?? ''); ?></div>

      <div class="hr"></div>

      <div class="row"><strong>Termos</strong></div>
      <ol>
        <li>Ao contratar o Aviv+, o usuário declara ter lido e concordado com as condições de uso, políticas aplicáveis e regras do plano selecionado.</li>
        <li>O serviço poderá sofrer atualizações, melhorias e ajustes técnicos visando melhor desempenho e segurança.</li>
        <li>É responsabilidade do usuário manter seus dados de acesso em sigilo e utilizar a plataforma de forma lícita.</li>
        <li>O Aviv+ poderá suspender contas em caso de violação de regras, tentativas de fraude, uso indevido ou comportamento abusivo.</li>
        <li>Pagamentos, renovações e cancelamentos obedecem aos prazos e condições informados no momento da contratação.</li>
        <li>O usuário autoriza o envio de comunicações transacionais relacionadas à conta (ex.: confirmação, cobrança, segurança).</li>
      </ol>

      <div class="hr"></div>

      <div class="row center"><strong>Assinatura</strong></div>
      <div class="sig-box center">
        <?php if (!empty($signatureDataUri)): ?>
          <img src="<?= $signatureDataUri ?>" style="max-width:220px;max-height:120px;">
        <?php else: ?>
          <div class="muted">Assinatura não informada</div>
        <?php endif; ?>
      </div>

      <div class="row center small muted" style="margin-top:10px;">
        Este documento é um comprovante eletrônico de aceite com dados de referência, data/hora e identificação de origem.
      </div>
    </div>
  </div>
</body>
</html>
