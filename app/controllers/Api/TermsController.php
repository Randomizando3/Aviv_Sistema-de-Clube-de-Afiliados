<?php
declare(strict_types=1);

namespace Api;

use Throwable;
use App\services\MailerService;

use Dompdf\Dompdf;
use Dompdf\Options;

require_once BASE_PATH . '/app/core/DB.php';
require_once BASE_PATH . '/app/core/Auth.php';
require_once BASE_PATH . '/app/core/Json.php';

class TermsController
{
    private function termsFilePath(): string
    {
        return BASE_PATH . '/storage/termos/termo_v1.txt';
    }

    /**
     * Próximo passo jurídico (já aplicado):
     * storage/termos/aceites/{id}.pdf
     */
    private function acceptDir(): string
    {
        return BASE_PATH . '/storage/termos/aceites';
    }

    private function tmpDir(): string
    {
        return BASE_PATH . '/storage/tmp';
    }

    private function termMeta(): array
    {
        return [
            'version' => 'v1',
            'title'   => 'Termo de Aceite Digital Aviv+',
        ];
    }

    private function baseUrl(): string
    {
        // Preferência: variável de ambiente do seu site (se existir)
        $env = trim((string)(getenv('SITE_URL') ?: ''));
        if ($env !== '') return rtrim($env, '/');

        // Fallback automático
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
        return $scheme . '://' . $host;
    }

    public function text(): void
    {
        $file = $this->termsFilePath();

        header('Content-Type: text/plain; charset=utf-8');

        if (!is_file($file)) {
            http_response_code(404);
            echo "Termo não encontrado.\n";
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

        // Assinatura base64 (sem GD/Imagick)
        $sigPngNormalized = $this->validateSignatureDataUrl($sigPng);

        $file = $this->termsFilePath();
        if (!is_file($file)) \Json::fail('Termo não encontrado no servidor', 500);

        $termText = (string)file_get_contents($file);
        if (trim($termText) === '') \Json::fail('Termo vazio no servidor', 500);

        $meta     = $this->termMeta();
        $termHash = hash('sha256', $termText);

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

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
                    $sigPngNormalized,
                    ($ip !== '' ? $ip : null),
                    ($ua !== '' ? $ua : null),
                ]);

