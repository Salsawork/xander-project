@extends('app')
@section('title', 'Products Page - Xander Billiard')

@php
    // === Data keranjang dari cookie (produk, venue, sparring) ===
    $cartProducts  = json_decode(request()->cookie('cartProducts') ?? '[]', true);
    $cartVenues    = json_decode(request()->cookie('cartVenues') ?? '[]', true);
    $cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
    $cartCount     = count($cartProducts) + count($cartVenues) + count($cartSparrings);

    /**
     * ===== Dummy 15 products (nama mengikuti yang diminta) =====
     * Mobile: 4 item / halaman
     * Desktop: 12 item / halaman
     */
    use Illuminate\Pagination\LengthAwarePaginator;

    $names = [
        // nama-nama yang “tetap pakai itu”
        'Testing ya',
        'Pool Cue Stick',
        'Billiard Balls Set',
        'Chalk Box',
        // sisanya dilengkapi agar total 15 item
        'Cue Case',
        'Tip Shaper',
        'Cue Tip Glue',
        'Bridge Head',
        'Triangle Rack',
        'Cue Glove',
        'Table Brush',
        'Table Cover',
        'Scoreboard',
        'Chalk Holder',
        'Measuring Tape',
    ];

    // bentuk koleksi produk dummy (15 item)
    $allProducts = collect($names)->values()->map(function ($name, $idx) {
        $i = $idx + 1;
        return (object) [
            'id'      => $i,
            'name'    => $name,
            'pricing' => [39990, 49990, 89990, 129990, 299990, 499990, 999990, 1499990, 1999990, 2499990][array_rand([0,1,2,3,4,5,6,7,8,9])],
            'images'  => ["https://placehold.co/600x900?text=" . urlencode($name)],
        ];
    });

    // deteksi sederhana mobile/desktop via User-Agent
    $ua       = request()->header('User-Agent', '');
    $isMobile = (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod|IEMobile|Opera Mini/i', $ua);
    $perPage  = $isMobile ? 4 : 12;

    $page   = max((int) request('page', 1), 1);
    $slice  = $allProducts->slice(($page - 1) * $perPage, $perPage)->values();

    $products = new LengthAwarePaginator(
        $slice,
        $allProducts->count(),
        $perPage,
        $page,
        ['path' => request()->url(), 'query' => request()->query()]
    );
@endphp

@push('styles')
    <style>
        /* ===== Anti white overscroll (global) ===== */
        :root { color-scheme: dark; }
        html, body {
            height: 100%;
            background-color: #0a0a0a;   /* pastikan root gelap */
            overscroll-behavior-y: none; /* nonaktifkan chain overscroll (Chrome/Android, Edge, Firefox) */
        }
        /* Pastikan wrapper utama dari layout juga gelap */
        #app, main { background-color: #0a0a0a; }

        /* iOS Safari fix: kanvas hitam fixed di balik konten saat rubber-band bounce */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: #0a0a0a;
            pointer-events: none;
            z-index: -1;  /* di belakang semua */
        }

        /* ===== Style bawaan halaman ===== */
        .max-h-0 { max-height: 0 !important; }
        @media (min-width: 1024px) { .lg-hidden { display: none !important; } }

        /* Mobile filter toggle */
        @media (max-width: 1023px) {
            .sm-hidden { display: none !important; }
            .mobile-filter-overlay {
                position: fixed; top:0; left:0; right:0; bottom:0;
                background: rgba(0,0,0,.5); z-index:40; display:none;
            }
            .mobile-filter-overlay.active { display:block; }
            .mobile-filter-sidebar {
                position: fixed; top:0; left:-100%;
                width:85%; max-width:320px; height:100%;
                background: rgb(23,23,23); z-index:50;
                transition:left .3s ease; overflow-y:auto; -webkit-overflow-scrolling:touch;
            }
            .mobile-filter-sidebar.open { left:0; }
        }

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
    <div
        class="min-h-screen bg-neutral-900 text-white"
        style="
            background-image: url('{{ asset('images/bg/background_1.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        "
    >
        <div class="mb-16 bg-cover bg-center p-24 sm-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">
                <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Product
            </p>
            <h2 class="text-4xl font-bold uppercase text-white">Explore All Products</h2>
        </div>
        <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-xs sm:text-sm text-gray-400 mt-1">
                <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Product
            </p>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">Explore All Products</h2>
        </div>

        <!-- Mobile Filter Button -->
        <div class="lg-hidden px-4 sm:px-6 mb-4">
            <button id="mobileFilterBtn" class="w-full bg-transparent rounded border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-400">
                <i class="fas fa-filter"></i>
                Filter & Search
            </button>
        </div>

        <!-- Mobile Filter Overlay -->
        <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 lg:py-12">
            <!-- Filter -->
            <aside id="filterProduct" class="mobile-filter-sidebar">
                <div class="px-4 space-y-6 text-white text-sm">
                    <div class="flex items-center justify-between mb-4 lg-hidden">
                        <h3 class="text-lg font-semibold">Filter & Search</h3>
                        <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
                    </div>

                    <form method="GET" action="{{ route('products.landing') }}">
                        <div>
                            <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                                class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <!-- Categories -->
                        <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                            <span>Categories</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent overflow-hidden max-h-96 transition-all duration-300">
                            <div class="space-y-2">
                                @foreach (['play' => 'Play Cue', 'break' => 'Break Cue', 'jump' => 'Jump Cue', 'accessories' => 'Accessories'] as $value => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="category" value="{{ $value }}"
                                            {{ request('category') == $value ? 'checked' : '' }} class="accent-blue-600" />
                                        <span class="text-sm">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <!-- Brand -->
                        <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                            <span>Brand</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent overflow-hidden max-h-96 transition-all duration-300">
                            <div class="space-y-2">
                                @foreach (['Mezz', 'Predator', 'Cuetec', 'Other'] as $brand)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="brand" value="{{ $brand }}"
                                            {{ request('brand') == $brand ? 'checked' : '' }} class="accent-blue-600" />
                                        <span class="text-sm">{{ $brand == 'Other' ? 'Others' : $brand }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <!-- Condition -->
                        <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                            <span>Condition</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent overflow-hidden max-h-96 transition-all duration-300">
                            <div class="flex gap-3">
                                <button type="submit" name="condition" value="new"
                                    class="flex-1 rounded border border-gray-400 px-3 py-2 text-sm hover:bg-gray-600 {{ request('condition') == 'new' ? 'bg-gray-600' : '' }}">
                                    New
                                </button>
                                <button type="submit" name="condition" value="used"
                                    class="flex-1 rounded border border-gray-400 px-3 py-2 text-sm hover:bg-gray-600 {{ request('condition') == 'used' ? 'bg-gray-600' : '' }}">
                                    Used
                                </button>
                            </div>
                        </div>
                        <!-- Price Range -->
                        <div class="flex items-center justify-between mb-2 font-semibold border-b border-gray-500 pb-1">
                            <span>Price Range</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent w-full flex items-center gap-2">
                            <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}
                                " class="w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
                            <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}
                                " class="w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
                        </div>
                        <!-- Buttons -->
                        <div class="flex gap-2 pt-2">
                            <button type="submit"
                                class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                                Filter
                            </button>
                            <a href="{{ route('products.landing') }}"
                                class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-center text-blue-500 hover:bg-blue-500 hover:text-white">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Products Section -->
            <section class="lg:col-span-4 flex flex-col gap-6">
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
                    @forelse ($products as $product)
                        <div onclick="window.location='{{ route('products.detail', $product->id) }}'" class="cursor-pointer">
                            <div class="rounded-lg lg:rounded-xl bg-neutral-800 p-2 sm:p-3 shadow-md hover:shadow-lg transition-shadow">
                                <div class="aspect-[3/4] overflow-hidden rounded-md bg-neutral-700 mb-2 sm:mb-3">
                                    @php
                                        $imagePath = 'https://placehold.co/400x600?text=No+Image';
                                        if (!empty($product->images) && is_array($product->images)) {
                                            foreach ($product->images as $img) {
                                                if (!empty($img)) {
                                                    $imagePath = preg_match('/^https?:\/\//i', $img)
                                                        ? $img
                                                        : asset('storage/uploads/' . ltrim($img, '/'));
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <img src="{{ $imagePath }}" alt="{{ $product->name }}"
                                        class="h-full w-full object-cover"
                                        onerror="this.src='https://placehold.co/400x600?text=No+Image'" />
                                </div>
                                <h4 class="text-xs sm:text-sm font-medium line-clamp-2">{{ $product->name }}</h4>
                                <p class="text-xs sm:text-sm text-gray-400 mt-1">
                                    Rp {{ number_format((float) $product->pricing, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-400">
                            No products found.
                        </div>
                    @endforelse
                </div>

                <!-- Pagination (custom pill untuk mobile & desktop) -->
                @php
                    $current = $products->currentPage();
                    $last    = $products->lastPage();
                    $prevUrl = $current > 1 ? $products->appends(request()->query())->url($current - 1) : null;
                    $nextUrl = $current < $last ? $products->appends(request()->query())->url($current + 1) : null;
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

                        {{-- Label tengah: "X of Y" (page of pages) --}}
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
                <!-- /Pagination -->
            </section>
        </div>

        <!-- === Floating Cart (sesuai contoh) === -->
        <button
            aria-label="Shopping cart with {{ $cartCount }} items"
            onclick="showCart()"
            class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg"
        >
            <i class="fas fa-shopping-cart text-white text-3xl"></i>
            @if ($cartCount > 0)
                <span class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                    {{ $cartCount }}
                </span>
            @endif
        </button>

        {{-- Modal/Drawer keranjang + fungsi showCart() biasanya ada di partial berikut --}}
        @include('public.cart')
    </div>
@endsection

@push('scripts')
   <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Toggle collapsible filter sections
            document.querySelectorAll(".toggleBtn").forEach((btn) => {
                const content = btn.parentElement.nextElementSibling;
                btn.addEventListener("click", () => {
                    if (content.classList.contains("max-h-0")) {
                        content.classList.remove("max-h-0"); btn.textContent = "–";
                    } else {
                        content.classList.add("max-h-0"); btn.textContent = "+";
                    }
                });
            });

            // Mobile filter sidebar
            const mobileFilterBtn   = document.getElementById("mobileFilterBtn");
            const filterProduct     = document.getElementById("filterProduct");
            const mobileFilterOverlay = document.getElementById("mobileFilterOverlay");
            const closeMobileFilter = document.getElementById("closeMobileFilter");

            function openMobileFilter() {
                filterProduct.classList.add("open");
                mobileFilterOverlay.classList.add("active");
                document.body.style.overflow = "hidden";
            }
            function closeMobileFilterFunc() {
                filterProduct.classList.remove("open");
                mobileFilterOverlay.classList.remove("active");
                document.body.style.overflow = "";
            }
            mobileFilterBtn?.addEventListener("click", openMobileFilter);
            closeMobileFilter?.addEventListener("click", closeMobileFilterFunc);
            mobileFilterOverlay?.addEventListener("click", closeMobileFilterFunc);
        });
   </script>
@endpush
