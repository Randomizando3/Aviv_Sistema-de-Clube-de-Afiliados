<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Json.php';

class MemberBenefitsController
{
  private function db(PDO $pdo): string {
    return (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
  }
  private function tableExists(PDO $pdo, string $t): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$this->db($pdo), $t]);
    return (int)$q->fetchColumn() > 0;
  }
  private function cols(PDO $pdo, string $t): array {
    $q = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$this->db($pdo), $t]);
    return array_map(fn($r)=>$r['COLUMN_NAME'], $q->fetchAll(PDO::FETCH_ASSOC));
  }

  private function currentUserPlanId(PDO $pdo, int $userId): ?string {
    if (!$this->tableExists($pdo,'subscriptions')) return null;
    $st = $pdo->prepare("SELECT plan_id FROM subscriptions WHERE user_id=? AND status='ativa' ORDER BY id DESC LIMIT 1");
    $st->execute([$userId]);
    $p = $st->fetchColumn();
    return $p ? (string)$p : null;
  }

  /** GET /?r=api/member/benefits/list */
  public function index(): void {
    \Auth::start();
    $me = \Auth::user();
    if (!$me) \Json::fail('unauthorized', 401);

    $pdo = \DB::pdo();

    // tabela obrigatória
    if (!$this->tableExists($pdo,'benefits')) {
      \Json::ok(['benefits'=>[]]); return;
    }

    $cols = $this->cols($pdo,'benefits');

    // mapeamentos tolerantes
    $cId    = 'id';
    $cTitle = in_array('title',$cols)?'title':(in_array('name',$cols)?'name':'title');
    $cPartn = in_array('partner',$cols)?'partner':(in_array('merchant',$cols)?'merchant':null);
    $cDesc  = in_array('description',$cols)?'description':(in_array('details',$cols)?'details':null);
    $cCity  = in_array('city',$cols)?'city':(in_array('cidade',$cols)?'cidade':null);
    $cImg   = in_array('image_url',$cols)?'image_url':(in_array('logo_url',$cols)?'logo_url':null);
    $cType  = in_array('type',$cols)?'type':null;             // 'code' | 'link' | 'service' (ou texto)
    $cCode  = in_array('code',$cols)?'code':null;             // cupom fixo definido no admin
    $cLink  = in_array('link',$cols)?'link':null;             // URL de uso
    $cPlans = in_array('plans',$cols)?'plans':null;           // JSON ou CSV com IDs de plano
    $cBadge = in_array('badge',$cols)?'badge':null;
    $cCat   = in_array('category',$cols)?'category':null;     // pode não existir (muitos DBs removeram)
    $cStart = in_array('starts_at',$cols)?'starts_at':null;
    $cEnd   = in_array('ends_at',$cols)?'ends_at':(in_array('valid_until',$cols)?'valid_until':null);
    $cStat  = in_array('status',$cols)?'status':(in_array('active',$cols)?'active':null);

    // SELECT montado apenas com o que existe
    $sel = ["$cId AS id", "$cTitle AS title"];
    if ($cPartn) $sel[] = "$cPartn AS partner";
    if ($cDesc)  $sel[] = "$cDesc AS description";
    if ($cCity)  $sel[] = "$cCity AS city";
    if ($cImg)   $sel[] = "$cImg AS image_url";
    if ($cType)  $sel[] = "$cType AS type";
    if ($cCode)  $sel[] = "$cCode AS code";
    if ($cLink)  $sel[] = "$cLink AS link";
    if ($cPlans) $sel[] = "$cPlans AS _plans_raw";
    if ($cBadge) $sel[] = "$cBadge AS badge";
    if ($cCat)   $sel[] = "$cCat AS category";
    if ($cStart) $sel[] = "$cStart AS _starts_at";
    if ($cEnd)   $sel[] = "$cEnd AS _ends_at";
    if ($cStat)  $sel[] = "$cStat AS _status";

    $sql = "SELECT ".implode(',', $sel)." FROM benefits";

    // filtros de disponibilidade
    $w = [];
    $today = date('Y-m-d');
    if ($cStat) {
      if ($cStat==='status')       $w[] = "LOWER($cStat) IN ('active','ativo')";
      else                         $w[] = "($cStat=1 OR LOWER($cStat) IN ('active','ativo'))";
    }
    if ($cStart) $w[] = "($cStart IS NULL OR DATE($cStart) <= '$today')";
    if ($cEnd)   $w[] = "($cEnd   IS NULL OR DATE($cEnd)   >= '$today')";
    if ($w) $sql .= " WHERE ".implode(' AND ', $w);

    // ordenação
    $sql .= in_array('created_at',$cols) ? " ORDER BY created_at DESC, id DESC" : " ORDER BY id DESC";

    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Filtragem por plano do usuário
    $userPlan = $this->currentUserPlanId($pdo, (int)$me['id']);
    $out = [];
    foreach ($rows as $r) {
      // decodifica lista de planos aceitos (se existir)
      $allowed = null;
      if (array_key_exists('_plans_raw', $r) && $r['_plans_raw'] !== null && $r['_plans_raw']!=='') {
        $decoded = json_decode((string)$r['_plans_raw'], true);
        if (is_array($decoded)) {
          $allowed = array_values(array_filter(array_map('strval', $decoded)));
        } else {
          $allowed = array_values(array_filter(array_map('trim', preg_split('/[,\s]+/u', (string)$r['_plans_raw']))));
        }
      }
      // regra: se lista existir e não contiver o plano do user, pula
      if (is_array($allowed) && $allowed && $userPlan && !in_array($userPlan, $allowed, true)) continue;

      unset($r['_plans_raw'], $r['_starts_at'], $r['_ends_at'], $r['_status']);
      // defaults de tipo
      if (empty($r['type'])) $r['type'] = $r['code'] ? 'code' : ($r['link'] ? 'link' : 'service');
      $out[] = $r;
    }

    \Json::ok(['benefits' => $out, 'plan' => $userPlan]);
  }
}
