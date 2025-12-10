<?php
namespace Api;

final class PartnerController
{
  // POST /?r=api/partner/offer
  public function createOffer(): void
  {
    $me = \Auth::user();
    if (!$me || ($me['role'] ?? 'member') !== 'partner') {
      \Json::fail('Unauthorized', 401);
    }

    $title       = trim($_POST['title'] ?? '');
    $type        = $_POST['type'] ?? 'coupon'; // coupon|link|service
    $specialty   = trim($_POST['specialty'] ?? '');
    $code        = trim($_POST['code'] ?? '');
    $link        = trim($_POST['link'] ?? '');
    $valid_until = $_POST['valid_until'] ?? null;
    $image_url   = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $partnerName = trim($_POST['partner'] ?? '');
    $discount    = trim($_POST['discount'] ?? '');
    $terms       = trim($_POST['terms'] ?? '');

    if ($title === '') {
      \Json::fail('Título é obrigatório');
    }

    $pdo = \DB::pdo();

    // IMPORTANTE: apenas colunas que já existem na sua tabela benefits
    $sql = "INSERT INTO benefits
              (title, partner, type, specialty, code, link, valid_until, image_url, description, url, discount, terms, active)
            VALUES
              (?,?,?,?,?,?,?,?,?,?,?, ?, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $title, $partnerName, $type, $specialty, $code, $link, $valid_until, $image_url, $description,
      null, // url
      $discount,
      $terms
    ]);

    \Json::ok([
      'ok'   => true,
      'data' => ['id' => $pdo->lastInsertId(), 'active' => 0]
    ]);
  }
}
