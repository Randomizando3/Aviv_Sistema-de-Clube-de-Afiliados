<?php
declare(strict_types=1);

namespace Api;

require_once __DIR__ . '/../../services/MailerService.php';
use App\services\MailerService;
use Throwable;

class FormController
{
    private function input(): array
    {
        if (!empty($_POST)) return $_POST;

        $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        $raw = file_get_contents('php://input') ?: '';

        if (stripos($ct, 'application/json') !== false) {
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        }

        if ($raw !== '') {
            $arr = [];
            parse_str($raw, $arr);
            if (is_array($arr)) return $arr;
        }
        return [];
    }

    private function sanitize(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private function respond(int $code, array $payload): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function isEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function digits(string $s): string
    {
        return preg_replace('/\D+/', '', $s);
    }

    public function partner(): void
    {
        try {
            if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                $this->respond(405, ['error' => 'Método não permitido']);
            }

            $in = $this->input();

            $nome      = $this->sanitize((string)($in['nome']      ?? ''));
            $empresa   = $this->sanitize((string)($in['empresa']   ?? ''));
            $whats     = $this->sanitize((string)($in['whats']     ?? ''));
            $email     = $this->sanitize((string)($in['email']     ?? ''));
            $categoria = $this->sanitize((string)($in['categoria'] ?? ''));
            $mensagem  = $this->sanitize((string)($in['mensagem']  ?? ''));

            $errors = [];

            if ($nome === '')      $errors['nome']    = 'Informe seu nome.';
            if ($empresa === '')   $errors['empresa'] = 'Informe o nome da empresa.';

            $whatsDigits = $this->digits($whats);
            if ($whatsDigits === '' || strlen($whatsDigits) < 8) {
                $errors['whats'] = 'Informe um WhatsApp válido.';
            }

            if ($email === '' || !$this->isEmail($email)) {
                $errors['email'] = 'Informe um e-mail válido.';
            }

            if ($categoria === '') {
                $errors['categoria'] = 'Selecione uma categoria.';
            }

            if (!empty($errors)) {
                $this->respond(422, ['error' => 'Dados inválidos', 'fields' => $errors]);
            }

            $html = $this->buildPartnerHtml([
                'nome'      => $nome,
                'empresa'   => $empresa,
                'whats'     => $whats,
                'email'     => $email,
                'categoria' => $categoria,
                'mensagem'  => $mensagem,
                'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
                'ua'        => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            $mailer = new MailerService();
            $send = $mailer->send([
                'subject'  => 'Novo cadastro de parceiro — ' . $empresa,
                'to'       => 'marketing@showdeimagem.com.br',
                'html'     => $html,
                'text'     => strip_tags(str_replace(['<br>','<br/>','<br />'], "\n", $html)),
                'reply_to' => [$email => $nome],
            ]);

            if (!($send['ok'] ?? false)) {
                // Loga no container a causa real:
                error_log('[FormController.partner] Falha no envio: ' . (string)($send['message'] ?? 'erro SMTP'));
                $this->respond(500, [
                    'error'   => 'Falha no envio de e-mail',
                    'message' => 'Não foi possível enviar sua solicitação agora. Tente novamente em instantes.'
                ]);
            }

            $this->respond(200, ['ok' => true, 'message' => 'Cadastro enviado com sucesso!']);

        } catch (Throwable $e) {
            error_log('[FormController.partner] Exceção: ' . $e->getMessage());
            $this->respond(500, [
                'error'   => 'internal_error',
                'message' => 'Ocorreu um erro inesperado.'
            ]);
        }
    }


    // dentro de Api\FormController
public function contact(): void
{
    try {
        $in = $this->input();

        $nome     = $this->sanitize((string)($in['nome']     ?? ''));
        $whats    = $this->sanitize((string)($in['whats']    ?? ''));
        $email    = $this->sanitize((string)($in['email']    ?? ''));
        $assunto  = $this->sanitize((string)($in['assunto']  ?? ''));
        $mensagem = $this->sanitize((string)($in['mensagem'] ?? ''));

        $errors = [];

        if ($nome === '')  { $errors['nome'] = 'Informe seu nome.'; }
        $whatsDigits = $this->digits($whats);
        if ($whatsDigits === '' || strlen($whatsDigits) < 8) {
            $errors['whats'] = 'Informe um WhatsApp válido.';
        }
        if ($email === '' || !$this->isEmail($email)) {
            $errors['email'] = 'Informe um e-mail válido.';
        }
        if ($assunto === '') {
            $errors['assunto'] = 'Selecione um assunto.';
        }
        if ($mensagem === '' || mb_strlen($mensagem) < 4) {
            $errors['mensagem'] = 'Escreva sua mensagem.';
        }

        if (!empty($errors)) {
            $this->respond(422, ['error' => 'Dados inválidos', 'fields' => $errors]);
        }

        $html = $this->buildContactHtml([
            'nome'     => $nome,
            'whats'    => $whats,
            'email'    => $email,
            'assunto'  => $assunto,
            'mensagem' => $mensagem,
            'ip'       => $_SERVER['REMOTE_ADDR'] ?? '',
            'ua'       => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);

        $mailer = new \App\services\MailerService();
        $send = $mailer->send([
            'subject'  => 'Contato — ' . ($assunto ?: 'Mensagem do site'),
            'to'       => 'marketing@showdeimagem.com.br',
            'html'     => $html,
            'reply_to' => [$email => $nome],
        ]);

        if (!($send['ok'] ?? false)) {
            error_log('[FormController.contact] Falha no envio: ' . (string)($send['message'] ?? 'SMTP error'));
            $this->respond(500, ['error' => 'Falha no envio de e-mail', 'message' => (string)($send['message'] ?? '')]);
        }

        $this->respond(200, ['ok' => true, 'message' => 'Mensagem enviada com sucesso!']);

    } catch (\Throwable $e) {
        error_log('[FormController.contact] ' . $e->getMessage());
        $this->respond(500, ['error' => 'internal_error', 'message' => 'Ocorreu um erro inesperado.']);
    }
}

private function buildContactHtml(array $d): string
{
    $escape = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $rows = [
        'Nome'        => $d['nome']     ?? '',
        'WhatsApp'    => $d['whats']    ?? '',
        'E-mail'      => $d['email']    ?? '',
        'Assunto'     => $d['assunto']  ?? '',
        'Mensagem'    => $d['mensagem'] ?? '',
        'IP'          => $d['ip']       ?? '',
        'Navegador'   => $d['ua']       ?? '',
        'Recebido em' => date('d/m/Y H:i:s'),
    ];

    $trs = '';
    foreach ($rows as $k => $v) {
        $trs .= '<tr><th style="text-align:left;padding:8px 10px;background:#f7fafc;border:1px solid #e8eef5;">'
            . $escape($k)
            . '</th><td style="padding:8px 10px;border:1px solid #e8eef5;">'
            . nl2br($escape((string)$v))
            . '</td></tr>';
    }

    return '<div style="font:14px/1.5 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial,sans-serif;color:#2C3E50">
  <h2 style="margin:0 0 10px">Nova mensagem de contato</h2>
  <p style="margin:0 0 16px">Formulário público do site.</p>
  <table style="border-collapse:collapse;width:100%;max-width:720px">' . $trs . '</table>
</div>';
}




    private function buildPartnerHtml(array $d): string
    {
        $escape = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $rows = [
            'Nome'        => $d['nome']      ?? '',
            'Empresa'     => $d['empresa']   ?? '',
            'WhatsApp'    => $d['whats']     ?? '',
            'E-mail'      => $d['email']     ?? '',
            'Categoria'   => $d['categoria'] ?? '',
            'Mensagem'    => $d['mensagem']  ?? '',
            'IP'          => $d['ip']        ?? '',
            'Navegador'   => $d['ua']        ?? '',
            'Recebido em' => date('d/m/Y H:i:s'),
        ];

        $trs = '';
        foreach ($rows as $k => $v) {
            $trs .= '<tr><th style="text-align:left;padding:8px 10px;background:#f7fafc;border:1px solid #e8eef5;">'
                  . $escape($k)
                  . '</th><td style="padding:8px 10px;border:1px solid #e8eef5;">'
                  . nl2br($escape((string)$v))
                  . '</td></tr>';
        }

        return '<div style="font:14px/1.5 -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial,sans-serif;color:#2C3E50">
  <h2 style="margin:0 0 10px">Novo cadastro de parceiro</h2>
  <p style="margin:0 0 16px">Confira os dados enviados pelo formulário público.</p>
  <table style="border-collapse:collapse;width:100%;max-width:720px">' . $trs . '</table>
</div>';
    }
}
