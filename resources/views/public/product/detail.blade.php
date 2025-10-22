@extends('app')
@section('title', 'Products Page - Xander Billiard')

@php
    use Illuminate\Support\Str;

    // ===== FE base URL untuk gambar produk =====
    $feBaseProducts = 'https://demo-xanders.ptbmn.id/images/products/';

    // Normalizer: apapun input (URL penuh / relative / nama file) -> absolut ke FE base di atas
    $normalizeToFeBase = function ($s) use ($feBaseProducts) {
        if (!is_string($s) || trim($s) === '') return null;
        $s = trim($s);

        // Sudah http(s)? — kalau domain FE, pakai basename-nya (anti duplikat path), kalau bukan biarkan apa adanya
        if (preg_match('#^https?://#i', $s)) {
            $parts = parse_url($s);
            $host  = strtolower($parts['host'] ?? '');
            $path  = $parts['path'] ?? '';
            $name  = basename($path);
            if ($host === 'demo-xanders.ptbmn.id') {
                return $name ? $feBaseProducts . $name : $s;
            }
            return $s; // CDN eksternal tetap dipakai apa adanya
        }

        // Relatif dari /images/products
        if (str_starts_with($s, '/images/products/')) {
            return $feBaseProducts . basename($s);
        }

        // Nama file saja
        $name = basename($s);
        if ($name !== '' && $name !== '/' && $name !== '.') {
            return $feBaseProducts . $name;
        }

        return null;
    };

    // ===== Safe helpers =====
    $toCount = function ($v) {
        return is_countable($v) ? count($v) : (is_null($v) ? 0 : (is_array($v) ? count($v) : 0));
    };

    // Pastikan semua variabel ada
    $cartProducts    = $cartProducts    ?? [];
    $cartVenues      = $cartVenues      ?? [];
    $cartSparrings   = $cartSparrings   ?? [];
    $relatedProducts = $relatedProducts ?? [];
    $relatedPriceMap = $relatedPriceMap ?? [];

    // Hitung cartCount dengan aman
    $cartCount = $toCount($cartProducts) + $toCount($cartVenues) + $toCount($cartSparrings);
    $relCount  = $toCount($relatedProducts);

    // Detail product guard
    $detail      = $detail ?? null;
    $detailId    = $detail->id    ?? 0;
    $detailName  = $detail->name  ?? 'Product';
    $detailDesc  = $detail->description ?? '';
    $detailPrice = (int) ($detail->pricing ?? 0);

    // Diskon (opsional dari controller)
    $detailHasDiscount     = $detailHasDiscount     ?? false;
    $detailDiscountPercent = (float) ($detailDiscountPercent ?? 0);
    $detailFinalPrice      = (int)   ($detailFinalPrice ?? $detailPrice);

    // ===== Kumpulkan gambar detail lalu normalisasi ke FE base =====
    $imagesRaw = [];
    if ($detail) {
        if (is_array($detail->images)) {
            $imagesRaw = $detail->images;
        } elseif (is_string($detail->images ?? '') && trim($detail->images) !== '') {
            $decoded = json_decode($detail->images, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) $imagesRaw = $decoded;
        }
        // fallback dari kolom lain (kalau ada)
        if (empty($imagesRaw) && !empty($detail->first_image_url ?? null)) {
            $imagesRaw = [$detail->first_image_url];
        }
    }

    // Normalisasi ke FE base (hasil akhir: URL absolut ke demo-xanders)
    $images = [];
    foreach ($imagesRaw as $im) {
        $norm = $normalizeToFeBase($im);
        if ($norm) $images[] = $norm;
    }
    // unique & reindex
    $images = array_values(array_unique($images));

    // Main image: gunakan gambar pertama; kalau kosong, pakai placeholder
    $mainImagePath = $images[0] ?? 'https://placehold.co/400x600?text=No+Image';
@endphp

