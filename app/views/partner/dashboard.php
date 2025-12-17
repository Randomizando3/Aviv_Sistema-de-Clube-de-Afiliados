<?php
// Partner Dashboard — Campanhas + compra de plano + pedidos
$u = Auth::user();
if (!$u || ($u['role'] ?? 'member') !== 'partner') {
  http_response_code(403);
  echo "<p style='padding:16px'>Acesso negado.</p>";
  return;
}

/**
 * MENU (mobile full-screen)
 * Conteúdo e links corretos:
 * - Anúncios  -> /?r=partner/dashboard
 * - Meus cupons -> /?r=partner/cupons
 * - Sair -> /?r=auth/logout
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

<section class="container" style="margin-top:18px">
  <div class="glass-card">
    <h1 class="sect-title">Parceiro • Campanhas de Publicidade</h1>
    <p class="muted">Crie sua campanha (título, link e até 5 imagens) e ative com um plano de visualizações.</p>
  </div>

  <!-- FLASH -->
  <div id="flash" style="margin-top:10px"></div>

  <!-- ===== Board: Rascunho (E) + Minhas campanhas (D) ===== -->
  <div class="glass-card" style="margin-top:12px">
    <div class="board">
      <!-- COLUNA ESQUERDA — Rascunho -->
      <section class="draft">
        <h2 class="sect-sub">Nova campanha</h2>
        <span class="bdg bdg--muted">Rascunho</span>

        <form id="camp-form-new" onsubmit="return false;" class="form-camp" style="margin-top:10px">
          <label class="field span-all">
            <span>Título*</span>
            <input name="title" required placeholder="Ex.: Clínica Aviv — Check-up completo" />
          </label>

          <label class="field span-all">
            <span>Link ao clicar</span>
            <div class="input-row">
              <input name="target_url" placeholder="https://seusite.com/pagina" />
              <a class="ghost-link" id="test-link-new" href="#" target="_blank" rel="noopener">Testar</a>
            </div>
          </label>

          <div class="subttl span-all">Imagens</div>

          <div class="img-grid span-all">
            <?php
              $imgFields = [
                'img_sky_1'   => 'Arranha-céu 1 (lateral)',
                'img_sky_2'   => 'Arranha-céu 2 (lateral)',
                'img_top_468' => 'Topo 468 px (largura)',
                'img_square_1'=> 'Quadrado 1',
                'img_square_2'=> 'Quadrado 2',
              ];
              foreach ($imgFields as $name => $label):
            ?>
            <div class="img-tile">
              <label class="tile-label"><?=htmlspecialchars($label)?></label>
              <input name="<?=$name?>" placeholder="URL da imagem" />
              <div class="thumb"><img data-pv="<?=$name?>-new" alt="" hidden></div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="actions span-all">
            <button type="button" id="btn-create-camp" class="btn">Criar campanha</button>
            <button type="button" id="btn-reset-camp" class="btn btn--ghost">Limpar</button>
          </div>
        </form>
      </section>

      <!-- COLUNA DIREITA — Lista com rolagem -->
      <section class="mycamps">
        <div class="mycamps-head">
          <h2 class="sect-sub">Minhas campanhas</h2>
          <small class="muted">até 3 cards visíveis (role para ver mais)</small>
        </div>
        <div class="camp-list-wrap">
          <div id="camp-list" class="camp-list" role="list" aria-label="Campanhas do parceiro"></div>
        </div>
      </section>
    </div>
  </div>

  <!-- ===== Comprar plano (combobox espessa) ===== -->
  <div class="glass-card" style="margin-top:12px">
    <h2 class="sect-sub">Comprar plano (ativar campanha)</h2>

    <div class="buy-grid">
      <div class="buy-left">
        <div class="field span-all">
          <span>Escolha a campanha</span>
          <div id="camp-combo" class="combo combo--thick" data-single>
            <button type="button" class="combo-btn" aria-expanded="false">
              <span class="combo-label">Selecione uma campanha</span>
              <svg viewBox="0 0 24 24" width="18" height="18"><path fill="currentColor" d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="combo-menu"><div class="combo-list" id="camp-combo-list"></div></div>
            <input type="hidden" id="buy-campaign" value="">
          </div>
        </div>

        <!-- preview da campanha escolhida -->
        <div id="camp-preview" class="mini-camp" hidden>
          <div class="mini-head">
            <h3 class="mini-title"></h3>
            <div class="mini-meta"></div>
          </div>
          <div class="mini-gal"></div>
        </div>
      </div>

      <div class="buy-right">
        <p class="muted" style="margin:0 0 8px">Planos disponíveis</p>
        <div id="plans" class="cards plans-grid"></div>
      </div>
    </div>
  </div>

  <!-- ===== Meus pedidos ===== -->
  <div class="glass-card" style="margin-top:12px">
    <h2 class="sect-sub">Meus pedidos</h2>
    <div id="orders" class="table-wrap"></div>
  </div>
</section>

<script>
(function(){
  function ready(fn){
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  ready(function(){
    const $ = s => document.querySelector(s);
    function escapeHtml(s){ return String(s||'').replace(/[&<>"]/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch])); }
    function escapeAttr(s){ return escapeHtml(s).replace(/'/g,'&#39;'); }

    const cssEscape = (window.CSS && typeof CSS.escape === 'function')
      ? CSS.escape
      : (v) => String(v||'').replace(/[^a-zA-Z0-9_\u00A0-\uFFFF-]/g, (ch) => '\\' + ch);

    // =========================
    // MENU MOBILE (div cheia)
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

    // Bind no botão do header/layout (tenta vários ids/classes comuns)
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

    // Fecha ao clicar no backdrop ou no X
    mnav?.querySelectorAll('[data-mnav-close]').forEach(el=>{
      el.addEventListener('click', function(e){
        e.preventDefault();
        closeMenu();
      });
    });

    // ESC fecha
    document.addEventListener('keydown', function(e){
      if (e.key === 'Escape' && mnav?.classList.contains('is-open')) closeMenu();
    });

    // Se clicar em qualquer link dentro do menu, fecha
    mnav?.querySelectorAll('a.mnav-link').forEach(a=>{
      a.addEventListener('click', ()=> closeMenu());
    });

    // -------- modal flash (popup) --------
    let _mfxTimer = null;
    function flash(type, msg, persistMs = 6000){
      document.querySelectorAll('.mfx-overlay').forEach(el=>el.remove());
      if (_mfxTimer) { clearTimeout(_mfxTimer); _mfxTimer = null; }

      const tMap = { ok:'Sucesso', warn:'Atenção', err:'Erro' };
      const cls  = (type === 'ok') ? 'mfx--ok' : (type === 'warn') ? 'mfx--warn' : 'mfx--err';
      const safeMsg = escapeHtml(msg || '');

      const ov = document.createElement('div');
      ov.className = 'mfx-overlay';
      ov.setAttribute('role','dialog');
      ov.setAttribute('aria-modal','true');
      ov.innerHTML = `
        <div class="mfx-box ${cls}" role="document">
          <div class="mfx-head">
            <span class="mfx-title">${tMap[type] || 'Aviso'}</span>
            <button type="button" class="mfx-close" aria-label="Fechar" title="Fechar">&times;</button>
          </div>
          <div class="mfx-body">${safeMsg}</div>
        </div>
      `;
      document.body.appendChild(ov);

      requestAnimationFrame(()=> ov.setAttribute('data-open',''));

      function close(){
        ov.removeAttribute('data-open');
        setTimeout(()=> ov.remove(), 150);
        document.removeEventListener('keydown', onKey);
        if (_mfxTimer) { clearTimeout(_mfxTimer); _mfxTimer = null; }
      }
      function onKey(e){ if (e.key === 'Escape') close(); }

      ov.addEventListener('click', (e)=>{ if (e.target === ov) close(); });
      ov.querySelector('.mfx-close')?.addEventListener('click', close);
      document.addEventListener('keydown', onKey);

      if (persistMs > 0) _mfxTimer = setTimeout(close, persistMs);
    }

    // retorno do checkout
    (function checkReturnParams(){
      const usp = new URLSearchParams(location.search);
      if (usp.get('paid') === '1') {
        flash('ok','Seu pagamento foi identificado. O pedido será marcado como pago e a campanha ativada.');
        usp.delete('paid');
        history.replaceState({}, '', `${location.pathname}${usp.toString()?('?'+usp.toString()):''}`);
      } else if (usp.get('canceled') === '1') {
        flash('warn','Pagamento cancelado pelo usuário. Você pode tentar novamente em Meus pedidos.');
        usp.delete('canceled');
        history.replaceState({}, '', `${location.pathname}${usp.toString()?('?'+usp.toString()):''}`);
      }
    })();

    // helpers
    function badge(st){
      const map = {
        'active':          {t:'Ativa', cls:'bdg--ok'},
        'exhausted':       {t:'Exaurida', cls:'bdg--muted'},
        'pending_payment': {t:'Pendente', cls:'bdg--warn'},
        'canceled':        {t:'Cancelada', cls:'bdg--err'},
        'overdue':         {t:'Vencida', cls:'bdg--err'},
        'refunded':        {t:'Estornada', cls:'bdg--muted'},
        'inactive':        {t:'Inativa', cls:'bdg--muted'}
      };
      const key = String(st||'').toLowerCase();
      const it = map[key] || {t:(st||'-'), cls:'bdg--muted'};
      return `<span class="bdg ${it.cls}">${it.t}</span>`;
    }
    function imagesOf(c){
      return ['img_sky_1','img_sky_2','img_top_468','img_square_1','img_square_2']
        .map(k=>({k, url:(c[k]||'').trim()})).filter(it=>it.url);
    }

    // -------- NOVA campanha (rascunho) --------
    const Fnew = document.getElementById('camp-form-new');
    function updateNewPreview(name){
      if (!Fnew) return;
      const input = Fnew.querySelector(`input[name="${name}"]`);
      const pv = Fnew.querySelector(`img[data-pv="${name}-new"]`);
      if (!pv) return;
      const url = (input?.value || '').trim();
      if (url) { pv.src = url; pv.hidden = false; } else { pv.hidden = true; pv.removeAttribute('src'); }
    }
    ['img_sky_1','img_sky_2','img_top_468','img_square_1','img_square_2'].forEach(n=>{
      const el = Fnew?.querySelector(`input[name="${n}"]`);
      if (el) el.addEventListener('input', () => updateNewPreview(n));
    });
    document.getElementById('test-link-new')?.addEventListener('click', (e)=>{
      e.preventDefault();
      const url = (Fnew?.target_url?.value || '').trim();
      if (url) window.open(url, '_blank', 'noopener');
      else flash('warn','Informe um link para testar.');
    });

    document.getElementById('btn-create-camp')?.addEventListener('click', async ()=>{
      if (!Fnew) return;
      const fd = new FormData(Fnew);
      if(!(fd.get('title')||'').trim()){ return flash('warn','Informe o título.'); }
      try{
        const r = await fetch('/?r=api/partner/ads/campaign/save',{method:'POST', body:fd});
        let j; try { j = await r.json(); } catch(e){ return flash('err','Erro de resposta ao criar'); }
        if(!j.ok){ return flash('err', j.error||'Falha ao criar'); }
        flash('ok','Campanha criada!');
        Fnew.reset();
        ['img_sky_1','img_sky_2','img_top_468','img_square_1','img_square_2'].forEach(updateNewPreview);
        await loadCampaigns(); await loadPlans();
      }catch(e){ flash('err', e.message || 'Erro ao criar'); }
    });

    document.getElementById('btn-reset-camp')?.addEventListener('click', ()=>{
      if (!Fnew) return;
      Fnew.reset();
      ['img_sky_1','img_sky_2','img_top_468','img_square_1','img_square_2'].forEach(updateNewPreview);
    });

    // -------- Lista de campanhas --------
    let CAMPS = [];
    const listEl = document.getElementById('camp-list');

    function renderCampCard(c){
      const imgs = imagesOf(c);
      return `
        <article class="camp-card" data-id="${c.id}" role="listitem" aria-label="Campanha #${c.id}">
          <header class="camp-card-head">
            <div class="cch-left">
              <h3 class="c-title">${escapeHtml(c.title||'-')}</h3>
              <div class="c-meta">
                ${badge(c.status||'inactive')}
                ${c.target_url ? `<a class="chip-link" href="${escapeAttr(c.target_url)}" target="_blank" rel="noopener">Abrir link</a>` : ''}
              </div>
            </div>
            <div class="cch-actions">
              <button type="button" class="btn btn-sm" data-act="edit">Editar</button>
            </div>
          </header>

          <div class="gal-mini">
            ${
              imgs.length
                ? imgs.map(it => `
                    <a href="${escapeAttr(it.url)}" target="_blank" rel="noopener" class="gm-it" title="${escapeHtml(it.k)}">
                      <img src="${escapeAttr(it.url)}" alt="${escapeHtml(it.k)}">
                      <span class="gm-tag">${escapeHtml(it.k.replace('img_','').replaceAll('_',' '))}</span>
                    </a>
                  `).join('')
                : `<div class="gm-ph">Sem imagens</div>`
            }
          </div>

          <form class="camp-edit" data-editing="0" onsubmit="return false;">
            <input type="hidden" name="id" value="${c.id}">
            <label class="field">
              <span>Título*</span>
              <input name="title" value="${escapeAttr(c.title||'')}" required />
            </label>
            <label class="field">
              <span>Link ao clicar</span>
              <div class="input-row">
                <input name="target_url" value="${escapeAttr(c.target_url||'')}" placeholder="https://..." />
                ${c.target_url ? `<a class="ghost-link" href="${escapeAttr(c.target_url)}" target="_blank" rel="noopener">Testar</a>` : `<span class="ghost-link ghost-disabled">Testar</span>`}
              </div>
            </label>

            <div class="img-grid">
              ${['img_sky_1','img_sky_2','img_top_468','img_square_1','img_square_2'].map(k => `
                <div class="img-tile">
                  <label class="tile-label">${escapeHtml(k.replace('img_','').replaceAll('_',' '))}</label>
                  <input name="${k}" value="${escapeAttr(c[k]||'')}" placeholder="URL da imagem" />
                  <div class="thumb"><img data-pv="${k}-${c.id}" src="${escapeAttr(c[k]||'')}" alt="" ${c[k]?'':'hidden'}></div>
                </div>
              `).join('')}
            </div>

            <div class="actions">
              <button type="button" class="btn" data-act="save">Salvar</button>
              <button type="button" class="btn btn--ghost" data-act="cancel">Cancelar</button>
            </div>
          </form>
        </article>
      `;
    }

    async function loadCampaigns(){
      if (!listEl) return;
      listEl.innerHTML = `<div class="muted">Carregando…</div>`;
      const r = await fetch('/?r=api/partner/ads/campaigns', {cache:'no-store'});
      let j; try { j = await r.json(); } catch(e){ listEl.innerHTML = `<p class="muted">Erro ao carregar.</p>`; return; }
      if(!j.ok){ listEl.innerHTML = `<p class="muted">${escapeHtml(j.error||'Falha ao carregar')}</p>`; return; }

      CAMPS = j.data || [];
      listEl.innerHTML = CAMPS.length ? CAMPS.map(renderCampCard).join('') : '<p class="muted">Você ainda não tem campanhas.</p>';

      listEl.querySelectorAll('[data-act="edit"]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const card = btn.closest('.camp-card');
          const form = card.querySelector('.camp-edit');
          form.setAttribute('data-editing','1');
          card.scrollIntoView({behavior:'smooth', block:'nearest'});
        });
      });
      listEl.querySelectorAll('[data-act="cancel"]').forEach(btn=>{
        btn.addEventListener('click', (e)=> e.target.closest('.camp-edit').setAttribute('data-editing','0'));
      });
      listEl.querySelectorAll('[data-act="save"]').forEach(btn=>{
        btn.addEventListener('click', async (e)=>{
          const form = e.target.closest('.camp-edit');
          const fd = new FormData(form);
          if(!(fd.get('title')||'').trim()){ return flash('warn','Informe o título.'); }
          try{
            const r = await fetch('/?r=api/partner/ads/campaign/save',{method:'POST', body:fd});
            let j; try { j = await r.json(); } catch(ex){ return flash('err','Erro de resposta ao salvar'); }
            if(!j.ok){ return flash('err', j.error||'Falha ao salvar'); }
            flash('ok','Campanha salva!');
            await loadCampaigns(); await loadPlans();
          }catch(ex){ flash('err', ex.message || 'Erro ao salvar'); }
        });
      });
      listEl.querySelectorAll('.camp-edit input[name^="img_"]').forEach(inp=>{
        inp.addEventListener('input', ()=>{
          const id = inp.closest('.camp-card')?.dataset.id;
          const pv = listEl.querySelector(`img[data-pv="${inp.name}-${id}"]`);
          const url = inp.value.trim();
          if (pv){
            if(url){ pv.src=url; pv.hidden=false; }
            else { pv.hidden=true; pv.removeAttribute('src'); }
          }
        });
      });

      renderCampaignCombo(CAMPS);
      renderBuyPreview(document.getElementById('buy-campaign')?.value);
    }

    // -------- combobox campanhas (compra) --------
    function renderCampaignCombo(rows){
      const list = document.getElementById('camp-combo-list');
      const label = document.querySelector('#camp-combo .combo-label');
      const hidden = document.getElementById('buy-campaign');
      const preview = document.getElementById('camp-preview');
      if(!list || !label || !hidden) return;

      if(!rows.length){
        list.innerHTML = `<div class="combo-empty">Crie uma campanha para comprar um plano.</div>`;
        label.textContent = 'Nenhuma campanha';
        hidden.value = '';
        if (preview) preview.hidden = true;
        return;
      }

      const group = 'campSel_' + Math.random().toString(36).slice(2);
      list.innerHTML = rows.map(c => `
        <label class="combo-opt">
          <input type="radio" name="${group}" value="${c.id}">
          <span><strong>${escapeHtml(c.title||'-')}</strong> · <em>${escapeHtml(c.status||'-')}</em></span>
        </label>
      `).join('');

      let selId = hidden.value || String(rows[0].id);
      const first = list.querySelector(`input[value="${cssEscape(selId)}"]`) || list.querySelector('input[type="radio"]');
      if (first){ first.checked = true; selId = first.value; }

      const c = rows.find(x=> String(x.id)===String(selId));
      label.textContent = c ? c.title : 'Selecione uma campanha';
      hidden.value = selId;
    }

    document.addEventListener('click', (e)=>{
      if (mnav?.classList.contains('is-open')) return;

      const btn = e.target.closest('.combo-btn');
      if (btn){
        const combo = btn.closest('.combo');
        const open = combo.hasAttribute('data-open');
        document.querySelectorAll('.combo[data-open]').forEach(c=> c.removeAttribute('data-open'));
        if (!open){ combo.setAttribute('data-open',''); btn.setAttribute('aria-expanded','true'); }
        else { combo.removeAttribute('data-open'); btn.setAttribute('aria-expanded','false'); }
        return;
      }

      const opt = e.target.closest('.combo .combo-opt');
      if (opt){
        const radio = opt.querySelector('input[type="radio"]');
        if (radio){
          radio.checked = true;
          const combo = opt.closest('.combo');
          combo.querySelector('.combo-label').textContent = opt.querySelector('span strong')?.textContent || 'Selecionado';
          const hidden = combo.querySelector('input[type="hidden"]');
          hidden.value = radio.value;
          combo.removeAttribute('data-open');
          combo.querySelector('.combo-btn')?.setAttribute('aria-expanded','false');
          renderBuyPreview(hidden.value);
        }
        return;
      }

      if (!e.target.closest('.combo')) {
        document.querySelectorAll('.combo[data-open]').forEach(c=> c.removeAttribute('data-open'));
        document.querySelectorAll('.combo .combo-btn').forEach(b=> b.setAttribute('aria-expanded','false'));
      }
    });

    function renderBuyPreview(campId){
      const wrap = document.getElementById('camp-preview');
      if (!wrap) return;
      const title = wrap.querySelector('.mini-title');
      const meta  = wrap.querySelector('.mini-meta');
      const gal   = wrap.querySelector('.mini-gal');
      const c = CAMPS.find(x => String(x.id) === String(campId));
      if (!c){ wrap.hidden = true; title.textContent=''; meta.innerHTML=''; gal.innerHTML=''; return; }
      wrap.hidden = false;
      title.textContent = c.title || '—';
      meta.innerHTML = `${badge(c.status||'inactive')} ${c.target_url ? `<a class="chip-link" href="${escapeAttr(c.target_url)}" target="_blank" rel="noopener">Abrir link</a>` : ''}`;
      const imgs = imagesOf(c);
      gal.innerHTML = imgs.length
        ? imgs.map(it=>`<img class="mini-gal-it" src="${escapeAttr(it.url)}" alt="${escapeHtml(it.k)}">`).join('')
        : '<div class="mini-gal-ph">Sem imagens</div>';
    }

    // -------- planos --------
    async function loadPlans(){
      const area = document.getElementById('plans');
      if (!area) return;

      const r = await fetch('/?r=api/partner/ads/plans', {cache:'no-store'});
      let j; try { j = await r.json(); } catch(e){ area.innerHTML='<p class="muted">Erro de resposta.</p>'; return; }
      if(!j.ok){ area.innerHTML='<p class="muted">Falha ao carregar planos.</p>'; return; }
      const rows = j.data || [];
      if(!rows.length){ area.innerHTML = '<p class="muted">Nenhum plano ativo no momento.</p>'; return; }

      area.innerHTML = rows.map(p => {
        const quota = Number(p.view_quota||0);
        const price = Number(p.price||0);
        const cpm   = quota>0 ? (price / quota) * 1000 : 0;
        return `
          <article class="plan-card modern" data-plan="${p.id}">
            <header class="p-head"><h3 class="p-name">${escapeHtml(p.name)}</h3></header>
            <div class="p-body">
              <div class="p-row"><span class="lbl">Views incluídas</span><span class="val">${quota.toLocaleString('pt-BR')}</span></div>
              <div class="p-row"><span class="lbl">Preço</span><span class="val strong">R$ ${price.toFixed(2)}</span></div>
              <div class="p-row sm"><span class="lbl">CPM aprox.</span><span class="val">R$ ${cpm.toFixed(2)}</span></div>
            </div>
            <footer class="p-actions"><button type="button" class="btn btn-buy" data-buy="${p.id}">Comprar</button></footer>
          </article>
        `;
      }).join('');

      area.querySelectorAll('[data-buy]').forEach(btn=>{
        btn.addEventListener('click', async ()=>{
          const campaignId = document.getElementById('buy-campaign')?.value;
          if(!campaignId){ return flash('warn','Selecione uma campanha antes de comprar.'); }
          const card = btn.closest('.plan-card');
          lockCard(card, true, 'Criando pedido...');
          try{
            const fd1 = new FormData(); fd1.set('plan_id', btn.dataset.buy); fd1.set('campaign_id', campaignId);
            const r1  = await fetch('/?r=api/partner/ads/order',{method:'POST', body:fd1});
            const j1  = await r1.json();
            if(!j1.ok){ throw new Error(j1.error||'Falha ao criar pedido'); }

            const fd2 = new FormData(); fd2.set('order_id', j1.data.order_id);
            const r2  = await fetch('/?r=api/partner/ads/pay',{method:'POST', body:fd2});
            const j2  = await r2.json();
            if(!j2.ok){ throw new Error(j2.error||'Falha ao iniciar pagamento'); }
            const url = j2.data && (j2.data.openUrl || j2.data.bankSlipUrl || j2.data.invoiceUrl || j2.data.checkout_url);
            if(url){ window.open(url, '_blank', 'noopener'); }
            flash('ok','Pedido criado! Se o boleto/recibo não abriu, use o botão Pagar em "Meus pedidos".', 8000);
            loadOrders();
          }catch(e){ flash('err', e.message || 'Erro ao processar compra'); }
          finally{ lockCard(card, false); }
        });
      });
    }

    function lockCard(card, on, text='Processando...'){
      if(!card) return;
      let ov = card.querySelector('.p-overlay');
      if(on){
        if(!ov){
          ov = document.createElement('div');
          ov.className='p-overlay';
          ov.innerHTML = `<div class="spinner"></div><div class="p-olbl">${escapeHtml(text)}</div>`;
          card.appendChild(ov);
        } else {
          const lbl = ov.querySelector('.p-olbl'); if (lbl) lbl.textContent = text;
        }
      } else { ov?.remove(); }
    }

    // -------- pedidos --------
    async function payOrder(orderId, el){
      if(!orderId) return;
      if(el){ el.disabled = true; el.textContent = 'Gerando...'; }
      try{
        const fd = new FormData(); fd.set('order_id', orderId);
        const r  = await fetch('/?r=api/partner/ads/pay',{method:'POST', body:fd});
        const j  = await r.json();
        if(!j.ok){ throw new Error(j.error||'Falha ao iniciar pagamento'); }
        const url = j.data && (j.data.openUrl || j.data.bankSlipUrl || j.data.invoiceUrl || j.data.checkout_url);
        if(url){ window.open(url, '_blank', 'noopener'); }
        flash('ok','Pagamento iniciado. Se já pagou, clique em “Atualizar status” em alguns minutos.');
        loadOrders();
      }catch(e){ flash('err', e.message || 'Erro ao pagar pedido'); }
      finally{ if(el){ el.disabled = false; el.textContent = 'Pagar'; } }
    }

    async function reconcileOrder(orderId, el){
      if(!orderId) return;
      if(el){ el.disabled = true; el.textContent = 'Atualizando...'; }
      try{
        const fd = new FormData(); fd.set('order_id', orderId);
        const r  = await fetch('/?r=api/partner/ads/reconcile',{method:'POST', body:fd});
        const j  = await r.json();
        if(!j.ok){ throw new Error(j.error||'Falha na conciliação'); }

        if (j.data && j.data.status === 'active') flash('ok','Seu pagamento foi identificado e o anúncio foi ativado.');
        else if (j.data && j.data.status === 'pending_payment') flash('warn','Pagamento ainda não identificado. Tente novamente em instantes.');
        else flash('warn','Status atualizado: ' + (j.data?.status || 'indisponível'));
        loadOrders();
      }catch(e){ flash('err', e.message || 'Erro ao atualizar status'); }
      finally{ if(el){ el.disabled = false; el.textContent = 'Atualizar status'; } }
    }

    async function loadOrders(){
      const area = document.getElementById('orders');
      if (!area) return;

      const r = await fetch('/?r=api/partner/ads/my', {cache:'no-store'});
      let j; try { j = await r.json(); } catch(e){ area.innerHTML='<p class="muted">Erro de resposta.</p>'; return; }
      if(!j.ok){ area.innerHTML='<p class="muted">Falha ao carregar pedidos.</p>'; return; }
      const rows = j.data || [];

      if(!rows.length){ area.innerHTML = '<p class="muted">Você ainda não tem pedidos.</p>'; return; }

      let html = `<table class="tbl"><thead><tr>
        <th>ID</th><th>Campanha</th><th>Plano</th><th>Status</th><th>Quota</th><th>Usadas</th><th>Valor</th><th>Criado em</th><th>Ação</th>
      </tr></thead><tbody>`;

      rows.forEach(o=>{
        const st = String(o.status||'').toLowerCase();
        const canPay   = (st==='pending_payment' || st==='canceled') && Number(o.amount||0) > 0;
        const canRecon = (st==='pending_payment');
        html += `<tr>
          <td>${escapeHtml(o.id)}</td>
          <td>${escapeHtml(o.campaign_title||'-')} ${o.campaign_status?`<small class="muted">(${escapeHtml(o.campaign_status)})</small>`:''}</td>
          <td>${escapeHtml(o.plan_name)}</td>
          <td>${badge(o.status)}</td>
          <td>${escapeHtml(o.quota_total)}</td>
          <td>${escapeHtml(o.quota_used)}</td>
          <td>R$ ${Number(o.amount||0).toFixed(2)}</td>
          <td>${escapeHtml((o.created_at||'').replace('T',' ').replace('Z',''))}</td>
          <td>
            ${canPay ? `<button type="button" class="btn btn-sm" data-pay="${escapeAttr(o.id)}">Pagar</button>` : ''}
            ${canRecon ? `<button type="button" class="btn btn-sm btn--ghost" data-recon="${escapeAttr(o.id)}">Atualizar status</button>` : (!canPay ? '—' : '')}
          </td>
        </tr>`;
      });

      html += `</tbody></table>`;
      area.innerHTML = html;

      area.querySelectorAll('[data-pay]').forEach(btn=> btn.addEventListener('click', ()=> payOrder(btn.dataset.pay, btn)));
      area.querySelectorAll('[data-recon]').forEach(btn=> btn.addEventListener('click', ()=> reconcileOrder(btn.dataset.recon, btn)));
    }

    (async function(){
      await loadCampaigns();
      await loadPlans();
      await loadOrders();
    })();
  });
})();
</script>

<style>
:root{
  --card-bg: #ffffff;
  --card-border: #e5ecf3;
  --card-radius: 16px;
  --card-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
  --text-main: #111827;
  --text-muted: #6b7280;
  --accent: #2563eb;
  --accent-soft: #eff6ff;
  --danger: #dc2626;
  --success: #15803d;
  --warning: #ea580c;
}
.no-scroll{ overflow:hidden; }

/* ===== MENU (div cheia) ===== */
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

