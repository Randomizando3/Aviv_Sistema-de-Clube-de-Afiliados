<?php
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../services/Affiliate.php';

use App\services\Affiliate;

// Base URL segura
$https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
       || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443)
       || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$scheme = $https ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
$BASE   = rtrim("$scheme://$host", '/');

// Code/link (fallback se controller não enviá-los)
$me   = \Auth::user();
$code = isset($code) && is_string($code) && $code !== '' ? $code : ($me ? Affiliate::getOrCreateCode((int)$me['id']) : '');
$link = $code ? ($BASE . '/?ref=' . rawurlencode($code)) : ($BASE . '/');
?>
<section class="container admin affiliate-links" style="margin-top:18px">
  <section class="admin-main">

    <!-- Link geral -->
    <div class="glass-card" style="padding:16px 18px">
      <h1 class="sect-title" style="margin:0">Afiliados • Meus links</h1>
      <p class="muted" style="margin:6px 0 0">Use seu link abaixo ou gere uma arte personalizada.</p>
    </div>

    <div class="glass-card" style="margin-top:12px">
      <h2 class="sect-sub" style="margin-bottom:8px">Link geral</h2>
      <div class="ref-row">
        <label class="ref-label">Código</label>
        <div class="ref-input">
          <input id="aff-code" value="<?= htmlspecialchars($code) ?>" readonly>
          <button class="btn btn-sm" data-copy="#aff-code">Copiar</button>
        </div>
      </div>
      <div class="ref-row" style="margin-top:10px">
        <label class="ref-label">Link</label>
        <div class="ref-input">
          <input id="aff-link" value="<?= htmlspecialchars($link) ?>" readonly>
          <button class="btn btn-sm" data-copy="#aff-link">Copiar</button>
          <a class="btn btn-sm btn--ghost" target="_blank" rel="noopener" href="<?= htmlspecialchars($link) ?>">Abrir</a>
        </div>
      </div>
    </div>

    <!-- EDITOR 2/3 • PREVIEW 1/3 -->
    <div class="editor-grid" style="margin-top:12px">
      <!-- CONTROLES (2/3) -->
      <div class="glass-card editor-controls">
        <div class="tabs">
          <button class="tab-btn current" data-tab="size">Fundo & Tamanho</button>
          <button class="tab-btn" data-tab="text">Texto</button>
          <button class="tab-btn" data-tab="sec">Texto Secundário</button>
          <button class="tab-btn" data-tab="logo">Logo</button>
          <button class="tab-btn" data-tab="export">Exportar</button>
        </div>

        <!-- Painel: Fundo & Tamanho -->
        <div class="tab-panel" data-panel="size" style="display:block">
          <div class="group">
            <div class="group-title">Tamanho</div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Largura</label><input id="w" class="field" type="number" min="100" max="6000" step="10" value="1080"></div>
              <div class="input-wrap"><label class="ref-label">Altura</label><input id="h" class="field" type="number" min="100" max="6000" step="10" value="1080"></div>
              <div class="input-wrap">
                <label class="ref-label">Escala PNG</label>
                <div class="select-wrap">
                  <select id="scale" class="field select">
                    <option value="1">1×</option>
                    <option value="2" selected>2×</option>
                    <option value="3">3×</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="actions" style="margin-top:8px">
              <button id="applyWH" class="btn btn-sm" type="button">Aplicar tamanho</button>
              <button id="resetPos" class="btn btn-sm btn--ghost" type="button">Centralizar elementos</button>
            </div>
          </div>

          <div class="group">
            <div class="group-title">Fundo</div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Cor 1</label><input id="bg1" class="field" type="color" value="#0e1f33"></div>
              <div class="input-wrap"><label class="ref-label">Cor 2</label><input id="bg2" class="field" type="color" value="#0a1a2b"></div>
              <div class="input-wrap"><label class="ref-label">Gradiente</label><label class="switch"><input id="useGrad" type="checkbox" checked><span></span></label></div>
            </div>
            <div class="row-3" style="margin-top:6px">
              <div class="input-wrap"><label class="ref-label">Raio borda</label><input id="radius" class="field" type="range" min="0" max="160" value="28"></div>
              <div class="input-wrap"><label class="ref-label">Cartão interno</label><label class="switch"><input id="useCard" type="checkbox" checked><span></span></label></div>
              <div class="input-wrap"><label class="ref-label">Opacidade cartão</label><input id="cardAlpha" class="field" type="range" min="0" max="100" value="8"></div>
            </div>
            <div class="row-3" style="margin-top:6px">
              <div class="input-wrap"><label class="ref-label">Mostrar grade</label><label class="switch"><input id="showGrid" type="checkbox"><span></span></label></div>
              <div class="input-wrap"><label class="ref-label">Área segura</label><label class="switch"><input id="safeArea" type="checkbox" checked><span></span></label></div>
            </div>
          </div>
        </div>

        <!-- Painel: Texto (primário) -->
        <div class="tab-panel" data-panel="text" style="display:none">
          <div class="group">
            <div class="group-title">Texto principal</div>
            <div class="input-wrap"><label class="ref-label">Conteúdo</label><input id="txt" class="field" type="text" value="Benefícios, cupons e muito mais"></div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Tamanho</label><input id="txtSize" class="field" type="range" min="10" max="300" value="96"></div>
              <div class="input-wrap"><label class="ref-label">Largura máx. (%)</label><input id="txtMax" class="field" type="range" min="30" max="95" value="80"></div>
              <div class="input-wrap">
                <label class="ref-label">Alinhamento</label>
                <div class="select-wrap">
                  <select id="align" class="field select">
                    <option value="left">Esquerda</option>
                    <option value="center" selected>Centro</option>
                    <option value="right">Direita</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Peso</label><div class="select-wrap"><select id="weight" class="field select"><option>600</option><option selected>800</option><option>900</option></select></div></div>
              <div class="input-wrap"><label class="ref-label">Cor</label><input id="txtColor" class="field" type="color" value="#ffffff"></div>
              <div class="input-wrap"><label class="ref-label">Sombra</label><label class="switch"><input id="shadow" type="checkbox" checked><span></span></label></div>
            </div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Blur</label><input id="shBlur" class="field" type="range" min="0" max="50" value="16"></div>
              <div class="input-wrap"><label class="ref-label">Opacidade</label><input id="shAlpha" class="field" type="range" min="0" max="100" value="30"></div>
            </div>
            <div class="help small">Arraste no canvas (agora livre em X e Y). Segure <strong>Shift</strong> para travar automaticamente no eixo do movimento.</div>
          </div>
        </div>

        <!-- Painel: Texto Secundário -->
        <div class="tab-panel" data-panel="sec" style="display:none">
          <div class="group">
            <div class="group-title">Texto secundário</div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Ativar</label><label class="switch"><input id="secOn" type="checkbox"><span></span></label></div>
              <div class="input-wrap"><label class="ref-label">Cor</label><input id="secColor" class="field" type="color" value="#e3efff"></div>
              <div class="input-wrap"><label class="ref-label">Peso</label><div class="select-wrap"><select id="secWeight" class="field select"><option>400</option><option selected>600</option><option>700</option></select></div></div>
            </div>
            <div class="input-wrap" style="margin-top:6px"><label class="ref-label">Conteúdo</label><input id="secTxt" class="field" type="text" placeholder="Escreva aqui…"></div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Tamanho</label><input id="secSize" class="field" type="range" min="8" max="200" value="48"></div>
              <div class="input-wrap"><label class="ref-label">Largura máx. (%)</label><input id="secMax" class="field" type="range" min="30" max="95" value="80"></div>
              <div class="input-wrap">
                <label class="ref-label">Alinhamento</label>
                <div class="select-wrap"><select id="secAlign" class="field select"><option value="left">Esquerda</option><option value="center" selected>Centro</option><option value="right">Direita</option></select></div>
              </div>
            </div>
            <div class="row-3">
              <div class="input-wrap"><label class="ref-label">Sombra</label><label class="switch"><input id="secShadow" type="checkbox" checked><span></span></label></div>
              <div class="input-wrap"><label class="ref-label">Blur</label><input id="secBlur" class="field" type="range" min="0" max="40" value="10"></div>
              <div class="input-wrap"><label class="ref-label">Opacidade</label><input id="secAlpha" class="field" type="range" min="0" max="100" value="25"></div>
            </div>
            <div class="help small">Arraste o texto secundário no canvas para posicionar.</div>
          </div>
        </div>

        <!-- Painel: Logo -->
        <div class="tab-panel" data-panel="logo" style="display:none">
          <div class="group">
            <div class="group-title">Logo</div>

            <div class="row-3">
              <div class="input-wrap">
                <label class="ref-label">Exibir</label>
                <label class="switch"><input id="logoOn" type="checkbox" checked><span></span></label>
              </div>
              <div class="input-wrap"><label class="ref-label">Tamanho (%)</label><input id="logoScale" class="field" type="range" min="10" max="200" value="60"></div>
              <div class="input-wrap"><label class="ref-label">Opacidade</label><input id="logoAlpha" class="field" type="range" min="0" max="100" value="100"></div>
            </div>

            <div class="row-3" style="margin-top:6px; align-items:end">
              <div class="input-wrap">
                <label class="ref-label">Estilo</label>
                <div class="select-wrap">
                  <select id="logoStyle" class="field select">
                    <option value="white" selected>Branco (padrão)</option>
                    <option value="color">Colorido</option>
                    <option value="custom">URL customizada</option>
                  </select>
                </div>
              </div>
              <div class="input-wrap" style="grid-column: span 2;">
                <label class="ref-label">URL (se custom)</label>
                <input id="logoUrl" class="field" type="url" placeholder="/img/logo-aviv-plus.png" disabled>
              </div>
            </div>

            <div class="help small">Dica: arraste o logo no canvas para posicionar.</div>
          </div>
        </div>

        <!-- Painel: Exportar -->
        <div class="tab-panel" data-panel="export" style="display:none">
          <div class="group">
            <div class="group-title">Exportar</div>
            <div class="row-3">
              <div class="input-wrap">
                <label class="ref-label">Escala PNG</label>
                <div class="select-wrap">
                  <select id="scale2" class="field select">
                    <option value="1">1×</option>
                    <option value="2" selected>2×</option>
                    <option value="3">3×</option>
                  </select>
                </div>
              </div>
              <div class="input-wrap">
                <label class="ref-label">Gerar HTML</label>
                <label class="switch"><input id="htmlEmbedData" type="checkbox" checked><span></span></label>
                <div class="help small">Se ligado, o &lt;img&gt; usa dataURL embutido.</div>
              </div>
            </div>
            <div class="actions" style="margin-top:8px">
              <button id="btn-download" class="btn btn-sm" type="button">Baixar PNG</button>
              <button id="btn-genhtml" class="btn btn-sm btn--ghost" type="button">Gerar HTML</button>
            </div>
            <div id="html-wrap" style="display:none; margin-top:10px">
              <label class="ref-label">Código HTML</label>
              <textarea id="html-code" class="field" rows="6" spellcheck="false"></textarea>
              <div class="actions" style="margin-top:8px">
                <button id="copy-html" class="btn btn-sm" type="button">Copiar HTML</button>
                <button id="save-html" class="btn btn-sm btn--ghost" type="button">Baixar HTML</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PREVIEW (1/3) -->
      <div class="glass-card preview-card">
        <div class="row-3">
          <div class="input-wrap" style="grid-column: span 3;">
            <label class="ref-label">Zoom da pré-visualização</label>
            <input id="pvZoom" class="field" type="range" min="30" max="100" value="90">
          </div>
        </div>
        <div class="preview-wrap">
          <canvas id="canvas" width="1080" height="1080"></canvas>
        </div>
        <div class="help small" style="margin-top:8px">Arraste elementos no canvas (livre em X e Y). Com <strong>Shift</strong>, travo automaticamente no eixo do movimento.</div>
      </div>
    </div>
  </section>
