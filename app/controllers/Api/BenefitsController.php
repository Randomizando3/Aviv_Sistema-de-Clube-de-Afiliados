<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';

class BenefitsController
{
  private function currentDb(PDO $pdo): string {
    return (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
  }
  private function tableExists(PDO $pdo, string $db, string $t): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$db,$t]);
    return (int)$q->fetchColumn() > 0;
  }
  private function norm(string $s): string { return mb_strtolower(trim($s)); }

  /** Flex: interpreta valores de “ativo” (1, '1', true, 'active', 'ativo', 'true'). */
  private function isActiveFlexible($v): bool {
    if ($v === null) return true;
    if (is_bool($v)) return $v;
    if (is_numeric($v)) return ((int)$v) === 1;
    $s = $this->norm((string)$v);
    return in_array($s, ['1','active','ativo','true','yes','sim'], true);
  }

  /** Retorna tokens normalizados do plano do usuário (id e nome, em minúsculas) */
  private function getUserPlanTokens(PDO $pdo, ?int $userId): array {
    if (!$userId) return ['plan_id'=>null, 'tokens'=>[]];

    $st = $pdo->prepare("
      SELECT plan_id FROM subscriptions
      WHERE user_id=? AND status IN ('ativa','active')
      ORDER BY id DESC LIMIT 1
    ");
    $st->execute([$userId]);
    $raw = trim((string)$st->fetchColumn());
    if ($raw === '') return ['plan_id'=>null, 'tokens'=>[]];

    $q = $pdo->prepare("SELECT id, name FROM plans WHERE LOWER(id)=LOWER(?) OR LOWER(name)=LOWER(?) LIMIT 1");
    $q->execute([$raw, $raw]);
    $row = $q->fetch(PDO::FETCH_ASSOC);

    $canonId = $row['id']   ?? $raw;
    $name    = $row['name'] ?? null;

    $tokens = array_values(array_unique(array_filter([
      $this->norm($raw),
      $this->norm($canonId),
      $name ? $this->norm($name) : null,
    ])));

    return ['plan_id'=>$canonId, 'tokens'=>$tokens, 'plan_name'=>$name ?? $raw];
  }

  /** GET /?r=api/benefits/list (membros) */
  public function index(): void {
    \Auth::requireRole(['member','admin']);
    $me  = \Auth::user();
    $userId = $me['id'] ?? null;

    $pdo = \DB::pdo();

    try {
      $db = $this->currentDb($pdo);
      if (!$this->tableExists($pdo,$db,'benefits')) {
        \Json::ok(['benefits'=>[], 'user_plan_id'=>null, 'specialties'=>[]]); return;
      }

      // 0) Filtros vindos do cliente (o search "q" é feito localmente na UI; aqui filtramos especialidade)
      $filterSpecialty = trim($_GET['specialty'] ?? '');

      // 1) Plano do usuário → tokens (id e nome)
      $planInfo = $this->getUserPlanTokens($pdo, (int)$userId);
      $userPlanId   = $planInfo['plan_id'];
      $userPlanName = $planInfo['plan_name'] ?? null;
      $TOKENS       = $planInfo['tokens']; // minúsculas

      // 2) Buscar benefícios válidos
      $today = date('Y-m-d');

      // Descobre colunas existentes para montar SELECT de forma resiliente
      $cols = $pdo->query("
        SELECT LOWER(COLUMN_NAME)
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'benefits'
      ")->fetchAll(PDO::FETCH_COLUMN) ?: [];

      $hasActive    = in_array('active',    $cols, true);
      $hasSpecialty = in_array('specialty', $cols, true);

      $activeCol    = $hasActive    ? ', active'    : '';
      $specialtyCol = $hasSpecialty ? ', specialty' : '';

      $sql = "SELECT id,title,partner,type,code,link,valid_until,description,image_url,created_at{$activeCol}{$specialtyCol}
              FROM benefits
              WHERE (valid_until IS NULL OR DATE(valid_until) >= :today)
              ORDER BY id DESC";
      $st  = $pdo->prepare($sql);
      $st->execute(['today'=>$today]);

      $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
      if (!$rows) { \Json::ok(['benefits'=>[], 'user_plan_id'=>$userPlanId, 'user_plan_name'=>$userPlanName, 'specialties'=>[]]); return; }

      // 3) Filtra ativos de forma flexível
      $rows = array_values(array_filter($rows, function($r) use ($hasActive) {
        return $hasActive ? $this->isActiveFlexible($r['active'] ?? null) : true;
      }));
      if (!$rows) { \Json::ok(['benefits'=>[], 'user_plan_id'=>$userPlanId, 'user_plan_name'=>$userPlanName, 'specialties'=>[]]); return; }

      // 4) Pivot planos
      $ids = implode(',', array_map('intval', array_column($rows,'id')));
      $bp  = $ids ? ($pdo->query("SELECT benefit_id, plan_id FROM benefit_plans WHERE benefit_id IN ($ids)")
                     ->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];

      $benefitPlans = [];
      $allPlanIds   = [];
      foreach ($bp as $r) {
        $bid = (int)$r['benefit_id'];
        $pid = (string)$r['plan_id'];
        $benefitPlans[$bid][] = $pid;
        $allPlanIds[] = $pid;
      }

      // 5) Nomes de planos (para dar match por nome também)
      $planNamesMap = [];
      if ($allPlanIds) {
        $in = implode(',', array_fill(0, count($allPlanIds), '?'));
        $q  = $pdo->prepare("SELECT id,name FROM plans WHERE id IN ($in)");
        $q->execute($allPlanIds);
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
          $planNamesMap[$r['id']] = $r['name'] ?? null;
        }
      }

      // 6) Normaliza + filtra por plano do usuário
      $filteredByPlan = [];
      foreach ($rows as $r) {
        $bid = (int)$r['id'];
        $allowed = array_values(array_filter(array_map('strval', $benefitPlans[$bid] ?? [])));
        if (empty($allowed)) continue; // sem vinculação de planos → não exibe

        $allowedLower = array_map([$this,'norm'], $allowed);
        $allowedNameLower = [];
        foreach ($allowed as $pid) {
          $nm = $planNamesMap[$pid] ?? null;
          if ($nm) $allowedNameLower[] = $this->norm($nm);
        }
        $allowedTokens = array_values(array_unique(array_merge($allowedLower, $allowedNameLower)));

        $match = false;
        foreach ($planInfo['tokens'] as $tk) { if (in_array($tk, $allowedTokens, true)) { $match = true; break; } }
        if (!$match) continue;

        $filteredByPlan[] = $r;
      }

      // 7) Lista de especialidades possíveis (após filtro por plano)
      $specs = [];
      if ($hasSpecialty && $filteredByPlan) {
        $specs = array_values(array_filter(array_unique(array_map(function($r){
          return trim((string)($r['specialty'] ?? ''));
        }, $filteredByPlan))));
        sort($specs, SORT_NATURAL | SORT_FLAG_CASE);
      }

      // 8) Aplica filtro por especialidade (se houver)
      if ($hasSpecialty && $filterSpecialty !== '') {
        $needle = $this->norm($filterSpecialty);
        $filteredByPlan = array_values(array_filter($filteredByPlan, function($r) use ($needle){
          return $this->norm((string)($r['specialty'] ?? '')) === $needle;
        }));
      }

      // 9) Monta payload final
      $out = [];
      foreach ($filteredByPlan as $r) {
        // badge simples por heurística
        $badge = '';
        $txt = (string)($r['description'] ?? '');
        if (preg_match('/(\d{1,3}\s*%)/u', $txt, $m))        $badge = $m[1];
        elseif (preg_match('/R\$\s*\d+[.,]?\d*/u', $txt,$m)) $badge = $m[0];
        elseif (stripos($txt,'meia')!==false)                $badge = 'MEIA';

        $out[] = [
          'id'          => (int)$r['id'],
          'title'       => (string)($r['title'] ?? ''),
          'partner'     => (string)($r['partner'] ?? ''),
          'specialty'   => (string)($r['specialty'] ?? ''), // <- novo campo exposto à UI
          'description' => (string)($r['description'] ?? ''),
          'image_url'   => (string)($r['image_url'] ?? ''),
          'link'        => (string)($r['link'] ?? ''),
          'type'        => (string)($r['type'] ?? 'coupon'),
          'code'        => (string)($r['code'] ?? ''),
          'valid_until' => $r['valid_until'] ?? null,
          'badge'       => $badge,
          'created_at'  => $r['created_at'] ?? null,
        ];
      }

      \Json::ok([
        'benefits'       => $out,
        'user_plan_id'   => $userPlanId,
        'user_plan_name' => $userPlanName,
        'specialties'    => $specs,
      ]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }
}
