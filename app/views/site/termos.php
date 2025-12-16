<?php // View: Regulamento do Associado — estilo igual ao "Planos" (glass-card clean, sem header/footer locais) ?>

<section class="container termos-page" style="margin-top:18px">
  <section class="admin-main">
    <!-- Cabeçalho -->
    <div class="glass-card">
      <h1 class="sect-title">Regulamento Oficial do Associado – AVIV+ Clube de Benefícios</h1>
      <p class="muted" style="margin:0">Versão 1.0 – 2025</p>
      <p class="muted" style="margin:6px 0 0">Última atualização: <?= date('d/m/Y') ?></p>
    </div>

    <!-- Layout (desktop: 2 colunas | mobile: 1 coluna com sumário sticky/top) -->
    <div class="terms-grid" style="margin-top:12px">
      <!-- Sumário -->
      <aside class="glass-card toc-card" aria-label="Sumário">
        <h2 class="sect-sub" style="margin:0 0 10px">Sumário</h2>

        <nav aria-label="Sumário do regulamento">
          <ul class="toc">
            <li><a href="#t1">1. Definições</a></li>
            <li><a href="#t2">2. Natureza do Produto</a></li>
            <li><a href="#t3">3. Adesão e Vigência</a></li>
            <li><a href="#t4">4. Mensalidade e Política de Pagamentos</a></li>
            <li><a href="#t5">5. Direitos do Associado</a></li>
            <li><a href="#t6">6. Deveres do Associado</a></li>
            <li><a href="#t7">7. Regras de Utilização dos Serviços</a></li>
            <li><a href="#t8">8. Sorteio Mensal</a></li>
            <li><a href="#t9">9. Exclusões Gerais</a></li>
            <li><a href="#t10">10. Cancelamento</a></li>
            <li><a href="#t11">11. Proteção de Dados – LGPD</a></li>
            <li><a href="#t12">12. Disposições Legais</a></li>
            <li><a href="#t13">13. Aceite Digital</a></li>
            <li><a href="#t14">14. Vigência</a></li>
          </ul>
        </nav>

        <div class="muted" style="margin-top:12px">
          Dica: use o sumário para navegar rapidamente.
        </div>
      </aside>

      <!-- Conteúdo -->
      <article class="glass-card legal-card" aria-label="Conteúdo do regulamento">
        <section class="t-sec">
          <p>
            Este Regulamento estabelece as regras gerais de adesão, utilização, direitos, deveres e obrigações dos ASSOCIADOS AVIV+,
            bem como as disposições legais e operacionais do Clube de Benefícios. Ao aderir ao plano, o associado declara estar ciente,
            concordar e aceitar integralmente os termos abaixo.
          </p>
        </section>

        <section id="t1" class="t-sec">
          <h2 class="t-h">1. Definições</h2>
          <p>Para fins deste Regulamento, entende-se:</p>
          <ul class="t-list">
            <li><strong>1.1. AVIV+</strong> – Empresa fornecedora do Clube de Benefícios, responsável pela gestão administrativa, atendimento, comunicação e organização dos serviços disponibilizados aos associados.</li>
            <li><strong>1.2. ASSOCIADO</strong> – Pessoa física ou jurídica que realiza adesão a um dos produtos AVIV+, mediante pagamento recorrente.</li>
            <li><strong>1.3. PLANO INDIVIDUAL</strong> – Modalidade destinada a um único titular.</li>
            <li><strong>1.4. PLANO FAMILIAR</strong> – Modalidade destinada ao titular + até 3 dependentes, conforme regras do produto.</li>
            <li><strong>1.5. PLANO EMPRESARIAL</strong> – Modalidade vinculada ao CNPJ contratante, conforme número de vidas contratadas.</li>
            <li><strong>1.6. BENEFÍCIOS</strong> – Conjunto de vantagens, serviços e assistências oferecidas pela AVIV+ por meio de parcerias.</li>
            <li><strong>1.7. PARCEIROS</strong> – Empresas terceiras prestadoras dos serviços descritos no portfólio (telemedicina, consultas, assistência etc.).</li>
            <li><strong>1.8. SORTEIOS</strong> – Ação promocional mensal, regulada em documento próprio.</li>
            <li><strong>1.9. MENSALIDADE</strong> – Valor pago mensalmente pelo associado para manutenção ativa do plano e acesso aos benefícios.</li>
          </ul>
        </section>

        <section id="t2" class="t-sec">
          <h2 class="t-h">2. Natureza do Produto</h2>
          <ul class="t-list">
            <li><strong>2.1.</strong> A AVIV+ não é plano de saúde, seguro saúde ou operadora de saúde.</li>
            <li><strong>2.2.</strong> Os produtos ofertados tratam-se de: clube de vantagens, assistências, telemedicina, descontos e serviços complementares para qualidade de vida.</li>
            <li><strong>2.3.</strong> Os serviços médicos e assistenciais são prestados por empresas parceiras, devidamente credenciadas, as quais possuem regulamentos próprios.</li>
          </ul>
        </section>

        <section id="t3" class="t-sec">
          <h2 class="t-h">3. Adesão e Vigência</h2>
          <ul class="t-list">
            <li><strong>3.1.</strong> A adesão ocorre mediante:
              <ul class="t-list" style="margin-top:6px">
                <li>a) preenchimento de cadastro;</li>
                <li>b) aceite eletrônico;</li>
                <li>c) concordância com este Regulamento;</li>
                <li>d) pagamento da primeira mensalidade.</li>
              </ul>
            </li>
            <li><strong>3.2.</strong> A vigência é mensal e renovada automaticamente mediante manutenção do pagamento.</li>
            <li><strong>3.3.</strong> O acesso aos serviços inicia-se após confirmação do pagamento, respeitando carências específicas quando previstas no produto.</li>
          </ul>
        </section>

        <section id="t4" class="t-sec">
          <h2 class="t-h">4. Mensalidade e Política de Pagamentos</h2>
          <ul class="t-list">
            <li><strong>4.1.</strong> A mensalidade deve ser paga até a data de vencimento.</li>
            <li><strong>4.2.</strong> O não pagamento gera automaticamente a suspensão do acesso aos benefícios após 3 dias do vencimento.</li>
            <li><strong>4.3.</strong> Após 30 dias de inadimplência, o contrato é cancelado automaticamente.</li>
            <li><strong>4.4.</strong> A reativação só será possível mediante regularização total dos pagamentos pendentes.</li>
            <li><strong>4.5.</strong> Em caso de cancelamento, o associado não terá direito a prêmios, sorteios, assistências ou qualquer benefício após o desligamento.</li>
            <li><strong>4.6.</strong> A AVIV+ poderá reajustar o valor da mensalidade anualmente, com aviso prévio mínimo de 30 dias.</li>
          </ul>
        </section>

        <section id="t5" class="t-sec">
          <h2 class="t-h">5. Direitos do Associado</h2>
          <p>O associado tem direito a:</p>
          <ul class="t-list">
            <li>a) Acesso aos benefícios contratados conforme plano adquirido.</li>
            <li>b) Atendimento por nossos canais oficiais.</li>
            <li>c) Utilização dos serviços dentro das regras próprias de cada produto.</li>
            <li>d) Participação no sorteio mensal (exceto inadimplentes).</li>
            <li>e) Solicitar cancelamento a qualquer momento.</li>
            <li>f) Receber atualizações e melhorias nos produtos, quando aplicadas.</li>
          </ul>
        </section>

        <section id="t6" class="t-sec">
          <h2 class="t-h">6. Deveres do Associado</h2>
          <p>O associado se compromete a:</p>
          <ul class="t-list">
            <li>a) Manter seus dados atualizados.</li>
            <li>b) Efetuar o pagamento das mensalidades em dia.</li>
            <li>c) Utilizar os benefícios de forma ética e responsável.</li>
            <li>d) Respeitar regulamentos internos de parceiros e prestadores.</li>
            <li>e) Não fraudar, compartilhar indevidamente ou ceder o uso de benefícios a terceiros não autorizados.</li>
            <li>f) Informar imediatamente qualquer inconsistência no cadastro ou nas cobranças.</li>
          </ul>
        </section>

        <section id="t7" class="t-sec">
          <h2 class="t-h">7. Regras de Utilização dos Serviços</h2>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">7.1. Telemedicina</h3>
          <ul class="t-list">
            <li>Ilimitada conforme regras do parceiro.</li>
            <li>Requer acesso à plataforma credenciada.</li>
            <li>Não substitui atendimento de urgência e emergência.</li>
          </ul>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">7.2. Consultas presenciais</h3>
          <ul class="t-list">
            <li>Coparticipação de R$ 50,00.</li>
            <li>Agendamento obrigatório via central indicada.</li>
            <li>Sujeito à disponibilidade da rede credenciada.</li>
          </ul>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">7.3. Exames com desconto</h3>
          <ul class="t-list">
            <li>Descontos praticados pela rede conveniada.</li>
            <li>Preços variam conforme prestador.</li>
          </ul>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">7.4. Assistência residencial</h3>
          <ul class="t-list">
            <li>Limites definidos no regulamento do parceiro.</li>
            <li>Serviços como eletricista, encanador, chaveiro e outros.</li>
          </ul>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">7.5. Assistência funeral</h3>
          <ul class="t-list">
            <li>Cobertura conforme apólice e regulamento do parceiro.</li>
            <li>Necessária comunicação imediata em caso de uso.</li>
          </ul>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">7.6. Medicamentos genéricos</h3>
          <ul class="t-list">
            <li>Até 6 utilizações/ano.</li>
            <li>Reembolso máximo de R$ 150,00 por uso.</li>
            <li>Necessário envio de receita médica, nota fiscal e formulário.</li>
          </ul>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">7.7. Clube de vantagens</h3>
          <ul class="t-list">
            <li>Descontos diversos de acordo com empresas parceiras.</li>
            <li>Parcerias podem ser alteradas sem aviso prévio.</li>
          </ul>
        </section>

        <section id="t8" class="t-sec">
          <h2 class="t-h">8. Sorteio Mensal</h2>
          <p>O associado declara ciência de que:</p>
          <ul class="t-list">
            <li>a) A participação é automática para adimplentes.</li>
            <li>b) O regulamento de sorteio é documento separado.</li>
            <li>c) A Aviv+ pode alterar itens, valores e regras a qualquer tempo.</li>
          </ul>
        </section>

        <section id="t9" class="t-sec">
          <h2 class="t-h">9. Exclusões Gerais</h2>
          <p>A Aviv+ não se responsabiliza por:</p>
          <ul class="t-list">
            <li>a) atendimentos médicos, diagnósticos, laudos ou tratamentos;</li>
            <li>b) condutas dos profissionais parceiros;</li>
            <li>c) disponibilidade individual dos prestadores;</li>
            <li>d) dados incorretos fornecidos pelo associado;</li>
            <li>e) falhas pontuais em sistemas de parceiros;</li>
            <li>f) eventos de força maior.</li>
          </ul>
        </section>

        <section id="t10" class="t-sec">
          <h2 class="t-h">10. Cancelamento</h2>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">10.1. Solicitação pelo associado</h3>
          <ul class="t-list">
            <li>Pode ser feita a qualquer momento por nossos canais.</li>
            <li>O cancelamento é efetivado em até 48h.</li>
          </ul>

          <h3 class="t-h" style="font-size:1.02rem;margin-top:10px">10.2. Cancelamento pela empresa</h3>
          <ul class="t-list">
            <li>Por inadimplência superior a 30 dias.</li>
            <li>Por fraude, má-fé ou uso indevido do produto.</li>
            <li>Por uso que viole este regulamento.</li>
          </ul>
        </section>

        <section id="t11" class="t-sec">
          <h2 class="t-h">11. Proteção de Dados – LGPD</h2>
          <p>A Aviv+ cumpre rigorosamente a Lei Geral de Proteção de Dados, garantindo:</p>
          <ul class="t-list">
            <li>a) tratamento seguro das informações;</li>
            <li>b) uso exclusivo para fins operacionais do serviço;</li>
            <li>c) confidencialidade dos dados;</li>
            <li>d) possibilidade de solicitar remoção ou correção;</li>
            <li>e) não compartilhamento sem autorização, exceto quando necessário para execução dos serviços.</li>
          </ul>
        </section>

        <section id="t12" class="t-sec">
          <h2 class="t-h">12. Disposições Legais</h2>
          <ul class="t-list">
            <li><strong>12.1.</strong> Este produto não caracteriza plano de saúde.</li>
            <li><strong>12.2.</strong> Os valores pagos não são reembolsáveis.</li>
            <li><strong>12.3.</strong> A Aviv+ pode alterar este regulamento mediante aviso prévio.</li>
            <li><strong>12.4.</strong> A adesão implica concordância com todos os termos.</li>
            <li><strong>12.5.</strong> O foro para dirimir questões será o da comarca de Niterói – RJ.</li>
          </ul>
        </section>

        <section id="t13" class="t-sec">
          <h2 class="t-h">13. Aceite Digital</h2>
          <p>Ao marcar o termo de aceite, concluir o cadastro ou efetuar o pagamento da primeira mensalidade, o associado declara:</p>
          <ul class="t-list">
            <li>✔ que leu,</li>
            <li>✔ compreendeu,</li>
            <li>✔ concorda,</li>
            <li>✔ aceita e</li>
            <li>✔ está plenamente ciente de todo o conteúdo deste Regulamento.</li>
          </ul>
        </section>

        <section id="t14" class="t-sec">
          <h2 class="t-h">14. Vigência</h2>
          <p>Este regulamento entra em vigor na data de sua publicação.</p>
        </section>
      </article>
    </div>
  </section>
