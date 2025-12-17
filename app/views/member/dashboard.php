<?php
Auth::start();
$me = Auth::user();

$memberId      = $me['id']    ?? null;
$nomeAssociado = $me['name']  ?? 'Associado';
$email         = $me['email'] ?? '—';
$memberCode    = $memberId ? 'AVIV-' . str_pad((string)$memberId, 6, '0', STR_PAD_LEFT) : '—';
$sinceYear     = date('Y');

$planoAtual  = '—';
$statusPlano = '—';

$document  = $me['document']   ?? null;
$birthDate = $me['birth_date'] ?? null;
$phone     = $me['phone']      ?? null;
$address   = $me['address']    ?? null;
$city      = $me['city']       ?? null;
$state     = $me['state']      ?? null;
$zip       = $me['zip']        ?? null;

function fmtDateBR($date){
  if (!$date) return '—';
  $s = explode('T', (string)$date)[0];
  if (!$s) return '—';
  [$y,$m,$d] = array_pad(explode('-', $s), 3, null);
  return ($y && $m && $d) ? sprintf('%02d/%02d/%04d', $d, $m, $y) : '—';
}
function maskCpf($doc){
  $d = preg_replace('/\D+/', '', (string)$doc);
  if (strlen($d) === 11) return substr($d,0,3).'.'.substr($d,3,3).'.'.substr($d,6,3).'-'.substr($d,9,2);
  return $doc ?: '—';
}

/**
 * 1 -> AA, 2 -> AB, 3 -> AC ... (A1 base-26)
 * Aqui vamos RESERVAR o AA para o titular,
 * então dependente #1 usa n=2 => AB.
 */
function alpha2FromNumber(int $n): string {
  if ($n < 1) $n = 1;
  $n--; // 0-based
  $a = intdiv($n, 26);
  $b = $n % 26;
  $a = max(0, min(25, $a));
  $b = max(0, min(25, $b));
  return chr(65 + $a) . chr(65 + $b);
}

$dependentes = [];
$subId = null;

try{
  $pdo = DB::pdo();

  $dbName = (string)$pdo->query("SELECT DATABASE()")->fetchColumn();

  $tableExists = function(string $table) use ($pdo, $dbName): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?");
    $q->execute([$dbName, $table]);
    return (int)$q->fetchColumn() > 0;
  };
  $colExists = function(string $table, string $col) use ($pdo, $dbName): bool {
    $q = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?");
    $q->execute([$dbName, $table, $col]);
    return (int)$q->fetchColumn() > 0;
  };

  if ($memberId){
    // user completo
    $u = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $u->execute([$memberId]);
    if ($row = $u->fetch(PDO::FETCH_ASSOC)){
      $nomeAssociado = $row['name'] ?? $nomeAssociado;
      $email         = $row['email'] ?? $email;
      $sinceYear     = !empty($row['created_at']) ? (int)substr($row['created_at'], 0, 4) : $sinceYear;
      $document  = $row['document']   ?? $document;
      $birthDate = $row['birth_date'] ?? $birthDate;
      $phone     = $row['phone']      ?? $phone;
      $address   = $row['address']    ?? $address;
      $city      = $row['city']       ?? $city;
      $state     = $row['state']      ?? $state;
      $zip       = $row['zip']        ?? $zip;
    }

    // assinatura atual
    $st = $pdo->prepare("SELECT id, plan_id, status FROM subscriptions WHERE user_id=? ORDER BY id DESC LIMIT 1");
    $st->execute([$memberId]);
    if ($sub = $st->fetch(PDO::FETCH_ASSOC)){
      $subId = $sub['id'] ?? null;
      $statusPlano = $sub['status'] ?? '—';
      $planId = $sub['plan_id'] ?? null;

      if ($planId){
        $ps = $pdo->prepare("SELECT name FROM plans WHERE id=? LIMIT 1");
        $ps->execute([$planId]);
        $pr = $ps->fetch(PDO::FETCH_ASSOC);
        $map = ['start'=>'Start','plus'=>'Plus','prime'=>'Prime','blaster'=>'Blaster'];
        $planoAtual = $pr['name'] ?? ($map[strtolower((string)$planId)] ?? strtoupper((string)$planId));
      }
    }

    // DEPENDENTES (subscription_people)
    if ($subId && $tableExists('subscription_people') && $colExists('subscription_people','subscription_id')) {

      // Descobre colunas disponíveis (para evitar erro)
      $cols = $pdo->query("
        SELECT COLUMN_NAME
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='subscription_people'
      ")->fetchAll(PDO::FETCH_COLUMN) ?: [];
      $cols = array_map('strtolower', $cols);

      $hasRole   = in_array('role', $cols, true);
      $hasMail   = in_array('email', $cols, true);
      $hasPhone  = in_array('phone', $cols, true);

      $nameCol = in_array('full_name', $cols, true) ? 'full_name' : (in_array('name', $cols, true) ? 'name' : null);
      $docVCol = in_array('doc_value', $cols, true) ? 'doc_value' : (in_array('document', $cols, true) ? 'document' : null);
      $hasBirth = in_array('birth_date', $cols, true);

      $select = "id";
      $select .= $nameCol ? ", {$nameCol} AS full_name" : ", NULL AS full_name";
      $select .= $docVCol ? ", {$docVCol} AS doc_value" : ", NULL AS doc_value";
      $select .= $hasBirth ? ", birth_date" : ", NULL AS birth_date";
      $select .= $hasMail ? ", email" : ", NULL AS email";
      $select .= $hasPhone ? ", phone" : ", NULL AS phone";
      if ($hasRole) $select .= ", role";

      $whereRole = $hasRole ? " AND LOWER(COALESCE(role,'dependent'))='dependent' " : "";

      $q = $pdo->prepare("
        SELECT {$select}
        FROM subscription_people
        WHERE subscription_id = ?
        {$whereRole}
        ORDER BY id ASC
      ");
      $q->execute([$subId]);

      $i = 0;
      while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        $i++;
        $suffix = alpha2FromNumber($i + 1); // i=1 => AB
        $depCode = $memberCode . '-' . $suffix;

        $dependentes[] = [
          'id' => (int)($row['id'] ?? 0),
          'code' => $depCode,
          'name' => $row['full_name'] ?: ('Dependente ' . $i),
          'document' => $row['doc_value'] ?? null,
          'birth_date' => $row['birth_date'] ?? null,
          'email' => $row['email'] ?? null,
          'phone' => $row['phone'] ?? null,
        ];
      }
    }
  }
}catch(Throwable $e){}

