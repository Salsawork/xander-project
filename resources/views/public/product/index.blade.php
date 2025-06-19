@extends('app')
@section('title', 'Products Page - Xander Billiard')

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">Home / Product</p>
            <h2 class="text-4xl font-bold uppercase text-white">Explore All Products</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-24 py-18">
            <!-- <div class="w-full space-y-6 rounded-xl bg-neutral-900 p-4 text-white text-sm">
                <div>
                    <input type="text" placeholder="Search"
                        class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Categories</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="space-y-2">
                        @foreach (['Play Cue', 'Break Cue', 'Jump Cue', 'Accessories'] as $item)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="category"
                                    class="h-4 w-4 appearance-none rounded-full border border-white checked:border-white checked:ring-4 checked:ring-white/10 focus:ring-0" />
                                <span>{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Brand</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="space-y-2">
                        @foreach (['Mezz', 'Predator', 'Cuetec', 'Others'] as $item)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" name="brand"
                                    class="h-4 w-4 appearance-none rounded-full border border-white checked:border-white checked:ring-4 checked:ring-white/10 focus:ring-0" />
                                <span>{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Condition</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="flex gap-3">
                        <button
                            class="rounded-md border border-gray-400 bg-transparent px-3 py-1.5 text-white hover:bg-gray-800">
                            New
                        </button>
                        <button
                            class="rounded-md border border-gray-400 bg-transparent px-3 py-1.5 text-white hover:bg-gray-800">
                            Used
                        </button>
                    </div>
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Price Range</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="w-full">
                        <input type="range" class="w-full" />
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                        Filter
                    </button>
                    <button
                        class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">
                        Reset
                    </button>
                </div>
            </div> -->
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
                                <img src="{{ $imagePath }}" 
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover"
                                    onerror="this.src='https://placehold.co/400x600?text=No+Image'" />
                            </div>
                            <h4 class="text-sm font-medium">{{ $product->name }}</h4>
                            <p class="text-sm text-gray-400">Rp. {{ number_format($product->pricing, 0, ',', '.') }}.-</p>
                        </div>
                    </span>
                @endforeach
            </section>
        </div>
        <button aria-label="Shopping cart with {{ count($carts) + count($sparrings ?? []) }} items" onclick="showCart()"
            class="fixed right-6 bottom-10 bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
            <i class="fas fa-shopping-cart text-white text-3xl">
            </i>
            <span
                class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                {{ count($carts) + count($sparrings ?? []) }}
            </span>
        </button>
        @include('public.cart')
    </div>
@endsection
