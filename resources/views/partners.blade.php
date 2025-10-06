<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Partners — Xander Billiard</title>
  @vite('resources/css/app.css')
  <style>
    :root{ color-scheme:dark; }
    html,body{ height:100%; background:#0a0a0a; overflow:hidden; overscroll-behavior:none; }
    #page-root{ height:100%; min-height:100svh; overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; background:#0a0a0a url('{{ asset('images/bg/background_3.png') }}') center/cover no-repeat; }
    #page-root,html,body{ overflow-x:hidden; } @supports(overflow:clip){ #page-root,html,body{ overflow-x:clip; } }
    img{ display:block; }
    .wrap{ max-width:1100px; margin:0 auto; padding:0 16px; }
    .hero{ background:url('{{ asset('images/bg/product_breadcrumb.png') }}') center/cover no-repeat; padding:96px 0 64px; }
    .title{ font-weight:900; }
    .muted{ color:#cfcfcf; }
    .grid{ display:grid; gap:18px; }
    @media(min-width:768px){ .cols-3{ grid-template-columns:repeat(3,1fr);} .cols-4{ grid-template-columns:repeat(4,1fr);} }
    .card{ background:#141414; border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:18px; }
    .logo{ height:56px; filter:grayscale(1) brightness(1.2); opacity:.9; }
    .btn{ display:inline-flex; gap:10px; align-items:center; padding:12px 16px; border-radius:12px; text-decoration:none; background:#00c2a2; color:#041; font-weight:800; }
    .btn.ghost{ background:transparent; color:#fff; border:1px solid rgba(255,255,255,.16); }
    .rule{ height:3px; background:#D9D9D9; border:none; }
  </style>
</head>
<body class="text-white">
<div id="page-root">
  @include('partials.navbar')

  <section class="hero">
    <div class="wrap">
      <p class="text-sm text-gray-400">Home / Company / Partners</p>
      <h1 class="title text-3xl md:text-4xl mt-2">Partners</h1>
      <p class="muted mt-2 max-w-3xl">Kami berkolaborasi dengan venue, brand perlengkapan, komunitas, dan media untuk memperluas ekosistem biliar.</p>
      <div class="mt-4 flex gap-3 flex-wrap">
        <a class="btn" href="mailto:partners@xanderbilliard.com?subject=Partnership%20Inquiry">Ajukan Kerja Sama</a>
        <a class="btn ghost" href="#tiers">Jenis Kemitraan</a>
      </div>
    </div>
  </section>

  <main class="wrap py-12">
    <h2 id="tiers" class="text-2xl font-extrabold mb-3">Jenis Kemitraan</h2>
    <div class="grid cols-3 mb-8">
      <div class="card"><h3 class="font-bold mb-1">Venue Partner</h3><p class="muted">Integrasi booking, price schedule, promo, & insight.</p></div>
      <div class="card"><h3 class="font-bold mb-1">Brand & Accessories</h3><p class="muted">Katalog, campaign, afiliasi, bundling.</p></div>
      <div class="card"><h3 class="font-bold mb-1">Community & Tournament</h3><p class="muted">Kolaborasi event, bracket digital, publikasi.</p></div>
    </div>

    <hr class="rule mb-6"/>

    <h2 class="text-2xl font-extrabold mb-3">Beberapa Partner</h2>
    @php
      $logos = [
        '/images/partners/p1.png','/images/partners/p2.png','/images/partners/p3.png','/images/partners/p4.png',
        '/images/partners/p5.png','/images/partners/p6.png','/images/partners/p7.png','/images/partners/p8.png',
      ];
    @endphp
    <div class="grid cols-4">
      @foreach($logos as $src)
        <div class="card flex items-center justify-center"><img class="logo" src="{{ asset($src) }}" alt="Partner"></div>
      @endforeach
    </div>

    <div class="mt-8">
      <a class="btn" href="{{ route('guideline.index') }}">Lihat Panduan Venue</a>
    </div>
  </main>

  @include('partials.footer')
</div>

<script>
  (function(){ const s=document.getElementById('page-root'); if(!s) return;
    function n(){ if(s.scrollTop<=0) s.scrollTop=1; const m=s.scrollHeight-s.clientHeight; if(s.scrollTop>=m) s.scrollTop=m-1; }
    let y=0; s.addEventListener('touchstart',e=>{y=(e.touches?.[0]?.clientY)||0; n();},{passive:true});
    s.addEventListener('touchmove',e=>{const ny=(e.touches?.[0]?.clientY)||0; const dy=ny-y; const atTop=s.scrollTop<=0; const atBot=s.scrollTop+s.clientHeight>=s.scrollHeight; if((atTop&&dy>0)||(atBot&&dy<0)) e.preventDefault();},{passive:false});
    document.addEventListener('touchmove',e=>{ if(!e.target.closest('#page-root')) e.preventDefault(); },{passive:false});
  })();
</script>
</body>
</html>
