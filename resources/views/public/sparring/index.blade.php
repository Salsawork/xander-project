@extends('app')

@section('title', 'Sparring')

@php
    // === Data keranjang dari cookie (produk, venue, sparring) ===
    $cartProducts  = json_decode(request()->cookie('cartProducts') ?? '[]', true);
    $cartVenues    = json_decode(request()->cookie('cartVenues') ?? '[]', true);
    $cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
    $cartCount     = count($cartProducts) + count($cartVenues) + count($cartSparrings);

    // ===== Jaga variabel $locations biar filter tidak error saat dummy =====
    $locations = $locations ?? ['Jakarta', 'Bandung', 'Surabaya', 'Medan'];

    // ===== Ambil data atlet yang SUDAH ada (apapun bentuknya) jadi Collection =====
    $existingItems = collect(
        ($athletes ?? collect()) instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? ($athletes->items() ?? [])
            : (is_iterable($athletes ?? []) ? $athletes : [])
    );

    // ===== Tambah dummy sampai MINIMAL 12 item (tanpa mengubah data asli) =====
    $need = max(0, 12 - $existingItems->count());
    $dummies = collect(range(1, $need))->map(function($i) use ($existingItems){
        $idx = (($existingItems->count() + $i - 1) % 6) + 1; // rotasi 1..6
        $id  = $existingItems->count() + $i;
        return (object)[
            'id'   => $id,
            'name' => "Athlete #$id",
            'athleteDetail' => (object)[
                'image'             => "athlete-$idx.png",
                'price_per_session' => 100000 * $idx,
            ],
        ];
    });

    $allItems = $existingItems->values()->merge($dummies->values());

    // ===== Pagination adaptif via query 'pp' (per page): desktop 8 / mobile 4 ====
    $pp   = (int) (request('pp') ?: 8);   // default 8; JS akan set 4 di mobile
    $page = (int) (request('page') ?: 1);

    $slice    = $allItems->forPage($page, $pp)->values();
    $athletes = new \Illuminate\Pagination\LengthAwarePaginator(
        $slice,
        $allItems->count(),
        $pp,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );
@endphp