/* ====== SEU CSS ORIGINAL (dashboard) ====== */
.board{
  display:grid;
  grid-template-columns: minmax(0,1.4fr) minmax(0,1fr);
  gap:16px;
  align-items:flex-start;
}
@media (max-width:1100px){
  .board{ grid-template-columns:1fr; }
}
.draft .sect-sub{margin:0}
.mycamps-head{
  display:flex;
  align-items:baseline;
  gap:8px;
  justify-content:space-between;
}
.form-camp{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:12px 16px;
}
.span-all{ grid-column:1 / -1; }
.field{
  display:flex;
  flex-direction:column;
  gap:6px;
}
.field span{
  font-size:.9rem;
  color:var(--text-muted);
}
.field input{
  box-sizing:border-box;
  width:100%;
  min-width:0;
  padding:10px 12px;
  border-radius:10px;
  border:1px solid #d1d9e6;
  background:#ffffff;
  color:var(--text-main);
  outline:none;
  font-size:.92rem;
}
.field input:focus{
  border-color:var(--accent);
  box-shadow:0 0 0 1px rgba(37,99,235,0.15);
}
.input-row{
  display:flex;
  gap:8px;
  align-items:center;
}
.ghost-link{
  font-size:.85rem;
  border:1px solid #d1d9e6;
  padding:8px 10px;
  border-radius:999px;
  background:#f9fafb;
  text-decoration:none;
  color:var(--accent);
  font-weight:500;
  white-space:nowrap;
}
.ghost-link:hover{
  background:#e5efff;
  border-color:#c4d3ff;
}
.ghost-link.ghost-disabled{
  opacity:.45;
  pointer-events:none;
}
.subttl{
  font-weight:700;
  color:var(--text-main);
  margin-bottom:.35rem;
  margin-top:.25rem;
}
.img-grid{
  display:grid;
  gap:10px;
  grid-template-columns:repeat(auto-fill,minmax(220px,1fr));
}
.img-tile{
  border:1px solid var(--card-border);
  border-radius:12px;
  background:#ffffff;
  padding:10px;
  display:flex;
  flex-direction:column;
  gap:8px;
}
.tile-label{
  font-size:.86rem;
  color:var(--text-muted);
}
.thumb{
  border:1px dashed #d8e1ec;
  border-radius:10px;
  background:#f9fafb;
  height:110px;
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
}
.thumb img{
  max-width:100%;
  max-height:100%;
}
.actions{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  justify-content:flex-start;
  margin-top:.6rem;
}
.camp-list-wrap{
  height: 780px;
  overflow:auto;
  border:1px solid var(--card-border);
  border-radius:var(--card-radius);
  padding:10px;
  background:#f9fafb;
}
.camp-list{
  display:grid;
  gap:12px;
}
.camp-card{
  border:1px solid var(--card-border);
  border-radius:12px;
  background:var(--card-bg);
  padding:12px;
  color:var(--text-main);
  display:flex;
  flex-direction:column;
  gap:10px;
  box-shadow:0 4px 18px rgba(15,23,42,0.03);
  min-height: 230px;
}
.camp-card-head{
  display:flex;
  align-items:flex-start;
  justify-content:space-between;
  gap:10px;
}
.c-title{
  margin:0 0 4px;
  font-weight:700;
  font-size:1rem;
}
.c-meta{
  display:flex;
  gap:8px;
  flex-wrap:wrap;
  align-items:center;
}
.cch-actions{
  display:flex;
  gap:8px;
}
.chip-link{
  display:inline-flex;
  align-items:center;
  padding:4px 10px;
  border-radius:999px;
  border:1px solid #d1d9e6;
  background:#f3f4ff;
  font-size:.8rem;
  color:var(--accent);
  text-decoration:none;
  font-weight:500;
}
.chip-link:hover{ background:#e5ebff; }
.gal-mini{
  display:grid;
  grid-template-columns:repeat(5, minmax(0,1fr));
  gap:8px;
}
.gm-it{
  position:relative;
  border:1px solid #e0e7f0;
  border-radius:10px;
  overflow:hidden;
  background:#f9fafb;
  display:block;
}
.gm-it img{
  width:100%;
  height:74px;
  object-fit:cover;
  display:block;
}
.gm-tag{
  position:absolute;
  left:6px;
  bottom:6px;
  background:rgba(15,23,42,.75);
  color:#f9fafb;
  border-radius:6px;
  padding:2px 6px;
  font-size:.7rem;
}
.gm-ph{
  border:1px dashed #d0d8e0;
  border-radius:10px;
  padding:10px;
  text-align:center;
  color:#9ca3af;
  font-size:.85rem;
}
.camp-edit{
  display:none;
  border-top:1px dashed #e0e7f0;
  padding-top:10px;
  margin-top:6px;
}
.camp-edit[data-editing="1"]{ display:block; }

.combo{ position:relative; }
.combo--thick .combo-btn{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding:11px 14px;
  border-radius:12px;
  border:1px solid #d1d9e6;
  background:#ffffff;
  color:var(--text-main);
  cursor:pointer;
  font-size:.95rem;
  font-weight:600;
}
.combo-btn svg{ flex-shrink:0; }
.combo-btn:focus-visible{
  outline:2px solid rgba(37,99,235,0.45);
  outline-offset:2px;
}
.combo[data-open] .combo-btn{
  border-color:var(--accent);
  box-shadow:0 0 0 1px rgba(37,99,235,0.15);
}
.combo-menu{
  position:absolute;
  top:calc(100% + 6px);
  left:0;
  right:0;
  display:none;
  background:#ffffff;
  color:var(--text-main);
  border:1px solid #d1d9e6;
  border-radius:12px;
  padding:8px;
  z-index:50;
  box-shadow:0 18px 40px rgba(15,23,42,.12);
}
.combo[data-open] .combo-menu{ display:block; }
.combo-list{
  max-height:240px;
  overflow:auto;
  display:grid;
  gap:6px;
  padding-right:4px;
}
.combo-opt{
  display:flex;
  align-items:center;
  gap:10px;
  padding:9px 10px;
  border-radius:10px;
  background:#f9fafb;
  border:1px solid transparent;
  cursor:pointer;
  font-size:.9rem;
}
.combo-opt:hover{
  background:#eef2ff;
  border-color:#c7d2fe;
}
.combo-opt input{ accent-color:var(--accent); }
.combo-empty{
  padding:8px 10px;
  border-radius:10px;
  background:#f9fafb;
  border:1px dashed #d1d9e6;
  font-size:.9rem;
  color:var(--text-muted);
}
.mini-camp{
  border:1px solid var(--card-border);
  border-radius:12px;
  background:#f9fafb;
  padding:10px;
}
.mini-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
}
.mini-title{
  margin:0;
  font-weight:700;
  color:var(--text-main);
  font-size:.98rem;
}
.mini-meta{
  display:flex;
  gap:8px;
  align-items:center;
  flex-wrap:wrap;
}
.mini-gal{
  margin-top:8px;
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:8px;
}
.mini-gal-it{
  width:100%;
  height:80px;
  object-fit:cover;
  border-radius:8px;
  border:1px solid #dde5f0;
}
.mini-gal-ph{
  border:1px dashed #d0d8e0;
  border-radius:8px;
  padding:8px;
  color:#9ca3af;
  text-align:center;
  font-size:.86rem;
}
.buy-grid{
  display:grid;
  grid-template-columns:360px minmax(0,1fr);
  gap:16px;
}
.buy-left{
  display:flex;
  flex-direction:column;
  gap:10px;
}
.buy-right{min-width:0}
@media (max-width:980px){
  .buy-grid{ grid-template-columns:1fr; }
}
.plans-grid{
  display:grid;
  grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
  gap:12px;
  padding:0;
}
.plan-card.modern{
  position:relative;
  border:1px solid var(--card-border);
  border-radius:14px;
  background:#ffffff;
  padding:12px;
  color:var(--text-main);
  box-shadow:0 6px 20px rgba(15,23,42,0.04);
}
.p-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
}
.p-name{
  margin:0;
  font-weight:700;
  font-size:.98rem;
}
.p-body{
  margin-top:8px;
  display:grid;
  gap:6px;
}
.p-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  font-size:.9rem;
}
.p-row.sm{
  opacity:.9;
  font-size:.85rem;
}
.p-row .lbl{ color:var(--text-muted); }
.p-row .val.strong{ font-weight:700; }
.p-actions{
  margin-top:10px;
  display:flex;
  justify-content:flex-end;
}
.p-overlay{
  position:absolute;
  inset:0;
  background:rgba(249,250,251,.8);
  backdrop-filter:blur(2px);
  display:flex;
  align-items:center;
  justify-content:center;
  flex-direction:column;
  gap:8px;
  border-radius:14px;
}
.spinner{
  width:22px;
  height:22px;
  border-radius:50%;
  border:3px solid rgba(148,163,184,.7);
  border-top-color:var(--accent);
  animation:sp .9s linear infinite;
}
.p-olbl{
  color:var(--text-main);
  font-weight:600;
  font-size:.9rem;
}
@keyframes sp{ to{transform:rotate(1turn)} }

