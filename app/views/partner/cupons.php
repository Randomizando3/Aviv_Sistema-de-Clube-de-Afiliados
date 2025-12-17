<?php
// View: Parceiro • Cupons & Ofertas (envio para aprovação do Admin)
$u = Auth::user();
if (!$u || ($u['role'] ?? 'member') !== 'partner') {
  http_response_code(403);
  echo "<p style='padding:16px'>Acesso negado.</p>";
  return;
}

/**
 * MENU (mobile full-screen) — mesmo do dashboard
 * - Anúncios      -> /?r=partner/dashboard
 * - Meus cupons   -> /?r=partner/cupons
 * - Sair          -> /?r=auth/logout
 */
$menuItems = [
  ['label'=>'🪧 Anúncios',    'href'=>'/?r=partner/dashboard'],
  ['label'=>'🏷️ Meus cupons', 'href'=>'/?r=partner/cupons'],
  ['label'=>'⬅️ Sair',        'href'=>'/?r=auth/login'],
];
?>

<!-- ===== MENU OVERLAY (div cheia) ===== -->
<div id="mnav" class="mnav" aria-hidden="true">
  <div class="mnav-backdrop" data-mnav-close></div>
  <aside class="mnav-panel" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="mnav-head">
      <div class="mnav-brand">
        <span class="mnav-dot"></span>
        <strong>Parceiro</strong>
        <small class="muted" style="margin-left:8px">Menu</small>
      </div>
      <button type="button" class="mnav-x" data-mnav-close aria-label="Fechar menu">&times;</button>
    </div>

    <nav class="mnav-links" aria-label="Navegação">
      <?php foreach ($menuItems as $it): ?>
        <a class="mnav-link" href="<?= htmlspecialchars($it['href']) ?>">
          <?= htmlspecialchars($it['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="mnav-foot">
      <div class="mnav-user">
        <div class="mnav-ava"><?= strtoupper(substr(($u['name'] ?? 'P'), 0, 1)) ?></div>
        <div class="mnav-ud">
          <div class="mnav-un"><?= htmlspecialchars($u['name'] ?? 'Parceiro') ?></div>
          <div class="mnav-ue muted"><?= htmlspecialchars($u['email'] ?? '—') ?></div>
        </div>
      </div>
    </div>
  </aside>
</div>

<section class="container partner-coupons" style="margin-top:18px">
  <div class="glass-card">
    <h1 class="sect-title">Parceiro • Cupons & Ofertas</h1>
    <p class="muted">Cadastre cupons, links ou serviços para oferecer aos associados. Seu envio vai para aprovação do admin.</p>
  </div>

  <!-- Flash -->
  <div id="flash" style="margin-top:10px"></div>

  <!-- Form + Preview (duas colunas) -->
  <div class="glass-card" style="margin-top:12px">
    <h2 class="sect-sub">Nova oferta</h2>

    <div class="two-col">
      <!-- Coluna 1: Form -->
      <form id="offer-form" onsubmit="return false;" class="form-grid">
        <!-- Título -->
        <div class="input-wrap span-2">
          <label class="lbl">Título*</label>
          <input class="field" name="title" required placeholder="Ex.: 15% OFF em exames de imagem">
        </div>

        <!-- Tipo -->
        <div class="input-wrap">
          <label class="lbl">Tipo</label>
          <div id="type-combo" class="combo" data-single>
            <button type="button" class="combo-btn" aria-expanded="false">
              <span class="combo-label">Cupom</span>
              <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="combo-menu">
              <div class="combo-list">
                <label class="combo-opt"><input type="radio" name="ofertype" value="coupon" checked><span>Cupom</span></label>
                <label class="combo-opt"><input type="radio" name="ofertype" value="link"><span>Link</span></label>
                <label class="combo-opt"><input type="radio" name="ofertype" value="service"><span>Serviço</span></label>
              </div>
            </div>
            <input type="hidden" name="type" value="coupon">
          </div>
        </div>

        <!-- Código (apenas quando CUPOM) -->
        <div class="input-wrap" data-if="coupon">
          <label class="lbl">Código do cupom</label>
          <div class="row">
            <input class="field" name="code" placeholder="AVIV15, SAUDE-2025...">
            <button class="btn btn-sm btn--ghost" id="gen-code" type="button" title="Gerar código">Gerar código</button>
          </div>
          <small class="muted">Este código será exibido para o associado ao visualizar o benefício.</small>
        </div>

        <!-- Link (para LINK/SERVIÇO) -->
        <div class="input-wrap" data-if="link|service">
          <label class="lbl">URL do parceiro</label>
          <input class="field" name="link" type="url" placeholder="https://seusite.com/pagina-da-oferta">
        </div>

        <!-- Especialidade opcional -->
        <div class="input-wrap">
          <label class="lbl">Especialidade (opcional)</label>
          <input class="field" name="specialty" placeholder="Ex.: Odontologia, Cardiologia, Clínica Geral">
        </div>

        <!-- Validade -->
        <div class="input-wrap">
          <label class="lbl">Validade (opcional)</label>
          <input class="field" name="valid_until" type="date">
        </div>

        <!-- Imagem -->
        <div class="input-wrap span-2">
          <label class="lbl">URL da imagem (recomendado)</label>
          <input class="field" name="image_url" type="url" placeholder="https://.../banner.jpg">
          <small class="muted">Imagens horizontais ~ 1200×630 funcionam bem no card de destaque.</small>
        </div>

        <!-- Descrição -->
        <div class="input-wrap span-2">
          <label class="lbl">Descrição</label>
          <textarea class="field ta-min" name="description" rows="6" placeholder="Detalhe as condições, regras de uso e observações importantes."></textarea>
        </div>

        <!-- Ações -->
        <div class="form-actions span-2">
          <button id="offer-send" class="btn btn--primary" type="button">Enviar para aprovação</button>
          <button id="offer-reset" class="btn btn--ghost" type="button">Limpar</button>
        </div>
      </form>

      <!-- Coluna 2: Prévia -->
      <aside class="preview-col">
        <div class="card-prev">
          <div class="prev-img" id="prev-img">
            <span class="ph">Prévia da imagem</span>
            <img id="prev-img-tag" alt="Prévia" hidden>
          </div>
          <div class="prev-body">
            <div class="prev-type" id="prev-type">Cupom</div>
            <h3 class="prev-title" id="prev-title">Título da oferta</h3>
            <p class="prev-desc" id="prev-desc">Descrição curta aparecerá aqui. Escreva algo objetivo e convidativo.</p>
            <div class="prev-meta">
              <span class="chip" id="prev-code" title="Código do cupom" hidden>—</span>
              <span class="chip ghost" id="prev-valid" hidden>Sem validade</span>
            </div>
          </div>
        </div>

        <div class="tips">
          <strong>Dicas rápidas</strong>
          <ul>
            <li>Use títulos curtos e claros (ex.: “20% OFF em consultas”).</li>
            <li>Coloque o que é necessário para o associado usar a oferta na descrição.</li>
            <li>Se for cupom, capriche num código fácil de lembrar.</li>
          </ul>
        </div>
      </aside>
    </div>
  </div>

  <!-- Observação -->
  <div class="glass-card" style="margin-top:12px">
    <p class="muted" style="margin:0">
      Após o envio, sua oferta ficará como <b>Pendente</b> até a revisão do Admin. Quando aprovada, ela aparece para os associados na lista de benefícios.
    </p>
  </div>
</section>

<script>
(function(){
  function ready(fn){
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    // =========================
    // MENU MOBILE (div cheia) — mesmo do dashboard
    // =========================
    const mnav = document.getElementById('mnav');

    function openMenu(){
      if (!mnav) return;
      mnav.classList.add('is-open');
      mnav.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('no-scroll');
    }
    function closeMenu(){
      if (!mnav) return;
      mnav.classList.remove('is-open');
      mnav.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('no-scroll');
    }

    const menuToggle =
      document.getElementById('navToggle') ||
      document.getElementById('menuToggle') ||
      document.getElementById('btn-menu') ||
      document.getElementById('btnMenu') ||
      document.querySelector('[data-nav-toggle]') ||
      document.querySelector('[data-menu-toggle]') ||
      document.querySelector('.nav-toggle') ||
      document.querySelector('.menu-toggle') ||
      document.querySelector('button[aria-label="Menu"]') ||
      document.querySelector('button[aria-controls="mobileMenu"]');

    if (menuToggle){
      menuToggle.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        openMenu();
      });
    }

    mnav?.querySelectorAll('[data-mnav-close]').forEach(el=>{
      el.addEventListener('click', function(e){
        e.preventDefault();
        closeMenu();
      });
    });

    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && mnav?.classList.contains('is-open')) closeMenu();
    });

    mnav?.querySelectorAll('a.mnav-link').forEach(a=>{
      a.addEventListener('click', ()=> closeMenu());
    });

    // =========================
    // CUPONS.PHP — script original
    // =========================
    const $  = (s,sc)=> (sc||document).querySelector(s);
    const F  = $('#offer-form');
    const FL = $('#flash');

    function flash(type, msg, persistMs=6000){
      const el = document.createElement('div');
      el.className = 'flash ' + (type==='ok' ? 'flash--ok' : type==='warn' ? 'flash--warn' : 'flash--err');
      el.innerHTML = `<strong>${type==='ok'?'Sucesso': type==='warn'?'Atenção':'Erro'}:</strong> ${msg}`;
      FL.appendChild(el);
      if (persistMs>0) setTimeout(()=> el.remove(), persistMs);
    }

    // combo: tipo
    const combo = $('#type-combo');
    function closeCombos(except=null){
      document.querySelectorAll('.combo[data-open]').forEach(c=>{
        if (except && c===except) return;
        c.removeAttribute('data-open');
        c.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');
      });
    }
    document.addEventListener('click', (e)=>{
      // importante: se menu estiver aberto, não processa combos
      if (mnav?.classList.contains('is-open')) return;

      const inside = e.target.closest('.combo');
      if (!inside) closeCombos();

      const btn = e.target.closest('.combo-btn');
      if (btn){
        const c = btn.closest('.combo');
        const open = c.hasAttribute('data-open');
        if (!open){ closeCombos(c); c.setAttribute('data-open',''); btn.setAttribute('aria-expanded','true'); }
        else { c.removeAttribute('data-open'); btn.setAttribute('aria-expanded','false'); }
        return;
      }

      const opt = e.target.closest('.combo[data-single] .combo-opt');
      if (opt){
        const r = opt.querySelector('input[type="radio"]');
        if (!r) return;
        r.checked = true;
        const label = opt.querySelector('span').textContent;
        combo.querySelector('.combo-label').textContent = label;
        combo.querySelector('input[name="type"]').value = r.value;
        combo.removeAttribute('data-open');
        combo.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');
        updateTypeVisibility();
        updatePreview();
      }
    });

    function updateTypeVisibility(){
      const t = (F.type.value || 'coupon').toLowerCase();
      F.querySelectorAll('[data-if]').forEach(el=>{
        const list = (el.getAttribute('data-if')||'').split('|').map(s=>s.trim());
        el.style.display = list.includes(t) ? '' : 'none';
      });
    }

    $('#gen-code').onclick = ()=>{
      const s1 = Math.random().toString(36).slice(2,6).toUpperCase();
      const s2 = Math.random().toString(36).slice(2,6).toUpperCase();
      F.code.value = `AVIV-${s1}${s2}`;
      updatePreview();
    };

    const prevImgTag = $('#prev-img-tag');
    const prevImgBox = $('#prev-img');
    function setPreviewImg(url){
      url = (url||'').trim();
      if (!url){
        prevImgTag.hidden = true;
        prevImgTag.src='';
        prevImgBox.querySelector('.ph').style.display='flex';
        return;
      }
      prevImgTag.src = url;
      prevImgTag.hidden = false;
      prevImgBox.querySelector('.ph').style.display='none';
    }

    function updatePreview(){
      const t     = (F.type.value || 'coupon').toLowerCase();
      $('#prev-type').textContent  = t==='coupon' ? 'Cupom' : (t==='link' ? 'Link' : 'Serviço');
      $('#prev-title').textContent = (F.title.value || 'Título da oferta');
      $('#prev-desc').textContent  = (F.description.value || 'Descrição curta aparecerá aqui. Escreva algo objetivo e convidativo.');

      const codeChip = $('#prev-code');
      if (t==='coupon' && F.code.value.trim()){
        codeChip.textContent = F.code.value.trim();
        codeChip.hidden = false;
      } else {
        codeChip.hidden = true;
        codeChip.textContent = '—';
      }

      const v = (F.valid_until.value || '').trim();
      const validChip = $('#prev-valid');
      if (v){
        validChip.hidden = false;
        validChip.textContent = 'Válido até ' + v.split('-').reverse().join('/');
      } else {
        validChip.hidden = true;
      }
    }

    ['input','change','keyup'].forEach(ev=>{
      F.addEventListener(ev, (e)=>{
        if (e.target.name === 'image_url'){ setPreviewImg(e.target.value); }
        updatePreview();
      });
    });

    $('#offer-send').onclick = async ()=>{
      const title = (F.title.value||'').trim();
      if (!title){ flash('err','Informe o título.'); return; }

      const body = new URLSearchParams({
        title,
        type: (F.type.value||'coupon'),
        code: (F.code?.value||'').trim(),
        link: (F.link?.value||'').trim(),
        specialty: (F.specialty?.value||'').trim(),
        valid_until: (F.valid_until?.value||'').trim(),
        description: (F.description?.value||'').trim(),
        image_url: (F.image_url?.value||'').trim()
      });

      const btn = $('#offer-send');
      btn.disabled = true; btn.textContent = 'Enviando...';
      try{
        const r = await fetch('/?r=api/partner/offer', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body
        });
        let j; try { j = await r.json(); } catch(e){ j = { ok:false, error:'Resposta inválida do servidor' }; }
        if (!j || j.ok === false){ throw new Error(j.error || 'Falha ao enviar'); }
        flash('ok','Oferta enviada! Aguarde a aprovação do admin.');
        F.reset();
        combo.querySelector('.combo-label').textContent = 'Cupom';
        F.type.value = 'coupon';
        updateTypeVisibility();
        setPreviewImg('');
        updatePreview();
      } catch(e){
        flash('err', e.message || 'Erro ao enviar');
      } finally {
        btn.disabled = false; btn.textContent = 'Enviar para aprovação';
      }
    };

    $('#offer-reset').onclick = ()=>{
      F.reset();
      combo.querySelector('.combo-label').textContent = 'Cupom';
      F.type.value = 'coupon';
      updateTypeVisibility();
      setPreviewImg('');
      updatePreview();
    };

    updateTypeVisibility();
    setPreviewImg('');
    updatePreview();
  });
})();
</script>

