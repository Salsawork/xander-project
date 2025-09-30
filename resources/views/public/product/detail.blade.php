@extends('app')
@section('title', 'Products Page - Xander Billiard')

@php
    // Cart count from cookies
    $cartProducts  = json_decode(request()->cookie('cartProducts') ?? '[]', true);
    $cartVenues    = json_decode(request()->cookie('cartVenues') ?? '[]', true);
    $cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
    $cartCount     = count($cartProducts) + count($cartVenues) + count($cartSparrings);

    // Gambar utama + thumbnails
    $images = is_array($detail->images ?? null) ? array_values(array_filter($detail->images)) : [];
    $resolveImg = function ($img) {
        if (!$img) return 'https://placehold.co/800x1066?text=No+Image';
        if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/storage/')) {
            return asset('storage/uploads/' . $img);
        }
        return $img;
    };
    $mainImagePath = $resolveImg($images[0] ?? null);

    // Spesifikasi (fallback)
    $specs = [
        'Tip Diameter' => $detail->tip_diameter ?? '12.5 mm',
        'Wrap'         => $detail->wrap ?? 'Luxe Leather Wrap for enhanced grip',
        'Weight'       => $detail->weight ?? 'Adjustable from 18.5 oz to 21 oz',
    ];

    // Related products
    $related = $relatedProducts ?? collect();
@endphp