$publicCardUrl = '/?r=site/card&code=' . urlencode($memberCode);
$adSkyFallback = $adSkyFallback ?? '/img/ads/160x600-default.png';
?>

<section class="container member dash-member" style="margin-top:18px; overflow:visible">
  <div class="rail-strip">

    <!-- Left sky -->
    <aside class="rail-sky rail-left" aria-label="Publicidade" aria-hidden="true">
      <div class="adbox-sky" id="ad-sky-left">
        <div class="ad-skeleton" aria-hidden="true"></div>
        <?php if (!empty($adSkyFallback)): ?>
          <a href="#" target="_blank" rel="noopener" class="ad-fallback" style="display:none">
            <img src="<?= htmlspecialchars($adSkyFallback) ?>" alt="Publicidade" width="168" height="600" loading="lazy">
          </a>
        <?php endif; ?>
      </div>
    </aside>

    <!-- CONTEÚDO CENTRAL -->
    <div class="rail-main">

      <!-- Carteirinha principal -->
      <section class="glass-card">
        <h2 class="sect-title">Carteirinha digital</h2>

        <div class="ucard">
          <div class="uc-top">
            <img class="uc-logo" src="/img/logo.png" alt="Aviv+" width="96" height="28">
            <div class="uc-code" title="Código do associado"><?= htmlspecialchars($memberCode) ?></div>
          </div>

          <div class="uc-body">
            <div class="uc-grid">
              <div class="uc-field uc-span2">
                <div class="uc-label">Nome completo</div>
                <div class="uc-value"><?= htmlspecialchars($nomeAssociado) ?></div>
              </div>

              <div class="uc-field">
                <div class="uc-label">CPF</div>
                <div class="uc-value"><?= htmlspecialchars(maskCpf($document)) ?></div>
              </div>
              <div class="uc-field">
                <div class="uc-label">Nascimento</div>
                <div class="uc-value"><?= htmlspecialchars(fmtDateBR($birthDate)) ?></div>
              </div>

              <div class="uc-field">
                <div class="uc-label">Plano</div>
                <div class="uc-value"><?= htmlspecialchars($planoAtual) ?></div>
              </div>
              <div class="uc-field">
                <div class="uc-label">Status</div>
                <div class="uc-value"><?= htmlspecialchars($statusPlano ?: '—') ?></div>
              </div>

              <div class="uc-field uc-span2">
                <div class="uc-label">Endereço</div>
                <div class="uc-value"><?= htmlspecialchars($address ?: '—') ?></div>
              </div>

              <div class="uc-field">
                <div class="uc-label">Cidade/UF</div>
                <div class="uc-value"><?= htmlspecialchars(trim(($city ?? '').($state?'/'.$state:'')) ?: '—') ?></div>
              </div>
              <div class="uc-field">
                <div class="uc-label">CEP</div>
                <div class="uc-value"><?= htmlspecialchars($zip ?: '—') ?></div>
              </div>

              <div class="uc-field">
                <div class="uc-label">Telefone</div>
                <div class="uc-value"><?= htmlspecialchars($phone ?: '—') ?></div>
              </div>
              <div class="uc-field">
                <div class="uc-label">E-mail</div>
                <div class="uc-value uc-nowrap"><?= htmlspecialchars($email) ?></div>
              </div>

              <div class="uc-field">
                <div class="uc-label">Membro desde</div>
                <div class="uc-value"><?= (int)$sinceYear ?></div>
              </div>
              <div class="uc-field">
                <div class="uc-label">Código do associado</div>
                <div class="uc-value">
                  <span class="js-member-code"><?= htmlspecialchars($memberCode) ?></span>
                  <button class="btn btn-xs btn--ghost js-copy-code" type="button">Copiar</button>
                </div>
              </div>
            </div>

            <div class="uc-right">
              <div class="uc-qr" aria-label="Código QR">
                <img class="js-qr-img"
                     data-public-url="<?= htmlspecialchars($publicCardUrl) ?>"
                     alt="QR" width="148" height="148" decoding="async" loading="lazy">
              </div>
              <div class="uc-qr-caption muted">Apresente este QR no parceiro</div>
            </div>
          </div>

          <div class="uc-actions">
            <button class="btn btn-sm js-fullqr" type="button"
                    data-title="Carteirinha • QR"
                    data-public-url="<?= htmlspecialchars($publicCardUrl) ?>">
              Mostrar QR em tela cheia
            </button>
            <a class="btn btn-sm btn--ghost" href="<?= htmlspecialchars($publicCardUrl) ?>&print=1" target="_blank" rel="noopener">Baixar PDF</a>
          </div>

          <div id="member-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
        </div>
      </section>

      <!-- Dependentes -->
      <section class="glass-card" style="margin-top:12px">
        <h2 class="sect-title">Dependentes</h2>
        <p class="muted" style="margin:-2px 0 10px">Carteirinhas vinculadas ao seu plano familiar.</p>

        <?php if (empty($dependentes)): ?>
          <div class="muted">Nenhum dependente encontrado para exibir no dashboard.</div>
        <?php else: ?>
          <div class="dep-grid">
            <?php foreach ($dependentes as $d): ?>
              <?php $depPublicCardUrl = '/?r=site/card&code=' . urlencode((string)$d['code']); ?>
              <article class="dep-card" aria-label="Carteirinha dependente">
                <div class="dep-top">
                  <div class="dep-name">
                    <div class="dep-label">Dependente</div>
                    <div class="dep-value"><?= htmlspecialchars($d['name']) ?></div>
                  </div>
                  <div class="dep-code"><?= htmlspecialchars($d['code']) ?></div>
                </div>

                <div class="dep-body">
                  <div class="dep-fields">
                    <div class="dep-row">
                      <span class="dep-k">CPF</span>
                      <span class="dep-v"><?= htmlspecialchars(maskCpf($d['document'])) ?></span>
                    </div>
                    <div class="dep-row">
                      <span class="dep-k">Nascimento</span>
                      <span class="dep-v"><?= htmlspecialchars(fmtDateBR($d['birth_date'])) ?></span>
                    </div>
                    <div class="dep-row">
                      <span class="dep-k">E-mail</span>
                      <span class="dep-v dep-ellipsis"><?= htmlspecialchars($d['email'] ?: '—') ?></span>
                    </div>
                    <div class="dep-row">
                      <span class="dep-k">Telefone</span>
                      <span class="dep-v"><?= htmlspecialchars($d['phone'] ?: '—') ?></span>
                    </div>
                  </div>

                  <div class="dep-qrbox">
                    <img class="js-qr-img"
                         data-public-url="<?= htmlspecialchars($depPublicCardUrl) ?>"
                         alt="QR dependente" width="112" height="112" loading="lazy" decoding="async">
                    <div class="muted" style="font-size:.78rem; text-align:center">QR do dependente</div>
                  </div>
                </div>

                <div class="dep-actions">
                  <button class="btn btn-xs btn--ghost js-fullqr" type="button"
                          data-title="Dependente • QR"
                          data-public-url="<?= htmlspecialchars($depPublicCardUrl) ?>">
                    QR em tela cheia
                  </button>

                  <a class="btn btn-xs btn--ghost" href="<?= htmlspecialchars($depPublicCardUrl) ?>&print=1" target="_blank" rel="noopener">
                    Baixar PDF
                  </a>

                  <button class="btn btn-xs btn--ghost js-copy-code" type="button">
                    <span class="js-member-code"><?= htmlspecialchars($d['code']) ?></span> • Copiar
                  </button>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- Faturas -->
      <section class="glass-card" style="margin-top:12px">
        <h2 class="sect-title">Faturas</h2>

        <div class="inv-quick">
          <div class="inv-tile" id="tile-next">
            <div class="tile-head">
              <div class="tile-title">Próxima cobrança</div>
              <span id="next-chip" class="chip chip-info" hidden>Próxima</span>
            </div>
            <div class="tile-value" id="next-amount">—</div>
            <div class="tile-sub" id="next-date">—</div>
            <div class="tile-actions" id="next-actions" hidden>
              <button class="btn btn-sm" type="button" id="btn-pay-next">Pagar agora</button>
            </div>
          </div>

          <div class="inv-tile" id="tile-last">
            <div class="tile-head">
              <div class="tile-title">Último pagamento</div>
              <span id="last-chip" class="chip chip-success" hidden>Pago</span>
            </div>
            <div class="tile-value" id="last-amount">—</div>
            <div class="tile-sub" id="last-date">—</div>
            <div class="tile-actions" id="last-actions" hidden>
              <a class="btn btn-sm btn--ghost" id="btn-receipt" href="#" target="_blank" rel="noopener">Ver recibo</a>
            </div>
          </div>

          <div class="inv-side-actions">
            <a class="btn btn-sm btn--ghost" href="/?r=member/faturas">Ver todas</a>
          </div>
        </div>

        <div class="muted" id="inv-note" style="margin-top:8px; display:none"></div>
      </section>
    </div>

    <!-- Right sky -->
    <aside class="rail-sky rail-right" aria-label="Publicidade" aria-hidden="true">
      <div class="adbox-sky" id="ad-sky-right">
        <div class="ad-skeleton" aria-hidden="true"></div>
        <?php if (!empty($adSkyFallback)): ?>
          <a href="#" target="_blank" rel="noopener" class="ad-fallback" style="display:none">
            <img src="<?= htmlspecialchars($adSkyFallback) ?>" alt="Publicidade" width="168" height="600" loading="lazy">
          </a>
        <?php endif; ?>
      </div>
    </aside>

  </div>