.tbl{
  width:100%;
  border-collapse:collapse;
  font-size:.9rem;
}
.tbl thead tr{ background:#f9fafb; }
.tbl th,
.tbl td{
  border-bottom:1px solid #e5ecf3;
  padding:8px;
  text-align:left;
}
.tbl th{
  font-weight:600;
  color:var(--text-muted);
}
.tbl td small.muted{
  color:var(--text-muted);
  font-weight:400;
}
.bdg{
  display:inline-block;
  padding:.18rem .6rem;
  border-radius:999px;
  font-size:.75rem;
  line-height:1;
  font-weight:700;
  border:1px solid transparent;
}
.bdg--ok{
  background:#ecfdf3;
  color:#166534;
  border-color:#bbf7d0;
}
.bdg--warn{
  background:#fff7ed;
  color:#9a3412;
  border-color:#fed7aa;
}
.bdg--err{
  background:#fef2f2;
  color:#b91c1c;
  border-color:#fecaca;
}
.bdg--muted{
  background:#eff4fb;
  color:#3b556e;
  border-color:#d6e0ea;
}
.btn{
  padding:9px 14px;
  border-radius:999px;
  border:1px solid var(--accent);
  background:var(--accent);
  color:#ffffff;
  cursor:pointer;
  font-size:.9rem;
  font-weight:600;
  line-height:1.1;
  transition:
    background-color .15s ease,
    border-color .15s ease,
    box-shadow .15s ease,
    transform .05s ease;
}
.btn:hover{
  background:#1d4ed8;
  border-color:#1d4ed8;
  box-shadow:0 8px 20px rgba(37,99,235,0.25);
  transform:translateY(-1px);
}
.btn:active{
  transform:translateY(0);
  box-shadow:0 3px 8px rgba(37,99,235,0.15);
}
.btn:disabled{
  opacity:.6;
  cursor:default;
  box-shadow:none;
}
.btn.btn-sm{
  padding:7px 11px;
  font-size:.82rem;
}
.btn--ghost{
  background:#ffffff;
  border:1px solid #d0d8e0;
  color:var(--text-main);
}
.btn--ghost:hover{
  background:#f3f4f6;
  border-color:#cbd5e1;
}

/* Modal flash */
.mfx-overlay{
  position:fixed;
  inset:0;
  background:rgba(15,23,42,.45);
  display:grid;
  place-items:center;
  z-index:9999;
  opacity:0;
  transition:opacity .15s ease;
}
.mfx-overlay[data-open]{ opacity:1; }
.mfx-box{
  width:min(560px, 92vw);
  background:#ffffff;
  color:var(--text-main);
  border:1px solid var(--card-border);
  border-radius:14px;
  box-shadow:0 18px 60px rgba(15,23,42,.22);
  transform:scale(.95);
  transition:transform .15s ease;
}
.mfx-overlay[data-open] .mfx-box{ transform:scale(1); }
.mfx-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:8px;
  padding:12px 14px;
  border-bottom:1px solid #e5ecf3;
}
.mfx-title{ margin:0; font-weight:700; }
.mfx-close{
  appearance:none;
  border:0;
  background:transparent;
  color:#6b7280;
  font-size:22px;
  line-height:1;
  cursor:pointer;
  padding:6px;
  border-radius:8px;
}
.mfx-close:hover{
  background:#f3f4f6;
  color:#111827;
}
.mfx-body{
  padding:14px;
  font-size:.95rem;
  color:var(--text-main);
}
.mfx-box.mfx--ok{
  box-shadow:0 18px 60px rgba(22,163,74,.18);
  border-color:#bbf7d0;
}
.mfx-box.mfx--warn{
  box-shadow:0 18px 60px rgba(234,179,8,.18);
  border-color:#fed7aa;
}
.mfx-box.mfx--err{
  box-shadow:0 18px 60px rgba(239,68,68,.2);
  border-color:#fecaca;
}
</style>
