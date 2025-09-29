@extends('app')
@section('title', 'Products Page - Xander Billiard')
@php
    $cartProducts = json_decode(request()->cookie('cartProducts') ?? '[]', true);
    $cartVenues = json_decode(request()->cookie('cartVenues') ?? '[]', true);
    $cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
    $cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);
@endphp

@section('content')
    <main class="min-h-screen px-20 py-10 bg-neutral-900 text-white">
        <div class="flex flex-col md:flex-row md:space-x-12">
            <div class="flex flex-col items-center md:items-start space-y-3 md:space-y-4 w-full md:w-[320px]">
                <nav class="text-xs text-gray-400 mb-3 self-start">
                    <a href="{{ route('index') }}">
                        Home
                    </a>
                    /
                    <a href="{{ route('products.landing') }}">
                        Product
                    </a>
                    /
                    <a href="{{ route('products.detail', $detail->id) }}">
                        {{ $detail->name }}
                    </a>
                </nav>
                @php
                    $mainImagePath = 'https://placehold.co/400x600?text=No+Image';
                    if (!empty($detail->images) && is_array($detail->images) && !empty($detail->images[0])) {
                        $img = $detail->images[0];
                        if (
                            !str_starts_with($img, 'http://') &&
                            !str_starts_with($img, 'https://') &&
                            !str_starts_with($img, '/storage/')
                        ) {
                            $mainImagePath = asset('storage/uploads/' . $img);
                        } else {
                            $mainImagePath = $img;
                        }
                    }
                @endphp
                <img id="mainImage" alt="{{ $detail->name }}" class="rounded-md w-full max-w-[320px] object-cover"
                    height="400" src="{{ $mainImagePath }}" width="320" />
                <div class="flex space-x-3">
                    <button aria-label="Previous image"
                        class="text-gray-400 hover:text-white focus:outline-none self-center">
                        <i class="fas fa-chevron-left text-xs">
                        </i>
                    </button>
                    <div class="flex space-x-2">
                        @foreach ($detail->images as $index => $image)
                            @php
                                $thumbImagePath = 'https://placehold.co/400x600?text=No+Image';
                                if (!empty($image)) {
                                    if (
                                        !str_starts_with($image, 'http://') &&
                                        !str_starts_with($image, 'https://') &&
                                        !str_starts_with($image, '/storage/')
                                    ) {
                                        $thumbImagePath = asset('storage/uploads/' . $image);
                                        $originalImage = $image; // Simpan nama file asli untuk fungsi JS
                                    } else {
                                        $thumbImagePath = $image;
                                        $originalImage = $image;
                                    }
                                }
                            @endphp
                            <img alt="{{ $detail->name . ' #' . $index }}"
                                class="rounded-md w-[70px] h-[70px] object-cover cursor-pointer border-2 {{ $index == 0 ? 'border-blue-600' : 'border-gray-600' }} thumbnail-image"
                                height="70" src="{{ $thumbImagePath }}" width="70"
                                onclick="changeMainImage('{{ $thumbImagePath }}', this)" />
                        @endforeach
                    </div>
                    <button aria-label="Next image" class="text-gray-400 hover:text-white focus:outline-none self-center">
                        <i class="fas fa-chevron-right text-xs">
                        </i>
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
                        class="mt-6 bg-blue-600 hover:bg-blue-700 text-white text-xs md:text-sm font-medium py-2 px-4 rounded"
                        type="submit">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Add to cart
                    </button>
                </form>
                <hr class="border-gray-700 my-6" />
                <div class="flex items-center space-x-3 text-xs text-gray-400 max-w-xl">
                    <span>
                        Share :
                    </span>
                    <a aria-label="Instagram" class="hover:text-white" href="#">
                        <i class="fab fa-instagram">
                        </i>
                    </a>
                    <a aria-label="Twitter" class="hover:text-white" href="#">
                        <i class="fab fa-twitter">
                        </i>
                    </a>
                    <a aria-label="Facebook" class="hover:text-white" href="#">
                        <i class="fab fa-facebook-f">
                        </i>
                    </a>
                </div>
                <hr class="border-gray-700 mt-3" />
            </section>
        </div>
        <button aria-label="Shopping cart with 3 items" onclick="showCart()"
            class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
            <i class="fas fa-shopping-cart text-white text-3xl">
            </i>
            @if ($cartCount > 0)
                <span
                    class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
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
        // Fungsi untuk mengganti gambar utama
        function changeMainImage(imageUrl, clickedThumb) {
            // Ganti gambar utama
            document.getElementById('mainImage').src = imageUrl;

            // Reset border semua thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail-image');
            thumbnails.forEach(thumb => {
                thumb.classList.remove('border-blue-600');
                thumb.classList.add('border-gray-600');
            });

            // Highlight thumbnail yang diklik
            clickedThumb.classList.remove('border-gray-600');
            clickedThumb.classList.add('border-blue-600');
        }

        document.getElementById('addToCartForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Tampilkan SweetAlert
            Swal.fire({
                title: 'Berhasil!',
                text: 'Produk ditambahkan ke keranjang',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                background: '#1E1E1F',
                color: '#FFFFFF',
                iconColor: '#4BB543'
            }).then((result) => {
                // Kirim form setelah user klik OK
                this.submit();
            });
        });
    </script>
@endpush
