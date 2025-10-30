@extends('app')
@section('title', 'Venues - Xander Billiard')

@php
    $cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);

    /**
     * FE base untuk SEMUA gambar venue.
     * Catatan: kita TIDAK akan memakai images/products/* atau placeholder CMS.
     */
    $feBaseVenue = 'https://demo-xanders.ptbmn.id/images/venue/';

    /**
     * Bangun kandidat URL gambar venue:
     * - Utama: FE base + basename(filename/url dari DB)
     * - Fallback FE-only: placeholder.webp → placeholder.png
     * - Last resort: placehold.co (agar tidak 404)
     *
     * Tidak ada satupun kandidat yang mengarah ke images/products/*.
     */
    $venueImgCandidates = function ($filenameOrUrl) use ($feBaseVenue) {
        $c = [];

        // 1) Ambil basename dari apapun yang datang dari DB (nama file / full URL)
        $raw = is_string($filenameOrUrl) ? trim($filenameOrUrl) : '';
        if ($raw !== '') {
            // Jika user simpan "https://.../images/products/xxx.png" di DB,
            // kita hanya ambil basename "xxx.png" lalu arahkan ke FE /images/venue/xxx.png
            $name = basename(parse_url($raw, PHP_URL_PATH) ?? $raw);
            if ($name && $name !== '/' && $name !== '.') {
                $c[] = $feBaseVenue . $name;
            }
        }

        // 2) Fallback FE-only (tetap di folder /images/venue/)
        $c[] = $feBaseVenue . 'placeholder.webp';
        $c[] = $feBaseVenue . 'placeholder.png';

        // 3) Last resort (agar tidak 404 kalau placeholder tidak tersedia)
        $c[] = 'https://placehold.co/800x600?text=No+Image';

        // Unik & bersih
        $uniq = [];
        foreach ($c as $x) {
            if (is_string($x) && $x !== '' && !in_array($x, $uniq, true)) {
                $uniq[] = $x;
            }
        }
        return $uniq;
    };

    /**
     * Ekstrak "kota" dari string alamat Indonesia.
     */
    $extractCity = function (?string $address) {
        $address = (string) $address;
        if ($address === '') return null;
        $parts = array_values(array_filter(array_map('trim', explode(',', $address)), fn($p) => $p !== ''));

        // 1) Jakarta xxx
        foreach ($parts as $p) {
            if (preg_match('~^Jakarta\s+(Pusat|Barat|Timur|Selatan|Utara)$~i', $p, $m)) {
                return 'Jakarta ' . ucfirst(strtolower($m[1]));
            }
        }

        // 2) Kota / Kabupaten / Kab.
        foreach ($parts as $p) {
            if (preg_match('~^(Kota|Kabupaten|Kab\.)\s*(.+)$~i', $p, $m)) {
                $prefix = strtolower($m[1]) === 'kab.' ? 'Kabupaten' : $m[1];
                return trim($prefix . ' ' . $m[2]);
            }
        }

        // 3) Sebelum provinsi
        $prov = [
            'Daerah Khusus Ibukota Jakarta','DKI Jakarta','Banten','Jawa Barat','Jawa Tengah','Jawa Timur','DI Yogyakarta','Bali',
            'Aceh','Sumatera Utara','Sumatera Barat','Riau','Kepulauan Riau','Jambi','Bengkulu','Sumatera Selatan','Kepulauan Bangka Belitung','Lampung',
            'Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara',
            'Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat',
            'Maluku','Maluku Utara','Nusa Tenggara Barat','Nusa Tenggara Timur',
            'Papua','Papua Barat','Papua Tengah','Papua Selatan','Papua Pegunungan','Papua Barat Daya'
        ];
        foreach ($parts as $i => $p) {
            if (in_array($p, $prov, true)) {
                if ($i > 0) return $parts[$i-1];
            }
        }

        // 4) Fallback aman
        $n = count($parts);
        if ($n >= 3) return $parts[$n-3];
        if ($n >= 2) return $parts[$n-2];
        return $parts[0] ?? null;
    };

    /**
     * Build daftar kota unik dari $addresses (distinct address dari controller)
     * atau dari $venues jika $addresses kosong.
     */
    $citySet = [];
    if (isset($addresses) && count($addresses)) {
        foreach ($addresses as $addr) {
            $c = $extractCity($addr ?? '');
            if ($c) $citySet[$c] = true;
        }
    } else {
        foreach ($venues as $v) {
            $c = $extractCity($v->address ?? '');
            if ($c) $citySet[$c] = true;
        }
    }
    $cities = array_keys($citySet);
    sort($cities, SORT_NATURAL | SORT_FLAG_CASE);

    $selectedCity = request('city');

    // ===== Format awal nilai price_min & price_max (jika dikirim angka mentah) =====
    $reqPriceMinRaw = preg_replace('/\D+/', '', (string) request('price_min', ''));
    $reqPriceMaxRaw = preg_replace('/\D+/', '', (string) request('price_max', ''));
    $reqPriceMinFmt = $reqPriceMinRaw !== '' ? number_format((int) $reqPriceMinRaw, 0, ',', '.') : '';
    $reqPriceMaxFmt = $reqPriceMaxRaw !== '' ? number_format((int) $reqPriceMaxRaw, 0, ',', '.') : '';