</section>

<script>
// ===== util =====
const $ = s => document.querySelector(s);
function clamp(v,min,max){ return Math.max(min, Math.min(max, v)); }
function loadImage(url){ return new Promise(res=>{ if(!url){res(null);return;} const i=new Image(); i.crossOrigin='anonymous'; i.onload=()=>res(i); i.onerror=()=>res(null); i.src=url; }); }
function lsGet(k, d){ try{ const v=localStorage.getItem(k); return v?JSON.parse(v):d; }catch(_){ return d; } }
function lsSet(k, v){ try{ localStorage.setItem(k, JSON.stringify(v)); }catch(_){} }

// logos (corrigido)
const LOGOS = {
  white: '/img/logo-aviv-plus.png', // branco (padrão)
  color: '/img/logo.png'            // colorido
};

const LINK = <?= json_encode($link, JSON_UNESCAPED_SLASHES) ?>;

// ===== state =====
const S = lsGet('aff_banner_state_v4', {
  w:1080, h:1080,
  bg1:'#0e1f33', bg2:'#0a1a2b', useGrad:true,
  radius:28, useCard:true, cardAlpha:0.08,
  showGrid:false, safeArea:true,
  text:{ value:'Benefícios, cupons e muito mais', x:0.5, y:0.44, size:96, maxPct:80, align:'center', color:'#ffffff', weight:'800', shadow:true, shBlur:16, shAlpha:0.30 },
  sec :{ on:false, value:'', x:0.5, y:0.58, size:48, maxPct:80, align:'center', color:'#e3efff', weight:'600', shadow:true, shBlur:10, shAlpha:0.25 },
  logo:{ url:LOGOS.white, style:'white', on:true, x:0.16, y:0.5, scalePct:60, alpha:1.0 },
  pvZoom: 0.9
});
let LOGO_IMG = null;

