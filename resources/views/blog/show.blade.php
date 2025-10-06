<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog Detail — Xander Billiard</title>
  @vite('resources/css/app.css')
  <style>
    :root{ color-scheme:dark; }
    html,body{ height:100%; background:#0a0a0a; overflow:hidden; overscroll-behavior:none; }
    #page-root{ height:100%; min-height:100svh; overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; background:#0a0a0a url('{{ asset('images/bg/background_3.png') }}') center/cover no-repeat; }
    #page-root,html,body{ overflow-x:hidden; } @supports(overflow:clip){ #page-root,html,body{ overflow-x:clip; } }
    img{ display:block; }
    .wrap{ max-width:840px; margin:0 auto; padding:0 16px; }
    .hero{ background:url('{{ asset('images/bg/product_breadcrumb.png') }}') center/cover no-repeat; padding:72px 0 24px; }
    .title{ font-weight:900; }
    .muted{ color:#cfcfcf; }
    .cover{ width:100%; aspect-ratio:16/9; object-fit:cover; border-radius:14px; border:1px solid rgba(255,255,255,.08); }
    .content p{ color:#eaeaea; line-height:1.75; margin:12px 0; }
    .content h2{ margin-top:28px; font-size:22px; }
    .back{ display:inline-flex; gap:8px; align-items:center; color:#fff; text-decoration:none; border:1px solid rgba(255,255,255,.16); border-radius:10px; padding:8px 12px; }
  </style>
</head>
<body class="text-white">
<div id="page-root">
  @include('partials.navbar')

  @php
    $slug = request()->route('slug');
    $map = [
      'control-ball-tips'=>['title'=>'5 Tips Control Ball untuk Pemula','date'=>'2025-09-15','img'=>'/images/blog/b1.jpg'],
      'price-schedule-launch'=>['title'=>'Rilis: Price Schedule untuk Venue','date'=>'2025-09-21','img'=>'/images/blog/b2.jpg'],
      'regional-open-2025'=>['title'=>'Regional Open 2025 Resmi Dibuka','date'=>'2025-08-01','img'=>'/images/blog/b3.jpg'],
      'cue-maintenance'=>['title'=>'Rawat Cue Supaya Awet','date'=>'2025-07-11','img'=>'/images/blog/b4.jpg'],
      'community-stories'=>['title'=>'Cerita Komunitas Bandung','date'=>'2025-07-05','img'=>'/images/blog/b5.jpg'],
    ];
    $post = $map[$slug] ?? ['title'=>'Artikel','date'=>now()->toDateString(),'img'=>'/images/blog/b1.jpg'];
  @endphp

  <section class="hero">
    <div class="wrap">
      <a class="back" href="{{ route('blog.index') }}">← Kembali ke Blog</a>
      <h1 class="title text-3xl md:text-4xl mt-3">{{ $post['title'] }}</h1>
      <div class="muted">{{ \Carbon\Carbon::parse($post['date'])->format('d M Y') }}</div>
    </div>
  </section>

  <main class="wrap py-8">
    <img class="cover" src="{{ asset($post['img']) }}" alt="">
    <article class="content mt-5">
      <p>Paragraf pembuka artikel. Tulis ceritamu di sini.</p>
      <h2>Subjudul</h2>
      <p>Isi artikel, tips, atau pembaruan produk. Gunakan <strong>teks tebal</strong> dan tautan bila perlu.</p>
      <p>Penutup artikel.</p>
    </article>
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
