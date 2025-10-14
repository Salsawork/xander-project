@extends('app')
@section('title', 'Create Table')

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

  /* Kanvas gelap tetap di belakang saat rubber-band (melebar di atas & bawah) */
  #antiBounceBg{
    position:fixed;
    inset:-20svh 0 -20svh 0;
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

  /* Panel gelap (kartu) konsisten */
  .panel-dark{
    background:#262626;
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
            Create Table
          </h1>

          <form method="POST" action="{{ route('venue.booking.store-table') }}"
                class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
            @csrf

            <section aria-labelledby="general-info-title"
                     class="panel-dark p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8 w-full">
              <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">
                General Information
              </h2>

              <div class="space-y-4 sm:space-y-6">
                <div>
                  <label class="block text-sm text-gray-400 mb-1.5" for="table-number">
                    Table Number
                  </label>
                  <input
                    id="table-number"
                    name="table_number"
                    type="text"
                    placeholder="Enter table number"
                    value="{{ old('table_number') }}"
                    required
                    autofocus
                    class="w-full rounded-lg border border-gray-600 bg-[#262626] px-4 py-2.5 text-base text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm text-gray-400 mb-1.5" for="status">
                    Status
                  </label>
                  <select
                    id="status"
                    name="status"
                    required
                    class="w-full rounded-lg border border-gray-600 bg-[#262626] px-4 py-2.5 text-base text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <option disabled {{ old('status') ? '' : 'selected' }}>Please choose status</option>
                    <option value="available" {{ old('status')==='available' ? 'selected' : '' }}>Available</option>
                    <option value="booked"    {{ old('status')==='booked'    ? 'selected' : '' }}>Booked</option>
                  </select>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-4">
                {{-- Discard: kembali ke halaman sebelumnya (bukan reset form) --}}
                <button
                  id="btnDiscard"
                  type="button"
                  class="w-full sm:w-auto order-2 sm:order-1 px-6 py-2.5 border-2 border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-colors"
                  data-fallback="{{ url('/') }}"
                >
                  Discard
                </button>

                <button
                  class="w-full sm:w-auto order-1 sm:order-2 px-6 py-2.5 bg-[#0a8cff] rounded-lg hover:bg-[#0077e6] transition-colors"
                  type="submit"
                >
                  Save
                </button>
              </div>
            </section>
          </form>
        </main>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Pastikan tombol Discard bisa "kembali"
  (function(){
    const btn = document.getElementById('btnDiscard');
    if(!btn) return;

    btn.addEventListener('click', function(){
      try{
        // Jika referrer masih satu origin, gunakan history.back()
        if (document.referrer) {
          const ref = new URL(document.referrer);
          if (ref.origin === window.location.origin) {
            history.back();
            return;
          }
        }
      }catch(e){/* noop */}

      // Fallback: arahkan ke halaman sebelumnya (server) atau root
      const fb = btn.getAttribute('data-fallback') || '/';
      window.location.href = fb;
    });
  })();
</script>
@endpush