</section>

<!-- Modal QR -->
<div class="modal" id="qr-modal" role="dialog" aria-modal="true" aria-labelledby="qr-modal-title">
  <div class="modal-box glass-card">
    <h3 id="qr-modal-title" style="margin:0 0 8px">Carteirinha • QR</h3>
    <div class="qr-big">
      <img id="qr-img-big" alt="QR ampliado" width="320" height="320" decoding="async">
    </div>
    <div class="modal-actions">
      <button class="btn btn-sm btn--ghost" type="button" id="qr-close">Fechar</button>
    </div>
  </div>
</div>

<script>
/* =========================================================
   MENU (DASHBOARD) — MESMO ESTILO DO SITE (mobile-menu simples)
   - Começa SEMPRE oculto
   - Ao clicar no botão, abre sobreposto
   - Não altera os itens: clona os links existentes do nav atual
   ========================================================= */
(function(){
  function isMobile(){ return window.matchMedia('(max-width: 900px)').matches; }

  var header =
    document.querySelector('header.topnav[data-topnav]') ||
    document.querySelector('header.topnav') ||
    document.querySelector('header.header') ||
    document.querySelector('header');

  if (!header) return;

  // Fonte dos links (não mexe no menu original)
  var navSrc =
    header.querySelector('#menu') ||
    header.querySelector('nav') ||
    document.getElementById('menu');

  // Cria container do menu mobile (overlay)
  var menu = document.getElementById('dashMobileMenu');
  if (!menu){
    menu = document.createElement('div');
    menu.id = 'dashMobileMenu';
    menu.className = 'dash-mobile-menu';
    menu.setAttribute('aria-label','Menu mobile');
    document.body.appendChild(menu);
  }

  // Botão (reusa se existir, senão cria)
  var btn =
    header.querySelector('#navToggle') ||
    header.querySelector('.nav-toggle') ||
    header.querySelector('[data-nav-toggle]');

  if (!btn){
    btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'nav-toggle';
    btn.id = 'navToggle';
    btn.innerHTML = '<span class="nav-toggle__bar" aria-hidden="true"></span><span class="sr-only">Abrir menu</span>';

    var slot =
      header.querySelector('.header__actions') ||
      header.querySelector('.topnav__actions') ||
      header.querySelector('.actions') ||
      header.querySelector('.right') ||
      header;
    slot.appendChild(btn);
  }

  btn.setAttribute('aria-controls', menu.id);
  btn.setAttribute('aria-expanded', 'false');

  function rebuildMenu(){
    if (!navSrc) return;

    // pega SOMENTE links visíveis do menu original
    var links = Array.from(navSrc.querySelectorAll('a[href]'));
    if (!links.length) return;

    menu.innerHTML = '';
    links.forEach(function(a){
      var c = a.cloneNode(true);
      c.removeAttribute('style');
      menu.appendChild(c);
    });
  }

  function openMenu(){
    if (!isMobile()) return;
    rebuildMenu();
    menu.classList.add('is-open');
    btn.setAttribute('aria-expanded','true');
    document.body.classList.add('menu-open');
  }

  function closeMenu(){
    menu.classList.remove('is-open');
    btn.setAttribute('aria-expanded','false');
    document.body.classList.remove('menu-open');
  }

  // GARANTE: começa fechado
  closeMenu();

  btn.addEventListener('click', function(e){
    e.preventDefault();
    if (!isMobile()) return;
    menu.classList.contains('is-open') ? closeMenu() : openMenu();
  });

  // Fecha ao clicar em qualquer link dentro do menu
  menu.addEventListener('click', function(e){
    var a = e.target.closest && e.target.closest('a');
    if (a) closeMenu();
  });

  // Fecha ao clicar fora (mesmo padrão do seu exemplo)
  document.addEventListener('click', function(e){
    if (!isMobile()) return;
    if (!menu.classList.contains('is-open')) return;
    if (menu.contains(e.target) || btn.contains(e.target)) return;
    closeMenu();
  });

  // ESC fecha
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeMenu();
  });

  // Resize: ao sair do mobile, fecha
  window.addEventListener('resize', function(){
    if (!isMobile()) closeMenu();
  });
})();

