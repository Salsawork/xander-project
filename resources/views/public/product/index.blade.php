@extends('app')
@section('title', 'Products Page - Xander Billiard')

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">Home / Product</p>
            <h2 class="text-4xl font-bold uppercase text-white">Explore All Products</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-24 py-18">
            <!-- Sidebar filter (disembunyikan) -->

            <section class="lg:col-span-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <span onclick="window.location='{{ route('products.detail', $product->id) }}'">
                        <div class="rounded-xl bg-neutral-800 p-3 shadow-md">
                            <div class="aspect-[3/4] overflow-hidden rounded-md bg-neutral-700 mb-3">
                                @php
                                    $imagePath = 'https://placehold.co/400x600?text=No+Image';

                                    if (!empty($product->images) && is_array($product->images)) {
                                        foreach ($product->images as $img) {
                                            if (!empty($img)) {
                                                $imagePath = asset('storage/uploads/' . $img);
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <img
                                    src="{{ $imagePath }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover"
                                    onerror="this.src='https://placehold.co/400x600?text=No+Image'"
                                />
                            </div>

                            <h4 class="text-sm font-medium">{{ $product->name }}</h4>

                            {{-- Format harga: Rp xx.xxx.xxx --}}
                            <p class="text-sm text-gray-400">
                                Rp {{ number_format((float) $product->pricing, 0, ',', '.') }}
                            </p>
                        </div>
                    </span>
                @endforeach
            </section>
        </div>

        <button
            aria-label="Shopping cart with {{ count($carts) + count($sparrings ?? []) }} items"
            onclick="showCart()"
            class="fixed right-6 bottom-10 bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg"
        >
            <i class="fas fa-shopping-cart text-white text-3xl"></i>
            <span
                class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                {{ count($carts) + count($sparrings ?? []) }}
            </span>
        </button>

        @include('public.cart')
    </div>
@endsection

