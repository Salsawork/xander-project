@extends('app')
@section('title', 'Products Page - Xander Billiard')

@php
    use Illuminate\Support\Str;

    $cartProducts  = json_decode(request()->cookie('cartProducts') ?? '[]', true);
    $cartVenues    = json_decode(request()->cookie('cartVenues') ?? '[]', true);
    $cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
    $cartCount     = count($cartProducts) + count($cartVenues) + count($cartSparrings);

    // Dummy Related Products
    $dummyRelatedProducts = [
        ['id'=>1,'name'=>'CUELEES TU + CT + 12 MAX','pricing'=>6700000,'image'=>'https://images.unsplash.com/photo-1629077832449-2d4c7e95f677?w=800&h=800&fit=crop'],
        ['id'=>2,'name'=>'MEZZ PBOI','pricing'=>2250000,'image'=>'https://images.unsplash.com/photo-1611068813580-7cbc98d72def?w=800&h=800&fit=crop'],
        ['id'=>3,'name'=>'AIR RUSH GOLD SW','pricing'=>4750000,'image'=>'https://images.unsplash.com/photo-1604881991720-f91add269bed?w=800&h=800&fit=crop'],
        ['id'=>4,'name'=>'EXCEED 16 N/LE + HP2','pricing'=>3500000,'image'=>'https://via.placeholder.com/800x800/666666/FFFFFF?text=No+Image'],
        ['id'=>5,'name'=>'EXCEED 16 N/LE + HP2','pricing'=>3500000,'image'=>'https://via.placeholder.com/800x800/666666/FFFFFF?text=No+Image'],
    ];
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

  .rel-track{
    overflow-x:auto;
    scroll-snap-type:x mandatory;
    -webkit-overflow-scrolling:touch;
    padding: 0 8px 2px 8px;
  }
  .rel-row{ display:flex; gap:16px; }
  .rel-card{
    flex:0 0 76vw;
    max-width:76vw;
    scroll-snap-align:start;
  }

  .rel-nav{
    display:none;
    position:absolute; top:50%; transform:translateY(-50%);
    width:42px; height:42px; border-radius:9999px;
    background:#1f2937; color:#e5e7eb; border:1px solid rgba(255,255,255,.15);
    align-items:center; justify-content:center; box-shadow:0 8px 18px rgba(0,0,0,.35);
  }
  .rel-nav:hover{ background:#374151; }
  .rel-nav.left{ left:-10px; }
  .rel-nav.right{ right:-10px; }

  @media (min-width:768px){
    .rel-track{ overflow:visible; padding:0; }
    .rel-row{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; }
    .rel-card{ flex:none; max-width:none; }
    .rel-nav{ display:flex; }
  }
  @media (min-width:1024px){
    .rel-row{ grid-template-columns:repeat(5,minmax(0,1fr)); gap:24px; }
  }

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
        <a href="{{ route('products.detail', ['id'=>$detail->id, 'slug'=>Str::slug($detail->name)]) }}">{{ $detail->name }}</a>
      </nav>

      @php
        $mainImagePath = 'https://placehold.co/400x600?text=No+Image';
        if (!empty($detail->images) && is_array($detail->images) && !empty($detail->images[0])) {
            $img = $detail->images[0];
            $mainImagePath = (!str_starts_with($img,'http://') && !str_starts_with($img,'https://') && !str_starts_with($img,'/storage/'))
                ? asset('storage/uploads/'.$img)
                : $img;
        }
      @endphp

      <img id="mainImage" alt="{{ $detail->name }}"
           class="rounded-md w-full max-w-[320px] object-cover bg-neutral-800"
           height="400" width="320" src="{{ $mainImagePath }}" loading="eager" decoding="async" />

      <div class="flex items-center justify-between w-full max-w-[320px]">
        <button aria-label="Previous image" class="text-gray-400 hover:text-white focus:outline-none">
          <i class="fas fa-chevron-left text-xs"></i>
        </button>

        <div class="flex gap-2">
          @if (!empty($detail->images) && is_array($detail->images))
            @foreach ($detail->images as $index => $image)
              @php
                $thumbImagePath = 'https://placehold.co/400x600?text=No+Image';
                if (!empty($image)) {
                    $thumbImagePath = (!str_starts_with($image,'http://') && !str_starts_with($image,'https://') && !str_starts_with($image,'/storage/'))
                        ? asset('storage/uploads/'.$image)
                        : $image;
                }
              @endphp
              <img
                alt="{{ $detail->name.' #'.$index }}"
                class="rounded-md w-[60px] h-[60px] sm:w-[70px] sm:h-[70px] object-cover cursor-pointer border-2 {{ $index==0 ? 'border-blue-600' : 'border-gray-600' }} thumbnail-image bg-neutral-800"
                height="60" width="60" src="{{ $thumbImagePath }}" loading="lazy" decoding="async"
                onerror="this.src='https://placehold.co/400x600?text=No+Image'"
                onclick="changeMainImage('{{ $thumbImagePath }}', this)"
              />
            @endforeach
          @endif
        </div>

        <button aria-label="Next image" class="text-gray-400 hover:text-white focus:outline-none">
          <i class="fas fa-chevron-right text-xs"></i>
        </button>
      </div>
    </div>

    <section class="flex-1 mt-8 md:mt-0">
      <h1 class="text-white font-extrabold text-xl md:text-2xl leading-tight">
        {{ $detail->name }}
      </h1>
      <p class="text-gray-300 mt-1 mb-6 text-sm md:text-base">
        Rp. {{ number_format($detail->pricing, 0, ',', '.') }},-
      </p>

      <hr class="border-gray-700 mb-6" />

      <p class="text-xs md:text-sm text-gray-400 mt-6 max-w-xl">
        {{ $detail->description }}
      </p>

      <form id="addToCartForm" action="{{ route('cart.add.product') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $detail->id }}">
        <button
          type="submit"
          class="mt-6 bg-blue-600 hover:bg-blue-700 text-white text-xs md:text-sm font-medium py-2 px-4 rounded"
        >
          <i class="fas fa-shopping-cart mr-2"></i>
          Add to cart
        </button>
      </form>

      <hr class="border-gray-700 my-6" />

      <div class="flex items-center space-x-3 text-xs text-gray-400 max-w-xl">
        <span>Share :</span>
        <a aria-label="Instagram" class="hover:text-white" href="#"><i class="fab fa-instagram"></i></a>
        <a aria-label="Twitter"   class="hover:text-white" href="#"><i class="fab fa-twitter"></i></a>
        <a aria-label="Facebook"  class="hover:text-white" href="#"><i class="fab fa-facebook-f"></i></a>
      </div>

      <hr class="border-gray-700 mt-3" />
    </section>
  </div>

  <!-- Related Products -->
  <section class="related-section mb-20">
    <div class="flex justify-between items-center mb-5 md:mb-8">
      <h2 class="text-white font-bold text-xl sm:text-2xl">Related products</h2>
      <a href="{{ route('products.landing') }}" class="text-gray-400 hover:text-white text-xs sm:text-sm transition">See More</a>
    </div>

    <div class="rel-wrapper">
      <button class="rel-nav left" type="button" aria-label="Scroll left" data-rel-prev="#relTrack">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button class="rel-nav right" type="button" aria-label="Scroll right" data-rel-next="#relTrack">
        <i class="fas fa-chevron-right"></i>
      </button>

      <div id="relTrack" class="rel-track no-scrollbar">
        <div class="rel-row">
          @foreach ($dummyRelatedProducts as $product)
            <a href="#" class="rel-card product-card block">
              <div class="product-image-wrapper">
                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" />
              </div>
              <div class="product-info">
                <h3 class="product-title" title="{{ $product['name'] }}">{{ $product['name'] }}</h3>
                <p class="product-price">Rp. {{ number_format($product['pricing'], 0, ',', '.') }},-</p>
              </div>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <!-- Floating Cart Button -->
  <button aria-label="Shopping cart with {{ $cartCount }} items" onclick="showCart()"
          class="fixed right-4 sm:right-6 top-[60%] bg-[#2a2a2a] rounded-full w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center shadow-lg">
    <i class="fas fa-shopping-cart text-white text-2xl sm:text-3xl"></i>
    @if ($cartCount > 0)
      <span class="absolute top-0.5 right-0.5 bg-blue-600 text-white text-[10px] sm:text-xs font-semibold rounded-full w-4.5 h-4.5 sm:w-5 sm:h-5 flex items-center justify-center">
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
  function changeMainImage(imageUrl, clickedThumb) {
    const main = document.getElementById('mainImage');
    if (main) main.src = imageUrl;
    document.querySelectorAll('.thumbnail-image').forEach(t => {
      t.classList.remove('border-blue-600'); t.classList.add('border-gray-600');
    });
    clickedThumb.classList.remove('border-gray-600');
    clickedThumb.classList.add('border-blue-600');
  }

  document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    Swal.fire({ title: 'Mohon tunggu...', text: 'Sedang memproses permintaan Anda.', allowOutsideClick: false, didOpen: () => Swal.showLoading(), background: '#1E1E1F', color: '#FFFFFF' });

    fetch(this.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
      body: new FormData(this)
    })
    .then(res => res.json())
    .then(data => {
      Swal.close();
      if (data.success) {
        Swal.fire({ title: 'Berhasil!', text: 'Produk ditambahkan ke keranjang', icon: 'success', confirmButtonText: 'OK', confirmButtonColor: '#3085d6', background: '#1E1E1F', color: '#FFFFFF', iconColor: '#4BB543' })
          .then(() => location.reload());
      } else {
        Swal.fire({ title: 'Gagal!', text: data.message || 'Terjadi kesalahan, coba lagi.', icon: 'error', confirmButtonColor: '#3085d6', background: '#1E1E1F', color: '#FFFFFF' });
      }
    })
    .catch(() => {
      Swal.close();
      Swal.fire({ title: 'Error!', text: 'Terjadi kesalahan jaringan. Silakan coba beberapa saat lagi.', icon: 'error', confirmButtonColor: '#3085d6', background: '#1E1E1F', color: '#FFFFFF' });
    });
  });

  function smoothScroll(el, left) { if (!el) return; el.scrollTo({ left, behavior: 'smooth' }); }
  document.querySelectorAll('[data-rel-prev]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const track = document.querySelector(btn.getAttribute('data-rel-prev'));
      const step = (track?.clientWidth || 0) * 0.9;
      smoothScroll(track, Math.max(0, track.scrollLeft - step));
    });
  });
  document.querySelectorAll('[data-rel-next]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const track = document.querySelector(btn.getAttribute('data-rel-next'));
      const step = (track?.clientWidth || 0) * 0.9;
      smoothScroll(track, Math.min((track?.scrollWidth||0), track.scrollLeft + step));
    });
  });
</script>
@endpush