/* ====== SKY ADS (esq/dir) ====== */
(function(){
  if (window.matchMedia('(max-width: 1020px)').matches) return;

  function loadSkys(){
    var L = document.getElementById('ad-sky-left');
    var R = document.getElementById('ad-sky-right');
    if(!L || !R) return;

    fetch('/?r=api/partner/ads/public-pool&type=sky&limit=2', { credentials:'same-origin' })
      .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
      .then(function(j){
        var list = (j && Array.isArray(j.items)) ? j.items.slice(0,2) : [];
        var boxes = [L,R];

        boxes.forEach(function(box, idx){
          var it = list[idx] || null;
          var sk = box.querySelector('.ad-skeleton'); if(sk) sk.remove();

          if(!it){
            var fb = box.querySelector('.ad-fallback');
            if(fb){ fb.style.display='block'; } else { box.parentElement.style.display='none'; }
            return;
          }

          box.innerHTML='';
          var a = document.createElement('a');
          a.href = it.target_url || '#';
          a.target='_blank';
          a.rel='noopener';
          a.title = it.title || 'Publicidade';

          var img = new Image();
          img.src = it.img;
          img.alt = it.title || 'Anúncio';
          img.loading='lazy';
          img.width = Number(it.w || 168);
          img.height = Number(it.h || 600);
          a.appendChild(img);
          box.appendChild(a);

          if(it.pixel){
            var px = new Image();
            px.src = it.pixel + '&ts=' + Date.now();
            px.width=1; px.height=1; px.alt='';
            px.style.position='absolute';
            px.style.inset='auto auto 0 0';
            px.style.opacity='0';
            box.appendChild(px);
          }
        });
      })
      .catch(function(){
        [L,R].forEach(function(box){
          var sk = box.querySelector('.ad-skeleton'); if(sk) sk.remove();
          var fb = box.querySelector('.ad-fallback');
          if(fb){ fb.style.display='block'; } else { box.parentElement.style.display='none'; }
        });
      });
  }

  if (document.readyState !== 'loading') loadSkys();
  else document.addEventListener('DOMContentLoaded', loadSkys);
})();

