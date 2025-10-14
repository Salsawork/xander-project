@extends('app')
@section('title', 'Create Session')

@push('styles')
<style>
  :root{ color-scheme: dark; --bg:#0a0a0a; --panel:#1e1e1e; --panel-2:#232323; }
  html, body{ background:var(--bg); }
  .card{
    background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)) , var(--panel-2);
    border:1px solid rgba(255,255,255,.08);
    border-radius:1rem;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
  }
  .label{ color:#a3a3a3; font-size:.8rem; margin-bottom:.35rem; display:block; }
  .field{
    width:100%;
    background:#121212e6;
    color:#fff;
    border:1px solid rgba(255,255,255,.12);
    border-radius:.65rem;
    padding:.7rem .9rem;
    font-size:.95rem;
    transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
  }
  .field:focus{
    outline:none;
    border-color:#60a5fa;
    box-shadow:0 0 0 3px rgba(96,165,250,.25);
    background:#0f0f10;
  }
  .hint{ font-size:.75rem; color:#9ca3af; }
  .divider{
    height:1px; background:linear-gradient(90deg, transparent, rgba(255,255,255,.12), transparent);
  }
</style>
@endpush

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar.athlete')

    <main class="flex-1 overflow-y-auto min-w-0 mb-8">
      @include('partials.topbar')

      <div class="max-w-6xl mx-auto px-5 sm:px-8 md:px-12 mt-20">
        <!-- Page Header -->
        <div class="mb-8 md:mb-10">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Create Session</h1>
              <p class="text-sm text-gray-400 mt-1">Catat sesi sparring/latihanmu dengan detail yang rapi.</p>
            </div>
          </div>
        </div>

        <!-- Errors / Alerts -->
        @if ($errors->any())
          <div class="mb-6 rounded-lg border border-red-700/40 bg-red-900/20 p-4">
            <p class="font-semibold text-red-300 mb-2">Periksa kembali input kamu:</p>
            <ul class="list-disc list-inside text-sm text-red-200 space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <!-- Form -->
        <form id="createSessionForm" action="{{ route('athlete.match.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
          @csrf

          <!-- Left: Session Details -->
          <section class="card p-6 sm:p-7">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold">Session Details</h2>
            </div>
            <div class="divider my-4"></div>

            <div class="space-y-5">
              <!-- Venue -->
              <div>
                <label class="label" for="venue_id">Venue</label>
                <select id="venue_id" name="venue_id" class="field">
                  <option value="">Pilih Venue</option>
                  @foreach($venues as $venue)
                    <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                      {{ $venue->name }}
                    </option>
                  @endforeach
                </select>
                @error('venue_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
              </div>

              <!-- Opponent -->
              <div>
                <label class="label" for="opponent_id">Opponent</label>
                <select id="opponent_id" name="opponent_id" class="field">
                  <option value="">Pilih Lawan</option>
                  @foreach($opponents as $opponent)
                    <option value="{{ $opponent->id }}" {{ old('opponent_id') == $opponent->id ? 'selected' : '' }}>
                      {{ $opponent->name }}
                    </option>
                  @endforeach
                </select>
                @error('opponent_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
              </div>

              <!-- Payment Method -->
              <div>
                <label class="label" for="payment_method">Payment Method</label>
                <input
                  id="payment_method"
                  type="text"
                  name="payment_method"
                  class="field"
                  placeholder="Contoh: Cash, GoPay, DANA"
                  value="{{ old('payment_method') }}"
                />
                <p class="hint mt-1">Metode pembayaran untuk sesi ini.</p>
                @error('payment_method') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
              </div>

              <!-- Total Amount (formatted) -->
              <div>
                <label class="label" for="total_amount">Total Amount (Rp)</label>
                <input
                  id="total_amount"
                  type="text"
                  name="total_amount"
                  class="field rupiah"
                  inputmode="numeric"
                  autocomplete="off"
                  placeholder="Contoh: 50.000"
                  value="{{ old('total_amount') }}"
                />
                <p class="hint mt-1">Ketik angka saja. Otomatis akan diformat ribuan. Saat dikirim akan jadi angka murni.</p>
                @error('total_amount') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
              </div>
            </div>
          </section>

          <!-- Right: Schedule -->
          <section class="card p-6 sm:p-7">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold">Schedule</h2>
            </div>
            <div class="divider my-4"></div>

            <div class="space-y-5">
              <!-- Date -->
              <div>
                <label class="label" for="date">Date</label>
                <input
                  id="date"
                  type="date"
                  name="date"
                  class="field"
                  value="{{ old('date') }}"
                />
                @error('date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
              </div>

              <!-- Time Range -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="label" for="time_start">Time Start</label>
                  <input
                    id="time_start"
                    type="time"
                    name="time_start"
                    class="field"
                    value="{{ old('time_start') }}"
                  />
                  @error('time_start') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                  <label class="label" for="time_end">Time End</label>
                  <input
                    id="time_end"
                    type="time"
                    name="time_end"
                    class="field"
                    value="{{ old('time_end') }}"
                  />
                  @error('time_end') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
              </div>

              <!-- Submit -->
              <div class="pt-2">
                <button
                  type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition-colors text-white font-semibold py-3"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 3a.75.75 0 00-1.5 0v6.25H3a.75.75 0 000 1.5h6.25V17a.75.75 0 001.5 0v-6.25H17a.75.75 0 000-1.5h-6.25V3z" />
                  </svg>
                  Create Session
                </button>
              </div>
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
  // ====== Format ribuan Indonesia untuk Total Amount ======
  const nfID = new Intl.NumberFormat('id-ID');

  function onlyDigits(v){
    return (v || '').toString().replace(/[^\d]/g, '');
  }

  function formatRupiah(el){
    const raw = onlyDigits(el.value);
    el.value = raw ? nfID.format(parseInt(raw, 10)) : '';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const amountEl = document.getElementById('total_amount');
    const form     = document.getElementById('createSessionForm');

    if (amountEl){
      // Inisialisasi tampilan jika ada old value dari server
      if (amountEl.value) formatRupiah(amountEl);

      // Format saat mengetik & saat blur
      amountEl.addEventListener('input', () => formatRupiah(amountEl));
      amountEl.addEventListener('blur',  () => formatRupiah(amountEl));
    }

    // Sebelum submit: ubah menjadi angka murni (tanpa titik) agar backend menerima integer
    if (form){
      form.addEventListener('submit', () => {
        if (amountEl) amountEl.value = onlyDigits(amountEl.value);
      });
    }
  });
</script>
@endpush
    