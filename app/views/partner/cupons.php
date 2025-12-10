<?php
// View: Parceiro • Cupons & Ofertas (envio para aprovação do Admin)
$u = Auth::user();
if (!$u || ($u['role'] ?? 'member') !== 'partner') {
  http_response_code(403);
  echo "<p style='padding:16px'>Acesso negado.</p>";
  return;
}
?>
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
          <button id="offer-send" class="btn">Enviar para aprovação</button>
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
  const $  = (s,sc)=> (sc||document).querySelector(s);
  const F  = $('#offer-form');
  const FL = $('#flash');

  // flash helper
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
      updatePreview(); // atualizar chip "Cupom/Link/Serviço"
    }
  });

  // alterna campos por tipo
  function updateTypeVisibility(){
    const t = (F.type.value || 'coupon').toLowerCase();
    F.querySelectorAll('[data-if]').forEach(el=>{
      const list = (el.getAttribute('data-if')||'').split('|').map(s=>s.trim());
      el.style.display = list.includes(t) ? '' : 'none';
    });
  }

  // gerar código local
  $('#gen-code').onclick = ()=>{
    const s1 = Math.random().toString(36).slice(2,6).toUpperCase();
    const s2 = Math.random().toString(36).slice(2,6).toUpperCase();
    F.code.value = `AVIV-${s1}${s2}`;
    updatePreview();
  };

  // preview da imagem
  const prevImgTag = $('#prev-img-tag');
  const prevImgBox = $('#prev-img');
  function setPreviewImg(url){
    url = (url||'').trim();
    if (!url){ prevImgTag.hidden = true; prevImgTag.src=''; prevImgBox.querySelector('.ph').style.display='flex'; return; }
    prevImgTag.src = url; prevImgTag.hidden = false; prevImgBox.querySelector('.ph').style.display='none';
  }

  // preview de texto/meta
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

  // eventos para atualizar preview
  ['input','change','keyup'].forEach(ev=>{
    F.addEventListener(ev, (e)=>{
      if (e.target.name === 'image_url'){ setPreviewImg(e.target.value); }
      updatePreview();
    });
  });

  // submit
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

  // init
  updateTypeVisibility();
  setPreviewImg('');
  updatePreview();
})();
</script>

<style>
/* layout base */
.partner-coupons .glass-card{ background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.10); padding:14px; border-radius:14px; color:#fff; }
.partner-coupons .muted{ color:#cfe1ff; opacity:.9; }
.partner-coupons .sect-title{ margin:0 0 10px; font-weight:800; }
.partner-coupons .sect-sub{ margin:0 0 8px; font-weight:800; color:#fff; }

/* grid principal */
.two-col{ display:grid; grid-template-columns: minmax(0,1fr) 360px; gap:16px; align-items:start; }
@media (max-width: 980px){ .two-col{ grid-template-columns: 1fr; } }

/* form */
.form-grid{ display:grid; gap:12px; grid-template-columns: 1fr 1fr; }
.input-wrap{ display:block; }
.span-2{ grid-column: 1 / -1; }
.lbl{ display:block; font-size:.86rem; margin:0 0 6px 2px; color:#cfe1ff; opacity:.95; }
.field{ width:100%; box-sizing:border-box; padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,.20); background:rgba(255,255,255,.08); color:#eaf3ff; outline:none; }
textarea.field{ resize:vertical; }
.ta-min{ min-height: 140px; }
.row{ display:flex; gap:8px; align-items:center; }
.form-actions{ display:flex; gap:8px; justify-content:flex-end; }

/* combo (single) */
.combo{ position:relative }
.combo-btn{ width:100%; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:10px 12px; border-radius:10px; border:1px solid rgba(186,126,255,.35); background:#281B3E; color:#f1e9ff; cursor:pointer }
.combo-btn:focus{ outline:2px solid rgba(186,126,255,.55); outline-offset:2px }
.combo-menu{ position:absolute; top:calc(100% + 6px); left:0; right:0; background:#201431; color:#f1e9ff; border:1px solid rgba(186,126,255,.35); border-radius:12px; padding:8px; z-index:50; box-shadow:0 8px 24px rgba(0,0,0,.35); display:none }
.combo[data-open] .combo-menu{ display:block }
.combo-list{ max-height:210px; overflow:auto; display:grid; gap:6px; padding-right:4px }
.combo-opt{ display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:8px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); cursor:pointer }
.combo-opt input{ accent-color:#b57bff }

/* preview */
.preview-col{ position:sticky; top:80px; display:flex; flex-direction:column; gap:10px }
.card-prev{
  border:1px solid rgba(255,255,255,.12);
  border-radius:12px;
  background:rgba(255,255,255,.06);
  overflow:hidden;
}
.prev-img{ position:relative; background:rgba(255,255,255,.08); height:180px; display:flex; align-items:center; justify-content:center; }
.prev-img img{ width:100%; height:100%; object-fit:cover; }
.prev-img .ph{ color:#c9b5ff; font-size:.95rem; }
.prev-body{ padding:10px }
.prev-type{ font-size:.8rem; font-weight:800; color:#d7c7ff; margin-bottom:6px }
.prev-title{ margin:.1rem 0 .35rem; font-size:1.05rem; font-weight:800; color:#fff }
.prev-desc{ margin:0 0 .5rem; color:#eaf3ff; opacity:.92 }
.prev-meta{ display:flex; gap:6px; flex-wrap:wrap }
.chip{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); color:#fff; font-size:.82rem; }
.chip.ghost{ background:transparent; }

/* botões */
.btn{ padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.10); color:#fff; cursor:pointer }
.btn.btn-sm{ padding:8px 12px }
.btn--ghost{ background:transparent; border:1px solid rgba(255,255,255,.18); } /* sem color custom */

/* flash */
.flash{ padding:10px 12px; border-radius:10px; border:1px solid transparent; margin-bottom:8px }
.flash--ok{ background:#e6f7ec; border-color:#b8ebc6; color:#0f7a2f }
.flash--warn{ background:#fff7e6; border-color:#ffe1a8; color:#8a5a00 }
.flash--err{ background:#ffecec; border-color:#ffc9c9; color:#a10000 }

/* lista dicas */
.tips{ margin-top:10px; border:1px dashed rgba(255,255,255,.25); border-radius:12px; padding:10px; color:#eaf3ff }
.tips strong{ display:block; margin-bottom:6px }
.tips ul{ margin:0 0 0 18px; padding:0 }
.tips li{ margin:.25rem 0 }
</style>
