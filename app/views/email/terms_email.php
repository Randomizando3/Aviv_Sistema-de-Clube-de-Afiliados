<?php
// views/emails/terms_email.php
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Confirmação Aviv+</title>
</head>
<body style="margin:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111;">
  <div style="max-width:640px;margin:0 auto;padding:24px;">
    <div style="background:#ffffff;border-radius:14px;padding:22px;border:1px solid #e9ecf5;">
      <h2 style="margin:0 0 10px;font-size:20px;">Confirmação de aceite – Aviv+</h2>

      <p style="margin:0 0 12px;line-height:1.45;">
        Olá <strong><?= htmlspecialchars($userName ?? ''); ?></strong>, tudo certo?
      </p>

      <p style="margin:0 0 12px;line-height:1.45;">
        Confirmamos o aceite dos termos e a contratação do plano:
        <strong><?= htmlspecialchars($planName ?? ''); ?></strong>.
      </p>

      <?php if (!empty($subscriptionId)): ?>
        <p style="margin:0 0 12px;line-height:1.45;">
          Referência: <strong><?= htmlspecialchars($subscriptionId); ?></strong>
        </p>
      <?php endif; ?>

      <p style="margin:0 0 14px;line-height:1.45;">
        Data/hora do aceite: <strong><?= htmlspecialchars($acceptedAt ?? ''); ?></strong>
      </p>

      <div style="margin-top:14px;padding:12px 14px;background:#f9fafc;border:1px solid #edf0f7;border-radius:12px;">
        <p style="margin:0;line-height:1.45;">
          Em anexo, você receberá um arquivo PDF contendo os <strong>Termos</strong> e a <strong>assinatura</strong>.
        </p>
      </div>

      <p style="margin:16px 0 0;color:#6b7280;font-size:12px;line-height:1.45;">
        Se você não reconhece esta ação, responda este e-mail ou contate o suporte imediatamente.
      </p>
    </div>

    <p style="margin:12px 0 0;color:#9aa3b2;font-size:12px;text-align:center;">
      Aviv+ • Mensagem automática, não responda para alterar dados cadastrais.
    </p>
  </div>
</body>
</html>