<style>
:root{
  --card-bg: #ffffff;
  --card-border: #e5ecf3;
  --card-radius: 16px;
  --text-main: #111827;
  --text-muted: #6b7280;
  --accent: #2563eb;
}
.no-scroll{ overflow:hidden; }

/* ===== MENU (div cheia) — mesmo do dashboard ===== */
.mnav{
  position:fixed;
  inset:0;
  z-index:99999;
  display:none;
}
.mnav.is-open{ display:block; }
.mnav-backdrop{
  position:absolute;
  inset:0;
  background:rgba(15,23,42,.55);
}
.mnav-panel{
  position:absolute;
  inset:0;
  background:rgba(255,255,255,.92);
  backdrop-filter: blur(10px);
  border-left:1px solid rgba(229,236,243,.9);
  box-shadow: 0 20px 80px rgba(15,23,42,.25);
  display:flex;
  flex-direction:column;
}
.mnav-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:14px 16px;
  border-bottom:1px solid rgba(229,236,243,.9);
}
.mnav-brand{ display:flex; align-items:center; gap:8px; color:var(--text-main); }
.mnav-dot{
  width:10px; height:10px; border-radius:999px;
  background:var(--accent);
  box-shadow:0 0 0 6px rgba(37,99,235,.12);
}
.mnav-x{
  appearance:none;
  border:0;
  background:transparent;
  color:#111827;
  font-size:28px;
  line-height:1;
  width:44px; height:44px;
  border-radius:12px;
  cursor:pointer;
}
.mnav-x:hover{ background:rgba(17,24,39,.06); }
.mnav-links{
  padding:10px 12px;
  display:flex;
  flex-direction:column;
  gap:10px;
}
.mnav-link{
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:12px 14px;
  border-radius:14px;
  border:1px solid rgba(209,217,230,.9);
  background:#ffffff;
  text-decoration:none;
  color:var(--text-main);
  font-weight:800;
}
.mnav-link:hover{
  border-color: rgba(37,99,235,.35);
  box-shadow: 0 10px 25px rgba(37,99,235,.12);
}
.mnav-foot{
  margin-top:auto;
  padding:12px 14px 16px;
  border-top:1px solid rgba(229,236,243,.9);
}
.mnav-user{
  display:flex;
  gap:10px;
  align-items:center;
}
.mnav-ava{
  width:40px; height:40px;
  border-radius:14px;
  display:grid; place-items:center;
  background:rgba(37,99,235,.12);
  color:var(--accent);
  font-weight:900;
}
.mnav-un{ font-weight:800; color:var(--text-main); }
.mnav-ue{ font-size:.9rem; }

