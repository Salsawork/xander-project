@extends('app')
@section('title', 'Create Promo')

@push('styles')
<style>
  /* ===== Anti white flash / rubber-band iOS ===== */
  :root { color-scheme: dark; }
  html, body{
    height:100%;
    background:#0a0a0a;
    overscroll-behavior-y:none;
    overscroll-behavior-x:none;
    touch-action:pan-y;
    -webkit-text-size-adjust:100%;
    scrollbar-gutter:stable both-edges;
  }
  #antiBounceBg{
    position:fixed; inset:-20svh 0 -20svh 0; background:#0a0a0a; pointer-events:none; z-index:-1;
  }
  #app, main, .min-h-screen { background:#0a0a0a; }
  .page-wrap{ background:#0a0a0a; overflow-x:clip; }
  main{ overscroll-behavior-y:contain; background:#0a0a0a; }

  /* Panel & inputs */
  .panel-dark{
    background:#262626; border-radius:.9rem; box-shadow:0 10px 30px rgba(0,0,0,.35);
    border:1px solid rgba(255,255,255,.06);
  }
  .field{
    width:100%; border-radius:.7rem; border:1px solid #4b5563; background:#1f1f1f; color:#fff;
    padding:.65rem .9rem; font-size:.95rem; outline:none; transition:border-color .15s, box-shadow .15s;
  }
  .field:focus{ border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.35); }
  label.small{ display:block; font-size:.8rem; color:#9ca3af; margin-bottom:.4rem; }

  .with-prefix{ position:relative; }
  .with-prefix .prefix{ position:absolute; left:.75rem; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:.9rem; pointer-events:none; }
  .with-prefix input{ padding-left:2.1rem; }

  .btn{
    display:inline-flex; align-items:center; justify-content:center; padding:.65rem 1rem; border-radius:.7rem;
    font-weight:600; transition:transform .05s ease, background .15s ease, color .15s ease, border .15s ease;
  }
  .btn:active{ transform:translateY(1px); }
  .btn-primary{ background:#0a8cff; color:#fff; }
  .btn-primary:hover{ background:#0077e6; }
  .btn-outline-danger{ border:2px solid #ef4444; color:#ef4444; background:transparent; }
  .btn-outline-danger:hover{ background:#ef4444; color:#fff; }

  .helper{ font-size:.78rem; color:#9ca3af; }

  @media (min-width:768px){ .grid-gap-md{ gap:1.25rem; } }
</style>
@endpush

@section('content')
  <!-- Kanvas gelap anti-bounce -->
  <div id="antiBounceBg" aria-hidden="true"></div>

  @php
    // URL kembali ke daftar promo (fallback aman)
    $backUrl = url('/venue/promo');
    if (\Illuminate\Support\Facades\Route::has('venue.promo.index')) {
      $backUrl = route('venue.promo.index');
    } elseif (\Illuminate\Support\Facades\Route::has('venue.promo')) {
      $backUrl = route('venue.promo');
    }
  @endphp

  <div class="page-wrap text-white">
    <div class="flex flex-col min-h-[100dvh] bg-neutral-900 font-sans">
      <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')

        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-8 md:my-8">
          @include('partials.topbar')

          <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">
            Promo Detail
          </h1>

          @if ($errors->any())
            <div class="mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-4 bg-red-700 rounded p-3 sm:p-4">
              <ul class="list-disc list-inside text-xs sm:text-sm">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('venue.promo.store') }}" class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8" id="promoForm">
            @csrf

            <section aria-labelledby="general-info-title" class="panel-dark rounded-lg p-4 sm:p-8 space-y-6 sm:space-y-8 w-full">
              <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">General Information</h2>

              <div class="grid grid-cols-1 md:grid-cols-2 grid-gap-md gap-4 sm:gap-6">
                <!-- Left column -->
                <div class="space-y-4 sm:space-y-6">
                  <div>
                    <label class="small" for="product-name">Name</label>
                    <input name="name" id="product-name" type="text" placeholder="e.g., Regular Weekday"
                           class="field" required />
                  </div>

                  <div>
                    <label class="small" for="product-code">Code</label>
                    <input name="code" id="product-code" type="text" placeholder="e.g., REGULAR-WEEKDAY"
                           class="field" required />
                  </div>

                  <div>
                    <label class="small" for="product-type">Type</label>
                    <select name="type" id="product-type" class="field" required>
                      <option value="" disabled selected>Select type</option>
                      <option value="percentage">Percentage</option>
                      <option value="amount">Amount</option>
                    </select>
                  </div>

                  <div>
                    <label class="small" for="discount-percentage">Discount Percentage</label>
                    <input name="discount_percentage" id="discount-percentage" type="number" placeholder="e.g., 10"
                           class="field" />
                  </div>

                  <div>
                    <label class="small" for="discount-amount-display">Discount Amount</label>
                    <div class="with-prefix">
                      <span class="prefix">Rp</span>
                      <!-- tampilan terformat -->
                      <input id="discount-amount-display" type="text" inputmode="numeric" autocomplete="off"
                             class="field" placeholder="e.g., 50.000" value="">
                    </div>
                    <!-- nilai yang dikirim -->
                    <input name="discount_amount" id="discount-amount" type="hidden" value="">
                    <p class="helper mt-2">Di-formatted otomatis. Yang tersimpan: angka murni tanpa titik.</p>
                  </div>
                </div>

                <!-- Right column -->
                <div class="space-y-4 sm:space-y-6">
                  <div>
                    <label class="small" for="minimum-purchase-display">Minimum Purchase</label>
                    <div class="with-prefix">
                      <span class="prefix">Rp</span>
                      <!-- tampilan terformat -->
                      <input id="minimum-purchase-display" type="text" inputmode="numeric" autocomplete="off"
                             class="field" placeholder="e.g., 100.000" value="">
                    </div>
                    <!-- nilai yang dikirim -->
                    <input name="minimum_purchase" id="minimum-purchase" type="hidden" value="">
                    <p class="helper mt-2">Gunakan angka saja; akan diformat sebagai rupiah.</p>
                  </div>

                  <div>
                    <label class="small" for="quota">Quota</label>
                    <input name="quota" id="quota" type="number" placeholder="e.g., 100" class="field" />
                  </div>

                  <div>
                    <label class="small" for="start-date">Start Date</label>
                    <input name="start_date" id="start-date" type="date" class="field" />
                  </div>

                  <div>
                    <label class="small" for="end-date">End Date</label>
                    <input name="end_date" id="end-date" type="date" class="field" />
                  </div>
                </div>
              </div>

              <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 pt-2">
                <button id="btnDiscard" type="button" class="btn btn-outline-danger w-full sm:w-auto" data-target="{{ $backUrl }}">
                  Discard
                </button>
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
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
  (function(){
    // ===== Discard: kembali ke halaman daftar promo =====
    const backBtn = document.getElementById('btnDiscard');
    backBtn?.addEventListener('click', () => {
      const target = backBtn.getAttribute('data-target') || '/';
      window.location.assign(target);
    });

    // ===== Formatter angka rupiah (display) -> hidden (raw) =====
    function onlyDigits(str){ return (str||'').replace(/\D+/g, ''); }
    function formatIDR(numStr){
      if (!numStr) return '';
      numStr = numStr.replace(/^0+(?!$)/, '');         // hapus leading zeros
      return numStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function wireRupiahPair(displayId, hiddenId){
      const disp = document.getElementById(displayId);
      const hid  = document.getElementById(hiddenId);
      if(!disp || !hid) return;

      // Init dari old() bila ada value server-side
      if (hid.value){
        const raw = onlyDigits(hid.value);
        hid.value  = raw;
        disp.value = formatIDR(raw);
      }

      disp.addEventListener('input', (e)=>{
        const raw = onlyDigits(e.target.value);
        hid.value = raw;
        e.target.value = formatIDR(raw);
      });

      disp.addEventListener('paste', (e)=>{
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text') || '';
        const raw = onlyDigits(text);
        hid.value = raw;
        disp.value = formatIDR(raw);
      });
    }

    // Pairing fields
    wireRupiahPair('discount-amount-display', 'discount-amount');
    wireRupiahPair('minimum-purchase-display', 'minimum-purchase');

    // Pastikan sebelum submit hidden sudah sinkron
    document.getElementById('promoForm')?.addEventListener('submit', ()=>{
      ['discount-amount-display','minimum-purchase-display'].forEach((id)=>{
        const disp = document.getElementById(id);
        if(!disp) return;
        const raw = onlyDigits(disp.value);
        const hid = document.getElementById(id.replace('-display',''));
        if(hid) hid.value = raw;
      });
    });
  })();
</script>
@endpush
