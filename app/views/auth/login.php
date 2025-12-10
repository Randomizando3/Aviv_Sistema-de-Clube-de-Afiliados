<?php
// app/views/auth/login.php

$PAGE_TITLE = 'Login • Aviv+';
$PAGE_CLASS = 'site-page'; // usa o fundo/tema público
?>

<div style="width: 100%;">
<?php require __DIR__ . '/../_partials/_head.php'; ?>
<?php require __DIR__ . '/../_partials/_header.php'; ?>
</div>

<style>
/* Fundo clean global da página */
body.site-page {
  background: radial-gradient(circle at top, #e8f2ff 0%, #f5f7fb 45%, #f5f7fb 100%);
}

/* Área principal */
.auth-shell {
  width: 100%;
}

.auth-center {
  width: min(92vw, 1120px);
  margin-inline: auto;
  min-height: calc(100dvh - var(--topnav-h, 64px) - 100px);
  display: grid;
  place-items: center;
  padding: 32px 0 72px;
}

/* Card clean do login */
.login-card {
  width: min(520px, 100%);
  background: #ffffff;
  border-radius: 22px;
  border: 1px solid #e2e8f0;
  box-shadow:
    0 28px 70px rgba(15, 23, 42, .12),
    0 0 0 1px rgba(148, 163, 184, .08);
  padding: 28px;
}

/* Título */
.login-title {
  margin: 0 0 18px 0;
  font-size: 1.6rem;
  font-weight: 800;
  color: #0f172a;
  font-family: "Poppins", sans-serif;
}

/* Inputs */
.input-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 10px 12px;
  background: #fff;
  margin: 10px 0;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
}

.input-wrap svg {
  color: #64748b;
  flex-shrink: 0;
}

.input-wrap input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: .95rem;
}

.input-wrap input::placeholder {
  color: #94a3b8;
}

/* Linha de ajuda */
.login-help {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 8px 0 4px;
  font-size: .9rem;
  color: #475569;
}

.login-help a {
  color: #2563eb;
  font-weight: 600;
  text-decoration: none;
}

.login-help a:hover {
  text-decoration: underline;
}

/* Botão */
.btn-login {
  width: 100%;
  padding: 12px 16px;
  border-radius: 999px;
  border: 1px solid #15803d;
  background: #16a34a;
  font-weight: 800;
  font-size: 1rem;
  cursor: pointer;
  color: #f8fafc;
  margin-top: 12px;

  box-shadow: 0 12px 30px rgba(22, 163, 74, 0.28);
  transition: .12s ease;
}

.btn-login:hover {
  background: #15803d;
  box-shadow: 0 14px 36px rgba(22, 163, 74, .35);
}

.btn-login:active {
  transform: translateY(1px);
}

.btn-login:disabled {
  opacity: .6;
  cursor: not-allowed;
}

/* Alerta */
.alert {
  display: none;
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #991b1b;
  font-size: .9rem;
  font-weight: 600;
}

.alert.is-shown {
  display: block;
}

/* Rodapé do card */
.login-foot {
  margin-top: 14px;
  text-align: center;
  font-size: .93rem;
  color: #334155;
}

.login-foot a {
  color: #2563eb;
  font-weight: 700;
  text-decoration: none;
}

.login-foot a:hover {
  text-decoration: underline;
}

/* Acessibilidade */
.sr-only {
  position: absolute;
  width: 1px; height: 1px;
  padding: 0; margin: -1px;
  overflow: hidden;
  clip: rect(0,0,0,0);
  white-space: nowrap;
  border: 0;
}
</style>

<section class="auth-shell">
  <div class="auth-center">
    <main class="login-card" aria-label="Formulário de login">
      <h1 class="login-title">Login</h1>

      <form id="login-form" action="#" method="post" novalidate>

        <label class="sr-only" for="email">E-mail</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M4 4h16v16H4z"/>
            <path d="m22 6-10 7L2 6"/>
          </svg>
          <input id="email" name="email" type="email"
                 placeholder="E-mail" autocomplete="username" required>
        </div>

        <label class="sr-only" for="password">Senha</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="4" y="11" width="16" height="9" rx="2"/>
            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
          </svg>
          <input id="password" name="password" type="password"
                 placeholder="Senha" autocomplete="current-password" required>
        </div>

        <div class="login-help">
          <label style="display:flex;align-items:center;gap:8px">
            <input type="checkbox" id="remember" name="remember"> Lembrar de mim
          </label>
          <a href="/?r=auth/forgot">Esqueci minha senha</a>
        </div>

        <button id="login-btn" class="btn-login" type="submit">Entrar</button>

        <div id="login-alert" class="alert" role="status" aria-live="polite"></div>

        <p class="login-foot">
          Não tem conta?
          <a href="/?r=auth/register">Criar conta</a>
        </p>
      </form>
    </main>
  </div>
</section>
<div style="width: 100%; color: #2563eb;">
<?php require __DIR__ . '/../_partials/_footer.php'; ?>
</div>

<script>
(function(){
  const form = document.getElementById('login-form');
  const alertBox = document.getElementById('login-alert');
  const btn = document.getElementById('login-btn');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    btn.disabled = true;
    alertBox.classList.remove('is-shown');
    alertBox.textContent = '';

    const body = new URLSearchParams({
      email: form.email.value.trim(),
      password: form.password.value,
      remember: form.remember.checked ? '1' : '0'
    });

    try {
      const r = await fetch('/?r=api/auth/login', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body
      });

      const ct  = (r.headers.get('content-type') || '').toLowerCase();
      const raw = await r.text();
      const j   = ct.includes('application/json') ? JSON.parse(raw) : { error: raw };

      if (!r.ok) throw new Error(j.error || 'Falha no login.');

      const role = (j.user && j.user.role) || 'member';
      const redirect = {
        admin:     '/?r=admin/dashboard',
        partner:   '/?r=partner/dashboard',
        affiliate: '/?r=affiliate/dashboard',
        member:    '/?r=member/dashboard'
      }[role] || '/?r=member/dashboard';

      location.href = redirect;
    } catch (err) {
      alertBox.textContent = 'Erro: ' + (err.message || 'credenciais inválidas');
      alertBox.classList.add('is-shown');
    } finally {
      btn.disabled = false;
    }
  });
})();
</script>
