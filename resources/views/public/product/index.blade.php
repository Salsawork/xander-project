@extends('app')
@section('title', 'Products Page - Xander Billiard')

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">
                <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Product
            </p>
            <h2 class="text-4xl font-bold uppercase text-white">Explore All Products</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-24 py-18">

            <aside class="w-full space-y-6 rounded-xl bg-neutral-900 p-4 text-white text-sm">
                <form method="GET" action="{{ route('products.landing') }}">
            
                    <!-- Search -->
                    <div>
                        <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                            class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
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
                                class="flex-1 rounded border border-gray-400 px-3 py-1.5 text-sm hover:bg-gray-600 {{ request('condition') == 'new' ? 'bg-gray-600' : '' }}">
                                New
                            </button>
                            <button type="submit" name="condition" value="used"
                                class="flex-1 rounded border border-gray-400 px-3 py-1.5 text-sm hover:bg-gray-600 {{ request('condition') == 'used' ? 'bg-gray-600' : '' }}">
                                Used
                            </button>
                        </div>
                    </div>
            
                    <!-- Price Range -->
                    <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                        <span>Price Range</span>
                        <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                    </div>
                    <div class="toggleContent overflow-hidden max-h-96 transition-all duration-300">
                        <div class="w-full flex items-center gap-2">
                            <input type="range" name="price" min="0" max="1000000"
                                value="{{ request('price', 0) }}" class="w-full accent-blue-600">
                        </div>
                    </div>
            
                    <!-- Buttons -->
                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                            Filter
                        </button>
                        <a href="{{ route('products.landing') }}"
                            class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">
                            Reset
                        </a>
                    </div>
            
                </form>
            </aside>
            

            <section class="lg:col-span-4 flex flex-col gap-6">

                <!-- Product Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse ($products as $product)
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
                                    <img src="{{ $imagePath }}" alt="{{ $product->name }}"
                                        class="h-full w-full object-cover"
                                        onerror="this.src='https://placehold.co/400x600?text=No+Image'" />
                                </div>
                                <h4 class="text-sm font-medium">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-400">
                                    Rp {{ number_format((float) $product->pricing, 0, ',', '.') }}
                                </p>
                            </div>
                        </span>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-400">
                            No products found.
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-6">
                    {{ $products->links('vendor.pagination.tailwind') }}
                </div>

            </section>

        </div>

        <button aria-label="Shopping cart with {{ count($carts) + count($sparrings ?? []) }} items" onclick="showCart()"
            class="fixed right-6 bottom-10 bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
            <i class="fas fa-shopping-cart text-white text-3xl"></i>
            <span
                class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                {{ count($carts) + count($sparrings ?? []) }}
            </span>
        </button>

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                const toggles = document.querySelectorAll(".toggleBtn");
            
                toggles.forEach((btn) => {
                    const content = btn.parentElement.nextElementSibling;
            
                    btn.addEventListener("click", () => {
                        if (content.classList.contains("max-h-0")) {
                            content.classList.remove("max-h-0");
                            btn.textContent = "–";
                        } else {
                            content.classList.add("max-h-0");
                            btn.textContent = "+";
                        }
                    });
                });
            });
            </script>
            
            <style>
            .max-h-0 {
                max-height: 0 !important;
            }
            </style>
            


        @include('public.cart')
    </div>
@endsection
