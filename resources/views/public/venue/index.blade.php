@extends('app')
@section('title', 'Venues - Xander Billiard')
@php
$cartProducts = json_decode(request()->cookie('cartProducts') ?? '[]', true);
$cartVenues = json_decode(request()->cookie('cartVenues') ?? '[]', true);
$cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
$cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);
@endphp

@push('styles')
<style>
    .toggleContent {
        overflow: hidden;
        transition: max-height 0.3s ease;
        max-height: 1000px;
    }

    .toggleContent.max-h-0 {
        max-height: 0;
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
            background: #171717;
            z-index: 50;
            transition: left 0.3s ease;
            overflow-y: auto;
        }

        .mobile-filter-sidebar.open {
            left: 0;
        }
    }

    /* Toggle content */
    .toggleContent {
        overflow: hidden;
        transition: max-height 0.3s ease;
        max-height: 1000px;
        /* default terbuka */
    }

    .toggleContent.max-h-0 {
        max-height: 0;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-neutral-900 text-white">
    <div class="mb-16 bg-cover bg-center p-24 sm-hidden"
        style="background-image: url('/images/bg/product_breadcrumb.png');">
        <p class="text-sm text-gray-400 mt-1"><span onclick="window.location='{{ route('index') }}'">Home</span> / Venue
        </p>
        <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR FAVORITE VENUE HERE</h2>
    </div>

    <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden"
        style="background-image: url('/images/bg/product_breadcrumb.png');">
        <p class="text-xs sm:text-sm text-gray-400 mt-1">
            <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Venue
        </p>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">FIND YOUR FAVORITE VENUE HERE
        </h2>
    </div>

    <div class="lg-hidden px-4 sm:px-6 mb-4">
        <button id="mobileFilterBtn"
            class="w-full bg-transparent rounded border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-400">
            <i class="fas fa-filter"></i>
            Filter & Search
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 sm:py-8 lg:py-12">
        <aside id="filterVenue" class="mobile-filter-sidebar bg-neutral-900">
            <div class="px-4 space-y-6 text-white text-sm">
                <!-- Close Button -->
                <div class="flex items-center justify-between mb-4 lg-hidden">
                    <h3 class="text-lg font-semibold">Filter & Search</h3>
                    <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
                </div>

                <form method="GET" action="{{ route('venues.index') }}">
                    <!-- Search -->
                    <div>
                        <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                            class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <!-- <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Date</span>
                            <span class="text-xl leading-none text-gray-300">–</span>
                        </div>
                        <div class="flex items-center gap-2 justify-center">
                            <button type="button" class="text-gray-400 hover:text-white">&#60;</button>
                            <span>February</span>
                            <button type="button" class="text-gray-400 hover:text-white">&#62;</button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center mt-2 text-xs text-gray-400">
                            @for ($i = 1; $i <= 28; $i++)
                            <span class="py-1">{{ $i }}</span>
                            @endfor
                        </div>
                    </div> -->

                    <div x-data="calendar('{{ request('date') }}')" 
                        class="bg-neutral-900 pt-4 text-white rounded-xl text-sm">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Date</span>
                            <span class="toggleBtn cursor-pointer text-xl">–</span>
                        </div>

                        <div class="toggleContent">
                            <!-- Hidden input agar ikut ke form -->
                            <input type="hidden" name="date" x-model="selectedDate">

                            <div class="flex items-center justify-center gap-2 mb-2">
                                <button type="button" @click="prevMonth()" class="text-gray-400 hover:text-white">&lt;</button>
                                <span x-text="monthNames[month] + ' ' + year"></span>
                                <button type="button" @click="nextMonth()" class="text-gray-400 hover:text-white">&gt;</button>
                            </div>

                            <div class="grid grid-cols-7 gap-1 text-center text-gray-400 text-xs">
                                <template x-for="d in daysInMonth()" :key="d">
                                    <span 
                                        class="py-1 cursor-pointer rounded transition-colors"
                                        :class="selectedDate === formatDate(year, month, d) 
                                                ? 'bg-blue-500 text-white' 
                                                : 'hover:bg-gray-600'"
                                        @click="selectDate(d)"
                                        x-text="d">
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Location</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent flex flex-wrap gap-2 mb-4">
                            @foreach ($addresses as $loc)
                            <label
                                class="px-3 py-1 rounded-full border cursor-pointer {{ request('address') == $loc ? 'border-blue-500 text-blue-400' : 'border-gray-500 text-gray-400' }}">
                                <input type="radio" name="address" value="{{ $loc }}" class="hidden"
                                    @checked(request('address')==$loc) />
                                {{ $loc }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
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
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                            Filter
                        </button>
                        <a href="{{ route('venues.index') }}"
                            class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </aside>
        <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

        <section class="lg:col-span-4 flex flex-col gap-6">
            @forelse ($venues as $venue)
            <div class="group">
                <div onclick="window.location='{{ route('venues.detail', $venue->id) }}'"
                    class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg flex flex-col sm:flex-row items-start sm:items-center p-4 sm:p-6 cursor-pointer transition hover:bg-neutral-700">

                    <!-- Image -->
                    <div class="w-full sm:w-64 h-40 sm:h-36 bg-neutral-700 rounded-lg mb-4 sm:mb-0 sm:mr-6 flex-shrink-0 flex items-center justify-center">
                        <span class="text-gray-500 text-lg sm:text-2xl">Image</span>
                    </div>

                    <!-- Details -->
                    <div class="w-full flex flex-col justify-between px-4">
                            <div class="flex justify-between items-start lg:mb-8">
                                <h3 class="text-lg sm:text-2xl font-bold">{{ $venue->name }}</h3>
                                <div class="flex justify-center items-end sm:mt-2">
                                    <i data-id="{{ $venue->id }}"
                                        class="fa-regular fa-bookmark text-gray-400 text-xl sm:text-2xl sm cursor-pointer hover:text-blue-500 transition"></i>
                                </div>
                            </div>
                            <p class="text-gray-400 text-sm mb-2">{{ $venue->address ?? 'Jakarta' }}</p>
                        <div class="mt-12">
                            <div class="flex items-baseline gap-1 text-sm">
                                <span class="text-gray-400">start from</span>
                                <span class="text-lg sm:text-xl font-bold">Rp. {{ number_format($venue->price ?? 50000, 0, ',', '.') }}</span>
                                <span class="text-gray-400">/ session</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400">No venues found.</div>
            @endforelse

            <!-- Pagination -->
            <div class="mt-6 flex justify-center">{{ $venues->links() }}</div>
        </section>
    </div>
    @include('public.cart')
</div>

<button aria-label="Shopping cart with {{ $cartCount }} items" onclick="showCart()"
    class="fixed right-6 bottom-10 bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
    <i class="fas fa-shopping-cart text-white text-3xl"></i>
    <span
        class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
        {{ $cartCount }}
    </span>
</button>

    <script>
        function syncFavoritesToUrl() {
            let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
            const params = new URLSearchParams(window.location.search);
    
            if (favorites.length > 0) {
                params.set("favorites", favorites.join(","));
            } else {
                params.delete("favorites");
            }
    
            // replaceState supaya tidak reload full
            window.history.replaceState({}, "", `${window.location.pathname}?${params.toString()}`);
            window.location.reload(); // reload utk trigger filter backend
        }
    
        document.addEventListener("DOMContentLoaded", () => {
            let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
            const params = new URLSearchParams(window.location.search);
    
            // ✅ Tambahkan favorites ke URL tanpa menghapus filter lain
            if (favorites.length > 0 && !params.has("favorites")) {
                params.set("favorites", favorites.join(","));
                window.location.href = `${window.location.pathname}?${params.toString()}`;
                return;
            }
    
            // Render icon sesuai data
            document.querySelectorAll("i[data-id]").forEach(icon => {
                const venueId = icon.getAttribute("data-id");
    
                if (favorites.includes(venueId)) {
                    icon.classList.remove("fa-regular", "text-gray-400");
                    icon.classList.add("fa-solid", "text-blue-500");
                }
    
                icon.addEventListener("click", function (e) {
                    e.stopPropagation();
    
                    if (favorites.includes(venueId)) {
                        favorites = favorites.filter(id => id !== venueId);
                        this.classList.remove("fa-solid", "text-blue-500");
                        this.classList.add("fa-regular", "text-gray-400");
                    } else {
                        favorites.push(venueId);
                        this.classList.remove("fa-regular", "text-gray-400");
                        this.classList.add("fa-solid", "text-blue-500");
                    }
    
                    localStorage.setItem("favorites", JSON.stringify(favorites));
                    syncFavoritesToUrl();
                });
            });
        });
    </script>

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
    
    <style>
    .toggleContent {
        overflow: hidden;
        transition: max-height 0.3s ease;
        max-height: 1000px; /* default terbuka */
    }
    .toggleContent.max-h-0 {
        max-height: 0;
    }
    </style>
    

@endsection

@push('scripts')
<script>
    function calendar(defaultDate = '') {
        const today = new Date();
        let initialDate = defaultDate ? new Date(defaultDate) : today;

        return {
            month: initialDate.getMonth(),
            year: initialDate.getFullYear(),
            selectedDate: defaultDate || '',
            toggle: true,
            monthNames: [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ],
            daysInMonth() {
                return new Date(this.year, this.month + 1, 0).getDate();
            },
            prevMonth() {
                if (this.month === 0) {
                    this.month = 11;
                    this.year--;
                } else {
                    this.month--;
                }
            },
            nextMonth() {
                if (this.month === 11) {
                    this.month = 0;
                    this.year++;
                } else {
                    this.month++;
                }
            },
            formatDate(year, month, day) {
                return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            },
            selectDate(day) {
                this.selectedDate = this.formatDate(this.year, this.month, day);
            }
        }
    }
</script>

@endpush