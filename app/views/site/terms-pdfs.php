<?php
declare(strict_types=1);

/* ===== UTF-8 / locale ===== */
@ini_set('default_charset', 'UTF-8');
if (function_exists('mb_internal_encoding')) { @mb_internal_encoding('UTF-8'); }
@setlocale(LC_ALL, 'pt_BR.UTF-8', 'pt_BR', 'en_US.UTF-8');

/* ===== Caminhos ===== */
$BASE_PATH  = \defined('BASE_PATH') ? \BASE_PATH : \dirname(__DIR__, 2);
$TERMS_DIR  = $BASE_PATH . '/storage/termos/aceites';     // pasta real
$PUBLIC_DIR = '/storage/termos/aceites';                  // exibido no topo

/* ===== Helpers ===== */
function sanitize_unicode_filename(string $name): string {
  $name = trim(str_replace("\0", '', $name));
  // preserva acentos; troca só caracteres perigosos por "_"
  $name = preg_replace('/[^\p{L}\p{N}\.\-_ ]+/u', '_', $name) ?? $name;
  $name = preg_replace('/\s+/u', ' ', $name) ?? $name;
  if ($name === '' || $name === '.' || $name === '..') $name = 'documento.pdf';
  return $name;
}
$ensurePdf = static fn(string $n) => preg_match('/\.pdf$/ui', $n) ? $n : ($n . '.pdf');
$fmtSize   = static function (int $b): string {
  $u=['B','KB','MB','GB']; $i=0; while($b>=1024 && $i<count($u)-1){$b/=1024;$i++;} return sprintf('%.1f %s',$b,$u[$i]);
};
$fmtDate   = static fn(int $t) => date('d/m/Y H:i', $t);

/* ===== Ações ===== */

/* Download: GET ?f=nome.pdf */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['f'])) {
  $fn   = sanitize_unicode_filename(basename((string)$_GET['f']));
  $file = $TERMS_DIR . '/' . $fn;

  if (!is_file($file)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Arquivo não encontrado.";
    exit;
  }

  header('Content-Type: application/pdf; charset=UTF-8');
  header('Content-Length: ' . filesize($file));
  header('Content-Disposition: attachment; filename="' . $fn . '"');
  header('X-Content-Type-Options: nosniff');
  readfile($file);
  exit;
}

/* Renomear: POST action=rename {old,new}  */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rename') {
  // zera QUALQUER buffer antes de responder
  while (ob_get_level() > 0) { @ob_end_clean(); }

  $old = $ensurePdf(sanitize_unicode_filename(basename((string)($_POST['old'] ?? ''))));
  $new = $ensurePdf(sanitize_unicode_filename(basename((string)($_POST['new'] ?? ''))));

  $payload = ['ok' => false, 'error' => ''];

  if ($old === '' || $new === '') {
    $payload['error'] = 'Nome inválido.';
  } else {
    if (!is_dir($TERMS_DIR)) { @mkdir($TERMS_DIR, 0775, true); }
    $src = $TERMS_DIR . '/' . $old;
    $dst = $TERMS_DIR . '/' . $new;

    if (!is_file($src)) {
      $payload['error'] = 'Arquivo de origem não existe.';
    } elseif (strcasecmp($old, $new) !== 0 && is_file($dst)) {
      $payload['error'] = 'Já existe arquivo com esse nome.';
    } else {
      $ok = @rename($src, $dst);
      if ($ok) { $payload = ['ok' => true, 'name' => $new]; }
      else     { $payload['error'] = 'Falha ao renomear (permissões?)'; }
    }
  }

  $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
  header('Content-Type: application/json; charset=UTF-8');
  header('Content-Length: ' . strlen((string)$json));
  header('Cache-Control: no-store');
  echo $json;
  exit;
}

/* ===== Listagem ===== */
if (!is_dir($TERMS_DIR)) { @mkdir($TERMS_DIR, 0775, true); }

$files = [];
foreach (is_dir($TERMS_DIR) ? scandir($TERMS_DIR) : [] as $f) {
  if ($f==='.' || $f==='..') continue;
  $p = $TERMS_DIR . '/' . $f;
  if (is_file($p) && preg_match('/\.pdf$/ui', $f)) {
    $files[] = ['name'=>$f, 'size'=>filesize($p), 'time'=>filemtime($p)];
  }
}
usort($files, static fn($a,$b) => $b['time'] <=> $a['time']);

