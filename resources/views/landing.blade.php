@extends('app')
@section('title', ' Xander Billiard')

@push('styles')
    <style>
        :root { color-scheme: dark; }

        html, body {
            height: 100%;
            background: #0a0a0a;
            overscroll-behavior-y: none;
        }
        body, main, #app { background: #0a0a0a; }

        /* ===============================
           Tinggi section berbasis viewport
        ================================*/
        .vh-section {
            min-height: calc(var(--svh, 1vh) * 100 - var(--header-h, 0px));
        }

        /* Khusus hero: saat mobile, jangan terlalu tinggi */
        @media (max-width: 640px) {
            .vh-section--hero {
                min-height: calc(var(--svh, 1vh) * 68 - var(--header-h, 0px));
            }
            .hero--tight .hero-title {
                font-size: clamp(1.75rem, 7.2vw, 2.5rem);
                line-height: 1.15;
            }
            .hero--tight .hero-lead {
                font-size: clamp(0.95rem, 3.8vw, 1.05rem);
            }
            .hero--tight .hero-btn {
                padding: 0.6rem 1rem;
                font-size: 0.95rem;
            }
            .hero--tight .hero-copy-wrap { max-width: 90%; }
            .hero--tight .hero-bg { object-position: center !important; }
        }

        /* ===== Slider Top Picks (mobile) ===== */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Hapus fade shadow di kiri/kanan */
        .tp-fade-left::before,
        .tp-fade-right::before { content: none !important; }

        .tp-nav {
            position: absolute;
            bottom: 50%;
            transform: translateY(50%);
            z-index: 10;
            width: 40px; height: 40px;
            display: grid; place-items: center;
            border-radius: 9999px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.25);
            backdrop-filter: blur(2px);
        }
        .tp-nav[disabled] { opacity: .35; cursor: not-allowed; }
        .tp-prev { left: .5rem; }
        .tp-next { right: .5rem; }

        .tp-card { width: 75vw; max-width: 360px; }
        @media (min-width: 420px) and (max-width: 767px) {
            .tp-card { width: 68vw; }
        }

        #topPicksScroller {
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            overscroll-behavior-x: contain;
            touch-action: pan-x;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Set variabel --header-h supaya section bisa pas 1 layar - tinggi header
        function setHeaderHeightVar() {
            const header =
                document.querySelector('#site-header') ||
                document.querySelector('[data-header]') ||
                document.querySelector('header');
            const h = header ? header.offsetHeight : 0;
            document.documentElement.style.setProperty('--header-h', h + 'px');
        }
        // Set variabel --svh (akurasi tinggi viewport di mobile)
        function setViewportVars() {
            const svh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--svh', svh + 'px');
        }

        // ====== Top Picks slider (mobile) ======
        function initTopPicksSlider() {
            const scroller = document.getElementById('topPicksScroller');
            const prevBtn  = document.getElementById('tpPrev');
            const nextBtn  = document.getElementById('tpNext');
            if (!scroller || !prevBtn || !nextBtn) return;

            function getStep() {
                const card = scroller.querySelector('.tp-card');
                if (!card) return 0;
                const cardRect = card.getBoundingClientRect();
                const gap = parseFloat(getComputedStyle(scroller).gap || 0);
                return cardRect.width + gap;
            }
            function updateNav() {
                const maxScroll = scroller.scrollWidth - scroller.clientWidth - 2;
                prevBtn.disabled = scroller.scrollLeft <= 2;
                nextBtn.disabled = scroller.scrollLeft >= maxScroll;
            }
            function scrollByStep(dir) {
                const step = getStep() || (scroller.clientWidth * 0.8);
                scroller.scrollBy({ left: dir * step, behavior: 'smooth' });
            }
            prevBtn.addEventListener('click', () => scrollByStep(-1), { passive: true });
            nextBtn.addEventListener('click', () => scrollByStep(1), { passive: true });
            scroller.addEventListener('scroll', updateNav, { passive: true });
            window.addEventListener('resize', updateNav);
            updateNav();
        }

        window.addEventListener('load', () => {
            setViewportVars();
            setHeaderHeightVar();
            initTopPicksSlider();
        });
        window.addEventListener('resize', () => {
            setViewportVars();
            setHeaderHeightVar();
        });
        window.addEventListener('orientationchange', () => {
            setViewportVars();
            setHeaderHeightVar();
        });
    </script>
