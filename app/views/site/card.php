<?php
Auth::start();
require_once __DIR__ . '/../../core/DB.php';

$pdo = DB::pdo();

/**
 * Suporte:
 * 1) Titular:    /?r=site/card&code=AVIV-000001
 * 2) Titular AA: /?r=site/card&code=AVIV-000001-AA  (AA reservado = titular)
 * 3) Dependente: /?r=site/card&code=AVIV-000001-AB  (AB=1º dependente, AC=2º, ...)
 * 4) Legado:     /?r=site/card&person=123           (subscription_people.id)
 */
$code   = $_GET['code']   ?? '';
$person = $_GET['person'] ?? null;

$personId = is_numeric($person) ? (int)$person : 0;

function h($v): string {
  return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}

function fmtDateBR($d){
  if(!$d) return '—';
  $s = explode('T',(string)$d)[0];
  if(!$s) return '—';
  [$y,$m,$dd] = array_pad(explode('-',$s),3,null);
  return ($y && $m && $dd) ? sprintf('%02d/%02d/%04d',$dd,$m,$y) : '—';
}
function digitsOnly($s){ return preg_replace('/\D+/', '', (string)$s); }

function maskCpf($doc){
  $d = digitsOnly($doc);
  if (strlen($d) === 11) return substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2);
  return $doc ?: '—';
}
function maskCnpj($doc){
  $d = digitsOnly($doc);
  if (strlen($d) === 14) {
    return substr($d,0,2).'.'.substr($d,2,3).'.'.substr($d,5,3).'/'.substr($d,8,4).'-'.substr($d,12,2);
  }
  return $doc ?: '—';
}
function fmtDoc($type, $value){
  $type = strtoupper(trim((string)$type));
  $value = (string)($value ?? '');
  if ($type === 'CPF')  return maskCpf($value);
  if ($type === 'CNPJ') return maskCnpj($value);
  return $value !== '' ? $value : '—';
}

function baseUrl(): string {
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
  $scheme = $https ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  return $scheme . '://' . $host;
}

/**
 * Regra nova:
 * - AA => 0  (reservado / titular)
 * - AB => 1  (1º dependente)
 * - AC => 2  (2º dependente)
 * ...
 * - AZ => 25
 * - BA => 26
 */
function suffixToIndex(string $suffix): int {
  $suffix = strtoupper(trim($suffix));
  if (!preg_match('/^[A-Z]{2}$/', $suffix)) return 0;

  // AA reservado pro titular
  if ($suffix === 'AA') return 0;

  // Base antigo (AA=1, AB=2...) e desloca -1 para virar (AB=1, AC=2...)
  $a = ord($suffix[0]) - 64; // A=1
  $b = ord($suffix[1]) - 64; // A=1
  $old = (($a - 1) * 26) + $b; // AA=1
  $idx = $old - 1; // AB(2)->1, AC(3)->2
  return $idx > 0 ? $idx : 0;
}

/**
 * Separa code em:
 * - base: AVIV-000001
 * - suffix: AA/AB... (ou '')
 */
function parseAvivCode(string $code): array {
  $code = strtoupper(trim($code));
  if (!preg_match('/^AVIV-\d{6}(?:-[A-Z]{2})?$/', $code)) {
    return ['base' => '', 'suffix' => ''];
  }
  $parts = explode('-', $code);
  $base = count($parts) >= 2 ? ($parts[0].'-'.$parts[1]) : '';
  $suffix = count($parts) === 3 ? $parts[2] : '';
  return ['base' => $base, 'suffix' => $suffix];
}

$parsed = parseAvivCode((string)$code);
$baseCode = $parsed['base'];     // AVIV-000001
$suffix   = $parsed['suffix'];   // AA/AB...

$userId = 0;
if ($baseCode !== '' && preg_match('/^AVIV-(\d{6})$/', $baseCode, $m)) {
  $userId = (int)ltrim($m[1], '0');
}

$nome  = 'Associado';
$email = '—';
$since = (int)date('Y');

$docType = 'CPF';
$docVal  = null;

$birth = null;
$phone = null;
$addr  = null;
$city  = null;
$state = null;
$zip   = null;

$plan   = '—';
$status = '—';

$isDependent = false;
$holderCode  = null; // AVIV-000001
$displayCode = null; // AVIV-000001 ou AVIV-000001-AB