                $id = (int)$pdo->lastInsertId();
            }

        } catch (Throwable $e) {
            \Json::fail('Erro ao registrar aceite: ' . $e->getMessage(), 500);
        }

        // ===== Gera e salva PDF (termo + assinatura no final)
        $pdfOk = false;
        $pdfPath = $this->acceptDir() . '/' . (int)$id . '.pdf';
        $pdfError = null;

        try {
            $this->ensureDir($this->tmpDir());
            $this->ensureDir($this->acceptDir());

            $pdfBytes = $this->buildPdfBytes([
                'meta'       => $meta,
                'termText'   => $termText,
                'termHash'   => $termHash,
                'planId'     => $planId,
                'cycle'      => $cycle,
                'signedName' => $name,
                'signedDoc'  => $doc,
                'signedAt'   => date('d/m/Y H:i:s'),
                'ip'         => $ip,
                'ua'         => $ua,
                'signature'  => $sigPngNormalized,
                'acceptId'   => (int)$id,
            ]);

            if ($pdfBytes !== '' && strlen($pdfBytes) > 4000) {
                file_put_contents($pdfPath, $pdfBytes);
                $pdfOk = is_file($pdfPath) && (int)filesize($pdfPath) > 4000;
            } else {
                $pdfError = 'PDF vazio/pequeno após render';
            }
        } catch (Throwable $e) {
            $pdfError = $e->getMessage();
            error_log('[TERMS_PDF] ' . $pdfError);
        }

        // ===== E-mail (usuário + admin) com anexo do PDF e URL ABSOLUTA
        $mailDetails = [
            'user'  => ['sent' => false, 'error' => null],
            'admin' => ['sent' => false, 'error' => null],
        ];

        $pdfUrlAbs = $this->baseUrl() . '/?r=api/terms/pdf&id=' . (int)$id;

        try {
            $mailer = new MailerService();

            $uEmail = (string)($u['email'] ?? '');
            $uName  = (string)($u['name'] ?? $name);

            $safeUName = htmlspecialchars($uName, ENT_QUOTES, 'UTF-8');

            // HTML COMPLETO (evita quebra de acentos em clientes “chatos”)
            $htmlBody = ''
                . '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Aceite do Termo</title></head>'
                . '<body style="font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#111">'
                . '<p>Ola, <strong>' . $safeUName . '</strong>.</p>'
                . '<p>Registramos o seu aceite do Termo para prosseguir com a assinatura.</p>'
                . '<ul>'
                . '<li>Plano: <strong>' . htmlspecialchars($planId, ENT_QUOTES, 'UTF-8') . '</strong></li>'
                . '<li>Ciclo: <strong>' . htmlspecialchars($cycle, ENT_QUOTES, 'UTF-8') . '</strong></li>'
                . '<li>Versao: <strong>' . htmlspecialchars($meta['version'], ENT_QUOTES, 'UTF-8') . '</strong></li>'
                . '<li>Hash: <code>' . htmlspecialchars($termHash, ENT_QUOTES, 'UTF-8') . '</code></li>'
                . '<li>Data: ' . htmlspecialchars(date('d/m/Y H:i:s'), ENT_QUOTES, 'UTF-8') . '</li>'
                . '</ul>';

            if ($pdfOk) {
                $htmlBody .= '<p><strong>PDF:</strong> anexado e disponivel em: '
                    . '<a href="' . htmlspecialchars($pdfUrlAbs, ENT_QUOTES, 'UTF-8') . '">'
                    . htmlspecialchars($pdfUrlAbs, ENT_QUOTES, 'UTF-8')
                    . '</a></p>';
            } else {
                $htmlBody .= '<p><em>Obs.: não foi possível gerar o PDF nesta tentativa (ver logs).</em></p>';
            }

            $htmlBody .= '</body></html>';

            // Texto simples: mantém compatibilidade máxima (sem acento se quiser 100% universal)
            $text  = "Olá, {$uName}\n\n";
            $text .= "Registramos o seu aceite do Termo para prosseguir com a assinatura.\n\n";
            $text .= "Plano: {$planId}\n";
            $text .= "Ciclo: {$cycle}\n";
            $text .= "Versão: {$meta['version']}\n";
            $text .= "Hash: {$termHash}\n";
            $text .= "Data: " . date('d/m/Y H:i:s') . "\n";
            $text .= $pdfOk
                ? "PDF: anexado e disponível em: {$pdfUrlAbs}\n"
                : "Obs.: não foi possível gerar o PDF nesta tentativa (ver logs).\n";

            $attachments = [];
            if ($pdfOk && is_file($pdfPath)) {
                $attachments[] = [
                    'path' => $pdfPath,
                    'name' => 'termo_assinado_' . (int)$id . '.pdf',
                    'mime' => 'application/pdf',
                ];
            }

            // 1) Usuário
            if ($uEmail && filter_var($uEmail, FILTER_VALIDATE_EMAIL)) {
                $resUser = $mailer->send([
                    'to'          => [$uEmail => $uName],
                    'subject'     => 'Aceite do Termo - Aviv+',
                    'html'        => $htmlBody,
                    'text'        => $text,
                    'attachments' => $attachments,
                ]);

                $mailDetails['user']['sent'] = (bool)($resUser['ok'] ?? false);
                if (!$mailDetails['user']['sent']) {
                    $mailDetails['user']['error'] = (string)($resUser['message'] ?? 'Falha desconhecida');
                    error_log('[TERMS_MAIL_USER] ' . $mailDetails['user']['error']);
                }
            } else {
                $mailDetails['user']['error'] = 'E-mail do usuário inválido/ausente';
            }

            // 2) Admin (mail.php -> to_email)
            $cfgPath = BASE_PATH . '/app/config/mail.php';
            if (is_file($cfgPath)) {
                $cfg = require $cfgPath;
                $adminEmail = (string)($cfg['to_email'] ?? '');
                if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                    $resAdmin = $mailer->send([
                        'to'          => [$adminEmail => 'AVIV PLUS'],
                        'subject'     => 'Novo aceite de termo (Admin) - Aviv+',
                        'html'        => $htmlBody,
                        'text'        => $text,
                        'attachments' => $attachments,
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
            'ok'           => true,
            'id'           => (int)$id,
            'term_hash'    => $termHash,
            'term_version' => $meta['version'],
            'cycle'        => $cycle,
            'pdf'          => [
                'ok'    => $pdfOk,
                'path'  => ($pdfOk ? $pdfPath : null),
                'error' => $pdfError,
                'url'   => '/?r=api/terms/pdf&id=' . (int)$id,
                'url_abs' => $pdfUrlAbs,
            ],
            'mail'         => $mailDetails,
        ]);
    }

    /**
     * Rota: api/terms/pdf&id=123
     * Regra: admin pode ver qualquer; usuário só o próprio.
     */
    public function pdf(): void
    {
        $u = \Auth::user();
        if (!$u || empty($u['id'])) { http_response_code(401); echo "unauthorized"; return; }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { http_response_code(422); echo "id inválido"; return; }

        try {
            $pdo = \DB::pdo();
            $st = $pdo->prepare('SELECT user_id FROM term_acceptances WHERE id=? LIMIT 1');
            $st->execute([$id]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);

            if (!$row) { http_response_code(404); echo "not_found"; return; }

            $ownerId = (int)$row['user_id'];
            $role = strtolower((string)($u['role'] ?? 'member'));
            $isAdmin = ($role === 'admin');

            if (!$isAdmin && (int)$u['id'] !== $ownerId) {
                http_response_code(403);
                echo "forbidden";
                return;
            }
        } catch (Throwable $e) {
            http_response_code(500);
            echo "db_error";
            return;
        }

        $pdfPath = $this->acceptDir() . '/' . $id . '.pdf';
        if (!is_file($pdfPath)) { http_response_code(404); echo "pdf_not_found"; return; }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="termo_assinado_' . $id . '.pdf"');
        header('Content-Length: ' . filesize($pdfPath));
        readfile($pdfPath);
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \RuntimeException("Diretório não gravável: {$dir}");
        }
    }

    /**
     * Sem GD: valida dataURL, limita tamanho e garante PNG.
     * Retorna dataURL normalizada.
     */
    private function validateSignatureDataUrl(string $dataUrl): string
    {
        if ($dataUrl === '' || !str_starts_with($dataUrl, 'data:image/png;base64,')) {
            throw new \RuntimeException('Assinatura inválida');
        }

        $b64 = substr($dataUrl, strlen('data:image/png;base64,'));
        $b64 = preg_replace('~\s+~', '', (string)$b64);

        // Limite para não explodir Dompdf/memória (ajuste se necessário)
        if (strlen($b64) > 350_000) {
            throw new \RuntimeException('Assinatura muito grande. Reduza a resolução do canvas.');
        }

        $bin = base64_decode($b64, true);
        if ($bin === false || $bin === '') {
            throw new \RuntimeException('Assinatura inválida (base64)');
        }

        // Magic bytes do PNG
        if (substr($bin, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            throw new \RuntimeException('Assinatura inválida (não é PNG)');
        }

        return 'data:image/png;base64,' . base64_encode($bin);
    }

    /**
     * @param array{
     *   meta: array{version:string,title:string},
     *   termText: string,
     *   termHash: string,
     *   planId: string,
     *   cycle: string,
     *   signedName: string,
     *   signedDoc: string,
     *   signedAt: string,
     *   ip: string,
     *   ua: string,
     *   signature: string,
     *   acceptId: int
     * } $data
     */
    private function buildPdfBytes(array $data): string
    {
        ini_set('memory_limit', '512M');

        $opt = new Options();
        $opt->setTempDir($this->tmpDir());
        $opt->setChroot(BASE_PATH);
        $opt->set('isRemoteEnabled', false);
        $opt->set('isHtml5ParserEnabled', true);
        $opt->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($opt);

        $termHtml = nl2br(htmlspecialchars($data['termText'], ENT_QUOTES, 'UTF-8'));
        $sig = $data['signature'];

        // Assinatura bem menor (sem GD): controla via CSS (px)
        $sigCssWidthPx = 260; // menor ainda (ajuste se quiser)

        $htmlPdf = '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">'
            . '<style>
                @page { margin: 24px 28px; }
                body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
                h1 { font-size: 16px; margin: 0 0 10px; }
                .meta { margin: 0 0 12px; padding: 10px; background: #f6f7fb; border: 1px solid #e6e8f0; border-radius: 8px; }
                .meta div { margin: 2px 0; }
                .term { border: 1px solid #e6e8f0; padding: 12px; border-radius: 8px; }
                .sigwrap { margin-top: 14px; border-top: 1px dashed #cfd5e6; padding-top: 12px; }
                .sigimg { border: 1px solid #cfd5e6; border-radius: 8px; padding: 8px; display: inline-block; background: #fff; }
                .small { font-size: 10px; color: #444; }
                .hash { font-family: DejaVu Sans, monospace; font-size: 10px; word-break: break-all; }
              </style></head><body>';

        $htmlPdf .= '<h1>' . htmlspecialchars($data['meta']['title'], ENT_QUOTES, 'UTF-8') . '</h1>';

        $htmlPdf .= '<div class="meta">'
            . '<div><strong>ID do Aceite:</strong> ' . (int)$data['acceptId'] . '</div>'
            . '<div><strong>Plano:</strong> ' . htmlspecialchars($data['planId'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div><strong>Ciclo:</strong> ' . htmlspecialchars($data['cycle'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div><strong>Versão:</strong> ' . htmlspecialchars($data['meta']['version'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div><strong>Assinado por:</strong> ' . htmlspecialchars($data['signedName'], ENT_QUOTES, 'UTF-8') . '</div>'
            . ($data['signedDoc'] !== '' ? '<div><strong>Documento:</strong> ' . htmlspecialchars($data['signedDoc'], ENT_QUOTES, 'UTF-8') . '</div>' : '')
            . '<div><strong>Data:</strong> ' . htmlspecialchars($data['signedAt'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div class="small"><strong>IP:</strong> ' . htmlspecialchars($data['ip'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div class="small"><strong>User-Agent:</strong> ' . htmlspecialchars($data['ua'], ENT_QUOTES, 'UTF-8') . '</div>'
            . '<div class="small"><strong>Hash do termo:</strong> <span class="hash">' . htmlspecialchars($data['termHash'], ENT_QUOTES, 'UTF-8') . '</span></div>'
            . '</div>';

        $htmlPdf .= '<div class="term">' . $termHtml . '</div>';

        $htmlPdf .= '<div class="sigwrap">'
            . '<div><strong>Assinatura (capturada no aceite digital):</strong></div>'
            . '<div class="sigimg"><img src="' . $sig . '" style="width:' . (int)$sigCssWidthPx . 'px; max-width:100%; height:auto;"></div>'
            . '<div class="small" style="margin-top:6px">Esta assinatura foi registrada eletronicamente junto ao aceite do termo.</div>'
            . '</div>';

        $htmlPdf .= '</body></html>';

        $dompdf->loadHtml($htmlPdf, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string)$dompdf->output();
    }
}
