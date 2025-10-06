<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Press & Media — Xander Billiard</title>
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
    @media(min-width:768px){ .cols-2{ grid-template-columns:repeat(2,1fr);} .cols-3{ grid-template-columns:repeat(3,1fr);} }
    .card{ background:#141414; border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:18px; }
    .btn{ display:inline-flex; align-items:center; gap:10px; padding:12px 16px; border-radius:12px; text-decoration:none; font-weight:800; }
    .btn.primary{ background:#00c2a2; color:#041; }
    .btn.ghost{ background:transparent; color:#fff; border:1px solid rgba(255,255,255,.16); }
    .rule{ height:3px; background:#D9D9D9; border:none; }
  </style>
</head>
<body class="text-white">
<div id="page-root">
  @include('partials.navbar')

  <section class="hero">
    <div class="wrap">
      <p class="text-sm text-gray-400">Home / Company / Press & Media</p>
      <h1 class="title text-3xl md:text-4xl mt-2">Press & Media</h1>
      <p class="muted mt-2 max-w-3xl">Informasi & aset media untuk jurnalis dan partner. Unduh media kit, brand assets, dan hubungi tim PR.</p>
      <div class="mt-4 flex gap-3 flex-wrap">
        <a class="btn primary" href="{{ asset('downloads/media-kit.zip') }}">Unduh Media Kit (ZIP)</a>
        <a class="btn ghost" href="mailto:press@xanderbilliard.com">press@xanderbilliard.com</a>
      </div>
    </div>
  </section>

  <main class="wrap py-12">
    <h2 class="text-2xl font-extrabold mb-3">Siap Kutip</h2>
    <div class="grid cols-2 mb-8">
      <div class="card">
        <h3 class="font-bold mb-1">Ringkas</h3>
        <p class="muted">Xander Billiard adalah platform ekosistem biliar untuk booking venue, turnamen digital, dan belanja perlengkapan.</p>
      </div>
      <div class="card">
        <h3 class="font-bold mb-1">Panjang</h3>
        <p class="muted">Didirikan pada 2025, Xander Billiard memberdayakan komunitas biliar melalui teknologi pemesanan, manajemen turnamen, dan retail perlengkapan…</p>
      </div>
    </div>

    <hr class="rule mb-6"/>

    <h2 class="text-2xl font-extrabold mb-3">Rilis Pers Terbaru</h2>
    @php
      $press = [
        ['title'=>'Xander luncurkan fitur Price Schedule untuk Venue','date'=>'2025-09-21','slug'=>'price-schedule-launch'],
        ['title'=>'Kolaborasi Kejuaraan Regional Jawa Barat','date'=>'2025-08-05','slug'=>'west-java-collab'],
      ];
    @endphp
    <div class="grid">
      @foreach($press as $p)
        <a href="{{ route('blog.show',$p['slug']) }}" class="card text-white no-underline">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h3 class="font-bold mb-1">{{ $p['title'] }}</h3>
              <div class="muted">{{ \Carbon\Carbon::parse($p['date'])->format('d M Y') }}</div>
            </div>
            <span class="btn ghost">Baca</span>
          </div>
        </a>
      @endforeach
    </div>

    <h2 class="text-2xl font-extrabold mt-8 mb-3">Brand Assets</h2>
    <div class="grid cols-3">
      <div class="card"><strong>Logo Utama (PNG)</strong><a class="btn primary mt-3" href="{{ asset('brand/logo-primary.png') }}" download>Unduh</a></div>
      <div class="card"><strong>Logo Monokrom (PNG)</strong><a class="btn primary mt-3" href="{{ asset('brand/logo-mono.png') }}" download>Unduh</a></div>
      <div class="card"><strong>Guideline (PDF)</strong><a class="btn primary mt-3" href="{{ asset('brand/brand-guideline.pdf') }}" download>Unduh</a></div>
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
