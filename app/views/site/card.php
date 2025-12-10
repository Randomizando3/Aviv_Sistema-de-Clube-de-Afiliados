<?php
Auth::start();
require_once __DIR__ . '/../../core/DB.php';

$code = $_GET['code'] ?? '';
if (!preg_match('/^AVIV-\d{6}$/', $code)) { $code = ''; }
$userId = $code ? (int)ltrim(substr($code, 5), '0') : 0;

$nome = 'Associado';
$email = '—';
$since = date('Y');
$doc = $birth = $phone = $addr = $city = $state = $zip = null;
$plan = '—'; $status = '—';

function fmtDateBR($d){ if(!$d) return '—'; $s=explode('T',(string)$d)[0]; if(!$s) return '—'; [$y,$m,$dd]=array_pad(explode('-',$s),3,null); return ($y&&$m&&$dd)?sprintf('%02d/%02d/%04d',$dd,$m,$y):'—'; }
function maskCpf($doc){ $d=preg_replace('/\D+/','',(string)$doc); return strlen($d)===11?substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2):($doc?:'—'); }

try{
  if ($userId > 0){
    $pdo = DB::pdo();
    $u = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $u->execute([$userId]);
    if ($row = $u->fetch(PDO::FETCH_ASSOC)){
      $nome  = $row['name'] ?? $nome;
      $email = $row['email'] ?? $email;
      $since = !empty($row['created_at']) ? (int)substr($row['created_at'],0,4) : $since;
      $doc   = $row['document']   ?? null;
      $birth = $row['birth_date'] ?? null;
      $phone = $row['phone']      ?? null;
      $addr  = $row['address']    ?? null;
      $city  = $row['city']       ?? null;
      $state = $row['state']      ?? null;
      $zip   = $row['zip']        ?? null;
    }
    $s = $pdo->prepare("SELECT plan_id,status FROM subscriptions WHERE user_id=? ORDER BY id DESC LIMIT 1");
    $s->execute([$userId]);
    if ($sub = $s->fetch(PDO::FETCH_ASSOC)){
      $status = $sub['status'] ?? '—';
      if ($sub['plan_id']){
        $ps = $pdo->prepare("SELECT name FROM plans WHERE id=? LIMIT 1");
        $ps->execute([$sub['plan_id']]);
        $r = $ps->fetch(PDO::FETCH_ASSOC);
        $map = ['start'=>'Start','plus'=>'Plus','prime'=>'Prime','blaster'=>'Blaster'];
        $plan = $r['name'] ?? ($map[strtolower((string)$sub['plan_id'])] ?? strtoupper((string)$sub['plan_id']));
      }
    }
  }
}catch(Throwable $e){}
$publicUrl = (isset($_SERVER['REQUEST_SCHEME'])?$_SERVER['REQUEST_SCHEME']:'https').'://'.$_SERVER['HTTP_HOST'].'/?r=site/card&code='.urlencode($code);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Carteirinha • Aviv+</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root{ --bg:#0c0f1a; --card:#10172a; --bd:rgba(255,255,255,.15); --txt:#eaf3ff; }
*{ box-sizing:border-box; }
body{ margin:0; background:var(--bg); color:var(--txt); font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif; }
.page{ display:grid; place-items:center; min-height:100dvh; padding:16px; }
.card{
  width:min(720px, 94vw);
  border-radius:16px; padding:16px 16px 12px;
  background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
  border:1px solid var(--bd);
  box-shadow:0 8px 36px rgba(0,0,0,.35);
}
.top{ display:flex; align-items:center; justify-content:space-between; gap:8px; padding-bottom:8px; border-bottom:1px dashed var(--bd) }
.logo{ width:96px; height:auto }
.code{ font-size:.8rem; font-weight:800; padding:4px 8px; border-radius:999px; border:1px solid var(--bd); background:rgba(255,255,255,.1) }

.body{ display:grid; grid-template-columns:minmax(0,1fr) 172px; gap:14px; padding-top:10px }
@media print, (max-width:760px){ .body{ grid-template-columns:1fr } }

.grid{ display:grid; gap:8px; grid-template-columns:repeat(2, minmax(0,1fr)); }
.field{ display:grid; gap:2px; min-width:0 }
.field .label{ font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; opacity:.9 }
.field .value{ font-weight:700; line-height:1.28; overflow-wrap:anywhere; }
.span-2{ grid-column:1/-1 }

.qr{ width:148px; height:148px; border-radius:12px; border:1px solid var(--bd); background:rgba(255,255,255,.06); overflow:hidden; display:grid; place-items:center }
.qr img{ width:100%; height:100%; object-fit:contain }
.actions{ display:flex; gap:8px; margin-top:10px; }
.btn{ padding:8px 12px; border-radius:10px; border:1px solid var(--bd); background:rgba(255,255,255,.1); color:#fff; text-decoration:none; display:inline-block }
@media print{ .actions{ display:none } body{ background:#fff } .card{ box-shadow:none; border-color:#ddd } .code{ border-color:#bbb } }
</style>
</head>
<body>
  <div class="page">
    <div class="card">
      <div class="top">
        <img class="logo" src="/img/logo-aviv-plus.png" alt="Aviv+">
        <div class="code"><?= htmlspecialchars($code ?: '—') ?></div>
      </div>

      <div class="body">
        <div class="grid">
          <div class="field span-2"><div class="label">Nome completo</div><div class="value"><?= htmlspecialchars($nome) ?></div></div>

          <div class="field"><div class="label">CPF</div><div class="value"><?= htmlspecialchars(maskCpf($doc)) ?></div></div>
          <div class="field"><div class="label">Nascimento</div><div class="value"><?= htmlspecialchars(fmtDateBR($birth)) ?></div></div>

          <div class="field"><div class="label">Plano</div><div class="value"><?= htmlspecialchars($plan) ?></div></div>
          <div class="field"><div class="label">Status</div><div class="value"><?= htmlspecialchars($status) ?></div></div>

          <div class="field span-2"><div class="label">Endereço</div><div class="value"><?= htmlspecialchars($addr ?: '—') ?></div></div>

          <div class="field"><div class="label">Cidade/UF</div><div class="value"><?= htmlspecialchars(trim(($city ?? '').($state?'/'.$state:'')) ?: '—') ?></div></div>
          <div class="field"><div class="label">CEP</div><div class="value"><?= htmlspecialchars($zip ?: '—') ?></div></div>

          <div class="field"><div class="label">Telefone</div><div class="value"><?= htmlspecialchars($phone ?: '—') ?></div></div>
          <div class="field"><div class="label">E-mail</div><div class="value"><?= htmlspecialchars($email) ?></div></div>

          <div class="field"><div class="label">Membro desde</div><div class="value"><?= (int)$since ?></div></div>
          <div class="field"><div class="label">Código do associado</div><div class="value"><?= htmlspecialchars($code ?: '—') ?></div></div>
        </div>

        <div>
          <div class="qr"><img src="<?= 'https://api.qrserver.com/v1/create-qr-code/?size=148x148&margin=0&data='.urlencode($publicUrl) ?>" alt="QR"></div>
        </div>
      </div>

      <div class="actions">
        <a class="btn" href="<?= htmlspecialchars($publicUrl) ?>">Abrir link</a>
        <a class="btn" href="javascript:window.print()">Imprimir / PDF</a>
      </div>
    </div>
  </div>

<?php if (!empty($_GET['print'])): ?>
<script>window.addEventListener('load', ()=> setTimeout(()=>window.print(), 50));</script>
<?php endif; ?>
</body>
</html>
