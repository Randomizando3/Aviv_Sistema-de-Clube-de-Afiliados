<?php
// View: Benefício • Doutor Online — Familiar (Out/2024)
// Arquivo: /public/docs/doutor-online-familiar.php

ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
  header('Content-Type: text/html; charset=UTF-8');
}

/**
 * Fallback de encoding:
 * - Se o arquivo for salvo em ANSI/Windows-1252 e o browser mostrar "�",
 *   este buffer converte o HTML final para UTF-8 antes de enviar.
 */
$__converter = function ($buffer) {
  // Se já for UTF-8 válido, não mexe
  if (@preg_match('//u', $buffer)) return $buffer;

  // Tenta converter de Windows-1252 para UTF-8
  if (function_exists('mb_convert_encoding')) {
    return mb_convert_encoding($buffer, 'UTF-8', 'Windows-1252');
  }
  if (function_exists('iconv')) {
    return iconv('Windows-1252', 'UTF-8//IGNORE', $buffer);
  }
  return $buffer;
};
ob_start($__converter);
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Doutor Online • Familiar • Aviv+</title>
  <meta name="robots" content="noindex,nofollow">

  <style>
    /* ===== Página do benefício (standalone) ===== */
    :root{
      --txt:#111322;
      --muted:#6b7280;
      --bd:#d0d7e2;
      --bg:#ffffff;
      --bg2:#f8fafc;
      --brand:#2563eb;
    }
    .container{ width:min(92vw, 1120px); margin-inline:auto; }
    .benefit-doc{ color:var(--txt); }
    .glass-card{
      background:var(--bg);
      border:1px solid rgba(15,23,42,.06);
      padding:18px;
      border-radius:18px;
      box-shadow:0 18px 40px rgba(15,23,42,.06);
    }
    .glass-sub{
      background:var(--bg2);
      border:1px solid rgba(15,23,42,.08);
      padding:14px;
      border-radius:16px;
    }
    .muted{ color:var(--muted); font-size:.92rem; }
    .sect-title{ margin:0; font-weight:900; letter-spacing:-.02em; }
    .sect-sub{ margin:0 0 8px; font-weight:900; }
    .kicker{ font-size:.82rem; color:#4b5563; font-weight:800; }

    /* header */
    .doc-head{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:14px;
    }
    .doc-cta{ min-width:320px; text-align:right; }
    @media (max-width:900px){
      .doc-head{ flex-direction:column; }
      .doc-cta{ text-align:left; min-width:0; }
    }

    /* buttons */
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
      width:100%;
      box-sizing:border-box;
    }
    .btn:hover{ box-shadow:0 8px 20px rgba(15,23,42,.10); }
    .btn-primary{ background:var(--brand); border-color:var(--brand); color:#fff; }
    .btn-outline{ background:transparent; }

    /* layout grid */
    .terms-grid{
      display:grid;
      gap:12px;
      grid-template-columns: 1fr 320px;
      align-items:start;
    }
    @media (max-width:980px){
      .terms-grid{ grid-template-columns:1fr; }
      .toc-card{ order:-1; }
    }

    /* text */
    .p{ margin:10px 0; line-height:1.6; color:#111827; }
    .ul{ margin:10px 0 0 18px; color:#111827; line-height:1.55; }
    .ul li{ margin:6px 0; }

    /* chips */
    .chips{ display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .chip{
      display:inline-flex; align-items:center; gap:6px;
      padding:6px 10px; border-radius:999px;
      background:#f9fafb; border:1px solid #e5e7eb;
      font-weight:900; font-size:.85rem; color:#374151;
    }

    /* info boxes */
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
    }
    .info h3{ margin:0 0 6px; font-size:1.02rem; font-weight:900; }
    .info p{ margin:0; color:#111827; line-height:1.55; font-size:.95rem; }

    /* callout */
    .callout{
      margin-top:12px;
      border:1px solid rgba(37,99,235,.25);
      background:rgba(37,99,235,.06);
      border-radius:16px;
      padding:12px;
      color:#111827;
      line-height:1.55;
    }
    .callout strong{ font-weight:900; }

    /* warning */
    .warn{
      margin-top:10px;
      border:1px solid rgba(245,158,11,.35);
      background:rgba(245,158,11,.10);
      border-radius:14px;
      padding:10px;
      color:#111827;
      line-height:1.55;
    }

    /* toc */
    .toc{ list-style:none; padding:0; margin:8px 0 0; display:grid; gap:8px; }
    .toc a{
      display:block;
      padding:10px 12px;
      border-radius:14px;
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      color:var(--txt);
      text-decoration:none;
      font-weight:900;
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
    .mini-text{ color:#111827; line-height:1.55; font-size:.95rem; }

    .foot{
      margin-top:14px;
      padding-top:10px;
      border-top:1px solid rgba(15,23,42,.08);
    }

    /* tags (especialidades) */
    .tags{ display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .tag{
      display:inline-flex;
      padding:6px 10px;
      border-radius:999px;
      background:#fff;
      border:1px solid rgba(15,23,42,.10);
      font-weight:900;
      font-size:.85rem;
      color:#374151;
    }
  </style>
</head>

<body>
<section class="container benefit-doc" style="margin-top:18px">

  <div class="glass-card">
    <div class="doc-head">
      <div>
        <div class="kicker">Sabemi • Descritivo do serviço • Out/2024</div>
        <h1 class="sect-title">Doutor Online — Familiar</h1>

        <p class="muted" style="margin:8px 0 0">
          Serviço de telemedicina/teleconsulta para o segurado titular e dependentes, com fluxo de orientação inicial (teletriagem),
          consultas e encaminhamentos, conforme regras do serviço.
        </p>

        <div class="chips" aria-label="Destaques do serviço">
          <span class="chip">Atendimento 24h / 7 dias</span>
          <span class="chip">Carência: 48 horas úteis</span>
          <span class="chip">Sem limite de utilização</span>
        </div>
      </div>

      <div class="doc-cta">
        <a class="btn btn-primary" href="https://beneficiario.redemaisaude.com.br" target="_blank" rel="noopener">
          Acessar portal RedeMais Saúde
        </a>

        <a class="btn btn-outline" style="margin-top:10px" href="tel:08007766013" aria-label="Ligar para 0800 776 6013">
          Ligar na central: 0800 776 6013
        </a>

        <div class="muted" style="font-size:.82rem;margin-top:8px">
          Acesso/acionamento também pelo aplicativo RedeMais Saúde.
        </div>
      </div>
    </div>
  </div>

  <div class="terms-grid" style="margin-top:12px">
    <!-- Coluna principal -->
    <main class="glass-card doc-body">

      <h2 id="visao-geral" class="sect-sub">Visão geral</h2>
      <p class="p">
        O serviço é acionado via plataforma RedeMais Saúde (app/portal) ou pela central 0800.
        Ele permite consultas a distância por videoconferência, com avaliação clínica, orientações e encaminhamentos quando aplicável,
        observando as premissas e limitações do regulamento do serviço.
      </p>

      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          <strong>Quem pode usar:</strong> serviço destinado ao <strong>segurado principal</strong> e até
          <strong>03 (três) dependentes</strong> por ele indicados.
        </p>
        <p class="p" style="margin-bottom:0">
          O acionamento é feito pelo <strong>CPF do titular</strong> junto à plataforma de acionamento do serviço.
        </p>
      </div>

      <h2 id="como-acionar" class="sect-sub" style="margin-top:14px">Como acionar</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Para utilizar o benefício, acione pela RedeMais Saúde informando o CPF:
        </p>
        <ul class="ul">
          <li>Pelo aplicativo RedeMais Saúde;</li>
          <li>Pelo portal: <strong>beneficiario.redemaisaude.com.br</strong>;</li>
          <li>Pela central de atendimento: <strong>0800 776 6013</strong>.</li>
        </ul>

        <div class="callout">
          <strong>Primeiro acesso:</strong> o segurado utiliza o <strong>CPF como login e senha</strong> no primeiro acesso,
          sendo necessária a <strong>alteração de senha</strong> nesse momento. A partir do segundo acesso, utiliza-se a nova senha definida.
        </div>
      </div>

      <h2 id="definicoes" class="sect-sub" style="margin-top:14px">Definições principais</h2>
      <div class="info-grid">
        <div class="info">
          <h3>Canais de comunicação</h3>
          <p>Aplicativo RedeMais Saúde, portal do beneficiário e central telefônica 0800.</p>
        </div>
        <div class="info">
          <h3>Central de assistência</h3>
          <p>Central telefônica do prestador para acionamento e suporte ao segurado/beneficiário.</p>
        </div>
        <div class="info">
          <h3>Concierge / Central de atendimento</h3>
          <p>Equipe multidisciplinar orientada a entender a necessidade do segurado e sanar dúvidas.</p>
        </div>
        <div class="info">
          <h3>Telemedicina</h3>
          <p>Consulta a distância por videoconferência para avaliação clínica, orientações, exames e prescrição quando aplicável.</p>
        </div>
        <div class="info">
          <h3>Atendimento</h3>
          <p>Disponível 24 horas por dia, 7 dias por semana (acionamento do serviço).</p>
        </div>
        <div class="info">
          <h3>Carência / Limite</h3>
          <p>Carência de 48 horas úteis após ativação. O serviço não possui limite de utilização.</p>
        </div>
      </div>

      <h2 id="ativacao" class="sect-sub" style="margin-top:14px">Ativação e utilização</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          A ativação ocorre após a ativação do seguro junto à seguradora. Ao acessar a plataforma, o segurado fica apto a utilizar
          os fluxos de atendimento disponíveis (teletriagem e teleconsulta), conforme critérios clínicos e disponibilidade.
        </p>

        <div class="callout">
          <strong>Atendimento:</strong> 24h / 7 dias.<br>
          <strong>Carência:</strong> 48 horas úteis após a ativação do seguro.<br>
          <strong>Limite do serviço:</strong> não possui limite de utilização.
        </div>
      </div>

      <h2 id="teletriagem" class="sect-sub" style="margin-top:14px">Orientação inicial (teletriagem)</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          O primeiro atendimento é feito por teletriagem (normalmente por equipe de enfermagem) e segue regras de privacidade,
          segurança em saúde e diretrizes éticas aplicáveis.
        </p>
        <p class="p" style="margin-bottom:6px"><strong>Após a teletriagem, o segurado pode:</strong></p>
        <ul class="ul">
          <li>Receber orientações de autocuidado;</li>
          <li>Ser direcionado a atendimento ambulatorial ou emergência em hospital;</li>
          <li>Ser convidado a participar de consulta eletiva ou pronto atendimento, conforme preferência;</li>
          <li>Ser transferido para teleconsulta imediata, se houver agenda disponível;</li>
          <li>Ter a teleconsulta agendada para um momento mais conveniente.</li>
        </ul>
      </div>

      <h2 id="teleconsulta" class="sect-sub" style="margin-top:14px">Teleconsulta</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          A teleconsulta é realizada por vídeo com médicos clínicos gerais ou especialistas. Após a orientação inicial,
          pode ser enviado ao segurado (por exemplo via SMS) um link para acesso à teleconsulta.
        </p>

        <p class="p" style="margin-bottom:6px"><strong>Formas de condução do atendimento:</strong></p>
        <ul class="ul">
          <li>Avaliação baseada na gravidade dos sintomas/queixas, inclusive múltiplos sintomas quando aplicável;</li>
          <li>Encaminhamento a especialistas conforme sinais e sintomas avaliados;</li>
          <li>Teleconsulta no momento (após teletriagem) ou agendada;</li>
          <li>Solicitação de exames complementares e retorno para interpretação de resultados;</li>
          <li>Prescrição de tratamento quando relacionada ao motivo do atendimento;</li>
          <li>Indicação de deslocamento para unidades de emergência/hospitais quando necessário.</li>
        </ul>

        <div class="warn">
          <strong>Importante:</strong> teleconsulta não substitui atendimento presencial em situações de urgência/emergência.
          Em caso de sinais de emergência, procure atendimento presencial imediato.
        </div>
      </div>

      <h2 id="especialidades" class="sect-sub" style="margin-top:14px">Especialidades e horários</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          O serviço contempla atendimento com médicos generalistas e acesso a especialidades (conforme disponibilidade).
        </p>

        <div class="info-grid" style="margin-top:10px">
          <div class="info">
            <h3>Generalistas</h3>
            <p>Disponível 24 horas, 7 dias na semana.</p>
          </div>
          <div class="info">
            <h3>Especialidades</h3>
            <p>Segunda a sexta-feira, das 09:00 às 18:00 (conforme disponibilidade).</p>
          </div>
          <div class="info">
            <h3>Carência</h3>
            <p>48 horas úteis após a ativação do seguro.</p>
          </div>
          <div class="info">
            <h3>Limite</h3>
            <p>Este serviço não possui limite de utilização.</p>
          </div>
        </div>

        <p class="p" style="margin-top:12px"><strong>Especialidades disponíveis (conforme material):</strong></p>
        <div class="tags" aria-label="Lista de especialidades">
          <span class="tag">Alergia e Imunologia</span>
          <span class="tag">Cirurgia Geral</span>
          <span class="tag">Cirurgia Vascular</span>
          <span class="tag">Coloproctologia</span>
          <span class="tag">Cardiologia</span>
          <span class="tag">Endocrinologia</span>
          <span class="tag">Ortopedia</span>
          <span class="tag">Clínico</span>
          <span class="tag">Pediatria</span>
          <span class="tag">Dermatologia</span>
          <span class="tag">Gastroenterologia</span>
          <span class="tag">Geriatria</span>
          <span class="tag">Ginecologia</span>
          <span class="tag">Hepatologia</span>
          <span class="tag">Hematologia</span>
          <span class="tag">Hemoterapia</span>
          <span class="tag">Infectologia</span>
          <span class="tag">Mastologia</span>
          <span class="tag">Nefrologia</span>
          <span class="tag">Neurologia</span>
          <span class="tag">Neuropediatria</span>
          <span class="tag">Oftalmologia</span>
          <span class="tag">Otorrinolaringologia</span>
          <span class="tag">Pneumologia</span>
          <span class="tag">Psiquiatria</span>
          <span class="tag">Reumatologia</span>
          <span class="tag">Urologia</span>
        </div>
      </div>

      <h2 id="exclusoes" class="sect-sub" style="margin-top:14px">Exclusões</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Situações citadas como fora do escopo do serviço incluem:
        </p>
        <ul class="ul">
          <li>Atendimento médico ou odontológico de urgência ou emergência;</li>
          <li>Eventuais atrasos/inviabilidade por caso fortuito ou força maior e eventos que impeçam a operação do serviço;</li>
          <li>Despesas relacionadas à não prestação do serviço ou execução de serviços não relacionados ao escopo;</li>
          <li>Procedimentos caracterizados como má-fé ou fraude na utilização do serviço, inclusive tentativa de obtenção de benefícios ilícitos.</li>
        </ul>

        <div class="warn">
          <strong>Importante:</strong> em caso de sinais de emergência, procure atendimento presencial imediato.
        </div>
      </div>

      <div class="foot">
        <span class="muted">Referência: Descritivo do Serviço • Doutor Online • Familiar • Out/2024 (Sabemi).</span>
      </div>

    </main>

    <!-- Coluna lateral -->
    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px">Atalhos</h3>
      <nav aria-label="Atalhos do benefício">
        <ul class="toc">
          <li><a href="https://beneficiario.redemaisaude.com.br" target="_blank" rel="noopener">Acessar portal</a></li>
          <li><a href="tel:08007766013">Ligar na central (0800)</a></li>
          <li><a href="#visao-geral">Visão geral</a></li>
          <li><a href="#como-acionar">Como acionar</a></li>
          <li><a href="#definicoes">Definições</a></li>
          <li><a href="#ativacao">Ativação e utilização</a></li>
          <li><a href="#teletriagem">Teletriagem</a></li>
          <li><a href="#teleconsulta">Teleconsulta</a></li>
          <li><a href="#especialidades">Especialidades</a></li>
          <li><a href="#exclusoes">Exclusões</a></li>
        </ul>
      </nav>

      <div class="mini">
        <div class="mini-title">Dica rápida</div>
        <div class="mini-text">
          Se for o primeiro acesso, tente entrar com CPF e faça a alteração de senha solicitada.
          Se tiver dificuldade de acesso, use a central 0800 para orientação de login/senha.
        </div>
      </div>
    </aside>
  </div>

</section>

<script>
  // Scroll suave para anchors internos
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href^="#"]');
    if(!a) return;
    const id = a.getAttribute('href');
    const el = document.querySelector(id);
    if(!el) return;
    e.preventDefault();
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    history.replaceState(null, '', id);
  });
</script>

</body>
</html>
<?php ob_end_flush(); ?>
