<?php
// View: Benefício • Assistência Funeral Familiar — Estendido Exclusivo (Ago/2024)
// Arquivo: /public/docs/funeral-estendido-exclusivo.php

ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
  header('Content-Type: text/html; charset=UTF-8');
}

/**
 * Fallback de encoding:
 * Se o arquivo for salvo em ANSI/Windows-1252 e aparecer "�", converte a saída para UTF-8.
 */
$__converter = function ($buffer) {
  if (@preg_match('//u', $buffer)) return $buffer;
  if (function_exists('mb_convert_encoding')) return mb_convert_encoding($buffer, 'UTF-8', 'Windows-1252');
  if (function_exists('iconv')) return iconv('Windows-1252', 'UTF-8//IGNORE', $buffer);
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
  <title>Assistência Funeral Familiar • Estendido Exclusivo</title>
  <meta name="robots" content="noindex,nofollow">

  <style>
    :root{
      --txt:#111322;
      --muted:#6b7280;
      --bd:#d0d7e2;
      --bg:#ffffff;
      --bg2:#f8fafc;
      --brand:#2563eb;
      --ok:#0ea5e9;
      --warn:#f59e0b;
    }
    body{ margin:0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Helvetica Neue", sans-serif; background:#ffffff; color:var(--txt); }
    .container{ width:min(92vw, 1120px); margin-inline:auto; }
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
    .kicker{ font-size:.82rem; color:#4b5563; font-weight:800; }
    .sect-title{ margin:0; font-weight:950; letter-spacing:-.02em; line-height:1.12; }
    .sect-sub{ margin:0 0 8px; font-weight:950; }
    .p{ margin:10px 0; line-height:1.65; color:#111827; }
    .ul{ margin:10px 0 0 18px; color:#111827; line-height:1.6; }
    .ul li{ margin:6px 0; }

    /* header */
    .doc-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:14px; }
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
      font-weight:950;
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

    /* chips */
    .chips{ display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .chip{
      display:inline-flex; align-items:center; gap:6px;
      padding:6px 10px; border-radius:999px;
      background:#f9fafb; border:1px solid #e5e7eb;
      font-weight:950; font-size:.85rem; color:#374151;
    }

    /* callouts */
    .callout{
      margin-top:12px;
      border:1px solid rgba(14,165,233,.30);
      background:rgba(14,165,233,.08);
      border-radius:16px;
      padding:12px;
      color:#111827;
      line-height:1.6;
    }
    .callout strong{ font-weight:950; }
    .warn{
      margin-top:10px;
      border:1px solid rgba(245,158,11,.35);
      background:rgba(245,158,11,.12);
      border-radius:14px;
      padding:10px;
      color:#111827;
      line-height:1.6;
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
    .info h3{ margin:0 0 6px; font-size:1.02rem; font-weight:950; }
    .info p{ margin:0; color:#111827; line-height:1.55; font-size:.95rem; }

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
      font-weight:950;
    }
    .toc a:hover{ background:#eef2ff; border-color:#a5b4fc; }

    .mini{
      margin-top:12px;
      border:1px dashed rgba(15,23,42,.18);
      border-radius:16px;
      padding:12px;
      background:#fff;
    }
    .mini-title{ font-weight:950; margin-bottom:4px; }
    .mini-text{ color:#111827; line-height:1.55; font-size:.95rem; }

    /* tags */
    .tags{ display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .tag{
      display:inline-flex;
      padding:6px 10px;
      border-radius:999px;
      background:#fff;
      border:1px solid rgba(15,23,42,.10);
      font-weight:950;
      font-size:.85rem;
      color:#374151;
    }

    .foot{
      margin-top:14px;
      padding-top:10px;
      border-top:1px solid rgba(15,23,42,.08);
    }
  </style>
</head>

<body>
<section class="container benefit-doc" style="margin-top:18px">

  <div class="glass-card">
    <div class="doc-head">
      <div>
        <div class="kicker">Sabemi • Descritivo do serviço • Ago/2024</div>
        <h1 class="sect-title">Assistência Funeral Familiar — Estendido Exclusivo</h1>

        <p class="muted" style="margin:8px 0 0">
          Para acionar a assistência, ligue para a central: <strong>0800 880 1900</strong>.
        </p>

        <div class="chips" aria-label="Destaques do serviço">
          <span class="chip">Atendimento 24 horas</span>
          <span class="chip">Âmbito: território brasileiro</span>
          <span class="chip">Opção: sepultamento ou cremação</span>
        </div>
      </div>

      <div class="doc-cta">
        <a class="btn btn-primary" href="tel:08008801900" aria-label="Ligar para 0800 880 1900">
          Ligar agora: 0800 880 1900
        </a>

        <a class="btn btn-outline" style="margin-top:10px" href="#solicitacao">
          Ver como solicitar
        </a>

        <div class="muted" style="font-size:.82rem;margin-top:8px">
          Acione antes de tomar qualquer medida por conta própria.
        </div>
      </div>
    </div>
  </div>

  <div class="terms-grid" style="margin-top:12px">
    <!-- Coluna principal -->
    <main class="glass-card doc-body">

      <h2 id="visao" class="sect-sub">Visão geral</h2>
      <p class="p">
        O serviço tem como objetivo viabilizar a realização do funeral, observando os limites contratados e durante a vigência
        da cobertura individual. A assistência considera condições de religiosidade/crença manifestadas pelo solicitante, e também
        a infraestrutura do local do óbito.
      </p>

      <div class="callout">
        <strong>Central de acionamento:</strong> 0800 880 1900.<br>
        <strong>Disponibilidade:</strong> 24 (vinte e quatro) horas.
      </div>

      <h2 id="definicoes" class="sect-sub" style="margin-top:14px">Definições e público atendido</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li><strong>Modalidade Estendido:</strong> prestação do serviço para Segurado Titular, Cônjuge, Pai, Mãe, Sogro, Sogra e Filhos (até 21 anos, conforme condições do serviço), incluindo previsões específicas para dependência e deficiência quando aplicável.</li>
          <li><strong>Limite de idade (Titular):</strong> 80 anos na adesão ao seguro (salvo disposição em contrário no contrato).</li>
          <li><strong>Pais/sogros:</strong> limite indicado para realização do serviço até 80 anos na data do óbito, conforme condições do serviço.</li>
          <li><strong>Âmbito territorial:</strong> serviços prestados em território brasileiro.</li>
          <li><strong>Domicílio:</strong> endereço residencial permanente do segurado (Brasil).</li>
          <li><strong>Tanatopraxia:</strong> técnica de preservação do corpo por alguns dias; disponível quando houver previsão legal/normativa que exija esse serviço.</li>
        </ul>
      </div>

      <h2 id="como-funciona" class="sect-sub" style="margin-top:14px">Como funciona o atendimento</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Na ocorrência de óbito, um familiar ou pessoa credenciada deve contatar a central (0800 880 1900), comunicar o falecimento
          e seguir as instruções repassando as informações necessárias (identificação do segurado, local do corpo, local do sepultamento/cremação, etc.).
        </p>
        <p class="p" style="margin-bottom:0">
          Após conferência das informações, a prestadora contata a funerária credenciada/autorizada no município para providenciar o necessário,
          respeitando o limite do valor contratado disponível no Certificado do Seguro/Apólice/Bilhete/Guia.
        </p>
      </div>

      <h2 id="coberturas" class="sect-sub" style="margin-top:14px">Serviços cobertos</h2>

      <div class="info-grid">
        <div class="info">
          <h3>Sepultamento</h3>
          <p>
            Providência do sepultamento em jazigo da família/cemitério municipal. Se não houver jazigo, pode haver locação por prazo indicado
            (ex.: 3 anos), conforme regras do serviço e do cemitério.
          </p>
        </div>

        <div class="info">
          <h3>Cremação</h3>
          <p>
            Disponível quando houver no município de domicílio. Se inexistente, pode haver traslado até a cidade mais próxima com o serviço,
            com limite de deslocamento (ex.: até 200 km ida e volta), e retorno das cinzas.
          </p>
        </div>

        <div class="info">
          <h3>Taxas e formalidades</h3>
          <p>
            Taxa de sepultamento (conforme município), registro em cartório quando permitido por legislação local, e procedimentos administrativos
            relacionados ao funeral conforme condições do serviço.
          </p>
        </div>

        <div class="info">
          <h3>Carro fúnebre e velório</h3>
          <p>
            Carro fúnebre para cortejo (conforme limite), locação de sala para velório (cemitério municipal) quando aplicável, livro de presença,
            ornamentação de urna e sala.
          </p>
        </div>
      </div>

      <div class="warn">
        <strong>Importante:</strong> o solicitante deve optar por <strong>sepultamento</strong> ou <strong>cremação</strong>.
        A utilização de um serviço anula o outro (e vice-versa), conforme condições do benefício.
      </div>

      <h2 id="detalhes-sepultamento" class="sect-sub" style="margin-top:14px">Detalhes — Sepultamento</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li><strong>Taxa de sepultamento:</strong> custeada conforme valor/designação do município onde ocorrer o sepultamento/cremação.</li>
          <li><strong>Cemitério particular:</strong> se taxas forem superiores à modalidade contratada, a diferença é de responsabilidade do solicitante/família.</li>
          <li><strong>Traslado para sepultamento fora do município do domicílio:</strong> pode não ser de responsabilidade da assistência (conforme condições do serviço).</li>
          <li><strong>Taxa de exumação:</strong> regras específicas se aplicam (ex.: quando todas as gavetas do jazigo estiverem ocupadas; quando a taxa for exigida antecipadamente; e conforme período de locação do jazigo).</li>
        </ul>
      </div>

      <h2 id="detalhes-cremacao" class="sect-sub" style="margin-top:14px">Detalhes — Cremação</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Para cremação, devem ser observadas exigências e documentos previstos. Em caso de morte violenta, o material cita documentação como:
          atestado/declarações e autorizações competentes, laudo do IML, boletim de ocorrência, entre outros, conforme aplicável.
        </p>
        <ul class="ul">
          <li>Atestado de óbito firmado por 2 (dois) médicos (quando exigido);</li>
          <li>Autorização concedida pelo parente mais próximo, conforme regras citadas no material;</li>
          <li>Se crematório particular tiver custo superior ao contratado, a diferença é do solicitante.</li>
        </ul>
        <p class="p" style="margin-bottom:0">
          Se não houver crematório no município de domicílio, pode haver traslado para cidade mais próxima com serviço de cremação, com limite de distância,
          e posterior retorno das cinzas aos familiares.
        </p>
      </div>

      <h2 id="velorio" class="sect-sub" style="margin-top:14px">Velório, urna e ornamentação</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li><strong>Locação de sala para velório:</strong> despesas de locação em cemitério municipal na cidade indicada; em cemitério particular, valores excedentes podem ser do solicitante/família.</li>
          <li><strong>Livro de presença:</strong> disponibilização para registro de comparecimento.</li>
          <li><strong>Ornamentação:</strong> inclui itens como coroa de flores (simples), manta mortuária, jogo de paramentos (castiçais, velas, suporte para urna e imagens/insígnias conforme religião e disponibilidade local), véu e velas.</li>
          <li><strong>Urna mortuária:</strong> fornecida conforme padrão descrito no material; caso solicitante opte por troca/upgrade, arca integralmente com o valor cobrado pela funerária.</li>
        </ul>
      </div>

      <h2 id="traslado" class="sect-sub" style="margin-top:14px">Traslado e preparação do corpo</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Em caso de falecimento em cidade diferente do domicílio, a assistência pode se encarregar de formalidades e do traslado do corpo até o local de inumação
          no domicílio, considerando limites de distância e condições do serviço (ex.: até 300 km ida e volta).
        </p>
        <ul class="ul">
          <li>Meio de transporte pode ser definido pela assistência (aéreo/terrestre), conforme necessidade e regras do serviço.</li>
          <li>Despesas que excedam o limite contratado (ou mudanças solicitadas) podem ficar por conta da família.</li>
          <li><strong>Preparação do corpo:</strong> inclui higienização (preparação simples – limpeza, formalização e tamponamento) e tanatopraxia, conforme condições do serviço.</li>
        </ul>
      </div>

      <h2 id="solicitacao" class="sect-sub" style="margin-top:14px">Solicitação da assistência</h2>
      <div class="callout">
        Para utilização da <strong>Assistência Funeral Estendido</strong>, o solicitante/familiar deve acionar o serviço pelo
        <strong>0800 880 1900</strong>, informando nome e CPF do segurado e familiares elegíveis, além das informações solicitadas pela central.
        O acionamento deve ser feito antes de qualquer medida pessoal em relação ao funeral.
      </div>

      <div class="warn">
        <strong>Sem reembolso:</strong> o serviço deve ser acionado para prestação dos serviços cobertos. Não há reembolso de despesas realizadas por conta própria,
        conforme regra indicada no material.
      </div>

      <h2 id="exclusoes" class="sect-sub" style="margin-top:14px">Exclusões (principais)</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Exemplos de itens/situações citadas como não cobertas incluem (lista não exaustiva):
        </p>
        <ul class="ul">
          <li>Eventos decorrentes de atos ilícitos praticados pelo segurado;</li>
          <li>Suicídio consumado ou frustrado dentro de prazo inferior a 2 anos do início de vigência (conforme material);</li>
          <li>Terrorismo, guerras, revoltas, greves, sabotagem, tumultos e afins;</li>
          <li>Irradiação/transmutação nuclear, desintegração/radioatividade;</li>
          <li>Fenômenos naturais extraordinários (inundações, terremotos, erupções, tempestades, furacões, maremotos etc.);</li>
          <li>Despesas com alimentação/bebidas, missa de 7º dia, xerox de documentação, vestimentas/roupas;</li>
          <li>Despesas/itens sem autorização prévia da central;</li>
          <li>Compra de sepultura/jazigo/terreno/cova/carneiro e outros itens patrimoniais;</li>
          <li>Taxas de capela/sepultamento/cremação superiores às praticadas pelo município (diferença pode ser do solicitante/família).</li>
        </ul>
      </div>

      <div class="foot">
        <span class="muted">Referência: Descritivo do Serviço • Assistência Funeral Familiar • Estendido Exclusivo • Ago/2024 (Sabemi).</span>
      </div>

    </main>

    <!-- Coluna lateral -->
    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px">Atalhos</h3>
      <nav aria-label="Atalhos do benefício">
        <ul class="toc">
          <li><a href="tel:08008801900">Ligar: 0800 880 1900</a></li>
          <li><a href="#visao">Visão geral</a></li>
          <li><a href="#definicoes">Definições</a></li>
          <li><a href="#coberturas">Serviços cobertos</a></li>
          <li><a href="#detalhes-sepultamento">Sepultamento</a></li>
          <li><a href="#detalhes-cremacao">Cremação</a></li>
          <li><a href="#velorio">Velório/Urna</a></li>
          <li><a href="#traslado">Traslado/Preparação</a></li>
          <li><a href="#solicitacao">Solicitação</a></li>
          <li><a href="#exclusoes">Exclusões</a></li>
        </ul>
      </nav>

      <div class="mini">
        <div class="mini-title">Dica rápida</div>
        <div class="mini-text">
          Em caso de óbito, a primeira ação deve ser ligar para a central <strong>0800 880 1900</strong> e seguir as instruções.
          Evite contratar serviços por fora antes do acionamento para não perder a cobertura.
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
