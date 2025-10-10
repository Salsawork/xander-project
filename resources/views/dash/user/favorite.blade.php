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
  .scroll-inner{ overscroll-behavior: contain; background:var(--page-bg); }
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

<div class="min-h-screen bg-neutral-900 text-white scroll-root">
  <div class="flex min-h-[100dvh]">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 my-8 bg-neutral-900 scroll-root">
      @include('partials.topbar')

      <div class="max-w-6xl mt-16 mx-6 md:mx-16 scroll-inner">
        <h1 class="text-2xl font-extrabold mb-6">My Favorites</h1>

        @if (session('success'))
          <div class="mb-4 rounded-md bg-green-600/20 text-green-300 px-4 py-3 border border-green-600/30">
            {{ session('success') }}
          </div>
        @endif

        @if ($favorites->isEmpty())
          <div class="rounded-xl bg-[#1f1f1f] border border-white/10 p-8 text-center">
            <div class="mx-auto mb-4 w-16 h-16 rounded-full bg-white/5 grid place-items-center">
              <i class="fas fa-heart text-xl text-gray-300"></i>
            </div>
            <p class="text-gray-300">Belum ada venue yang kamu tambahkan ke favorit.</p>
            <a href="{{ route('venues.index') }}"
               class="inline-flex items-center mt-4 rounded-md bg-[#0a8aff] hover:bg-[#0a79e0] px-4 py-2 text-sm font-semibold shadow ring-1 ring-white/10">
              Jelajah Venue
            </a>
          </div>
        @else
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($favorites as $fav)
              @php
                $venue = $fav->venue;
              @endphp
              <div class="rounded-xl overflow-hidden bg-[#1f1f1f] border border-white/10">
                <div class="h-40 bg-black/20 relative">
                  {{-- Placeholder gambar sederhana --}}
                  <svg viewBox="0 0 400 160" class="w-full h-full">
                    <defs>
                      <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#2a2a2a"/>
                        <stop offset="100%" stop-color="#1f1f1f"/>
                      </linearGradient>
                    </defs>
                    <rect width="400" height="160" fill="url(#g)"/>
                    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#aaa" font-size="16">
                      Venue Image
                    </text>
                  </svg>

                  <button
                    class="absolute top-3 right-3 inline-flex items-center gap-2 text-xs font-medium rounded-full px-3 py-1.5 bg-white/10 hover:bg-white/20"
                    onclick="toggleFavorite({{ $venue->id }}, this)">
                    <i class="fas fa-heart"></i>
                    <span>Unfavorite</span>
                  </button>
                </div>

                <div class="p-4">
                  <h3 class="font-bold text-lg leading-6 truncate" title="{{ $venue->name ?? 'Venue' }}">
                    <a href="{{ route('venues.detail', $venue) }}" class="hover:underline">
                      {{ $venue->name ?? 'Venue' }}
                    </a>
                  </h3>
                  @isset($venue->address)
                    <p class="text-sm text-gray-400 mt-1 line-clamp-2">{{ $venue->address }}</p>
                  @endisset
                </div>
              </div>
            @endforeach
          </div>

          <div class="mt-8">
            {{ $favorites->links() }}
          </div>
        @endif
      </div>
    </main>
  </div>
</div>

<script>
  async function toggleFavorite(venueId, btnEl){
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
          window.location.href = "{{ route('login') }}";
          return;
        }
        throw new Error('Request gagal');
      }
      const data = await res.json();
      // Optimistic: kalau dihapus, sembunyikan card
      if (data.action === 'removed') {
        const card = btnEl.closest('.rounded-xl');
        if (card) card.remove();
        // Jika semua card habis, reload untuk empty state/pagination segar
        if (document.querySelectorAll('.rounded-xl.bg-\\[\\#1f1f1f\\]').length === 0) {
          window.location.reload();
        }
      } else {
        // Jika ditambahkan lagi (jarang di halaman ini), ubah label
        btnEl.querySelector('span').textContent = 'Unfavorite';
      }
    } catch (e) {
      alert('Gagal mengubah favorit. Coba lagi.');
    }
  }
</script>
@endsection
