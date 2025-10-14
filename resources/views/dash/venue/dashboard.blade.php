@extends('app')
@section('title', 'Venue Dashboard')

@push('styles')
<style>
  /* ====== Anti white flash / rubber-band iOS ====== */
  :root { color-scheme: dark; }

  /* Pastikan SEMUA root benar-benar gelap */
  html, body { 
    height: 100%;
    background: #0a0a0a;
    /* Matikan overscroll glow/bounce */
    overscroll-behavior-y: none;
    overscroll-behavior-x: none;
    touch-action: pan-y;               /* iOS Safari: tetap bisa scroll vertikal */
    -webkit-text-size-adjust: 100%;
    scrollbar-gutter: stable both-edges;
  }

  /* Kanvas gelap fixed di belakang segalanya, memanjang melebihi viewport
     agar saat rubber-band yang terlihat tetap gelap (bukan putih) */
  #antiBounceBg{
    position: fixed;
    inset: -20svh 0 -20svh 0;         /* tambah area atas & bawah */
    background: #0a0a0a;
    pointer-events: none;
    z-index: -1;
  }

  /* Semua kontainer utama juga gelap */
  #app, main, .min-h-screen { background: #0a0a0a; }

  /* Pembungkus halaman: cegah bleed horizontal & jaga gelap */
  .page-wrap{
    background: #0a0a0a;
    overflow-x: clip;                  /* cegah white gap horizontal saat momentum scroll */
  }

  /* Scroll area utama (main) tetap gelap & tidak men-“tembus” ke body */
  main{
    overscroll-behavior-y: contain;    /* tahan propagate bounce ke body */
    background: #0a0a0a;
  }

  /* Kartu & panel mengikuti tema gelapmu */
  .panel-dark{
    background: #292929;
    border-radius: .75rem;
    box-shadow: 0 10px 30px rgba(0,0,0,.35);
  }
</style>
@endpush

@section('content')
  <!-- Kanvas gelap anti-bounce -->
  <div id="antiBounceBg" aria-hidden="true"></div>

  <div class="page-wrap text-white">
    <div class="flex flex-col min-h-[100dvh] bg-neutral-900 font-sans">
      <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')

        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-6 md:my-8">
          @include('partials.topbar')

          <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">
            Dashboard Venue
          </h1>

          <section class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
            @if($venue)
              {{-- Row 1: Statistik Utama (3 Kartu) --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-4 sm:mb-6 md:mb-8">
                {{-- Kartu Ratings --}}
                <div class="panel-dark p-4 sm:p-5 md:p-6 shadow-md flex">
                  @include('dash.venue.components.dashboard.ratings')
                </div>

                {{-- Kartu Monthly Earnings --}}
                <div class="panel-dark sm:col-span-2 p-4 sm:p-5 md:p-6 shadow-md h-full">
                  @include('dash.venue.components.dashboard.monthly-earnings', [
                    'monthlyEarnings'   => $monthlyEarnings,
                    'lastMonthEarnings' => $lastMonthEarnings,
                    'percentageChange'  => $percentageChange
                  ])
                </div>

                {{-- Kartu Session Purchased --}}
                <div class="panel-dark sm:col-span-2 p-4 sm:p-5 md:p-6 shadow-md">
                  @include('dash.venue.components.dashboard.session-purchased')
                </div>
              </div>

              {{-- Row 2: Grafik dan Notifikasi --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-4 sm:mb-6 md:mb-8">
                {{-- Komponen Sales Report --}}
                <div class="panel-dark sm:col-span-2 md:col-span-3 p-4 sm:p-5 md:p-6 shadow-md">
                  @include('dash.venue.components.dashboard.sales-report')
                </div>

                {{-- Komponen Notification --}}
                <div class="panel-dark sm:col-span-2 p-4 sm:p-5 md:p-6 shadow-md">
                  @include('dash.venue.components.dashboard.notification')
                </div>
              </div>

              {{-- Row 3: Transaksi Terbaru dan Review --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4">
                {{-- Komponen Recent Transaction --}}
                <div class="panel-dark sm:col-span-2 md:col-span-3 p-3 sm:p-4 shadow-md">
                  @include('dash.venue.components.dashboard.recent-transaction')
                </div>

                {{-- Komponen Review --}}
                <div class="panel-dark sm:col-span-2 p-3 sm:p-4 shadow-md">
                  @include('dash.venue.components.dashboard.review')
                </div>
              </div>
            @else
              <div class="bg-red-500/20 border border-red-500 rounded-lg p-4 sm:p-5 md:p-6 shadow-md">
                <h2 class="text-lg sm:text-xl font-bold mb-2">Data Venue Tidak Ditemukan</h2>
                <p class="text-sm sm:text-base">Kamu belum memiliki data venue. Silakan hubungi admin untuk menambahkan data venue kamu.</p>
              </div>
            @endif
          </section>
        </main>
      </div>
    </div>
  </div>
@endsection
