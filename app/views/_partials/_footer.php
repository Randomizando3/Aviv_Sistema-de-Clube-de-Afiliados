<?php if (!empty($PAGE_BARE)) return; ?>

<?php
// app/views/_partials/_footer.php (sem banner 468)

// Quadrados (250x250) dentro do footer
$adSquareFallback = $adSquareFallback ?? '/img/ads/250x250-default.png';
?>

<footer class="site-footer" role="contentinfo">
  <div class="container footer-grid">
    <!-- Coluna esquerda: conteúdo -->
    <div class="footer-info">
      <img src="/img/logo-aviv-plus.png" alt="Aviv+" width="120" height="50" />
      <p class="muted">Clube de assinaturas com benefícios, cupons e carteirinha digital.</p>

      <div class="footer-cols">
        <div>
          <h4>Produto</h4>
          <ul>
            <li><a href="/?r=site/planos">Planos</a></li>
            <li><a href="/?r=site/parceiros">Seja parceiro</a></li>
            <li><a href="/?r=site/faq">Perguntas frequentes</a></li>
          </ul>
        </div>
        <div>
          <h4>Conta</h4>
          <ul>
            <li><a href="/?r=auth/login">Entrar</a></li>
            <li><a href="/?r=auth/register">Criar conta</a></li>
            <li><a href="/?r=auth/forgot">Esqueci minha senha</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Coluna direita: 2 quadrados lado a lado -->
    <aside class="footer-ads" aria-label="Publicidade" id="footer-ads">
      <div class="adbox-250" id="ad-square-1">
        <div class="ad-skeleton" aria-hidden="true"></div>
        <?php if (!empty($adSquareFallback)): ?>
          <a href="#" target="_blank" rel="noopener" class="ad-fallback" style="display:none">
            <img src="<?= htmlspecialchars($adSquareFallback) ?>" alt="Publicidade" width="250" height="250" loading="lazy">
          </a>
        <?php endif; ?>
      </div>

      <div class="adbox-250" id="ad-square-2">
        <div class="ad-skeleton" aria-hidden="true"></div>
        <?php if (!empty($adSquareFallback)): ?>
          <a href="#" target="_blank" rel="noopener" class="ad-fallback" style="display:none">
            <img src="<?= htmlspecialchars($adSquareFallback) ?>" alt="Publicidade" width="250" height="250" loading="lazy">
          </a>
        <?php endif; ?>
      </div>
    </aside>
  </div>

  <div class="copy">
    <div class="container">© <?= date('Y') ?> Aviv+. Todos os direitos reservados.</div>
  </div>
</footer>

<style>
:root{
  --brand-green: #16a34a;
  --brand-green-dark: #15803d;
}

/* Largura igual ao header */
.site-footer .container{
  width:min(92vw, 1120px);
  margin-inline:auto;
}

/* Skeleton shimmer (reuso nos quadrados) */
.ad-skeleton{
  position:absolute;
  inset:0;
  background:linear-gradient(90deg, rgba(15,118,110,.15), rgba(22,163,74,.4), rgba(15,118,110,.15));
  background-size:200% 100%;
  animation: adsh 1.1s linear infinite;
}
@keyframes adsh{
  to { background-position: -200% 0; }
}

/* === Footer com fundo verde do site === */
.site-footer{
  color:#ecfdf5;
  padding: 24px 0 0;
  background: linear-gradient(135deg, var(--brand-green), var(--brand-green-dark));
}

/* Grid principal */
.footer-grid{
  display:grid;
  grid-template-columns: 1fr auto;
  gap: 18px;
  align-items:flex-start;
}

/* Coluna esquerda */
.footer-info img{
  display:block;
  margin-bottom:10px;
}
.footer-info .muted{
  color:#d1fae5;
  margin:6px 0 12px;
  font-size:.9rem;
}

