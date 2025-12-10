<?php
declare(strict_types=1);

namespace App\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Throwable;

class MailerService
{
    private string $host;
    private int    $port;
    private string $secure; // 'tls' (587) | 'ssl' (465)
    private bool   $auth = true;
    private string $user;
    private string $pass;
    private string $from;
    private string $fromName;

    public function __construct(?array $cfg = null)
    {
        // Defaults: Titan por padrão (587/TLS)
        $this->host     = $cfg['host']      ?? (getenv('SMTP_HOST') ?: 'smtp.titan.email');
        $this->port     = (int)($cfg['port']   ?? (getenv('SMTP_PORT') ?: 587));
        $this->secure   = $cfg['secure']    ?? (getenv('SMTP_SECURE') ?: 'tls');
        $this->user     = $cfg['user']      ?? (getenv('SMTP_USER') ?: 'software@showdeimagem.com.br');
        $this->pass     = $cfg['pass']      ?? (getenv('SMTP_PASS') ?: 'software_Show2024');
        $this->from     = $cfg['from']      ?? (getenv('SMTP_FROM') ?: $this->user);
        $this->fromName = $cfg['from_name'] ?? (getenv('SMTP_FROM_NAME') ?: 'AVIV PLUS');
    }

    /**
     * @param array{
     *   to: string|array<string,string>|array<int,string>,
     *   subject?: string,
     *   html?: string,
     *   text?: string,
     *   reply_to?: string|array<string,string>|array<int,string>
     * } $opts
     * @return array{ok:bool,message?:string,code?:int}
     */
    public function send(array $opts): array
    {
        $to      = $opts['to'] ?? null;
        $subject = (string)($opts['subject'] ?? '(sem assunto)');
        $html    = (string)($opts['html'] ?? '');
        $text    = (string)($opts['text'] ?? '');
        $replyTo = $opts['reply_to'] ?? null;

        if (!$to)  return ['ok' => false, 'message' => 'destinatário ausente', 'code' => 0];
        if ($html === '' && $text === '') return ['ok' => false, 'message' => 'corpo ausente', 'code' => 0];

        // 1) Tenta a config pedida (por padrão 587/tls), 2) fallback 465/ssl
        $try1 = $this->attemptSend($to, $subject, $html, $text, $replyTo, strtolower($this->secure), $this->port);
        if ($try1['ok']) return $try1;

        $altSecure = strtolower($this->secure) === 'tls' ? 'ssl' : 'tls';
        $altPort   = strtolower($this->secure) === 'tls' ? 465   : 587;

        $try2 = $this->attemptSend($to, $subject, $html, $text, $replyTo, $altSecure, $altPort);
        return $try2;
    }

    private function attemptSend($to, string $subject, string $html, string $text, $replyTo, string $secure, int $port): array
    {
        $m = new PHPMailer(true);

        try {
            $m->isSMTP();
            $m->Host       = $this->host;
            $m->SMTPAuth   = $this->auth;
            $m->Username   = $this->user;
            $m->Password   = $this->pass;
            $m->CharSet    = 'UTF-8';
            $m->Timeout    = 20;
            $m->SMTPKeepAlive = false;

            // Debug opcional
            if ((string)getenv('MAIL_DEBUG') !== '') {
                $m->SMTPDebug   = SMTP::DEBUG_SERVER;
                $m->Debugoutput = static function (string $str, int $level) {
                    error_log('[SMTP][' . $level . '] ' . $str);
                };
            }

            // TLS relaxado em DEV, se desejar
            if ((string)getenv('MAIL_STRICT_TLS') === '0') {
                $m->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ],
                ];
            }

            if ($secure === 'ssl') {
                $m->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $m->Port       = $port ?: 465;
            } else {
                $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $m->Port       = $port ?: 587;
            }

            // Remetente (mesmo usuário no Titan)
            $m->setFrom($this->from, $this->fromName);

            // Destinatários
            $this->addAddresses($m, $to);

            // Reply-To
            if ($replyTo) $this->addReplyTo($m, $replyTo);

            // Conteúdo
            if ($html !== '') $m->isHTML(true);
            $m->Subject = $subject;
            if ($html !== '') $m->Body    = $html;
            if ($text !== '') $m->AltBody = $text;

            $m->send();
            return ['ok' => true];

        } catch (Throwable $e) {
            // Codifica melhor as mensagens comuns
            $msg = $e->getMessage();
            $code = 0;
            if (preg_match('/\b(535|534|530)\b/', $msg, $m1)) {
                $code = (int)$m1[1];
            }
            return ['ok' => false, 'message' => $msg, 'code' => $code];
        }
    }

    private function addAddresses(PHPMailer $m, $to): void
    {
        if (is_string($to)) { $m->addAddress($to); return; }
        if (is_array($to)) {
            foreach ($to as $k => $v) {
                if (is_string($k))      $m->addAddress($k, (string)$v);
                elseif (is_string($v))  $m->addAddress($v);
            }
        }
    }

    private function addReplyTo(PHPMailer $m, $replyTo): void
    {
        if (is_string($replyTo)) { $m->addReplyTo($replyTo); return; }
        if (is_array($replyTo)) {
            foreach ($replyTo as $k => $v) {
                if (is_string($k))      { $m->addReplyTo($k, (string)$v); break; }
                elseif (is_string($v))  { $m->addReplyTo($v); break; }
            }
        }
    }
}