@push('styles')
<style>
  /* ===== Anti white overscroll (global) ===== */
  :root { color-scheme: dark; }
  html, body {
    height: 100%;
    background:#0a0a0a;
    overscroll-behavior-y: none;
  }
  #app, main { background:#0a0a0a; }
  body::before { content:""; position:fixed; inset:0; background:#0a0a0a; pointer-events:none; z-index:-1; }
  body { -webkit-overflow-scrolling: touch; touch-action: pan-y; }
  img { color: transparent; }

  /* Dekor latar lembut */
  .page-bg{
    --c1: rgba(255,255,255,.04); --c2: transparent;
    background:
      radial-gradient(80rem 40rem at -20% 120%, var(--c1), var(--c2)),
      radial-gradient(50rem 30rem at 120% 20%, var(--c1), var(--c2));
  }

  /* Kartu/cover */
  .card{ background:#2a2a2a; border-radius:14px; box-shadow:0 6px 18px rgba(0,0,0,.28); }

  /* Ukuran gambar utama seperti contoh */
  .main-cover{
    aspect-ratio: 3 / 4;        /* proporsi portrait */
    background:#1f2937;
    width:100%;
    border-radius:14px;          /* rounded-xl feel */
    overflow:hidden;
  }

  /* Thumbnails seperti contoh (84x84, rounded, jarak konsisten) */
  .thumb{
    width:84px; height:84px;
    border-radius:12px;
    object-fit:cover;
    background:#1f2937;
    border:2px solid #4b5563;
    cursor:pointer;
  }
  .thumb-active{ border-color:#2563eb; }

  /* Tombol panah kecil (28px) */
  .nav-dot{
    width:28px; height:28px;
    border-radius:9999px;
    display:grid; place-items:center;
    background:rgba(255,255,255,.12);
    color:#cbd5e1;
    transition:background .2s, color .2s;
  }
  .nav-dot:hover{ background:rgba(255,255,255,.18); color:#fff; }

  /* Grid related products */
  .rel-grid{ display:grid; grid-template-columns:repeat(1,minmax(0,1fr)); gap:1rem; }
  @media(min-width:640px){ .rel-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(min-width:1024px){ .rel-grid{ grid-template-columns:repeat(4,1fr); } }
  @media(min-width:1280px){ .rel-grid{ grid-template-columns:repeat(5,1fr); } }
</style>
@endpush

@section('content')
<main class="min-h-screen page-bg text-white">
  <div class="max-w-7xl mx-auto px-4 md:px-8 lg:px-12 py-6 md:py-10">

    {{-- Breadcrumb --}}
    <nav class="text-sm md:text-xs text-gray-200/90 mb-4 md:mb-5">
      <a class="hover:underline" href="{{ route('index') }}">Home</a>
      <span class="mx-2">/</span>
      <a class="hover:underline" href="{{ route('products.landing') }}">Product</a>
      <span class="mx-2">/</span>
      <span class="text-gray-300">{{ $detail->name }}</span>
    </nav>

    {{-- Top: Image + Detail --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-14 items-start">

      {{-- Left: main image + thumbs --}}
      <div>
        <div class="card overflow-hidden">
          <img id="mainImage"
               class="main-cover"
               src="{{ $mainImagePath }}"
               alt="{{ $detail->name }}"
               loading="eager" decoding="async"
               onerror="this.src='https://placehold.co/800x1066?text=No+Image'">
        </div>

        @if(count($images))
          <div class="mt-4 flex items-center justify-between gap-3">
            <button id="prevBtn" aria-label="Previous image" class="nav-dot">
              <i class="fas fa-chevron-left text-[10px]"></i>
            </button>

            <div class="flex gap-3">
              @foreach ($images as $i => $img)
                @php $thumb = $resolveImg($img); @endphp
                <img
                  data-index="{{ $i }}"
                  src="{{ $thumb }}"
                  alt="{{ $detail->name.' #'.$i }}"
                  class="thumb {{ $i===0 ? 'thumb-active' : '' }}"
                  loading="lazy" decoding="async"
                  onerror="this.src='https://placehold.co/168x168?text=No+Image'">
              @endforeach
            </div>

            <button id="nextBtn" aria-label="Next image" class="nav-dot">
              <i class="fas fa-chevron-right text-[10px]"></i>
            </button>
          </div>
        @endif
      </div>

      {{-- Right: Title, Price, Specs, Desc, CTA, Share --}}
      <div>
        <h1 class="font-extrabold text-2xl md:text-3xl lg:text-4xl leading-tight">
          {{ $detail->name }}
        </h1>
        <p class="text-gray-300 mt-2 mb-6 text-base md:text-lg">
          {{-- FORMAT: Rp 3.500.000 --}}
          Rp {{ number_format($detail->pricing, 0, ',', '.') }}
        </p>

        <hr class="border-gray-700 mb-6" />

        {{-- Specs (2 columns) --}}
        <div class="grid grid-cols-3 gap-y-3 gap-x-4 text-sm">
          @foreach($specs as $label => $value)
            <div class="text-gray-400">{{ $label }}:</div>
            <div class="col-span-2 text-gray-200">{{ $value }}</div>
          @endforeach
        </div>

        {{-- Description --}}
        <p class="text-sm md:text-base text-gray-300 mt-6 leading-relaxed">
          {{ $detail->description }}
        </p>

        {{-- Add to cart --}}
        <form id="addToCartForm" action="{{ route('cart.add.product') }}" method="POST">
          @csrf
          <input type="hidden" name="id" value="{{ $detail->id }}">
          <button type="submit"
                  class="mt-6 inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 px-4 rounded">
            <i class="fas fa-shopping-cart"></i>
            add to cart
          </button>
        </form>

        {{-- Share --}}
        <hr class="border-gray-700 my-6" />
        <div class="flex items-center gap-4 text-sm text-gray-400">
          <span>Share :</span>
          <a class="hover:text-white" aria-label="Instagram" href="#"><i class="fab fa-instagram"></i></a>
          <a class="hover:text-white" aria-label="Twitter"   href="#"><i class="fab fa-twitter"></i></a>
          <a class="hover:text-white" aria-label="Facebook"  href="#"><i class="fab fa-facebook-f"></i></a>
        </div>
        <hr class="border-gray-700 mt-4" />
      </div>
    </div>

    {{-- Related products --}}
    <div class="mt-12 md:mt-16">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg md:text-xl font-semibold">Related products</h2>
        <a href="{{ route('products.landing') }}" class="text-sm text-gray-300 hover:text-white">See More</a>
      </div>

      <div class="rel-grid">
        @forelse ($related as $prod)
          @php
            $pimg = 'https://placehold.co/600x800?text=No+Image';
            if (is_array($prod->images ?? null)) {
              foreach ($prod->images as $im) {
                if (!empty($im)) { $pimg = $resolveImg($im); break; }
              }
            }
          @endphp
          <a href="{{ route('products.detail', $prod->id) }}" class="block">
            <div class="card p-2 hover:shadow-xl transition-shadow">
              <div class="rounded-[12px] overflow-hidden bg-neutral-700" style="aspect-ratio:3/4;">
                <img src="{{ $pimg }}" alt="{{ $prod->name }}" class="w-full h-full object-cover"
                     loading="lazy" decoding="async"
                     onerror="this.src='https://placehold.co/600x800?text=No+Image'">
              </div>
              <div class="px-2 py-3">
                <h3 class="text-sm font-medium line-clamp-2">{{ $prod->name }}</h3>
                <p class="text-sm text-gray-400 mt-1">
                  Rp {{ number_format((float)($prod->pricing ?? 0), 0, ',', '.') }}
                </p>
              </div>
            </div>
          </a>
        @empty
          <div class="text-gray-400">No related products.</div>
        @endforelse
      </div>
    </div>

  </div>

  {{-- Floating Cart Button --}}
  <button
    aria-label="Shopping cart with {{ $cartCount }} items"
    onclick="showCart()"
    class="fixed right-4 md:right-6 bottom-6 md:bottom-8 bg-[#2a2a2a] rounded-full w-14 h-14 md:w-16 md:h-16 flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow"
  >
    <i class="fas fa-shopping-cart text-white text-2xl md:text-3xl"></i>
    @if ($cartCount > 0)
      <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
        {{ $cartCount }}
      </span>
    @endif
  </button>

  @include('public.cart')
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Thumbnail -> main image + prev/next
  (function () {
    const main   = document.getElementById('mainImage');
    const thumbs = Array.from(document.querySelectorAll('.thumb'));
    let idx      = Math.max(0, thumbs.findIndex(t => t.classList.contains('thumb-active')));

    function setActive(i){
      if(!thumbs.length) return;
      idx = (i + thumbs.length) % thumbs.length;
      thumbs.forEach(el => el.classList.remove('thumb-active'));
      const el = thumbs[idx];
      el.classList.add('thumb-active');
      if(main) main.src = el.getAttribute('src');
    }

    thumbs.forEach((el,i)=> el.addEventListener('click', ()=> setActive(i)));
    document.getElementById('prevBtn')?.addEventListener('click', ()=> setActive(idx - 1));
    document.getElementById('nextBtn')?.addEventListener('click', ()=> setActive(idx + 1));
  })();

  // SweetAlert konfirmasi add to cart
  const form = document.getElementById('addToCartForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Berhasil!',
        text: 'Produk ditambahkan ke keranjang',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6',
        background: '#1E1E1F',
        color: '#FFFFFF',
        iconColor: '#4BB543'
      }).then(() => this.submit());
    });
  }
</script>
@endpush
