@extends('app')
@section('title', 'Products Page - Xander Billiard')
@push('styles')
    <style>
        .max-h-0 {
            max-height: 0 !important;
        }
        
        @media (min-width: 1024px) {
            .lg-hidden {
                display: none !important;
            }
        }

        /* Mobile filter toggle */
        @media (max-width: 1023px) {
            .sm-hidden {
                display: none !important;
            }

            .mobile-filter-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
                display: none;
            }
            
            .mobile-filter-overlay.active {
                display: block;
            }
            
            .mobile-filter-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 85%;
                max-width: 320px;
                height: 100%;
                background: rgb(23, 23, 23);
                z-index: 50;
                transition: left 0.3s ease;
                overflow-y: auto;
            }
            
            .mobile-filter-sidebar.open {
                left: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
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
                            <input type="text" id="price_min" name="price_min" placeholder="Min"
                                value="{{ request('price_min') }}"
                                class="w-1/2 rounded border border-gray-400 px-2 py-1 focus:outline-none focus:ring focus:ring-blue-500" />
                            <input type="text" id="price_max" name="price_max" placeholder="Max"
                                value="{{ request('price_max') }}"
                                class="w-1/2 rounded border border-gray-400 px-2 py-1 focus:outline-none focus:ring focus:ring-blue-500" />
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

                <!-- Pagination -->
                <div class="flex justify-center mt-6">
                    {{ $products->links('vendor.pagination.tailwind') }}
                </div>

            </section>

        </div>

        <!-- Cart Button -->
        <button aria-label="Shopping cart with {{ count($cartProducts) + count($cartSparrings ?? []) + count($cartVenues ?? []) }} items" onclick="showCart()"
            class="fixed right-4 sm:right-6 bottom-6 sm:bottom-10 bg-[#2a2a2a] rounded-full w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow">
            <i class="fas fa-shopping-cart text-white text-2xl sm:text-3xl"></i>
            <span class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                {{ count($cartProducts) + count($cartSparrings ?? []) + count($cartVenues ?? []) }}
            </span>
        </button>

        @include('public.cart')
    </div>

    <script>
        function formatNumberInput(input) {
            let value = input.value.replace(/\D/g, ""); // hapus non digit
            if (!value) {
                input.value = "";
                return;
            }
            input.value = new Intl.NumberFormat("id-ID").format(value);
        }

        function unformatNumberInput(input) {
            return input.value.replace(/\./g, ""); // hilangkan titik pemisah
        }

        document.addEventListener("DOMContentLoaded", () => {
            const minInput = document.getElementById("price_min");
            const maxInput = document.getElementById("price_max");

            // format langsung saat load jika ada value
            if (minInput.value) minInput.value = new Intl.NumberFormat("id-ID").format(minInput.value);
            if (maxInput.value) maxInput.value = new Intl.NumberFormat("id-ID").format(maxInput.value);

            // format ketika user ketik
            minInput.addEventListener("input", () => formatNumberInput(minInput));
            maxInput.addEventListener("input", () => formatNumberInput(maxInput));

            // sebelum submit, balikin ke angka biasa (biar server bisa baca)
            minInput.form.addEventListener("submit", () => {
                minInput.value = unformatNumberInput(minInput);
                maxInput.value = unformatNumberInput(maxInput);
            });
        });
    </script>
@endsection

@push('scripts')
   <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Toggle functionality for filter sections
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

            // Mobile filter sidebar functionality
            const mobileFilterBtn = document.getElementById("mobileFilterBtn");
            const filterProduct = document.getElementById("filterProduct");
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

            if (mobileFilterBtn) {
                mobileFilterBtn.addEventListener("click", openMobileFilter);
            }

            if (closeMobileFilter) {
                closeMobileFilter.addEventListener("click", closeMobileFilterFunc);
            }

            if (mobileFilterOverlay) {
                mobileFilterOverlay.addEventListener("click", closeMobileFilterFunc);
            }
        });
   </script>

   
@endpush