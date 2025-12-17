<?php
// View: Benefício • Clube de Vantagens + Desconto em Medicamentos (Set/2024)
// Arquivo: /public/docs/clube-vantagens-medicamentos.php

// 1) Sempre responder em UTF-8
ini_set('default_charset', 'UTF-8');
if (!headers_sent()) {
  header('Content-Type: text/html; charset=UTF-8');
}

// 2) Fallback: se o arquivo estiver em ANSI/Windows-1252, converte tudo para UTF-8
//    (isso elimina os "�" mesmo se o editor tiver salvado em encoding errado)
$__converter = function ($buffer) {
  // Se já for UTF-8 válido, retorna como está
  if (@preg_match('//u', $buffer)) return $buffer;

  // Tenta converter de Windows-1252 para UTF-8
  if (function_exists('mb_convert_encoding')) {
    return mb_convert_encoding($buffer, 'UTF-8', 'Windows-1252');
  }
  if (function_exists('iconv')) {
    return iconv('Windows-1252', 'UTF-8//IGNORE', $buffer);
  }
  // Sem libs, retorna original (melhor do que quebrar)
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
  <title>Clube de Vantagens + Desconto em Medicamentos • Aviv+</title>
  <meta name="robots" content="noindex,nofollow">

  <style>
    /* ===== Página do benefício (standalone) ===== */
    .benefit-doc{ --txt:#111322; --muted:#6b7280; --bd:#d0d7e2; --bg:#ffffff; --bg2:#f8fafc; }
    .benefit-doc .glass-card{
      background:var(--bg);
      border:1px solid rgba(15,23,42,.06);
      padding:18px;
      border-radius:18px;
      color:var(--txt);
      box-shadow:0 18px 40px rgba(15,23,42,.06);
    }
    .benefit-doc .glass-sub{
      background:var(--bg2);
      border:1px solid rgba(15,23,42,.08);
      padding:14px;
      border-radius:16px;
    }
    .benefit-doc .sect-title{ margin:0; font-weight:900; letter-spacing:-.02em; }
    .benefit-doc .sect-sub{ margin:0 0 8px; font-weight:800; }
    .benefit-doc .muted{ color:var(--muted); font-size:.92rem; }
    .benefit-doc .doc-kicker{ font-size:.82rem; color:#4b5563; font-weight:700; }
    .benefit-doc .doc-p{ margin:10px 0; line-height:1.55; color:#111827; }
    .benefit-doc .doc-ul{ margin:10px 0 0 18px; color:#111827; line-height:1.5; }
    .benefit-doc .doc-ul li{ margin:6px 0; }

    /* header */
    .doc-head{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap:14px;
    }
    .doc-cta{ min-width:240px; text-align:right; }
    @media (max-width:900px){
      .doc-head{ flex-direction:column; }
      .doc-cta{ text-align:left; min-width:0; }
    }

    /* grid */
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

    /* buttons */
    .benefit-doc .btn{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:8px;
      border-radius:12px;
      padding:10px 14px;
      border:1px solid var(--bd);
      background:#fff;
      color:var(--txt);
      font-weight:800;
      text-decoration:none;
    }
    .benefit-doc .btn:hover{ box-shadow:0 8px 20px rgba(15,23,42,.10); }
    .benefit-doc .btn-primary{
      background:#2563eb;
      border-color:#2563eb;
      color:#fff;
    }

    /* info boxes */
    .info-grid{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
      margin-top:12px;
    }
    @media (max-width:720px){ .info-grid{ grid-template-columns:1fr; } }
    .info-box{
      border:1px solid rgba(15,23,42,.10);
      border-radius:16px;
      padding:12px;
      background:#fff;
    }
    .info-box h3{ margin:0 0 6px; font-size:1.02rem; font-weight:900; }
    .info-box p{ margin:0; color:#111827; line-height:1.5; font-size:.95rem; }

    /* callout */
    .callout{
      margin-top:12px;
      border:1px solid rgba(37,99,235,.25);
      background:rgba(37,99,235,.06);
      border-radius:16px;
      padding:12px;
    }
    .callout-title{ font-weight:900; margin-bottom:4px; }
    .callout-text{ color:#111827; }

    /* how-to */
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
    }
    .howto h3{ margin:0 0 8px; font-weight:900; }
    .howto ol{ margin:0 0 10px 18px; padding:0; color:#111827; line-height:1.55; }
    .link{ font-weight:900; color:#2563eb; text-decoration:none; }
    .link:hover{ text-decoration:underline; }

    /* warning */
    .warn{
      margin-top:10px;
      border:1px solid rgba(245,158,11,.35);
      background:rgba(245,158,11,.10);
      border-radius:14px;
      padding:10px;
      color:#111827;
      line-height:1.5;
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
      font-weight:800;
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

    .doc-foot{
      margin-top:14px;
      padding-top:10px;
      border-top:1px solid rgba(15,23,42,.08);
    }

    /* fallback caso seu projeto não tenha .container */
    .container{ width:min(92vw, 1120px); margin-inline:auto; }
  </style>
</head>

<body>
<section class="container benefit-doc" style="margin-top:18px">
  <div class="glass-card">
    <div class="doc-head">
      <div>
        <div class="doc-kicker">Sabemi • Descritivo do serviço • Set/2024</div>
        <h1 class="sect-title">Assistências: Clube de Vantagens + Desconto em Medicamentos</h1>
        
      </div>

      <div class="doc-cta">
        <a class="btn btn-primary" href="https://clube.yousafer.com.br/login" target="_blank" rel="noopener">
          Acessar plataforma
        </a>
        <div class="muted" style="font-size:.82rem;margin-top:8px">
          Acesso via login no portal do clube.
        </div>
      </div>
    </div>
  </div>

  <div class="terms-grid" style="margin-top:12px">
    <main class="glass-card doc-body">
      <h2 class="sect-sub">Visão geral</h2>
      <p class="doc-p">
        O clube reúne uma ampla rede de descontos em categorias como farmácias, alimentos e bebidas,
        lazer, bem-estar e saúde, educação, produtos, moda, viagens e turismo, casa/decoração e outros.
        A proposta é permitir economia em compras, serviços, experiências e cursos, conforme ofertas
        disponíveis no portal.
      </p>

      <div class="info-grid">
        <div class="info-box">
          <h3>Dinheiro de volta (cashback)</h3>
          <p>
            Em compras feitas em lojas online participantes, pode haver retorno de uma porcentagem do valor.
            O prazo de crédito pode variar (ex.: até 60 dias), dependendo das regras da loja/oferta.
          </p>
        </div>

        <div class="info-box">
          <h3>Acesso fácil</h3>
          <p>
            O uso é feito com login e senha, podendo ser acessado de qualquer lugar em qualquer dispositivo.
          </p>
        </div>

        <div class="info-box">
          <h3>Primeiro acesso</h3>
          <p>
            Em regra, o primeiro login é feito informando o CPF e utilizando os primeiros dígitos do CPF
            como senha inicial (padrão de acesso indicado no material do benefício).
          </p>
        </div>

        <div class="info-box">
          <h3>Desconto em medicamentos</h3>
          <p>
            Além do clube, existe o serviço de desconto para medicamentos classificados como "medicamento"
            pela Anvisa, válido em farmácias credenciadas consultáveis no portal.
          </p>
        </div>
      </div>

      <div class="callout">
        <div class="callout-title">Carência</div>
        <div class="callout-text">
          Prazo informado no material: <strong>2 dias úteis</strong> após a contratação/ativação.
        </div>
      </div>

      <h2 class="sect-sub" style="margin-top:14px">Como utilizar</h2>

      <div class="howto-grid">
        <section class="howto">
          <h3>Clube de Vantagens</h3>
          <ol>
            <li>Acesse o portal do clube e faça login.</li>
            <li>Encontre a oferta desejada por marca, palavra-chave ou categoria.</li>
            <li>Abra a oferta para ver regras, percentual/condição de desconto e instruções.</li>
            <li>Finalize no site do parceiro seguindo o fluxo indicado e pague pelos meios aceitos pelo parceiro.</li>
          </ol>
          <a class="link" href="https://clube.yousafer.com.br/login" target="_blank" rel="noopener">
            Ir para o portal do Clube
          </a>
        </section>

        <section class="howto">
          <h3>Desconto em Medicamentos</h3>
          <ol>
            <li>No portal, localize uma farmácia credenciada (endereço/unidade) na sua cidade.</li>
            <li>Na farmácia, informe seus dados ao atendente conforme solicitado (ex.: CPF e dados básicos), pois o vínculo do desconto segue a regra do convênio/operador indicado.</li>
            <li>Solicite o desconto usando o CPF e conclua o pagamento conforme as condições da drogaria.</li>
          </ol>
          <a class="link" href="https://clube.yousafer.com.br/login" target="_blank" rel="noopener">
            Ver credenciadas e regras
          </a>
        </section>
      </div>

      <h2 class="sect-sub" style="margin-top:14px">Condições e limitações</h2>
      <div class="glass-sub">
        <p class="doc-p" style="margin-top:0">
          O material do benefício informa que o desconto em medicamentos pode não ser assegurado em algumas situações, por exemplo:
        </p>
        <ul class="doc-ul">
          <li>Quando o preço/desconto já foi negociado diretamente no balcão (fora do registro do sistema do convênio).</li>
          <li>Quando houver promessas de cobertura de preço de concorrentes por parte de lojas/parceiros.</li>
          <li>Quando o desconto estiver atrelado a programas de fidelidade específicos (ex.: indústria farmacêutica).</li>
          <li>Quando os preços/condições forem do programa Farmácia Popular (se aplicável).</li>
          <li>Quando houver promoções com bonificação (ex.: "leve 2, pague 1") ou descontos praticados pontualmente no PDV.</li>
        </ul>

        <div class="warn">
          <strong>Importante:</strong> o material também indica que o desconto pode não ser aplicável a itens como:
          vitaminas, produtos hospitalares, itens oncológicos orais, nutrição, cosméticos, perfumaria,
          preservativos, soluções hospitalares e outros itens/drogas orais voltados a patologias raras e de alta complexidade.
        </div>
      </div>

      <div class="doc-foot">
        <span class="muted">Referência: Set/2024 • Sabemi</span>
      </div>
    </main>

    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px">Atalhos</h3>
      <nav aria-label="Atalhos do benefício">
        <ul class="toc">
          <li><a href="https://clube.yousafer.com.br/login" target="_blank" rel="noopener">Acessar portal (login)</a></li>
          <li><a href="#como-usar" onclick="document.querySelector('.howto-grid')?.scrollIntoView({behavior:'smooth'});return false;">Como utilizar</a></li>
          <li><a href="#regras" onclick="document.querySelector('.glass-sub')?.scrollIntoView({behavior:'smooth'});return false;">Condições e limitações</a></li>
        </ul>
      </nav>

      <div class="mini">
        <div class="mini-title">Dica rápida</div>
        <div class="mini-text">
          Se for o primeiro acesso, tente entrar com CPF e a senha inicial padrão descrita no benefício.
          Caso não funcione, utilize o fluxo de recuperação/ajuda do próprio portal.
        </div>
      </div>
    </aside>
  </div>
</section>
</body>
</html>
<?php ob_end_flush(); ?>