const canvas = $('#canvas'), ctx = canvas.getContext('2d');

// ===== Tabs =====
document.querySelectorAll('.tab-btn').forEach(b=>{
  b.addEventListener('click', ()=>{
    document.querySelectorAll('.tab-btn').forEach(x=>x.classList.remove('current'));
    document.querySelectorAll('.tab-panel').forEach(p=>p.style.display='none');
    b.classList.add('current');
    const id = b.getAttribute('data-tab');
    document.querySelector(`.tab-panel[data-panel="${id}"]`).style.display='block';
  });
});

// ===== UI <-> State =====
function bind(id, path, transform= v=>v){
  const el = $(id);
  const get = () => path.reduce((o,k)=>o[k], S);
  const set = (val) => {
    let o=S;
    for(let i=0;i<path.length-1;i++){ o=o[path[i]]; }
    o[path.at(-1)] = transform(val);
    scheduleDraw();
    lsSet('aff_banner_state_v4', S);
  };
  // initial
  if (el.type === 'checkbox') el.checked = !!get();
  else if (el.type === 'range' || el.type === 'number' || el.tagName==='SELECT' || el.type==='color' || el.type==='text' || el.type==='url') el.value = get();

  const ev = (el.type==='text'||el.type==='url') ? 'change':'input';
  el.addEventListener(ev, ()=>{
    const v = (el.type==='checkbox') ? el.checked
            : (el.type==='number'||el.type==='range') ? +el.value
            : el.value;
    set(v);
    if (id==='#logoStyle') syncLogoUrlFromStyle();
    if (id==='#logoUrl') loadLogo();
  });
}

