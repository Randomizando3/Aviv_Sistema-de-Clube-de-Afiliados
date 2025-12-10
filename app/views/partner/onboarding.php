<section class="container partner-onb">
  <div class="glass-card">
    <h1 class="sect-title">Cadastro de Parceiro</h1>
    <p class="muted">Preencha os dados do seu negócio para análise e aprovação.</p>

    <form class="form-grid mtop" onsubmit="return false">
      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/></svg>
        <input type="text" id="p_name" placeholder="Nome do negócio" required>
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18"/></svg>
        <input type="text" id="p_cnpj" placeholder="CNPJ (opcional)">
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18h18"/></svg>
        <input type="text" id="p_segment" placeholder="Segmento (ex.: Restaurantes, Saúde)">
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>
        <input type="text" id="p_city" placeholder="Cidade/UF (ex.: São Paulo/SP)">
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v16H4z"/></svg>
        <input type="email" id="p_email" placeholder="E-mail de contato" required>
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-3"/></svg>
        <input type="tel" id="p_phone" placeholder="Telefone (opcional)">
      </div>

      <label style="display:flex;align-items:center;gap:8px;margin-top:6px">
        <input type="checkbox" id="p_terms"> Li e aceito os termos de parceria (demo)
      </label>

      <div class="form-actions">
        <button class="btn" id="p_submit" type="button">Enviar para aprovação</button>
        <a class="btn btn--ghost" href="/?r=partner/dashboard">Já sou parceiro</a>
      </div>
    </form>
  </div>
</section>

<script>
  document.getElementById('p_submit')?.addEventListener('click', ()=>{
    const ok = document.getElementById('p_name').value && document.getElementById('p_email').value && document.getElementById('p_terms').checked;
    if(!ok){ alert('Preencha nome, e-mail e aceite os termos.'); return; }
    alert('Cadastro enviado! Nossa equipe irá analisar (demo).');
    location.href='/?r=partner/dashboard';
  });
</script>
