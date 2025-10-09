{{-- resources/views/blog/index.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog — Xander Billiard</title>
  @vite('resources/css/app.css')
  <style>
    :root{
      color-scheme:dark;
      --container: 1200px;      /* lebar konten lebih lega */
    }

    html,body{ height:100%; background:#0a0a0a; overflow:hidden; overscroll-behavior:none; }
    #page-root{
      height:100%;
      min-height:100svh;
      overflow-y:auto;
      overscroll-behavior:contain;
      -webkit-overflow-scrolling:touch;
      background:#0a0a0a url('{{ asset('images/bg/background_3.png') }}') center/cover no-repeat;
    }
    #page-root,html,body{ overflow-x:hidden; } @supports(overflow:clip){ #page-root,html,body{ overflow-x:clip; } }
    img{ display:block; }

    /* ===== Layout container yang tidak mepet ===== */
    .wrap{
      max-width: var(--container);
      margin-inline: auto;
      padding-inline: clamp(16px, 4vw, 48px);
    }

    /* ===== Hero lebih tinggi + ruang bawah ===== */
    .hero{
      background:url('{{ asset('images/bg/product_breadcrumb.png') }}') center/cover no-repeat;
      padding: 120px 0 56px;
    }
    .title{ font-weight:900; letter-spacing:.2px; }
    .muted{ color:#cfcfcf; }

    /* ===== Grid kartu longgar ===== */
    .grid{ display:grid; gap: 24px; }
    @media(min-width:640px){ .cols-2{ grid-template-columns:repeat(2,1fr);} }
    @media(min-width:1024px){ .cols-3{ grid-template-columns:repeat(3,1fr); gap: 28px; } }

    /* ===== Kartu artikel ===== */
    a.clean{ text-decoration:none; color:#fff; display:block; }
    .card{
      background:#141414;
      border:1px solid rgba(255,255,255,.09);
      border-radius:18px;
      overflow:hidden;
      transition:.2s transform,.2s box-shadow;
      box-shadow: 0 12px 28px rgba(0,0,0,.28);
    }
    .card:hover{ transform:translateY(-4px); box-shadow:0 18px 44px rgba(0,0,0,.38); }

    .thumb{
      width:100%; aspect-ratio:16/9; object-fit:cover; background:#0c0c0c;
      border-bottom:1px solid rgba(255,255,255,.06);
    }
    .pad{ padding:20px; }
    .badge{
      display:inline-flex; font-size:12px; padding:6px 10px; border-radius:999px;
      background:#0f0f0f; border:1px solid rgba(255,255,255,.10); color:#93c5fd;
    }

    /* ===== Search lebih nyaman ===== */
    .search-wrap{ display:grid; grid-template-columns:1fr auto; gap:12px; }
    .search{
      width:100%; padding:12px 14px; background:#0f0f0f;
      border:1px solid rgba(255,255,255,.14); border-radius:12px; color:#fff; outline:none;
    }
    .btn{
      display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
      padding:.7rem 1rem; border-radius:12px; font-weight:600; font-size:.9rem;
      background:#2563eb; color:#fff; border:1px solid rgba(255,255,255,.12);
    }
    .btn:hover{ background:#1d4ed8; }
  </style>
</head>
<body class="text-white">
<div id="page-root">
  @include('partials.navbar')

  {{-- ===== HERO ===== --}}
  <section class="hero">
    <div class="wrap">
      <p class="text-sm text-gray-400">Home / Blog</p>
      <h1 class="title text-3xl md:text-4xl mt-2">Blog</h1>
      <p class="muted mt-2">Cerita produk, tips bermain, dan update komunitas.</p>
      <form action="{{ route('blog.index') }}" method="get" class="mt-5 search-wrap">
        <input class="search" type="text" name="q" value="{{ request('q') }}" placeholder="Cari artikel / event…">
        <button class="btn" type="submit">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18a8 8 0 1 1 6.32-3.09l4.39 4.4-1.42 1.41-4.39-4.39A7.97 7.97 0 0 1 10 18m0-2a6 6 0 1 0 0-12 6 6 0 0 0 0 12"/></svg>
          Search
        </button>
      </form>
    </div>
  </section>

  @php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    // Ambil data dari tabel events sebagai sumber "berita"
    $q = trim(request('q',''));
    $eventsQuery = \App\Models\Event::query();

    if($q !== ''){
        $eventsQuery->where(function($x) use ($q){
            $x->where('name','like',"%{$q}%")
              ->orWhere('location','like',"%{$q}%")
              ->orWhere('game_types','like',"%{$q}%");
        });
    }

    // urutkan dari yang terbaru (start_date kalau ada, fallback created_at)
    $events = $eventsQuery
      ->orderByRaw('COALESCE(start_date, created_at) DESC')
      ->get();

    // helper gambar
    $resolveImage = function ($path) {
        if (empty($path)) return null;
        return Str::startsWith($path, ['http://','https://','/']) ? $path : asset($path);
    };
  @endphp

  {{-- ===== LIST ===== --}}
  <main class="wrap py-12 md:py-14 lg:py-16">
    @if($events->isEmpty())
      <p class="muted">Tidak ada artikel yang cocok.</p>
    @else
      <div class="grid cols-3">
        @foreach($events as $e)
          @php
            $img = $resolveImage($e->image_url ?? null) ?: asset('images/placeholder/service.png');
            // slug untuk blog detail: pakai slug event jika ada, jika tidak pakai nama yang dislug + id
            $slug = $e->slug ?? (Str::slug($e->name).'-'.$e->id);
            $date = $e->start_date ?? $e->created_at;
            try { $dateStr = Carbon::parse($date)->format('d M Y'); } catch (\Throwable $th) { $dateStr = ''; }
          @endphp

          <a class="card clean" href="{{ route('blog.show', $slug) }}">
            <img class="thumb" src="{{ $img }}" alt="{{ $e->name }}">
            <div class="pad">
              <div class="badge">{{ $e->status ?? 'Event' }}</div>
              <h3 class="mt-2 mb-1 text-lg font-bold line-clamp-2">{{ $e->name }}</h3>
              <div class="muted">{{ $dateStr }}</div>
            </div>
          </a>
        @endforeach
      </div>
    @endif
  </main>

  @include('partials.footer')
</div>

<script>
  /* Cegah rubber-band putih di luar container */
  (function(){ const s=document.getElementById('page-root'); if(!s) return;
    function n(){ if(s.scrollTop<=0) s.scrollTop=1; const m=s.scrollHeight-s.clientHeight; if(s.scrollTop>=m) s.scrollTop=m-1; }
    let y=0; s.addEventListener('touchstart',e=>{y=(e.touches?.[0]?.clientY)||0; n();},{passive:true});
    s.addEventListener('touchmove',e=>{const ny=(e.touches?.[0]?.clientY)||0; const dy=ny-y; const atTop=s.scrollTop<=0; const atBot=s.scrollTop+s.clientHeight>=s.scrollHeight; if((atTop&&dy>0)||(atBot&&dy<0)) e.preventDefault();},{passive:false});
    document.addEventListener('touchmove',e=>{ if(!e.target.closest('#page-root')) e.preventDefault(); },{passive:false});
  })();
</script>
</body>
</html>
