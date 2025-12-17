<?php
/**
 * View: Benefício • Saúde Completa (Set/2024) — Sabemi
 * Arquivo: /public/docs/saude-completa.php
 *
 * Observação:
 * - Salve este arquivo em UTF-8 (sem BOM).
 * - Não usamos header() aqui para evitar warning caso o editor adicione BOM/bytes antes do PHP.
 */

$pdfFileName = 'Saúde Completa Set_24.pdf';
$pdfHref = '/docs/' . rawurlencode($pdfFileName);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Saúde Completa • Aviv+</title>
  <meta name="robots" content="noindex,nofollow" />
  <style>
    :root{
      --txt:#111322;
      --muted:#6b7280;
      --bd:#d0d7e2;
      --bg:#ffffff;
      --bg2:#f8fafc;
      --brand:#2563eb;
      --brand2:#1d4ed8;
      --ok:#16a34a;
      --warn:#f59e0b;
      --danger:#ef4444;
      --shadow:0 18px 40px rgba(15,23,42,.06);
      --shadow2:0 12px 30px rgba(15,23,42,.05);
      --radius:18px;
      --radius2:16px;
      --container: 1180px;
    }

    *{ box-sizing:border-box; }
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Apple Color Emoji","Segoe UI Emoji";
      color:var(--txt);
      background: linear-gradient(180deg, #f7fafc 0%, #ffffff 55%, #f7fafc 100%);
    }
    a{ color:inherit; }

    .container{
      width:min(92vw, var(--container));
      margin-inline:auto;
      padding:18px 0 40px;
    }

    .glass-card{
      background:var(--bg);
      border:1px solid rgba(15,23,42,.06);
      padding:18px;
      border-radius:var(--radius);
      color:var(--txt);
      box-shadow:var(--shadow);
    }
    .glass-sub{
      background:var(--bg2);
      border:1px solid rgba(15,23,42,.08);
      padding:14px;
      border-radius:var(--radius2);
    }

    .muted{ color:var(--muted); font-size:.92rem; }
    .sect-title{ margin:0; font-weight:900; letter-spacing:-.02em; }
    .sect-sub{ margin:0 0 10px; font-weight:900; letter-spacing:-.01em; }
    .kicker{
      font-size:.82rem;
      color:#4b5563;
      font-weight:800;
      display:flex;
      align-items:center;
      gap:8px;
      flex-wrap:wrap;
    }

    .doc-head{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:14px;
    }
    .doc-cta{
      min-width:280px;
      text-align:right;
    }
    @media (max-width:900px){
      .doc-head{ flex-direction:column; }
      .doc-cta{ text-align:left; min-width:0; width:100%; }
    }

    .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      border-radius:12px;
      padding:10px 14px;
      border:1px solid var(--bd);
      background:#fff;
      color:var(--txt);
      font-weight:900;
      text-decoration:none;
      cursor:pointer;
      transition: transform .06s ease, box-shadow .15s ease, background .15s ease;
      user-select:none;
      white-space:nowrap;
    }
    .btn:hover{ box-shadow:0 8px 20px rgba(15,23,42,.10); }
    .btn:active{ transform:translateY(1px); }
    .btn-primary{
      background:var(--brand);
      border-color:var(--brand);
      color:#fff;
    }
    .btn-primary:hover{ background:var(--brand2); border-color:var(--brand2); }
    .btn-ghost{ background:transparent; }
    .btn-pdf{ border-color: rgba(15,23,42,.12); background:#fff; }

    .btn-row{
      display:flex;
      gap:8px;
      flex-wrap:wrap;
      justify-content:flex-end;
    }
    @media (max-width:900px){
      .btn-row{ justify-content:flex-start; }
    }

    .grid{
      display:grid;
      gap:12px;
      grid-template-columns: 1fr 320px;
      align-items:start;
      margin-top:12px;
    }
    @media (max-width:980px){
      .grid{ grid-template-columns:1fr; }
      .toc-card{ order:-1; }
    }

    .toc{
      list-style:none;
      padding:0;
      margin:10px 0 0;
      display:grid;
      gap:8px;
    }
    .toc a{
      display:block;
      padding:10px 12px;
      border-radius:14px;
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      text-decoration:none;
      font-weight:900;
      color:var(--txt);
      transition: background .15s ease, border-color .15s ease;
    }
    .toc a:hover{ background:#eef2ff; border-color:#a5b4fc; }

    .mini{
      margin-top:12px;
      border:1px dashed rgba(15,23,42,.18);
      border-radius:16px;
      padding:12px;
      background:#fff;
    }
    .mini-title{ font-weight:900; margin-bottom:4px; }
    .mini-text{ color:#111827; line-height:1.5; font-size:.95rem; }

    .badges{
      display:flex;
      flex-wrap:wrap;
      gap:8px;
      margin-top:10px;
    }
    .badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:8px 10px;
      border-radius:999px;
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      font-weight:900;
      font-size:.84rem;
      color:#111827;
    }
    .badge.ok{ border-color: rgba(22,163,74,.25); background: rgba(22,163,74,.08); }
    .badge.warn{ border-color: rgba(245,158,11,.35); background: rgba(245,158,11,.12); }
    .badge.info{ border-color: rgba(37,99,235,.25); background: rgba(37,99,235,.08); }

    .p{ margin:10px 0; line-height:1.6; color:#111827; }

    .info-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
      margin-top:12px;
    }
    @media (max-width:720px){ .info-grid{ grid-template-columns:1fr; } }
    .info{
      border:1px solid rgba(15,23,42,.10);
      border-radius:16px;
      padding:12px;
      background:#fff;
      box-shadow:var(--shadow2);
    }
    .info h3{ margin:0 0 6px; font-size:1.02rem; font-weight:950; letter-spacing:-.01em; }
    .info p{ margin:0; color:#111827; line-height:1.55; font-size:.95rem; }

    .callout{
      margin-top:12px;
      border:1px solid rgba(37,99,235,.25);
      background:rgba(37,99,235,.06);
      border-radius:16px;
      padding:12px;
    }
    .callout-title{ font-weight:950; margin-bottom:4px; }
    .callout-text{ color:#111827; line-height:1.55; }

    .howto-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
      margin-top:10px;
    }
    @media (max-width:820px){ .howto-grid{ grid-template-columns:1fr; } }
    .howto{
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      border-radius:16px;
      padding:12px;
      box-shadow:var(--shadow2);
    }
    .howto h3{ margin:0 0 8px; font-weight:950; }
    .howto ol{ margin:0 0 10px 18px; padding:0; color:#111827; line-height:1.6; }
    .howto li{ margin:6px 0; }

    .list{ margin:8px 0 0 18px; line-height:1.6; color:#111827; }
    .list li{ margin:6px 0; }

    .chips{ display:flex; flex-wrap:wrap; gap:8px; margin-top:8px; }
    .chip{
      display:inline-flex;
      align-items:center;
      padding:8px 10px;
      border-radius:999px;
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      font-weight:900;
      font-size:.86rem;
      color:#111827;
    }

    .warn{
      margin-top:10px;
      border:1px solid rgba(245,158,11,.35);
      background:rgba(245,158,11,.10);
      border-radius:14px;
      padding:10px;
      color:#111827;
      line-height:1.55;
    }
    .danger{
      margin-top:10px;
      border:1px solid rgba(239,68,68,.30);
      background:rgba(239,68,68,.08);
      border-radius:14px;
      padding:10px;
      color:#111827;
      line-height:1.55;
    }

    .doc-foot{
      margin-top:14px;
      padding-top:10px;
      border-top:1px solid rgba(15,23,42,.08);
      display:flex;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }

    .anchor{ position:relative; top:-10px; }

    .pdf-note{
      margin-top:10px;
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:10px;
      flex-wrap:wrap;
    }
    .pdf-note .text{
      color:#111827;
      line-height:1.55;
      flex: 1 1 420px;
      min-width: 260px;
    }
  </style>
</head>
<body>

<section class="container">

  <div class="glass-card">
    <div class="doc-head">
      <div>
        <div class="kicker">Sabemi • Descritivo do serviço • Set/2024</div>
        <h1 class="sect-title">Saúde Completa</h1>

        <div class="badges">
          <div class="badge info">Consultas com participação fixa</div>
          <div class="badge ok">Exames com cashback (ver PDF)</div>
          <div class="badge warn">Medicamentos com desconto</div>
          <div class="badge info">Atendimento: seg–sex (08h–18h)</div>
        </div>

        <p class="muted" style="margin:10px 0 0">
          Canal principal de acionamento e orientações: WhatsApp do concierge/central.
        </p>
      </div>

      <div class="doc-cta">
        <div class="btn-row">
          <a class="btn btn-primary" href="https://wa.me/555120420536" target="_blank" rel="noopener">
            Chamar no WhatsApp
          </a>
          <a class="btn btn-pdf" href="<?= htmlspecialchars($pdfHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
            Abrir PDF completo
          </a>
        </div>

        <div class="muted" style="font-size:.82rem;margin-top:8px">
          WhatsApp: (51) 2042-0536 • Seg–Sex (08h–18h), exceto feriados.
        </div>

        <div class="btn-row" style="margin-top:10px">
          <a class="btn btn-ghost" href="#consultas">Consultas</a>
          <a class="btn btn-ghost" href="#exames">Exames</a>
        </div>
      </div>
    </div>
  </div>

  <div class="grid">
    <main class="glass-card">

      <span id="visao" class="anchor"></span>
      <h2 class="sect-sub">Visão geral</h2>
      <p class="p">
        O benefício “Saúde Completa” organiza o acesso a consultas e orientações por meio de concierge/central,
        com participação fixa por consulta. Também inclui regras de cashback/reembolso para exames (detalhadas no PDF),
        acesso a clube de vantagens e desconto em medicamentos, conforme condições do programa.
      </p>

      <div class="info-grid">
        <div class="info">
          <h3>Como acionar</h3>
          <p>Acionamento via WhatsApp: (51) 2042-0536, seg–sex (08h–18h), exceto feriados.</p>
        </div>
        <div class="info">
          <h3>Participação fixa</h3>
          <p>Consultas com participação fixa de <strong>R$ 50,00</strong> por especialidade (quando aplicável).</p>
        </div>
        <div class="info">
          <h3>Dependentes</h3>
          <p>Prevê atendimento também para cônjuge e filhos (até 21 anos), conforme regras do benefício.</p>
        </div>
        <div class="info">
          <h3>Exames</h3>
          <p>Cashback/reembolso conforme regras e tabela do PDF do benefício.</p>
        </div>
      </div>

      <div class="callout">
        <div class="callout-title">Prazos no fluxo</div>
        <div class="callout-text">
          O retorno com disponibilidade/indicações pode ocorrer em dias úteis (conforme demanda e região).
          O pagamento costuma ser orientado pela central (ex.: via Pix) antes do agendamento.
        </div>
      </div>

      <span id="consultas" class="anchor"></span>
      <h2 class="sect-sub" style="margin-top:14px">Consultas • Como solicitar e agendar</h2>

      <div class="howto-grid">
        <section class="howto">
          <h3>Passo a passo (consulta)</h3>
          <ol>
            <li>Chame a central no WhatsApp <strong>(51) 2042-0536</strong>.</li>
            <li>No menu, siga: <strong>Consultas/Exames → Consultas → Solicitar consulta</strong>.</li>
            <li>Informe a especialidade desejada e seus dados básicos.</li>
            <li>Aguarde o retorno com unidade/profissional e instruções de pagamento.</li>
            <li>Efetue o pagamento (geralmente via Pix) dentro do prazo informado para confirmar o agendamento.</li>
            <li>Receba a confirmação com data/horário e orientações para comparecimento.</li>
          </ol>
        </section>

        <section class="howto">
          <h3>Regras importantes</h3>
          <ul class="list">
            <li>O agendamento pode depender da confirmação de pagamento dentro do prazo indicado.</li>
            <li>Em algumas situações, a central pode pedir confirmação rápida para manter a reserva.</li>
            <li>Quando necessário, pode ser emitida guia/autorização conforme orientação da central.</li>
          </ul>

          <div class="warn">
            <strong>Atenção:</strong> se a central informar um prazo para pagamento/confirmar, o não cumprimento pode cancelar a reserva automaticamente.
          </div>
        </section>
      </div>

      <span id="reembolso-consulta" class="anchor"></span>
      <h2 class="sect-sub" style="margin-top:14px">Consulta sem rede/infraestrutura • Reembolso</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Para localidades sem infraestrutura/rede credenciada, o benefício pode oferecer opção de reembolso
          <strong>mediante autorização prévia</strong>, dentro do limite informado pela central.
        </p>

        <ul class="list">
          <li>Limite de reembolso citado no material: <strong>até R$ 200,00</strong> (mediante autorização prévia).</li>
          <li>Participação fixa por consulta: <strong>R$ 50,00</strong>.</li>
          <li>A central pode orientar a composição do valor no momento do acionamento.</li>
          <li>Prazo para solicitar reembolso (quando houver): normalmente <strong>até 7 dias</strong> após a realização.</li>
        </ul>

        <div class="danger">
          <strong>Importante:</strong> o reembolso (quando aplicável) depende de autorização prévia e do envio correto de documentação.
        </div>
      </div>

      <span id="familia" class="anchor"></span>
      <h2 class="sect-sub" style="margin-top:14px">Dependentes (familiar)</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          O material indica cobertura de utilização também para <strong>cônjuge</strong> e <strong>filhos até 21 anos</strong>.
        </p>
        <ul class="list">
          <li>O cadastro/validação de dependentes pode ser solicitado via WhatsApp com a central.</li>
          <li>Após cadastrados, os dependentes podem acionar a central para solicitar consultas/exames conforme as mesmas regras.</li>
        </ul>
      </div>

      <span id="especialidades" class="anchor"></span>
      <h2 class="sect-sub" style="margin-top:14px">Especialidades (conforme material)</h2>
      <div class="chips">
        <span class="chip">Angiologia</span><span class="chip">Cardiologia</span><span class="chip">Clínico Geral</span>
        <span class="chip">Dermatologia</span><span class="chip">Endocrinologia</span><span class="chip">Gastroenterologia</span>
        <span class="chip">Ginecologia</span><span class="chip">Mastologia</span><span class="chip">Neurologia</span>
        <span class="chip">Oftalmologia</span><span class="chip">Ortopedia</span><span class="chip">Otorrinolaringologia</span>
        <span class="chip">Pediatria</span><span class="chip">Pneumologia</span><span class="chip">Psiquiatria</span>
        <span class="chip">Reumatologia</span><span class="chip">Urologia</span><span class="chip">Nefrologia</span>
        <span class="chip">Nutrologia</span>
      </div>

      <span id="exames" class="anchor"></span>
      <h2 class="sect-sub" style="margin-top:14px">Exames • Cashback/Reembolso</h2>
      <p class="p">
        O fluxo geral é: <strong>realizar o exame</strong> e depois solicitar o cashback via WhatsApp,
        enviando a documentação exigida dentro do prazo. A tabela e condições completas estão no PDF do benefício.
      </p>

      <div class="glass-sub">
        <div class="pdf-note">
          <div class="text">
            <strong>Tabela e regras completas:</strong> abra o PDF “Saúde Completa Set_24”.
          </div>
          <div>
            <a class="btn btn-primary" href="<?= htmlspecialchars($pdfHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
              Abrir PDF completo
            </a>
          </div>
        </div>

        <div class="warn">
          <strong>Dica:</strong> envie sempre NF + comprovante + pedido médico (quando houver), conforme orientação da central.
        </div>
      </div>

      <div class="doc-foot">
        <span class="muted">Referência: Saúde Completa • Set/2024 • Sabemi</span>
        <span class="muted">PDF: <?= htmlspecialchars($pdfFileName, ENT_QUOTES, 'UTF-8') ?></span>
      </div>

    </main>

    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px; font-weight:950;">Atalhos</h3>
      <nav aria-label="Atalhos do benefício">
        <ul class="toc">
          <li><a href="https://wa.me/555120420536" target="_blank" rel="noopener">Chamar WhatsApp</a></li>
          <li><a href="<?= htmlspecialchars($pdfHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Abrir PDF completo</a></li>
          <li><a href="#consultas" onclick="document.querySelector('#consultas')?.scrollIntoView({behavior:'smooth'});return false;">Consultas</a></li>
          <li><a href="#reembolso-consulta" onclick="document.querySelector('#reembolso-consulta')?.scrollIntoView({behavior:'smooth'});return false;">Reembolso (consulta)</a></li>
          <li><a href="#especialidades" onclick="document.querySelector('#especialidades')?.scrollIntoView({behavior:'smooth'});return false;">Especialidades</a></li>
          <li><a href="#exames" onclick="document.querySelector('#exames')?.scrollIntoView({behavior:'smooth'});return false;">Exames (ver PDF)</a></li>
        </ul>
      </nav>

      <div class="mini">
        <div class="mini-title">Dica rápida</div>
        <div class="mini-text">
          Para agilizar, já deixe separado: CPF, especialidade desejada, cidade/bairro,
          e (para cashback) Nota Fiscal + comprovante.
        </div>
      </div>
    </aside>
  </div>

</section>

</body>
</html>
