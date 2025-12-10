<?php
namespace Api;

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';
require_once __DIR__ . '/../../services/Affiliate.php';

use App\services\Affiliate;

class AffiliateController
{
    private function baseUrl(): string
    {
        $https =
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443)
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        $scheme = $https ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        return rtrim("$scheme://$host", '/');
    }

    // Pública (sem login) – usada como fallback pela view
    public function publicSettings(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'data' => [
                'percent'     => Affiliate::percent(),
                'min_payout'  => Affiliate::minPayout(),
                'cookie_days' => Affiliate::cookieDays(),
            ]
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    // API autenticada: stats + lista (tempo real)
    public function overviewApi(): void
    {
        \Auth::start();
        $me = \Auth::user();
        if (!$me) { \Json::fail('unauthorized', 401); }

        $uid   = (int)$me['id'];
        $stats = Affiliate::statsForAffiliate($uid);
        $list  = Affiliate::listCommissions($uid, 30);

        \Json::ok(['data' => ['stats' => $stats, 'list' => $list]]);
    }

    // Views
    public function dashboard(): void
    {
        \Auth::start();
        $me = \Auth::user();
        if (!$me) { header('Location: /?r=auth/login'); exit; }

        $uid  = (int)$me['id'];
        $code = Affiliate::getOrCreateCode($uid);
        $link = $this->baseUrl() . '/?ref=' . $code;

        // render inicial (JS vai atualizar em tempo real)
        $stats = Affiliate::statsForAffiliate($uid);
        $list  = Affiliate::listCommissions($uid, 30);

        include __DIR__ . '/../../views/affiliate/dashboard.php';
    }

    public function links(): void
    {
        \Auth::start();
        $me = \Auth::user();
        if (!$me) { header('Location: /?r=auth/login'); exit; }

        $uid  = (int)$me['id'];
        $code = Affiliate::getOrCreateCode($uid);
        $link = $this->baseUrl() . '/?ref=' . $code;

        include __DIR__ . '/../../views/affiliate/links.php';
    }

    public function ganhos(): void
    {
        \Auth::start();
        $me = \Auth::user();
        if (!$me) { header('Location: /?r=auth/login'); exit; }

        $uid   = (int)$me['id'];
        $stats = Affiliate::statsForAffiliate($uid);
        $list  = Affiliate::listCommissions($uid, 100);

        include __DIR__ . '/../../views/affiliate/ganhos.php';
    }
}
