@extends('app')
@section('title', 'Admin Dashboard - Edit Event')

@push('styles')
<style>
  /* ====== Dark base & anti bounce ====== */
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  html, body{
    height:100%;
    min-height:100%;
    background:var(--page-bg);
    overscroll-behavior-y: none;
    overscroll-behavior-x: none;
    touch-action: pan-y;
    -webkit-text-size-adjust:100%;
  }
  #antiBounceBg{
    position: fixed; left:0; right:0; top:-120svh; bottom:-120svh;
    background:var(--page-bg); z-index:-1; pointer-events:none;
  }
  .scroll-safe{
    background-color:#171717; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;
  }

  /* ====== Card polish ====== */
  .card{ background:#262626; border:1px solid #3b3b3b; border-radius:0.75rem; }
  .card h2{ font-weight:700; font-size:0.95rem; padding-bottom:0.5rem; border-bottom:1px dashed #3f3f3f; color:#e5e5e5; }

  /* ====== Inputs ====== */
  .form-label{ color:#9ca3af; font-size:0.75rem; margin-bottom:0.25rem; display:block; }
  .form-input{
      width:100%; border:1px solid #525252; background:#1f1f1f; color:#fff;
      padding:0.55rem 0.75rem; border-radius:0.5rem; font-size:0.9rem; outline: none;
  }
  .form-input:focus{ border-color:#60a5fa; box-shadow:0 0 0 2px rgba(96,165,250,.25); }

  .btn{ display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.6rem 1rem; border-radius:.5rem; font-weight:600; font-size:.9rem; transition:.15s ease; }
  .btn-primary{ background:#2563eb; color:#fff; } .btn-primary:hover{ background:#1d4ed8; }
  .btn-outline-danger{ border:1px solid #dc2626; color:#dc2626; } .btn-outline-danger:hover{ background:#dc2626; color:#fff; }

  .grid-2{ display:grid; grid-template-columns:1fr; gap:1rem; }
  @media (min-width:1024px){ .grid-2{ grid-template-columns:1fr 1fr; } }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

@php
  $startDateValue = $event->start_date instanceof \Carbon\Carbon
      ? $event->start_date->format('Y-m-d')
      : ($event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('Y-m-d') : '');

  $endDateValue = $event->end_date instanceof \Carbon\Carbon
      ? $event->end_date->format('Y-m-d')
      : ($event->end_date ? \Carbon\Carbon::parse($event->end_date)->format('Y-m-d') : '');

  // Ambil nilai mentah dari DB/old() sebagai integer untuk hidden
  $raw_price_ticket      = (int) old('price_ticket',      $event->price_ticket);
  $raw_price_ticket_player      = (int) old('price_ticket_player',      $event->price_ticket_player);
  $raw_total_prize       = (int) old('total_prize_money', $event->total_prize_money);
  $raw_champion_prize    = (int) old('champion_prize',    $event->champion_prize);
  $raw_runner_up_prize   = (int) old('runner_up_prize',   $event->runner_up_prize);
  $raw_third_place_prize = (int) old('third_place_prize', $event->third_place_prize);

  // Nilai untuk tampilan awal (diformat)
  $fmt = fn($n) => $n ? number_format((int)$n, 0, ',', '.') : '';

  // Normalisasi URL gambar saat ini
  $currentImg = null;
  if (!empty($event->image_url)) {
      $raw = trim($event->image_url);
      $currentImg = preg_match('/^https?:\/\//i', $raw) ? $raw : asset('images/events/' . basename($raw));
  }
@endphp

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
      @include('partials.topbar')

      <div class="mt-20 px-4 sm:px-8 max-w-7xl mx-auto w-full">
        <!-- Header -->
        <div class="flex items-center justify-between gap-3 mb-6">
          <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold">Edit Event: {{ $event->name }}</h1>
            <p class="text-sm text-gray-400 mt-1">Perbarui informasi event dengan rapi dan konsisten.</p>
          </div>
        </div>

        <!-- Alerts -->
        @if (session('success'))
          <div class="mb-4 rounded-lg border border-green-700 bg-green-900/30 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
          <div class="mb-4 rounded-lg border border-red-700 bg-red-900/30 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <!-- Form -->
        <form method="POST"
              action="{{ route('admin.event.update', $event->id) }}"
              enctype="multipart/form-data"
              id="eventEditForm"
              class="grid-2">
          @csrf
          @method('PUT')

          <!-- KIRI: Info Utama -->
          <section class="card p-5 sm:p-7">
            <h2>Informasi Event</h2>

            <div class="mt-5 space-y-5">
              {{-- Nama Event --}}
              <div>
                <label class="form-label" for="name">Nama Event</label>
                <input class="form-input" id="name" name="name" type="text"
                       value="{{ old('name', $event->name) }}"
                       placeholder="Masukkan nama event">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
              </div>

              {{-- Deskripsi --}}
              <div>
                <label class="form-label" for="description">Deskripsi</label>
                <textarea class="form-input whitespace-pre-wrap break-words" id="description" name="description" rows="4"
                          placeholder="Masukkan deskripsi">{{ old('description', $event->description) }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                {{-- Di halaman show gunakan {!! nl2br(e($event->description)) !!} agar line break tampil. --}}
              </div>

              {{-- Jenis Game & Lokasi --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="form-label" for="game_types">Jenis Game</label>
                  <input class="form-input" id="game_types" name="game_types" type="text"
                         value="{{ old('game_types', $event->game_types) }}"
                         placeholder="Contoh: 8 Ball, 9 Ball">
                  @error('game_types') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                  <label class="form-label" for="location">Lokasi</label>
                  <input class="form-input" id="location" name="location" type="text"
                         value="{{ old('location', $event->location) }}"
                         placeholder="Masukkan lokasi event">
                  @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
              </div>

              {{-- Tanggal Mulai & Selesai --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="form-label" for="start_date">Tanggal Mulai</label>
                  <input class="form-input" id="start_date" name="start_date" type="date"
                         value="{{ old('start_date', $startDateValue) }}">
                  @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                  <label class="form-label" for="end_date">Tanggal Selesai</label>
                  <input class="form-input" id="end_date" name="end_date" type="date"
                         value="{{ old('end_date', $endDateValue) }}">
                  @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
              </div>

              {{-- Biaya & Stok --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="form-label" for="price_ticket_view">Harga Tiket (Rp)</label>
                  {{-- INPUT TAMPILAN --}}
                  <input class="form-input" id="price_ticket_view" type="text"
                         inputmode="numeric" autocomplete="off"
                         value="{{ $fmt($raw_price_ticket) }}"
                         placeholder="Misal: 150.000">
                  {{-- INPUT MENTAH (DIKIRIM) --}}
                  <input type="hidden" id="price_ticket" name="price_ticket"
                         value="{{ $raw_price_ticket }}">
                  @error('price_ticket') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                  <label class="form-label" for="stock">Stok</label>
                  <input class="form-input" id="stock" name="stock" type="number"
                         value="{{ old('stock', $event->stock) }}"
                         placeholder="Masukkan stok tiket">
                  @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="form-label" for="price_ticket_player_show">Harga Tiket Pemain (Rp)</label>
                  {{-- INPUT TAMPILAN --}}
                  <input class="form-input" id="price_ticket_player_show" type="text"
                         inputmode="numeric" autocomplete="off"
                         value="{{ $fmt($raw_price_ticket_player) }}"
                         placeholder="Misal: 150.000">
                  {{-- INPUT MENTAH (DIKIRIM) --}}
                  <input type="hidden" id="price_ticket_player" name="price_ticket_player"
                         value="{{ $raw_price_ticket_player }}">
                  @error('price_ticket_player') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                  <label class="form-label" for="player_slots">Stok</label>
                  <input class="form-input" id="player_slots" name="player_slots" type="number"
                         value="{{ old('player_slots', $event->player_slots) }}"
                         placeholder="Masukkan stok tiket">
                  @error('player_slots') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
              </div>
            </div>
          </section>

          <!-- KANAN: Hadiah & Gambar -->
          <section class="flex flex-col gap-5">
            <div class="card p-5 sm:p-7">
              <h2>Hadiah & Breakdown</h2>

              <div class="mt-5 space-y-5">
                {{-- Total Hadiah --}}
                <div>
                  <label class="form-label" for="total_prize_money_view">Total Hadiah (Rp)</label>
                  <input class="form-input" id="total_prize_money_view" type="text"
                         inputmode="numeric" autocomplete="off"
                         value="{{ $fmt($raw_total_prize) }}"
                         placeholder="Misal: 50.000.000">
                  <input type="hidden" id="total_prize_money" name="total_prize_money"
                         value="{{ $raw_total_prize }}">
                  @error('total_prize_money') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  {{-- Juara 1 --}}
                  <div>
                    <label class="form-label" for="champion_prize_view">Juara 1 (Rp)</label>
                    <input class="form-input" id="champion_prize_view" type="text"
                           inputmode="numeric" autocomplete="off"
                           value="{{ $fmt($raw_champion_prize) }}"
                           placeholder="Misal: 25.000.000">
                    <input type="hidden" id="champion_prize" name="champion_prize"
                           value="{{ $raw_champion_prize }}">
                    @error('champion_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                  {{-- Juara 2 --}}
                  <div>
                    <label class="form-label" for="runner_up_prize_view">Juara 2 (Rp)</label>
                    <input class="form-input" id="runner_up_prize_view" type="text"
                           inputmode="numeric" autocomplete="off"
                           value="{{ $fmt($raw_runner_up_prize) }}"
                           placeholder="Misal: 15.000.000">
                    <input type="hidden" id="runner_up_prize" name="runner_up_prize"
                           value="{{ $raw_runner_up_prize }}">
                    @error('runner_up_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                  {{-- Juara 3 --}}
                  <div class="sm:col-span-2">
                    <label class="form-label" for="third_place_prize_view">Juara 3 (Rp)</label>
                    <input class="form-input" id="third_place_prize_view" type="text"
                           inputmode="numeric" autocomplete="off"
                           value="{{ $fmt($raw_third_place_prize) }}"
                           placeholder="Misal: 10.000.000">
                    <input type="hidden" id="third_place_prize" name="third_place_prize"
                           value="{{ $raw_third_place_prize }}">
                    @error('third_place_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                </div>
              </div>
            </div>

            <div class="card p-5 sm:p-7">
              <h2>Gambar Event</h2>

              <div class="mt-5 space-y-4">
                <div>
                  <label class="form-label" for="image_url">Upload Gambar</label>
                  <input class="form-input" id="image_url" name="image_url" type="file" accept="image/*">
                  @error('image_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                @if($currentImg)
                  <div>
                    <p class="text-xs text-gray-400 mb-2">Gambar saat ini:</p>
                    <img src="{{ $currentImg }}" alt="{{ $event->name }}"
                         class="w-56 max-w-full rounded-md border border-gray-700 object-cover">
                  </div>
                @endif
              </div>
            </div>

            <div class="flex items-center justify-end gap-3">
              <a href="{{ route('admin.event.index') }}" class="btn btn-outline-danger">Batal</a>
              <button type="submit" class="btn btn-primary">Update</button>
            </div>
          </section>
        </form>
      </div>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // ===== Helper number-format (Rupiah, tanpa simbol) =====
  const nfID = new Intl.NumberFormat('id-ID');
  const onlyDigits = (v) => (v || '').toString().replace(/[^\d]/g, '');

  // Sinkronisasi 2 input: *_view (tampilan) <-> hidden (nilai mentah)
  function bindRupiahPair(viewId, hiddenId){
      const viewEl   = document.getElementById(viewId);
      const hiddenEl = document.getElementById(hiddenId);
      if(!viewEl || !hiddenEl) return;

      // Inisialisasi tampilan dari nilai hidden
      const initRaw = onlyDigits(hiddenEl.value);
      viewEl.value = initRaw ? nfID.format(parseInt(initRaw, 10)) : '';

      // Saat user mengetik di tampilan -> update hidden (digits) & keep formatted di view
      viewEl.addEventListener('input', () => {
          const raw = onlyDigits(viewEl.value);
          hiddenEl.value = raw === '' ? 0 : parseInt(raw, 10);
          viewEl.value   = raw ? nfID.format(parseInt(raw, 10)) : '';
      });

      // Saat blur, rapikan lagi tampilannya
      viewEl.addEventListener('blur', () => {
          const raw = onlyDigits(viewEl.value);
          viewEl.value = raw ? nfID.format(parseInt(raw, 10)) : '';
      });
  }

  document.addEventListener('DOMContentLoaded', function() {
      // Pasangkan semua field rupiah
      bindRupiahPair('price_ticket_view',      'price_ticket');
      bindRupiahPair('price_ticket_player_show',      'price_ticket_player');
      bindRupiahPair('total_prize_money_view', 'total_prize_money');
      bindRupiahPair('champion_prize_view',    'champion_prize');
      bindRupiahPair('runner_up_prize_view',   'runner_up_prize');
      bindRupiahPair('third_place_prize_view', 'third_place_prize');

      // Saat submit: pastikan hidden tetap digits
      const form = document.getElementById('eventEditForm');
      form.addEventListener('submit', () => {
          ['price_ticket', 'price_ticket_player','total_prize_money','champion_prize','runner_up_prize','third_place_prize']
              .forEach(id => {
                  const el = document.getElementById(id);
                  if (el) {
                      const raw = onlyDigits(el.value);
                      el.value = raw === '' ? 0 : parseInt(raw, 10);
                  }
              });
      });
  });
</script>
@endpush
