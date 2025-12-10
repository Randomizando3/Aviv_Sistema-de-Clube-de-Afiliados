<?php
declare(strict_types=1);

namespace App\services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
  private array $cfg;

  public function __construct() {
    $this->cfg = require dirname(__DIR__) . '/config/mail.php';
  }

  public function send(string $subject, string $html, string $altText = '', ?string $replyName = null, ?string $replyEmail = null): void {
    $m = new PHPMailer(true);
    try {
      $m->CharSet  = 'UTF-8';
      $m->isSMTP();
      $m->Host       = $this->cfg['host'];
      $m->Port       = $this->cfg['port'];
      $m->SMTPAuth   = true;
      $m->Username   = $this->cfg['username'];
      $m->Password   = $this->cfg['password'];
      if (($this->cfg['secure'] ?? 'tls') === 'ssl') {
        $m->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      } else {
        $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      }

      $m->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
      $m->addAddress($this->cfg['to_email']);
      if ($replyEmail) {
        $m->addReplyTo($replyEmail, $replyName ?: $replyEmail);
      }

      $m->isHTML(true);
      $m->Subject = $subject;
      $m->Body    = $html;
      $m->AltBody = $altText ?: strip_tags($html);

      $m->send();
    } catch (Exception $e) {
      throw new \RuntimeException('Falha no envio SMTP: ' . $e->getMessage(), 500);
    }
  }
}
