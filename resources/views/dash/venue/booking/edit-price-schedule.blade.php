@extends('app')
@section('title', 'Edit Price Schedule')

@push('styles')
<style>
  :root {
    color-scheme: dark;
  }

  html,
  body {
    height: 100%;
    background: #0a0a0a;
    overscroll-behavior-y: none;
    overscroll-behavior-x: none;
    touch-action: pan-y;
    -webkit-text-size-adjust: 100%;
    scrollbar-gutter: stable both-edges;
  }

  #antiBounceBg {
    position: fixed;
    inset: -20svh 0 -20svh 0;
    background: #0a0a0a;
    pointer-events: none;
    z-index: -1;
  }

  #app,
  main,
  .min-h-screen,
  .page-wrap {
    background: #0a0a0a;
    overflow-x: clip;
  }

  main {
    overscroll-behavior-y: contain;
  }

  .panel-dark {
    background: #262626;
    border-radius: .9rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
    border: 1px solid rgba(255, 255, 255, .06);
  }

  .field {
    width: 100%;
    border-radius: .7rem;
    border: 1px solid #4b5563;
    background: #1f1f1f;
    color: #fff;
    padding: .625rem .875rem;
    font-size: .95rem;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
  }

  .field:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, .35);
  }

  label.small {
    display: block;
    font-size: .8rem;
    color: #9ca3af;
    margin-bottom: .375rem;
  }

  .with-prefix {
    position: relative;
  }

  .with-prefix .prefix {
    position: absolute;
    left: .75rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: .9rem;
    color: #9ca3af;
    pointer-events: none;
  }

  .with-prefix input {
    padding-left: 2.1rem;
  }

  .helper {
    font-size: .78rem;
    color: #9ca3af;
  }

  .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: .6rem 1rem;
    border-radius: .7rem;
    font-weight: 600;
    transition: transform .05s ease, background .15s ease, color .15s ease, border .15s ease;
  }

  .btn:active {
    transform: translateY(1px);
  }

  .btn-primary {
    background: #0a8cff;
    color: #fff;
  }

  .btn-primary:hover {
    background: #0077e6;
  }

  .btn-outline-danger {
    border: 2px solid #ef4444;
    color: #ef4444;
    background: transparent;
  }

  .btn-outline-danger:hover {
    background: #ef4444;
    color: #fff;
  }

  .day-pill {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .45rem .7rem;
    border-radius: 9999px;
    background: #1f1f1f;
    border: 1px solid #475569;
    font-size: .9rem;
  }

  .day-pill input {
    accent-color: #3b82f6;
  }

  @media (min-width: 768px) {
    .grid-gap-md {
      gap: 1.25rem;
    }
  }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

@php
$bookingUrl = url('/venue/booking');
if (Route::has('venue.booking.index')) {
$bookingUrl = route('venue.booking.index');
} elseif (Route::has('venue.booking')) {
$bookingUrl = route('venue.booking');
} elseif (Route::has('booking.index')) {
$bookingUrl = route('booking.index');
}
@endphp

