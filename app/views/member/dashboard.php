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

try{
  $pdo = DB::pdo();
  if ($memberId){
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

    $st = $pdo->prepare("SELECT plan_id,status FROM subscriptions WHERE user_id=? ORDER BY id DESC LIMIT 1");
    $st->execute([$memberId]);
    if ($sub = $st->fetch(PDO::FETCH_ASSOC)){
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
  }
}catch(Throwable $e){}

$publicCardUrl = '/?r=site/card&code=' . urlencode($memberCode);

// fallback opcional para os sky banners
$adSkyFallback = $adSkyFallback ?? '/img/ads/160x600-default.png';
?>
<section class="container member dash-member" style="margin-top:18px; overflow:visible">
  <!-- STRIP com rails: os asides ficam “fora” via margem negativa -->
  <div class="rail-strip">
    <!-- Left sky (fora do grid via margem negativa) -->
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

    <!-- CONTEÚDO CENTRAL (DASH) -->
    <div class="rail-main">
      <!-- Carteirinha -->
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
                  <span id="member-code"><?= htmlspecialchars($memberCode) ?></span>
                  <button class="btn btn-xs btn--ghost" type="button" id="btn-copy">Copiar</button>
                </div>
              </div>
            </div>

            <div class="uc-right">
              <div class="uc-qr" aria-label="Código QR">
                <img id="qr-img" alt="QR" width="148" height="148" decoding="async" loading="lazy">
              </div>
              <div class="uc-qr-caption muted">Apresente este QR no parceiro</div>
            </div>
          </div>

          <div class="uc-actions">
            <button class="btn btn-sm" type="button" id="btn-fullqr">Mostrar QR em tela cheia</button>
            <a class="btn btn-sm btn--ghost" id="btn-pdf" href="<?= htmlspecialchars($publicCardUrl) ?>&print=1" target="_blank" rel="noopener">Baixar PDF</a>
          </div>

          <div id="member-alert" class="alert" role="status" aria-live="polite" style="display:none"></div>
        </div>
      </section>

      <!-- Faturas (resumo: próxima + última paga) -->
      <section class="glass-card">
        <h2 class="sect-title">Faturas</h2>

        <div class="inv-quick">
          <!-- Próxima -->
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

          <!-- Último pagamento -->
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

    <!-- Right sky (fora do grid via margem negativa) -->
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
/* ====== SKY ADS (esq/dir) ====== */
(function(){
  // Não busca nem renderiza anúncios em telas até 1020px
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

/* ====== LÓGICA DO DASH (QR/Faturas) ====== */
(function(){
  const publicUrl = location.origin + <?= json_encode($publicCardUrl) ?>;
  const qrBase = 'https://api.qrserver.com/v1/create-qr-code/?margin=0&data=' + encodeURIComponent(publicUrl);
  const small = document.getElementById('qr-img');
  const big   = document.getElementById('qr-img-big');
  if (small) small.src = qrBase + '&size=148x148';
  if (big)   big.src   = qrBase + '&size=320x320';

  document.getElementById('btn-copy')?.addEventListener('click', async ()=>{
    const code = document.getElementById('member-code')?.textContent || '';
    try{
      await navigator.clipboard.writeText(code);
      toast('Código copiado!');
    }catch(_){
      toast('Não foi possível copiar.');
    }
  });

  const m = document.getElementById('qr-modal');
  document.getElementById('btn-fullqr')?.addEventListener('click', ()=> m?.classList.add('is-open'));
  document.getElementById('qr-close')?.addEventListener('click', ()=> m?.classList.remove('is-open'));
  m?.addEventListener('click', (e)=>{ if(e.target === m) m.classList.remove('is-open'); });

  const fmtBRL = v => 'R$ ' + (Number(v||0)).toFixed(2).replace('.', ',');
  const fmtDateBR = d => {
    if(!d) return '—';
    const s = String(d).split('T')[0]||'';
    const [y,m,dd] = s.split('-');
    return y ? `${dd}/${m}/${y}` : '—';
  };
  const norm = s => String(s||'').toLowerCase();

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

  (async () => {
    try{
      const r = await fetch('/?r=api/member/invoices');
      const j = await r.json();
      if(!r.ok) throw new Error(j.error||'Falha ao carregar faturas');

      const inv = Array.isArray(j.invoices) ? j.invoices : [];

      const pend = inv.filter(i => ['pendente','atraso'].includes(norm(i.status)) && i.due_date)
                      .sort((a,b)=> a.due_date.localeCompare(b.due_date));
      const upcoming = inv.filter(i => norm(i.status)==='agendada')
                          .sort((a,b)=> (a.due_date||'').localeCompare(b.due_date||''));
      const next = pend[0] || upcoming[0] || null;

      if (next){
        nextAmount.textContent = fmtBRL(next.amount);
        nextDate.textContent   = next.due_date ? ('Vencimento: ' + fmtDateBR(next.due_date)) : '—';

        const st = norm(next.status);
        nextChip.hidden = false;
        nextChip.className = 'chip ' + (st==='atraso' ? 'chip-failed' : st==='pendente' ? 'chip-pending' : 'chip-info');
        nextChip.textContent = st==='atraso' ? 'Em atraso' : st==='pendente' ? 'Pendente' : 'Próxima';

        if (['pendente','atraso'].includes(st)){
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

      const pagos = inv.filter(i => norm(i.status)==='pago');
      let last = null;
      if (pagos.length){
        last = pagos
          .map(i => ({...i, _ts: Date.parse(i.paid_at||i.due_date||'1970-01-01') }))
          .sort((a,b)=> b._ts - a._ts)[0];
      }
      if (last){
        lastAmount.textContent = fmtBRL(last.amount);
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

  btnPayNext?.addEventListener('click', async ()=>{
    if (!NEXT_ID) return;
    btnPayNext.disabled = true;
    try{
      const r = await fetch('/?r=api/member/invoices/pay', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ id: NEXT_ID })
      });
      const j = await r.json();
      if(!r.ok) throw new Error(j.error||'Falha ao iniciar pagamento');

      const url = j.checkout_url || j.boleto_url || null;
      if (url) window.open(url, '_blank', 'noopener');
      else if (j.pix_copy){ await navigator.clipboard?.writeText(j.pix_copy); toast('PIX copia e cola copiado!'); }
      else toast('Pagamento iniciado.');
    }catch(err){
      toast('Não foi possível iniciar o pagamento.');
    }finally{
      btnPayNext.disabled = false;
    }
  });

  function toast(msg){
    const box = document.getElementById('member-alert');
    if (!box) return;
    box.textContent = msg;
    box.style.display = 'block';
    setTimeout(()=> box.style.display='none', 1600);
  }
})();
</script>

<style>
:root{
  --rail-w: 168px;
  --rail-gap: 12px;
  --rail-top: calc(var(--topnav-h, 52px) + 12px);
}

/* container herdado já limita largura; garantimos overflow visível */
.container.member{
  width: min(92vw, var(--container)) !important;
  margin-inline:auto;
  padding-inline:0;
  overflow:visible;
}

/* strip: 3 col visual (L | MAIN | R), mas os asides “saem” do container */
.rail-strip{
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  align-items: start;
}
.rail-main{ min-width:0; }

/* asides colados nas bordas do container, saindo com margem negativa */
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

/* caixa do anúncio */
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

/* Skeleton */
.ad-skeleton{
  position:absolute;
  inset:0;
  background:linear-gradient(90deg, rgba(148,163,184,.18), rgba(148,163,184,.26), rgba(148,163,184,.18));
  background-size:200% 100%;
  animation: adsh 1.1s linear infinite;
}
@keyframes adsh { to { background-position: -200% 0; } }

/* ===== Cards desta página ===== */
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

/* Alerta pequeno */
.alert{
  margin-top:10px;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid var(--border-subtle);
  background:#f3f4ff;
  color:var(--ink);
  font-weight:600;
}

/* ===== Carteirinha (uc-*) ===== */
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
.uc-logo{
  width:96px;
  height:auto;
}
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
.uc-field{
  display:grid;
  gap:2px;
  min-width:0;
}
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
.uc-qr img{
  width:100%;
  height:100%;
  object-fit:contain;
}
.uc-qr-caption{
  font-size:.8rem;
}

.uc-actions{
  display:flex;
  gap:8px;
  margin-top:10px;
  flex-wrap:wrap;
}

/* ===== Faturas (resumo) ===== */
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
.tile-title{
  font-weight:800;
  color:var(--ink);
}
.tile-value{
  font-size:1.4rem;
  font-weight:900;
  color:var(--ink);
}
.tile-sub{
  font-size:.92rem;
  color:var(--muted);
}
.tile-actions{ margin-top:4px; }

.inv-side-actions{
  display:flex;
  align-items:center;
}
.inv-side-actions .btn{
  white-space:nowrap;
}

/* Chips (coloridos mas legíveis no claro) */
.chip{
  display:inline-block;
  padding:5px 8px;
  border-radius:999px;
  font-size:.82rem;
  border:1px solid #e5e7eb;
  background:#f9fafb;
  color:var(--ink);
}
.chip-info{
  background:#e0f2fe;
  border-color:#bae6fd;
  color:#075985;
}
.chip-success{
  background:#dcfce7;
  border-color:#bbf7d0;
  color:#166534;
}
.chip-pending{
  background:#fef9c3;
  border-color:#fef08a;
  color:#854d0e;
}
.chip-failed{
  background:#fee2e2;
  border-color:#fecaca;
  color:#b91c1c;
}

/* Botões menores desta página */
.btn-sm{
  padding:9px 14px;
  font-size:.95rem;
}
.btn-xs{
  padding:6px 10px;
  font-size:.85rem;
}

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
.modal-box{
  max-width:520px;
  width:100%;
}
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

/* Responsivo: esconder rails em telas pequenas */
@media (max-width: 1020px){
  .rail-sky{ display:none !important; }
}
</style>
