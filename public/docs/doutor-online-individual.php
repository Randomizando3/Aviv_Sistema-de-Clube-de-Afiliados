<?php
// View: Benefício • Doutor Online - Individual (Out/2024)
// Arquivo: /public/docs/doutor-online-individual.php

ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
  header('Content-Type: text/html; charset=UTF-8');
}

/**
 * Fallback de encoding:
 * - Se o arquivo foi salvo como ANSI/Windows-1252, o browser mostrará "�".
 * - Este buffer converte tudo para UTF-8 antes de enviar.
 */
$__converter = function ($buffer) {
  // se já for UTF-8 válido, não mexe
  if (@preg_match('//u', $buffer)) return $buffer;

  // tenta converter de Windows-1252 para UTF-8
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
  <title>Doutor Online (Individual) • Aviv+</title>
  <meta name="robots" content="noindex,nofollow">

  <style>
    .benefit-doc{ --txt:#111322; --muted:#6b7280; --bd:#d0d7e2; --bg:#ffffff; --bg2:#f8fafc; --brand:#2563eb; }
    .container{ width:min(92vw, 1120px); margin-inline:auto; }
    .glass-card{
      background:var(--bg);
      border:1px solid rgba(15,23,42,.06);
      padding:18px;
      border-radius:18px;
      color:var(--txt);
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
    .p{ margin:10px 0; line-height:1.6; color:#111827; }

    .doc-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:14px; }
    .doc-cta{ min-width:320px; text-align:right; display:flex; flex-direction:column; gap:8px; }
    @media (max-width:900px){
      .doc-head{ flex-direction:column; }
      .doc-cta{ text-align:left; min-width:0; }
    }

    .btn{
      display:inline-flex; align-items:center; justify-content:center; gap:8px;
      border-radius:12px; padding:10px 14px; border:1px solid var(--bd);
      background:#fff; color:var(--txt); font-weight:900; text-decoration:none;
    }
    .btn:hover{ box-shadow:0 8px 20px rgba(15,23,42,.10); }
    .btn-primary{ background:var(--brand); border-color:var(--brand); color:#fff; }
    .btn-ghost{ background:transparent; }

    .grid{ display:grid; gap:12px; grid-template-columns: 1fr 320px; align-items:start; }
    @media (max-width:980px){ .grid{ grid-template-columns:1fr; } .toc-card{ order:-1; } }

    .info-grid{ display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:12px; }
    @media (max-width:720px){ .info-grid{ grid-template-columns:1fr; } }
    .info{
      border:1px solid rgba(15,23,42,.10);
      border-radius:16px; padding:12px; background:#fff;
    }
    .info h3{ margin:0 0 6px; font-size:1.02rem; font-weight:900; }
    .info p{ margin:0; color:#111827; line-height:1.55; font-size:.95rem; }

    .callout{
      margin-top:12px;
      border:1px solid rgba(37,99,235,.25);
      background:rgba(37,99,235,.06);
      border-radius:16px;
      padding:12px;
      color:#111827;
    }
    .callout strong{ font-weight:900; }

    ul, ol{ color:#111827; line-height:1.55; }
    .list{ margin:8px 0 0 18px; }
    .list li{ margin:6px 0; }

    .pillrow{ display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .pill{
      display:inline-flex; align-items:center; gap:6px;
      padding:6px 10px; border-radius:999px;
      background:#f9fafb; border:1px solid #e5e7eb;
      font-weight:800; font-size:.85rem; color:#374151;
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

    .toc{ list-style:none; padding:0; margin:8px 0 0; display:grid; gap:8px; }
    .toc a{
      display:block; padding:10px 12px; border-radius:14px;
      border:1px solid rgba(15,23,42,.10); background:#fff; color:var(--txt);
      text-decoration:none; font-weight:900;
    }
    .toc a:hover{ background:#eef2ff; border-color:#a5b4fc; }

    .mini{
      margin-top:12px; border:1px dashed rgba(15,23,42,.18);
      border-radius:16px; padding:12px; background:#fff;
    }
    .mini-title{ font-weight:900; margin-bottom:4px; }
    .mini-text{ color:#111827; line-height:1.55; font-size:.95rem; }

    .foot{ margin-top:14px; padding-top:10px; border-top:1px solid rgba(15,23,42,.08); }
  </style>
</head>

<body>
<section class="container benefit-doc" style="margin-top:18px">

  <div class="glass-card">
    <div class="doc-head">
      <div>
        <div class="kicker">Sabemi • Descritivo do serviço • Out/2024</div>
        <h1 class="sect-title">Doutor Online — Individual</h1>
        <p class="muted" style="margin:6px 0 0">
          Telemedicina/teleconsulta com fluxo de orientação inicial (teletriagem), consulta e encaminhamentos, conforme regras do serviço.
        </p>

        <div class="pillrow">
          <span class="pill">Atendimento 24h / 7 dias</span>
          <span class="pill">Carência: 48 horas úteis</span>
          <span class="pill">Sem limite de utilização</span>
        </div>
      </div>

      <div class="doc-cta">
        <a class="btn btn-primary" href="https://beneficiario.redemaisaude.com.br" target="_blank" rel="noopener">
          Acessar portal RedeMais Saúde
        </a>
        <a class="btn btn-ghost" href="tel:08007766013" rel="noopener">
          Ligar na central: 0800 776 6013
        </a>
        <div class="muted" style="font-size:.82rem">
          Acionamento via aplicativo RedeMais Saúde, portal do beneficiário ou central telefônica.
        </div>
      </div>
    </div>
  </div>

  <div class="grid" style="margin-top:12px">
    <main class="glass-card">

      <h2 class="sect-sub" id="acionar">Como acionar</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Para usar o benefício, acione pela plataforma RedeMais Saúde utilizando seu CPF. O acesso pode ser feito:
        </p>
        <ul class="list">
          <li>Pelo aplicativo RedeMais Saúde;</li>
          <li>Pelo portal do beneficiário (link acima);</li>
          <li>Pela central de atendimento 0800 776 6013.</li>
        </ul>
        <div class="callout" style="margin-top:10px">
          <strong>Primeiro acesso:</strong> utilize o CPF como login e a senha inicial conforme a regra do serviço; no primeiro acesso, pode ser solicitada a alteração de senha.
        </div>
      </div>

      <h2 class="sect-sub" style="margin-top:14px" id="definicoes">Definições principais</h2>
      <div class="info-grid">
        <div class="info">
          <h3>Canais de comunicação</h3>
          <p>Aplicativo RedeMais Saúde, portal do beneficiário e central telefônica (0800).</p>
        </div>
        <div class="info">
          <h3>Central da assistência</h3>
          <p>Canal telefônico do prestador, disponível 24 horas por dia, 7 dias por semana.</p>
        </div>
        <div class="info">
          <h3>Horário de prestação</h3>
          <p>Disponível 24 horas por dia, 7 dias por semana para acionamento do serviço.</p>
        </div>
        <div class="info">
          <h3>Telemedicina</h3>
          <p>Consulta à distância por vídeo para avaliação clínica, podendo gerar orientações, exames e prescrição quando aplicável.</p>
        </div>
        <div class="info">
          <h3>Segurado</h3>
          <p>Pessoa física segurada, com domicílio permanente no Brasil, vinculada à apólice.</p>
        </div>
        <div class="info">
          <h3>Vigência</h3>
          <p>A garantia do serviço acompanha a vigência da apólice/contratação do segurado.</p>
        </div>
      </div>

      <h2 class="sect-sub" style="margin-top:14px" id="ativacao">Ativação e utilização</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          A ativação ocorre automaticamente após a adesão/ativação junto à seguradora, sem necessidade de cadastro prévio no prestador.
        </p>
        <p class="p">
          No uso, o segurado entra na plataforma e realiza o atendimento inicial. Conforme disponibilidade e necessidade clínica,
          pode ocorrer consulta com clínico no momento, ou agendamento para momento mais oportuno. Se necessário, há encaminhamento
          para teleconsulta com especialista, sem custo adicional indicado no material.
        </p>

        <div class="callout">
          <strong>Concierge / Central de atendimento:</strong> equipe multidisciplinar para orientar e sanar dúvidas do segurado.
          <br><strong>Atendimento:</strong> 24h / 7 dias.
          <br><strong>Carência:</strong> 48 horas úteis após ativação.
          <br><strong>Limite:</strong> não possui limite de utilização.
        </div>
      </div>

      <h2 class="sect-sub" style="margin-top:14px" id="teletriagem">Orientação inicial (teletriagem)</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          O primeiro atendimento é feito por teletriagem, normalmente por equipe de enfermagem via teleconsulta.
          O serviço observa regras de privacidade e segurança em saúde e diretrizes éticas aplicáveis.
        </p>
        <p class="p" style="margin-bottom:6px"><strong>Após a teletriagem, o segurado pode:</strong></p>
        <ul class="list">
          <li>Receber orientações de autocuidado;</li>
          <li>Ser direcionado a atendimento ambulatorial ou emergência em hospital;</li>
          <li>Ser convidado a participar de consulta eletiva ou pronto atendimento, conforme preferência;</li>
          <li>Ser transferido para teleconsulta imediata, se houver agenda disponível;</li>
          <li>Ter teleconsulta agendada para momento mais oportuno.</li>
        </ul>
      </div>

      <h2 class="sect-sub" style="margin-top:14px" id="teleconsulta">Teleconsulta</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          A teleconsulta é realizada por vídeo com médicos clínicos gerais ou especialistas. Após a orientação inicial,
          pode ser enviado ao segurado (por exemplo via SMS) um link para acesso à consulta por vídeo.
        </p>
        <div class="callout">
          <strong>Coparticipação:</strong> o material informa que a teleconsulta não possui coparticipação.
        </div>

        <p class="p" style="margin-bottom:6px"><strong>Formas de condução do atendimento:</strong></p>
        <ul class="list">
          <li>Avaliação baseada na gravidade dos sintomas/queixas, considerando múltiplos sintomas quando aplicável;</li>
          <li>Encaminhamento a especialistas conforme sinais e sintomas avaliados;</li>
          <li>Vídeo-consulta no momento (após teletriagem) ou agendada, 24h/dia, todos os dias do ano;</li>
          <li>Solicitação de exames complementares e retorno para interpretação de resultados;</li>
          <li>Prescrição de tratamento quando relacionada ao motivo do atendimento;</li>
          <li>Teleconsulta não indicada para emergências médicas;</li>
          <li>Indicação de deslocamento para unidades de emergência/hospitais quando necessário.</li>
        </ul>
      </div>

      <h2 class="sect-sub" style="margin-top:14px" id="especialidades">Especialidades e horários</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          O serviço contempla atendimento com clínico geral e acesso a diversas especialidades médicas (conforme disponibilidade).
        </p>

        <p class="p" style="margin:0 0 6px"><strong>Atendimento com médicos generalistas:</strong> 24h / 7 dias.</p>
        <p class="p" style="margin:0 0 10px"><strong>Atendimento com especialistas:</strong> Segunda a sexta, das 09:00 às 18:00 (exceto sábados, domingos e feriados).</p>

        <div class="callout" style="margin-top:0">
          <strong>Carência:</strong> 48 horas úteis após ativação do segurado junto à seguradora.
          <br><span class="muted">O serviço de telemedicina permanece disponível enquanto permitido pelos órgãos reguladores citados no material.</span>
        </div>

        <p class="p" style="margin-bottom:6px"><strong>Lista de especialidades (conforme material):</strong></p>
        <div class="pillrow">
          <span class="pill">Alergia/Imunologia</span><span class="pill">Cirurgia Geral</span><span class="pill">Cirurgia Vascular</span>
          <span class="pill">Coloproctologia</span><span class="pill">Cardiologia</span><span class="pill">Endocrinologia</span>
          <span class="pill">Ortopedia</span><span class="pill">Clínico</span><span class="pill">Pediatria</span>
          <span class="pill">Dermatologia</span><span class="pill">Gastroenterologia</span><span class="pill">Geriatria</span>
          <span class="pill">Ginecologia</span><span class="pill">Hepatologia</span><span class="pill">Hematologia</span>
          <span class="pill">Hemoterapia</span><span class="pill">Infectologia</span><span class="pill">Mastologia</span>
          <span class="pill">Nefrologia</span><span class="pill">Neurologia</span><span class="pill">Neuropediatria</span>
          <span class="pill">Oftalmologia</span><span class="pill">Otorrinolaringologia</span><span class="pill">Pneumologia</span>
          <span class="pill">Psiquiatria</span><span class="pill">Reumatologia</span><span class="pill">Urologia</span>
        </div>
      </div>

      <h2 class="sect-sub" style="margin-top:14px" id="exclusoes">Exclusões</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Situações citadas como fora do escopo do serviço incluem:
        </p>
        <ul class="list">
          <li>Atendimento médico ou odontológico de urgência/emergência;</li>
          <li>Atrasos/indisponibilidade por caso fortuito ou força maior e eventos que impeçam a operação do serviço;</li>
          <li>Despesas relacionadas à não prestação do serviço ou execução de serviço não relacionado ao escopo;</li>
          <li>Procedimentos caracterizados como má-fé ou fraude na utilização do serviço, inclusive tentativa de obtenção de benefícios ilícitos.</li>
        </ul>

        <div class="warn">
          <strong>Importante:</strong> em caso de sinais de emergência, procure atendimento presencial imediato.
        </div>
      </div>

      <div class="foot">
        <span class="muted">Referência: Descritivo do Serviço • Doutor Online – Individual • Out/2024 (Sabemi).</span>
      </div>
    </main>

    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px">Atalhos</h3>
      <nav aria-label="Atalhos do benefício">
        <ul class="toc">
          <li><a href="https://beneficiario.redemaisaude.com.br" target="_blank" rel="noopener">Acessar portal</a></li>
          <li><a href="tel:08007766013">Ligar na central (0800)</a></li>
          <li><a href="#acionar">Como acionar</a></li>
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
          Se você não conseguir acessar pelo portal, ligue na central 0800 e solicite orientação de acesso (login/senha) e fluxo de teleconsulta.
        </div>
      </div>
    </aside>
  </div>
</section>
</body>
</html>
<?php ob_end_flush(); ?>
