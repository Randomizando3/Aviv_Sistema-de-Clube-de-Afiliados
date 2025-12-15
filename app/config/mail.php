<?php
declare(strict_types=1);

return [
  // Leia de env quando existir; usa seus defaults se não houver.
  'host'       => getenv('SMTP_HOST') ?: 'smtp.titan.email',
  'port'       => (int) (getenv('SMTP_PORT') ?: 587),           // 587 TLS (STARTTLS)
  'secure'     => getenv('SMTP_SECURE') ?: 'tls',               // 'tls' ou 'ssl'
  'username'   => getenv('SMTP_USER') ?: 'software@showdeimagem.com.br',
  'password'   => getenv('SMTP_PASS') ?: 'software_Show2024',
  'from_email' => getenv('SMTP_FROM') ?: 'software@showdeimagem.com.br',
  'from_name'  => getenv('SMTP_FROM_NAME') ?: 'Aviv+ Website',
  'to_email'   => getenv('MAIL_TO') ?: 'software@showdeimagem.com.br',
];