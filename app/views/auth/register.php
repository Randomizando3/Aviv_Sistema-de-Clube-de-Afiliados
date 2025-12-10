<?php
// app/views/auth/register.php

// Prefill do código do afiliado: prioriza ?ref= e, se não tiver, usa o cookie HttpOnly
$qsRef = isset($_GET['ref']) ? preg_replace('~[^a-zA-Z0-9_-]~','', (string)$_GET['ref']) : '';
$ckRef = isset($_COOKIE['aviv_ref']) ? preg_replace('~[^a-zA-Z0-9_-]~','', (string)$_COOKIE['aviv_ref']) : '';
$prefillRef = $qsRef ?: $ckRef;

// Variáveis comuns aos parciais
$PAGE_TITLE = 'Criar conta • Aviv+';
$PAGE_CLASS = 'site-page'; // usa o fundo/tema público
?>

<?php require __DIR__ . '/../_partials/_head.php'; ?>
<?php require __DIR__ . '/../_partials/_header.php'; ?>

<style>
/* Mesmo fundo clean do login */
body.site-page {
  background: radial-gradient(circle at top, #e8f2ff 0%, #f5f7fb 45%, #f5f7fb 100%);
}

/* Área principal */
.auth-shell{
  width:100%;
}

.auth-center{
  width: min(92vw, 1120px);
  margin-inline:auto;
  min-height: calc(100dvh - var(--topnav-h, 64px) - 100px);
  display:grid;
  place-items:center;
  padding:32px 0 72px;
}

/* Card clean, igual ao login (pode ser um pouquinho mais largo se quiser) */
.login-card{
  width: min(520px, 100%);
  background:#ffffff;
  border-radius:22px;
  border:1px solid #e2e8f0;
  box-shadow:
    0 28px 70px rgba(15, 23, 42, .12),
    0 0 0 1px rgba(148, 163, 184, .08);
  padding:28px;
}

/* Título */
.login-title{
  margin:0 0 18px 0;
  font-size:1.6rem;
  font-weight:800;
  color:#0f172a;
  font-family:"Poppins", sans-serif;
}

/* Inputs (mesmo padrão do login) */
.input-wrap{
  display:flex;
  align-items:center;
  gap:10px;
  border:1px solid #e5e7eb;
  border-radius:12px;
  padding:10px 12px;
  background:#fff;
  margin:10px 0;
  box-shadow:0 10px 25px rgba(15, 23, 42, 0.06);
}
.input-wrap svg{
  color:#64748b;
  flex-shrink:0;
}
.input-wrap input{
  width:100%;
  border:none;
  outline:none;
  background:transparent;
  font-size:.95rem;
}
.input-wrap input::placeholder{
  color:#94a3b8;
}

/* Linha de ajuda / termos */
.login-help{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin:8px 0 4px;
  font-size:.9rem;
  color:#475569;
}
.login-help a{
  color:#2563eb;
  font-weight:600;
  text-decoration:none;
}
.login-help a:hover{
  text-decoration:underline;
}

/* Fieldset tipo de conta adaptado para card branco */
.login-card fieldset{
  border:1px solid #e2e8f0;
  border-radius:12px;
  padding:10px 12px;
  margin:8px 0;
  background:#f8fafc;
  color:#0f172a;
}
.login-card fieldset legend{
  font-size:13px;
  font-weight:600;
  color:#475569;
  padding:0 4px;
}
.login-card fieldset label{
  display:block;
  margin:4px 0;
  font-size:.9rem;
}

/* Botão igual ao login */
.btn-login{
  width:100%;
  padding:12px 16px;
  border-radius:999px;
  border:1px solid #15803d;
  background:#16a34a;
  font-weight:800;
  font-size:1rem;
  cursor:pointer;
  color:#f8fafc;
  margin-top:12px;
  box-shadow:0 12px 30px rgba(22, 163, 74, 0.28);
  transition:.12s ease;
}
.btn-login:hover{
  background:#15803d;
  box-shadow:0 14px 36px rgba(22, 163, 74, .35);
}
.btn-login:active{
  transform:translateY(1px);
}
.btn-login:disabled{
  opacity:.6;
  cursor:not-allowed;
}

/* Alert shared */
.alert{
  display:none;
  margin-top:12px;
  padding:10px 12px;
  border-radius:12px;
  border:1px solid #fecaca;
  background:#fef2f2;
  color:#991b1b;
  font-size:.9rem;
  font-weight:600;
}
.alert.is-shown{
  display:block;
}

/* Rodapé do card */
.login-foot{
  margin-top:14px;
  text-align:center;
  font-size:.93rem;
  color:#334155;
}
.login-foot a{
  color:#2563eb;
  font-weight:700;
  text-decoration:none;
}
.login-foot a:hover{
  text-decoration:underline;
}

/* Acessibilidade */
.sr-only{
  position:absolute;
  width:1px;height:1px;
  padding:0;margin:-1px;
  overflow:hidden;
  clip:rect(0,0,0,0);
  white-space:nowrap;
  border:0;
}
</style>

<section class="auth-shell">
  <div class="auth-center">
    <main class="login-card" aria-label="Formulário de cadastro">
      <h1 class="login-title">Criar conta</h1>

      <form id="register-form" action="#" method="post" novalidate>
        <!-- Nome -->
        <label class="sr-only" for="name">Nome completo</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/>
          </svg>
          <input id="name" name="name" type="text" placeholder="Nome completo" required>
        </div>

        <!-- CPF/CNPJ -->
        <label class="sr-only" for="cpf_cnpj">CPF ou CNPJ</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="3" y="3" width="18" height="14" rx="2"/><path d="M7 21h10"/>
          </svg>
          <input id="cpf_cnpj" name="cpf_cnpj" type="text" placeholder="CPF ou CNPJ" required>
        </div>

        <!-- Celular -->
        <label class="sr-only" for="phone">Celular</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/>
          </svg>
          <input id="phone" name="phone" type="tel" placeholder="Celular (com DDD)" required>
        </div>

        <!-- Código do Afiliado (ref) -->
        <label class="sr-only" for="ref">Código do afiliado</label>
        <div class="input-wrap" style="display:flex;align-items:center;gap:10px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M3 7h18M3 12h18M3 17h18"/>
          </svg>
          <input id="ref" name="ref" type="text" placeholder="Código do afiliado"
                 value="<?= htmlspecialchars($prefillRef) ?>" readonly>
          <button type="button" id="ref-edit" class="btn btn--ghost"
                  title="Editar código" aria-label="Editar código"
                  style="padding:6px 10px;font-size:.8rem;">
            ✏️
          </button>
        </div>

        <!-- Email -->
        <label class="sr-only" for="email">E-mail</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/>
          </svg>
          <input id="email" name="email" type="email" placeholder="E-mail" required>
        </div>

        <!-- Senha -->
        <label class="sr-only" for="password">Senha</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <rect x="4" y="11" width="16" height="9" rx="2"/>
            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
          </svg>
          <input id="password" name="password" type="password"
                 placeholder="Senha (mín. 6 caracteres)" minlength="6" required>
        </div>

        <!-- Confirmar senha -->
        <label class="sr-only" for="password2">Confirmar senha</label>
        <div class="input-wrap">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/>
          </svg>
          <input id="password2" name="password2" type="password"
                 placeholder="Confirmar senha" minlength="6" required>
        </div>

        <!-- Tipo de conta -->
        <fieldset>
          <legend>Tipo de conta</legend>
          <label>
            <input type="radio" name="role" value="member" checked> Membro
          </label>
          <label>
            <input type="radio" name="role" value="partner"> Parceiro
          </label>
          <label>
            <input type="radio" name="role" value="affiliate"> Afiliado
          </label>
        </fieldset>

        <!-- Aceite de termos -->
        <div class="login-help">
          <label style="display:flex;align-items:center;gap:8px" for="terms">
            <input type="checkbox" id="terms" required>
            <span>Aceito os <a href="/?r=site/termos" target="_blank" rel="noopener"
               onclick="event.stopPropagation()">Termos de Uso</a></span>
          </label>
        </div>

        <button id="register-btn" class="btn-login" type="submit">Criar conta</button>
        <div id="register-alert" class="alert" role="status" aria-live="polite"></div>

        <p class="login-foot">
          Já tem conta?
          <a href="/?r=auth/login">Entrar</a>
        </p>
      </form>
    </main>
  </div>
</section>

<?php require __DIR__ . '/../_partials/_footer.php'; ?>

<script>
(function(){
  const f        = document.getElementById('register-form');
  const a        = document.getElementById('register-alert');
  const b        = document.getElementById('register-btn');
  const refInput = document.getElementById('ref');
  const refEdit  = document.getElementById('ref-edit');

  // Permite editar o código (por padrão vem readonly)
  refEdit.addEventListener('click', () => {
    refInput.readOnly = !refInput.readOnly;
    if (!refInput.readOnly){ refInput.focus(); }
  });

  f.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!document.getElementById('terms').checked) {
      a.textContent = 'Você precisa aceitar os termos.';
      a.classList.add('is-shown');
      return;
    }
    if (f.password.value !== f.password2.value) {
      a.textContent = 'As senhas não conferem.';
      a.classList.add('is-shown');
      return;
    }

    const oldText = b.textContent;
    b.textContent = 'Criando sua conta…';
    b.disabled = true;
    a.classList.remove('is-shown');
    a.textContent = '';

    try {
      const role = (new FormData(f).get('role')) || 'member';

      const body = new URLSearchParams({
        name:      f.name.value.trim(),
        email:     f.email.value.trim(),
        password:  f.password.value,
        role,
        cpf_cnpj:  f.cpf_cnpj.value.trim(),
        phone:     f.phone.value.trim(),
        ref:       (f.ref.value || '').replace(/[^a-zA-Z0-9_-]/g,'')
      });

      const r = await fetch('/?r=api/auth/register', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body
      });

      let j; try { j = await r.json(); } catch(_){}
      if (!r.ok) throw new Error(j?.error || 'Não foi possível registrar.');

      const redirect = {
        member:    '/?r=member/dashboard',
        partner:   '/?r=partner/dashboard',
        affiliate: '/?r=affiliate/dashboard'
      }[role] || '/?r=member/dashboard';

      location.href = redirect;
    } catch (err) {
      a.textContent = 'Erro: ' + (err.message || 'falha inesperada');
      a.classList.add('is-shown');
    } finally {
      b.textContent = oldText;
      b.disabled = false;
    }
  });
})();
</script>
