@extends('app')
@section('title', 'Venue Booking')

@push('styles')
<style>
  /* ===== Anti white flash / rubber-band iOS ===== */
  :root { color-scheme: dark; }

  /* Pastikan root selalu gelap & tidak memantul menembus body */
  html, body{
    height:100%;
    background:#0a0a0a;                /* kanvas dasar gelap */
    overscroll-behavior-y:none;        /* cegah bounce propagate */
    overscroll-behavior-x:none;
    touch-action:pan-y;                /* iOS Safari: tetap bisa scroll vertikal */
    -webkit-text-size-adjust:100%;
    scrollbar-gutter:stable both-edges;
  }

  /* Kanvas gelap tetap di belakang saat rubber-band */
  #antiBounceBg{
    position:fixed;
    inset:-20svh 0 -20svh 0;           /* perpanjang atas & bawah */
    background:#0a0a0a;
    pointer-events:none;
    z-index:-1;
  }

  /* Semua kontainer utama juga gelap */
  #app, main, .min-h-screen { background:#0a0a0a; }

  /* Pembungkus halaman: cegah white gap horizontal saat momentum scroll */
  .page-wrap{
    background:#0a0a0a;
    overflow-x:clip;
  }

  /* Scroll area utama (main) tidak menembus ke body */
  main{
    overscroll-behavior-y:contain;
    background:#0a0a0a;
  }

  /* Panel gelap (opsional, mengikuti gaya kamu) */
  .panel-dark{
    background:#292929;
    border-radius:.75rem;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
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

        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-8 md:my-8">
          @include('partials.topbar')

          <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">
            Booking Management
          </h1>

          <section class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
            <div class="md:hidden grid grid-cols-1 gap-3 sm:gap-4">
              {{-- Available & Booked Count --}}
              <div class="panel-dark p-4 sm:p-5 shadow-md flex flex-col items-center justify-center min-h-[150px]">
                @include('dash.venue.components.booking.avail-booked', ['availableCount' => $availableCount, 'bookedCount' => $bookedCount])
              </div>

              {{-- Table List --}}
              <div class="panel-dark p-4 sm:p-5 shadow-md h-auto">
                @include('dash.venue.components.booking.table-list', ['tables' => $tables])
              </div>

              {{-- Price & Schedule --}}
              <div class="panel-dark p-4 sm:p-5 shadow-md">
                @include('dash.venue.components.booking.price-schedule', ['priceSchedules' => $priceSchedules])
              </div>
            </div>

            <div class="hidden md:grid md:grid-cols-5 gap-3 sm:gap-4">
              {{-- Table List - Menggunakan auto-height --}}
              <div class="col-span-1 sm:col-span-2 md:col-span-3 panel-dark p-4 sm:p-5 md:p-6 shadow-md h-auto sm:h-[500px] md:h-130">
                @include('dash.venue.components.booking.table-list', ['tables' => $tables])
              </div>

              <div class="col-span-1 sm:col-span-2 md:col-span-2 flex flex-col gap-3 sm:gap-4">
                {{-- Available & Booked Count --}}
                <div class="panel-dark p-4 sm:p-5 md:p-6 shadow-md flex flex-col items-center justify-center min-h-[150px]">
                  @include('dash.venue.components.booking.avail-booked', ['availableCount' => $availableCount, 'bookedCount' => $bookedCount])
                </div>

                {{-- Price & Schedule --}}
                <div class="panel-dark p-4 sm:p-5 md:p-6 shadow-md flex-grow">
                  @include('dash.venue.components.booking.price-schedule', ['priceSchedules' => $priceSchedules])
                </div>
              </div>
            </div>
          </section>
        </main>
      </div>
    </div>
  </div>
@endsection