/* ===== Layout base: cartas brancas no estilo Planos ===== */
.partner-coupons .glass-card{
  background:#ffffff;
  border:1px solid #e9eef2;
  padding:20px;
  border-radius:16px;
  color:#2C3E50;
  box-shadow:0 10px 26px rgba(0,0,0,.06);
}
.partner-coupons .muted{
  color:#64748b;
  opacity:1;
  font-size:.9rem;
}
.partner-coupons .sect-title{
  margin:0 0 8px;
  font-weight:700;
  font-family:"Poppins","Segoe UI",system-ui,sans-serif;
  font-size:1.6rem;
}
.partner-coupons .sect-sub{
  margin:0 0 10px;
  font-weight:700;
  font-family:"Poppins","Segoe UI",system-ui,sans-serif;
  font-size:1.2rem;
}

/* ===== Grid principal ===== */
.two-col{
  display:grid;
  grid-template-columns:minmax(0,1fr) 360px;
  gap:20px;
  align-items:flex-start;
}
@media (max-width: 980px){
  .two-col{ grid-template-columns:1fr; }
}

/* ===== Form ===== */
.form-grid{
  display:grid;
  gap:14px;
  grid-template-columns:1fr 1fr;
}
.input-wrap{ display:block; }
.span-2{ grid-column:1 / -1; }

