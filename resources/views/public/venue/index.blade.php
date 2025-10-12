@extends('app')
@section('title', 'Venues - Xander Billiard')
@php
    $cartCount     = count($cartProducts) + count($cartVenues) + count($cartSparrings);
@endphp

@push('styles')
<style>
    :root { color-scheme: dark; }
    html, body { height: 100%; background: #0a0a0a; overscroll-behavior-y: none; }
    body::before { content: ""; position: fixed; inset: 0; background: #0a0a0a; pointer-events: none; z-index: -1; }

    .toggleContent { overflow: hidden; transition: max-height .3s ease; max-height: 1000px; }
    .toggleContent.max-h-0 { max-height: 0; }

    @media (min-width: 1024px) { .lg-hidden { display: none !important; } }

    @media (max-width: 1023px) {
        .sm-hidden { display: none !important; }
        .mobile-filter-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 40; display: none; }
        .mobile-filter-overlay.active { display: block; }
        .mobile-filter-sidebar { position: fixed; top: 0; left: -100%; width: 85%; max-width: 320px; height: 100%; background: #171717; z-index: 50; transition: left .3s ease; overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .mobile-filter-sidebar.open { left: 0; }
    }

    .pager { display:inline-flex; align-items:center; gap:10px; background:#1f2937; border:1px solid rgba(255,255,255,.06); border-radius:9999px; padding:6px 10px; box-shadow: 0 8px 20px rgba(0,0,0,.35) inset, 0 4px 14px rgba(0,0,0,.25); }
    .pager-label { min-width:90px; text-align:center; color:#e5e7eb; font-weight:600; letter-spacing:.2px; }
    .pager-btn { width:44px; height:44px; display:grid; place-items:center; border-radius:9999px; line-height:0; text-decoration:none; border:1px solid rgba(255,255,255,.15); box-shadow:0 2px 6px rgba(0,0,0,.35); transition: transform .15s ease, opacity .15s ease; }
    .pager-btn:hover { transform: translateY(-1px); }
    .pager-prev { background:#e5e7eb; color:#0f172a; }
    .pager-next { background:#2563eb; color:#fff; }
    .pager-btn[aria-disabled="true"] { opacity:.45; pointer-events:none; filter:grayscale(20%); }

    @media (max-width:640px){
        .pager { padding:4px 8px; gap:8px; }
        .pager-btn { width:40px; height:40px; }
        .pager-label { min-width:80px; font-size:.9rem; }
    }
</style>
@endpush

@section('content')
<div
    class="min-h-screen bg-neutral-900 text-white"
    style="
        background-image: url('{{ asset('images/bg/background_2.png') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    "
>
    <div class="mb-16 bg-cover bg-center p-24 sm-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
        <p class="text-sm text-gray-400 mt-1"><span onclick="window.location='{{ route('index') }}'">Home</span> / Venue</p>
        <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR FAVORITE VENUE HERE</h2>
    </div>

    <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden" style="background-image: url('/images/bg/product_breadcrumb.png');">
        <p class="text-xs sm:text-sm text-gray-400 mt-1">
            <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Venue
        </p>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">FIND YOUR FAVORITE VENUE HERE</h2>
    </div>

    <div class="lg-hidden px-4 sm:px-6 mb-4">
        <button id="mobileFilterBtn" class="w-full bg-transparent rounded border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-400">
            <i class="fas fa-filter"></i>
            Filter & Search
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 sm:py-8 lg:py-12" id="list">
        <aside id="filterVenue" class="mobile-filter-sidebar">
            <div class="px-4 space-y-6 text-white text-sm">
                <div class="flex items-center justify-between mb-4 lg-hidden">
                    <h3 class="text-lg font-semibold">Filter & Search</h3>
                    <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
                </div>

                <form method="GET" action="{{ route('venues.index') }}">
                    <input type="hidden" name="pp" value="{{ request('pp', 4) }}">

                    <div>
                        <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                            class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>

                    <div x-data="calendar('{{ request('date') }}')" class="pt-4 text-white rounded-xl text-sm">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Date</span>
                            <span class="toggleBtn cursor-pointer text-xl">–</span>
                        </div>

                        <div class="toggleContent">
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
                                        :class="selectedDate === formatDate(year, month, d) ? 'bg-blue-500 text-white' : 'hover:bg-gray-600'"
                                        @click="selectDate(d)"
                                        x-text="d">
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Location</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent flex flex-wrap gap-2 mb-4">
                            @foreach ($addresses as $loc)
                                <label class="px-3 py-1 rounded-full border cursor-pointer {{ request('address') == $loc ? 'border-blue-500 text-blue-400' : 'border-gray-500 text-gray-400' }}">
                                    <input type="radio" name="address" value="{{ $loc }}" class="hidden" @checked(request('address')==$loc) />
                                    {{ $loc }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Price Range</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent w-full flex items-center gap-2">
                            <input type="text" id="price_min" name="price_min" placeholder="Min" value="{{ request('price_min') }}"
                                   class="w-1/2 rounded border border-gray-400 px-2 py-1 focus:outline-none focus:ring focus:ring-blue-500" />
                            <input type="text" id="price_max" name="price_max" placeholder="Max" value="{{ request('price_max') }}"
                                   class="w-1/2 rounded border border-gray-400 px-2 py-1 focus:outline-none focus:ring focus:ring-blue-500" />
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="submit" class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">Filter</button>
                        <a href="{{ route('venues.index', array_merge(request()->except('page'), ['pp' => 4])) }}" class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">Reset</a>
                    </div>
                </form>
            </div>
        </aside>
        <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

        <section class="lg:col-span-4 flex flex-col gap-6">
            @forelse ($venues as $venue)
                <div class="group">
                    {{-- GUNAKAN HREF DENGAN SLUG --}}
                    <a href="{{ route('venues.detail', ['venue' => $venue->id, 'slug' => $venue->name]) }}"
                       class="block bg-neutral-800 rounded-xl overflow-hidden shadow-lg flex flex-col sm:flex-row items-start sm:items-center p-4 sm:p-6 cursor-pointer transition hover:bg-neutral-700">

                        <!-- Image -->
                        <div class="w-full sm:w-64 h-40 sm:h-36 bg-neutral-700 rounded-lg mb-4 sm:mb-0 sm:mr-6 flex-shrink-0 flex items-center justify-center">
                            <span class="text-gray-500 text-lg sm:text-2xl">Image</span>
                        </div>

                        <!-- Details -->
                        <div class="w-full flex flex-col justify-between px-4">
                            <div class="flex justify-between items-start lg:mb-8">
                                <h3 class="text-lg sm:text-2xl font-bold">{{ $venue->name }}</h3>
                                <div class="flex justify-center items-end sm:mt-2">
                                    @auth
                                      @if (auth()->user()->roles === 'user')
                                        <i
                                          data-id="{{ $venue->id }}"
                                          class="{{ auth()->user()->favorites->contains('venue_id', $venue->id)
                                              ? 'fa-solid text-blue-500'
                                              : 'fa-regular text-gray-400' }}
                                              fa-bookmark text-xl sm:text-2xl cursor-pointer hover:text-blue-500 transition">
                                        </i>
                                      @endif
                                    @endauth
                                  </div>
                                  
                            </div>
                            <p class="text-gray-400 text-sm mb-2">{{ $venue->address ?? 'Jakarta' }}</p>
                            <div class="mt-12">
                                <div class="flex items-baseline gap-1 text-sm">
                                    <span class="text-gray-400">start from</span>
                                    <span class="text-lg sm:text-xl font-bold">Rp. {{ number_format($venue->price ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-gray-400">/ session</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="text-center py-12 text-gray-400">No venues found.</div>
            @endforelse

            @php
                $current = method_exists($venues, 'currentPage') ? $venues->currentPage() : 1;
                $last    = method_exists($venues, 'lastPage')    ? $venues->lastPage()    : 1;
                $prevUrl = $current > 1 ? $venues->appends(array_merge(request()->query(), ['pp' => request('pp', 4)]))->url($current - 1) : null;
                $nextUrl = $current < $last ? $venues->appends(array_merge(request()->query(), ['pp' => request('pp', 4)]))->url($current + 1) : null;
            @endphp
            <div class="flex justify-center mt-6">
                <nav class="pager" role="navigation" aria-label="Pagination">
                    @if ($prevUrl)
                        <a class="pager-btn pager-prev" href="{{ $prevUrl }}#list" aria-label="Previous page">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @else
                        <span class="pager-btn pager-prev" aria-disabled="true" aria-label="Previous page">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    @endif

                    <span class="pager-label">{{ $current }} of {{ $last }}</span>

                    @if ($nextUrl)
                        <a class="pager-btn pager-next" href="{{ $nextUrl }}#list" aria-label="Next page">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    @else
                        <span class="pager-btn pager-next" aria-disabled="true" aria-label="Next page">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        </section>
    </div>

    @include('public.cart')
</div>
@if (Auth::check() && Auth::user()->roles === 'user')
<button
    aria-label="Shopping cart with {{ $cartCount }} items"
    onclick="showCart()"
    class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg z-[60]"
>
    <i class="fas fa-shopping-cart text-white text-3xl"></i>
    @if ($cartCount > 0)
        <span class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
            {{ $cartCount }}
        </span>
    @endif
</button>
@endif

<script>
    (function ensurePP(){
        const url = new URL(window.location.href);
        const pp  = url.searchParams.get('pp');
        if (pp !== '4') {
            url.searchParams.set('pp','4');
            url.searchParams.set('page','1');
            url.hash = '#list';
            window.location.replace(url.toString());
        }
    })();
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const toggles = document.querySelectorAll(".toggleBtn");
        toggles.forEach((btn) => {
            const content = btn.parentElement.nextElementSibling;
            btn.addEventListener("click", () => {
                if (content.classList.contains("max-h-0")) { content.classList.remove("max-h-0"); btn.textContent = "–"; }
                else { content.classList.add("max-h-0"); btn.textContent = "+"; }
            });
        });

        const mobileFilterBtn = document.getElementById("mobileFilterBtn");
        const filterVenue = document.getElementById("filterVenue");
        const mobileFilterOverlay = document.getElementById("mobileFilterOverlay");
        const closeMobileFilter = document.getElementById("closeMobileFilter");

        function openMobileFilter() {
            filterVenue.classList.add("open");
            mobileFilterOverlay.classList.add("active");
            document.body.style.overflow = "hidden";
        }
        function closeMobileFilterFunc() {
            filterVenue.classList.remove("open");
            mobileFilterOverlay.classList.remove("active");
            document.body.style.overflow = "";
        }

        mobileFilterBtn?.addEventListener("click", openMobileFilter);
        closeMobileFilter?.addEventListener("click", closeMobileFilterFunc);
        mobileFilterOverlay?.addEventListener("click", closeMobileFilterFunc);
    });
</script>

<script>
    function formatNumberInput(input) {
        let value = input.value.replace(/\D/g, "");
        if (!value) { input.value = ""; return; }
        input.value = new Intl.NumberFormat("id-ID").format(value);
    }
    function unformatNumberInput(input) { return input.value.replace(/\./g, ""); }

    document.addEventListener("DOMContentLoaded", () => {
        const minInput = document.getElementById("price_min");
        const maxInput = document.getElementById("price_max");

        if (minInput && minInput.value) minInput.value = new Intl.NumberFormat("id-ID").format(minInput.value);
        if (maxInput && maxInput.value) maxInput.value = new Intl.NumberFormat("id-ID").format(maxInput.value);

        minInput?.addEventListener("input", () => formatNumberInput(minInput));
        maxInput?.addEventListener("input", () => formatNumberInput(maxInput));

        if (minInput && minInput.form) {
            minInput.form.addEventListener("submit", () => {
                minInput.value = unformatNumberInput(minInput);
                if (maxInput) maxInput.value = unformatNumberInput(maxInput);
            });
        }
    });
</script>

<style>
    .toggleContent { overflow: hidden; transition: max-height .3s ease; max-height: 1000px; }
    .toggleContent.max-h-0 { max-height: 0; }
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
            monthNames: ["January","February","March","April","May","June","July","August","September","October","November","December"],
            daysInMonth() { return new Date(this.year, this.month + 1, 0).getDate(); },
            prevMonth() { this.month === 0 ? (this.month = 11, this.year--) : this.month--; },
            nextMonth() { this.month === 11 ? (this.month = 0, this.year++) : this.month++; },
            formatDate(year, month, day) { return `${year}-${String(month + 1).padStart(2,'0')}-${String(day).padStart(2,'0')}`; },
            selectDate(day) { this.selectedDate = this.formatDate(this.year, this.month, day); }
        }
    }
</script>
@endpush