@push('styles')
    <style>
        /* ===== Anti white overscroll (global) ===== */
        :root { color-scheme: dark; }
        html, body { height:100%; background:#0a0a0a; overscroll-behavior-y:none; }
        #app, main { background:#0a0a0a; }
        body::before { content:""; position:fixed; inset:0; background:#0a0a0a; pointer-events:none; z-index:-1; }

        .max-h-0 { max-height: 0 !important; }
        @media (min-width: 1024px) { .lg-hidden { display:none !important; } }
        @media (max-width: 1023px) { .sm-hidden { display:none !important; } }

        /* Mobile filter overlay + sidebar */
        @media (max-width:1023px){
            .mobile-filter-overlay{
                position:fixed; inset:0; background:rgba(0,0,0,.5);
                z-index:40; display:none;
            }
            .mobile-filter-overlay.active{ display:block; }
            .mobile-filter-sidebar{
                position:fixed; top:0; left:-100%;
                width:85%; max-width:340px; height:100%;
                background:rgb(23,23,23); z-index:50;
                transition:left .3s ease; overflow-y:auto; -webkit-overflow-scrolling:touch;
                padding-bottom:24px;
            }
            .mobile-filter-sidebar.open{ left:0; }
        }
        .toggleContent{ overflow:hidden; transition:max-height .3s ease; max-height:1000px; }
        .toggleContent.max-h-0{ max-height:0; }

        /* ===== Pill Pagination (mobile & desktop) ===== */
        .pager {
            display:inline-flex; align-items:center; gap:10px;
            background:#1f2937; /* slate-800 */
            border:1px solid rgba(255,255,255,.06);
            border-radius:9999px; padding:6px 10px;
            box-shadow: 0 8px 20px rgba(0,0,0,.35) inset, 0 4px 14px rgba(0,0,0,.25);
        }
        .pager-label {
            min-width:90px; text-align:center; color:#e5e7eb; font-weight:600; letter-spacing:.2px;
        }
        .pager-btn {
            width:44px; height:44px; display:grid; place-items:center;
            border-radius:9999px; line-height:0; text-decoration:none;
            border:1px solid rgba(255,255,255,.15); box-shadow:0 2px 6px rgba(0,0,0,.35);
            transition: transform .15s ease, opacity .15s ease;
        }
        .pager-btn:hover { transform: translateY(-1px); }
        .pager-prev { background:#e5e7eb; color:#0f172a; }
        .pager-next { background:#2563eb; color:#fff; }
        .pager-btn[aria-disabled="true"] { opacity:.45; pointer-events:none; filter:grayscale(20%); }
        @media (max-width:640px){
            .pager { padding:4px 8px; gap:8px; }
            .pager-btn { width:40px; height:40px; }
            .pager-label { min-width:80px; font-size:.9rem; }
        }
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
                <div class="px-4 lg:px-0 space-y-6 text-white text-sm lg:sticky lg:top-24">
                    <div class="flex items-center justify-between mb-4 lg-hidden">
                        <h3 class="text-lg font-semibold">Filter & Search</h3>
                        <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
                    </div>

                    <form method="GET" action="{{ route('sparring.index') }}" class="space-y-4 rounded-xl lg:bg-neutral-900 lg:p-4">
                        <input type="hidden" name="pp" id="ppInput" value="{{ $pp }}"/>

                        <!-- Search -->
                        <div>
                            <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                                   class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        </div>

                        <!-- Date -->
                        <div x-data="calendar('{{ request('date') }}')" class="pt-2 text-white rounded-xl text-sm">
                            <input type="hidden" name="date" x-model="selectedDate">
                            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                                <span>Date</span>
                                <span class="toggleBtn cursor-pointer text-xl">–</span>
                            </div>
                            <div class="toggleContent">
                                <input type="hidden" name="date" x-model="selectedDate">
    
                                <div class="flex items-center justify-center gap-2 mb-2">
                                    <button type="button" @click="prevMonth()" class="text-gray-400 hover:text-white">&lt;</button>
                                    <span x-text="monthNames[month] + ' ' + year"></span>
                                    <button type="button" @click="nextMonth()" class="text-gray-400 hover:text-white">&gt;</button>
                                </div>
    
                                <div class="grid grid-cols-7 gap-1 text-center text-gray-400 text-xs">
                                    <template x-for="d in daysInMonth()" :key="d">
                                        <span 
                                            class="py-1 cursor-pointer rounded transition-colors"
                                            :class="selectedDate === formatDate(year, month, d) 
                                                    ? 'bg-blue-500 text-white' 
                                                    : 'hover:bg-gray-600'"
                                            @click="selectDate(d)"
                                            x-text="d">
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                                <span>Location</span>
                                <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                            </div>
                            <div class="toggleContent flex flex-wrap gap-2">
                                @foreach ($locations as $loc)
                                    <label class="px-3 py-1 rounded-full border cursor-pointer {{ request('address') == $loc ? 'border-blue-500 text-blue-400' : 'border-gray-500 text-gray-400' }}">
                                        <input type="radio" name="address" value="{{ $loc }}" class="hidden" @checked(request('address') == $loc) />
                                        {{ $loc }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                                <span>Price Range</span>
                                <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                            </div>
                            <div class="toggleContent w-full flex items-center gap-2">
                                <input type="text" id="price_min" name="price_min" placeholder="Min"
                                       value="{{ request('price_min') }}"
                                       class="w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
                                <input type="text" id="price_max" name="price_max" placeholder="Max"
                                       value="{{ request('price_max') }}"
                                       class="w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
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
                        <a href="{{ route('sparring.detail', $athlete->id) }}" class="block">
                            <div class="rounded-lg lg:rounded-xl bg-neutral-800 p-2 sm:p-3 shadow-md hover:shadow-lg transition-shadow">
                                <div class="aspect-[3/4] overflow-hidden rounded-md bg-neutral-700 mb-2 sm:mb-3">
                                    @if ($athlete->athleteDetail && $athlete->athleteDetail->image)
                                        <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}"
                                             class="h-full w-full object-cover"
                                             alt="{{ $athlete->name }}"
                                             onerror="this.src='{{ asset('images/athlete/athlete-1.png') }}'">
                                    @else
                                        <img src="{{ asset('images/athlete/athlete-1.png') }}"
                                             class="h-full w-full object-cover"
                                             alt="{{ $athlete->name }}">
                                    @endif
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium line-clamp-2">{{ $athlete->name }}</h3>
                                <p class="text-xs sm:text-sm text-gray-400 mt-1">
                                    Rp. {{ number_format($athlete->athleteDetail->price_per_session ?? 0, 0, ',', '.') }} / session
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-400">No athletes available at the moment.</div>
                    @endforelse
                </div>

                {{-- Pagination: model pill (Prev • X of Y • Next) --}}
                @php
                    $current = $athletes->currentPage();
                    $last    = $athletes->lastPage();
                    $prevUrl = $current > 1 ? $athletes->appends(request()->query())->url($current - 1) . '#list' : null;
                    $nextUrl = $current < $last ? $athletes->appends(request()->query())->url($current + 1) . '#list' : null;
                @endphp
                <div class="flex justify-center mt-6">
                    <nav class="pager" role="navigation" aria-label="Pagination">
                        {{-- Prev --}}
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

                        {{-- Label tengah --}}
                        <span class="pager-label">{{ $current }} of {{ $last }}</span>

                        {{-- Next --}}
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

        @include('public.cart')
    </div>
@endsection

@push('scripts')
    <script>
        // ===== Atur per-page adaptif: Desktop 8 / Mobile 4 (query ?pp=...)
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

        // Toggle (Location, Price, dll)
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll(".toggleBtn").forEach((btn) => {
                const content = btn.parentElement.nextElementSibling;
                btn.addEventListener("click", () => {
                    if (content.classList.contains("max-h-0")) { content.classList.remove("max-h-0"); btn.textContent = "–"; }
                    else { content.classList.add("max-h-0"); btn.textContent = "+"; }
                });
            });
        });

        // Mobile filter open/close
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

        // Format harga (input)
        function formatNumberInput(input) {
            let value = input.value.replace(/\D/g, "");
            if (!value) { input.value = ""; return; }
            input.value = new Intl.NumberFormat("id-ID").format(value);
        }
        function unformatNumberInput(value) { return (value || "").toString().replace(/\./g, ""); }

        document.addEventListener("DOMContentLoaded", () => {
            const minInput = document.getElementById("price_min");
            const maxInput = document.getElementById("price_max");
            if (minInput && minInput.value) minInput.value = new Intl.NumberFormat("id-ID").format(minInput.value);
            if (maxInput && maxInput.value) maxInput.value = new Intl.NumberFormat("id-ID").format(maxInput.value);

            minInput?.addEventListener("input", () => formatNumberInput(minInput));
            maxInput?.addEventListener("input", () => formatNumberInput(maxInput));

            const form = document.querySelector('#filterSparring form');
            form?.addEventListener("submit", () => {
                if (minInput) minInput.value = unformatNumberInput(minInput.value);
                if (maxInput) maxInput.value = unformatNumberInput(maxInput.value);
            });
        });

        // Mini Alpine Calendar (dummy)
        function calendar(defaultDate = '') {
            const today = new Date();
            let initialDate = defaultDate ? new Date(defaultDate) : today;
            return {
                month: initialDate.getMonth(),
                year : initialDate.getFullYear(),
                selectedDate: defaultDate || '',
                toggle: true,
                monthNames: ["January","February","March","April","May","June","July","August","September","October","November","December"],
                daysInMonth(){ return new Date(this.year, this.month + 1, 0).getDate(); },
                prevMonth(){ this.month === 0 ? (this.month = 11, this.year--) : this.month--; },
                nextMonth(){ this.month === 11 ? (this.month = 0, this.year++) : this.month++; },
                formatDate(y, m, d){ return `${y}-${String(m + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`; },
                selectDate(d){ this.selectedDate = this.formatDate(this.year, this.month, d); }
            }
        }
    </script>
@endpush