@endpush

@section('content')
    <div class="bg-neutral-900 text-white">

        <!-- Jumbotron -->
        <section class="relative isolate w-full overflow-hidden bg-neutral-900 vh-section vh-section--hero hero--tight">
            <!-- BG image (tanpa overlay/shadow) -->
            <img src="{{ asset('/images/jumbotron1.png') }}" alt="Billiard promo"
                 class="hero-bg absolute inset-0 h-full w-full object-cover object-[78%_center]" />

            <!-- KONTEN -->
            <div class="absolute inset-0 z-10 flex items-center">
                <div class="mx-auto w-full max-w-7xl px-6 md:px-20">
                    <div class="hero-copy-wrap max-w-xl md:max-w-2xl text-white">
                        <p class="font-semibold mb-3 text-white/90 text-sm md:text-base lg:text-lg">
                            Limited Time Offer
                        </p>
                        <h1 class="hero-title text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] mb-5">
                            20% Off on All Billiard
                            <br class="hidden sm:block" />
                            Accessories!
                        </h1>
                        <p class="hero-lead text-base md:text-lg text-white/85 leading-relaxed mb-8 max-w-prose">
                            Save 20% on all our premium billiard accessories. Don’t miss out—shop now
                            and grab this exclusive offer while it lasts!
                        </p>
                        <a href="{{ route('products.landing') }}"
                           class="hero-btn inline-flex items-center rounded-md bg-blue-500 px-5 py-2.5 text-sm md:text-base font-medium
                           text-white shadow hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-white/30">
                           Buy Now
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Top Picks -->
        <section class="relative bg-cover bg-center bg-no-repeat vh-section px-6 md:px-20 py-12 md:py-16 bg-neutral-900"
                 style="background-image: url('/images/bg/background_1.png')">
            <div class="relative z-10 text-white h-full flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl md:text-3xl font-bold">Top Picks</h2>
                </div>

                <!-- Filter Buttons -->
                <div class="flex flex-wrap gap-3 mb-8">
                    <a href="{{ route('level', ['level' => 'professional']) }}">
                        <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">
                            Professional Grade
                        </button>
                    </a>
                    <a href="{{ route('level', ['level' => 'beginner']) }}">
                        <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">
                            Beginner-Friendly
                        </button>
                    </a>
                    <a href="{{ route('level', ['level' => 'under50']) }}">
                        <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">
                            Under $50
                        </button>
                    </a>
                    <a href="{{ route('level', ['level' => 'cue-cases']) }}">
                        <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">
                            Cue Cases
                        </button>
                    </a>
                </div>

                @php $items = $products; @endphp

                <!-- ===== MOBILE: Horizontal Slider (tanpa fade/shadow) ===== -->
                <div class="relative md:hidden -mx-6 px-6 tp-fade-left tp-fade-right">
                    <button id="tpPrev" class="tp-nav tp-prev" aria-label="Previous">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button id="tpNext" class="tp-nav tp-next" aria-label="Next">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div id="topPicksScroller"
                         class="flex overflow-x-auto no-scrollbar gap-4 snap-x snap-mandatory pb-2">
                        @forelse ($items as $product)
                            @php
                                $images = $product->images
                                    ? (is_array($product->images) ? $product->images : json_decode($product->images, true))
                                    : [];
                                $firstImage = !empty($images) ? $images[0] : null;
                                $idx = ($loop->index % 5) + 1;
                                $defaultImg = asset("images/products/{$idx}.png");
                                if ($firstImage) {
                                    $clean = str_replace('http://127.0.0.1:8000', '', $firstImage);
                                    $src = preg_match('/^https?:\\/\\//i', $clean) ? $clean : asset(ltrim($clean, '/'));
                                } else {
                                    $src = $defaultImg;
                                }
                            @endphp

                            <article class="group tp-card snap-start shrink-0">
                                <a href="{{ route('products.detail', $product->id) }}" class="block">
                                    <div class="relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-neutral-800">
                                        <img src="{{ $src }}" alt="{{ $product->name }}"
                                             class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                             onerror="this.onerror=null;this.src='{{ $defaultImg }}'">
                                    </div>
                                </a>
                                <div class="mt-3">
                                    <a href="{{ route('products.detail', $product->id) }}" class="hover:text-blue-400">
                                        <h3 class="font-bold tracking-tight line-clamp-2">{{ $product->name }}</h3>
                                    </a>
                                    <p class="text-sm text-white/80 mt-1">
                                        @if (!empty($product->discount) && $product->discount > 0)
                                            <span class="line-through text-gray-400">
                                                Rp {{ number_format($product->pricing, 0, ',', '.') }}
                                            </span>
                                            @php $final = $product->pricing - $product->pricing * $product->discount; @endphp
                                            Rp {{ number_format($final, 0, ',', '.') }}
                                        @else
                                            Rp {{ number_format($product->pricing, 0, ',', '.') }}
                                        @endif
                                    </p>
                                </div>
                            </article>
                        @empty
                            <div class="snap-start shrink-0 w-[80vw] text-center py-8">
                                <p>Tidak ada produk yang tersedia saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- ===== DESKTOP/TABLET: Grid normal ===== -->
                <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-8">
                    @forelse ($items as $product)
                        @php
                            $images = $product->images
                                ? (is_array($product->images) ? $product->images : json_decode($product->images, true))
                                : [];
                            $firstImage = !empty($images) ? $images[0] : null;
                            $idx = ($loop->index % 5) + 1;
                            $defaultImg = asset("images/products/{$idx}.png");
                            if ($firstImage) {
                                $clean = str_replace('http://127.0.0.1:8000', '', $firstImage);
                                $src = preg_match('/^https?:\\/\\//i', $clean) ? $clean : asset(ltrim($clean, '/'));
                            } else {
                                $src = $defaultImg;
                            }
                        @endphp

                        <article class="group">
                            <a href="{{ route('products.detail', $product->id) }}" class="block">
                                <div class="relative aspect-[3/4] w-full overflow-hidden rounded-xl bg-neutral-800">
                                    <img src="{{ $src }}" alt="{{ $product->name }}"
                                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                         onerror="this.onerror=null;this.src='{{ $defaultImg }}'">
                                </div>
                            </a>
                            <div class="mt-3">
                                <a href="{{ route('products.detail', $product->id) }}" class="hover:text-blue-400">
                                    <h3 class="font-bold tracking-tight">{{ $product->name }}</h3>
                                </a>
                                <p class="text-sm text-white/80 mt-1">
                                    @if (!empty($product->discount) && $product->discount > 0)
                                        <span class="line-through text-gray-400">
                                            Rp {{ number_format($product->pricing, 0, ',', '.') }}
                                        </span>
                                        @php $final = $product->pricing - $product->pricing * $product->discount; @endphp
                                        Rp {{ number_format($final, 0, ',', '.') }}
                                    @else
                                        Rp {{ number_format($product->pricing, 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-4 text-center py-8">
                            <p>Tidak ada produk yang tersedia saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Jumbotron 2 - Guidelines (tanpa overlay/shadow) -->
        <section class="relative bg-cover bg-center min-h-[70vh] md:h-screen flex items-center"
                 style="background-image: url('/images/jumbotron2.png')">
            <div class="relative max-w-xl px-6 md:px-20 text-white z-10 py-16">
                <p class="font-semibold mb-2 text-sm md:text-base">Billiard Guidelines</p>
                <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-4">
                    Billiard Playing Guide for Beginners
                </h1>
                <p class="text-sm md:text-base mb-6">
                    Learn basic techniques, game rules, and etiquette at the billiard table. Improve
                    your skills and enjoy the game with more confidence.
                </p>
                <a href="{{ route('guideline.index') }}"
                   class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded">
                   Learn More
                </a>
            </div>
        </section>

        <!-- Services -->
        <section class="relative bg-cover bg-center py-20 px-6 md:px-20"
                 style="background-image: url('/images/bg/background_2.png')">
            <div class="relative z-10 text-white w-full">
                <h2 class="text-2xl md:text-3xl font-bold text-center mb-12">Our Services</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    @php
                        $services = [
                            'Tip Installation',
                            'Tip Reshaping',
                            'Shaft Cleaning',
                            'Grip Replacement',
                            'Balancing & Refinishing',
                            'Ferrule Replacement',
                        ];
                    @endphp
                    @foreach ($services as $service)
                        <div class="border border-white/20 rounded-xl p-6 flex flex-col gap-6 hover:border-white transition">
                            <div class="text-3xl mb-10 text-[#F8F8F8]"><x-icon.setting class="h-10 w-10" /></div>
                            <div class="flex justify-between items-center">
                                <p class="text-sm text-[#F8F8F8]">{{ $service }} <span class="ml-3">></span></p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Join Community -->
        <section class="flex flex-col md:flex-row min-h-[80vh]">
            <div class="w-full md:w-1/2 h-64 md:h-auto">
                <img src="{{ asset('images/jumbotron3.png') }}" alt="Billiard Balls" class="w-full h-full object-cover" />
            </div>
            <div class="w-full md:w-1/2 bg-[#1c1c1c] text-white flex items-center justify-center px-6 md:px-16 py-10 md:py-0">
                <div class="max-w-md">
                    <h2 class="text-2xl md:text-4xl font-bold mb-4">Join the Billiard Community Today!</h2>
                    <p class="text-sm md:text-base text-gray-300 mb-6 leading-relaxed">
                        Be part of a vibrant and passionate community that shares your love for billiards. Connect with
                        players of all skill levels, participate in events, share tips and tricks, and grow your game
                        together.
                    </p>
                    <a href="{{ route('community.index') }}"
                       class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded">
                       Connect Now
                    </a>
                </div>
            </div>
        </section>

        <!-- Latest News & Events -->
        <section class="relative bg-cover bg-center bg-no-repeat text-white vh-section px-6 md:px-16 py-12 md:py-16 bg-neutral-900"
                 style="background-image: url('/images/bg/background_3.png')">
            <div class="mx-auto max-w-7xl relative z-10 h-full flex flex-col">
                <h2 class="text-2xl md:text-3xl font-bold mb-8">Latest News &amp; Events</h2>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- MAIN EVENT (tanpa overlay) -->
                    <article class="relative lg:col-span-2 rounded-xl overflow-hidden h-[420px] md:h-[520px] bg-neutral-800">
                        <img src="{{ asset('images/latest-event/1.png') }}" alt="Master the Game Workshop"
                             class="absolute inset-0 w-full h-full object-cover" />
                        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                            <h3 class="text-xl md:text-2xl font-semibold">
                                Master the Game: A Comprehensive Billiard Workshop with Experts
                            </h3>
                            <p class="mt-2 text-sm md:text-base text-gray-200 max-w-3xl">
                                Take your billiard skills to the next level with our in-depth workshop led by
                                professional players and coaches. Learn advanced cue control, strategic shot
                                selection, and table management from experts.
                            </p>
                        </div>
                    </article>

                    <!-- SUB EVENTS -->
                    <aside class="flex flex-col gap-6">
                        @php
                            $events = [
                                [
                                    'img' => 'images/latest-event/2.png',
                                    'title' => 'The Road to Glory: National Billiard Championship 2025 Kicks Off This Summer',
                                    'desc'  => 'Experience the pinnacle of billiard excellence as top players from across the country compete for the prestigious championship title.',
                                ],
                                [
                                    'img' => 'images/latest-event/3.png',
                                    'title' => 'Building Connections Through Billiards: Community Cue Night for All Skill Levels',
                                    'desc'  => 'Join us for an evening of camaraderie, casual matches, and friendly competition. Friendly for beginners—challenging for regulars. All are welcome!',
                                ],
                                [
                                    'img' => 'images/latest-event/4.png',
                                    'title' => 'Regional Doubles Tournament Offers Thrilling Prizes',
                                    'desc'  => 'Test your partnership and precision in this high-energy doubles tournament. Face teams from around the region for exciting rewards and recognition.',
                                ],
                            ];
                        @endphp

                        @foreach ($events as $e)
                            <article class="flex gap-4 items-start p-3 rounded-xl bg-neutral-800/50 hover:bg-neutral-800 transition min-h-[108px]">
                                <img src="{{ asset($e['img']) }}" alt="{{ $e['title'] }}"
                                     class="w-32 md:w-36 h-40 object-cover rounded-md bg-neutral-700 flex-shrink-0" />
                                <div class="flex flex-col justify-center">
                                    <h4 class="text-sm md:text-base font-semibold leading-snug">{{ $e['title'] }}</h4>
                                    <p class="text-xs md:text-sm text-gray-300 mt-1">{{ $e['desc'] }}</p>
                                </div>
                            </article>
                        @endforeach
                    </aside>
                </div>
            </div>
        </section>
    </div>
@endsection
