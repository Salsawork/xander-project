@extends('app')
@section('title', ' Xander Billiard')

@push('styles')
    <style>
        :root {
            color-scheme: dark;
        }

        html,
        body {
            height: 100%;
            background: #0a0a0a;
            overscroll-behavior-y: none;
        }

        body,
        main,
        #app {
            background: #0a0a0a;
        }

        /* Section = tinggi 1 layar dikurangi tinggi header */
        .vh-section {
            min-height: calc(100vh - var(--header-h, 0px));
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Hitung tinggi header supaya section pas 1 layar
        function setHeaderHeightVar() {
            const header =
                document.querySelector('#site-header') || // kalau ada id
                document.querySelector('[data-header]') || // atau pakai attribute
                document.querySelector('header'); // fallback
            const h = header ? header.offsetHeight : 0;
            document.documentElement.style.setProperty('--header-h', h + 'px');
        }
        window.addEventListener('load', setHeaderHeightVar);
        window.addEventListener('resize', setHeaderHeightVar);
    </script>
@endpush

@section('content')
    <div class="bg-neutral-900 text-white">

        <!-- Jumbotron -->
        <section class="relative isolate w-full overflow-hidden bg-neutral-900 vh-section">
            <!-- BG image -->
            <img src="{{ asset('/images/jumbotron1.png') }}" alt="Billiard promo"
                class="absolute inset-0 h-full w-full object-cover object-[78%_center]" />

            <!-- OVERLAYS: kiri gelap + gradasi → kanan transparan -->
            <div class="absolute inset-0 pointer-events-none z-[1]">
                <!-- 1) Linear gradient (kiri gelap → kanan transparan) -->
                <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent"></div>

                <!-- 2) Vignette lembut di kiri (gelap + blur) -->
                <div class="absolute left-0 top-1/2 -translate-y-1/2
                h-[130%] w-[58%] bg-black/35 blur-2xl">
                </div>

                <!-- 3) Sedikit bayangan diagonal (opsional; hapus kalau tak perlu) -->
                <div class="absolute inset-0 bg-gradient-to-tr from-black/20 via-transparent to-transparent"></div>
            </div>

            <!-- KONTEN (tetap di tengah layar) -->
            <div class="absolute inset-0 z-10 flex items-center">
                <div class="mx-auto w-full max-w-7xl px-6 md:px-20">
                    <div class="max-w-xl md:max-w-2xl text-white">
                        <p class="font-semibold mb-3 text-white/90 text-sm md:text-base lg:text-lg">
                            Limited Time Offer
                        </p>

                        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] mb-5">
                            20% Off on All Billiard
                            <br class="hidden sm:block" />
                            Accessories!
                        </h1>

                        <p class="text-base md:text-lg text-white/85 leading-relaxed mb-8 max-w-prose">
                            Save 20% on all our premium billiard accessories. Don’t miss out—shop now
                            and grab this exclusive offer while it lasts!
                        </p>

                        <!-- BUY NOW -> abu gelap -->
                        <a href="{{ route('products.landing') }}"
                            class="inline-flex items-center rounded-md bg-[#2D2D2D] px-5 py-2.5 text-sm md:text-base font-medium
                  text-white shadow hover:bg-[#3A3A3A] focus:outline-none focus:ring-2 focus:ring-white/30">
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

                <!-- Filter -->
                <div class="flex flex-wrap gap-3 mb-8">
                    <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Professional
                        Grade</button>
                    <button
                        class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Beginner-Friendly</button>
                    <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Under $50</button>
                    <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Cue Cases</button>
                </div>

                @php
                    // Ambil tepat 5 produk pertama (support array atau Collection)
                    $items =
                        $products instanceof \Illuminate\Support\Collection
                            ? $products->take(5)
                            : collect($products)->take(5);
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                    @forelse ($items as $product)
                        <article class="group">
                            @php
                                $images = $product->images
                                    ? (is_array($product->images)
                                        ? $product->images
                                        : json_decode($product->images, true))
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

                                {{-- Harga: Rp xx.xxx.xxx --}}
                                <p class="text-sm text-white/80 mt-1">
                                    @if (!empty($product->discount) && $product->discount > 0)
                                        <span class="line-through text-gray-400">
                                            Rp {{ number_format($product->pricing, 0, ',', '.') }}
                                        </span>
                                        @php
                                            $final = $product->pricing - $product->pricing * $product->discount;
                                        @endphp
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

        <!-- Jumbotron 2 - Guidelines (kiri ada shadow hitam + teks center-left) -->
        <section class="relative isolate vh-section w-full overflow-hidden bg-neutral-900">
            <!-- Background image -->
            <img src="{{ asset('/images/jumbotron2.png') }}" alt="Guidelines"
                class="absolute inset-0 h-full w-full object-cover object-[78%_center]" />

            <!-- OVERLAYS: gelap di kiri -->
            <div class="absolute inset-0 pointer-events-none z-[1]">
                <div class="absolute inset-0 bg-gradient-to-r from-black/95 via-black/70 to-transparent"></div>
                <div class="absolute inset-0"
                    style="background: radial-gradient(120% 80% at 0% 50%, rgba(0,0,0,.55) 0%, rgba(0,0,0,.40) 40%, rgba(0,0,0,0) 70%);">
                </div>
            </div>

            <!-- Content: vertikal TENGAH, tetap rata kiri -->
            <div class="absolute inset-0 z-10 flex items-center">
                <div class="mx-auto w-full max-w-7xl px-6 md:px-20">
                    <div class="max-w-xl md:max-w-2xl text-white">
                        <p class="font-semibold mb-3 text-white/90 text-sm md:text-base">Billiard Guidelines</p>

                        <h2 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] mb-5">
                            Shoot Like a Pro
                        </h2>

                        <p class="text-base md:text-lg text-white/85 leading-relaxed mb-8 max-w-prose">
                            Learn tips, tricks, and strategies from the pros and discover essential equipment
                            care techniques.
                        </p>

                        <!-- LEARN NOW -> abu gelap -->
                        <a href="#"
                            class="inline-flex items-center rounded-md bg-[#2D2D2D] px-5 py-2.5 text-sm md:text-base font-medium
                  text-white shadow hover:bg-[#3A3A3A] focus:outline-none focus:ring-2 focus:ring-white/30">
                            Learn Now
                        </a>
                    </div>
                </div>
            </div>
        </section>



        <!-- Join Community -->
        <section class="vh-section flex flex-col md:flex-row bg-neutral-900">
            <div class="w-full md:w-1/2 h-1/2 md:h-auto">
                <img src="{{ asset('images/jumbotron3.png') }}" alt="Billiard Balls" class="w-full h-full object-cover" />
            </div>
            <div class="w-full md:w-1/2 bg-[#1c1c1c] text-white flex items-center justify-center px-6 md:px-16">
                <div class="max-w-md py-8 md:py-0">
                    <h2 class="text-2xl md:text-4xl font-bold mb-4">Join the Billiard<br> Community<br> Today!</h2>
                    <p class="text-sm md:text-base text-gray-300 mb-6 leading-relaxed">
                        Be part of a vibrant and passionate<br> community that shares your love for<br> billiards. Connect
                        with
                        players of all<br> skill levels, participate in events,<br> share tips and tricks, and grow your<br>
                        game
                        together.
                    </p>
                    <!-- CONNECT NOW -> abu gelap -->
                    <a href="#"
                        class="inline-block px-4 py-2 bg-[#2D2D2D] hover:bg-[#3A3A3A] text-white text-sm font-medium rounded focus:outline-none focus:ring-2 focus:ring-white/30">
                        Connect Now
                    </a>
                </div>
            </div>
        </section>

        <!-- Latest News & Events -->
        <section
            class="relative bg-cover bg-center bg-no-repeat text-white vh-section px-6 md:px-16 py-12 md:py-16 bg-neutral-900"
            style="background-image: url('/images/bg/background_3.png')">
            <div class="mx-auto max-w-7xl relative z-10 h-full flex flex-col">
                <h2 class="text-2xl md:text-3xl font-bold mb-8">Latest News &amp; Events</h2>

                <!-- GRID: kiri 2/3, kanan 1/3 -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- MAIN EVENT (2 kolom) -->
                    <article
                        class="relative lg:col-span-2 rounded-xl overflow-hidden h-[420px] md:h-[520px] bg-neutral-800">
                        <img src="{{ asset('images/latest-event/1.png') }}" alt="Master the Game Workshop"
                            class="absolute inset-0 w-full h-full object-cover" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>

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

                    <!-- SUB EVENTS (1 kolom) -->
                    <aside class="flex flex-col gap-6">
                        @php
                            $events = [
                                [
                                    'img' => 'images/latest-event/2.png',
                                    'title' =>
                                        'The Road to Glory: National Billiard Championship 2025 Kicks Off This Summer',
                                    'desc' =>
                                        'Experience the pinnacle of billiard excellence as top players from across the country compete for the prestigious championship title.',
                                ],
                                [
                                    'img' => 'images/latest-event/3.png',
                                    'title' =>
                                        'Building Connections Through Billiards: Community Cue Night for All Skill Levels',
                                    'desc' =>
                                        'Join us for an evening of camaraderie, casual matches, and friendly competition. Friendly for beginners—challenging for regulars. All are welcome!',
                                ],
                                [
                                    'img' => 'images/latest-event/4.png',
                                    'title' => 'Regional Doubles Tournament Offers Thrilling Prizes',
                                    'desc' =>
                                        'Test your partnership and precision in this high-energy doubles tournament. Face teams from around the region for exciting rewards and recognition.',
                                ],
                            ];
                        @endphp

                        @foreach ($events as $e)
                            <article
                                class="flex gap-4 items-start p-3 rounded-xl bg-neutral-800/50 hover:bg-neutral-800 transition min-h-[108px]">
                                <img src="{{ asset($e['img']) }}" alt="{{ $e['title'] }}"
                                    class="w-32 md:w-36 h-40 object-cover rounded-md bg-neutral-700 flex-shrink-0" />
                                <div class="flex flex-col justify-center">
                                    <h4 class="text-sm md:text-base font-semibold leading-snug">
                                        {{ $e['title'] }}
                                    </h4>
                                    <p class="text-xs md:text-sm text-gray-300 mt-1">
                                        {{ $e['desc'] }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </aside>

                </div>
            </div>
        </section>


    </div>
@endsection
