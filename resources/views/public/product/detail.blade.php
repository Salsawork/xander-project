@extends('app')
@section('title', 'Products Page - Xander Billiard')

@php
    use Illuminate\Support\Str;

    // ===== FE base URL untuk gambar produk =====
    $feBaseProducts = 'https://xanderbilliard.site/images/products/';

    // Normalizer: apapun input (URL penuh / relative / nama file) -> absolut ke FE base di atas
    $normalizeToFeBase = function ($s) use ($feBaseProducts) {
        if (!is_string($s) || trim($s) === '') {
            return null;
        }
        $s = trim($s);

        if (preg_match('#^https?://#i', $s)) {
            $parts = parse_url($s);
            $host = strtolower($parts['host'] ?? '');
            $path = $parts['path'] ?? '';
            $name = basename($path);
            if ($host === 'demo-xanders.ptbmn.id') {
                return $name ? $feBaseProducts . $name : $s;
            }
            return $s; // CDN eksternal tetap dipakai apa adanya
        }

        if (str_starts_with($s, '/images/products/')) {
            return $feBaseProducts . basename($s);
        }

        $name = basename($s);
        if ($name !== '' && $name !== '/' && $name !== '.') {
            return $feBaseProducts . $name;
        }

        return null;
    };

    $toCount = function ($v) {
        return is_countable($v) ? count($v) : (is_null($v) ? 0 : (is_array($v) ? count($v) : 0));
    };

    $cartProducts     = $cartProducts     ?? [];
    $cartVenues       = $cartVenues       ?? [];
    $cartSparrings    = $cartSparrings    ?? [];
    $relatedProducts  = $relatedProducts  ?? [];
    $relatedPriceMap  = $relatedPriceMap  ?? [];

    $cartCount = $toCount($cartProducts) + $toCount($cartVenues) + $toCount($cartSparrings);
    $relCount  = $toCount($relatedProducts);

    $detail                = $detail ?? null;
    $detailId              = $detail->id ?? 0;
    $detailName            = $detail->name ?? 'Product';
    $detailDesc            = $detail->description ?? '';
    $detailPrice           = (int) ($detail->pricing ?? 0);
    $detailHasDiscount     = $detailHasDiscount ?? false;
    $detailDiscountPercent = (float) ($detailDiscountPercent ?? 0);
    $detailFinalPrice      = (int) ($detailFinalPrice ?? $detailPrice);

    $imagesRaw = [];
    if ($detail) {
        if (is_array($detail->images)) {
            $imagesRaw = $detail->images;
        } elseif (is_string($detail->images ?? '') && trim($detail->images) !== '') {
            $decoded = json_decode($detail->images, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $imagesRaw = $decoded;
            }
        }
        if (empty($imagesRaw) && !empty($detail->first_image_url ?? null)) {
            $imagesRaw = [$detail->first_image_url];
        }
    }

    $images = [];
    foreach ($imagesRaw as $im) {
        $norm = $normalizeToFeBase($im);
        if ($norm) {
            $images[] = $norm;
        }
    }
    $images = array_values(array_unique($images));

    $mainImagePath = $images[0] ?? 'https://placehold.co/400x600?text=No+Image';
@endphp