.lbl{
  display:block;
  font-size:.9rem;
  margin:0 0 6px 2px;
  color:#4b5563;
  font-weight:600;
}
.field{
  width:100%;
  box-sizing:border-box;
  padding:11px 12px;
  border-radius:10px;
  border:1px solid #d1d9e6;
  background:#ffffff;
  color:#111827;
  outline:none;
  font-size:.95rem;
}
.field:focus{
  border-color:#5DADE2;
  box-shadow:0 0 0 1px #cde9ff;
}
textarea.field{ resize:vertical; }
.ta-min{ min-height:140px; }

.row{
  display:flex;
  gap:8px;
  align-items:center;
}

.form-actions{
  display:flex;
  gap:10px;
  justify-content:flex-end;
}

/* ===== Combo (single) ===== */
.combo{ position:relative; }
.combo-btn{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding:11px 12px;
  border-radius:10px;
  border:1px solid #d1d9e6;
  background:#ffffff;
  color:#111827;
  cursor:pointer;
  font-size:.95rem;
  font-weight:600;
}
.combo-btn:focus{
  outline:2px solid #5DADE2;
  outline-offset:2px;
}
.combo-menu{
  position:absolute;
  top:calc(100% + 6px);
  left:0;
  right:0;
  background:#ffffff;
  color:#111827;
  border:1px solid #e2e8f0;
  border-radius:12px;
  padding:6px;
  z-index:50;
  box-shadow:0 14px 32px rgba(15,23,42,.16);
  display:none;
}
.combo[data-open] .combo-menu{ display:block; }

