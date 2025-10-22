@extends('app')
@section('title', 'Edit Sparring Schedule')

@push('styles')
<style>
  /* ===== Global anti white-flash / rubber-band ===== */
  :root {
    color-scheme: dark;
    --page-bg: #0a0a0a;
  }

  html,
  body {
    height: 100%;
    min-height: 100%;
    background: var(--page-bg);
    overscroll-behavior-y: none;
    /* cegah chaining ke body */
    overscroll-behavior-x: none;
    touch-action: pan-y;
    /* iOS Safari: tetap bisa scroll vertikal */
    -webkit-text-size-adjust: 100%;
  }

  /* Kanvas gelap “di belakang segalanya” saat bounce */
  #antiBounceBg {
    position: fixed;
    left: 0;
    right: 0;
    top: -120svh;
    /* svh stabil di mobile */
    bottom: -120svh;
    background: var(--page-bg);
    z-index: -1;
    pointer-events: none;
  }

  /* Pastikan wrapper gelap */
  #app,
  main {
    background: var(--page-bg);
  }

  /* Container scroll aman dari white glow */
  .scroll-safe {
    background-color: #171717;
    /* senada bg-neutral-900 */
    overscroll-behavior: contain;
    /* hentikan overscroll di container ini */
    -webkit-overflow-scrolling: touch;
  }

  /* existing card styles */
  .card {
    background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02)), #232323;
    border: 1px solid rgba(255, 255, 255, .08);
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
  }

  .label {
    color: #a3a3a3;
    font-size: .8rem;
    margin-bottom: .35rem;
    display: block;
  }

  .field {
    width: 100%;
    background: #121212e6;
    color: #fff;
    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: .65rem;
    padding: .7rem .9rem;
    font-size: .95rem;
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
  }

  .field:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(96, 165, 250, .25);
    background: #0f0f10;
  }

  .hint {
    font-size: .75rem;
    color: #9ca3af;
  }

  .divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .12), transparent);
  }
</style>
@endpush

@section('content')
<!-- Layer gelap anti-bounce -->
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar.athlete')

    <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
      @include('partials.topbar')

      <div class="mt-12 sm:mt-28 px-4 sm:px-8">
        <div class="max-w-4xl mx-auto">
          <!-- Page Header -->
          <div class="mb-6 flex items-center justify-between">
            <div>
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Edit Sparring Schedule</h1>
              <p class="text-sm text-gray-400 mt-1">Perbarui jadwal sparring kamu.</p>
            </div>
            <a href="{{ route('athlete.sparring') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-700 hover:bg-gray-600 active:bg-gray-500 transition-colors text-white font-semibold px-4 py-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back
            </a>
          </div>

          <!-- Flash Messages -->
          @if(session('success'))
          <div class="mb-4 bg-green-500 text-white px-4 py-2 rounded text-sm">
            {{ session('success') }}
          </div>
          @endif
          @if(session('error'))
          <div class="mb-4 bg-red-500 text-white px-4 py-2 rounded text-sm">
            {{ session('error') }}
          </div>
          @endif

          <!-- Errors / Alerts -->
          @if ($errors->any())
          <div class="mb-4 bg-red-500 text-white px-4 py-2 rounded text-sm">
            <p class="font-semibold mb-1">Periksa kembali input kamu:</p>
            <ul class="list-disc list-inside text-xs space-y-1">
              @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif

          <!-- Form -->
          <form id="editSessionForm" action="{{ route('athlete.sparring.update', $schedule) }}" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @csrf
            @method('PUT')
            <section class="card p-4 sm:p-6">
              <h2 class="text-lg font-semibold mb-4">Schedule</h2>
              <div class="divider mb-4"></div>

              <div class="space-y-4">
                <!-- Date -->
                <div>
                  <label class="label" for="date">Date</label>
                  <input
                    id="date"
                    type="date"
                    name="date"
                    class="field text-right"
                    value="{{ old('date', $schedule->date->format('Y-m-d')) }}" />
                  @error('date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Time Range -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="label" for="start_time">Time Start</label>
                    <input
                      id="start_time"
                      type="time"
                      name="start_time"
                      class="field text-right"
                      value="{{ old('start_time', $schedule->start_time->format('H:i')) }}" />
                    @error('start_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                  <div>
                    <label class="label" for="end_time">Time End</label>
                    <input
                      id="end_time"
                      type="time"
                      name="end_time"
                      class="field text-right"
                      value="{{ old('end_time', $schedule->end_time->format('H:i')) }}" />
                    @error('end_time') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                </div>

                <!-- Booking Status -->
                <div>
                  <label class="label" for="is_booked">Booking Status</label>
                  <select id="is_booked" name="is_booked" class="field">
                    <option value="0" {{ old('is_booked', $schedule->is_booked) == 0 ? 'selected' : '' }}>Available</option>
                    <option value="1" {{ old('is_booked', $schedule->is_booked) == 1 ? 'selected' : '' }}>Booked</option>
                  </select>
                  @error('is_booked') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Submit -->
                <div class="pt-2">
                  <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition-colors text-white font-semibold py-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Update Session
                  </button>
                </div>
              </div>
            </section>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // ====== Format ribuan Indonesia untuk Total Amount ======
  const nfID = new Intl.NumberFormat('id-ID');

  function onlyDigits(v) {
    return (v || '').toString().replace(/[^\d]/g, '');
  }

  function formatRupiah(el) {
    const raw = onlyDigits(el.value);
    el.value = raw ? nfID.format(parseInt(raw, 10)) : '';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const amountEl = document.getElementById('total_amount');
    const form = document.getElementById('editSessionForm');

    if (amountEl) {
      // Inisialisasi tampilan jika ada old value dari server
      if (amountEl.value) formatRupiah(amountEl);

      // Format saat mengetik & saat blur
      amountEl.addEventListener('input', () => formatRupiah(amountEl));
      amountEl.addEventListener('blur', () => formatRupiah(amountEl));
    }

    // Sebelum submit: ubah menjadi angka murni (tanpa titik) agar backend menerima integer
    if (form) {
      form.addEventListener('submit', () => {
        if (amountEl) amountEl.value = onlyDigits(amountEl.value);
      });
    }
  });
</script>
@endpush