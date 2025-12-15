<?php
declare(strict_types=1);

namespace Api;

use Throwable;
use App\services\MailerService;

require_once BASE_PATH . '/app/core/DB.php';
require_once BASE_PATH . '/app/core/Auth.php';
require_once BASE_PATH . '/app/core/Json.php';

class TermsController
{
    private function termsFilePath(): string
    {
        return BASE_PATH . '/storage/termos/termo_v1.txt';
    }

    private function termMeta(): array
    {
        return [
            'version' => 'v1',
            'title'   => 'Termo de Aceite Digital Aviv+',
        ];
    }

    public function text(): void
    {
        $file = $this->termsFilePath();

        if (!is_file($file)) {
            http_response_code(404);
            echo "Termo não encontrado em: {$file}\n";
            return;
        }

        $txt = (string)file_get_contents($file);
        if (trim($txt) === '') {
            http_response_code(500);
            echo "Termo está vazio.\n";
            return;
        }

        http_response_code(200);
        echo $txt;
    }

    public function accept(): void
    {
        $u = \Auth::user();
        if (!$u || empty($u['id'])) \Json::fail('unauthorized', 401);

        $planId = trim((string)($_POST['plan_id'] ?? ''));
        $cycle  = trim((string)($_POST['cycle'] ?? ''));
        $name   = trim((string)($_POST['signed_name'] ?? ''));
        $doc    = trim((string)($_POST['signed_doc'] ?? ''));
        $sigPng = trim((string)($_POST['signature_png'] ?? ''));

        if ($planId === '') \Json::fail('plan_id obrigatório', 422);
        if ($cycle === '')  $cycle = 'monthly';
        if ($name === '' || mb_strlen($name) < 3) \Json::fail('Nome inválido', 422);

        if ($sigPng === '' || !str_starts_with($sigPng, 'data:image/png;base64,')) {
            \Json::fail('Assinatura inválida', 422);
        }

        $file = $this->termsFilePath();
        if (!is_file($file)) \Json::fail('Termo não encontrado no servidor', 500);

        $termText = (string)file_get_contents($file);
        if (trim($termText) === '') \Json::fail('Termo vazio no servidor', 500);

        $meta = $this->termMeta();
        $termHash = hash('sha256', $termText);

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $id = null;

        try {
            $pdo = \DB::pdo();

            // evita duplicar aceite do mesmo termo/usuário/plano
            $st = $pdo->prepare('SELECT id FROM term_acceptances WHERE user_id=? AND plan_id=? AND term_hash=? LIMIT 1');
            $st->execute([(int)$u['id'], $planId, $termHash]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);

            if ($row && !empty($row['id'])) {
                $id = (int)$row['id'];
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO term_acceptances
                    (user_id, plan_id, term_version, term_title, term_text, term_hash, signed_name, signed_doc, signature_png, ip_address, user_agent, created_at)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
                );

                $ins->execute([
                    (int)$u['id'],
                    $planId,
                    (string)$meta['version'],
                    (string)$meta['title'],
                    $termText,
                    $termHash,
                    $name,
                    ($doc !== '' ? $doc : null),
                    $sigPng,
                    $ip,
                    $ua,
                ]);

                $id = (int)$pdo->lastInsertId();
            }

        } catch (Throwable $e) {
            \Json::fail('Erro ao registrar aceite: ' . $e->getMessage(), 500);
        }

        // ===== E-mail (igual padrão do contato: usa config e SMTP)
        $mailDetails = [
            'user'  => ['sent' => false, 'error' => null],
            'admin' => ['sent' => false, 'error' => null],
        ];

        try {
            $mailer = new MailerService();

            $uEmail = (string)($u['email'] ?? '');
            $uName  = (string)($u['name'] ?? $name);

            $html =
                '<p>Olá, <strong>' . htmlspecialchars($uName, ENT_QUOTES, 'UTF-8') . '</strong>.</p>' .
                '<p>Registramos o seu aceite do Termo para prosseguir com a assinatura.</p>' .
                '<ul>' .
                '<li>Plano: <strong>' . htmlspecialchars($planId, ENT_QUOTES, 'UTF-8') . '</strong></li>' .
                '<li>Ciclo: <strong>' . htmlspecialchars($cycle, ENT_QUOTES, 'UTF-8') . '</strong></li>' .
                '<li>Versão: <strong>' . htmlspecialchars($meta['version'], ENT_QUOTES, 'UTF-8') . '</strong></li>' .
                '<li>Hash: <code>' . htmlspecialchars($termHash, ENT_QUOTES, 'UTF-8') . '</code></li>' .
                '<li>Data: ' . date('d/m/Y H:i:s') . '</li>' .
                '</ul>';

            $text = "Aceite registrado.\nPlano: {$planId}\nCiclo: {$cycle}\nVersão: {$meta['version']}\nHash: {$termHash}\n";

            // 1) Envia para o usuário (se tiver e-mail válido)
            if ($uEmail && filter_var($uEmail, FILTER_VALIDATE_EMAIL)) {
                $resUser = $mailer->send([
                    'to'      => [$uEmail => $uName],
                    'subject' => 'Aceite do Termo - Aviv+',
                    'html'    => $html,
                    'text'    => $text,
                ]);
                $mailDetails['user']['sent'] = (bool)($resUser['ok'] ?? false);
                if (!$mailDetails['user']['sent']) {
                    $mailDetails['user']['error'] = (string)($resUser['message'] ?? 'Falha desconhecida');
                    error_log('[TERMS_MAIL_USER] ' . $mailDetails['user']['error']);
                }
            }

            // 2) Envia para o e-mail administrativo do sistema (se existir config/mail.php com to_email)
            $cfgPath = BASE_PATH . '/app/config/mail.php';
            if (is_file($cfgPath)) {
                $cfg = require $cfgPath;
                $adminEmail = (string)($cfg['to_email'] ?? '');
                if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                    $resAdmin = $mailer->send([
                        'to'      => [$adminEmail => 'AVIV PLUS'],
                        'subject' => 'Novo aceite de termo (Admin) - Aviv+',
                        'html'    => $html,
                        'text'    => $text,
                    ]);
                    $mailDetails['admin']['sent'] = (bool)($resAdmin['ok'] ?? false);
                    if (!$mailDetails['admin']['sent']) {
                        $mailDetails['admin']['error'] = (string)($resAdmin['message'] ?? 'Falha desconhecida');
                        error_log('[TERMS_MAIL_ADMIN] ' . $mailDetails['admin']['error']);
                    }
                }
            }

        } catch (Throwable $e) {
            error_log('[TERMS_MAIL_FATAL] ' . $e->getMessage());
        }

        \Json::ok([
            'ok' => true,
            'id' => (int)$id,
            'term_hash' => $termHash,
            'term_version' => $meta['version'],
            'cycle' => $cycle,
            'mail' => $mailDetails,
        ]);
    }
}