@push('styles')
<style>
  :root { color-scheme: dark; }
  html, body { height:100%; background-color:#0a0a0a; overscroll-behavior-y:none; }
  #app, main { background-color:#0a0a0a; }
  body::before { content:""; position:fixed; inset:0; background:#0a0a0a; pointer-events:none; z-index:-1; }
  body { -webkit-overflow-scrolling:touch; touch-action:pan-y; }
  img { color:transparent; }

  .product-card { background:#2a2a2a; border-radius:14px; overflow:hidden; transition:transform .25s ease, box-shadow .25s ease; }
  .product-card:hover { transform:translateY(-4px); box-shadow:0 10px 24px rgba(0,0,0,.45); }
  .product-image-wrapper { width:100%; height:280px; background:#1a1a1a; }
  .product-image-wrapper img { width:100%; height:100%; object-fit:cover; display:block; }
  .product-info { padding:0.9rem 1rem 1.1rem; }
  .product-title { font-size:1rem; font-weight:700; color:#fff; margin:0 0 .45rem 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .product-price { font-size:.9rem; color:#9ca3af; }

  .related-section { margin-top: 1.25rem !important; }
  @media (min-width:768px){ .related-section { margin-top: 1.5rem !important; } }
  @media (min-width:1024px){ .related-section { margin-top: 2rem !important; } }

  .rel-wrapper { position:relative; }
  .no-scrollbar::-webkit-scrollbar { display:none; }
  .no-scrollbar { -ms-overflow-style:none; scrollbar-width:none; }

  .rel-track{ -webkit-overflow-scrolling:touch; padding: 0 8px 2px 8px; scroll-behavior: auto; }
  
  .rel-row-grid { display:flex; gap:16px; }
  @media (min-width:768px){
    .rel-track.grid { overflow:visible; padding:0; }
    .rel-row-grid{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; }
  }
  @media (min-width:1024px){
    .rel-row-grid{ grid-template-columns:repeat(5,minmax(0,1fr)); gap:24px; }
  }

  .rel-track.carousel{ overflow-x:auto; scroll-snap-type:x mandatory; }
  .rel-track.carousel.scrolling{ scroll-snap-type: none; scroll-behavior: auto; }
  
  .rel-row-scroll{ display:flex; gap:16px; flex-wrap:nowrap; }
  .rel-card{ scroll-snap-align:start; }
  
  @media (min-width:768px){
    .rel-card.carousel-item{ flex:0 0 240px; max-width:240px; }
  }
  @media (min-width:1024px){
    .rel-card.carousel-item{ flex:0 0 260px; max-width:260px; }
  }
  @media (max-width:767px){
    .rel-row-scroll .rel-card{ flex:0 0 76vw; max-width:76vw; }
  }

  .rel-nav{
    display:none; position:absolute; top:50%; transform:translateY(-50%);
    width:42px; height:42px; border-radius:9999px; background:#1f2937; color:#e5e7eb;
    border:1px solid rgba(255,255,255,.15); align-items:center; justify-content:center; 
    box-shadow:0 8px 18px rgba(0,0,0,.35); z-index:5; cursor:pointer; transition: all 0.2s;
  }
  .rel-nav:hover{ background:#374151; transform:translateY(-50%) scale(1.1); }
  .rel-nav:active{ transform:translateY(-50%) scale(0.95); }
  .rel-nav.left{ left:-10px; }
  .rel-nav.right{ right:-10px; }
  @media (min-width:768px){ .rel-nav.carousel-only{ display:flex; } }

  .rel-fade{ position:absolute; top:0; bottom:0; width:60px; pointer-events:none; z-index:4; opacity:0; transition: opacity 0.3s; }
  .rel-fade.left{ left:0; background:linear-gradient(90deg, #0a0a0a 0%, rgba(10,10,10,0) 100%); }
  .rel-fade.right{ right:0; background:linear-gradient(-90deg, #0a0a0a 0%, rgba(10,10,10,0) 100%); }
  .rel-fade.show{ opacity:1; }
  
  @media (max-width:767px){
    .product-image-wrapper { height:250px; }
    .product-title { font-size:.98rem; }
    .product-price { font-size:.88rem; }
  }
</style>
@endpush

@section('content')
<main
  class="min-h-screen px-4 sm:px-6 md:px-20 py-8 md:py-10 bg-neutral-900 text-white"
  style="background-image:url('{{ asset('images/bg/background_1.png') }}'); background-size:cover; background-position:center; background-repeat:no-repeat;"
>
  <!-- Product Detail Section -->
  <div class="flex flex-col md:flex-row md:space-x-12">
    <div class="flex flex-col items-center md:items-start gap-3 md:gap-4 w-full md:w-[320px]">
      <nav class="text-[11px] sm:text-xs text-gray-400 mb-1 md:mb-3 self-start">
        <a href="{{ route('index') }}">Home</a> /
        <a href="{{ route('products.landing') }}">Product</a> /
        @if($detailId)
          <a href="{{ route('products.detail', ['id'=>$detailId, 'slug'=>Str::slug($detailName)]) }}">{{ $detailName }}</a>
        @else
          <span>{{ $detailName }}</span>
        @endif
      </nav>

      <img id="mainImage" alt="{{ $detailName }}"
           class="rounded-md w-full max-w-[320px] object-cover bg-neutral-800"
           height="400" width="320"
           src="{{ $mainImagePath }}"
           loading="eager" decoding="async"
           onerror="this.onerror=null;this.src='https://placehold.co/400x600?text=No+Image';" />

      <div class="flex items-center justify-between w-full max-w-[320px]">
        <button aria-label="Previous image" class="text-gray-400 hover:text-white focus:outline-none">
          <i class="fas fa-chevron-left text-xs"></i>
        </button>

        <div class="flex gap-2">
          @foreach ($images as $index => $thumbUrl)
            <img
              alt="{{ $detailName.' #'.$index }}"
              class="rounded-md w-[60px] h-[60px] sm:w-[70px] sm:h-[70px] object-cover cursor-pointer border-2 {{ $index==0 ? 'border-blue-600' : 'border-gray-600' }} thumbnail-image bg-neutral-800"
              height="60" width="60" src="{{ $thumbUrl }}" loading="lazy" decoding="async"
              onerror="this.src='https://placehold.co/400x600?text=No+Image'"
              onclick="changeMainImage('{{ $thumbUrl }}', this)"
            />
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
            <span class="text-gray-400 text-sm line-through">
              Rp. {{ number_format($detailPrice, 0, ',', '.') }}
            </span>
            <span class="inline-flex items-center rounded-full bg-red-500 text-white text-[11px] font-bold px-2 py-0.5">
              -{{ number_format($detailDiscountPercent, 0) }}%
            </span>
          </div>
          <div class="text-white font-extrabold text-2xl leading-tight mt-1">
            Rp. {{ number_format($detailFinalPrice, 0, ',', '.') }},-
          </div>
        @else
          <p class="text-gray-300 text-xl md:text-2xl">
            Rp. {{ number_format($detailPrice, 0, ',', '.') }},-
          </p>
        @endif
      </div>

      <hr class="border-gray-700 mb-6" />

      <p class="text-xs md:text-sm text-gray-400 mt-6 max-w-xl">{{ $detailDesc }}</p>

      <form id="addToCartForm"
            action="{{ \Illuminate\Support\Facades\Route::has('cart.add.product') ? route('cart.add.product') : url('/cart/add/product') }}"
            method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $detailId }}">
        <div class="flex items-center gap-3 mt-4">
          <div class="flex items-center gap-2">
            <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()" class="w-7 h-7 rounded-full bg-neutral-800 text-white border border-neutral-700 hover:bg-neutral-700 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">-</button>
            <input type="number" name="quantity" value="1" min="1" readonly class="w-14 text-center rounded-md bg-neutral-800 text-white border border-neutral-700 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm pl-4">
            <button type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()" class="w-7 h-7 rounded-full bg-neutral-800 text-white border border-neutral-700 hover:bg-neutral-700 focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm">+</button>
          </div>

          <button type="button" id="addToCartButton"
                  class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-5 rounded-md transition">
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

  <!-- Related Products (INFINITE LOOPING CAROUSEL) -->
  <section class="related-section mb-20">
    <div class="flex justify-between items-center mb-5 md:mb-8">
      <h2 class="text-white font-bold text-xl sm:text-2xl">Related products</h2>
      <a href="{{ route('products.landing') }}" class="text-gray-400 hover:text-white text-xs sm:text-sm transition">See More</a>
    </div>

    @php
      $relMode = $relCount > 5 ? 'carousel' : 'grid';
    @endphp

    <div class="rel-wrapper" data-rel-mode="{{ $relMode }}" data-count="{{ $relCount }}">
      <div class="rel-fade left" id="relFadeLeft"></div>
      <div class="rel-fade right" id="relFadeRight"></div>

      @if ($relMode === 'carousel')
        <button class="rel-nav left carousel-only" type="button" aria-label="Scroll left" id="btnPrev">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="rel-nav right carousel-only" type="button" aria-label="Scroll right" id="btnNext">
          <i class="fas fa-chevron-right"></i>
        </button>
      @endif

      <div id="relTrack" class="rel-track no-scrollbar {{ $relMode }}">
        <div id="relRow" class="{{ $relMode === 'carousel' ? 'rel-row-scroll' : 'rel-row-grid' }}">
          @forelse ($relatedProducts as $product)
            @php
              $slug = Str::slug($product->name ?? 'product');

              // Kumpulkan gambar related → normalisasi ke FE base juga
              $rImagesRaw = [];
              if (is_array($product->images ?? null)) { $rImagesRaw = $product->images; }
              elseif (is_string($product->images ?? '') && trim($product->images) !== '') {
                $dec = json_decode($product->images, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($dec)) $rImagesRaw = $dec;
              }
              $rImages = [];
              foreach ($rImagesRaw as $im) {
                $norm = $normalizeToFeBase($im);
                if ($norm) $rImages[] = $norm;
              }
              $rImages = array_values(array_unique($rImages));
              $img = $rImages[0] ?? 'https://placehold.co/800x800?text=No+Image';

              // Harga
              $basePrice = (int) ($product->pricing ?? 0);
              $map = $relatedPriceMap[$product->id] ?? ['has_discount'=>false, 'discount_percent'=>0, 'final_price'=>$basePrice];
              $rHas = (bool) ($map['has_discount'] ?? false);
              $rPct = (float) ($map['discount_percent'] ?? 0);
              $rFin = (int)   ($map['final_price'] ?? $basePrice);
            @endphp

            <a href="{{ route('products.detail', ['id' => $product->id, 'slug' => $slug]) }}"
               class="rel-card product-card block focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 {{ $relMode === 'carousel' ? 'carousel-item' : '' }}"
               data-product-id="{{ $product->id }}">
              <div class="product-image-wrapper">
                <img src="{{ $img }}" alt="{{ $product->name ?? 'Product' }}" loading="lazy"
                     onerror="this.onerror=null;this.src='https://placehold.co/800x800?text=No+Image';" />
              </div>
              <div class="product-info">
                <h3 class="product-title" title="{{ $product->name ?? 'Product' }}">{{ $product->name ?? 'Product' }}</h3>

                @if ($rHas)
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-[12px] text-gray-400 line-through">
                      Rp. {{ number_format($basePrice, 0, ',', '.') }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-red-500 text-white text-[10px] font-bold px-2 py-0.5">
                      -{{ number_format($rPct, 0) }}%
                    </span>
                  </div>
                  <p class="product-price text-white font-semibold">
                    Rp. {{ number_format($rFin, 0, ',', '.') }},-
                  </p>
                @else
                  <p class="product-price">
                    Rp. {{ number_format($basePrice, 0, ',', '.') }},-
                  </p>
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

  @if (Auth::check() && (Auth::user()->roles ?? '') === 'user')
  <button aria-label="Shopping cart with {{ $cartCount }} items" onclick="showCart && showCart()"
          class="fixed right-4 sm:right-6 top-[60%] bg-[#2a2a2a] rounded-full w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center shadow-lg">
    <i class="fas fa-shopping-cart text-white text-2xl sm:text-3xl"></i>
    @if ($cartCount > 0)
      <span class="absolute top-0.5 right-0.5 bg-blue-600 text-white text-[10px] sm:text-xs font-semibold rounded-full w-4.5 h-4.5 sm:w-5 sm:h-5 flex items-center justify-center">
        {{ $cartCount }}
      </span>
    @endif
  </button>
  @endif

  {{-- aman walau partial belum ada --}}
  @includeIf('public.cart')
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function changeMainImage(imageUrl, clickedThumb) {
    const main = document.getElementById('mainImage');
    if (main) main.src = imageUrl;
    document.querySelectorAll('.thumbnail-image').forEach(t => {
      t.classList.remove('border-blue-600'); t.classList.add('border-gray-600');
    });
    clickedThumb.classList.remove('border-gray-600');
    clickedThumb.classList.add('border-blue-600');
  }

  const cartForm = document.getElementById('addToCartForm');
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
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(async res => {
        Swal.close();

        if (res.status === 401) {
          Swal.fire({
            title: 'Belum Login!',
            text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
            icon: 'warning',
            confirmButtonText: 'Login Sekarang',
            confirmButtonColor: '#3085d6',
            background: '#1E1E1F',
            color: '#FFFFFF'
          }).then(() => {
            window.location.href = '/login';
          });
          return;
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

  const addBtn = document.getElementById('addToCartButton');
  if (addBtn) {
    addBtn.addEventListener('click', (e) => {
      e.preventDefault();

      const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
      const userRole = "{{ Auth::check() ? (Auth::user()->roles ?? '') : '' }}";

      if (!isLoggedIn) {
        Swal.fire({
          title: 'Belum Login!',
          text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
          icon: 'warning',
          confirmButtonText: 'Login Sekarang',
          confirmButtonColor: '#3085d6',
          background: '#1E1E1F',
          color: '#FFFFFF'
        }).then(() => {
          window.location.href = '/login';
        });
        return;
      }

      if (userRole !== 'user') {
        Swal.fire({
          title: 'Akses Ditolak!',
          text: 'Hanya user yang bisa menambahkan ke keranjang.',
          icon: 'error',
          confirmButtonColor: '#3085d6',
          background: '#1E1E1F',
          color: '#FFFFFF'
        });
        return;
      }

      const form = document.getElementById('addToCartForm');
      if (form) {
        const event = new Event('submit', { bubbles: true, cancelable: true });
        form.dispatchEvent(event);
      }
    });
  }

  // ===== INFINITE LOOPING CAROUSEL =====
  (function() {
    const wrapper = document.querySelector('.rel-wrapper');
    const track = document.getElementById('relTrack');
    const row = document.getElementById('relRow');
    const fadeL = document.getElementById('relFadeLeft');
    const fadeR = document.getElementById('relFadeRight');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');

    if (!wrapper || !track || !row) return;
    if (wrapper.dataset.relMode !== 'carousel') return;

    const originals = Array.from(row.children).filter(el => !el.classList.contains('cloned'));
    const itemCount = originals.length;
    if (itemCount === 0) return;
    if (row.dataset.loopInit === '1') return;
    row.dataset.loopInit = '1';

    function createClones() {
      const before = document.createDocumentFragment();
      const after = document.createDocumentFragment();

      originals.forEach(el => {
        const cloneAfter = el.cloneNode(true);
        cloneAfter.classList.add('cloned');
        cloneAfter.setAttribute('aria-hidden', 'true');
        after.appendChild(cloneAfter);
      });

      originals.slice().forEach(el => {
        const cloneBefore = el.cloneNode(true);
        cloneBefore.classList.add('cloned');
        cloneBefore.setAttribute('aria-hidden', 'true');
        before.appendChild(cloneBefore);
      });

      row.insertBefore(before, row.firstChild);
      row.appendChild(after);
    }

    createClones();

    function getItemWidth() {
      const allCards = row.querySelectorAll('.rel-card');
      if (!allCards.length) return 0;
      const card = allCards[0];
      const cardWidth = card.offsetWidth;
      const gap = parseFloat(getComputedStyle(row).gap || '16');
      return cardWidth + gap;
    }

    let itemWidth = 0;
    let isAdjusting = false;

    function resetPosition() {
      itemWidth = getItemWidth();
      if (!itemWidth) return;
      const centerPos = itemWidth * itemCount;
      isAdjusting = true;
      track.scrollLeft = centerPos;
      setTimeout(() => { isAdjusting = false; updateFades(); }, 50);
    }

    function updateFades() {
      fadeL?.classList.add('show');
      fadeR?.classList.add('show');
    }

    resetPosition();

    let scrollTimer;
    function handleScroll() {
      clearTimeout(scrollTimer);
      scrollTimer = setTimeout(() => {
        if (isAdjusting) return;
        itemWidth = getItemWidth();
        if (!itemWidth) return;

        const scrollPos = track.scrollLeft;
        const batch = itemWidth * itemCount;
        const rel = scrollPos % batch;
        const idx = Math.floor(scrollPos / batch);

        if (idx >= 2) {
          isAdjusting = true; track.scrollLeft = batch + rel;
          setTimeout(() => { isAdjusting = false; }, 50);
        } else if (idx <= 0 && scrollPos < batch * 0.5) {
          isAdjusting = true; track.scrollLeft = batch + rel;
          setTimeout(() => { isAdjusting = false; }, 50);
        }
        updateFades();
      }, 100);
    }

    track.addEventListener('scroll', handleScroll, { passive: true });

    function smoothScrollBy(delta) {
      track.scrollTo({ left: track.scrollLeft + delta, behavior: 'smooth' });
    }

    btnPrev?.addEventListener('click', () => {
      const step = Math.floor(track.clientWidth * 0.75);
      smoothScrollBy(-step);
    });
    btnNext?.addEventListener('click', () => {
      const step = Math.floor(track.clientWidth * 0.75);
      smoothScrollBy(step);
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => { resetPosition(); }, 200);
    });

    let touchStartX = 0, touchStartScroll = 0, isTouching = false;
    track.addEventListener('touchstart', (e) => {
      touchStartX = e.touches[0].clientX;
      touchStartScroll = track.scrollLeft;
      isTouching = true;
    }, { passive: true });
    track.addEventListener('touchmove', (e) => {
      if (!isTouching) return;
      const x = e.touches[0].clientX;
      track.scrollLeft = touchStartScroll + (touchStartX - x);
    }, { passive: true });
    track.addEventListener('touchend', () => { isTouching = false; }, { passive: true });

    track.addEventListener('wheel', (e) => {
      if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
      if (e.shiftKey) {
        e.preventDefault();
        track.scrollLeft += e.deltaY;
      }
    }, { passive: false });

    track.setAttribute('tabindex', '0');
    track.addEventListener('keydown', (e) => {
      const step = Math.floor(track.clientWidth * 0.75);
      if (e.key === 'ArrowLeft') { e.preventDefault(); smoothScrollBy(-step); }
      else if (e.key === 'ArrowRight') { e.preventDefault(); smoothScrollBy(step); }
    });

    updateFades();
    const observer = new ResizeObserver(() => {
      if (!isAdjusting) {
        const w = getItemWidth();
        if (w !== itemWidth && w > 0) resetPosition();
      }
    });
    observer.observe(row);
  })();
</script>
@endpush