.combo-list{
  max-height:210px;
  overflow:auto;
  display:grid;
  gap:4px;
}
.combo-opt{
  display:flex;
  align-items:center;
  gap:8px;
  padding:6px 8px;
  border-radius:8px;
  cursor:pointer;
  font-size:.9rem;
}
.combo-opt:hover{
  background:#f3f4ff;
}
.combo-opt input{
  accent-color:#3B8FC6;
}

/* ===== Preview ===== */
.preview-col{
  position:sticky;
  top:80px;
  display:flex;
  flex-direction:column;
  gap:12px;
}
.card-prev{
  border:1px solid #e9eef2;
  border-radius:16px;
  background:#ffffff;
  overflow:hidden;
  box-shadow:0 10px 26px rgba(0,0,0,.06);
}
.prev-img{
  position:relative;
  background:#f3f6fb;
  height:180px;
  display:flex;
  align-items:center;
  justify-content:center;
  border-bottom:1px solid #e5e9f2;
}
.prev-img img{
  width:100%;
  height:100%;
  object-fit:cover;
}
.prev-img .ph{
  color:#94a3b8;
  font-size:.95rem;
}
.prev-body{
  padding:12px 14px 14px;
}
.prev-type{
  font-size:.78rem;
  font-weight:800;
  color:#3B8FC6;
  margin-bottom:4px;
}
.prev-title{
  margin:.1rem 0 .35rem;
  font-size:1.08rem;
  font-weight:700;
  font-family:"Poppins","Segoe UI",system-ui,sans-serif;
  color:#111827;
}
.prev-desc{
  margin:0 0 .55rem;
  color:#4b5563;
  font-size:.93rem;
}
.prev-meta{
  display:flex;
  gap:6px;
  flex-wrap:wrap;
}
.chip{
  display:inline-flex;
  align-items:center;
  gap:6px;
  padding:6px 10px;
  border-radius:999px;
  background:#e5f0ff;
  border:1px solid #c7ddff;
  color:#1d4ed8;
  font-size:.8rem;
  font-weight:700;
}
.chip.ghost{
  background:#ffffff;
  border:1px dashed #d1d5db;
  color:#6b7280;
}

