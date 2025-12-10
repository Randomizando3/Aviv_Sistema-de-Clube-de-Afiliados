<section class="container partner-offers">
  <div class="glass-card">
    <header class="sect-head">
      <h1 class="sect-title">Minhas ofertas</h1>
      <button class="btn btn-sm" id="btn-new">Nova oferta</button>
    </header>

    <!-- Filtros -->
    <div class="filters-inline">
      <select id="o-status" aria-label="Status">
        <option value="">Todos</option>
        <option value="ativa">Ativa</option>
        <option value="pausada">Pausada</option>
        <option value="expirada">Expirada</option>
      </select>
      <select id="o-cat" aria-label="Categoria">
        <option value="">Todas</option>
        <option value="restaurantes">Restaurantes</option>
        <option value="saude">Saúde</option>
        <option value="educacao">Educação</option>
        <option value="servicos">Serviços</option>
        <option value="lazer">Lazer</option>
        <option value="compras">Compras</option>
      </select>
    </div>

    <!-- Lista -->
    <div class="offers-grid" id="offer-list">
      <!-- Preenchido via JS com dados fake/localStorage -->
    </div>
  </div>
</section>

<!-- Modal: Nova/Editar oferta -->
<div class="modal" id="offer-modal" role="dialog" aria-modal="true" aria-labelledby="offer-modal-title">
  <div class="modal-box glass-card">
    <h3 id="offer-modal-title" style="margin:0 0 8px">Oferta</h3>
    <form class="form-grid" id="offer-form" onsubmit="return false">
      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/></svg>
        <input type="text" id="f_title" placeholder="Título da oferta (ex.: Almoço -20%)" required>
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18"/></svg>
        <input type="text" id="f_desc" placeholder="Descrição curta (ex.: seg–sex 12h–15h)">
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18h18"/></svg>
        <select id="f_cat" required>
          <option value="">Categoria</option>
          <option value="restaurantes">Restaurantes</option>
          <option value="saude">Saúde</option>
          <option value="educacao">Educação</option>
          <option value="servicos">Serviços</option>
          <option value="lazer">Lazer</option>
          <option value="compras">Compras</option>
        </select>
      </div>

      <div class="grid-2">
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/></svg>
          <input type="date" id="f_from" placeholder="Validade de">
        </div>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20"/></svg>
          <input type="date" id="f_to" placeholder="Validade até">
        </div>
      </div>

      <div class="grid-2">
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/></svg>
          <input type="text" id="f_type" placeholder="Tipo (ex.: % OFF, R$ OFF, MEIA)" >
        </div>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18"/></svg>
          <input type="number" id="f_limit" min="0" placeholder="Limite total (0 = ilimitado)">
        </div>
      </div>

      <label style="display:flex;align-items:center;gap:8px">
        <input type="checkbox" id="f_per_user"> Limitar 1 por usuário
      </label>

      <fieldset class="plan-checks">
        <legend>Planos elegíveis</legend>
        <label><input type="checkbox" id="pl_start" checked> Start</label>
        <label><input type="checkbox" id="pl_plus" checked> Plus</label>
        <label><input type="checkbox" id="pl_prime" checked> Prime</label>
      </fieldset>

      <div class="form-actions">
        <button class="btn btn-sm" id="f_save" type="submit">Salvar</button>
        <button class="btn btn-sm btn--ghost" id="f_cancel" type="button">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
  // ====== Mock/localStorage de ofertas ======
  const key = 'partner_offers';
  function getOffers(){
    try{ return JSON.parse(localStorage.getItem(key) || '[]'); }catch(e){ return []; }
  }
  function setOffers(list){
    try{ localStorage.setItem(key, JSON.stringify(list)); }catch(e){}
  }
  function seed(){
    if(getOffers().length) return;
    setOffers([
      {id:'of1', title:'Almoço -20%', desc:'seg–sex 12h–15h', cat:'restaurantes', from:'2025-08-01', to:'2025-09-30', type:'%OFF', limit:200, perUser:true, plans:['start','plus','prime'], status:'ativa'},
      {id:'of2', title:'Check-up -10%', desc:'agendamento online', cat:'saude', from:'2025-07-01', to:'2025-12-31', type:'%OFF', limit:0, perUser:false, plans:['start','plus','prime'], status:'ativa'},
      {id:'of3', title:'1º mês grátis', desc:'turmas de inglês', cat:'educacao', from:'2025-08-10', to:'2025-10-10', type:'GRATIS', limit:100, perUser:true, plans:['plus','prime'], status:'pausada'},
      {id:'of4', title:'Meia entrada', desc:'sessões 2D/3D', cat:'lazer', from:'2025-06-01', to:'2025-08-15', type:'MEIA', limit:300, perUser:false, plans:['plus','prime'], status:'expirada'}
    ]);
  }
  seed();

  // Render
  const listEl = document.getElementById('offer-list');
  function render(){
    const s = document.getElementById('o-status').value;
    const c = document.getElementById('o-cat').value;
    const arr = getOffers().filter(o => (!s || o.status===s) && (!c || o.cat===c));
    listEl.innerHTML = '';
    if(!arr.length){
      listEl.innerHTML = `<div class="glass-card muted" style="grid-column:1/-1">Nenhuma oferta encontrada.</div>`;
      return;
    }
    arr.forEach(o=>{
      const el = document.createElement('article');
      el.className = 'offer-card glass-card';
      el.innerHTML = `
        <header class="offer-head">
          <div class="oh-left">
            <strong>${o.title}</strong>
            <span class="muted">${o.desc || ''}</span>
          </div>
          <div class="oh-right">
            <span class="badge">${o.type ?? ''}</span>
            <span class="chip ${o.status==='ativa'?'chip-success':o.status==='pausada'?'chip-pending':'chip-failed'}">${o.status}</span>
          </div>
        </header>
        <div class="offer-meta">
          <span>Categoria: <b>${o.cat}</b></span>
          <span>Validade: <b>${o.from || '—'} a ${o.to || '—'}</b></span>
          <span>Limite: <b>${o.limit===0?'ilimitado':o.limit}</b>${o.perUser?' • 1 por usuário':''}</span>
          <span>Planos: <b>${o.plans.join(', ')}</b></span>
        </div>
        <div class="offer-actions">
          <button class="btn btn-sm" data-edit="${o.id}">Editar</button>
          <button class="btn btn-sm btn--ghost" data-toggle="${o.id}">${o.status==='ativa'?'Pausar':'Ativar'}</button>
          <button class="btn btn-sm btn--ghost" data-del="${o.id}">Excluir</button>
        </div>
      `;
      listEl.appendChild(el);
    });
  }
  document.getElementById('o-status')?.addEventListener('change', render);
  document.getElementById('o-cat')?.addEventListener('change', render);

  // Modal abrir/fechar
  const modal = document.getElementById('offer-modal');
  function openModal(){ modal.classList.add('is-open'); }
  function closeModal(){ modal.classList.remove('is-open'); }
  document.getElementById('f_cancel')?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e)=>{ if(e.target===modal) closeModal(); });

  // Nova
  let editingId = null;
  document.getElementById('btn-new')?.addEventListener('click', ()=>{
    editingId = null;
    document.getElementById('offer-modal-title').textContent = 'Nova oferta';
    document.getElementById('offer-form').reset();
    document.getElementById('pl_start').checked = true;
    document.getElementById('pl_plus').checked = true;
    document.getElementById('pl_prime').checked = true;
    openModal();
  });

  // Delegação de eventos (editar, pausar/ativar, excluir)
  listEl.addEventListener('click', (e)=>{
    const btn = e.target.closest('button');
    if(!btn) return;
    const id = btn.dataset.edit || btn.dataset.toggle || btn.dataset.del;
    const arr = getOffers();
    const idx = arr.findIndex(x=>x.id===id);
    if(idx<0) return;

    if(btn.dataset.edit){
      editingId = id;
      const o = arr[idx];
      document.getElementById('offer-modal-title').textContent = 'Editar oferta';
      f_title.value = o.title; f_desc.value = o.desc || ''; f_cat.value = o.cat;
      f_from.value = o.from || ''; f_to.value = o.to || ''; f_type.value = o.type || '';
      f_limit.value = o.limit || 0; f_per_user.checked = !!o.perUser;
      pl_start.checked = o.plans.includes('start');
      pl_plus.checked  = o.plans.includes('plus');
      pl_prime.checked = o.plans.includes('prime');
      openModal();
    }
    else if(btn.dataset.toggle){
      arr[idx].status = (arr[idx].status==='ativa'?'pausada':'ativa');
      setOffers(arr); render();
    }
    else if(btn.dataset.del){
      if(confirm('Excluir oferta?')){ arr.splice(idx,1); setOffers(arr); render(); }
    }
  });

  // Salvar
  const f = document.getElementById('offer-form');
  const f_title = document.getElementById('f_title');
  const f_desc = document.getElementById('f_desc');
  const f_cat = document.getElementById('f_cat');
  const f_from = document.getElementById('f_from');
  const f_to = document.getElementById('f_to');
  const f_type = document.getElementById('f_type');
  const f_limit = document.getElementById('f_limit');
  const f_per_user = document.getElementById('f_per_user');
  const pl_start = document.getElementById('pl_start');
  const pl_plus  = document.getElementById('pl_plus');
  const pl_prime = document.getElementById('pl_prime');

  f.addEventListener('submit', ()=>{
    if(!f_title.value || !f_cat.value){ alert('Preencha título e categoria.'); return; }
    const payload = {
      id: editingId || ('of' + Math.random().toString(36).slice(2,7)),
      title: f_title.value.trim(),
      desc: f_desc.value.trim(),
      cat: f_cat.value,
      from: f_from.value, to: f_to.value,
      type: f_type.value.trim() || '%OFF',
      limit: parseInt(f_limit.value || '0', 10),
      perUser: !!f_per_user.checked,
      plans: [
        ...(pl_start.checked?['start']:[]),
        ...(pl_plus.checked?['plus']:[]),
        ...(pl_prime.checked?['prime']:[])
      ],
      status: 'ativa'
    };
    const arr = getOffers();
    const idx = arr.findIndex(x=>x.id===payload.id);
    if(idx>=0) arr[idx] = {...arr[idx], ...payload};
    else arr.unshift(payload);
    setOffers(arr); closeModal(); render();
  });

  render();
</script>
