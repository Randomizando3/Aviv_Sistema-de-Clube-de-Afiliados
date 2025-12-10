<section class="container admin">
  <div class="admin-grid">
    <?php require __DIR__ . '/_sidebar.php'; ?>

    <section class="admin-main">
      <header class="sect-head">
        <h1 class="sect-title">Configurações</h1>
      </header>

      <div class="glass-card">
        <h2 class="sect-sub">ASAAS</h2>
        <form class="form-grid" id="cfg-form" onsubmit="return false">
          <div class="input-wrap">
            <input id="cfg_asaas_key" type="password" placeholder="API Key (privada)" value="">
          </div>
          <div class="grid-2">
            <div class="input-wrap"><input id="cfg_asaas_webhook" type="url" placeholder="Webhook URL (ex.: https://seu.com/webhooks/asaas)"></div>
            <div class="input-wrap"><input id="cfg_asaas_secret" type="password" placeholder="Webhook secret"></div>
          </div>
          <div class="form-actions">
            <button class="btn btn-sm" id="cfg_save" type="submit">Salvar (demo)</button>
          </div>
        </form>
        <p class="muted">No backend, manter chaves em variáveis de ambiente (.env) e validar assinatura do webhook.</p>
      </div>

      <div class="glass-card mtop">
        <h2 class="sect-sub">Marca</h2>
        <form class="form-grid" onsubmit="return false">
          <div class="grid-2">
            <div class="input-wrap"><input id="cfg_site_name" type="text" placeholder="Nome do site" value="Aviv+"></div>
            <div class="input-wrap"><input id="cfg_support_email" type="email" placeholder="E-mail de suporte" value="suporte@aviv.plus"></div>
          </div>
          <div class="form-actions">
            <button class="btn btn-sm" type="button" disabled>Enviar logo (demo)</button>
            <button class="btn btn-sm btn--ghost" type="button" disabled>Salvar</button>
          </div>
        </form>
      </div>

      <div class="glass-card mtop">
        <h2 class="sect-sub">E-mails</h2>
        <form class="form-grid" onsubmit="return false">
          <div class="grid-2">
            <div class="input-wrap"><input type="text" placeholder="Remetente (ex.: Aviv+ &lt;no-reply@aviv.plus&gt;)"></div>
            <div class="input-wrap"><input type="text" placeholder="Provedor (ex.: SMTP, SES, etc.)"></div>
          </div>
          <div class="form-actions">
            <button class="btn btn-sm" type="button" disabled>Salvar (demo)</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</section>

<script>
  const keyC='admin_config';
  function getC(){ try{return JSON.parse(localStorage.getItem(keyC)||'{}')}catch(e){return{}} }
  function setC(v){ localStorage.setItem(keyC, JSON.stringify(v)); }

  // carregar
  (()=>{
    const c=getC();
    if(c.asaas_key) cfg_asaas_key.value=c.asaas_key;
    if(c.asaas_webhook) cfg_asaas_webhook.value=c.asaas_webhook;
    if(c.asaas_secret) cfg_asaas_secret.value=c.asaas_secret;
  })();

  document.getElementById('cfg_save').addEventListener('click', ()=>{
    const c=getC();
    c.asaas_key=cfg_asaas_key.value.trim();
    c.asaas_webhook=cfg_asaas_webhook.value.trim();
    c.asaas_secret=cfg_asaas_secret.value.trim();
    setC(c);
    alert('Configuração salva (demo).');
  });
</script>