bind('#w', ['w'], v=>clamp(+v,100,6000));
bind('#h', ['h'], v=>clamp(+v,100,6000));
bind('#bg1', ['bg1']);
bind('#bg2', ['bg2']);
bind('#useGrad', ['useGrad']);
bind('#radius', ['radius'], v=>clamp(+v,0,160));
bind('#useCard', ['useCard']);
bind('#cardAlpha', ['cardAlpha'], v=>clamp(+v,0,100)/100);
bind('#showGrid', ['showGrid']);
bind('#safeArea', ['safeArea']);

bind('#txt', ['text','value']);
bind('#weight', ['text','weight']);
bind('#txtColor', ['text','color']);
bind('#txtSize', ['text','size'], v=>clamp(+v,10,300));
bind('#txtMax', ['text','maxPct'], v=>clamp(+v,30,95));
bind('#align', ['text','align']);
bind('#shadow', ['text','shadow']);
bind('#shBlur', ['text','shBlur'], v=>clamp(+v,0,50));
bind('#shAlpha', ['text','shAlpha'], v=>clamp(+v,0,100)/100);

bind('#secOn', ['sec','on']);
bind('#secTxt', ['sec','value']);
bind('#secColor', ['sec','color']);
bind('#secWeight', ['sec','weight']);
bind('#secSize', ['sec','size'], v=>clamp(+v,8,200));
bind('#secMax', ['sec','maxPct'], v=>clamp(+v,30,95));
bind('#secAlign', ['sec','align']);
bind('#secShadow', ['sec','shadow']);
bind('#secBlur', ['sec','shBlur'], v=>clamp(+v,0,40));
bind('#secAlpha', ['sec','shAlpha'], v=>clamp(+v,0,100)/100);

bind('#logoOn', ['logo','on']);
bind('#logoScale', ['logo','scalePct'], v=>clamp(+v,10,200));
bind('#logoAlpha', ['logo','alpha'], v=>clamp(+v,0,100)/100);
bind('#logoStyle', ['logo','style']);
bind('#logoUrl', ['logo','url']);

$('#applyWH').addEventListener('click', ()=>{ canvas.width = S.w; canvas.height = S.h; scheduleDraw(true); });
$('#resetPos').addEventListener('click', ()=>{
  S.text.x = 0.5; S.text.y = 0.44;
  S.sec.x  = 0.5; S.sec.y  = 0.58;
  S.logo.x = 0.16; S.logo.y = 0.5;
  scheduleDraw(true);
});