@endphp

@push('styles')
<style>
    :root { color-scheme: dark; }
    html, body { height: 100%; background: #0a0a0a; overscroll-behavior-y: none; }
    body::before { content: ""; position: fixed; inset: 0; background: #0a0a0a; pointer-events: none; z-index: -1; }

    .toggleContent { overflow: hidden; transition: max-height .3s ease; max-height: 1000px; }
    .toggleContent.max-h-0 { max-height: 0; }

    @media (min-width: 1024px) { .lg-hidden { display: none !important; } }

    @media (max-width: 1023px) {
        .sm-hidden { display: none !important; }
        .mobile-filter-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 40; display: none; }
        .mobile-filter-overlay.active { display: block; }
        .mobile-filter-sidebar { position: fixed; top: 0; left: -100%; width: 85%; max-width: 320px; height: 100%; background: #171717; z-index: 50; transition: left .3s ease; overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .mobile-filter-sidebar.open { left: 0; }
    }

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

    /* City pill */
    .city-pill { display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .75rem; border-radius:9999px; border:1px solid rgba(255,255,255,.32); color:#9ca3af; cursor:pointer; user-select:none; transition: background .15s ease, color .15s ease, border-color .15s ease; }
    .city-pill:hover { border-color:#60a5fa; color:#dbeafe; }
    .city-pill.selected { background:#1f2937; border-color:#3b82f6; color:#93c5fd; }
    .city-pill input { display:none; }

    /* ===================== */
    /*   IMAGE LOADING UI    */
    /* ===================== */
    .img-wrapper { position: relative; background: #171717; overflow: hidden; border-radius: .5rem; }
    .img-wrapper > img { display:block; width:100%; height:100%; object-fit:cover; opacity:0; transition: opacity .28s ease; }
    .img-wrapper > img.is-loaded { opacity:1; }
    .img-loading { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:#171717; z-index:1; }
    .img-loading.is-hidden { display:none; }
    .spinner { width:40px; height:40px; border:3px solid rgba(255,255,255,.18); border-top-color:#9ca3af; border-radius:50%; animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .camera-icon { width:48px; height:48px; color: rgba(130,130,130,.4); }

    @media (prefers-reduced-motion: reduce){
        .img-wrapper > img { transition: none; }
        .spinner { animation: none; }
    }
</style>
@endpush

@section('content')
<div
    class="min-h-screen bg-neutral-900 text-white"
    style="
        background-image: url('{{ asset('images/bg/background_2.png') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    "
>
    <!-- Hero -->
    <div class="mb-16 bg-cover bg-center p-24 sm-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
        <p class="text-sm text-gray-400 mt-1"><span onclick="window.location='{{ route('index') }}'">Home</span> / Venue</p>
        <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR FAVORITE VENUE HERE</h2>
    </div>
    <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
        <p class="text-xs sm:text-sm text-gray-400 mt-1">
            <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Venue
        </p>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">FIND YOUR FAVORITE VENUE HERE</h2>
    </div>

    <!-- Mobile Filter Button -->
    <div class="lg-hidden px-4 sm:px-6 mb-4">
        <button id="mobileFilterBtn" class="w-full bg-transparent rounded border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-400/20">
            <i class="fas fa-filter"></i>
            Filter & Search
        </button>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 sm:py-8 lg:py-12" id="list">
        <!-- Filter -->
        <aside id="filterVenue" class="mobile-filter-sidebar">
            <div class="px-4 space-y-6 text-white text-sm">
                <div class="flex items-center justify-between mb-4 lg-hidden">
                    <h3 class="text-lg font-semibold">Filter & Search</h3>
                    <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
                </div>

                <form id="filterForm" method="GET" action="{{ route('venues.index') }}">
                    <input type="hidden" name="pp" value="{{ request('pp', 4) }}">

                    {{-- Search (MENYARING daftar CITY secara client-side; submit saat klik "Filter") --}}
                    <div>
                        <input
                            id="searchInput"
                            type="text"
                            name="search"
                            placeholder="Search"
                            value="{{ request('search') }}"
                            autocomplete="off"
                            class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>

                    {{-- CITY FILTER --}}
                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>City</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div id="cityFilter" class="toggleContent flex flex-wrap gap-2 mb-2">
                            @php $isAll = empty($selectedCity); @endphp
                            <label class="city-pill {{ $isAll ? 'selected' : '' }}" data-city-pill data-all="1">
                                <input type="radio" name="city" value="" {{ $isAll ? 'checked' : '' }}>
                                All Cities
                            </label>

                            @foreach ($cities as $city)
                                @php $id = 'city_' . md5($city); $isSelected = $selectedCity === $city; @endphp
                                <label for="{{ $id }}" class="city-pill {{ $isSelected ? 'selected' : '' }}" data-city-pill data-city="{{ $city }}">
                                    <input id="{{ $id }}" type="radio" name="city" value="{{ $city }}" {{ $isSelected ? 'checked' : '' }}>
                                    {{ $city }}
                                </label>
                            @endforeach
                        </div>
                        <div id="cityEmptyMsg" class="hidden text-xs text-gray-400">No cities match your search.</div>
                    </div>

                    {{-- Price Range --}}
                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Price Range</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent w-full flex items-center gap-2">
                            @php
                                $reqPriceMinRaw = preg_replace('/\D+/', '', (string) request('price_min', ''));
                                $reqPriceMaxRaw = preg_replace('/\D+/', '', (string) request('price_max', ''));
                                $reqPriceMinFmt = $reqPriceMinRaw !== '' ? number_format((int) $reqPriceMinRaw, 0, ',', '.') : '';
                                $reqPriceMaxFmt = $reqPriceMaxRaw !== '' ? number_format((int) $reqPriceMaxRaw, 0, ',', '.') : '';
                            @endphp
                            <input type="text" id="price_min" name="price_min" placeholder="Min"
                                   value="{{ $reqPriceMinFmt }}"
                                   inputmode="numeric" pattern="[0-9\.]*"
                                   class="money-input w-1/2 rounded border border-gray-400 px-2 py-1 bg-transparent focus:outline-none focus:ring focus:ring-blue-500" />
                            <input type="text" id="price_max" name="price_max" placeholder="Max"
                                   value="{{ $reqPriceMaxFmt }}"
                                   inputmode="numeric" pattern="[0-9\.]*"
                                   class="money-input w-1/2 rounded border border-gray-400 px-2 py-1 bg-transparent focus:outline-none focus:ring focus:ring-blue-500" />
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">Filter</button>
                        {{-- Reset: clear semua filter --}}
                        <a href="{{ route('venues.index', ['pp' => 4]) }}" class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">Reset</a>
                    </div>
                </form>
            </div>
        </aside>
        <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

        <!-- List -->
        <section class="lg:col-span-4 flex flex-col gap-6">
            @forelse ($venues as $venue)
                @php
                    // Ambil dari $venue->images[0] jika ada, else pakai $venue->image
                    $rawFirst = null;
                    if (!empty($venue->images)) {
                        $arr = is_array($venue->images) ? $venue->images : (is_string($venue->images) ? json_decode($venue->images, true) : []);
                        if (is_array($arr) && !empty($arr)) $rawFirst = $arr[0];
                    }
                    if (!$rawFirst) $rawFirst = $venue->image ?? '';

                    $candidates = $venueImgCandidates($rawFirst);
                    $primarySrc = $candidates[0] ?? ($feBaseVenue . 'placeholder.png');
                @endphp

                <div class="group">
                    <a href="{{ route('venues.detail', ['venue' => $venue->id, 'slug' => $venue->name]) }}"
                       class="block bg-neutral-800 rounded-xl overflow-hidden shadow-lg flex flex-col sm:flex-row items-start sm:items-center p-4 sm:p-6 cursor-pointer transition hover:bg-neutral-700">

                        {{-- IMAGE: FE /images/venue/* with loader overlay + fallback berantai --}}
                        <div class="img-wrapper w-full sm:w-64 h-40 sm:h-36 bg-neutral-700 mb-4 sm:mb-0 sm:mr-6 flex-shrink-0 overflow-hidden">
                            <div class="img-loading" aria-hidden="true" role="progressbar" aria-label="Loading image">
                                <div class="spinner" aria-hidden="true"></div>
                            </div>
                            <img
                                class="w-full h-full object-cover block js-venue-img"
                                src="{{ $primarySrc }}"
                                data-src-candidates='@json($candidates)'
                                data-lazy-load
                                alt="{{ $venue->name }}"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>

                        <div class="w-full flex flex-col justify-between px-4">
                            <div class="flex justify-between items-start lg:mb-8">
                                <h3 class="text-lg sm:text-2xl font-bold">{{ $venue->name }}</h3>
                                <div class="flex justify-center items-end sm:mt-2">
                                    @auth
                                      @if (auth()->user()->roles === 'user')
                                        <i
                                        onclick="event.stopPropagation();event.preventDefault();"
                                          data-id="{{ $venue->id }}"
                                          data-url="{{ route('venues.favorite', $venue->id) }}"
                                          class="{{ auth()->user()->favorites->contains('venue_id', $venue->id)
                                              ? 'fa-solid text-blue-500'
                                              : 'fa-regular text-gray-400' }}
                                              fa-bookmark text-xl sm:text-2xl cursor-pointer hover:text-blue-500 transition favorite-toggle">
                                        </i>
                                      @endif
                                    @endauth
                                  </div>
                                  
                            </div>
                            <p class="text-gray-400 text-sm mb-2">{{ $venue->address ?? 'Jakarta' }}</p>
                            <div class="mt-12">
                                <div class="flex items-baseline gap-1 text-sm">
                                    <span class="text-gray-400">start from</span>
                                    <span class="text-lg sm:text-xl font-bold">Rp {{ number_format($venue->price ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-gray-400">/ hour</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="text-center py-12 text-gray-400">No venues found.</div>
            @endforelse

            {{-- Pagination --}}
            @php
                $current = method_exists($venues, 'currentPage') ? $venues->currentPage() : 1;
                $last    = method_exists($venues, 'lastPage')    ? $venues->lastPage()    : 1;
                $prevUrl = $current > 1 ? $venues->appends(array_merge(request()->query(), ['pp' => request('pp', 4)]))->url($current - 1) : null;
                $nextUrl = $current < $last ? $venues->appends(array_merge(request()->query(), ['pp' => request('pp', 4)]))->url($current + 1) : null;
            @endphp
            <div class="flex justify-center mt-6">
                <nav class="pager" role="navigation" aria-label="Pagination">
                    @if ($prevUrl)
                        <a class="pager-btn pager-prev" href="{{ $prevUrl }}#list" aria-label="Previous page">
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
                        <a class="pager-btn pager-next" href="{{ $nextUrl }}#list" aria-label="Next page">
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

    @include('public.cart')
</div>

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

{{-- Enforce pp=4 --}}
<script>
    (function ensurePP(){
        const url = new URL(window.location.href);
        const pp  = url.searchParams.get('pp');
        if (pp !== '4') {
            url.searchParams.set('pp','4');
            url.searchParams.set('page','1');
            url.hash = '#list';
            window.location.replace(url.toString());
        }
    })();
</script>

<script>
    // Toggle sections & mobile filter
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".toggleBtn").forEach((btn) => {
            const content = btn.parentElement.nextElementSibling;
            btn.addEventListener("click", () => {
                if (content.classList.contains("max-h-0")) { content.classList.remove("max-h-0"); btn.textContent = "–"; }
                else { content.classList.add("max-h-0"); btn.textContent = "+"; }
            });
        });

        const mobileFilterBtn     = document.getElementById("mobileFilterBtn");
        const filterVenue         = document.getElementById("filterVenue");
        const mobileFilterOverlay = document.getElementById("mobileFilterOverlay");
        const closeMobileFilter   = document.getElementById("closeMobileFilter");

        function openMobileFilter() {
            filterVenue.classList.add("open");
            mobileFilterOverlay.classList.add("active");
            document.body.style.overflow = "hidden";
        }
        function closeMobileFilterFunc() {
            filterVenue.classList.remove("open");
            mobileFilterOverlay.classList.remove("active");
            document.body.style.overflow = "";
        }

        mobileFilterBtn?.addEventListener("click", openMobileFilter);
        closeMobileFilter?.addEventListener("click", closeMobileFilterFunc);
        mobileFilterOverlay?.addEventListener("click", closeMobileFilterFunc);
    });
</script>

<script>
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
            inp.value = formatIDR(inp.value);
            inp.addEventListener('input', () => { inp.value = formatIDR(inp.value); });
            inp.addEventListener('blur',  () => { inp.value = formatIDR(inp.value); });
        });

        const form = document.getElementById('filterForm');
        if (form) {
            form.addEventListener('submit', () => {
                inputs.forEach((inp) => { inp.value = unformatIDR(inp.value); });
            });
        }
    }
    document.addEventListener('DOMContentLoaded', attachMoneyFormatter);
</script>

<script>
    // ====== FILTER CITY PILLS BERDASARKAN TEKS PADA INPUT SEARCH (client-side only) ======
    function filterCityPills() {
        const input   = document.getElementById('searchInput');
        const wrap    = document.getElementById('cityFilter');
        const emptyEl = document.getElementById('cityEmptyMsg');
        if (!input || !wrap) return;

        const term = (input.value || '').toLowerCase().trim();

        const pills = wrap.querySelectorAll('[data-city-pill]');
        let visibleCount = 0;

        pills.forEach(pill => {
            const isAll = pill.hasAttribute('data-all');
            if (isAll) { // "All Cities" selalu terlihat
                pill.style.display = '';
                return;
            }
            const city = (pill.getAttribute('data-city') || pill.textContent || '').toLowerCase();
            const match = term === '' ? true : city.includes(term);
            pill.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        if (emptyEl) {
            if (term !== '' && visibleCount === 0) emptyEl.classList.remove('hidden');
            else emptyEl.classList.add('hidden');
        }
    }

    // City pill visual selected-state (tidak submit otomatis)
    function activateCityPillsSelection() {
        const wrap = document.getElementById('cityFilter');
        if (!wrap) return;
        const pills = wrap.querySelectorAll('[data-city-pill]');
        pills.forEach(pill => {
            const input = pill.querySelector('input[type="radio"]');
            if (!input) return;

            if (input.checked) pill.classList.add('selected');

            pill.addEventListener('click', () => {
                input.checked = true;
                pills.forEach(p => p.classList.remove('selected'));
                pill.classList.add('selected');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const si = document.getElementById('searchInput');
        if (si) {
            si.addEventListener('input', filterCityPills);
            // jalankan 1x saat load agar sinkron dengan nilai awal dari server
            filterCityPills();
        }
        activateCityPillsSelection();

        // ====== Image loader + fallback chain (spinner + fade-in) ======
        function initImageLoadingWithFallback(selector = '.img-wrapper img[data-lazy-load]'){
            document.querySelectorAll(selector).forEach((img) => {
                const wrapper = img.closest('.img-wrapper');
                const loader  = wrapper ? wrapper.querySelector('.img-loading') : null;

                // List kandidat dari data attribute
                let list = [];
                try { list = JSON.parse(img.getAttribute('data-src-candidates') || '[]'); } catch(e) { list = []; }
                if (!Array.isArray(list) || list.length === 0) { list = [img.getAttribute('src')].filter(Boolean); }

                let idx = 0;
                const showLoader = () => loader && loader.classList.remove('is-hidden');
                const hideLoader = () => loader && loader.classList.add('is-hidden');
                const markLoaded = () => { img.classList.add('is-loaded'); hideLoader(); };

                if (img.complete && img.naturalWidth > 0) { markLoaded(); }
                else { showLoader(); }

                img.addEventListener('load', () => { if (img.naturalWidth > 0) { markLoaded(); } }, { passive: true });
                img.addEventListener('error', () => {
                    if (idx < list.length - 1) {
                        idx++;
                        showLoader();
                        const nextSrc = list[idx];
                        if (nextSrc && img.src !== nextSrc) { img.src = nextSrc; }
                    } else {
                        // Sudah tidak ada kandidat lain: tetap hilangkan loader agar tidak muter terus
                        markLoaded();
                    }
                }, { passive: true });
            });
        }

        initImageLoadingWithFallback();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
      const icons = document.querySelectorAll('.favorite-toggle');
    
      icons.forEach(icon => {
        icon.addEventListener('click', async (e) => {
          e.preventDefault();           // cegah aksi default
          e.stopPropagation();          // cegah klik menembus ke <a>
    
          const url = icon.dataset.url;
          if (!url) return;
    
          // Opsional: efek loading kecil
          icon.classList.add('fa-spin', 'fa-spinner');
          icon.classList.remove('fa-bookmark');
    
          try {
            const response = await fetch(url, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
              },
            });
    
            const data = await response.json();
    
            if (data.success) {
              if (data.action === 'added') {
                icon.classList.remove('fa-regular', 'text-gray-400', 'fa-spinner', 'fa-spin');
                icon.classList.add('fa-solid', 'fa-bookmark', 'text-blue-500');
              } else if (data.action === 'removed') {
                icon.classList.remove('fa-solid', 'text-blue-500', 'fa-spinner', 'fa-spin');
                icon.classList.add('fa-regular', 'fa-bookmark', 'text-gray-400');
              }
            } else {
              alert(data.message || 'Terjadi kesalahan.');
            }
    
          } catch (err) {
            console.error(err);
            alert('Gagal memproses permintaan.');
          } finally {
            icon.classList.remove('fa-spin', 'fa-spinner');
            icon.classList.add('fa-bookmark');
          }
        });
      });
    });
</script>
    
@endsection