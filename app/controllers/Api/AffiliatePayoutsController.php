<?php
namespace Api;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';
require_once __DIR__ . '/../../services/Affiliate.php';

final class AffiliatePayoutsController
{
    private function mustLogin(): array
    {
        \Auth::start();
        $u = \Auth::user();
        if (!$u) \Json::fail('unauthorized', 401);
        return $u;
    }

    // GET /?r=api/affiliate/payout/mine
    public function mine(): void
    {
        $u = $this->mustLogin();
        $items = \App\services\Affiliate::listPayoutsForUser((int)$u['id']);
        \Json::ok(['items'=>$items]);
    }

    // POST /?r=api/affiliate/payout/request
    // body: amount, pix_type?, pix_key?
    public function request(): void
    {
        $u = $this->mustLogin();

        $amount  = (float)($_POST['amount'] ?? 0);
        $pixType = $_POST['pix_type'] ?? null; // cpf|cnpj|email|phone|evp
        $pixKey  = $_POST['pix_key']  ?? null;

        if ($amount <= 0) \Json::fail('invalid_amount', 422);

        try {
            $id = \App\services\Affiliate::requestPayout((int)$u['id'], $amount, $pixType, $pixKey);
            \Json::ok(['id'=>$id]);
        } catch (\Throwable $e) {
            \Json::fail($e->getMessage(), 422);
        }
    }
}