/* ===== HTML ===== */
header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Termos de Aceite (PDFs) • Aviv+</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{--bg:#0f1220;--card:#14182b;--muted:#9aa3b2;--txt:#e8ecf4;--accent:#3b82f6}
    *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--txt);font:14px/1.45 system-ui,Segoe UI,Roboto,Arial}
    .wrap{max-width:980px;margin:28px auto;padding:0 16px}
    .title{font-size:20px;margin:0 0 10px}
    .muted{color:var(--muted)}
    .card{background:var(--card);border-radius:12px;overflow:hidden;border:1px solid rgba(255,255,255,.06)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:14px 12px}
    th{font-weight:600;text-align:left;background:rgba(255,255,255,.03)}
    tr+tr td{border-top:1px solid rgba(255,255,255,.06)}
    .btn{background:var(--accent);color:#fff;border:0;border-radius:10px;padding:8px 14px;cursor:pointer;text-decoration:none;display:inline-block}
    .name{cursor:default}
    .name input{width:100%;padding:4px 6px;border-radius:6px;border:1px solid rgba(255,255,255,.12);background:#0b0e1a;color:var(--txt)}
    .hint{font-size:12px;margin:10px 2px}
  </style>
</head>
<body>
  <div class="wrap">
    <h1 class="title">Termos de Aceite (PDFs)</h1>
    <p class="muted">Diret&oacute;rio: <code><?= htmlspecialchars($PUBLIC_DIR, ENT_QUOTES, 'UTF-8') ?></code></p>

    <div class="card">
      <table aria-label="lista de PDFs">
        <thead>
          <tr>
            <th style="width:55%">Arquivo</th>
            <th style="width:15%">Tamanho</th>
            <th style="width:20%">Modificado</th>
            <th style="width:10%">A&ccedil;&otilde;es</th>
          </tr>
        </thead>
        <tbody id="rows">
        <?php if (!$files): ?>
          <tr><td colspan="4" class="muted">Nenhum PDF encontrado.</td></tr>
        <?php else: foreach ($files as $f): ?>
          <tr data-name="<?= htmlspecialchars($f['name'],ENT_QUOTES,'UTF-8') ?>">
            <td><span class="name" title="Duplo clique para renomear"><?= htmlspecialchars($f['name'],ENT_QUOTES,'UTF-8') ?></span></td>
            <td class="muted"><?= $fmtSize($f['size']) ?></td>
            <td class="muted"><?= $fmtDate($f['time']) ?></td>
            <td><a class="btn" href="?r=terms-pdfs&amp;f=<?= rawurlencode($f['name']) ?>">Baixar</a></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

    <p class="hint muted">Dica: d&ecirc; <strong>duplo clique</strong> no nome para renomear. A extens&atilde;o <code>.pdf</code> &eacute; mantida.</p>
  </div>

<script>
(function(){
  const SELF = '?r=terms-pdfs';

  function makeInput(span, currentName){
    const input = document.createElement('input');
    input.value = currentName.replace(/\.pdf$/i,'');
    span.replaceWith(input);
    input.focus(); input.select();

    const finish = async (apply)=>{
      const newSpan = document.createElement('span');
      newSpan.className = 'name';

      if(!apply){ newSpan.textContent = currentName; input.replaceWith(newSpan); return; }

      const base = (input.value || '').trim();
      if(!base){ alert('Nome inválido.'); newSpan.textContent=currentName; input.replaceWith(newSpan); return; }
      const newName = /\.pdf$/i.test(base) ? base : base + '.pdf';

      try{
        const fd = new FormData();
        fd.append('action','rename');
        fd.append('old', currentName);
        fd.append('new', newName);

        const r = await fetch(SELF, { method:'POST', body: fd, credentials:'same-origin' });
        const text = await r.text();
        if(!r.ok) throw new Error('HTTP ' + r.status + (text ? (' – ' + text) : ''));
        let j; try { j = JSON.parse(text); } catch { throw new Error('Resposta n\u00E3o-JSON recebida.'); }

        if(!j.ok){ alert(j.error || 'Falha ao renomear.'); newSpan.textContent=currentName; input.replaceWith(newSpan); return; }

        newSpan.textContent = j.name;
        const tr = input.closest('tr');
        tr.dataset.name = j.name;
        tr.querySelector('td:last-child a').href = `?r=terms-pdfs&f=${encodeURIComponent(j.name)}`;
        input.replaceWith(newSpan);
      }catch(e){
        alert('Erro ao renomear: ' + e.message);
        newSpan.textContent = currentName; input.replaceWith(newSpan);
      }
    };

    input.addEventListener('keydown', e=>{
      if(e.key==='Enter') finish(true);
      if(e.key==='Escape') finish(false);
    });
    input.addEventListener('blur', ()=>finish(true));
  }

  document.getElementById('rows').addEventListener('dblclick', e=>{
    const span = e.target.closest('.name');
    if(!span) return;
    const tr = span.closest('tr');
    const current = tr?.dataset?.name;
    if(!current) return;
    makeInput(span, current);
  });
})();
</script>
</body>
</html>
