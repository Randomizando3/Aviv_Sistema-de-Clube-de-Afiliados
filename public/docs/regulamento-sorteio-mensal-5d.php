<?php
// View: Benefício • Regulamento do Sorteio Mensal (Séries com 100 mil títulos) — Sabemi
// Arquivo: /public/docs/regulamento-sorteio-mensal-5d.php
// Fonte: PDF “Regulamento de Sorteio Mensal 5D JAN_24.pdf”

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
  <title>Regulamento do Sorteio Mensal • Sabemi</title>
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
    body{ margin:0; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Helvetica Neue", sans-serif; background:#fff; color:var(--txt); }
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
    .doc-cta{ min-width:360px; text-align:right; }
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

    /* chips */
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

    /* blocks */
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

    /* example */
    .example{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:10px;
      margin-top:10px;
    }
    @media (max-width:820px){ .example{ grid-template-columns:1fr; } }
    .mono{
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size:.95rem;
    }
    .badge{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:10px 12px;
      border-radius:14px;
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      font-weight:950;
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
        <div class="kicker">Seguradora Sabemi • Regulamento • Fevereiro/2024</div>
        <h1 class="sect-title">Regulamento do Sorteio Mensal</h1>

        <p class="muted" style="margin:8px 0 0">
          Sorteios apurados com base nas extrações da <strong>Loteria Federal do Brasil</strong>.
        </p>

        <div class="chips" aria-label="Destaques">
          <span class="chip">1 sorteio mensal</span>
          <span class="chip">Último sábado do mês</span>
          <span class="chip">Território nacional</span>
          <span class="chip">IR: 25% (quando aplicável)</span>
        </div>
      </div>

      <div class="doc-cta">
        <a class="btn btn-primary" href="https://loterias.caixa.gov.br/Paginas/Federal.aspx" target="_blank" rel="noopener">
          Ver resultados da Loteria Federal
        </a>
        <a class="btn btn-outline" style="margin-top:10px" href="#como-funciona">
          Como funciona o número sorteado
        </a>
        <div class="muted" style="font-size:.82rem;margin-top:8px">
          Regulamento também disponível em <strong>www.sabemi.com.br</strong>.
        </div>
      </div>
    </div>
  </div>

  <div class="terms-grid" style="margin-top:12px">

    <!-- Coluna principal -->
    <main class="glass-card doc-body">

      <h2 id="visao" class="sect-sub">Visão geral</h2>
      <p class="p">
        Regulamento válido para promoções vinculadas aos produtos de Capitalização registrados pela KOVR junto à SUSEP,
        sob os números <strong>15414.900581/2019-13</strong> ou <strong>15414.900397/2019-65</strong>.
      </p>

      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          A <strong>Sabemi Seguradora S.A.</strong> (CNPJ <strong>87.163.234/0001-38</strong>) é proprietária de Títulos de Capitalização,
          da modalidade incentivo, emitidos e administrados pela <strong>KOVR CAPITALIZAÇÃO S.A.</strong> (CNPJ <strong>93.202.448/0001-79</strong>),
          aprovados pela SUSEP conforme os processos citados acima.
        </p>
      </div>

      <h2 id="participacao" class="sect-sub" style="margin-top:14px">Condição para participar</h2>
      <div class="callout">
        Ao aderir ao seguro e manter em dia o pagamento do respectivo prêmio, você recebe o direito de participar de
        <strong>1 (um) sorteio mensal</strong> no <strong>último sábado do mês</strong>, no valor bruto indicado no Certificado de Seguro,
        com incidência de <strong>25% de Imposto de Renda</strong>, conforme legislação vigente.
      </div>

      <h2 id="apuracao" class="sect-sub" style="margin-top:14px">Como os sorteios são apurados</h2>
      <p class="p">
        Os sorteios serão apurados com base nas extrações da <strong>Loteria Federal do Brasil</strong>. A participação se inicia
        no mês subsequente ao início de vigência do seguro, e a promoção permanece vigente enquanto o pagamento estiver em dia.
      </p>
      <p class="p">
        Os resultados podem ser acompanhados no site da CAIXA e em Casas Lotéricas. Caso não ocorra extração na data prevista,
        o sorteio correspondente é adiado para a primeira extração realizada até o dia que anteceder ao sábado seguinte.
      </p>

      <h2 id="como-funciona" class="sect-sub" style="margin-top:14px">Como é formado o número sorteado (5D)</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          Será contemplado o Título vigente na data do sorteio cujo número de sorte coincida, da esquerda para a direita,
          com as <strong>unidades simples</strong> dos <strong>5 (cinco) primeiros prêmios</strong> da Loteria Federal,
          lidos de cima para baixo.
        </p>

        <div class="example">
          <div class="info">
            <h3>Exemplo (prêmios da Loteria Federal)</h3>
            <div class="mono" style="line-height:1.9">
              1º Prêmio = 10105<br>
              2º Prêmio = 11328<br>
              3º Prêmio = 05271<br>
              4º Prêmio = 74200<br>
              5º Prêmio = 49849
            </div>
          </div>

          <div class="info">
            <h3>Unidades simples (último dígito)</h3>
            <div class="mono" style="line-height:1.9">
              1º: <strong>5</strong><br>
              2º: <strong>8</strong><br>
              3º: <strong>1</strong><br>
              4º: <strong>0</strong><br>
              5º: <strong>9</strong>
            </div>
            <div style="margin-top:10px">
              <span class="badge">Exemplo de Nº sorteado: <span class="mono">58.109</span></span>
            </div>
          </div>
        </div>
      </div>

      <h2 id="comunicacao" class="sect-sub" style="margin-top:14px">Comunicação e recebimento do prêmio</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          O contemplado será avisado por ligação telefônica, por escrito ou por outro meio eletrônico disponibilizado para contato.
          O recebimento da premiação depende de estar rigorosamente em dia com o pagamento dos prêmios do seguro.
        </p>
        <p class="p">
          Para recebimento, o contemplado deverá apresentar:
        </p>
        <ul class="ul">
          <li>Cópia de <strong>RG</strong> e <strong>CPF</strong> válidos;</li>
          <li><strong>Comprovante de residência</strong> atualizado (expedido, no máximo, a 180 dias da apresentação);</li>
          <li>Informar <strong>profissão</strong> e <strong>renda</strong>;</li>
          <li>Assinar termo de recebimento e quitação do valor do prêmio.</li>
        </ul>
      </div>

      <h2 id="observacoes" class="sect-sub" style="margin-top:14px">Observações importantes</h2>
      <div class="warn">
        A aprovação do Título de Capitalização pela SUSEP não implica, por parte da Autarquia, incentivo ou recomendação à sua aquisição,
        representando exclusivamente sua adequação às normas em vigor.
      </div>

      <div class="glass-sub" style="margin-top:10px">
        <p class="p" style="margin-top:0">
          Entende-se por <strong>“Prêmio de Seguro”</strong> o valor pago pelo Segurado à Seguradora, e por
          <strong>“Premiação/Prêmio de Sorteio”</strong> o valor a ser pago ao Segurado contemplado pelos números sorteados pela Loteria Federal.
        </p>
      </div>

      <div class="foot">
        <span class="muted">Referência: Regulamento do Sorteio Mensal • Sabemi • Fevereiro/2024.</span>
      </div>

    </main>

    <!-- Coluna lateral -->
    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px">Atalhos</h3>
      <nav aria-label="Atalhos do regulamento">
        <ul class="toc">
          <li><a href="https://loterias.caixa.gov.br/Paginas/Federal.aspx" target="_blank" rel="noopener">Resultados Loteria Federal</a></li>
          <li><a href="#visao">Visão geral</a></li>
          <li><a href="#participacao">Condição para participar</a></li>
          <li><a href="#apuracao">Apuração</a></li>
          <li><a href="#como-funciona">Como funciona (5D)</a></li>
          <li><a href="#comunicacao">Recebimento do prêmio</a></li>
          <li><a href="#observacoes">Observações</a></li>
        </ul>
      </nav>

      <div class="mini">
        <div class="mini-title">Dica rápida</div>
        <div class="mini-text">
          Para validar o “5D”, pegue o <strong>último dígito</strong> de cada um dos 5 primeiros prêmios da Loteria Federal
          (1º ao 5º) e forme o número na ordem.
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
