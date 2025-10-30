@extends('app')
@section('title', 'User Dashboard - Favorite')

@push('styles')
<style>
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }

  /* Root gelap + tinggi penuh */
  :root, html, body{ background:var(--page-bg); }
  html, body{ height:100%; }

  /* Matikan overscroll glow/bounce tembus body */
  html, body{
    overscroll-behavior-y: none;
    overscroll-behavior-x: none;
    touch-action: pan-y;
    -webkit-text-size-adjust: 100%;
  }

  /* Kanvas gelap fixed di belakang semua konten (extend atas/bawah) */
  #antiBounceBg{
    position: fixed;
    left:0; right:0;
    top:-120svh; bottom:-120svh;
    background:var(--page-bg);
    z-index:-1;
    pointer-events:none;
  }

  /* Pastikan wrapper layout gelap juga */
  #app, main{ background:var(--page-bg); }

  /* Scroll containers: cegah chaining + wajib bg gelap */
  .scroll-root{ overscroll-behavior: contain; background:var(--page-bg); }
  .scroll-inner{ overscroll-beavior: contain; background:var(--page-bg); }

  /* ====== Animations shared ====== */
  @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
  .float-animation{ animation: float 3s ease-in-out infinite; }

  @keyframes fadeIn { from{opacity:0; transform:translateY(20px)} to{opacity:1; transform:translateY(0)} }
  .fade-in{ animation: fadeIn .6s ease-out forwards; }

  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
  .pulse-slow{ animation: pulse 2s ease-in-out infinite; }

  /* ====== Tambahan style untuk tampilan saat ADA data ====== */
  .fav-badge{
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
  }

  @keyframes gradientRotate {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  .gradient-border{ position:relative; background: linear-gradient(135deg, #1f1f1f 0%, #151515 100%); }
  .gradient-border::before{
    content:''; position:absolute; inset:-2px; border-radius:inherit; padding:2px;
    background: linear-gradient(90deg,#ef4444,#f59e0b,#10b981,#3b82f6,#8b5cf6,#ef4444);
    background-size:300% 300%; animation: gradientRotate 6s ease infinite;
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor; mask-composite: exclude;
    opacity:0; transition: opacity .3s;
  }
  .gradient-border:hover::before{ opacity:1; }

  .fav-card{ position:relative; overflow:hidden; }
  .fav-card::before{
    content:''; position:absolute; top:0; left:-100%; width:100%; height:100%;
    background: linear-gradient(90deg,transparent,rgba(255,255,255,.05),transparent);
    transition:left .5s; z-index:1; pointer-events:none;
  }
  .fav-card:hover::before{ left:100%; }

  .image-overlay{
    background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,.3) 50%, rgba(0,0,0,.8) 100%);
  }

  /* ====== Loader gambar + fade-in ====== */
  .img-wrapper { position: relative; background: #171717; overflow: hidden; border-radius: .5rem; }
  .img-wrapper > img { display:block; width:100%; height:100%; object-fit:cover; opacity:0; transition: opacity .28s ease; }
  .img-wrapper > img.is-loaded { opacity:1; }
  .img-loading { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:#171717; z-index:1; }
  .img-loading.is-hidden { display:none; }
  .spinner { width:40px; height:40px; border:3px solid rgba(255,255,255,.18); border-top-color:#9ca3af; border-radius:50%; animation: spin .8s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<!-- Anti white flash canvas -->
<div id="antiBounceBg" aria-hidden="true"></div>

<!-- Stabilkan unit tinggi viewport di mobile (toolbar naik/turun) -->
<script>
  (function(){
    function setSVH(){
      const svh = window.innerHeight * 0.01;
      document.documentElement.style.setProperty('--svh', svh + 'px');
    }
    setSVH();
    window.addEventListener('resize', setSVH);
  })();
</script>

@php
  // FE base ABSOLUTE untuk venue
  $feBase = 'https://demo-xanders.ptbmn.id/images/venue/';
@endphp

<div class="min-h-screen bg-neutral-900 text-white scroll-root">
  <div class="flex min-h-[100dvh]">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 my-8 bg-neutral-900 scroll-root">
      @include('partials.topbar')

      <div class="max-w-6xl mt-16 mx-6 md:mx-16 scroll-inner">
        <h1 class="text-2xl font-extrabold mb-6">My Favorites</h1>

        @if (session('success'))
          <div class="mb-4 rounded-md bg-green-600/20 text-green-300 px-4 py-3 border border-green-600/30 fade-in">
            {{ session('success') }}
          </div>
        @endif

        @if ($favorites->isEmpty())
          {{-- ====== Empty State (tetap) ====== --}}
          <div class="rounded-xl bg-gradient-to-br from-[#1f1f1f] to-[#151515] border border-white/10 p-12 text-center fade-in">
            <div class="mx-auto mb-6 w-24 h-24 rounded-full bg-gradient-to-br from-red-500/20 to-pink-500/20 grid place-items-center float-animation">
              <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-500/30 to-pink-500/30 grid place-items-center">
                <i class="fas fa-heart text-3xl text-red-400 pulse-slow"></i>
              </div>
            </div>

            <h2 class="text-xl font-bold text-white mb-3">Belum Ada Venue Favorite</h2>
            <p class="text-gray-400 mb-2 max-w-md mx-auto">
              Kamu belum menambahkan venue apapun ke daftar favorite.
            </p>
            <p class="text-sm text-gray-500 mb-8 max-w-md mx-auto">
              Mulai jelajahi venue-venue menarik dan simpan yang kamu suka dengan menekan tombol <i class="fas fa-heart text-red-400"></i>
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
              <a href="{{ route('venues.index') }}"
                 class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[#0a8aff] to-[#0066cc] hover:from-[#0a79e0] hover:to-[#0055b3] px-6 py-3 text-sm font-semibold shadow-lg shadow-blue-500/20 ring-1 ring-white/10 transition-all duration-200 hover:scale-105">
                <i class="fas fa-search"></i>
                <span>Jelajahi Venue</span>
              </a>
              
              <a href="{{ route('dashboard') }}"
                 class="inline-flex items-center gap-2 rounded-lg bg-white/5 hover:bg-white/10 px-6 py-3 text-sm font-semibold border border-white/10 transition-all duration-200">
                <i class="fas fa-home"></i>
                <span>Kembali ke Dashboard</span>
              </a>
            </div>

            <div class="mt-12 pt-8 border-t border-white/10">
              <p class="text-xs text-gray-500 mb-4 font-semibold uppercase tracking-wider">Tips</p>
              <div class="grid sm:grid-cols-3 gap-4 text-left max-w-2xl mx-auto">
                <div class="bg-white/5 rounded-lg p-4 border border-white/5">
                  <div class="w-8 h-8 rounded-full bg-blue-500/20 grid place-items-center mb-3">
                    <i class="fas fa-search text-blue-400 text-sm"></i>
                  </div>
                  <p class="text-xs text-gray-400">Cari venue berdasarkan lokasi atau kategori yang kamu inginkan</p>
                </div>
                <div class="bg-white/5 rounded-lg p-4 border border-white/5">
                  <div class="w-8 h-8 rounded-full bg-red-500/20 grid place-items-center mb-3">
                    <i class="fas fa-heart text-red-400 text-sm"></i>
                  </div>
                  <p class="text-xs text-gray-400">Tekan ikon hati untuk menambahkan venue ke favorite</p>
                </div>
                <div class="bg-white/5 rounded-lg p-4 border border-white/5">
                  <div class="w-8 h-8 rounded-full bg-green-500/20 grid place-items-center mb-3">
                    <i class="fas fa-bookmark text-green-400 text-sm"></i>
                  </div>
                  <p class="text-xs text-gray-400">Akses venue favorite kamu kapan saja dari halaman ini</p>
                </div>
              </div>
            </div>
          </div>

        @else
          {{-- ====== ADA DATA: gambar utama FE /images/venue + fallback berantai aman ====== --}}
          <div id="favGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($favorites as $fav)
              @php
                $venue = $fav->venue ?? null;
              @endphp
              @continue(!$venue)

              @php
                $venueId     = $venue->id;
                $venueName   = $venue->name ?? 'Venue';
                $venueAddr   = $venue->address ?? null;
                $venuePrice  = $venue->price ?? null;
                $venueRating = $venue->rating ?? null;

                // prioritas pertama: image_url yang disimpan saat favorite (kalau ada)
                $candidates = [];
                if (!empty($fav->image_url)) {
                  $candidates[] = (string) $fav->image_url;
                }

                // prioritas kedua: FE base + basename dari $venue->image / $venue->images[0]
                $rawImage = '';
                if (!empty($venue->images)) {
                  $arr = is_array($venue->images) ? $venue->images : (is_string($venue->images) ? json_decode($venue->images, true) : []);
                  if (is_array($arr) && !empty($arr)) $rawImage = (string) $arr[0];
                }
                if ($rawImage === '' && !empty($venue->image)) {
                  $rawImage = (string) $venue->image;
                }
                if ($rawImage !== '') {
                  $name = basename(parse_url($rawImage, PHP_URL_PATH) ?? $rawImage);
                  if ($name && $name !== '/' && $name !== '.') {
                    $candidates[] = $feBase . $name;
                  }
                }

                // fallback: global placeholder (bukan di /images/venue/, supaya tidak 404)
                $candidates[] = 'https://demo-xanders.ptbmn.id/images/placeholder.webp';
                $candidates[] = 'https://demo-xanders.ptbmn.id/images/placeholder.png';
                $candidates[] = 'https://placehold.co/800x600?text=No+Image';

                // unik & bersih
                $candidates = array_values(array_unique(array_filter($candidates, fn($x) => is_string($x) && $x !== '')));

                $primarySrc = $candidates[0] ?? 'https://placehold.co/800x600?text=No+Image';
              @endphp

              <div class="fav-card gradient-border rounded-2xl overflow-hidden bg-[#1f1f1f] border border-white/10 hover:border-white/20 transition-all duration-300 hover:shadow-2xl hover:shadow-white/5 hover:-translate-y-1 fade-in group">
                <div class="h-48 bg-black/20 relative overflow-hidden img-wrapper">
                  <div class="img-loading" aria-hidden="true" role="progressbar"><div class="spinner"></div></div>

                  {{-- IMG dengan kandidat fallback berantai --}}
                  <img
                    src="{{ $primarySrc }}"
                    data-src-candidates='@json($candidates)'
                    alt="{{ $venueName }}"
                    class="w-full h-full object-cover js-fav-img"
                    loading="lazy"
                    decoding="async"
                  />

                  <div class="image-overlay absolute inset-0"></div>

                  {{-- Unfavorite --}}
                  <button
                    class="absolute top-3 right-3 inline-flex items-center gap-2 text-xs font-semibold rounded-full px-3.5 py-2 bg-red-500/95 hover:bg-red-600 text-white shadow-xl shadow-red-500/30 backdrop-blur-sm transition-all duration-200 hover:scale-105 z-10 border border-red-400/30"
                    onclick="toggleFavorite({{ $venueId }}, this)"
                    title="Hapus dari favorite">
                    <i class="fas fa-heart"></i>
                    <span>Favorite</span>
                  </button>

                  @if($venueRating)
                    <div class="absolute bottom-3 left-3 flex items-center gap-1.5 bg-black/70 backdrop-blur-sm rounded-full px-3 py-1.5 border border-white/10">
                      <i class="fas fa-star text-yellow-400 text-xs"></i>
                      <span class="text-white font-semibold text-sm">{{ number_format($venueRating, 1) }}</span>
                    </div>
                  @endif
                </div>

                <div class="p-5">
                  <h3 class="font-bold text-xl leading-6 mb-2 group-hover:text-[#0a8aff] transition-colors" title="{{ $venueName }}">
                    <a href="{{ route('venues.detail', ['venue' => $venueId]) }}" class="line-clamp-2">
                      {{ $venueName }}
                    </a>
                  </h3>

                  @if($venueAddr)
                    <div class="flex items-start gap-2 mb-3">
                      <i class="fas fa-map-marker-alt text-sm text-gray-500 mt-0.5 flex-shrink-0"></i>
                      <p class="text-sm text-gray-400 line-clamp-2">{{ $venueAddr }}</p>
                    </div>
                  @endif

                  <div class="flex items-center justify-between pt-3 border-t border-white/10">
                    @if($venuePrice)
                      <div class="flex flex-col">
                        <span class="text-xs text-gray-500 mb-0.5">Mulai dari</span>
                        <span class="text-lg font-bold text-[#0a8aff]">Rp {{ number_format($venuePrice, 0, ',', '.') }}</span>
                      </div>
                    @else
                      <span class="text-sm text-gray-500">Harga belum tersedia</span>
                    @endif

                    <a href="{{ route('venues.detail', ['venue' => $venueId]) }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white/5 hover:bg-white/10 px-4 py-2 text-sm font-medium border border-white/10 hover:border-white/20 transition-all duration-200 group-hover:bg-[#0a8aff]/10 group-hover:border-[#0a8aff]/30 group-hover:text-[#0a8aff]">
                      <span>Detail</span>
                      <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <div class="mt-10">
            {{ $favorites->links() }}
          </div>
        @endif
      </div>
    </main>
  </div>
</div>

<script>
  // Fallback berantai untuk semua .js-fav-img
  function initFavImages(){
    document.querySelectorAll('.js-fav-img').forEach((img) => {
      const wrapper = img.closest('.img-wrapper');
      const loader  = wrapper ? wrapper.querySelector('.img-loading') : null;

      let list = [];
      try { list = JSON.parse(img.getAttribute('data-src-candidates') || '[]'); } catch(e) { list = []; }
      if (!Array.isArray(list) || list.length === 0) { list = [img.getAttribute('src')].filter(Boolean); }

      let idx = 0;
      const showLoader = () => loader && loader.classList.remove('is-hidden');
      const hideLoader = () => loader && loader.classList.add('is-hidden');
      const markLoaded = () => { img.classList.add('is-loaded'); hideLoader(); };

      if (img.complete && img.naturalWidth > 0) { markLoaded(); }
      else { showLoader(); }

      img.addEventListener('load', () => {
        if (img.naturalWidth > 0) { markLoaded(); }
      }, { passive: true });

      img.addEventListener('error', () => {
        if (idx < list.length - 1) {
          idx++;
          showLoader();
          const nextSrc = list[idx];
          if (nextSrc && img.src !== nextSrc) { img.src = nextSrc; }
        } else {
          // Sudah tidak ada kandidat lain → sembunyikan loader agar tidak muter
          markLoaded();
        }
      }, { passive: true });
    });
  }
  document.addEventListener('DOMContentLoaded', initFavImages);
</script>

<script>
  async function toggleFavorite(venueId, btnEl){
    if(!venueId){ return; } // guard ekstra jika null
    
    // Disable button saat loading
    btnEl.disabled = true;
    btnEl.style.opacity = '0.6';
    
    try {
      const res = await fetch(`{{ url('/venues') }}/${venueId}/favorite`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
        },
      });
      if (!res.ok) {
        if (res.status === 401) {
          window.location.href = '{{ route('login') }}';
          return;
        }
        throw new Error('Request gagal');
      }
      const data = await res.json();

      if (data.action === 'removed') {
        const card = btnEl.closest('.fav-card');
        if (card) {
          card.style.transition = 'all 0.3s ease-out';
          card.style.opacity = '0';
          card.style.transform = 'scale(0.9)';
          setTimeout(() => {
            card.remove();
            const grid = document.getElementById('favGrid');
            if (grid && grid.querySelectorAll('.fav-card').length === 0) {
              window.location.reload(); // tampilkan empty state
            }
          }, 300);
        }
      } else {
        const label = btnEl.querySelector('span');
        if (label) label.textContent = 'Unfavorite';
        btnEl.disabled = false;
        btnEl.style.opacity = '1';
      }
    } catch (e) {
      console.error('Error:', e);
      alert('Gagal mengubah favorite. Coba lagi.');
      btnEl.disabled = false;
      btnEl.style.opacity = '1';
    }
  }

  // Delay animasi per-card (optional)
  document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.fav-card');
    cards.forEach((card, i) => { card.style.animationDelay = `${i * 0.06}s`; });
  });
</script>
@endsection