$('#pvZoom').addEventListener('input', (e)=>{
  const v = clamp(+e.target.value, 30, 100)/100;
  S.pvZoom = v; applyPreviewZoom(); lsSet('aff_banner_state_v4', S);
});

function applyPreviewZoom(){
  const wrap = document.querySelector('.preview-wrap');
  wrap.style.setProperty('--zoom', S.pvZoom.toString());
}

// logo: estilo -> URL
function syncLogoUrlFromStyle(){
  if (S.logo.style === 'white') S.logo.url = LOGOS.white;
  else if (S.logo.style === 'color') S.logo.url = LOGOS.color;
  $('#logoUrl').disabled = (S.logo.style !== 'custom');
  loadLogo();
}

// Copiar
function copySelector(sel){
  try{ const el=document.querySelector(sel); if(!el) return; el.select(); el.setSelectionRange(0,99999); document.execCommand('copy'); }
  catch(e){ try{ navigator.clipboard && navigator.clipboard.writeText(document.querySelector(sel).value); }catch(_){ } }
}
document.addEventListener('click', (ev)=>{
  const b=ev.target.closest('[data-copy]'); if(!b) return;
  ev.preventDefault(); copySelector(b.getAttribute('data-copy'));
  const old=b.textContent; b.textContent='Copiado!'; setTimeout(()=>b.textContent=old,900);
});

// ===== draw =====
let raf=0;
function scheduleDraw(now=false){ if(now){draw(); return;} if(raf) cancelAnimationFrame(raf); raf=requestAnimationFrame(draw); }

function drawRoundedRect(x,y,w,h,r){ const rr=Math.min(r,w/2,h/2); ctx.beginPath(); ctx.moveTo(x+rr,y); ctx.arcTo(x+w,y,x+w,y+h,rr); ctx.arcTo(x+w,y+h,x,y+h,rr); ctx.arcTo(x,y+h,x,y,rr); ctx.arcTo(x,y,x+w,y,rr); ctx.closePath(); }

function draw(){
  raf=0;
  const {w,h} = S;
  if (canvas.width!==w || canvas.height!==h){ canvas.width=w; canvas.height=h; }

  // Fundo
  if (S.useGrad){
    const g=ctx.createLinearGradient(0,0,w,h);
    g.addColorStop(0,S.bg1); g.addColorStop(1,S.bg2);
    ctx.fillStyle=g;
  } else { ctx.fillStyle=S.bg1; }
  ctx.fillRect(0,0,w,h);

  // Moldura/Cartão
  const pad = Math.round(Math.min(w,h)*0.06);
  const radius = S.radius;
  const cx = pad, cy=pad, cw=w-pad*2, ch=h-pad*2;

  drawRoundedRect(cx,cy,cw,ch,radius);
  ctx.strokeStyle = 'rgba(255,255,255,.18)';
  ctx.lineWidth=Math.max(1,Math.round(cw*0.003));
  ctx.stroke();

  if (S.useCard){
    drawRoundedRect(cx,cy,cw,ch,radius);
    ctx.fillStyle = `rgba(255,255,255,${S.cardAlpha})`; ctx.fill();
    ctx.strokeStyle = 'rgba(255,255,255,.10)'; ctx.lineWidth=1; ctx.stroke();
  }

  // Área segura
  const safePad = S.safeArea ? Math.round(Math.min(w,h)*0.11) : pad;
  const sx = safePad, sy=safePad, sw=w-safePad*2, sh=h-safePad*2;

  // Grade
  if (S.showGrid){
    ctx.save(); ctx.strokeStyle='rgba(255,255,255,.10)'; ctx.lineWidth=1;
    for(let i=1;i<4;i++){
      const gx = sx + (sw/4)*i;
      const gy = sy + (sh/4)*i;
      ctx.beginPath(); ctx.moveTo(gx, sy); ctx.lineTo(gx, sy+sh); ctx.stroke();
      ctx.beginPath(); ctx.moveTo(sx, gy); ctx.lineTo(sx+sw, gy); ctx.stroke();
    }
    ctx.restore();
  }

  // LOGO
  if (S.logo.on && LOGO_IMG){
    const rh = (S.logo.scalePct/100)*sh;
    const ratio = LOGO_IMG.width / LOGO_IMG.height;
    const lw = rh*ratio, lh = rh;
    const x = clamp(S.logo.x*w - lw/2, sx, sx+sw-lw);
    const y = clamp(S.logo.y*h - lh/2, sy, sy+sh-lh);
    ctx.globalAlpha = S.logo.alpha;
    ctx.drawImage(LOGO_IMG, x, y, lw, lh);
    ctx.globalAlpha = 1;
    BBOX.logo = {x,y,w:lw,h:lh};
  } else { BBOX.logo = null; }

  // TEXTO PRINCÁRIO
  drawTextBlock('text', sx, sy, sw, sh);

  // TEXTO SECUNDÁRIO
  if (S.sec.on && (S.sec.value||'').trim()!==''){
    drawTextBlock('sec', sx, sy, sw, sh);
  } else { BBOX.sec = null; }
}

