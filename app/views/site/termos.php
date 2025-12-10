<?php // View: Termos de Uso — tema glass, sem header/footer locais ?>

<section class="container" style="margin-top:18px">
  <!-- Cabeçalho da página -->
  <div class="glass-card">
    <h1 class="sect-title">Termos de Uso</h1>
    <p class="muted">Última atualização: <?= date('d/m/Y') ?></p>
  </div>

  <!-- Grid principal -->
  <div class="terms-grid" style="margin-top:12px">
    <!-- Sumário -->
    <aside class="glass-card toc-card">
      <h3 class="muted" style="margin:0 0 6px">Sumário</h3>
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
    </aside>

    <!-- Conteúdo legal -->
    <article class="glass-card legal-card">
      <section id="t1" class="t-sec">
        <h2>1. Definições</h2>
        <p>“Aviv+” refere-se ao site, plataforma e serviços. “Usuário” é quem acessa ou utiliza o site. “Assinante” é o usuário com plano ativo.</p>
      </section>

      <section id="t2" class="t-sec">
        <h2>2. Aceite e Atualizações</h2>
        <p>Ao utilizar o site, você concorda com estes Termos. Poderemos atualizá-los; a versão vigente é a publicada nesta página.</p>
      </section>

      <section id="t3" class="t-sec">
        <h2>3. Cadastro e Conta</h2>
        <ul class="t-list">
          <li>É necessário ser maior de 18 anos ou legalmente capaz.</li>
          <li>Você é responsável pela veracidade dos dados e pela confidencialidade das credenciais.</li>
          <li>Contas podem ser suspensas em caso de fraude ou violação destes Termos.</li>
        </ul>
      </section>

      <section id="t4" class="t-sec">
        <h2>4. Planos e Pagamentos</h2>
        <ul class="t-list">
          <li>Planos/valores constam em <a href="/?r=site/planos">/?r=site/planos</a> e podem mudar.</li>
          <li>Cobranças são recorrentes conforme o ciclo do plano contratado.</li>
          <li>Cancelamentos seguem regras do plano; benefícios cessam ao fim do ciclo vigente.</li>
          <li>Direito de arrependimento (art. 49, CDC) será respeitado quando aplicável.</li>
        </ul>
      </section>

      <section id="t5" class="t-sec">
        <h2>5. Benefícios e Parcerias</h2>
        <p>Benefícios são prestados por terceiros parceiros, sujeitos a disponibilidade local e alterações. Podemos ajustar o catálogo para manter qualidade e conformidade.</p>
      </section>

      <section id="t6" class="t-sec">
        <h2>6. Telemedicina</h2>
        <p>Quando ofertada, segue a regulamentação vigente e é realizada por profissionais habilitados. Não substitui, quando necessário, atendimentos presenciais, exames ou urgências.</p>
      </section>

      <section id="t7" class="t-sec">
        <h2>7. Uso Adequado</h2>
        <ul class="t-list">
          <li>É vedado uso para fins ilícitos, abusivos, scraping massivo, engenharia reversa ou violação de privacidade.</li>
          <li>Medidas técnicas e legais podem ser adotadas para coibir uso indevido.</li>
        </ul>
      </section>

      <section id="t8" class="t-sec">
        <h2>8. Privacidade</h2>
        <p>Tratamos dados conforme nossa Política de Privacidade (consulte <a href="/?r=site/contato">/?r=site/contato</a>). Ao usar os serviços, você concorda com as práticas descritas.</p>
      </section>

      <section id="t9" class="t-sec">
        <h2>9. Propriedade Intelectual</h2>
        <p>Marcas, conteúdos, layouts e códigos do site são protegidos. Não é permitido uso sem autorização, salvo exceções legais.</p>
      </section>

      <section id="t10" class="t-sec">
        <h2>10. Responsabilidade</h2>
        <p>Serviços são prestados “no estado em que se encontram”. Empregamos esforços razoáveis de disponibilidade e segurança, sem garantir operação ininterrupta. Na máxima extensão legal, eventual indenização limita-se ao valor pago no período imediatamente anterior ao evento.</p>
      </section>

      <section id="t11" class="t-sec">
        <h2>11. Suporte e Contato</h2>
        <p>Canais: <a href="mailto:contato@avivmais.com">contato@avivmais.com</a> e <a href="/?r=site/contato">/?r=site/contato</a>. Atendimento em horário comercial; telemedicina conforme informado.</p>
      </section>

      <section id="t12" class="t-sec">
        <h2>12. Lei Aplicável e Foro</h2>
        <p>Aplica-se a legislação brasileira. Foro do domicílio do consumidor, ou da Comarca de Cabo Frio/RJ quando não houver relação de consumo.</p>
      </section>
    </article>
  </div>
</section>

<!-- Estilos locais mínimos (apoiam o seu tema glass) -->
<style>
  .terms-grid{
    display:grid; grid-template-columns: 280px 1fr; gap:12px;
  }
  @media (max-width: 980px){
    .terms-grid{ grid-template-columns: 1fr; }
  }
  .toc{ list-style:none; margin:8px 0 0; padding:0; display:grid; gap:6px; }
  .toc a{
    display:block; padding:8px 10px; border-radius:10px; text-decoration:none;
    color:#eaf3ff; font-weight:800; border:1px solid rgba(255,255,255,.14);
    background:rgba(255,255,255,.06);
  }
  .toc a:hover{ background:rgba(255,255,255,.10); }
  .toc a[aria-current="true"]{
    background:#fff; color:#0e253b; border-color:transparent;
  }
  .legal-card h2{
    margin:0 0 6px; font-family:"Poppins", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    font-weight:800; color:#fff; font-size: clamp(1.05rem, .95rem + .6vw, 1.3rem);
  }
  .legal-card p, .legal-card li{ color:#eaf3ff; }
  .t-sec + .t-sec{ margin-top:10px; }
  .t-list{ margin:.25rem 0 .6rem 1.2rem; }
</style>

<!-- Realce do item atual no sumário -->
<script>
(function(){
  const links=[...document.querySelectorAll('.toc a')];
  if(!('IntersectionObserver' in window) || !links.length) return;
  const map={}; links.forEach(a=>{
    const id=a.getAttribute('href').replace('#','');
    const el=document.getElementById(id); if(el) map[id]={a,el};
  });
  const obs=new IntersectionObserver((ents)=>{
    ents.forEach(ent=>{
      const id=ent.target.id; if(!map[id]) return;
      if(ent.isIntersecting){
        links.forEach(l=>l.removeAttribute('aria-current'));
        map[id].a.setAttribute('aria-current','true');
      }
    });
  },{rootMargin:'-40% 0px -55% 0px', threshold:[0,1]});
  Object.values(map).forEach(x=>obs.observe(x.el));
})();
</script>