/* ====== QR (principal + dependentes) + Modal + Copiar ====== */
(function(){
  function makeQrUrl(publicUrl, size){
    const absolute = location.origin + publicUrl;
    return 'https://api.qrserver.com/v1/create-qr-code/?margin=0&data=' + encodeURIComponent(absolute) + '&size=' + size + 'x' + size;
  }

  document.querySelectorAll('.js-qr-img').forEach(img => {
    const publicUrl = img.getAttribute('data-public-url') || '';
    if (!publicUrl) return;
    const w = Number(img.getAttribute('width') || 148);
    img.src = makeQrUrl(publicUrl, w);
  });

  document.querySelectorAll('.js-copy-code').forEach(btn => {
    btn.addEventListener('click', async () => {
      const codeEl = btn.querySelector('.js-member-code') || btn.closest('*')?.querySelector('.js-member-code');
      const code = codeEl ? (codeEl.textContent || '').trim() : '';
      if (!code) return toast('Código vazio.');

      try{
        await navigator.clipboard.writeText(code);
        toast('Código copiado!');
      }catch(_){
        toast('Não foi possível copiar.');
      }
    });
  });

  const modal = document.getElementById('qr-modal');
  const modalTitle = document.getElementById('qr-modal-title');
  const modalImg = document.getElementById('qr-img-big');

  document.querySelectorAll('.js-fullqr').forEach(btn => {
    btn.addEventListener('click', () => {
      const title = btn.getAttribute('data-title') || 'Carteirinha • QR';
      const publicUrl = btn.getAttribute('data-public-url') || '';
      if (!publicUrl) return;

      modalTitle.textContent = title;
      modalImg.src = makeQrUrl(publicUrl, 320);
      modal?.classList.add('is-open');
    });
  });

  document.getElementById('qr-close')?.addEventListener('click', ()=> modal?.classList.remove('is-open'));
  modal?.addEventListener('click', (e)=>{ if(e.target === modal) modal.classList.remove('is-open'); });

  function toast(msg){
    const box = document.getElementById('member-alert');
    if (!box) return;
    box.textContent = msg;
    box.style.display = 'block';
    setTimeout(()=> box.style.display='none', 1600);
  }
})();

