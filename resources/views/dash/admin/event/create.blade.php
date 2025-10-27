@extends('app')
@section('title', 'Admin Dashboard - Tambah Event')

@push('styles')
<style>
    /* ====== Anti overscroll / white bounce ====== */
    :root{ color-scheme: dark; --page-bg:#0a0a0a; }
    html, body{
        height:100%; min-height:100%; background:var(--page-bg);
        overscroll-behavior-y: none; overscroll-behavior-x: none; touch-action: pan-y;
        -webkit-text-size-adjust:100%;
    }
    #antiBounceBg{ position: fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
    .scroll-safe{ background-color:#171717; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }

    .card{ background:#262626; border:1px solid #3b3b3b; border-radius:0.75rem; }
    .form-label{ color:#9ca3af; font-size:0.75rem; margin-bottom:0.25rem; display:block; }
    .form-input{
        width:100%; border:1px solid #525252; background:#1f1f1f; color:#fff;
        padding:0.55rem 0.75rem; border-radius:0.5rem; font-size:0.9rem; outline: none;
    }
    .form-input:focus{ border-color:#60a5fa; box-shadow:0 0 0 2px rgba(96,165,250,.25); }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar')
    <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
      @include('partials.topbar')

      <div class="mt-20 sm:mt-0 px-4 sm:px-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold my-6 sm:my-8">Tambah Event Baru</h1>

        @if ($errors->any())
          <div class="mb-4 rounded-lg border border-red-700 bg-red-900/30 px-4 py-3 text-sm">
            <ul class="list-disc pl-5">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.event.store') }}" enctype="multipart/form-data" class="card p-6 sm:p-8 space-y-6" id="eventForm">
          @csrf

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="form-label" for="name">Nama Event</label>
              <input name="name" value="{{ old('name') }}" id="name" type="text" class="form-input" placeholder="Masukkan nama event">
              @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="form-label" for="location">Lokasi</label>
              <input name="location" value="{{ old('location') }}" id="location" type="text" class="form-input" placeholder="Masukkan lokasi event">
              @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Biaya (Rupiah) pakai pair: *_view + hidden name="price_ticket" --}}
            <div>
              <label class="form-label" for="price_ticket_view">Harga Tiket (Rp)</label>
              <input id="price_ticket_view" type="text" inputmode="numeric" autocomplete="off" class="form-input" placeholder="Misal: 150.000"
                     value="{{ old('price_ticket') ? number_format((int)preg_replace('/\D/','', old('price_ticket')), 0, ',', '.') : '' }}">
              <input type="hidden" id="price_ticket" name="price_ticket" value="{{ (int)preg_replace('/\D/','', old('price_ticket', '')) }}">
              @error('price_ticket') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Stok --}}
            <div>
              <label class="form-label" for="stock">Stok Tiket</label>
              <input name="stock" value="{{ old('stock') }}" id="stock" type="number" class="form-input" placeholder="0">
              @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Harga tiket player --}}
            <div>
              <label class="form-label" for="price_player_view">Harga Tiket Pemain(Rp)</label>
              <input id="price_player_view" type="text" inputmode="numeric" autocomplete="off" class="form-input" placeholder="Misal: 150.000"
                     value="{{ old('price_ticket_player') ? number_format((int)preg_replace('/\D/','', old('price_ticket_player')), 0, ',', '.') : '' }}">
              <input type="hidden" id="price_ticket_player" name="price_ticket_player" value="{{ (int)preg_replace('/\D/','', old('price_ticket_player', '')) }}">
              @error('price_ticket_player') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Slot Player --}}
            <div>
              <label class="form-label" for="player_slots">Slot Player</label>
              <input name="player_slots" value="{{ old('player_slots') }}" id="player_slots" type="number" class="form-input" placeholder="0">
              @error('player_slots') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="form-label" for="start_date">Tanggal Mulai</label>
              <input name="start_date" value="{{ old('start_date') }}" id="start_date" type="date" class="form-input">
              @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="form-label" for="end_date">Tanggal Selesai</label>
              <input name="end_date" value="{{ old('end_date') }}" id="end_date" type="date" class="form-input">
              @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="form-label" for="game_types">Jenis Permainan</label>
              <input name="game_types" value="{{ old('game_types') }}" id="game_types" type="text" class="form-input" placeholder="Contoh: 9 Ball, 10 Ball">
              @error('game_types') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="form-label" for="status">Status</label>
              <select name="status" id="status" class="form-input">
                <option value="">Pilih status</option>
                <option value="Upcoming" {{ old('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="Ongoing"  {{ old('status') == 'Ongoing'  ? 'selected' : '' }}>Ongoing</option>
                <option value="Ended"    {{ old('status') == 'Ended'    ? 'selected' : '' }}>Ended</option>
              </select>
              @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          <div>
            <label class="form-label" for="description">Deskripsi</label>
            <textarea name="description" id="description" rows="4"
                      class="form-input whitespace-pre-wrap break-words resize-none"
                      placeholder="Tulis deskripsi event">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            {{-- Di halaman show gunakan {!! nl2br(e($event->description)) !!} agar line break tampil. --}}
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            {{-- Total hadiah & breakdown - pair rupiah seragam --}}
            <div>
              <label class="form-label" for="total_prize_money_view">Total Hadiah (Rp)</label>
              <input id="total_prize_money_view" type="text" inputmode="numeric" autocomplete="off" class="form-input" placeholder="Misal: 50.000.000"
                     value="{{ old('total_prize_money') ? number_format((int)preg_replace('/\D/','', old('total_prize_money')), 0, ',', '.') : '' }}">
              <input type="hidden" id="total_prize_money" name="total_prize_money" value="{{ (int)preg_replace('/\D/','', old('total_prize_money', '')) }}">
              @error('total_prize_money') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="form-label" for="champion_prize_view">Juara 1 (Rp)</label>
              <input id="champion_prize_view" type="text" inputmode="numeric" autocomplete="off" class="form-input" placeholder="Misal: 25.000.000"
                     value="{{ old('champion_prize') ? number_format((int)preg_replace('/\D/','', old('champion_prize')), 0, ',', '.') : '' }}">
              <input type="hidden" id="champion_prize" name="champion_prize" value="{{ (int)preg_replace('/\D/','', old('champion_prize', '')) }}">
              @error('champion_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="form-label" for="runner_up_prize_view">Juara 2 (Rp)</label>
              <input id="runner_up_prize_view" type="text" inputmode="numeric" autocomplete="off" class="form-input" placeholder="Misal: 15.000.000"
                     value="{{ old('runner_up_prize') ? number_format((int)preg_replace('/\D/','', old('runner_up_prize')), 0, ',', '.') : '' }}">
              <input type="hidden" id="runner_up_prize" name="runner_up_prize" value="{{ (int)preg_replace('/\D/','', old('runner_up_prize', '')) }}">
              @error('runner_up_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="form-label" for="third_place_prize_view">Juara 3 (Rp)</label>
              <input id="third_place_prize_view" type="text" inputmode="numeric" autocomplete="off" class="form-input" placeholder="Misal: 10.000.000"
                     value="{{ old('third_place_prize') ? number_format((int)preg_replace('/\D/','', old('third_place_prize')), 0, ',', '.') : '' }}">
              <input type="hidden" id="third_place_prize" name="third_place_prize" value="{{ (int)preg_replace('/\D/','', old('third_place_prize', '')) }}">
              @error('third_place_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
              <label class="form-label" for="match_style">Format Pertandingan</label>
              <input name="match_style" value="{{ old('match_style') }}" id="match_style" type="text" class="form-input" placeholder="Contoh: Single Elimination">
              @error('match_style') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
              <label class="form-label" for="finals_format">Format Final</label>
              <input name="finals_format" value="{{ old('finals_format') }}" id="finals_format" type="text" class="form-input" placeholder="Contoh: Best of 5">
              @error('finals_format') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
          </div>

          <div>
            <label class="form-label" for="divisions">Divisi (opsional)</label>
            <input name="divisions" value="{{ old('divisions') }}" id="divisions" type="text" class="form-input" placeholder="Contoh: Open, Master, Junior">
            @error('divisions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label" for="social_media_handle">Akun Sosial Media</label>
            <input name="social_media_handle" value="{{ old('social_media_handle') }}" id="social_media_handle" type="text" class="form-input" placeholder="Contoh: @official_event">
            @error('social_media_handle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          <div>
            <label class="form-label" for="image_url">Gambar Event (Wajib diisi)</label>
            <input name="image_url" id="image_url" type="file" accept="image/*" class="form-input">
            @error('image_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
            <a href="{{ route('admin.event.index') }}" class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-sm">Batal</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm">Simpan</button>
          </div>
        </form>
      </div>
    </main>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // ===== Helper: format angka ke format rupiah (tanpa simbol) =====
  const nfID = new Intl.NumberFormat('id-ID');
  const onlyDigits = (v) => (v || '').toString().replace(/[^\d]/g,'');

  // Pasangkan field tampilan vs hidden yang dikirim
  function bindRupiahPair(viewId, hiddenId){
      const viewEl   = document.getElementById(viewId);
      const hiddenEl = document.getElementById(hiddenId);
      if(!viewEl || !hiddenEl) return;

      // Sync tampilan dari hidden (kalau ada)
      const initRaw = onlyDigits(hiddenEl.value);
      viewEl.value  = initRaw ? nfID.format(parseInt(initRaw,10)) : '';

      // Ketik → update hidden & tampilkan format
      viewEl.addEventListener('input', () => {
          const raw = onlyDigits(viewEl.value);
          hiddenEl.value = raw === '' ? '' : parseInt(raw,10);
          viewEl.value   = raw ? nfID.format(parseInt(raw,10)) : '';
      });

      // Rapikan saat blur
      viewEl.addEventListener('blur', () => {
          const raw = onlyDigits(viewEl.value);
          viewEl.value = raw ? nfID.format(parseInt(raw,10)) : '';
      });
  }

  document.addEventListener('DOMContentLoaded', () => {
      // Bind semua field rupiah
      bindRupiahPair('price_ticket_view',      'price_ticket');
      bindRupiahPair('price_player_view',      'price_ticket_player');
      bindRupiahPair('total_prize_money_view', 'total_prize_money');
      bindRupiahPair('champion_prize_view',    'champion_prize');
      bindRupiahPair('runner_up_prize_view',   'runner_up_prize');
      bindRupiahPair('third_place_prize_view', 'third_place_prize');

      // Pastikan hidden tetap digit sebelum submit
      const form = document.getElementById('eventForm');
      form.addEventListener('submit', () => {
          ['price_ticket' ,'price_ticket_player','total_prize_money','champion_prize','runner_up_prize','third_place_prize']
              .forEach(id => {
                  const el = document.getElementById(id);
                  if (el) {
                      const raw = onlyDigits(el.value);
                      el.value = raw === '' ? '' : parseInt(raw, 10);
                  }
              });
      });
  });
</script>
@endpush
