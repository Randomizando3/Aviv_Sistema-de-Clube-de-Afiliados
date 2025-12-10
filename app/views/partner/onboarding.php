<section class="container partner-onb">
  <div class="glass-card">
    <h1 class="sect-title">Cadastro de Parceiro</h1>
    <p class="muted">Preencha os dados do seu negócio para análise e aprovação.</p>

    <form class="form-grid mtop" onsubmit="return false">
      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18"/>
        </svg>
        <input type="text" id="p_name" placeholder="Nome do negócio" required>
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 12h18"/>
        </svg>
        <input type="text" id="p_cnpj" placeholder="CNPJ (opcional)">
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 18h18"/>
        </svg>
        <input type="text" id="p_segment" placeholder="Segmento (ex.: Restaurantes, Saúde)">
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2v20M2 12h20"/>
        </svg>
        <input type="text" id="p_city" placeholder="Cidade/UF (ex.: São Paulo/SP)">
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M4 4h16v16H4z"/>
        </svg>
        <input type="email" id="p_email" placeholder="E-mail de contato" required>
      </div>

      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 17v3a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-3"/>
        </svg>
        <input type="tel" id="p_phone" placeholder="Telefone (opcional)">
      </div>

      <label class="terms-row">
        <input type="checkbox" id="p_terms">
        <span>Li e aceito os termos de parceria (demo)</span>
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
    const name  = document.getElementById('p_name').value.trim();
    const email = document.getElementById('p_email').value.trim();
    const terms = document.getElementById('p_terms').checked;

    if (!name || !email || !terms) {
      alert('Preencha nome, e-mail e aceite os termos.');
      return;
    }

    alert('Cadastro enviado! Nossa equipe irá analisar (demo).');
    location.href='/?r=partner/dashboard';
  });
</script>

<style>
/* ===== Cadastro de Parceiro – layout clean ===== */

.partner-onb .glass-card{
  padding:18px 18px 20px;
}

/* grid do formulário */
.partner-onb .form-grid{
  display:grid;
  grid-template-columns:repeat(2, minmax(0,1fr));
  gap:12px 16px;
  margin-top:14px;
}
.partner-onb .form-grid.mtop{
  margin-top:18px;
}
@media (max-width:768px){
  .partner-onb .form-grid{
    grid-template-columns:1fr;
  }
}

/* linha de input com ícone em pílula */
.partner-onb .input-wrap{
  display:flex;
  align-items:center;
  gap:10px;
  padding:8px 12px;
  border-radius:999px;
  border:1px solid #d1d9e6;
  background:#ffffff;
  box-shadow:0 2px 6px rgba(15,23,42,0.03);
}
.partner-onb .input-wrap svg{
  color:#9ca3af;
  flex-shrink:0;
}
.partner-onb .input-wrap input{
  border:none;
  outline:none;
  width:100%;
  min-width:0;
  font-size:.92rem;
  background:transparent;
  color:#111827;
}
.partner-onb .input-wrap input::placeholder{
  color:#9ca3af;
}

/* termos */
.partner-onb .terms-row{
  grid-column:1 / -1;
  display:flex;
  align-items:flex-start;
  gap:8px;
  margin-top:4px;
  font-size:.88rem;
  color:#4b5563;
}
.partner-onb .terms-row input[type="checkbox"]{
  margin-top:2px;
}

/* ações */
.partner-onb .form-actions{
  grid-column:1 / -1;
  display:flex;
  flex-wrap:wrap;
  gap:10px;
  align-items:center;
  justify-content:flex-start;
  margin-top:4px;
}
.partner-onb .form-actions .btn--ghost{
  text-decoration:none;
}
</style>
