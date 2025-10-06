<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog — Xander Billiard</title>
  @vite('resources/css/app.css')
  <style>
    :root{ color-scheme:dark; }
    html,body{ height:100%; background:#0a0a0a; overflow:hidden; overscroll-behavior:none; }
    #page-root{ height:100%; min-height:100svh; overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; background:#0a0a0a url('{{ asset('images/bg/background_3.png') }}') center/cover no-repeat; }
    #page-root,html,body{ overflow-x:hidden; } @supports(overflow:clip){ #page-root,html,body{ overflow-x:clip; } }
    img{ display:block; }
    .wrap{ max-width:1100px; margin:0 auto; padding:0 16px; }
    .hero{ background:url('{{ asset('images/bg/product_breadcrumb.png') }}') center/cover no-repeat; padding:96px 0 40px; }
    .title{ font-weight:900; }
    .muted{ color:#cfcfcf; }
    .grid{ display:grid; gap:18px; }
    @media(min-width:768px){ .cols-3{ grid-template-columns:repeat(3,1fr);} }
    .card{ background:#141414; border:1px solid rgba(255,255,255,.08); border-radius:16px; overflow:hidden; transition:.2s transform,.2s box-shadow; }
    .card:hover{ transform:translateY(-4px); box-shadow:0 16px 40px rgba(0,0,0,.35); }
    .thumb{ width:100%; aspect-ratio:16/9; object-fit:cover; background:#0c0c0c; }
    .pad{ padding:16px; }
    .badge{ display:inline-flex; font-size:12px; padding:6px 10px; border-radius:999px; background:#0f0f0f; border:1px solid rgba(255,255,255,.08); color:#93c5fd; }
    input.search{ width:100%; padding:12px 14px; background:#0f0f0f; border:1px solid rgba(255,255,255,.12); border-radius:12px; color:#fff; outline:none; }
    a.clean{ text-decoration:none; color:#fff; }
  </style>
</head>
<body class="text-white">
<div id="page-root">
  @include('partials.navbar')

  <section class="hero">
    <div class="wrap">
      <p class="text-sm text-gray-400">Home / Blog</p>
      <h1 class="title text-3xl md:text-4xl mt-2">Blog</h1>
      <p class="muted mt-2">Cerita produk, tips bermain, dan update komunitas.</p>
      <form action="{{ route('blog.index') }}" method="get" class="mt-4">
        <input class="search" type="text" name="q" value="{{ request('q') }}" placeholder="Cari artikel…">
      </form>
    </div>
  </section>

  @php
    $posts = [
      ['slug'=>'control-ball-tips','title'=>'5 Tips Control Ball untuk Pemula','cat'=>'Tips','date'=>'2025-09-15','img'=>'/images/blog/b1.jpg'],
      ['slug'=>'price-schedule-launch','title'=>'Rilis: Price Schedule untuk Venue','cat'=>'Product','date'=>'2025-09-21','img'=>'/images/blog/b2.jpg'],
      ['slug'=>'regional-open-2025','title'=>'Regional Open 2025 Resmi Dibuka','cat'=>'Event','date'=>'2025-08-01','img'=>'/images/blog/b3.jpg'],
      ['slug'=>'cue-maintenance','title'=>'Rawat Cue Supaya Awet','cat'=>'Tips','date'=>'2025-07-11','img'=>'/images/blog/b4.jpg'],
      ['slug'=>'community-stories','title'=>'Cerita Komunitas Bandung','cat'=>'Community','date'=>'2025-07-05','img'=>'/images/blog/b5.jpg'],
    ];
    $q = trim(request('q',''));
    if($q!==''){
      $posts = array_values(array_filter($posts, fn($p)=>stripos($p['title'],$q)!==false));
    }
  @endphp

  <main class="wrap py-10">
    @if(empty($posts))
      <p class="muted">Tidak ada artikel yang cocok.</p>
    @else
    <div class="grid cols-3">
      @foreach($posts as $p)
      <a class="card clean" href="{{ route('blog.show',$p['slug']) }}">
        <img class="thumb" src="{{ asset($p['img']) }}" alt="{{ $p['title'] }}">
        <div class="pad">
          <div class="badge">{{ $p['cat'] }}</div>
          <h3 class="mt-2 mb-1 text-lg font-bold">{{ $p['title'] }}</h3>
          <div class="muted">{{ \Carbon\Carbon::parse($p['date'])->format('d M Y') }}</div>
        </div>
      </a>
      @endforeach
    </div>
    @endif
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