@push('styles')
    <style>
        :root { color-scheme: dark; }
        html, body { height: 100%; background-color: #0a0a0a; overscroll-behavior-y: none; }
        #app, main { background-color: #0a0a0a; }
        body::before { content: ""; position: fixed; inset: 0; background: #0a0a0a; pointer-events: none; z-index: -1; }
        body { -webkit-overflow-scrolling: touch; touch-action: pan-y; }
        img { color: transparent; }

        /* ====== CARD & TEXT ====== */
        .product-card { background: #2a2a2a; border-radius: 14px; overflow: hidden; transition: transform .25s ease, box-shadow .25s ease; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,.45); }
        .product-info { padding: 0.9rem 1rem 1.1rem; }
        .product-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0 0 .45rem 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .product-price { font-size: .9rem; color: #9ca3af; }

        /* ====== RELATED LAYOUT ====== */
        .related-section { margin-top: 1.25rem !important; }
        @media (min-width:768px){ .related-section { margin-top: 1.5rem !important; } }
        @media (min-width:1024px){ .related-section { margin-top: 2rem !important; } }
        .rel-wrapper { position: relative; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .rel-track { -webkit-overflow-scrolling: touch; padding: 0 8px 2px 8px; scroll-behavior: auto; }
        .rel-row-grid { display: flex; gap: 16px; }
        @media (min-width:768px){ .rel-track.grid { overflow: visible; padding: 0; } .rel-row-grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 20px; } }
        @media (min-width:1024px){ .rel-row-grid { grid-template-columns: repeat(5, minmax(0,1fr)); gap: 24px; } }
        .rel-track.carousel { overflow-x: auto; scroll-snap-type: x mandatory; }
        .rel-track.carousel.scrolling { scroll-snap-type: none; scroll-behavior: auto; }
        .rel-row-scroll { display: flex; gap: 16px; flex-wrap: nowrap; }
        .rel-card { scroll-snap-align: start; }
        @media (min-width:768px){ .rel-card.carousel-item { flex: 0 0 240px; max-width: 240px; } }
        @media (min-width:1024px){ .rel-card.carousel-item { flex: 0 0 260px; max-width: 260px; } }
        @media (max-width:767px){ .rel-row-scroll .rel-card { flex: 0 0 76vw; max-width: 76vw; } }
        .rel-nav { display: none; position: absolute; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border-radius: 9999px; background: #1f2937; color: #e5e7eb; border: 1px solid rgba(255,255,255,.15); align-items: center; justify-content: center; box-shadow: 0 8px 18px rgba(0,0,0,.35); z-index: 5; cursor: pointer; transition: all .2s; }
        .rel-nav:hover { background: #374151; transform: translateY(-50%) scale(1.1); }
        .rel-nav:active { transform: translateY(-50%) scale(.95); }
        .rel-nav.left { left: -10px; }
        .rel-nav.right { right: -10px; }
        @media (min-width:768px){ .rel-nav.carousel-only { display: flex; } }
        .rel-fade { position: absolute; top: 0; bottom: 0; width: 60px; pointer-events: none; z-index: 4; opacity: 0; transition: opacity .3s; }
        .rel-fade.left { left: 0; background: linear-gradient(90deg, #0a0a0a 0%, rgba(10,10,10,0) 100%); }
        .rel-fade.right { right: 0; background: linear-gradient(-90deg, #0a0a0a 0%, rgba(10,10,10,0) 100%); }
        .rel-fade.show { opacity: 1; }

        /* ====== IMAGE LOADING UI (spinner / camera) ====== */
        .img-wrapper { position: relative; background: #1a1a1a; overflow: hidden; }
        .img-wrapper > img { width: 100%; height: 100%; display: block; object-fit: cover; opacity: 0; transition: opacity .28s ease; }
        .img-wrapper > img.is-loaded { opacity: 1; }
        .img-loading { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: #1a1a1a; z-index: 1; }
        .img-loading.is-hidden { display: none; }
        .spinner { width: 36px; height: 36px; border: 3px solid rgba(255,255,255,.2); border-top-color: #a3a3a3; border-radius: 50%; animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .camera-icon { width: 42px; height: 42px; color: rgba(255,255,255,.4); }
        @media (prefers-reduced-motion: reduce){ .img-wrapper > img { transition: none; } .spinner { animation: none; } }

        /* Sizing helpers */
        .main-img-box { width: 100%; max-width: 320px; height: 400px; border-radius: .5rem; }
        @media (max-width:767px){ .main-img-box { height: 360px; } }
        .thumb-box { width: 60px; height: 60px; border-radius: .5rem; }
        @media (min-width:640px){ .thumb-box { width: 70px; height: 70px; } }
    </style>
@endpush

@section('content')
    <main class="min-h-screen px-4 sm:px-6 md:px-20 py-8 md:py-10 bg-neutral-900 text-white"
        style="background-image:url('{{ asset('images/bg/background_1.png') }}'); background-size:cover; background-position:center; background-repeat:no-repeat;">
        <!-- Product Detail Section -->
        <div class="flex flex-col md:flex-row md:space-x-12">
            <div class="flex flex-col items-center md:items-start gap-3 md:gap-4 w-full md:w-[320px]">
                <nav class="text-[11px] sm:text-xs text-gray-400 mb-1 md:mb-3 self-start">
                    <a href="{{ route('index') }}">Home</a> /
                    <a href="{{ route('products.landing') }}">Product</a> /
                    @if ($detailId)
                        <a href="{{ route('products.detail', ['id' => $detailId, 'slug' => Str::slug($detailName)]) }}">{{ $detailName }}</a>
                    @else
                        <span>{{ $detailName }}</span>
                    @endif
                </nav>

                <!-- MAIN IMAGE with loading overlay + fallback chain -->
                <div class="img-wrapper main-img-box bg-neutral-800">
                    <div class="img-loading" aria-hidden="true" role="progressbar" aria-label="Loading image">
                        <div class="spinner" aria-hidden="true"></div>
                    </div>
                    <img id="mainImage" alt="{{ $detailName }}"
                         class="object-cover rounded-md"
                         src="{{ $mainImagePath }}"
                         data-lazy-load
                         data-src-candidates='@json($images)'
                         loading="eager" decoding="async"
                         onerror="this.onerror=null;this.src='https://placehold.co/400x600?text=No+Image';" />
                </div>

                <!-- THUMBNAILS -->
                <div class="flex items-center justify-between w-full max-w-[320px]">
                    <button aria-label="Previous image" class="text-gray-400 hover:text-white focus:outline-none">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>

                    <div class="flex gap-2">
                        @foreach ($images as $index => $thumbUrl)
                            <div class="img-wrapper thumb-box bg-neutral-800 border-2 {{ $index == 0 ? 'border-blue-600' : 'border-gray-600' }}">
                                <div class="img-loading">
                                    <svg class="camera-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <img alt="{{ $detailName . ' #' . $index }}"
                                     class="object-cover cursor-pointer thumbnail-image"
                                     src="{{ $thumbUrl }}"
                                     data-lazy-load
                                     loading="lazy" decoding="async"
                                     onerror="this.onerror=null;this.src='https://placehold.co/400x600?text=No+Image'"
                                     onclick="changeMainImage('{{ $thumbUrl }}', this)" />
                            </div>
                        @endforeach
                    </div>

                    <button aria-label="Next image" class="text-gray-400 hover:text-white focus:outline-none">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>

            <section class="flex-1 mt-8 md:mt-0">
                <h1 class="text-white font-extrabold text-xl md:text-2xl leading-tight">{{ $detailName }}</h1>

                <div class="mt-2 mb-6">
                    @if ($detailHasDiscount)
                        <div class="inline-flex items-center gap-2">
                            <span class="text-gray-400 text-sm line-through">Rp. {{ number_format($detailPrice, 0, ',', '.') }}</span>
                            <span class="inline-flex items-center rounded-full bg-red-500 text-white text-[11px] font-bold px-2 py-0.5">-{{ number_format($detailDiscountPercent, 0) }}%</span>
                        </div>
                        <div class="text-white font-extrabold text-2xl leading-tight mt-1">Rp. {{ number_format($detailFinalPrice, 0, ',', '.') }},-</div>
                    @else
                        <p class="text-gray-300 text-xl md:text-2xl">Rp. {{ number_format($detailPrice, 0, ',', '.') }},-</p>
                    @endif
                </div>

                <hr class="border-gray-700 mb-6" />

                {{-- Deskripsi aman (newline -> <br>) --}}
                <div class="text-xs md:text-sm text-gray-400 mt-6 max-w-xl break-words leading-relaxed">
                    {!! nl2br(e($detailDesc)) !!}
                </div>

                {{-- FORM ADD TO CART (pola sama dengan Venues Page: cek via JS) --}}
                <form id="addToCartForm" action="{{ \Illuminate\Support\Facades\Route::has('cart.add.product') ? route('cart.add.product') : url('/cart/add/product') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $detailId }}">
                    <div class="flex items-center gap-3 mt-4">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()" class="w-7 h-7 rounded-full bg-neutral-800 text-white border border-neutral-700 hover:bg-neutral-700 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">-</button>
                            <input type="number" name="quantity" value="1" min="1" readonly class="w-14 text-center rounded-md bg-neutral-800 text-white border border-neutral-700 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm pl-4">
                            <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()" class="w-7 h-7 rounded-full bg-neutral-800 text-white border border-neutral-700 hover:bg-neutral-700 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">+</button>
                        </div>

                        <button type="button" id="addToCartButton" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-5 rounded-md transition">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Add to cart
                        </button>
                    </div>
                </form>

                <hr class="border-gray-700 my-6" />

                <div class="flex items-center space-x-3 text-xs text-gray-400 max-w-xl">
                    <span>Share :</span>
                    <a aria-label="WhatsApp" class="hover:text-white" href="https://wa.me/6281284679921" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i></a>
                    <a aria-label="Twitter" class="hover:text-white" href="#"><i class="fab fa-twitter"></i></a>
                    <a aria-label="Facebook" class="hover:text-white" href="#"><i class="fab fa-facebook-f"></i></a>
                </div>

                <hr class="border-gray-700 mt-3" />
            </section>
        </div>

        <!-- Related Products (GRID/CAROUSEL) -->
        <section class="related-section mb-20">
            <div class="flex justify-between items-center mb-5 md:mb-8">
                <h2 class="text-white font-bold text-xl sm:text-2xl">Related products</h2>
                <a href="{{ route('products.landing') }}" class="text-gray-400 hover:text-white text-xs sm:text-sm transition">See More</a>
            </div>

            @php $relMode = $relCount > 5 ? 'carousel' : 'grid'; @endphp

            <div class="rel-wrapper" data-rel-mode="{{ $relMode }}" data-count="{{ $relCount }}">
                <div class="rel-fade left" id="relFadeLeft"></div>
                <div class="rel-fade right" id="relFadeRight"></div>

                @if ($relMode === 'carousel')
                    <button class="rel-nav left carousel-only" type="button" aria-label="Scroll left" id="btnPrev"><i class="fas fa-chevron-left"></i></button>
                    <button class="rel-nav right carousel-only" type="button" aria-label="Scroll right" id="btnNext"><i class="fas fa-chevron-right"></i></button>
                @endif

                <div id="relTrack" class="rel-track no-scrollbar {{ $relMode }}">
                    <div id="relRow" class="{{ $relMode === 'carousel' ? 'rel-row-scroll' : 'rel-row-grid' }}">
                        @forelse ($relatedProducts as $product)
                            @php
                                $slug      = Str::slug($product->name ?? 'product');
                                $rImagesRaw = [];
                                if (is_array($product->images ?? null)) {
                                    $rImagesRaw = $product->images;
                                } elseif (is_string($product->images ?? '') && trim($product->images) !== '') {
                                    $dec = json_decode($product->images, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) { $rImagesRaw = $dec; }
                                }
                                $rImages = [];
                                foreach ($rImagesRaw as $im) { $norm = $normalizeToFeBase($im); if ($norm) { $rImages[] = $norm; } }
                                $rImages = array_values(array_unique($rImages));
                                $img = $rImages[0] ?? 'https://placehold.co/800x800?text=No+Image';

                                $basePrice = (int) ($product->pricing ?? 0);
                                $map       = $relatedPriceMap[$product->id] ?? ['has_discount' => false, 'discount_percent' => 0, 'final_price' => $basePrice];
                                $rHas = (bool) ($map['has_discount'] ?? false);
                                $rPct = (float) ($map['discount_percent'] ?? 0);
                                $rFin = (int)   ($map['final_price'] ?? $basePrice);
                            @endphp

                            <a href="{{ route('products.detail', ['id' => $product->id, 'slug' => $slug]) }}"
                               class="rel-card product-card block focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 {{ $relMode === 'carousel' ? 'carousel-item' : '' }}"
                               data-product-id="{{ $product->id }}">
                                <div class="img-wrapper" style="height:280px;">
                                    <div class="img-loading"><div class="spinner"></div></div>
                                    <img src="{{ $img }}" alt="{{ $product->name ?? 'Product' }}"
                                         data-lazy-load
                                         onerror="this.onerror=null;this.src='https://placehold.co/800x800?text=No+Image';" />
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title" title="{{ $product->name ?? 'Product' }}">{{ $product->name ?? 'Product' }}</h3>
                                    @if ($rHas)
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[12px] text-gray-400 line-through">Rp. {{ number_format($basePrice, 0, ',', '.') }}</span>
                                            <span class="inline-flex items-center rounded-full bg-red-500 text-white text-[10px] font-bold px-2 py-0.5">-{{ number_format($rPct, 0) }}%</span>
                                        </div>
                                        <p class="product-price text-white font-semibold">Rp. {{ number_format($rFin, 0, ',', '.') }},-</p>
                                    @else
                                        <p class="product-price">Rp. {{ number_format($basePrice, 0, ',', '.') }},-</p>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-gray-400 text-sm">No related products.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- Floating Cart hanya untuk role "user" --}}
        @if (Auth::check() && (Auth::user()->roles ?? '') === 'user')
            <button aria-label="Shopping cart with {{ $cartCount }} items" onclick="showCart && showCart()" class="fixed right-4 sm:right-6 top-[60%] bg-[#2a2a2a] rounded-full w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center shadow-lg">
                <i class="fas fa-shopping-cart text-white text-2xl sm:text-3xl"></i>
                @if ($cartCount > 0)
                    <span class="absolute top-0.5 right-0.5 bg-blue-600 text-white text-[10px] sm:text-xs font-semibold rounded-full w-4.5 h-4.5 sm:w-5 sm:h-5 flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </button>
        @endif

        @includeIf('public.cart')
    </main>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ====== State login/role (pola sama seperti Venues Page) ======
        const isLoggedIn = @json(auth()->check());
        const userRole   = @json(\Illuminate\Support\Facades\Auth::check() ? (\Illuminate\Support\Facades\Auth::user()->roles ?? null) : null);

        // =============== IMAGE LOADING HANDLER (spinner + fallback chain) ===============
        function initImageLoading(root = document){
            const imgs = root.querySelectorAll('img[data-lazy-load]');
            imgs.forEach(img => {
                const wrapper = img.closest('.img-wrapper');
                const loader  = wrapper ? wrapper.querySelector('.img-loading') : null;

                // Parse candidate list (if provided)
                let candidates = [];
                try { candidates = JSON.parse(img.getAttribute('data-src-candidates') || '[]'); } catch(_) {}
                if(!Array.isArray(candidates) || !candidates.length){ candidates = [img.getAttribute('src')].filter(Boolean); }

                let i = 0;
                const showLoader = () => loader && loader.classList.remove('is-hidden');
                const hideLoader = () => loader && loader.classList.add('is-hidden');
                const markLoaded = () => { img.classList.add('is-loaded'); hideLoader(); };
                const tryNext = () => { if(i < candidates.length - 1){ i++; showLoader(); const next = candidates[i]; if(next && img.src !== next){ img.src = next; } } else { markLoaded(); } };

                if (img.complete && img.naturalWidth > 0) { markLoaded(); }
                else { showLoader(); }

                img.addEventListener('load', () => { if(img.naturalWidth > 0) { markLoaded(); } }, { passive: true });
                img.addEventListener('error', tryNext, { passive: true });
            });
        }

        // =============== MAIN IMAGE SWITCHER ===============
        function changeMainImage(imageUrl, clickedThumb){
            const main = document.getElementById('mainImage');
            if(!main) return;

            // Reset selection state on thumbnails
            document.querySelectorAll('.thumbnail-image').forEach(t => {
                const box = t.closest('.img-wrapper');
                if(box){ box.classList.remove('border-blue-600'); box.classList.add('border-gray-600'); }
            });
            const clickedBox = clickedThumb.closest('.img-wrapper');
            if(clickedBox){ clickedBox.classList.remove('border-gray-600'); clickedBox.classList.add('border-blue-600'); }

            // Show loader again for main image
            const mainWrap = main.closest('.img-wrapper');
            const loader = mainWrap ? mainWrap.querySelector('.img-loading') : null;
            if(main){ main.classList.remove('is-loaded'); }
            if(loader){ loader.classList.remove('is-hidden'); }

            // Update src (keep existing candidates on main)
            main.src = imageUrl;
        }

        // =============== ADD TO CART (disamakan dengan Venues Page: gate by login & role) ===============
        const cartForm = document.getElementById('addToCartForm');
        const addBtn   = document.getElementById('addToCartButton');

        if (addBtn) {
            addBtn.addEventListener('click', (e) => {
                e.preventDefault();

                if (!isLoggedIn) {
                    Swal.fire({
                        title: 'Belum Login!',
                        text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
                        icon: 'warning',
                        confirmButtonText: 'Login Sekarang',
                        confirmButtonColor: '#3085d6',
                        background: '#1E1E1F',
                        color: '#FFFFFF'
                    }).then(() => { window.location.href = '/login'; });
                    return;
                }

                if (userRole !== 'user') {
                    Swal.fire({
                        title: 'Akses Ditolak!',
                        text: 'Hanya role "user" yang bisa menambahkan ke keranjang.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                        background: '#1E1E1F',
                        color: '#FFFFFF'
                    });
                    return;
                }

                // Lolos gate → submit form
                const form = document.getElementById('addToCartForm');
                if (form) form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            });
        }

        if (cartForm) {
            cartForm.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Mohon tunggu...',
                    text: 'Sedang memproses permintaan Anda.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    background: '#1E1E1F',
                    color: '#FFFFFF'
                });

                const formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(async res => {
                    Swal.close();

                    // Tangani 401/403 agar sama seperti Venues
                    if (res.status === 401) {
                        Swal.fire({
                            title: 'Belum Login!',
                            text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
                            icon: 'warning',
                            confirmButtonText: 'Login Sekarang',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF'
                        }).then(() => { window.location.href = '/login'; });
                        return null;
                    }
                    if (res.status === 403) {
                        const data403 = await res.json().catch(() => null);
                        Swal.fire({
                            title: 'Akses ditolak',
                            text: data403?.message || 'Hanya role "user" yang diizinkan.',
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF'
                        });
                        return null;
                    }

                    const data = await res.json().catch(() => null);
                    if (res.ok && data && (data.success ?? false)) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Produk berhasil ditambahkan ke keranjang!',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF',
                            iconColor: '#4BB543'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data?.message || 'Terjadi kesalahan, coba lagi.',
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF'
                        });
                    }
                    return null;
                })
                .catch(err => {
                    console.error(err);
                    Swal.close();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan jaringan. Silakan coba beberapa saat lagi.',
                        icon: 'error',
                        confirmButtonColor: '#3085d6',
                        background: '#1E1E1F',
                        color: '#FFFFFF'
                    });
                });
            });
        }

        // Init image loaders after DOM ready
        document.addEventListener('DOMContentLoaded', () => { initImageLoading(); });
        // (Jika ada JS carousel tambahan, masukkan di sini)
    </script>
@endpush