.footer-cols{
  display:grid;
  grid-template-columns: repeat(2, minmax(0,1fr));
  gap: 14px 24px;
}
.footer-cols h4{
  margin:0 0 6px;
  font-size:.9rem;
  color:#bbf7d0;
  text-transform:uppercase;
  letter-spacing:.04em;
  font-weight:700;
}
.footer-cols ul{
  list-style:none;
  margin:0;
  padding:0;
}
.footer-cols li{
  margin:4px 0;
}
.footer-cols a{
  color:#ecfdf5;
  text-decoration:none;
  opacity:.95;
  font-size:.9rem;
}
.footer-cols a:hover{
  opacity:1;
  text-decoration:underline;
}

/* Ads no footer (direita) */
.footer-ads{
  display:grid;
  grid-template-columns: 250px 250px;
  gap: 10px;
  padding-left: 8px;
}
.adbox-250{
  width:250px;
  max-width:100%;
  aspect-ratio: 1/1;
  border:1px solid rgba(15,23,42,.16);
  border-radius:16px;
  overflow:hidden;
  background:#ffffff;
  position:relative;
  box-shadow: 0 16px 32px rgba(15,23,42,.35);
}
.adbox-250 a{
  display:block;
  width:100%;
  height:100%;
}
.adbox-250 img{
  display:block;
  width:100%;
  height:100%;
  object-fit:cover;
}

/* Barra de copyright com faixa verde mais escura */
.copy{
  margin-top: 20px;
  padding: 10px 0 12px;
  color:#bbf7d0;
  font-size:.85rem;
  background:rgba(0,0,0,.12);
  border-top:1px solid rgba(15,23,42,.25);
}

/* Responsivo */
@media (max-width: 920px){
  .footer-grid{
    grid-template-columns: 1fr;
  }
  .footer-ads{
    grid-template-columns: 1fr 1fr;
    justify-content:center;
    padding-left:0;
  }
}
@media (max-width: 560px){
  .footer-ads{
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .adbox-250{
    width:100%;
  }
}
</style>

<script>
(function(){
  function ready(fn){ if(document.readyState!=='loading'){ fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }

  ready(function(){
    // Carrega apenas os 2 quadrados (250x250)
    var wrap = document.getElementById('footer-ads');
    var b1 = document.getElementById('ad-square-1');
    var b2 = document.getElementById('ad-square-2');
    if(!wrap || !b1 || !b2) return;

    fetch('/?r=api/partner/ads/public-pool&type=square&limit=2', { credentials:'same-origin' })
      .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
      .then(function(j){
        var list = (j && Array.isArray(j.items)) ? j.items.slice(0,2) : [];
        [b1,b2].forEach(function(box, idx){
          var it = list[idx] || null;
          var sk = box.querySelector('.ad-skeleton'); if(sk) sk.remove();

          if(!it){
            var fb = box.querySelector('.ad-fallback');
            if(fb){ fb.style.display='block'; } else { box.style.display='none'; }
            return;
          }

          box.innerHTML='';
          var a = document.createElement('a');
          a.href = it.target_url || '#';
          a.target='_blank';
          a.rel='noopener';
          a.title = it.title || 'Publicidade';

          var img = new Image();
          img.src = it.img;
          img.alt = it.title || 'Anúncio';
          img.loading='lazy';
          img.width = Number(it.w || 250);
          img.height = Number(it.h || 250);

          a.appendChild(img);
          box.appendChild(a);

          if(it.pixel){
            var px = new Image();
            px.src = it.pixel + '&ts=' + Date.now();
            px.width=1;
            px.height=1;
            px.alt='';
            px.style.position='absolute';
            px.style.inset='auto auto 0 0';
            px.style.opacity='0';
            box.appendChild(px);
          }
        });
      })
      .catch(function(){
        [b1,b2].forEach(function(box){
          var sk = box.querySelector('.ad-skeleton'); if(sk) sk.remove();
          var fb = box.querySelector('.ad-fallback');
          if(fb){ fb.style.display='block'; } else { box.style.display='none'; }
        });
      });
  });
})();
</script>