function drawTextBlock(key, sx, sy, sw, sh){
  const T = S[key];
  ctx.fillStyle=T.color;
  ctx.textBaseline='top';
  ctx.font = `${T.weight} ${T.size}px system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif`;

  const maxW = sw * (T.maxPct/100);
  const lh = Math.round(T.size*1.12);
  const lines = wrapText(ctx, T.value, maxW, lh);
  const totalH = lines.length * lh;

  let tx = T.x*S.w, ty = T.y*S.h - totalH/2;
  tx = clamp(tx, sx, sx+sw);
  ty = clamp(ty, sy, sy+sh-totalH);

  ctx.textAlign = T.align;
  if (T.shadow){
    ctx.shadowColor = `rgba(0,0,0,${T.shAlpha})`;
    ctx.shadowBlur = T.shBlur;
    ctx.shadowOffsetX = 0; ctx.shadowOffsetY = Math.round(T.shBlur/3);
  } else { ctx.shadowColor='transparent'; ctx.shadowBlur=0; }

  let alignX = tx;
  if (T.align==='left')  alignX = clamp(tx, sx, sx+sw-maxW);
  if (T.align==='right') alignX = clamp(tx, sx+maxW, sx+sw);

  lines.forEach((t,i)=>{
    const yy = ty + i*lh;
    ctx.fillText(t, alignX, yy, maxW);
  });
  ctx.shadowBlur=0;

  const maxLineW = Math.min(maxW, Math.max(...lines.map(t=>ctx.measureText(t).width)));
  let left = alignX;
  if (T.align==='center') left = alignX - maxLineW/2;
  if (T.align==='right')  left = alignX - maxLineW;
  BBOX[key] = {x:left, y:ty, w:maxLineW, h:totalH};
}

function wrapText(ctx, text, maxWidth, lineHeight){
  const words = String(text||'').split(/\s+/).filter(Boolean);
  const lines=[]; let line='';
  for(let i=0;i<words.length;i++){
    const test = line ? line+' '+words[i] : words[i];
    if (ctx.measureText(test).width > maxWidth && line){
      lines.push(line); line=words[i];
    } else { line=test; }
  }
  if (line) lines.push(line);
  return lines;
}

// ===== drag (livre em X e Y; Shift trava eixo) =====
const BBOX = { text:null, sec:null, logo:null };
let dragging = null; // 'text' | 'sec' | 'logo'
let dragOff = {x:0, y:0};
let dragStart = null; // pointer inicial
let moveAxis = null;  // 'x' | 'y' | null

function hit(pt, box){ return box && pt.x>=box.x && pt.x<=box.x+box.w && pt.y>=box.y && pt.y<=box.y+box.h; }
function getPointer(evt){
  const r=canvas.getBoundingClientRect();
  const x = (evt.touches?evt.touches[0].clientX:evt.clientX) - r.left;
  const y = (evt.touches?evt.touches[0].clientY:evt.clientY) - r.top;
  const scaleX = canvas.width / r.width, scaleY = canvas.height / r.height;
  return { x:x*scaleX, y:y*scaleY };
}
function onDown(e){
  const p = getPointer(e);
  if (BBOX.sec && hit(p,BBOX.sec)) dragging='sec';
  else if (BBOX.text && hit(p,BBOX.text)) dragging='text';
  else if (BBOX.logo && hit(p,BBOX.logo)) dragging='logo';
  else return;

  const b = BBOX[dragging];
  dragOff.x=p.x-(b.x+b.w/2); dragOff.y=p.y-(b.y+b.h/2);
  dragStart = {x:p.x, y:p.y};
  moveAxis = null;
  e.preventDefault();
}
function onMove(e){
  if (!dragging) return;
  const p = getPointer(e);
  const cx = p.x - dragOff.x, cy = p.y - dragOff.y;

  if (e.shiftKey){
    const dx = Math.abs(p.x - (dragStart?.x ?? p.x));
    const dy = Math.abs(p.y - (dragStart?.y ?? p.y));
    moveAxis = moveAxis || (dx > dy ? 'x' : 'y');
  } else {
    moveAxis = null;
  }

  const nx = clamp(cx/canvas.width, 0, 1);
  const ny = clamp(cy/canvas.height, 0, 1);

  if (dragging==='logo'){
    if (!moveAxis || moveAxis==='x') S.logo.x = nx;
    if (!moveAxis || moveAxis==='y') S.logo.y = ny;
  } else {
    const T = S[dragging];
    if (!moveAxis || moveAxis==='x') T.x = nx;
    if (!moveAxis || moveAxis==='y') T.y = ny;
  }
  scheduleDraw();
  e.preventDefault();
}
function onUp(){ dragging=null; moveAxis=null; dragStart=null; lsSet('aff_banner_state_v4', S); }

