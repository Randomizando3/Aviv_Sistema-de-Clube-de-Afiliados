<?php // View: Termos de Uso — estilo igual ao "Planos" (glass-card clean, sem header/footer locais) ?>

<section class="container termos-page" style="margin-top:18px">
  <section class="admin-main">
    <!-- Cabeçalho -->
    <div class="glass-card">
      <h1 class="sect-title">Termos de Uso</h1>
      <p class="muted">Última atualização: <?= date('d/m/Y') ?></p>
    </div>

    <!-- Layout (desktop: 2 colunas | mobile: 1 coluna com sumário sticky/top) -->
    <div class="terms-grid" style="margin-top:12px">
      <!-- Sumário -->
      <aside class="glass-card toc-card" aria-label="Sumário">
        <h2 class="sect-sub" style="margin:0 0 10px">Sumário</h2>

        <nav aria-label="Sumário dos termos">
          <ul class="toc">
            <li><a href="#t1">1. Definições</a></li>
            <li><a href="#t2">2. Aceite e Atualizações</a></li>
            <li><a href="#t3">3. Cadastro e Conta</a></li>
            <li><a href="#t4">4. Planos e Pagamentos</a></li>
            <li><a href="#t5">5. Benefícios e Parcerias</a></li>
            <li><a href="#t6">6. Telemedicina</a></li>
            <li><a href="#t7">7. Uso Adequado</a></li>
            <li><a href="#t8">8. Privacidade</a></li>
            <li><a href="#t9">9. Propriedade Intelectual</a></li>
            <li><a href="#t10">10. Responsabilidade</a></li>
            <li><a href="#t11">11. Suporte e Contato</a></li>
            <li><a href="#t12">12. Lei Aplicável e Foro</a></li>
          </ul>
        </nav>

        <div class="muted" style="margin-top:12px">
          Dica: use o sumário para navegar rapidamente.
        </div>
      </aside>

      <!-- Conteúdo -->
      <article class="glass-card legal-card" aria-label="Conteúdo dos termos">
        <section id="t1" class="t-sec">
          <h2 class="t-h">1. Definições</h2>
          <p>“Aviv+” refere-se ao site, plataforma e serviços. “Usuário” é quem acessa ou utiliza o site. “Assinante” é o usuário com plano ativo.</p>
        </section>

        <section id="t2" class="t-sec">
          <h2 class="t-h">2. Aceite e Atualizações</h2>
          <p>Ao utilizar o site, você concorda com estes Termos. Poderemos atualizá-los; a versão vigente é a publicada nesta página.</p>
        </section>

        <section id="t3" class="t-sec">
          <h2 class="t-h">3. Cadastro e Conta</h2>
          <ul class="t-list">
            <li>É necessário ser maior de 18 anos ou legalmente capaz.</li>
            <li>Você é responsável pela veracidade dos dados e pela confidencialidade das credenciais.</li>
            <li>Contas podem ser suspensas em caso de fraude ou violação destes Termos.</li>
          </ul>
        </section>

        <section id="t4" class="t-sec">
          <h2 class="t-h">4. Planos e Pagamentos</h2>
          <ul class="t-list">
            <li>Planos/valores constam em <a href="/?r=site/planos">/?r=site/planos</a> e podem mudar.</li>
            <li>Cobranças são recorrentes conforme o ciclo do plano contratado.</li>
            <li>Cancelamentos seguem regras do plano; benefícios cessam ao fim do ciclo vigente.</li>
            <li>Direito de arrependimento (art. 49, CDC) será respeitado quando aplicável.</li>
          </ul>
        </section>

        <section id="t5" class="t-sec">
          <h2 class="t-h">5. Benefícios e Parcerias</h2>
          <p>Benefícios são prestados por terceiros parceiros, sujeitos a disponibilidade local e alterações. Podemos ajustar o catálogo para manter qualidade e conformidade.</p>
        </section>

        <section id="t6" class="t-sec">
          <h2 class="t-h">6. Telemedicina</h2>
          <p>Quando ofertada, segue a regulamentação vigente e é realizada por profissionais habilitados. Não substitui, quando necessário, atendimentos presenciais, exames ou urgências.</p>
        </section>

        <section id="t7" class="t-sec">
          <h2 class="t-h">7. Uso Adequado</h2>
          <ul class="t-list">
            <li>É vedado uso para fins ilícitos, abusivos, scraping massivo, engenharia reversa ou violação de privacidade.</li>
            <li>Medidas técnicas e legais podem ser adotadas para coibir uso indevido.</li>
          </ul>
        </section>

        <section id="t8" class="t-sec">
          <h2 class="t-h">8. Privacidade</h2>
          <p>Tratamos dados conforme nossa Política de Privacidade (consulte <a href="/?r=site/contato">/?r=site/contato</a>). Ao usar os serviços, você concorda com as práticas descritas.</p>
        </section>

        <section id="t9" class="t-sec">
          <h2 class="t-h">9. Propriedade Intelectual</h2>
          <p>Marcas, conteúdos, layouts e códigos do site são protegidos. Não é permitido uso sem autorização, salvo exceções legais.</p>
        </section>

        <section id="t10" class="t-sec">
          <h2 class="t-h">10. Responsabilidade</h2>
          <p>Serviços são prestados “no estado em que se encontram”. Empregamos esforços razoáveis de disponibilidade e segurança, sem garantir operação ininterrupta. Na máxima extensão legal, eventual indenização limita-se ao valor pago no período imediatamente anterior ao evento.</p>
        </section>

        <section id="t11" class="t-sec">
          <h2 class="t-h">11. Suporte e Contato</h2>
          <p>Canais: <a href="mailto:contato@avivmais.com">contato@avivmais.com</a> e <a href="/?r=site/contato">/?r=site/contato</a>. Atendimento em horário comercial; telemedicina conforme informado.</p>
        </section>

        <section id="t12" class="t-sec">
          <h2 class="t-h">12. Lei Aplicável e Foro</h2>
          <p>Aplica-se a legislação brasileira. Foro do domicílio do consumidor, ou da Comarca de Cabo Frio/RJ quando não houver relação de consumo.</p>
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