</section>

<style>
/* ===== mesma largura do Header (igual ao Planos) ===== */
.container.termos-page{
  width:min(92vw, var(--container)) !important;
  margin-inline:auto;
  padding-inline:0;
}

/* ===== base visual clean (igual ao Planos) ===== */
.glass-card{
  background:rgba(255,255,255,.92);
  border:1px solid rgba(15,23,42,.06);
  padding:18px;
  border-radius:18px;
  color:var(--text,#111322);
  box-shadow:0 18px 40px rgba(15,23,42,.06);
}

.sect-title{
  margin:0 0 8px;
  font-weight:800;
  color:var(--text,#111322);
}

.sect-sub{
  margin:0 0 8px;
  font-weight:700;
  color:var(--text,#111322);
}

.muted{
  color:var(--muted,#6b7280);
  opacity:1;
  font-size:.9rem;
}

/* ===== grid termos ===== */
.terms-grid{
  display:grid;
  grid-template-columns: 320px 1fr;
  gap:12px;
  align-items:start;
}
@media (max-width:980px){
  .terms-grid{ grid-template-columns:1fr; }
}

/* ===== TOC: igual vibe dos inputs/botões do Planos ===== */
.toc{
  list-style:none;
  margin:0;
  padding:0;
  display:grid;
  gap:8px;
}
.toc a{
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 12px;
  border-radius:12px;
  text-decoration:none;

  border:1px solid #d0d7e2;
  background:#ffffff;
  color:#111322;
  font-weight:700;

  transition:background .15s ease, transform .05s ease, box-shadow .15s ease, border-color .15s ease;
}
.toc a:hover{
  background:#f3f4ff;
  box-shadow:0 2px 6px rgba(15,23,42,.08);
}
.toc a:active{ transform:translateY(1px); }

.toc a[aria-current="true"]{
  background:#111322;
  color:#ffffff;
  border-color:transparent;
}

/* ===== sticky do sumário (desktop) ===== */
@media (min-width:981px){
  .toc-card{
    position:sticky;
    top:92px; /* ajuste fino conforme altura do seu header fixo */
  }
}

/* ===== conteúdo legal: tipografia clean ===== */
.legal-card{
  overflow:hidden;
}
.legal-card a{
  color:#0b6aa1;
  font-weight:700;
  text-decoration:none;
}
.legal-card a:hover{ text-decoration:underline; }

.t-sec{
  padding:6px 0;
}
.t-sec + .t-sec{
  margin-top:10px;
  padding-top:14px;
  border-top:1px solid #eef2f7;
}

/* Títulos internos (sem mudar o conteúdo, só visual) */
.t-h{
  margin:0 0 6px;
  font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-weight:800;
  color:#111322;
  font-size: clamp(1.05rem, .95rem + .6vw, 1.3rem);
}

/* Texto e listas */
.legal-card p,
.legal-card li{
  color:#111322;
  opacity:.92;
}
.t-list{
  margin:.25rem 0 .6rem 1.1rem;
}
.t-list li{
  margin:6px 0;
}

/* ===== suaviza ancoragem (quando clicar no sumário) ===== */
html{ scroll-behavior:smooth; }
.t-sec{ scroll-margin-top: 96px; } /* evita ficar atrás do header */
</style>

<script>
/* Realce do item atual no sumário (mantido, só mais robusto) */
(function(){
  const links = Array.from(document.querySelectorAll('.toc a'));
  if(!('IntersectionObserver' in window) || !links.length) return;

  const map = {};
  links.forEach(a => {
    const id = (a.getAttribute('href') || '').replace('#','').trim();
    const el = id ? document.getElementById(id) : null;
    if(el) map[id] = { a, el };
  });

  const setCurrent = (id) => {
    links.forEach(l => l.removeAttribute('aria-current'));
    if(map[id]?.a) map[id].a.setAttribute('aria-current','true');
  };

  // Estado inicial (hash)
  if(location.hash){
    const id = location.hash.replace('#','');
    if(map[id]) setCurrent(id);
  } else {
    const first = Object.keys(map)[0];
    if(first) setCurrent(first);
  }

  const obs = new IntersectionObserver((ents)=>{
    ents.forEach(ent=>{
      if(ent.isIntersecting && ent.target && ent.target.id){
        if(map[ent.target.id]) setCurrent(ent.target.id);
      }
    });
  }, { rootMargin:'-35% 0px -55% 0px', threshold:[0,1] });

  Object.values(map).forEach(x => obs.observe(x.el));
})();
</script>
