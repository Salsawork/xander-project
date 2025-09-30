@extends('app')

@section('title', 'Sparring')

@push('styles')
    <style>
        /* ===== Anti white overscroll (global) ===== */
        :root { color-scheme: dark; }
        html, body {
            height: 100%;
            background-color: #0a0a0a;   /* pastikan root gelap */
            overscroll-behavior-y: none; /* nonaktifkan chain overscroll (Chrome/Android, Edge, Firefox) */
        }
        /* Pastikan wrapper utama dari layout juga gelap */
        #app, main { background-color: #0a0a0a; }

        /* iOS Safari fix: kanvas hitam fixed di balik konten saat rubber-band bounce */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: #0a0a0a;
            pointer-events: none;
            z-index: -1;  /* di belakang semua */
        }

        /* ===== Util ===== */
        .max-h-0 { max-height: 0 !important; }

        @media (min-width: 1024px) {
            .lg-hidden { display: none !important; }
        }
        @media (max-width: 1023px) {
            .sm-hidden { display: none !important; }
        }

        /* ===== Mobile filter overlay + sidebar ===== */
        @media (max-width: 1023px) {
            .mobile-filter-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
                display: none;
            }
            .mobile-filter-overlay.active { display: block; }

            .mobile-filter-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 85%;
                max-width: 340px;
                height: 100%;
                background: rgb(23, 23, 23);
                z-index: 50;
                transition: left 0.3s ease;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 24px;
            }
            .mobile-filter-sidebar.open { left: 0; }
        }

        /* Toggle content anim */
        .toggleContent {
            overflow: hidden;
            transition: max-height 0.3s ease;
            max-height: 1000px; /* default terbuka */
        }
        .toggleContent.max-h-0 { max-height: 0; }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">

        <!-- Desktop Hero -->
        <div class="mb-16 bg-cover bg-center p-24 sm-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">
                <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Sparring
            </p>
            <h2 class="text-4xl font-bold uppercase text-white">POWER. PRECISION. PLAY.</h2>
        </div>

        <!-- Mobile Hero -->
        <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-xs sm:text-sm text-gray-400 mt-1">
                <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Sparring
            </p>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">Power. Precision. Play.</h2>
        </div>

        <!-- Mobile Filter Button -->
        <div class="lg-hidden px-4 sm:px-6 mb-4">
            <button id="mobileFilterBtn"
                class="w-full bg-transparent rounded border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-700/40">
                <i class="fas fa-filter"></i>
                Filter & Search
            </button>
        </div>

        <!-- Mobile Filter Overlay -->
        <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

        <!-- Grid: Filter + List -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 lg:py-12">
            <!-- Filter (mobile = sidebar slide-in, desktop = kolom statis) -->
            <aside id="filterSparring" class="mobile-filter-sidebar lg:relative lg:left-0 lg:col-span-1">
                <div class="px-4 lg:px-0 space-y-6 text-white text-sm lg:sticky lg:top-24">
                    <!-- Header hanya tampil di mobile -->
                    <div class="flex items-center justify-between mb-4 lg-hidden">
                        <h3 class="text-lg font-semibold">Filter & Search</h3>
                        <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
                    </div>

                    <form method="GET" action="{{ route('sparring.index') }}" class="space-y-4 rounded-xl lg:bg-neutral-900 lg:p-4">
                        <!-- Search -->
                        <div>
                            <input type="text" name="search" placeholder="Search"
                                value="{{ request('search') }}"
                                class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        </div>

                        <!-- Date -->
                        <div x-data="calendar('{{ request('date') }}')" class="pt-2 text-white rounded-xl text-sm">
                            <input type="hidden" name="date" x-model="selectedDate">
                            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                                <span>Date</span>
                                <span @click="toggle = !toggle" class="cursor-pointer text-xl leading-none text-gray-300">–</span>
                            </div>
                            <div x-show="toggle" x-transition>
                                <div class="flex items-center justify-center gap-2 mb-2">
                                    <button type="button" @click="prevMonth()" class="text-gray-400 hover:text-white">&lt;</button>
                                    <span x-text="monthNames[month] + ' ' + year"></span>
                                    <button type="button" @click="nextMonth()" class="text-gray-400 hover:text-white">&gt;</button>
                                </div>
                                <div class="grid grid-cols-7 gap-1 text-center text-gray-400 text-xs">
                                    <template x-for="d in daysInMonth()" :key="d">
                                        <span
                                            class="py-1 cursor-pointer rounded transition-colors"
                                            :class="selectedDate === formatDate(year, month, d) ? 'bg-blue-500 text-white' : 'hover:bg-gray-600'"
                                            @click="selectDate(d)"
                                            x-text="d">
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div>
                            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                                <span>Location</span>
                                <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                            </div>
                            <div class="toggleContent flex flex-wrap gap-2">
                                @foreach ($locations as $loc)
                                    <label class="px-3 py-1 rounded-full border cursor-pointer
                                                  {{ request('address') == $loc ? 'border-blue-500 text-blue-400' : 'border-gray-500 text-gray-400' }}">
                                        <input type="radio" name="address" value="{{ $loc }}" class="hidden" @checked(request('address') == $loc) />
                                        {{ $loc }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div>
                            <div class="flex items-center justify-between my-2 font-semibold border-b border-gray-500 pb-1">
                                <span>Price Range</span>
                                <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                            </div>
                            <div class="toggleContent w-full flex items-center gap-2">
                                <input type="text" id="price_min" name="price_min" placeholder="Min"
                                    value="{{ request('price_min') }}"
                                    class="w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
                                <input type="text" id="price_max" name="price_max" placeholder="Max"
                                    value="{{ request('price_max') }}"
                                    class="w-1/2 rounded border border-gray-400 bg-transparent px-2 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-500" />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-2 pt-2">
                            <button type="submit"
                                class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                                Filter
                            </button>
                            <a href="{{ route('sparring.index') }}"
                               class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-center text-blue-500 hover:bg-blue-500 hover:text-white">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </aside>

            <!-- Athletes List -->
            <section class="lg:col-span-4 flex flex-col gap-6">
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6">
                    @forelse ($athletes as $athlete)
                        <a href="{{ route('sparring.detail', $athlete->id) }}" class="block">
                            <div class="rounded-lg lg:rounded-xl bg-neutral-800 p-2 sm:p-3 shadow-md hover:shadow-lg transition-shadow">
                                <div class="aspect-[3/4] overflow-hidden rounded-md bg-neutral-700 mb-2 sm:mb-3">
                                    @if ($athlete->athleteDetail && $athlete->athleteDetail->image)
                                        <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}"
                                             class="h-full w-full object-cover"
                                             alt="{{ $athlete->name }}"
                                             onerror="this.src='{{ asset('images/athlete/athlete-1.png') }}'">
                                    @else
                                        <img src="{{ asset('images/athlete/athlete-1.png') }}"
                                             class="h-full w-full object-cover"
                                             alt="{{ $athlete->name }}">
                                    @endif
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium line-clamp-2">{{ $athlete->name }}</h3>
                                <p class="text-xs sm:text-sm text-gray-400 mt-1">
                                    Rp. {{ number_format($athlete->athleteDetail->price_per_session ?? 0, 0, ',', '.') }} / session
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full text-center py-12 text-gray-400">
                            No athletes available at the moment.
                        </div>
                    @endforelse
                </div>

                <!-- Pagination (jika pakai paginator untuk athletes) -->
                @if(method_exists($athletes, 'links'))
                    <div class="flex justify-center mt-6">
                        {{ $athletes->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </section>
        </div>

        <!-- Floating Shopping Cart Button -->
        <button aria-label="Shopping cart with {{ count($cartProducts ?? []) + count($cartVenues ?? []) + count($cartSparrings ?? []) }} items"
                onclick="showCart()"
                class="fixed right-4 sm:right-6 bottom-6 sm:bottom-10 bg-[#2a2a2a] rounded-full w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow z-50">
            <i class="fas fa-shopping-cart text-white text-2xl sm:text-3xl"></i>
            <span class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                {{ count($cartProducts ?? []) + count($cartVenues ?? []) + count($cartSparrings ?? []) }}
            </span>
        </button>

        @include('public.cart')
    </div>
@endsection

@push('scripts')
    <script>
        // ===== Toggle section (Location, Price, dll) =====
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

        // ===== Mobile filter open/close =====
        document.addEventListener("DOMContentLoaded", () => {
            const mobileFilterBtn = document.getElementById("mobileFilterBtn");
            const filterSparring = document.getElementById("filterSparring");
            const mobileFilterOverlay = document.getElementById("mobileFilterOverlay");
            const closeMobileFilter = document.getElementById("closeMobileFilter");

            function openMobileFilter() {
                filterSparring.classList.add("open");
                mobileFilterOverlay.classList.add("active");
                document.body.style.overflow = "hidden";
            }
            function closeMobileFilterFunc() {
                filterSparring.classList.remove("open");
                mobileFilterOverlay.classList.remove("active");
                document.body.style.overflow = "";
            }

            if (mobileFilterBtn) mobileFilterBtn.addEventListener("click", openMobileFilter);
            if (closeMobileFilter) closeMobileFilter.addEventListener("click", closeMobileFilterFunc);
            if (mobileFilterOverlay) mobileFilterOverlay.addEventListener("click", closeMobileFilterFunc);
        });

        // ===== Price input: format ribuan saat ketik, unformat saat submit =====
        function formatNumberInput(input) {
            let value = input.value.replace(/\D/g, ""); // hapus non digit
            if (!value) { input.value = ""; return; }
            input.value = new Intl.NumberFormat("id-ID").format(value);
        }
        function unformatNumberInput(value) {
            return (value || "").toString().replace(/\./g, "");
        }
        document.addEventListener("DOMContentLoaded", () => {
            const minInput = document.getElementById("price_min");
            const maxInput = document.getElementById("price_max");
            if (minInput && minInput.value) minInput.value = new Intl.NumberFormat("id-ID").format(minInput.value);
            if (maxInput && maxInput.value) maxInput.value = new Intl.NumberFormat("id-ID").format(maxInput.value);

            if (minInput) minInput.addEventListener("input", () => formatNumberInput(minInput));
            if (maxInput) maxInput.addEventListener("input", () => formatNumberInput(maxInput));

            // sebelum submit, balikin ke angka biasa (biar server bisa baca)
            const form = document.querySelector('#filterSparring form');
            if (form) {
                form.addEventListener("submit", () => {
                    if (minInput) minInput.value = unformatNumberInput(minInput.value);
                    if (maxInput) maxInput.value = unformatNumberInput(maxInput.value);
                });
            }
        });

        // ===== Mini Alpine Calendar =====
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
                daysInMonth() { return new Date(this.year, this.month + 1, 0).getDate(); },
                prevMonth() {
                    if (this.month === 0) { this.month = 11; this.year--; }
                    else { this.month--; }
                },
                nextMonth() {
                    if (this.month === 11) { this.month = 0; this.year++; }
                    else { this.month++; }
                },
                formatDate(year, month, day) {
                    return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                },
                selectDate(day) { this.selectedDate = this.formatDate(this.year, this.month, day); }
            }
        }
    </script>
@endpush
