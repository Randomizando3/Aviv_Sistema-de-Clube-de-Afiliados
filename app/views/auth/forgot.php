<main class="login-card" aria-label="Recuperar acesso">
  <h1 class="login-title">Recuperar acesso</h1>

  <form id="forgot-form" action="#" method="post" novalidate>
    <label class="sr-only" for="email">E-mail</label>
    <div class="input-wrap">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/>
      </svg>
      <input id="email" name="email" type="email" placeholder="Seu e-mail cadastrado" required>
    </div>

    <div class="login-help">
      <span></span>
      <a href="/?r=auth/login">Voltar ao login</a>
    </div>

    <button id="forgot-btn" class="btn-login" type="submit">Enviar link de reset</button>
    <div id="forgot-alert" class="alert" role="status" aria-live="polite"></div>

    <p class="login-foot">
      Não tem conta?
      <a href="/?r=auth/register">Criar conta</a>
    </p>
  </form>
</main>

<script>
  const ff = document.getElementById('forgot-form');
  const fa = document.getElementById('forgot-alert');
  const fb = document.getElementById('forgot-btn');

  ff.addEventListener('submit', (e) => {
    e.preventDefault();
    fa.innerHTML = `
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
      </svg>
      Se o e-mail existir, enviaremos instruções de recuperação. (demo)
    `;
    fa.classList.add('is-shown');
    fb.disabled = true;
    setTimeout(() => { fb.disabled = false; }, 2500);
  });
</script>