/* ====== LÓGICA DAS FATURAS (mantida) ====== */
(function(){
  const fmtBRL = v => 'R$ ' + (Number(v||0)).toFixed(2).replace('.', ',');
  const fmtDateBR = d => {
    if(!d) return '—';
    const s = String(d).split('T')[0]||'';
    const [y,m,dd] = s.split('-');
    return y ? `${dd}/${m}/${y}` : '—';
  };
  const norm = s => String(s||'').toLowerCase().trim();

  const nextAmount = document.getElementById('next-amount');
  const nextDate   = document.getElementById('next-date');
  const nextChip   = document.getElementById('next-chip');
  const nextActs   = document.getElementById('next-actions');
  const btnPayNext = document.getElementById('btn-pay-next');

  const lastAmount = document.getElementById('last-amount');
  const lastDate   = document.getElementById('last-date');
  const lastChip   = document.getElementById('last-chip');
  const lastActs   = document.getElementById('last-actions');
  const btnReceipt = document.getElementById('btn-receipt');

  const note = document.getElementById('inv-note');

  let NEXT_ID = null;

  function invAmount(i){
    return (i && (i.amount ?? i.value)) ?? 0;
  }

  function isPendingStatus(st){
    st = norm(st);
    return ['pending','pendente'].includes(st);
  }
  function isOverdueStatus(st){
    st = norm(st);
    return ['overdue','atraso','em atraso'].includes(st);
  }
  function isPaidStatus(st){
    st = norm(st);
    return ['paid','pago'].includes(st);
  }
  function isUpcomingStatus(st){
    st = norm(st);
    return ['scheduled','agendada','agendado','upcoming'].includes(st);
  }

  (async () => {
    try{
      const r = await fetch('/?r=api/member/invoices');
      const j = await r.json();
      if(!r.ok) throw new Error(j.error||'Falha ao carregar faturas');

      const inv = Array.isArray(j.invoices) ? j.invoices : [];

      const pend = inv
        .filter(i => (isPendingStatus(i.status) || isOverdueStatus(i.status)) && i.due_date)
        .sort((a,b)=> String(a.due_date||'').localeCompare(String(b.due_date||'')));

      const upcoming = inv
        .filter(i => isUpcomingStatus(i.status))
        .sort((a,b)=> String(a.due_date||'').localeCompare(String(b.due_date||'')));

      const next = pend[0] || upcoming[0] || null;

      if (next){
        nextAmount.textContent = fmtBRL(invAmount(next));
        nextDate.textContent   = next.due_date ? ('Vencimento: ' + fmtDateBR(next.due_date)) : '—';

        const st = norm(next.status);
        nextChip.hidden = false;
        nextChip.className = 'chip ' + (isOverdueStatus(st) ? 'chip-failed' : isPendingStatus(st) ? 'chip-pending' : 'chip-info');
        nextChip.textContent = isOverdueStatus(st) ? 'Em atraso' : isPendingStatus(st) ? 'Pendente' : 'Próxima';

        if (isPendingStatus(st) || isOverdueStatus(st)){
          NEXT_ID = next.id;
          nextActs.hidden = false;
        } else {
          nextActs.hidden = true;
        }
      } else {
        nextAmount.textContent = '—';
        nextDate.textContent   = 'Sem próximas cobranças.';
        nextChip.hidden = true;
        nextActs.hidden = true;
      }

      const pagos = inv.filter(i => isPaidStatus(i.status));
      let last = null;

      if (pagos.length){
        last = pagos
          .map(i => ({...i, _ts: Date.parse(i.paid_at||i.due_date||'1970-01-01') }))
          .sort((a,b)=> b._ts - a._ts)[0];
      }

      if (last){
        lastAmount.textContent = fmtBRL(invAmount(last));
        lastDate.textContent   = (last.paid_at ? ('Pago em: ' + fmtDateBR(last.paid_at)) :
                                  last.due_date ? ('Ref. ' + fmtDateBR(last.due_date)) : '—');
        lastChip.hidden = false;

        if (last.receipt_url){
          btnReceipt.href = last.receipt_url;
          lastActs.hidden = false;
        } else {
          lastActs.hidden = true;
        }
      } else {
        lastAmount.textContent = '—';
        lastDate.textContent   = 'Ainda não há pagamentos.';
        lastChip.hidden = true;
        lastActs.hidden = true;
      }

      note.style.display = 'block';
      note.textContent = 'Dica: acesse “Ver todas” para consultar histórico completo e segunda via.';

    }catch(e){
      nextAmount.textContent = '—';
      nextDate.textContent   = 'Não foi possível carregar.';
      nextChip.hidden = true; nextActs.hidden = true;

      lastAmount.textContent = '—';
      lastDate.textContent   = '—';
      lastChip.hidden = true; lastActs.hidden = true;

      note.style.display = 'block';
      note.textContent = 'Não foi possível carregar suas faturas agora.';
    }
  })();

  btnPayNext?.addEventListener('click', async ()=>{ /* mantém como está no seu projeto */ });
})();
</script>

<style>
:root{
  --rail-w: 168px;
  --rail-gap: 12px;
  --rail-top: calc(var(--topnav-h, 52px) + 12px);
}

/* ===== FIX: impede scroll lateral ===== */
html, body{ max-width:100%; overflow-x:hidden; }

/* SR-only (caso o tema não tenha) */
.sr-only{
  position:absolute !important;
  width:1px !important;height:1px !important;
  padding:0 !important;margin:-1px !important;
  overflow:hidden !important;
  clip:rect(0,0,0,0) !important;
  white-space:nowrap !important;border:0 !important;
}

