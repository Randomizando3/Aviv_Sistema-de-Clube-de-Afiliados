<main class="login-shell" aria-label="Recuperar acesso">
  <section class="login-card">
    <h1 class="login-title">Recuperar acesso</h1>
    <p class="login-sub">
      Informe o e-mail cadastrado. Se encontrarmos uma conta, enviaremos um link para redefinir sua senha.
    </p>

    <form id="forgot-form" action="#" method="post" novalidate>
      <label class="sr-only" for="email">E-mail</label>
      <div class="input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" aria-hidden="true">
          <rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect>
          <polyline points="22 6 12 13 2 6"></polyline>
        </svg>
        <input
          id="email"
          name="email"
          type="email"
          placeholder="Seu e-mail cadastrado"
          autocomplete="email"
          required>
      </div>

      <div class="login-help">
        <span></span>
        <a href="/?r=auth/login">Voltar ao login</a>
      </div>

      <button id="forgot-btn" class="btn-login" type="submit">
        Enviar link de reset
      </button>

      <div id="forgot-alert" class="alert" role="status" aria-live="polite"></div>

      <p class="login-foot">
        Não tem conta?
        <a href="/?r=auth/register">Criar conta</a>
      </p>
    </form>
  </section>
</main>

<script>
  const ff = document.getElementById('forgot-form');
  const fa = document.getElementById('forgot-alert');
  const fb = document.getElementById('forgot-btn');

  ff.addEventListener('submit', (e) => {
    e.preventDefault();

    // estado "enviando"
    fb.disabled = true;
    fb.classList.add('is-loading');
    fb.textContent = 'Enviando…';

    // mensagem de demo
    fa.innerHTML = `
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10"></circle>
        <path d="M12 8v4"></path>
        <path d="M12 16h.01"></path>
      </svg>
      Se o e-mail existir, enviaremos instruções de recuperação.
    `;
    fa.classList.add('is-shown');

    // “reset” do botão após alguns segundos (demo)
    setTimeout(() => {
      fb.disabled = false;
      fb.classList.remove('is-loading');
      fb.textContent = 'Enviar link de reset';
    }, 2500);
  });
</script>

<style>
/* Acessibilidade básica */
.sr-only{
  position:absolute;
  width:1px;
  height:1px;
  padding:0;
  margin:-1px;
  overflow:hidden;
  clip:rect(0,0,0,0);
  white-space:nowrap;
  border:0;
}

/* Shell centralizado (opcional, só atua se não tiver outro layout) */
.login-shell{
  min-height: calc(100vh - 80px);
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px 16px 40px;
}

/* Card principal */
.login-card{
  width:100%;
  max-width:420px;
  background:rgba(255,255,255,.96);
  border-radius:18px;
  padding:22px 22px 18px;
  border:1px solid rgba(15,23,42,.06);
  box-shadow:0 18px 40px rgba(15,23,42,.10);
  box-sizing:border-box;
}

.login-title{
  margin:0 0 4px;
  font-size:1.4rem;
  font-weight:800;
  color:#111322;
}

.login-sub{
  margin:0 0 16px;
  font-size:.9rem;
  color:#6b7280;
}

/* Input + ícone */
.input-wrap{
  display:flex;
  align-items:center;
  gap:8px;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid #d0d7e2;
  background:#f9fafb;
  box-sizing:border-box;
}
.input-wrap svg{
  flex:0 0 auto;
  color:#6b7280;
}
.input-wrap input{
  border:none;
  outline:none;
  background:transparent;
  flex:1 1 auto;
  font-size:.95rem;
  color:#111322;
}
.input-wrap input::placeholder{
  color:#9ca3af;
}

/* Link auxiliar (voltar / etc.) */
.login-help{
  display:flex;
  align-items:center;
  justify-content:space-between;
  margin-top:10px;
  font-size:.85rem;
}
.login-help a{
  color:#2563eb;
  text-decoration:none;
}
.login-help a:hover{
  text-decoration:underline;
}

/* Botão principal */
.btn-login{
  margin-top:14px;
  width:100%;
  border:none;
  border-radius:12px;
  padding:11px 14px;
  font-size:.95rem;
  font-weight:600;
  cursor:pointer;
  background:linear-gradient(135deg,#2563eb,#4f46e5);
  color:#f9fafb;
  box-shadow:0 10px 30px rgba(37,99,235,.24);
  transition:transform .08s ease, box-shadow .12s ease, opacity .12s ease;
}
.btn-login:hover:not(:disabled){
  transform:translateY(-1px);
  box-shadow:0 14px 34px rgba(37,99,235,.28);
}
.btn-login:active:not(:disabled){
  transform:translateY(0);
  box-shadow:0 8px 20px rgba(37,99,235,.2);
}
.btn-login:disabled{
  opacity:.7;
  cursor:default;
}
.btn-login.is-loading{
  cursor:progress;
}

/* Alert */
.alert{
  margin-top:12px;
  padding:9px 10px;
  border-radius:10px;
  border:1px solid #e5e7eb;
  background:#f9fafb;
  color:#111322;
  font-size:.85rem;
  display:none;
  align-items:flex-start;
  gap:8px;
}
.alert svg{
  flex:0 0 auto;
  margin-top:1px;
  color:#2563eb;
}
.alert.is-shown{
  display:flex;
}

/* Rodapé do card */
.login-foot{
  margin:18px 0 0;
  font-size:.85rem;
  color:#6b7280;
  text-align:center;
}
.login-foot a{
  color:#2563eb;
  font-weight:600;
  text-decoration:none;
}
.login-foot a:hover{
  text-decoration:underline;
}
</style>