canvas.addEventListener('mousedown', onDown);
canvas.addEventListener('mousemove', onMove);
window.addEventListener('mouseup', onUp);
canvas.addEventListener('touchstart', onDown, {passive:false});
canvas.addEventListener('touchmove', onMove, {passive:false});
canvas.addEventListener('touchend', onUp);

// ===== logo loader =====
async function loadLogo(){ LOGO_IMG = await loadImage(S.logo.url); scheduleDraw(true); }
syncLogoUrlFromStyle();
loadLogo();

// ===== preview zoom =====
applyPreviewZoom();

// ===== inicial =====
canvas.width=S.w; canvas.height=S.h;
scheduleDraw(true);

// ===== download PNG =====
$('#btn-download').addEventListener('click', ()=>{
  const scale = clamp(+($('#scale2').value || $('#scale').value) || 2, 1, 4);
  const exp = document.createElement('canvas');
  exp.width = S.w*scale; exp.height = S.h*scale;
  const ex = exp.getContext('2d');
  ex.scale(scale, scale);
  draw();
  ex.drawImage(canvas, 0, 0);
  const a=document.createElement('a');
  a.download=`banner-aviv-${Date.now()}.png`;
  a.href=exp.toDataURL('image/png');
  a.click();
});

// ===== gerar HTML =====
$('#btn-genhtml').addEventListener('click', ()=>{
  const useData = $('#htmlEmbedData').checked;
  draw();
  const dataURL = canvas.toDataURL('image/png');
  const src = useData ? dataURL : dataURL; // pode trocar por URL hospedada
  const html =
`<a href="<?= htmlspecialchars($link) ?>" target="_blank" rel="noopener">
  <img src="${src}" alt="Aviv+ — Benefícios, cupons e muito mais" width="${S.w}" height="${S.h}" style="display:block;border:0">
</a>`;
  $('#html-code').value = html;
  $('#html-wrap').style.display = 'block';
});

$('#copy-html').addEventListener('click', ()=>{
  const ta = $('#html-code');
  ta.select(); ta.setSelectionRange(0, 999999);
  document.execCommand('copy');
});

$('#save-html').addEventListener('click', ()=>{
  const blob = new Blob([$('#html-code').value], {type:'text/html;charset=utf-8'});
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = `banner-aviv-${Date.now()}.html`;
  a.click();
  URL.revokeObjectURL(url);
});
</script>

