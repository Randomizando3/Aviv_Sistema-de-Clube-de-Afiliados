<?php
// View: Benefício • Assistência Medicamentos Genéricos Gratuitos (Jul/2024) — Sabemi
// Arquivo: /public/docs/medicamentos-genericos-gratuitos.php

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
  <title>Medicamentos Genéricos Gratuitos • Sabemi</title>
  <meta name="robots" content="noindex,nofollow">

  <style>
    :root{
      --txt:#111322;
      --muted:#6b7280;
      --bd:#d0d7e2;
      --bg:#ffffff;
      --bg2:#f8fafc;
      --brand:#2563eb;
      --ok:#16a34a;
      --warn:#f59e0b;
      --info:#0ea5e9;
    }
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Helvetica Neue", sans-serif;
      background:#fff;
      color:var(--txt);
    }
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
    .btn-primary{ background:var(--ok); border-color:var(--ok); color:#fff; }
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

    /* info grid */
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

    /* steps */
    .steps{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
      margin-top:10px;
    }
    @media (max-width:820px){ .steps{ grid-template-columns:1fr; } }
    .step{
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      border-radius:16px;
      padding:12px;
    }
    .step h3{ margin:0 0 8px; font-weight:950; }
    .step ol{ margin:0 0 10px 18px; padding:0; color:#111827; line-height:1.65; }

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

    code.k{
      display:inline-block;
      padding:2px 8px;
      border-radius:999px;
      border:1px solid rgba(15,23,42,.10);
      background:#fff;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
      font-size:.9rem;
    }
  </style>
</head>

<body>
<section class="container benefit-doc" style="margin-top:18px">

  <div class="glass-card">
    <div class="doc-head">
      <div>
        <div class="kicker">Sabemi • Descritivo do serviço • Jul/2024</div>
        <h1 class="sect-title">Assistência Medicamentos Genéricos Gratuitos</h1>

        <p class="muted" style="margin:8px 0 0">
          Acionamento via WhatsApp: <strong>(51) 2042 0536</strong>.
        </p>

        <div class="chips" aria-label="Destaques do serviço">
          <span class="chip">Atendimento: seg–sex • 8h às 18h</span>
          <span class="chip">Âmbito nacional</span>
          <span class="chip">1 solicitação/mês</span>
          <span class="chip">Até R$ 150 por solicitação</span>
          <span class="chip">Carência: 3 meses</span>
        </div>
      </div>

      <div class="doc-cta">
        <a class="btn btn-primary" href="https://wa.me/555120420536" target="_blank" rel="noopener">
          Chamar no WhatsApp
        </a>

        <a class="btn btn-outline" style="margin-top:10px" href="#como-usar">
          Ver como solicitar
        </a>

        <div class="muted" style="font-size:.82rem;margin-top:8px">
          Horário: seg. a sex., 8h–18h (exceto feriados).
        </div>
      </div>
    </div>
  </div>

  <div class="terms-grid" style="margin-top:12px">

    <!-- Coluna principal -->
    <main class="glass-card doc-body">

      <h2 id="visao" class="sect-sub">Visão geral</h2>
      <p class="p">
        O serviço oferece <strong>subsídio (reembolso)</strong> para aquisição de <strong>medicamentos genéricos</strong>,
        desde que exista <strong>prescrição/receita médica</strong> em nome do segurado e que o pagamento do seguro esteja em dia.
      </p>

      <div class="callout">
        <strong>Acionamento:</strong> WhatsApp <code class="k">(51) 2042 0536</code><br>
        <strong>Reembolso:</strong> até <strong>15 dias úteis</strong> após o envio completo da documentação.
      </div>

      <h2 id="condicoes" class="sect-sub" style="margin-top:14px">Condições gerais</h2>

      <div class="info-grid">
        <div class="info">
          <h3>Horário de atendimento</h3>
          <p>De segunda a sexta-feira, das <strong>8h às 18h</strong>, exceto feriados.</p>
        </div>
        <div class="info">
          <h3>Âmbito territorial</h3>
          <p><strong>Abrangência nacional</strong>.</p>
        </div>
        <div class="info">
          <h3>Limite de utilizações</h3>
          <p><strong>01 (uma)</strong> solicitação por mês e até <strong>06 (seis)</strong> utilizações por ano de vigência.</p>
        </div>
        <div class="info">
          <h3>Limite monetário</h3>
          <p>Até <strong>R$ 150,00</strong> por solicitação.</p>
        </div>
      </div>

      <div class="warn">
        <strong>Carência:</strong> para início da utilização há carência de <strong>3 (três) meses</strong> a partir do início da vigência do seguro.
      </div>

      <h2 id="como-usar" class="sect-sub" style="margin-top:14px">Como utilizar (passo a passo)</h2>

      <div class="steps">
        <section class="step">
          <h3>1) Compre o medicamento</h3>
          <ol>
            <li>Compre o <strong>medicamento genérico</strong> indicado pelo médico.</li>
            <li>Guarde a <strong>Nota Fiscal</strong> com detalhamento do medicamento.</li>
            <li>Garanta que a receita/prescrição esteja em nome do segurado e com CRM do médico.</li>
          </ol>
        </section>

        <section class="step">
          <h3>2) Solicite o reembolso</h3>
          <ol>
            <li>Entre em contato pelo WhatsApp <code class="k">(51) 2042 0536</code>.</li>
            <li>Envie a documentação exigida (lista abaixo).</li>
            <li>Acompanhe a análise; o reembolso pode ocorrer em até <strong>15 dias úteis</strong> após envio completo.</li>
          </ol>
        </section>
      </div>

      <h2 id="documentos" class="sect-sub" style="margin-top:14px">Documentos necessários</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li><strong>Receita médica</strong> (datada, assinada e carimbada pelo médico com <strong>CRM</strong>);</li>
          <li><strong>Nota Fiscal</strong> de compra (com <strong>detalhamento</strong> do medicamento);</li>
          <li><strong>Telefone para contato</strong>;</li>
          <li><strong>Documento de identificação</strong> do segurado com foto e <strong>CPF</strong>;</li>
          <li><strong>Chave Pix</strong> (para recebimento do valor reembolsado).</li>
        </ul>
      </div>

      <div class="callout">
        <strong>Prazo para solicitar:</strong> até <strong>30 (trinta) dias</strong> após o atendimento médico (data identificada na Receita Médica).
      </div>

      <h2 id="reembolso" class="sect-sub" style="margin-top:14px">Prazo de reembolso</h2>
      <div class="glass-sub">
        <p class="p" style="margin-top:0">
          O prazo para reembolso pode ser de até <strong>15 (quinze) dias úteis</strong> após o envio de toda a documentação necessária.
          O reembolso é realizado em conta corrente indicada pelo segurado titular da apólice.
        </p>
      </div>

      <h2 id="exclusoes" class="sect-sub" style="margin-top:14px">Exclusões gerais</h2>
      <div class="glass-sub">
        <ul class="ul" style="margin-top:0">
          <li>Notas fiscais <strong>sem detalhamento</strong> de medicamento genérico não serão elegíveis;</li>
          <li>Receitas médicas <strong>sem assinatura</strong>, <strong>data</strong> da consulta e <strong>carimbo/CRM</strong> não serão elegíveis;</li>
          <li>Valor de <strong>R$ 150,00</strong> <strong>não é cumulativo</strong> de um mês para o outro;</li>
          <li>Solicitações de reembolso <strong>após 30 dias</strong> do atendimento médico;</li>
          <li>Mais de <strong>01 (uma)</strong> utilização do serviço por mês;</li>
          <li>Medicamentos de classificação comercial <strong>OTC (venda livre)</strong>;</li>
          <li><strong>Medicamentos contínuos</strong>.</li>
        </ul>

        <div class="warn">
          <strong>Atenção:</strong> solicitações analisadas que não atendam às premissas do regulamento não terão direito ao reembolso.
        </div>
      </div>

      <div class="foot">
        <span class="muted">Referência: Descritivo do Serviço • Assistência Medicamentos Genéricos Gratuitos • Jul/2024 (Sabemi).</span>
      </div>

    </main>

    <!-- Coluna lateral -->
    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px">Atalhos</h3>
      <nav aria-label="Atalhos do benefício">
        <ul class="toc">
          <li><a href="https://wa.me/555120420536" target="_blank" rel="noopener">Chamar no WhatsApp</a></li>
          <li><a href="#visao">Visão geral</a></li>
          <li><a href="#condicoes">Condições gerais</a></li>
          <li><a href="#como-usar">Como utilizar</a></li>
          <li><a href="#documentos">Documentos</a></li>
          <li><a href="#reembolso">Prazo de reembolso</a></li>
          <li><a href="#exclusoes">Exclusões</a></li>
        </ul>
      </nav>

      <div class="mini">
        <div class="mini-title">Dica rápida</div>
        <div class="mini-text">
          Antes de solicitar, confira se a Nota Fiscal tem o <strong>detalhamento</strong> do medicamento e se a receita tem
          <strong>assinatura</strong>, <strong>data</strong> e <strong>CRM</strong>. Isso evita reprovação na análise.
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