/* ===== MENU MOBILE (estilo do site: div none/block) ===== */
@media (max-width:900px){
  /* esconde o menu original do dashboard no mobile (pra nunca “nascer aberto”) */
  header.topnav[data-topnav] nav,
  header.topnav nav,
  header.topnav[data-topnav] #menu,
  header.topnav #menu,
  #menu{
    display:none !important;
    visibility:hidden !important;
  }

  /* botão */
  header.topnav[data-topnav] .nav-toggle,
  header.topnav .nav-toggle{
    display:inline-grid !important;
    place-items:center;
    position:relative;
    width:42px;height:42px;
    border:0;
    background:#0000;
    border-radius:8px;
    cursor:pointer;
    z-index:260;
  }
  header.topnav[data-topnav] .nav-toggle__bar,
  header.topnav[data-topnav] .nav-toggle__bar::before,
  header.topnav[data-topnav] .nav-toggle__bar::after,
  header.topnav .nav-toggle__bar,
  header.topnav .nav-toggle__bar::before,
  header.topnav .nav-toggle__bar::after{
    content:"";
    display:block;
    height:2px;
    background:var(--ink, #2C3E50);
    width:22px;
    margin:auto;
    transition:.2s;
    position:relative
  }
  header.topnav[data-topnav] .nav-toggle__bar::before,
  header.topnav .nav-toggle__bar::before{position:absolute;inset:-6px 0 0 0}
  header.topnav[data-topnav] .nav-toggle__bar::after,
  header.topnav .nav-toggle__bar::after{position:absolute;inset: 6px 0 0 0}

  /* overlay menu */
  .dash-mobile-menu{
    display:none;
    position:fixed;
    left:0;right:0;
    top: var(--topnav-h, 68px);
    bottom:0;
    background:#fff;
    z-index:250;
    padding:16px 16px calc(16px + env(safe-area-inset-bottom));
    overflow:auto;-webkit-overflow-scrolling:touch;
    box-shadow:0 12px 24px rgba(0,0,0,.12);
  }
  .dash-mobile-menu.is-open{
    display:flex;
    flex-direction:column;
    gap:12px;
  }
  .dash-mobile-menu a{
    display:block;width:100%;
    text-align:center;
    padding:14px 16px;
    border-radius:14px;
    font-weight:800;
    text-decoration:none;
    color:var(--ink, #2C3E50) !important;
    border:1px solid #e9eef2;
    background:#fff;
    box-shadow:0 8px 30px rgba(0,0,0,.08);
  }

  body.menu-open{ overflow:hidden; }
}

/* ===== Layout rail ===== */
.container.member{
  width: min(92vw, var(--container)) !important;
  margin-inline:auto;
  padding-inline:0;
  overflow:visible;
}

.rail-strip{
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  align-items: start;
}
.rail-main{ min-width:0; }

.rail-sky{
  position: sticky;
  top: var(--rail-top);
  align-self: start;
  z-index: 1;
}
.rail-left{
  position: absolute;
  left: 0;
  margin-left: calc(-1 * (var(--rail-w) + var(--rail-gap)));
}
.rail-right{
  position: absolute;
  right: 0;
  margin-right: calc(-1 * (var(--rail-w) + var(--rail-gap)));
}

.adbox-sky{
  width: var(--rail-w);
  max-width: 100%;
  aspect-ratio: 168/600;
  border:1px solid var(--border-subtle);
  border-radius:12px;
  overflow:hidden;
  background:#ffffff;
  position:relative;
  box-shadow:0 6px 24px rgba(15,23,42,.08);
}
.adbox-sky a{ display:block; width:100%; height:100%; }
.adbox-sky img{ display:block; width:100%; height:100%; object-fit:cover; }

.ad-skeleton{
  position:absolute;
  inset:0;
  background:linear-gradient(90deg, rgba(148,163,184,.18), rgba(148,163,184,.26), rgba(148,163,184,.18));
  background-size:200% 100%;
  animation: adsh 1.1s linear infinite;
}
@keyframes adsh { to { background-position: -200% 0; } }

.glass-card{
  background:#ffffff;
  border:1px solid var(--border-subtle);
  border-radius:18px;
  box-shadow:0 18px 40px rgba(15,23,42,.10);
  padding:20px 20px 18px;
}
.sect-title{
  margin:0 0 10px;
  font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight:800;
  color:var(--ink);
  font-size: clamp(1.2rem, 1rem + 1vw, 1.6rem);
}
.muted{ color:var(--muted); }

.alert{
  margin-top:10px;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid var(--border-subtle);
  background:#f3f4ff;
  color:var(--ink);
  font-weight:600;
}

/* ===== Carteirinha principal ===== */
.ucard{
  border:1px solid var(--border-subtle);
  border-radius:18px;
  padding:14px;
  background:linear-gradient(135deg, #e0f2ff, #ffffff 40%, #f5f6fb 100%);
  box-shadow:0 18px 40px rgba(15,23,42,.10);
}
.uc-top{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding-bottom:8px;
  border-bottom:1px dashed #d4dbe6;
}
.uc-logo{ width:96px; height:auto; }
.uc-code{
  font-size:.8rem;
  font-weight:800;
  padding:4px 10px;
  border-radius:999px;
  border:1px solid #cbd5f5;
  background:#eef2ff;
  color:var(--ink);
}
.uc-body{
  display:grid;
  grid-template-columns:minmax(0,1fr) 172px;
  gap:14px;
  padding-top:10px;
}
@media (max-width: 760px){
  .uc-body{ grid-template-columns:1fr; }
}
.uc-grid{
  display:grid;
  gap:8px;
  grid-template-columns:repeat(2, minmax(0,1fr));
}
@media (max-width: 560px){
  .uc-grid{ grid-template-columns:1fr; }
}
.uc-field{ display:grid; gap:2px; min-width:0; }
.uc-label{
  font-size:.72rem;
  text-transform:uppercase;
  letter-spacing:.04em;
  opacity:.85;
  color:var(--muted);
}
.uc-value{
  font-weight:700;
  line-height:1.26;
  overflow-wrap:anywhere;
  color:var(--ink);
}
.uc-span2{ grid-column:1/-1; }
.uc-nowrap{
  white-space:nowrap;
  overflow:hidden;
  text-overflow:ellipsis;
}
.uc-right{
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:6px;
}
.uc-qr{
  width:148px;
  height:148px;
  border-radius:12px;
  border:1px solid var(--border-subtle);
  background:#ffffff;
  overflow:hidden;
  display:grid;
  place-items:center;
}
.uc-qr img{ width:100%; height:100%; object-fit:contain; }
.uc-qr-caption{ font-size:.8rem; }
.uc-actions{
  display:flex;
  gap:8px;
  margin-top:10px;
  flex-wrap:wrap;
}

/* ===== Dependentes ===== */
.dep-grid{
  display:grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap:12px;
}
@media (max-width: 860px){
  .dep-grid{ grid-template-columns: 1fr; }
}
.dep-card{
  border:1px solid var(--border-subtle);
  border-radius:16px;
  padding:12px;
  background:#ffffff;
  box-shadow:0 12px 28px rgba(15,23,42,.06);
}
.dep-top{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
  padding-bottom:10px;
  border-bottom:1px dashed #e2e8f0;
}
.dep-label{
  font-size:.72rem;
  letter-spacing:.04em;
  text-transform:uppercase;
  color:var(--muted);
}
.dep-value{
  font-weight:900;
  color:var(--ink);
  line-height:1.15;
}
.dep-code{
  font-weight:900;
  font-size:.82rem;
  padding:4px 10px;
  border-radius:999px;
  border:1px solid #cbd5f5;
  background:#eef2ff;
  color:var(--ink);
  white-space:nowrap;
}
.dep-body{
  margin-top:10px;
  display:grid;
  grid-template-columns: minmax(0, 1fr) 130px;
  gap:12px;
  align-items:start;
}
@media (max-width: 560px){
  .dep-body{ grid-template-columns: 1fr; }
}
.dep-fields{ display:grid; gap:6px; }
.dep-row{
  display:flex;
  justify-content:space-between;
  gap:10px;
  font-size:.92rem;
}
.dep-k{ color:var(--muted); font-weight:700; }
.dep-v{ color:var(--ink); font-weight:800; text-align:right; }
.dep-ellipsis{
  max-width: 220px;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.dep-qrbox{
  display:grid;
  justify-items:center;
  gap:6px;
  padding:10px;
  border:1px solid var(--border-subtle);
  border-radius:14px;
  background:#f8fafc;
}
.dep-qrbox img{
  width:112px;
  height:112px;
  border-radius:10px;
  background:#fff;
  border:1px solid var(--border-subtle);
}
.dep-actions{
  display:flex;
  gap:8px;
  margin-top:10px;
  flex-wrap:wrap;
}

/* ===== Faturas ===== */
.inv-quick{
  display:grid;
  grid-template-columns: 1fr 1fr auto;
  gap:12px;
  align-items:stretch;
}
@media (max-width:820px){
  .inv-quick{ grid-template-columns: 1fr; }
  .inv-side-actions{ order:3; }
}
.inv-tile{
  border:1px solid var(--border-subtle);
  border-radius:14px;
  padding:12px 12px 10px;
  background:#ffffff;
  display:flex;
  flex-direction:column;
  gap:6px;
  box-shadow:0 12px 28px rgba(15,23,42,.06);
}
.tile-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
}
.tile-title{ font-weight:800; color:var(--ink); }
.tile-value{ font-size:1.4rem; font-weight:900; color:var(--ink); }
.tile-sub{ font-size:.92rem; color:var(--muted); }
.tile-actions{ margin-top:4px; }
.inv-side-actions{ display:flex; align-items:center; }
.inv-side-actions .btn{ white-space:nowrap; }

/* Chips */
.chip{
  display:inline-block;
  padding:5px 8px;
  border-radius:999px;
  font-size:.82rem;
  border:1px solid #e5e7eb;
  background:#f9fafb;
  color:var(--ink);
}
.chip-info{ background:#e0f2fe; border-color:#bae6fd; color:#075985; }
.chip-success{ background:#dcfce7; border-color:#bbf7d0; color:#166534; }
.chip-pending{ background:#fef9c3; border-color:#fef08a; color:#854d0e; }
.chip-failed{ background:#fee2e2; border-color:#fecaca; color:#b91c1c; }

/* Botões menores */
.btn-sm{ padding:9px 14px; font-size:.95rem; }
.btn-xs{ padding:6px 10px; font-size:.85rem; }

/* Modal */
.modal{
  position:fixed;
  inset:0;
  display:none;
  place-items:center;
  background:rgba(15,23,42,.35);
  z-index:100;
  padding:16px;
}
.modal.is-open{ display:grid; }
.modal-box{ max-width:520px; width:100%; }
.modal-actions{
  display:flex;
  gap:10px;
  justify-content:flex-end;
  margin-top:12px;
}
.qr-big{
  display:grid;
  place-items:center;
  height:340px;
  border-radius:16px;
  background:#f3f4ff;
  border:1px solid var(--border-subtle);
}

@media (max-width: 1020px){
  .rail-sky{ display:none !important; }
}
</style>