try {
  // ========== MODO PERSON (LEGADO) ==========
  if ($personId > 0) {
    $st = $pdo->prepare("
      SELECT
        sp.id               AS person_id,
        sp.role             AS role,
        sp.full_name        AS person_name,
        sp.doc_type         AS person_doc_type,
        sp.doc_value        AS person_doc_value,
        sp.birth_date       AS person_birth_date,
        sp.email            AS person_email,
        sp.phone            AS person_phone,
        s.id                AS subscription_id,
        s.user_id           AS user_id,
        s.plan_id           AS plan_id,
        s.status            AS sub_status,
        u.name              AS holder_name,
        u.email             AS holder_email,
        u.document          AS holder_document,
        u.birth_date        AS holder_birth_date,
        u.phone             AS holder_phone,
        u.address           AS holder_address,
        u.city              AS holder_city,
        u.state             AS holder_state,
        u.zip               AS holder_zip,
        u.created_at        AS holder_created_at
      FROM subscription_people sp
      INNER JOIN subscriptions s ON s.id = sp.subscription_id
      INNER JOIN users u ON u.id = s.user_id
      WHERE sp.id = ?
      LIMIT 1
    ");
    $st->execute([$personId]);

    if ($row = $st->fetch(PDO::FETCH_ASSOC)) {
      $role = strtolower((string)($row['role'] ?? 'dependent'));
      $isDependent = ($role === 'dependent');

      $uid = (int)($row['user_id'] ?? 0);
      $holderCode = $uid > 0 ? ('AVIV-' . str_pad((string)$uid, 6, '0', STR_PAD_LEFT)) : null;

      $since = !empty($row['holder_created_at']) ? (int)substr((string)$row['holder_created_at'], 0, 4) : (int)date('Y');

      $nome = ($row['person_name'] ?: ($row['holder_name'] ?? $nome));
      $docType = $row['person_doc_type'] ?? 'CPF';
      $docVal  = $row['person_doc_value'] ?? null;

      $birth = $row['person_birth_date'] ?? ($isDependent ? null : ($row['holder_birth_date'] ?? null));
      $email = ($row['person_email'] ?? '') ?: ($row['holder_email'] ?? $email);
      $phone = ($row['person_phone'] ?? '') ?: ($row['holder_phone'] ?? null);

      $addr  = $row['holder_address'] ?? null;
      $city  = $row['holder_city'] ?? null;
      $state = $row['holder_state'] ?? null;
      $zip   = $row['holder_zip'] ?? null;

      $status = $row['sub_status'] ?? '—';

      if (!empty($row['plan_id'])) {
        $ps = $pdo->prepare("SELECT name FROM plans WHERE id=? LIMIT 1");
        $ps->execute([$row['plan_id']]);
        $pr = $ps->fetch(PDO::FETCH_ASSOC);
        $map = ['start'=>'Start','plus'=>'Plus','prime'=>'Prime','blaster'=>'Blaster'];
        $plan = $pr['name'] ?? ($map[strtolower((string)$row['plan_id'])] ?? strtoupper((string)$row['plan_id']));
      }

      $displayCode = $isDependent
        ? ('DEP-' . str_pad((string)$personId, 6, '0', STR_PAD_LEFT))
        : ($holderCode ?: '—');

      $code = $holderCode ?: $code;
      $baseCode = $holderCode ?: $baseCode;
      $suffix = '';
    }
  }

  // ========== MODO CODE (TITULAR OU DEPENDENTE POR SUFIXO) ==========
  if ($personId <= 0 && $userId > 0) {
    $holderCode = $baseCode ?: ('AVIV-' . str_pad((string)$userId, 6, '0', STR_PAD_LEFT));

    // Puxa dados do titular (sempre)
    $u = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $u->execute([$userId]);
    if ($row = $u->fetch(PDO::FETCH_ASSOC)) {
      $since = !empty($row['created_at']) ? (int)substr((string)$row['created_at'],0,4) : (int)$since;

      $holderName  = $row['name'] ?? 'Associado';
      $holderEmail = $row['email'] ?? '—';

      $holderDoc   = $row['document'] ?? null;
      $holderBirth = $row['birth_date'] ?? null;
      $holderPhone = $row['phone'] ?? null;

      $holderAddr  = $row['address'] ?? null;
      $holderCity  = $row['city'] ?? null;
      $holderState = $row['state'] ?? null;
      $holderZip   = $row['zip'] ?? null;

      // Default do card = titular
      $nome = $holderName;
      $email = $holderEmail;
      $docType = 'CPF';
      $docVal  = $holderDoc;
      $birth   = $holderBirth;
      $phone   = $holderPhone;
      $addr    = $holderAddr;
      $city    = $holderCity;
      $state   = $holderState;
      $zip     = $holderZip;
    }

    // assinatura atual do titular
    $subId = null;
    $s = $pdo->prepare("SELECT id, plan_id, status FROM subscriptions WHERE user_id=? ORDER BY id DESC LIMIT 1");
    $s->execute([$userId]);
    if ($sub = $s->fetch(PDO::FETCH_ASSOC)){
      $subId = (int)($sub['id'] ?? 0);
      $status = $sub['status'] ?? '—';

      if (!empty($sub['plan_id'])){
        $ps = $pdo->prepare("SELECT name FROM plans WHERE id=? LIMIT 1");
        $ps->execute([$sub['plan_id']]);
        $r = $ps->fetch(PDO::FETCH_ASSOC);
        $map = ['start'=>'Start','plus'=>'Plus','prime'=>'Prime','blaster'=>'Blaster'];
        $plan = $r['name'] ?? ($map[strtolower((string)$sub['plan_id'])] ?? strtoupper((string)$sub['plan_id']));
      }
    }

    // Se veio com sufixo e for dependente (AB/AC/...), busca o N-ésimo
    if ($suffix !== '' && $subId > 0) {
      $idx = suffixToIndex($suffix); // AA=0 (titular), AB=1, AC=2...
      if ($idx > 0) {
        $cols = $pdo->query("
          SELECT COLUMN_NAME
          FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='subscription_people'
        ")->fetchAll(PDO::FETCH_COLUMN) ?: [];
        $cols = array_map('strtolower', $cols);

        $hasRole  = in_array('role', $cols, true);
        $hasBirth = in_array('birth_date', $cols, true);
        $hasMail  = in_array('email', $cols, true);
        $hasPhone = in_array('phone', $cols, true);

        // Tenta suportar schema com full_name/doc_value; se não existir, mantém como está (vai falhar limpo)
        $select = "sp.id, sp.full_name, sp.doc_type, sp.doc_value";
        if ($hasBirth) $select .= ", sp.birth_date";
        if ($hasMail)  $select .= ", sp.email";
        if ($hasPhone) $select .= ", sp.phone";
        if ($hasRole)  $select .= ", sp.role";

        $whereRole = $hasRole ? " AND LOWER(COALESCE(sp.role,'dependent'))='dependent' " : "";

        $q = $pdo->prepare("
          SELECT $select
          FROM subscription_people sp
          WHERE sp.subscription_id = ?
          $whereRole
          ORDER BY sp.id ASC
          LIMIT 1 OFFSET " . (int)($idx - 1) . "
        ");
        $q->execute([$subId]);

        if ($sp = $q->fetch(PDO::FETCH_ASSOC)) {
          $isDependent = true;

          $nome = $sp['full_name'] ?? $nome;
          $docType = $sp['doc_type'] ?? $docType;
          $docVal  = $sp['doc_value'] ?? $docVal;

          if (!empty($sp['birth_date'])) $birth = $sp['birth_date'];
          if (!empty($sp['email']))      $email = $sp['email'];
          if (!empty($sp['phone']))      $phone = $sp['phone'];
        }
      } else {
        // AA (reservado) ou sufixo inválido => mantém titular
        $isDependent = false;
      }
    }

    $displayCode = $suffix !== '' ? ($holderCode . '-' . $suffix) : ($holderCode ?: '—');
  }

} catch (Throwable $e) { /* silencioso */ }

// Link público correto
$base = baseUrl();

if ($personId > 0) {
  $publicUrl = $base . '/?r=site/card&person=' . urlencode((string)$personId);
} else {
  $requestedCode = strtoupper(trim((string)($_GET['code'] ?? '')));
  if (!preg_match('/^AVIV-\d{6}(?:-[A-Z]{2})?$/', $requestedCode)) {
    $requestedCode = $displayCode ?: '';
  }
  $publicUrl = $base . '/?r=site/card&code=' . urlencode((string)$requestedCode);
}

if (!empty($_GET['print'])) {
  $publicUrl .= '&print=1';
}

// QR
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?margin=0&size=148x148&data=' . urlencode($publicUrl);

// label doc
$docLabel = strtoupper(trim((string)$docType));
if (!in_array($docLabel, ['CPF','CNPJ','RG','CN','MATRICULA'], true)) $docLabel = 'DOCUMENTO';

?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Carteirinha • Aviv+</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root{
  --ink:#0f172a;
  --muted:#64748b;
  --border-subtle: rgba(15,23,42,.10);
  --blue:#2563eb;
}
*{ box-sizing:border-box; }
body{
  margin:0;
  background:#f6f7fb;
  color:var(--ink);
  font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;
}
.page{
  display:grid;
  place-items:center;
  min-height:100dvh;
  padding:16px;
}
.glass-card{
  width:min(820px, 94vw);
  border-radius:18px;
  padding:16px 16px 14px;
  background:linear-gradient(135deg, #e0f2ff, #ffffff 40%, #f5f6fb 100%);
  border:1px solid var(--border-subtle);
  box-shadow:0 18px 40px rgba(15,23,42,.10);
}
.top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  padding-bottom:10px;
  border-bottom:1px dashed rgba(148,163,184,.75);
}
.logo{ width:96px; height:auto; }
.code-pill{
  font-size:.82rem;
  font-weight:900;
  padding:5px 10px;
  border-radius:999px;
  border:1px solid #cbd5f5;
  background:#eef2ff;
}
.code-sub{
  margin-top:4px;
  font-size:.78rem;
  color:var(--muted);
  text-align:right;
}
.body{
  display:grid;
  grid-template-columns:minmax(0,1fr) 172px;
  gap:14px;
  padding-top:12px;
}
@media (max-width:760px){
  .body{ grid-template-columns:1fr; }
  .code-sub{ text-align:left; }
}
.grid{
  display:grid;
  gap:8px;
  grid-template-columns:repeat(2, minmax(0,1fr));
}
@media (max-width:560px){
  .grid{ grid-template-columns:1fr; }
}
.field{ display:grid; gap:2px; min-width:0; }
.label{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.04em;
  opacity:.9;
  color:var(--muted);
}
.value{
  font-weight:800;
  line-height:1.26;
  overflow-wrap:anywhere;
  color:var(--ink);
}
.span-2{ grid-column:1/-1; }
.right{
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:6px;
}
.qr{
  width:148px;
  height:148px;
  border-radius:12px;
  border:1px solid var(--border-subtle);
  background:#ffffff;
  overflow:hidden;
  display:grid;
  place-items:center;
}
.qr img{ width:100%; height:100%; object-fit:contain; }
.qr-cap{ font-size:.8rem; color:var(--muted); text-align:center; }

.actions{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  margin-top:12px;
}
.btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  padding:10px 14px;
  border-radius:999px;
  border:0;
  background:var(--blue);
  color:#fff;
  font-weight:900;
  text-decoration:none;
  cursor:pointer;
  box-shadow:0 12px 24px rgba(15,23,42,.16);
}
.btn--ghost{
  background:#e5e7eb;
  color:#111827;
  box-shadow:none;
}
.muted{ color:var(--muted); }

@media print{
  body{ background:#fff; }
  .page{ padding:0; }
  .glass-card{ box-shadow:none; border-color:#ddd; background:#fff; }
  .actions{ display:none; }
}
</style>
</head>
<body>
  <div class="page">
    <div class="glass-card">
      <div class="top">
        <img class="logo" src="/img/logo.png" alt="Aviv+">
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:2px">
          <div class="code-pill"><?= h($displayCode ?: '—') ?></div>
          <?php if (!empty($holderCode) && $isDependent): ?>
            <div class="code-sub">Titular: <?= h($holderCode) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <div class="body">
        <div class="grid">
          <div class="field span-2">
            <div class="label">Nome completo</div>
            <div class="value"><?= h($nome) ?></div>
          </div>

          <div class="field">
            <div class="label"><?= h($docLabel) ?></div>
            <div class="value"><?= h(fmtDoc($docType, $docVal)) ?></div>
          </div>

          <div class="field">
            <div class="label">Nascimento</div>
            <div class="value"><?= h(fmtDateBR($birth)) ?></div>
          </div>

          <div class="field">
            <div class="label">Plano</div>
            <div class="value"><?= h($plan) ?></div>
          </div>
          <div class="field">
            <div class="label">Status</div>
            <div class="value"><?= h($status) ?></div>
          </div>

          <div class="field span-2">
            <div class="label">Endereço</div>
            <div class="value"><?= h($addr ?: '—') ?></div>
          </div>

          <div class="field">
            <div class="label">Cidade/UF</div>
            <div class="value"><?= h(trim(($city ?? '').($state?'/'.$state:'')) ?: '—') ?></div>
          </div>
          <div class="field">
            <div class="label">CEP</div>
            <div class="value"><?= h($zip ?: '—') ?></div>
          </div>

          <div class="field">
            <div class="label">Telefone</div>
            <div class="value"><?= h($phone ?: '—') ?></div>
          </div>
          <div class="field">
            <div class="label">E-mail</div>
            <div class="value"><?= h($email ?: '—') ?></div>
          </div>

          <div class="field">
            <div class="label">Membro desde</div>
            <div class="value"><?= (int)$since ?></div>
          </div>

          <div class="field">
            <div class="label">Código</div>
            <div class="value"><?= h($displayCode ?: '—') ?></div>
          </div>
        </div>

        <div class="right">
          <div class="qr"><img src="<?= h($qrUrl) ?>" alt="QR"></div>
          <div class="qr-cap muted">Apresente este QR no parceiro</div>
        </div>
      </div>

      <div class="actions">
        <a class="btn btn--ghost" href="<?= h($publicUrl) ?>">Abrir link</a>
        <button class="btn" type="button" onclick="window.print()">Imprimir / PDF</button>
      </div>
    </div>
  </div>

<?php if (!empty($_GET['print'])): ?>
<script>window.addEventListener('load', ()=> setTimeout(()=>window.print(), 60));</script>
<?php endif; ?>
</body>
</html>
