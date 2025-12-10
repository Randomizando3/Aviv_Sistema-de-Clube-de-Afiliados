
(function(){
  "use strict";

  // --- endpoints ---
  const POOL = "/?r=api/partner/ads/public-pool"; // mesmo domínio

  // --- tamanhos forçados por tipo ---
  const SIZES = {
    sky:      { w:168, h:600 },     // arranha-céu (lateral)
    sky_1:    { w:168, h:600 },
    sky_2:    { w:168, h:600 },
    top_468:  { w:468, h:60  },     // topo 468
    square:   { w:250, h:250 },     // quadrado
    square_1: { w:250, h:250 },
    square_2: { w:250, h:250 }
  };

  const GROUP = (t) => (
    t === "sky_1" || t === "sky_2" ? "sky" :
    t === "square_1" || t === "square_2" ? "square" :
    t
  );

  function toInt(v, d=1){ const n = parseInt(v,10); return isNaN(n)?d:n; }

  function firePixel(url){
    if(!url) return;
    const img = new Image();
    img.referrerPolicy = "no-referrer-when-downgrade";
    img.src = url + (url.includes("?") ? "&" : "?") + "ts=" + Date.now();
  }

  async function fetchPool(params){
    const usp = new URLSearchParams(params);
    const r = await fetch(POOL + "&" + usp.toString(), { credentials:"same-origin" });
    try { return await r.json(); } catch(e){ return { items:[] }; }
  }

  function renderItem(item, type){
    const t    = GROUP(type);
    const size = SIZES[type] || SIZES[t] || SIZES.square;

    // wrapper com tamanho rígido
    const wrap = document.createElement("div");
    wrap.style.cssText = [
      "display:inline-block",
      "position:relative",
      "overflow:hidden",
      "border-radius:12px",
      "border:1px solid #e5eaf0",
      "background:#fff",
      "box-sizing:border-box",
      `width:${size.w}px`,
      `height:${size.h}px`
    ].join(";");

    // link
    const a = document.createElement("a");
    a.href   = item.target_url || "#";
    a.target = "_blank";
    a.rel    = "noopener";
    a.style.cssText = [
      "display:block",
      "width:100%",
      "height:100%",
      "line-height:0",
      "text-decoration:none",
      "background:#fff"
    ].join(";");

    // imagem forçada ao box (sem distorcer)
    const img = document.createElement("img");
    img.src      = item.img;
    img.alt      = item.title || "Anúncio";
    img.loading  = "lazy";
    img.decoding = "async";
    img.width    = size.w;        // atributos HTML ajudam layout
    img.height   = size.h;
    img.style.cssText = [
      "display:block",
      "width:100%",
      "height:100%",
      "object-fit:contain",       // não distorce; pode sobrar borda
      "object-position:center",
      "border:0",
      "background:#fff"
    ].join(";");

    a.appendChild(img);
    wrap.appendChild(a);

    // pixel de impressão (contagem)
    if (item.pixel) firePixel(item.pixel);

    return wrap;
  }

  async function mountOne(container){
    const type  = (container.dataset.type || "square").toLowerCase();
    const limit = Math.max(1, toInt(container.dataset.limit || "1", 1));
    const id    = container.dataset.id ? toInt(container.dataset.id, 0) : null;
    const order = container.dataset.order ? toInt(container.dataset.order, 0) : null;

    const q = { type, limit };
    if (id)    q.id    = id;     // força campanha específica (opcional)
    if (order) q.order = order;  // força pedido específico (opcional)

    container.innerHTML = ""; // limpa placeholder

    const data  = await fetchPool(q);
    const items = Array.isArray(data.items) ? data.items.slice(0, limit) : [];
    if (!items.length) return;

    const frag = document.createDocumentFragment();
    items.forEach(it => frag.appendChild(renderItem(it, type)));
    container.appendChild(frag);
  }

  function autoMount(){
    document.querySelectorAll("[data-aviv-ad]").forEach(mountOne);
  }

  // API opcional no window
  window.AvivAds = {
    mount: mountOne,  // AvivAds.mount(element)
    scan:  autoMount  // reprocessa todos [data-aviv-ad]
  };

  if (document.readyState === "loading"){
    document.addEventListener("DOMContentLoaded", autoMount);
  } else {
    autoMount();
  }
})();
