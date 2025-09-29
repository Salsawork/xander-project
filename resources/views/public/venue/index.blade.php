@extends('app')
@section('title', 'Venues - Xander Billiard')

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1"><span onclick="window.location='{{ route('index') }}'">Home</span> / Venue
            </p>
            <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR FAVORITE VENUE HERE</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-24 py-18">

            <div class="w-full space-y-6 rounded-xl bg-neutral-900 p-4 text-white text-sm">
                <form method="GET" action="{{ route('venues.index') }}" class="space-y-4">

                    <div>
                        <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                            class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>

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
                        <div class="toggleContent flex flex-wrap gap-2">
                            @foreach ($addresses as $loc)
                                <label class="px-3 py-1 rounded-full border cursor-pointer
                                              {{ request('address') == $loc ? 'border-blue-500 text-blue-400' : 'border-gray-500 text-gray-400' }}">
                                    <input type="radio" name="address" value="{{ $loc }}" class="hidden"
                                        @checked(request('address') == $loc) />
                                    {{ $loc }}
                                </label>
                            @endforeach
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

            <section class="lg:col-span-4 flex flex-col gap-8">
                @forelse ($venues as $venue)
                    <div class="relative">
                        <span class="relative" onclick="window.location='{{ route('venues.detail', $venue->id) }}'">
                            <div
                                class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg flex flex-row items-center p-6 relative">
                                <div
                                    class="w-64 h-36 bg-neutral-700 rounded-lg mr-8 flex-shrink-0 flex items-center justify-center">
                                    <span class="text-gray-500 text-2xl">Image</span>
                                </div>
                                <div class="flex-1 flex flex-col justify-between h-full">
                                    <div>
                                        <h3 class="text-2xl font-bold mb-1">{{ $venue->name }} | {{ $venue->id }}</h3>
                                        <div class="text-gray-400 text-sm mb-2">{{ $venue->address ?? 'Jakarta' }}</div>
                                    </div>
                                    <div class="mt-4">
                                        <span class="text-gray-400 text-sm">start from</span>
                                        <span class="text-xl font-bold text-white ml-2">Rp.
                                            {{ number_format($venue->price ?? 50000, 0, ',', '.') }}</span>
                                        <span class="text-gray-400 text-sm">/ session</span>
                                    </div>
                                </div>
                            </div>
                        </span>

                        <div class="absolute top-6 right-6">
                            <i data-id="{{ $venue->id }}"
                                class="fa-regular fa-bookmark text-gray-400 text-2xl cursor-pointer hover:text-blue-500">
                            </i>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-gray-400">No venues found.</p>
                    </div>
                @endforelse

                <div class="mt-6 flex justify-center">
                    {{ $venues->links() }}
                </div>
            </section>
        </div>

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
