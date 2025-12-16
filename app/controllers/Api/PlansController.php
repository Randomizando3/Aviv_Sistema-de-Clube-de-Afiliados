<?php
namespace Api;

use PDO;

require_once __DIR__ . '/../../core/DB.php';
require_once __DIR__ . '/../../core/Json.php';

class PlansController
{
  private function currentDb(PDO $pdo): string {
    return (string)$pdo->query("SELECT DATABASE()")->fetchColumn();
  }

  private function tableExists(PDO $pdo, string $db, string $table): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$db, $table]);
    return (int)$q->fetchColumn() > 0;
  }

  private function cols(PDO $pdo, string $table): array {
    $db = $this->currentDb($pdo);
    $st = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $st->execute([$db, $table]);
    return array_map(fn($r) => $r['COLUMN_NAME'], $st->fetchAll(PDO::FETCH_ASSOC));
  }

  /** GET /?r=api/plans/list */
  public function index(): void {
    $pdo = \DB::pdo();

    try {
      $db = $this->currentDb($pdo);
      if (!$this->tableExists($pdo, $db, 'plans')) {
        \Json::ok(['plans' => []]); return;
      }

      $cols   = $this->cols($pdo, 'plans');

      // mapeamentos tolerantes
      $cId    = in_array('id', $cols) ? 'id' : (in_array('slug', $cols) ? 'slug' : 'id');
      $cName  = in_array('name', $cols) ? 'name' : (in_array('title', $cols) ? 'title' : $cId);

      $cPM    = in_array('price_monthly', $cols) ? 'price_monthly'
               : (in_array('price', $cols) ? 'price'
               : (in_array('monthly_price', $cols) ? 'monthly_price' : null));

      $cPY    = in_array('price_yearly',  $cols) ? 'price_yearly'
               : (in_array('annual_price', $cols) ? 'annual_price'
               : (in_array('yearly_price', $cols) ? 'yearly_price' : null));

      $cStatus= in_array('status', $cols) ? 'status'
               : (in_array('active', $cols) ? 'active'
               : (in_array('is_active', $cols) ? 'is_active' : null));

      $cSort  = in_array('sort_order', $cols) ? 'sort_order'
               : (in_array('sort', $cols) ? 'sort'
               : (in_array('ordem', $cols) ? 'ordem' : $cName));

      $cDesc  = in_array('description', $cols) ? 'description' : null;
      $cFeat  = in_array('features', $cols) ? 'features' : null;

      // familiar (se existir)
      $cIsFam = in_array('is_family', $cols) ? 'is_family' : null;
      $cMinU  = in_array('min_users', $cols) ? 'min_users' : null;
      $cMaxU  = in_array('max_users', $cols) ? 'max_users' : null;
      $cAddM  = in_array('add_user_monthly', $cols) ? 'add_user_monthly' : null;
      $cAddY  = in_array('add_user_yearly',  $cols) ? 'add_user_yearly'  : null;

      // SELECT
      $select = "$cId AS id, $cName AS name";
      if ($cPM)   $select .= ", $cPM AS price_monthly";
      if ($cPY)   $select .= ", $cPY AS price_yearly";
      if ($cDesc) $select .= ", $cDesc AS description";
      if ($cFeat) $select .= ", $cFeat AS _features_raw";

      if ($cIsFam) $select .= ", $cIsFam AS is_family";
      if ($cMinU)  $select .= ", $cMinU  AS min_users";
      if ($cMaxU)  $select .= ", $cMaxU  AS max_users";
      if ($cAddM)  $select .= ", $cAddM  AS add_user_monthly";
      if ($cAddY)  $select .= ", $cAddY  AS add_user_yearly";

      $sql = "SELECT $select FROM plans";
      $where = [];
      if ($cStatus) {
        if ($cStatus === 'status') {
          $where[] = "status='active'";
        } else {
          $where[] = "$cStatus=1";
        }
      }
      if ($where) $sql .= " WHERE " . implode(' AND ', $where);
      $sql .= " ORDER BY $cSort ASC";

      $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];

      // normalização pós-query
      foreach ($rows as &$r) {
        $r['price_monthly'] = isset($r['price_monthly']) ? (float)$r['price_monthly'] : 0.0;
        $r['price_yearly']  = isset($r['price_yearly'])  ? (float)$r['price_yearly']  : 0.0;

        // familiar defaults
        $r['is_family'] = isset($r['is_family']) ? (int)$r['is_family'] : 0;
        $r['min_users'] = isset($r['min_users']) ? (int)$r['min_users'] : 1;
        $r['max_users'] = isset($r['max_users']) ? (int)$r['max_users'] : 0;
        $r['add_user_monthly'] = isset($r['add_user_monthly']) ? (float)$r['add_user_monthly'] : 0.0;
        $r['add_user_yearly']  = isset($r['add_user_yearly'])  ? (float)$r['add_user_yearly']  : 0.0;

        if ($r['min_users'] < 1) $r['min_users'] = 1;
        if ($r['max_users'] < 0) $r['max_users'] = 0;
        if ($r['add_user_monthly'] < 0) $r['add_user_monthly'] = 0;
        if ($r['add_user_yearly'] < 0) $r['add_user_yearly'] = 0;

        // se não for familiar, normaliza para “individual”
        if ((int)$r['is_family'] !== 1) {
          $r['min_users'] = 1;
          $r['max_users'] = 0;
          $r['add_user_monthly'] = 0.0;
          $r['add_user_yearly']  = 0.0;
        } else {
          if ($r['min_users'] < 2) $r['min_users'] = 2;
          if ($r['max_users'] > 0 && $r['max_users'] < $r['min_users']) $r['max_users'] = $r['min_users'];
        }

        $desc = isset($r['description']) ? (string)$r['description'] : '';

        // features preferem a coluna 'features'; se não existir, derivam da 'description'
        $features = [];
        if (isset($r['_features_raw']) && $r['_features_raw'] !== '' && $r['_features_raw'] !== null) {
          $raw = $r['_features_raw'];
          $decoded = json_decode($raw, true);
          if (is_array($decoded)) {
            $features = array_map('trim', array_filter($decoded, fn($x)=>$x!=='' && $x!==null));
          } else {
            $features = preg_split('/[\r\n;•]+/u', (string)$raw, -1, PREG_SPLIT_NO_EMPTY);
            $features = array_map('trim', $features);
          }
        } elseif ($desc !== '') {
          $features = preg_split('/[\r\n;•]+/u', $desc, -1, PREG_SPLIT_NO_EMPTY);
          $features = array_map('trim', $features);
        }

        $features = array_values(array_unique(array_filter($features, fn($x)=>$x!=='')));
        $r['features'] = $features;

        if ($desc === '' && !empty($features)) {
          $r['description'] = implode("\n", $features);
        } else {
          $r['description'] = $desc;
        }

        unset($r['_features_raw']);
      }
      unset($r);

      \Json::ok(['plans' => $rows]);
    } catch (\Throwable $e) {
      \Json::fail($e->getMessage(), 500);
    }
  }
}