<style>
/* ——— estilo roxinho/admin ——— */
.muted{ color:#cfe1ff; opacity:.88; }
.sect-title{ font-weight:800; color:#fff; }
.sect-sub{ font-weight:800; color:#fff; margin:0; }
.glass-card{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); padding:14px; border-radius:14px; color:#fff; }

.btn{ display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:999px; background:#fff; color:#0e253b; font-weight:800; border:0; text-decoration:none; cursor:pointer; }
.btn--ghost{ background:rgba(255,255,255,.14); color:#fff; border:1px solid rgba(255,255,255,.24); }
.btn.btn-sm{ padding:9px 14px; font-size:.95rem; box-shadow:0 8px 18px rgba(0,0,0,.18); }
.actions{ display:flex; gap:10px; flex-wrap:wrap; }

/* Link inputs */
.ref-row{ display:grid; gap:6px; }
.ref-label{ font-weight:800; opacity:.95 }
.ref-input{ display:flex; gap:10px; align-items:center; }
.ref-input input{
  flex:1; border-radius:999px; border:1px solid rgba(255,255,255,.18);
  background:rgba(255,255,255,.07); color:#fff; padding:10px 12px; font-weight:800;
}

/* Editor grid 2/3 + 1/3 */
.editor-grid{ display:grid; gap:12px; grid-template-columns: 2fr 1fr; align-items:start; }
@media (max-width: 1200px){ .editor-grid{ grid-template-columns:1fr; } }

/* Reordenar no mobile: preview primeiro, depois controles */
@media (max-width: 900px){
  .preview-card{ order:-1; }
}

/* Tabs */
.tabs{ display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px; }
/* rolável no mobile */
@media (max-width: 720px){
  .tabs{ flex-wrap:nowrap; overflow:auto; -webkit-overflow-scrolling:touch; }
  .tab-btn{ flex:0 0 auto; }
}
.tab-btn{
  padding:8px 12px; border-radius:10px; border:1px solid rgba(186,126,255,.35);
  background:#281B3E; color:#f1e9ff; cursor:pointer; font-weight:700;
}
.tab-btn.current{ outline:2px solid rgba(186,126,255,.45); outline-offset:2px; }

/* Groups */
.group{ background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.10); border-radius:12px; padding:12px; }
.group + .group{ margin-top:10px; }
.group-title{ font-weight:800; margin:0 0 8px; opacity:.95; }
.help.small{ font-size:.85rem; opacity:.8; margin-top:6px }

/* Controls layout */
.row-3{ display:grid; gap:8px; grid-template-columns:1fr 1fr 1fr; }
@media (max-width: 900px){ .row-3{ grid-template-columns:1fr; } }
.input-wrap{ display:grid; gap:6px; }
.field{
  width:100%; box-sizing:border-box; padding:10px 12px; border-radius:10px;
  border:1px solid rgba(255,255,255,.20); background:rgba(255,255,255,.08); color:#eaf3ff; outline:none;
}

/* Color inputs — mostram swatch corretamente */
.field[type="color"]{
  padding:0; height:38px; min-height:38px; cursor:pointer;
  background:transparent;
}
.field[type="color"]::-webkit-color-swatch-wrapper{ padding:0; }
.field[type="color"]::-webkit-color-swatch{ border:none; border-radius:8px; }
.field[type="color"]::-moz-color-swatch{ border:none; border-radius:8px; }

/* Select roxinho local do editor */
.select-wrap{ position:relative; }
.select{
  appearance:none; -webkit-appearance:none; -moz-appearance:none;
  background:#281B3E; border:1px solid rgba(186,126,255,.35); color:#f1e9ff; padding-right:38px;
}
.select-wrap::after{
  content:""; position:absolute; right:12px; top:50%; width:0; height:0; pointer-events:none;
  border-left:6px solid transparent; border-right:6px solid transparent; border-top:7px solid #f1e9ff; transform:translateY(-50%);
}

/* Switch */
.switch{ position:relative; display:inline-block; width:46px; height:26px; }
.switch input{ display:none; }
.switch span{
  position:absolute; cursor:pointer; inset:0; background:#2b2240; border:1px solid rgba(186,126,255,.35);
  border-radius:999px; transition:.2s;
}
.switch span:before{
  content:""; position:absolute; height:20px; width:20px; left:3px; top:50%; transform:translateY(-50%);
  background:#fff; border-radius:50%; transition:.2s;
}
.switch input:checked + span{ background:#6f43c5; }
.switch input:checked + span:before{ transform:translate(18px,-50%); }

/* Preview */
.preview-card{ display:flex; flex-direction:column; }
.preview-wrap{ --zoom: .9; width:100%; overflow:hidden; }
.preview-wrap canvas{
  display:block;
  width: calc(var(--zoom) * 100%);
  max-width:100%;
  height:auto;
  border-radius:12px;
  border:1px solid rgba(255,255,255,.14);
  background:transparent;
}

/* ==== MOBILE TWEAKS ==== */
@media (max-width: 720px){
  .glass-card{ padding:12px; }
  .actions .btn{ flex:1 1 auto; }

  /* inputs de link: quebram em linhas e botões ficam fluidos */
  .ref-input{ flex-wrap:wrap; }
  .ref-input .btn{ flex:1 1 160px; }
  .ref-input a.btn{ text-align:center; }

  /* mantém preview sempre visível no topo da página */
  .preview-wrap{ margin-top:4px; }
}
</style>