<div class="page-wrap text-white">
  <div class="flex flex-col min-h-[100dvh] bg-neutral-900 font-sans">
    <div class="flex flex-1 min-h-0">
      @include('partials.sidebar')

      <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-8 md:my-8">
        @include('partials.topbar')

        <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">
          Edit Price Schedule
        </h1>

        @if (session('success'))
        <div class="px-4 sm:px-8 md:px-16 mb-4">
          <div class="bg-green-500 text-white text-sm font-bold px-4 py-3 rounded-md" role="alert">
            <p>{{ session('success') }}</p>
          </div>
        </div>
        @endif

        <form method="POST" action="{{ route('price-schedule.update', $priceSchedule->id) }}" class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8" id="priceScheduleForm">
          @csrf
          @method('PUT')

          <section class="panel-dark p-6 sm:p-8 space-y-8 w-full">
            <h2 class="text-lg font-bold border-b border-gray-600 pb-2">Schedule Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 grid-gap-md gap-5">
              <div>
                <label for="name" class="small">Schedule Name</label>
                <input id="name" name="name" type="text" class="field" required value="{{ old('name', $priceSchedule->name) }}">
              </div>

              <div>
                <label for="price_display" class="small">Price</label>
                <div class="with-prefix">
                  <span class="prefix">Rp</span>
                  <input id="price_display" type="text" inputmode="numeric" autocomplete="off" class="field"
                    value="{{ old('price', number_format((int)$priceSchedule->price, 0, ',', '.')) }}">
                </div>
                <input id="price" name="price" type="hidden" value="{{ old('price', $priceSchedule->price) }}">
              </div>

              <div>
                <label for="start_time" class="small">Start Time</label>
                <input id="start_time" name="start_time" type="time" class="field" required
                  value="{{ old('start_time', optional($priceSchedule->start_time)->format('H:i')) }}">
              </div>

              <div>
                <label for="end_time" class="small">End Time</label>
                <input id="end_time" name="end_time" type="time" class="field" required
                  value="{{ old('end_time', optional($priceSchedule->end_time)->format('H:i')) }}">
              </div>

              <div class="md:col-span-2">
                <label class="small">Days</label>
                @php
                $dayOld = (array) old('days', $priceSchedule->days ?? []);
                $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                @endphp
                <div class="flex flex-wrap gap-2 sm:gap-3">
                  @foreach($days as $d)
                  <label class="day-pill">
                    <input type="checkbox" name="days[]" value="{{ $d }}" {{ in_array($d, $dayOld) ? 'checked' : '' }}>
                    <span>{{ ucfirst($d) }}</span>
                  </label>
                  @endforeach
                </div>
              </div>

              <div>
                <label for="time_category" class="small">Time Category</label>
                <select id="time_category" name="time_category" class="field" required>
                  <option disabled>Choose time category</option>
                  <option value="peak-hours" {{ old('time_category', $priceSchedule->time_category)==='peak-hours' ? 'selected' : '' }}>Peak Hours</option>
                  <option value="normal-hours" {{ old('time_category', $priceSchedule->time_category)==='normal-hours' ? 'selected' : '' }}>Normal Hours</option>
                </select>
              </div>

              <div>
                <label for="is_active" class="small">Status</label>
                <select id="is_active" name="is_active" class="field" required>
                  <option value="1" {{ old('is_active', $priceSchedule->is_active)==1 ? 'selected' : '' }}>Active</option>
                  <option value="0" {{ old('is_active', $priceSchedule->is_active)==0 ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>

              <div class="md:col-span-2">
                <label for="tables_applicable" class="small">Applicable Tables</label>
                <select id="tables_applicable" name="tables_applicable[]" multiple class="field" required>
                  @foreach ($tables as $table)
                  <option value="{{ $table->table_number }}"
                    @if(collect(old('tables_applicable', $priceSchedule->tables_applicable ?? []))->contains($table->table_number)) selected @endif>
                    {{ $table->table_number }}
                  </option>
                  @endforeach
                </select>
                <p class="helper mt-2">Tahan Ctrl (Windows) / Cmd (Mac) untuk memilih lebih dari satu meja.</p>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3">
              <button id="btnDiscard" type="button" class="btn btn-outline-danger w-full sm:w-auto" data-target="{{ $bookingUrl }}">
                Discard
              </button>
              <button type="submit" class="btn btn-primary w-full sm:w-auto">Update Schedule</button>
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
  (function() {
    const btn = document.getElementById('btnDiscard');
    btn?.addEventListener('click', () => window.location.assign(btn.dataset.target || '/'));

    const disp = document.getElementById('price_display');
    const real = document.getElementById('price');

    const onlyDigits = str => (str || '').replace(/[^\d]/g, '');
    const formatIDR = s => s ? s.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';

    if (real && disp) {
      // FIX ===============================
      let raw = real.value.replace(/[,\.].*/, ''); // hapus koma & titik desimal
      raw = onlyDigits(raw);
      // ==================================

      disp.value = formatIDR(raw);
      real.value = raw;
    }

    disp?.addEventListener('input', e => {
      const raw = onlyDigits(e.target.value);
      real.value = raw;
      e.target.value = formatIDR(raw);
    });

    disp?.addEventListener('paste', e => {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text') || '';
      const raw = onlyDigits(text);
      real.value = raw;
      disp.value = formatIDR(raw);
    });

    document.getElementById('priceScheduleForm')?.addEventListener('submit', () => {
      const raw = onlyDigits(disp.value);
      real.value = raw;
    });
  })();
</script>
@endpush