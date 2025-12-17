<?php
// file: config/mail.php
declare(strict_types=1);

return [
  // Leia de env quando existir; usa seus defaults se não houver.
  'host'       => getenv('SMTP_HOST') ?: 'mail.avivmais.com.br',
  'port'       => (int) (getenv('SMTP_PORT') ?: 587),            // 587 STARTTLS (recomendado) | 465 SSL
  'secure'     => getenv('SMTP_SECURE') ?: 'tls',                // 'tls' (STARTTLS) ou 'ssl'
  'username'   => getenv('SMTP_USER') ?: 'aviv@avivmais.com.br',
  'password'   => getenv('SMTP_PASS') ?: 'H34ts33k3r!',          // senha (SMTP_PASS)
  'from_email' => getenv('SMTP_FROM') ?: 'aviv@avivmais.com.br',
  'from_name'  => getenv('SMTP_FROM_NAME') ?: 'Aviv+ Website',

  // Destino "geral" (se você usar em outras rotas/serviços)
  'to_email'   => getenv('MAIL_TO') ?: 'aviv@avivmais.com.br',

  // NOVO: destino específico para formulários do site (FormController)
  // Use no .env como: EMAIL_FORM_TORECEIVE=contato@avivmais.com.br
  'form_to_email' => getenv('EMAIL_FORM_TORECEIVE') ?: 'contato@avivmais.com.br',
];
