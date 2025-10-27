@extends('app')

@section('title', 'Sparring')

@php
    $cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);

    // Koleksi lokasi (array/Collection) dari controller, fallback default jika kosong
    $locations = (isset($locations) && count($locations)) ? collect($locations) : collect(['Jakarta', 'Bandung', 'Surabaya', 'Medan']);

    // Paginasi manual dari collection agar konsisten dengan desain grid
    $existingItems = collect(
        ($athletes ?? collect()) instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? ($athletes->items() ?? [])
            : (is_iterable($athletes ?? []) ? $athletes : [])
    );

    $pp   = (int) (request('pp') ?: 8);
    $page = (int) (request('page') ?: 1);

    $slice    = $existingItems->forPage($page, $pp)->values();
    $athletes = new \Illuminate\Pagination\LengthAwarePaginator(
        $slice,
        $existingItems->count(),
        $pp,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    $currentAddress = trim((string) request('address', ''));

    // ====== Format awal nilai price_min & price_max ke format angka Indonesia (1.000.000) ======
    $reqPriceMinRaw = preg_replace('/\D+/', '', (string) request('price_min', ''));
    $reqPriceMaxRaw = preg_replace('/\D+/', '', (string) request('price_max', ''));
    $reqPriceMinFmt = $reqPriceMinRaw !== '' ? number_format((int) $reqPriceMinRaw, 0, ',', '.') : '';
    $reqPriceMaxFmt = $reqPriceMaxRaw !== '' ? number_format((int) $reqPriceMaxRaw, 0, ',', '.') : '';

    /**
     * Kandidat URL gambar atlet (berantai):
     * - Pertama: images/athlete/{basename dari DB}
     * - Fallback FE-only: placeholder.webp -> athlete-1.png
     * - Last resort: placehold.co (anti-404)
     */
    $athleteImgCandidates = function ($athlete) {
        $c = [];

        $raw = (string) ($athlete->athleteDetail->image ?? '');
        if ($raw !== '') {
            $name = basename(parse_url($raw, PHP_URL_PATH) ?? $raw);
            if ($name && $name !== '/' && $name !== '.') {
                $c[] = asset('images/athlete/' . $name);
            }
        }

        // Fallbacks FE-only
        $c[] = asset('images/athlete/placeholder.webp');
        $c[] = asset('images/athlete/athlete-1.png');

        // Last resort
        $c[] = 'https://placehold.co/480x640?text=No+Image';

        // Unik & bersih
        $uniq = [];
        foreach ($c as $x) {
            if (is_string($x) && $x !== '' && !in_array($x, $uniq, true)) $uniq[] = $x;
        }
        return $uniq;
    };
@endphp

@push('styles')
<style>
  :root { color-scheme: dark; }
  html, body { height:100%; background:#0a0a0a; overscroll-behavior-y:none; }
  #app, main { background:#0a0a0a; }
  body::before { content:""; position:fixed; inset:0; background:#0a0a0a; pointer-events:none; z-index:-1; }

  .max-h-0 { max-height: 0 !important; }
  @media (min-width: 1024px) { .lg-hidden { display:none !important; } }
  @media (max-width: 1023px) { .sm-hidden { display:none !important; } }

  @media (max-width:1023px){
    .mobile-filter-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:40; display:none; }
    .mobile-filter-overlay.active{ display:block; }
    /* Sidebar filter transparan (tanpa bg hitam) */
    .mobile-filter-sidebar{
      position:fixed; top:0; left:-100%;
      width:85%; max-width:340px; height:100%;
      background:transparent;
      z-index:50; transition:left .3s ease;
      overflow-y:auto; -webkit-overflow-scrolling:touch;
      padding-bottom:24px; border-right:0;
    }
    .mobile-filter-sidebar.open{ left:0; }
  }
  .toggleContent{ overflow:hidden; transition:max-height .3s ease; max-height:1000px; }
  .toggleContent.max-h-0{ max-height:0; }

  .pager { display:inline-flex; align-items:center; gap:10px; background:#1f2937; border:1px solid rgba(255,255,255,.06); border-radius:9999px; padding:6px 10px; box-shadow: 0 8px 20px rgba(0,0,0,.35) inset, 0 4px 14px rgba(0,0,0,.25); }
  .pager-label { min-width:90px; text-align:center; color:#e5e7eb; font-weight:600; letter-spacing:.2px; }
  .pager-btn { width:44px; height:44px; display:grid; place-items:center; border-radius:9999px; line-height:0; text-decoration:none; border:1px solid rgba(255,255,255,.15); box-shadow:0 2px 6px rgba(0,0,0,.35); transition: transform .15s ease, opacity .15s ease; }
  .pager-btn:hover { transform: translateY(-1px); }
  .pager-prev { background:#e5e7eb; color:#0f172a; }
  .pager-next { background:#2563eb; color:#fff; }
  .pager-btn[aria-disabled="true"] { opacity:.45; pointer-events:none; filter:grayscale(20%); }
  @media (max-width:640px){
      .pager { padding:4px 8px; gap:8px; }
      .pager-btn { width:40px; height:40px; }
      .pager-label { min-width:80px; font-size:.9rem; }
  }

  /* Chips lokasi klik-submit */
  .chip {
    appearance: none;
    border:1px solid #6b7280;
    color:#9ca3af;
    background:transparent;
    padding:.35rem .7rem;
    border-radius:9999px;
    font-size:.85rem;
    cursor:pointer;
    transition: all .15s ease;
  }
  .chip:hover { border-color:#93c5fd; color:#dbeafe; background:rgba(147,197,253,.08); }
  .chip.active { border-color:#3b82f6; color:#93c5fd; background:rgba(59,130,246,.15); }

  /* =======================
     IMAGE LOADING OVERLAY
     ======================= */
  .img-wrapper {
    position: relative;
    background: #171717;
    overflow: hidden;
  }
  .img-wrapper img {
    width:100%; height:100%; object-fit:cover; display:block;
    opacity: 0; transition: opacity .3s ease;
  }
  .img-wrapper img.loaded { opacity: 1; }

  .img-loading {
    position:absolute; inset:0;
    display:flex; align-items:center; justify-content:center;
    background: #171717;
    z-index:1;
  }
  .img-loading.hidden { display:none; }

  .spinner {
    width: 40px; height: 40px;
    border: 3px solid rgba(130,130,130,.25);
    border-top-color: #9ca3af;
    border-radius: 50%;
    animation: spin .8s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* (Opsional) ikon kamera untuk thumb kecil */
  .camera-icon { width: 48px; height: 48px; color: rgba(130,130,130,.45); }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-neutral-900 text-white"
     style="background-image:url('{{ asset('images/bg/background_3.png') }}'); background-size:cover; background-position:center; background-repeat:no-repeat;">

  <!-- Desktop Hero -->
  <div class="mb-16 bg-cover bg-center p-24 sm-hidden" style="background-image:url('/images/bg/product_breadcrumb.png')">
      <p class="text-sm text-gray-400 mt-1">
          <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Sparring
      </p>
      <h2 class="text-4xl font-bold uppercase text-white">POWER. PRECISION. PLAY.</h2>
  </div>

  <!-- Mobile Hero -->
  <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden" style="background-image:url('/images/bg/product_breadcrumb.png')">
      <p class="text-xs sm:text-sm text-gray-400 mt-1">
          <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Sparring
      </p>
      <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">Power. Precision. Play.</h2>
  </div>

  <!-- Mobile Filter Button -->
  <div class="lg-hidden px-4 sm:px-6 mb-4">
      <button id="mobileFilterBtn"
          class="w-full bg-transparent rounded border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-700/40">
          <i class="fas fa-filter"></i>
          Filter & Search
      </button>
  </div>

  <!-- Mobile Filter Overlay -->
  <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

  <!-- Grid: Filter + List -->
  <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 lg:py-12" id="list">
    <!-- Filter -->
    <aside id="filterSparring" class="mobile-filter-sidebar lg:relative lg:left-0 lg:col-span-1">
      <div class="px-4 lg:px-0 space-y-6 text-white text-sm lg:sticky lg:top-0">
        <div class="flex items-center justify-between mb-4 lg-hidden">
          <h3 class="text-lg font-semibold">Filter & Search</h3>
          <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
        </div>

        <form method="GET" action="{{ route('sparring.index') }}" class="space-y-4 rounded-xl lg:p-3" id="filterForm">
          <input type="hidden" name="pp" id="ppInput" value="{{ $pp }}"/>

          <!-- Search -->
          <div>
            <input id="searchInput" type="text" name="search" placeholder="Search"
                   value="{{ request('search') }}"
                   autocomplete="off"
                   class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            <p class="text-xs text-gray-400 mt-1">Ketik kata kunci lalu klik tombol <strong>Filter</strong> untuk menerapkan.</p>
          </div>

          <!-- Location (klik = submit otomatis) -->
          <div>
            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
              <span>Location</span>
              <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
            </div>

            <div id="locationsChips" class="toggleContent flex flex-wrap gap-2">
              {{-- Chip "All" untuk reset filter lokasi --}}
              <button type="submit" name="address" value=""
                      class="chip {{ $currentAddress === '' ? 'active' : '' }}"
                      data-all="1"
                      title="All locations">
                All
              </button>

              @forelse ($locations as $loc)
                @php $isActive = ($currentAddress !== '' && $currentAddress === $loc); @endphp
                <button type="submit"
                        name="address"
                        value="{{ $loc }}"
                        class="chip {{ $isActive ? 'active' : '' }}"
                        data-loc="{{ $loc }}"
                        title="{{ $loc }}">
                  {{ $loc }}
                </button>
              @empty
                <span id="locationsEmpty" class="text-gray-400 text-xs">No locations match your search.</span>
              @endforelse
            </div>
          </div>

          <!-- Price Range -->
          <div>
            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
              <span>Price Range</span>
              <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
            </div>
            <div class="toggleContent w-full flex items-center gap-2">
              <input type="text"
                     id="price_min"
                     name="price_min"
                     placeholder="Min"
                     inputmode="numeric"
                     pattern="[0-9\.]*"
                     value="{{ $reqPriceMinFmt }}"
                     class="money-input w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
              <input type="text"
                     id="price_max"
                     name="price_max"
                     placeholder="Max"
                     inputmode="numeric"
                     pattern="[0-9\.]*"
                     value="{{ $reqPriceMaxFmt }}"
                     class="money-input w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
            </div>
          </div>

          <!-- Buttons -->
          <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">Filter</button>
            <a href="{{ route('sparring.index') }}" class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-center text-blue-500 hover:bg-blue-500 hover:text-white">Reset</a>
          </div>
        </form>
      </div>
    </aside>

    <!-- Athletes List -->
    <section class="lg:col-span-4 flex flex-col gap-6">
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
        @forelse ($athletes as $athlete)
          @php
            $candidates = $athleteImgCandidates($athlete);
            $primarySrc = $candidates[0] ?? asset('images/athlete/athlete-1.png');
          @endphp

          <a href="{{ route('sparring.detail', ['id' => $athlete->id, 'slug' => \Illuminate\Support\Str::slug($athlete->name)]) }}" class="block">
            <div class="rounded-lg lg:rounded-xl bg-neutral-800 p-2 sm:p-3 shadow-md hover:shadow-lg transition-shadow">
              <div class="aspect-[3/4] overflow-hidden rounded-md bg-neutral-700 mb-2 sm:mb-3 img-wrapper">
                <!-- Loading overlay -->
                <div class="img-loading">
                  <div class="spinner"></div>
                </div>

                <img
                  src="{{ $primarySrc }}"
                  data-src-candidates='@json($candidates)'
                  data-lazy-load
                  class="h-full w-full object-cover js-img-fallback"
                  alt="{{ $athlete->name }}"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <h3 class="text-xs sm:text-sm font-medium line-clamp-2">{{ $athlete->name }}</h3>
              <p class="text-xs sm:text-sm text-gray-400 mt-1">
                Rp {{ number_format($athlete->athleteDetail->price_per_session ?? 0, 0, ',', '.') }} / session
              </p>
            </div>
          </a>
        @empty
          <div class="col-span-full text-center py-12 text-gray-400">No athletes available at the moment.</div>
        @endforelse
      </div>

      {{-- Pagination: pill --}}
      @php
          $current = $athletes->currentPage();
          $last    = $athletes->lastPage();
          $prevUrl = $current > 1 ? $athletes->appends(request()->query())->url($current - 1) . '#list' : null;
          $nextUrl = $current < $last ? $athletes->appends(request()->query())->url($current + 1) . '#list' : null;
      @endphp
      <div class="flex justify-center mt-6">
        <nav class="pager" role="navigation" aria-label="Pagination">
          @if ($prevUrl)
            <a class="pager-btn pager-prev" href="{{ $prevUrl }}" aria-label="Previous page">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          @else
            <span class="pager-btn pager-prev" aria-disabled="true" aria-label="Previous page">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          @endif

          <span class="pager-label">{{ $current }} of {{ $last }}</span>

          @if ($nextUrl)
            <a class="pager-btn pager-next" href="{{ $nextUrl }}" aria-label="Next page">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          @else
            <span class="pager-btn pager-next" aria-disabled="true" aria-label="Next page">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          @endif
        </nav>
      </div>
    </section>
  </div>

  <!-- Floating Cart -->
  @if (Auth::check() && Auth::user()->roles === 'user')
    <button
      aria-label="Shopping cart with {{ $cartCount }} items"
      onclick="showCart()"
      class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg z-[60]"
    >
      <i class="fas fa-shopping-cart text-white text-3xl"></i>
      @if ($cartCount > 0)
        <span class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
          {{ $cartCount }}
        </span>
      @endif
    </button>
  @endif

  @include('public.cart')
</div>
@endsection

@push('scripts')
<script>
  // Per-page adaptif: Desktop 8 / Mobile 4
  (function(){
      const isDesktop  = window.matchMedia('(min-width: 1024px)').matches;
      const desiredPP  = isDesktop ? 8 : 4;
      const url        = new URL(window.location.href);
      const currentPP  = parseInt(url.searchParams.get('pp') || '0', 10);

      if (!currentPP || currentPP !== desiredPP) {
          url.searchParams.set('pp', String(desiredPP));
          url.searchParams.set('page', '1');
          url.hash = '#list';
          window.location.replace(url.toString());
          return;
      }
      const ppInput = document.getElementById('ppInput');
      if (ppInput) ppInput.value = String(desiredPP);
  })();

  // Toggle sections
  document.addEventListener("DOMContentLoaded", () => {
      document.querySelectorAll(".toggleBtn").forEach((btn) => {
          const content = btn.parentElement.nextElementSibling;
          btn.addEventListener("click", () => {
              if (content.classList.contains("max-h-0")) { content.classList.remove("max-h-0"); btn.textContent = "–"; }
              else { content.classList.add("max-h-0"); btn.textContent = "+"; }
          });
      });
  });

  // Mobile filter
  document.addEventListener("DOMContentLoaded", () => {
      const mobileFilterBtn     = document.getElementById("mobileFilterBtn");
      const filterSparring      = document.getElementById("filterSparring");
      const mobileFilterOverlay = document.getElementById("mobileFilterOverlay");
      const closeMobileFilter   = document.getElementById("closeMobileFilter");

      function openMobileFilter() {
          filterSparring.classList.add("open");
          mobileFilterOverlay.classList.add("active");
          document.body.style.overflow = "hidden";
      }
      function closeMobileFilterFunc() {
          filterSparring.classList.remove("open");
          mobileFilterOverlay.classList.remove("active");
          document.body.style.overflow = "";
      }

      mobileFilterBtn?.addEventListener("click", openMobileFilter);
      closeMobileFilter?.addEventListener("click", closeMobileFilterFunc);
      mobileFilterOverlay?.addEventListener("click", closeMobileFilterFunc);
  });

  // ====== FORMAT INPUT UANG (IDR) UNTUK PRICE RANGE ======
  function unformatIDR(str) { return (str || '').replace(/[^\d]/g, ''); }
  function formatIDR(str) {
      const digits = unformatIDR(str);
      if (!digits) return '';
      return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function attachMoneyFormatter() {
      const inputs = document.querySelectorAll('.money-input');
      inputs.forEach((inp) => {
          // Format awal
          inp.value = formatIDR(inp.value);
          // Saat mengetik
          inp.addEventListener('input', () => {
              const before = inp.value;
              const caretBefore = inp.selectionStart;
              const lenBefore = before.length;

              inp.value = formatIDR(before);

              const lenAfter = inp.value.length;
              const delta = lenAfter - lenBefore;
              const newPos = typeof caretBefore === 'number' ? Math.max(0, Math.min(lenAfter, caretBefore + delta)) : lenAfter;
              try { inp.setSelectionRange(newPos, newPos); } catch(e) {}
          });
          // Saat blur
          inp.addEventListener('blur', () => { inp.value = formatIDR(inp.value); });
      });

      // Submit kirim angka mentah
      const form = document.getElementById('filterForm');
      if (form) {
          form.addEventListener('submit', () => {
              inputs.forEach((inp) => { inp.value = unformatIDR(inp.value); });
          });
      }
  }
  document.addEventListener('DOMContentLoaded', attachMoneyFormatter);

  // ===========================
  // IMAGE LOADER + FALLBACK JS
  // ===========================
  function initImageLoading() {
    document.querySelectorAll('img[data-lazy-load]').forEach((img) => {
      const wrapper = img.closest('.img-wrapper');
      const loader  = wrapper?.querySelector('.img-loading');

      // Fade-in ketika load sukses
      const onLoad = () => {
        img.classList.add('loaded');
        if (loader) loader.classList.add('hidden');
      };
      img.addEventListener('load', onLoad, { passive: true });

      // Fallback berantai dari data-src-candidates
      try {
        const list = JSON.parse(img.getAttribute('data-src-candidates') || '[]');
        let i = 0;
        const onErr = () => {
          i++;
          if (i < list.length) {
            if (img.src !== list[i]) img.src = list[i];
          } else {
            // Hentikan spinner walau gagal semua
            img.classList.add('loaded');
            if (loader) loader.classList.add('hidden');
          }
        };
        img.addEventListener('error', onErr, { passive: true });
      } catch (e) {
        // Jika parsing gagal, tetap sembunyikan loader saat event error pertama
        img.addEventListener('error', () => {
          img.classList.add('loaded');
          if (loader) loader.classList.add('hidden');
        }, { passive: true });
      }

      // Jika sudah ter-cache
      if (img.complete) {
        // Paksa trigger 'load' pada img yang sudah cache tanpa event
        requestAnimationFrame(() => onLoad());
      }
    });
  }
  window.addEventListener('load', initImageLoading);
</script>
@endpush
