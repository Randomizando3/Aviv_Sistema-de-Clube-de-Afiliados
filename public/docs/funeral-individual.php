<?php
// View: Benefício • Assistência Funeral Individual (Ago/2024) — Sabemi
// Arquivo: /public/docs/funeral-individual.php

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
  <title>Assistência Funeral Individual • Sabemi</title>
  <meta name="robots" content="noindex,nofollow">

  <style>
    :root{
      --txt:#111322;
      --muted:#6b7280;
      --bd:#d0d7e2;
      --bg:#ffffff;
      --bg2:#f8fafc;
      --brand:#2563eb;
      --warn:#f59e0b;
      --info:#0ea5e9;
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
    .kicker{ font-size:.82rem; color:#4b5563; font-weight:900; }
    .sect-title{ margin:0; font-weight:950; letter-spacing:-.02em; line-height:1.12; }
    .sect-sub{ margin:0 0 8px; font-weight:950; }

    .p{ margin:10px 0; line-height:1.68; color:#111827; }
    .ul{ margin:10px 0 0 18px; color:#111827; line-height:1.65; }
    .ul li{ margin:6px 0; }

    /* header */
    .doc-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:14px; }
    .doc-cta{ min-width:340px; text-align:right; }
    @media (max-width:900px){
      .doc-head{ flex-direction:column; }
      .doc-cta{ text-align:left; min-width:0; }
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
      font-weight:950;
      text-decoration:none;
      width:100%;
      box-sizing:border-box;
    }
    .btn:hover{ box-shadow:0 8px 20px rgba(15,23,42,.10); }
    .btn-primary{ background:var(--brand); border-color:var(--brand); color:#fff; }
    .btn-outline{ background:transparent; }

    .chips{ display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .chip{
      display:inline-flex; align-items:center; gap:6px;
      padding:6px 10px; border-radius:999px;
      background:#f9fafb; border:1px solid #e5e7eb;
      font-weight:950; font-size:.85rem; color:#374151;
    }

    /* layout */
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
    .warn{
      margin-top:10px;
      border:1px solid rgba(245,158,11,.35);
      background:rgba(245,158,11,.12);
      border-radius:14px;
      padding:10px;
      color:#111827;
      line-height:1.6;
    }

    /* info cards */
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
        <h1 class="sect-title">Assistência Funeral Individual</h1>

        <p class="muted" style="margin:8px 0 0">
          Para acionar sua assistência, basta ligar para a central: <strong>0800 880 1900</strong>.
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
        O Serviço de Assistência Funeral Individual tem como objetivo a realização do funeral do segurado falecido,
        qualquer que seja a causa (natural ou acidental), observados os limites contratados, durante a vigência da cobertura.
      </p>

      <div class="callout">
        <strong>Central de acionamento:</strong> 0800 880 1900.<br>
        <strong>Disponibilidade:</strong> 24 (vinte e quatro) horas.
      </div>

      <h2 id="definicoes" class="sect-sub" style="margin-top:14px">Definições</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li><strong>Modalidade Individual:</strong> garante a prestação de serviço para o segurado titular.</li>
          <li><strong>Limite de idade:</strong> limite de idade do segurado titular é de 80 (oitenta) anos na adesão (salvo disposição contratual).</li>
          <li><strong>Âmbito territorial:</strong> serviços prestados em todo território brasileiro.</li>
          <li><strong>Domicílio:</strong> endereço residencial permanente do segurado, em território brasileiro.</li>
          <li><strong>Segurado:</strong> pessoa física que contrata a apólice/seguro com domicílio permanente no Brasil.</li>
          <li><strong>Tanatopraxia:</strong> técnica que possibilita a preservação do corpo por alguns dias; disponível apenas quando houver previsão legal/normativa equivalente que exija o serviço.</li>
        </ul>
      </div>

      <h2 id="como-funciona" class="sect-sub" style="margin-top:14px">Como funciona</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Na hipótese de óbito do segurado, um familiar ou porta-voz credenciado deve contatar a central (0800 880 1900),
          comunicar o falecimento e seguir as instruções, repassando informações necessárias para a identificação do segurado,
          local onde se encontra o corpo, se a família possui jazigo e outras informações para execução do serviço.
        </p>
        <p class="p" style="margin-bottom:0">
          Após conferência das informações, a prestadora comunicará a funerária credenciada/autorizada no município para providenciar o necessário,
          observando o limite do valor contratado.
        </p>
      </div>

      <h2 id="cobertos" class="sect-sub" style="margin-top:14px">Serviços cobertos</h2>
      <p class="p">No momento da solicitação, o solicitante deverá indicar se optará por <strong>sepultamento</strong> ou <strong>cremação</strong>.</p>

      <div class="info-grid">
        <div class="info">
          <h3>Sepultamento</h3>
          <p>
            Providencia o sepultamento em jazigo da família, em cemitério municipal, na cidade indicada.
            Se a família não possuir jazigo, a assistência se responsabiliza pela locação de sepultura por prazo de <strong>3 (três) anos</strong>.
          </p>
        </div>

        <div class="info">
          <h3>Cremação</h3>
          <p>
            Quando disponível no município de domicílio, a assistência providencia o serviço conforme legislação e normas vigentes.
            Em caso de inexistência de crematório, pode haver traslado do corpo para a cidade mais próxima (raio máximo citado de <strong>até 100 km ida e volta</strong>)
            e posterior retorno das cinzas à família.
          </p>
        </div>

        <div class="info">
          <h3>Taxas relacionadas</h3>
          <p>
            Inclui despesas como taxa de sepultamento (conforme município), locação de jazigo/sepultura (quando aplicável) e regras específicas para exumação
            conforme condições do serviço.
          </p>
        </div>

        <div class="info">
          <h3>Carro fúnebre e velório</h3>
          <p>
            Fornecimento de carro fúnebre para cortejo, locação de sala para velório em cemitério municipal (quando aplicável),
            livro de presença e ornamentação de urna e sala, conforme condições.
          </p>
        </div>
      </div>

      <div class="warn">
        <strong>Importante:</strong> a utilização do serviço de <strong>sepultamento</strong> anula a utilização do serviço de <strong>cremação</strong> e vice-versa.
      </div>

      <h2 id="sepultamento" class="sect-sub" style="margin-top:14px">Detalhes — Sepultamento</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li>Se a família escolher <strong>cemitério particular</strong> com taxas superiores às municipais, a diferença é de responsabilidade do solicitante.</li>
          <li><strong>Traslado do corpo</strong> quando o sepultamento ocorrer fora do município de domicílio do segurado: indicado como <strong>não sendo de responsabilidade</strong> da assistência funeral.</li>
          <li><strong>Taxa de sepultamento:</strong> a assistência se responsabiliza por providenciar e custear, conforme valores do município onde ocorrer o sepultamento/cremação.</li>
        </ul>
      </div>

      <h2 id="exumacao" class="sect-sub" style="margin-top:14px">Taxa de exumação (regras principais)</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li>Se a família tiver jazigo próprio e todas as gavetas estiverem ocupadas, a assistência pode se responsabilizar pela taxa de exumação (conforme condições).</li>
          <li>Se for exigido pagamento antecipado da taxa pelo cemitério, há regras específicas conforme locação/condições do serviço.</li>
          <li>Se a assistência realizou a locação do jazigo, após o término do período, a assistência não se responsabiliza por taxa de exumação nem pelo corpo que ocupava o jazigo (passando a ser responsabilidade do cemitério, conforme indicado).</li>
        </ul>
      </div>

      <h2 id="cremacao" class="sect-sub" style="margin-top:14px">Detalhes — Cremação e documentos</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Para cremação, devem ser cumpridas orientações e exigências, incluindo documentação. O material cita, por exemplo:
        </p>
        <ul class="ul">
          <li>Atestado de óbito firmado por <strong>2 (dois) médicos</strong> (exigência mencionada);</li>
          <li>Em caso de <strong>morte violenta</strong>, documentação como atestado por médico legista, autorização judicial, laudo do IML, boletim de ocorrência e declaração de autoridade policial (conforme aplicável);</li>
          <li>Autorização de cremação concedida pelo parente mais próximo (conforme indicado).</li>
        </ul>
        <p class="p" style="margin-bottom:0">
          Se a cremação ocorrer em crematório particular com taxas superiores à modalidade contratada, a diferença é de responsabilidade do solicitante.
        </p>
      </div>

      <h2 id="formalidades" class="sect-sub" style="margin-top:14px">Formalidades administrativas e registro</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li>A assistência pode disponibilizar representante no domicílio ou hospital onde ocorreu o óbito para coleta de documentos e tratativas do sepultamento/cremação junto à funerária.</li>
          <li><strong>Registro em cartório:</strong> a assistência pode providenciar e custear o registro em cartório do óbito, quando permitido pela legislação local.</li>
          <li>A liberação do corpo no IML ou hospital é responsabilidade de representante legal do segurado (conforme indicado).</li>
        </ul>
      </div>

      <h2 id="cortejo" class="sect-sub" style="margin-top:14px">Carro fúnebre, velório e ornamentação</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li><strong>Carro fúnebre:</strong> fornecido para cortejo conforme limite; prestado apenas dentro do município de sepultamento (não sendo alterado para deslocamento), com <strong>locomoção máxima citada de 100 km</strong>.</li>
          <li><strong>Locação de sala para velório:</strong> despesas referentes à locação de sala para velório em cemitério municipal na cidade indicada.</li>
          <li><strong>Livro de presença:</strong> disponibilizado para registro de comparecimento.</li>
          <li><strong>Ornamentação de urna e sala:</strong> coroa de flores (simples), manta mortuária, jogo de paramentos (castiçais, velas, suporte para urna e imagens/insígnias conforme religião e disponibilidade local), véu e velas.</li>
        </ul>
      </div>

      <h2 id="urna" class="sect-sub" style="margin-top:14px">Urna mortuária (especificações)</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li>Urna padrão simples;</li>
          <li>Modelo sextavada, com ou sem visor, madeira bordada em relevo, alça varão ou argola;</li>
          <li>Acabamento interno: forro samilon e babado simples;</li>
          <li>Acabamento externo: verniz PU alto brilho, com Bíblia, lisa ou cruz.</li>
        </ul>
        <div class="warn">
          <strong>Importante:</strong> se este modelo não puder ser fornecido, será utilizada urna padrão similar.
          Caso o solicitante opte por troca/upgrade, arca integralmente com o valor cobrado pela funerária.
        </div>
      </div>

      <h2 id="traslado" class="sect-sub" style="margin-top:14px">Traslado do corpo</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Em caso de falecimento em cidade diferente do domicílio, a assistência pode se encarregar das formalidades para a liberação do corpo/cinzas
          e do traslado até o local de inumação no domicílio, considerando distância máxima citada de <strong>até 100 km</strong> (ida e volta).
        </p>
        <ul class="ul">
          <li>O meio de transporte pode ser definido conforme necessidade (aéreo/terrestre).</li>
          <li>Em regra, pode ser considerado transporte aéreo quando o corpo estiver a mais de <strong>300 km</strong> do endereço do domicílio, ou quando o trajeto rodoviário superar <strong>5 (cinco) horas</strong>.</li>
          <li>Despesas que excedam o custo/limite ou modificações solicitadas podem correr por conta da família (ex.: passagens e hospedagem).</li>
        </ul>
      </div>

      <h2 id="preparacao" class="sect-sub" style="margin-top:14px">Preparação do corpo</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li>Higienização (preparação simples: limpeza, formolização e tamponamento) e tanatopraxia, conforme condições.</li>
          <li>Tanatopraxia indicada como disponível quando o sepultamento for superior a <strong>24 horas</strong> após o óbito, quando houver internação hospitalar, ou por determinação legal/municipal (conforme indicado).</li>
        </ul>
      </div>

      <h2 id="solicitacao" class="sect-sub" style="margin-top:14px">Solicitação da assistência</h2>
      <div class="callout">
        Para utilização da assistência, o solicitante/familiar deve acionar o serviço pelo <strong>0800 880 1900</strong>,
        informando nome do segurado falecido, CPF e demais informações solicitadas pela central.
        O acionamento deve ser feito <strong>antes</strong> de qualquer medida pessoal em relação ao funeral.
      </div>

      <div class="warn">
        <strong>Sem reembolso:</strong> o serviço deve ser acionado para prestação dos serviços cobertos. Não há posterior reembolso de despesas.
      </div>

      <h2 id="exclusoes" class="sect-sub" style="margin-top:14px">Exclusões (principais)</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li>Eventos resultantes de atos ilícitos praticados pelo segurado;</li>
          <li>Suicídio consumado ou frustrado ocorrido em prazo inferior a 2 anos do início de vigência (conforme indicado);</li>
          <li>Terrorismo, guerras, revoltas, greves, sabotagem, tumultos, perturbações de ordem pública e afins;</li>
          <li>Irradiação/transmutação nuclear, desintegração ou radioatividade;</li>
          <li>Fenômenos naturais de caráter extraordinário (inundações, terremotos, erupções, tempestades, furacões, maremotos etc.);</li>
          <li>Viagens em aviões que não sejam linha comercial (conforme indicado);</li>
          <li>Atendimento em localidades onde legislação/normas não permitam intervenção da assistência;</li>
          <li>Desaparecimento do segurado/ausência do segurado (sem buscas/provas/formalidades legais e burocráticas);</li>
          <li>Destino dos ossos após exumação, ao término do prazo de locação de jazigo;</li>
          <li>Vestuário/roupas, missa de 7º dia, xerox de documentação, refeições e bebidas;</li>
          <li>Confecção de gaveta em túmulo de terceiro, reformas em jazigo;</li>
          <li>Exumação após vencimento do período de locação do jazigo;</li>
          <li>Taxas de capela/sepultamento/cremação superiores às praticadas pelo município (diferença do solicitante);</li>
          <li>Despesas não relacionadas diretamente ao funeral sem autorização prévia da central;</li>
          <li>Reembolsos de despesas providenciadas diretamente pela família e não autorizadas pela central;</li>
          <li>Compra/confecção/manutenção/recuperação de jazigos;</li>
          <li>Aquisição de sepultura/jazigo/terreno/cova/carneiro, etc.</li>
        </ul>
      </div>

      <div class="foot">
        <span class="muted">Referência: Descritivo do Serviço • Assistência Funeral Individual • Ago/2024 (Sabemi).</span>
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
          <li><a href="#cobertos">Serviços cobertos</a></li>
          <li><a href="#sepultamento">Sepultamento</a></li>
          <li><a href="#cremacao">Cremação</a></li>
          <li><a href="#formalidades">Formalidades/Registro</a></li>
          <li><a href="#cortejo">Carro fúnebre/Velório</a></li>
          <li><a href="#urna">Urna mortuária</a></li>
          <li><a href="#traslado">Traslado</a></li>
          <li><a href="#preparacao">Preparação</a></li>
          <li><a href="#solicitacao">Solicitação</a></li>
          <li><a href="#exclusoes">Exclusões</a></li>
        </ul>
      </nav>

      <div class="mini">
        <div class="mini-title">Dica rápida</div>
        <div class="mini-text">
          Em caso de óbito, a primeira ação deve ser ligar para a central <strong>0800 880 1900</strong> e seguir as instruções.
          Evite contratar serviços por fora antes do acionamento.
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
