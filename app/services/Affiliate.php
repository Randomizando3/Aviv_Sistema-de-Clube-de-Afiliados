<?php
namespace App\services;

final class Affiliate
{
    private const DEFAULT_PERCENT     = 10.0; // fallback se não achar no BD/arquivo/ENV
    private const DEFAULT_COOKIE_DAYS = 30;
    private const DEFAULT_MIN_PAYOUT  = 50.0;

    /* ===== Infra ===== */
    private static function q(string $sql, array $params = [])
    {
        $st = \DB::pdo()->prepare($sql);
        $st->execute($params);
        return $st;
    }

    private static function hasTable(string $name): bool
    {
        try {
            $st = self::q(
                "SELECT 1 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME=?",
                [$name]
            );
            return (bool)$st->fetchColumn();
        } catch (\Throwable $e) { return false; }
    }

    private static function settingsPath(): string
    {
        return dirname(__DIR__, 2) . '/storage/settings.json';
    }

    /* ==== Settings helpers ==== */
    private static function settingFromDb(string $key): ?string
    {
        try {
            $st = \DB::pdo()->prepare("SELECT v FROM settings WHERE k=? LIMIT 1");
            $st->execute([$key]);
            $val = $st->fetchColumn();
            if ($val === false || $val === null) return null;
            return is_string($val) ? trim($val) : (string)$val;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function settingFromFile(string $key): ?string
    {
        try {
            $p = self::settingsPath();
            if (is_file($p)) {
                $json = json_decode(file_get_contents($p), true);
                if (is_array($json) && array_key_exists($key, $json)) {
                    $v = $json[$key];
                    return $v === '' ? null : (string)$v;
                }
            }
        } catch (\Throwable $e) {}
        return null;
    }

    /* ===== Getters ===== */
    public static function percent(): float
    {
        $v = self::settingFromDb('affiliate.percent');
        if ($v !== null && is_numeric($v)) return max(0.0, (float)$v);

        $v = self::settingFromFile('affiliate.percent');
        if ($v !== null && is_numeric($v)) return max(0.0, (float)$v);

        $env = getenv('AFFILIATE_PERCENT');
        if ($env !== false && is_numeric($env)) return max(0.0, (float)$env);

        return self::DEFAULT_PERCENT;
    }

    public static function cookieDays(): int
    {
        $v = self::settingFromDb('affiliate.cookie_days');
        if ($v !== null && is_numeric($v)) return max(1, (int)$v);

        $v = self::settingFromFile('affiliate.cookie_days');
        if ($v !== null && is_numeric($v)) return max(1, (int)$v);

        $env = getenv('AFFILIATE_COOKIE_DAYS');
        if ($env !== false && is_numeric($env)) return max(1, (int)$env);

        return self::DEFAULT_COOKIE_DAYS;
    }

    public static function minPayout(): float
    {
        $v = self::settingFromDb('affiliate.min_payout');
        if ($v !== null && is_numeric($v)) return max(0.0, (float)$v);

        $v = self::settingFromFile('affiliate.min_payout');
        if ($v !== null && is_numeric($v)) return max(0.0, (float)$v);

        $env = getenv('AFFILIATE_MIN_PAYOUT');
        if ($env !== false && is_numeric($env)) return max(0.0, (float)$env);

        return self::DEFAULT_MIN_PAYOUT;
    }

    /* ===== Setters (Admin) ===== */
    public static function setPercent(float $p): void
    {
        $p = max(0.0, $p);
        $val = (string)$p;

        if (self::hasTable('settings')) {
            self::q(
                "INSERT INTO settings (k,v) VALUES ('affiliate.percent',?)
                 ON DUPLICATE KEY UPDATE v=VALUES(v)",
                [$val]
            );
            return;
        }

        $path = self::settingsPath();
        $data = [];
        if (is_file($path)) {
            $cur = json_decode(@file_get_contents($path), true);
            if (is_array($cur)) $data = $cur;
        }
        $data['affiliate.percent'] = $val;
        @mkdir(dirname($path), 0775, true);
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    }

    public static function setMinPayout(float $v): void
    {
        $v = max(0.0, $v);
        if (self::hasTable('settings')) {
            self::q(
                "INSERT INTO settings (k,v) VALUES ('affiliate.min_payout',?)
                 ON DUPLICATE KEY UPDATE v=VALUES(v)",
                [(string)$v]
            );
            return;
        }
        $path = self::settingsPath();
        $data = [];
        if (is_file($path)) {
            $cur = json_decode(@file_get_contents($path), true);
            if (is_array($cur)) $data = $cur;
        }
        $data['affiliate.min_payout'] = (string)$v;
        @mkdir(dirname($path), 0775, true);
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    }

    public static function setCookieDays(int $days): void
    {
        $days = max(1, $days);
        if (self::hasTable('settings')) {
            self::q(
                "INSERT INTO settings (k,v) VALUES ('affiliate.cookie_days',?)
                 ON DUPLICATE KEY UPDATE v=VALUES(v)",
                [(string)$days]
            );
            return;
        }
        $path = self::settingsPath();
        $data = [];
        if (is_file($path)) {
            $cur = json_decode(@file_get_contents($path), true);
            if (is_array($cur)) $data = $cur;
        }
        $data['affiliate.cookie_days'] = (string)$days;
        @mkdir(dirname($path), 0775, true);
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
    }

    /* ===== Referral ===== */
    public static function captureRefFromQuery(): void
    {
        if (empty($_GET['ref'])) return;
        $code = preg_replace('~[^a-zA-Z0-9_-]~', '', (string)$_GET['ref']);
        if ($code === '') return;

        $row = self::q('SELECT id FROM affiliate_links WHERE code=? LIMIT 1', [$code])->fetch(\PDO::FETCH_ASSOC);
        if (!$row) return;

        $days = self::cookieDays();
        setcookie('aviv_ref', $code, time() + $days * 86400, '/', '', false, true);
        $_COOKIE['aviv_ref'] = $code;

        $ip = $_SERVER['REMOTE_ADDR']     ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        try {
            self::q('INSERT INTO affiliate_clicks (link_id, ip, ua) VALUES (?,?,?)', [(int)$row['id'], $ip, $ua]);
        } catch (\Throwable $e) {}
    }

    public static function getOrCreateCode(int $userId): string
    {
        $r = self::q('SELECT code FROM affiliate_links WHERE user_id=? LIMIT 1', [$userId])->fetch(\PDO::FETCH_ASSOC);
        if ($r && !empty($r['code'])) return $r['code'];

        $base = strtolower(base_convert((string)($userId * 1009), 10, 36));
        $code = 'u' . $base;
        $try  = 0;
        while (true) {
            $exists = self::q('SELECT id FROM affiliate_links WHERE code=? LIMIT 1', [$code])->fetch();
            if (!$exists) break;
            $try++;
            $code = 'u' . $base . $try;
        }
        self::q('INSERT INTO affiliate_links (user_id, code) VALUES (?, ?)', [$userId, $code]);
        return $code;
    }

    public static function attachReferralOnRegister(int $newUserId): void
    {
        $code = $_COOKIE['aviv_ref'] ?? null;
        if (!$code) return;

        $link = self::q('SELECT id, user_id FROM affiliate_links WHERE code=? LIMIT 1', [$code])->fetch(\PDO::FETCH_ASSOC);
        if (!$link) return;
        if ((int)$link['user_id'] === $newUserId) return;

        $already = self::q('SELECT id FROM affiliate_conversions WHERE link_id=? AND user_id=? LIMIT 1',
            [(int)$link['id'], $newUserId])->fetch();
        if ($already) return;

        self::q('INSERT INTO affiliate_conversions (link_id, user_id, amount, commission, status) VALUES (?,?,?,?,?)',
            [(int)$link['id'], $newUserId, 0.00, 0.00, 'pending']);
    }

    public static function onInvoicePaid(array $payload): void
    {
        $userId = self::resolveUserIdFromPayload($payload);
        if (!$userId) return;

        $amount = (float)($payload['amount'] ?? 0);
        if ($amount <= 0) return;

        $conv = self::q('SELECT id FROM affiliate_conversions WHERE user_id=? ORDER BY id DESC LIMIT 1', [$userId])->fetch(\PDO::FETCH_ASSOC);
        if (!$conv) return;

        $percent    = self::percent(); // BD em tempo real
        $commission = round($amount * $percent / 100, 2);
        $subId      = $payload['subscription_id'] ?? null;

        self::q('UPDATE affiliate_conversions
                 SET subscription_id=?, amount=?, commission=?, status=?
                 WHERE id=?',
                [$subId, $amount, $commission, 'approved', (int)$conv['id']]);
    }

    private static function resolveUserIdFromPayload(array $payload): ?int
    {
        if (!empty($payload['member_id'])) return (int)$payload['member_id'];

        if (!empty($payload['subscription_id'])) {
            $r = self::q('SELECT user_id FROM subscriptions WHERE id=? LIMIT 1', [$payload['subscription_id']])->fetch(\PDO::FETCH_ASSOC);
            if ($r && isset($r['user_id'])) return (int)$r['user_id'];
        }

        if (!empty($payload['invoice_id'])) {
            $r = self::q('SELECT s.user_id
                          FROM invoices i
                          JOIN subscriptions s ON s.id=i.subscription_id
                          WHERE i.asaas_invoice_id=? OR i.id=? LIMIT 1',
                         [$payload['invoice_id'], $payload['invoice_id']])->fetch(\PDO::FETCH_ASSOC);
            if ($r && isset($r['user_id'])) return (int)$r['user_id'];
        }
        return null;
    }

    /* ===== KPIs / saldos ===== */
    public static function sumApprovedCommissions(int $affiliateUserId): float
    {
        $r = self::q(
            "SELECT SUM(c.commission) AS s
             FROM affiliate_conversions c
             JOIN affiliate_links l ON l.id=c.link_id
             WHERE l.user_id=? AND c.status='approved'",
            [$affiliateUserId]
        )->fetch(\PDO::FETCH_ASSOC);
        return (float)($r['s'] ?? 0);
    }

    public static function sumPaidPayouts(int $affiliateUserId): float
    {
        if (!self::hasTable('affiliate_payouts')) return 0.0;
        $r = self::q(
            "SELECT SUM(amount) AS s
             FROM affiliate_payouts
             WHERE affiliate_user_id=? AND status='paid'",
            [$affiliateUserId]
        )->fetch(\PDO::FETCH_ASSOC);
        return (float)($r['s'] ?? 0);
    }

    public static function sumLockedPayouts(int $affiliateUserId): float
    {
        if (!self::hasTable('affiliate_payouts')) return 0.0;
        $r = self::q(
            "SELECT SUM(amount) AS s
             FROM affiliate_payouts
             WHERE affiliate_user_id=? AND status IN ('requested','approved','paid')",
            [$affiliateUserId]
        )->fetch(\PDO::FETCH_ASSOC);
        return (float)($r['s'] ?? 0);
    }

    public static function availableForPayout(int $affiliateUserId): float
    {
        $avail = self::sumApprovedCommissions($affiliateUserId) - self::sumLockedPayouts($affiliateUserId);
        return max(0.0, round($avail, 2));
    }

    public static function statsForAffiliate(int $affiliateUserId): array
{
    $row = self::q(
        "SELECT
           COUNT(DISTINCT c.user_id)                               AS n_regs,
           SUM(CASE WHEN c.status='approved' THEN c.amount ELSE 0 END)        AS sum_amount_appr,
           SUM(CASE WHEN c.status='approved' THEN c.commission ELSE 0 END)    AS sum_comm_appr,
           SUM(CASE WHEN c.status='approved' THEN 1 ELSE 0 END)    AS n_approved,
           SUM(CASE WHEN c.status='pending'  THEN 1 ELSE 0 END)    AS n_pending,
           SUM(CASE WHEN c.status='rejected' THEN 1 ELSE 0 END)    AS n_rejected
         FROM affiliate_conversions c
         JOIN affiliate_links l ON l.id=c.link_id
         WHERE l.user_id=?",
        [$affiliateUserId]
    )->fetch(\PDO::FETCH_ASSOC) ?: [];

    $sumApproved = (float)($row['sum_comm_appr'] ?? 0.0);
    $sumPaid     = self::sumPaidPayouts($affiliateUserId);
    $locked      = self::sumLockedPayouts($affiliateUserId);
    $avail       = max(0.0, round($sumApproved - $locked, 2));

    // Lidos do DB em tempo real
    $minPayout = self::minPayout();
    $percent   = self::percent();

    return [
        // KPIs
        'n_regs'         => (int)($row['n_regs'] ?? 0),
        'n_approved'     => (int)($row['n_approved'] ?? 0),
        'n_pending'      => (int)($row['n_pending'] ?? 0),
        'n_rejected'     => (int)($row['n_rejected'] ?? 0),

        // Somas
        'sum_amount'     => (float)($row['sum_amount_appr'] ?? 0),
        'sum_commission' => $sumApproved,

        // Saldos
        'locked'         => $locked,
        'available'      => $avail,
        'can_request'    => $avail >= $minPayout,

        // Configs (sempre do DB)
        'min_payout'     => $minPayout,
        'percent'        => $percent,

        // Aliases usados nas views
        'sum_approved'   => $sumApproved,
        'sum_paid'       => $sumPaid,
        'balance'        => $avail,
    ];
}


    public static function listCommissions(int $affiliateUserId, int $limit = 30): array
    {
        $limit = max(1, (int)$limit);
        $st = self::q(
            "SELECT c.id, c.user_id, c.subscription_id, c.amount, c.commission,
                    c.status, c.created_at,
                    u.name  AS member_name,
                    u.email AS member_email
             FROM affiliate_conversions c
             JOIN affiliate_links l ON l.id=c.link_id
             JOIN users u          ON u.id=c.user_id
             WHERE l.user_id=?
             ORDER BY c.id DESC
             LIMIT $limit",
            [$affiliateUserId]
        );
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    /* ===== Saques ===== */
    public static function requestPayout(int $affiliateUserId, float $amount, ?string $pixType, ?string $pixKey): int
    {
        if (!self::hasTable('affiliate_payouts')) {
            throw new \RuntimeException('payouts_unavailable');
        }
        $amount = round(max(0.0, $amount), 2);
        if ($amount <= 0) throw new \InvalidArgumentException('invalid_amount');

        $available = self::availableForPayout($affiliateUserId);
        $min = self::minPayout();

        if ($amount < $min)        throw new \RuntimeException('below_minimum');
        if ($amount > $available)  throw new \RuntimeException('insufficient_balance');

        self::q(
            "INSERT INTO affiliate_payouts (affiliate_user_id, amount, status, pix_type, pix_key)
             VALUES (?,?,?,?,?)",
            [$affiliateUserId, $amount, 'requested', $pixType, $pixKey]
        );

        return (int)\DB::pdo()->lastInsertId();
    }

    public static function listPayoutsForUser(int $affiliateUserId): array
    {
        if (!self::hasTable('affiliate_payouts')) return [];
        $st = self::q("SELECT * FROM affiliate_payouts WHERE affiliate_user_id=? ORDER BY id DESC", [$affiliateUserId]);
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function listPayoutsAdmin(?string $status=null, int $limit = 200): array
    {
        if (!self::hasTable('affiliate_payouts')) return [];
        $where = ''; $args = [];
        if ($status && in_array($status, ['requested','approved','paid','rejected'], true)) {
            $where = "WHERE p.status=?";
            $args[] = $status;
        }
        $limit = max(1, (int)$limit);
        $st = self::q(
            "SELECT p.*, u.name AS affiliate_name, u.email AS affiliate_email
             FROM affiliate_payouts p
             JOIN users u ON u.id = p.affiliate_user_id
             $where
             ORDER BY p.id DESC
             LIMIT $limit",
            $args
        );
        return $st->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function approvePayout(int $id): void
    {
        if (!self::hasTable('affiliate_payouts')) throw new \RuntimeException('payouts_unavailable');
        self::q("UPDATE affiliate_payouts SET status='approved', processed_at=NOW() WHERE id=? AND status='requested'", [$id]);
    }

    public static function markPayoutPaid(int $id): void
    {
        if (!self::hasTable('affiliate_payouts')) throw new \RuntimeException('payouts_unavailable');
        self::q("UPDATE affiliate_payouts SET status='paid', processed_at=NOW() WHERE id=? AND status IN ('requested','approved')", [$id]);
    }

    public static function rejectPayout(int $id, ?string $reason=null): void
    {
        if (!self::hasTable('affiliate_payouts')) throw new \RuntimeException('payouts_unavailable');
        self::q("UPDATE affiliate_payouts SET status='rejected', notes=?, processed_at=NOW() WHERE id=?", [$reason, $id]);
    }
}
