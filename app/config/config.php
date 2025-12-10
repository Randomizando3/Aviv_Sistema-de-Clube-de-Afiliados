<?php
// carrega .env simples
$envFile = dirname(__DIR__, 2) . '/.env';
if (is_file($envFile)) {
  foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $_ENV[$k] = $v;
    putenv("$k=$v");
  }
}
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'aviv');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// === ASAAS (sandbox) ===
if (!defined('ASAAS_BASE')) define('ASAAS_BASE', 'https://api-sandbox.asaas.com/v3'); // recomendo esse host
if (!defined('ASAAS_API_KEY')) define('ASAAS_API_KEY', '$aact_hmlg_000MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OmI2NTRkYzg5LTVhMTgtNDRjMi05NmI3LWEzZDU1YzE2ZGM4Njo6JGFhY2hfNmFlYzg0MzYtNmU0Yy00ZTIxLTk0MWQtM2M0NmVkMjkyNjQ2');
if (!defined('ASAAS_WEBHOOK_SECRET')) define('ASAAS_WEBHOOK_SECRET', 'sist2016');