/* ===== Botões ===== */
.btn{
  padding:10px 16px;
  border-radius:999px;
  border:none;
  cursor:pointer;
  font-weight:700;
  font-size:.95rem;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:.35rem;
  transition:.18s transform ease, .18s box-shadow ease, .18s background ease;
}
.btn.btn-sm{
  padding:8px 12px;
  font-size:.85rem;
}
.btn--primary{
  background:linear-gradient(90deg,#5DADE2,#3B8FC6);
  color:#ffffff;
  box-shadow:0 8px 22px rgba(59,143,198,.35);
}
.btn--primary:hover{
  transform:translateY(-1px);
  box-shadow:0 10px 26px rgba(59,143,198,.40);
}
.btn--ghost{
  background:#f5f7fa;
  border:1px solid #d1d9e6;
  color:#374151;
}
.btn--ghost:hover{
  background:#e5edf8;
}

/* ===== Flash ===== */
.flash{
  padding:10px 12px;
  border-radius:10px;
  border:1px solid transparent;
  margin-bottom:8px;
  font-size:.9rem;
}
.flash--ok{
  background:#e6f7ec;
  border-color:#b8ebc6;
  color:#0f7a2f;
}
.flash--warn{
  background:#fff7e6;
  border-color:#ffe1a8;
  color:#8a5a00;
}
.flash--err{
  background:#ffecec;
  border-color:#ffc9c9;
  color:#a10000;
}

/* ===== Dicas ===== */
.tips{
  margin-top:10px;
  border:1px dashed #e2e8f0;
  border-radius:12px;
  padding:10px 12px;
  color:#4b5563;
  background:#ffffff;
}
.tips strong{
  display:block;
  margin-bottom:6px;
}
.tips ul{
  margin:0 0 0 18px;
  padding:0;
  font-size:.9rem;
}
.tips li{
  margin:.25rem 0;
}
</style>
