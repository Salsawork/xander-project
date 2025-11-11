@extends('app')
@section('title', ' Xander Billiard')

@push('styles')
    <style>
        :root {
            color-scheme: dark;
            --page-bg: #0f0f0f;
            --card-bg: #161616;
            --card-border: #2a2a2a;
            --hover-accent: #828282;
        }

        html, body {
            height: 100%;
            background: #0a0a0a;
            overscroll-behavior-y: none;
        }
        body, main, #app { background: #0a0a0a; }

        .vh-section { min-height: calc(var(--svh, 1vh) * 100 - var(--header-h, 0px)); }

        @media (max-width: 640px) {
            .vh-section--hero { min-height: calc(var(--svh, 1vh) * 68 - var(--header-h, 0px)); }
            .hero--tight .hero-title { font-size: clamp(1.75rem, 7.2vw, 2.5rem); line-height: 1.15; }
            .hero--tight .hero-lead { font-size: clamp(0.95rem, 3.8vw, 1.05rem); }
            .hero--tight .hero-btn { padding: 0.6rem 1rem; font-size: 0.95rem; }
            .hero--tight .hero-copy-wrap { max-width: 90%; }
            .hero--tight .hero-bg { object-position: center !important; }
        }

        .no-scrollbar::-webkit-scrollbar{ display:none; }
        .no-scrollbar{ -ms-overflow-style:none; scrollbar-width:none; }

        /* ==== IMAGE LOADING STATES ==== */
        .img-wrapper {
            position: relative;
            background: var(--card-bg);
            overflow: hidden;
        }

        .img-loading {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--card-bg);
            z-index: 1;
        }

        .img-loading.hidden { display: none; }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(130, 130, 130, 0.2);
            border-top-color: var(--hover-accent);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .camera-icon { width: 48px; height: 48px; color: rgba(130, 130, 130, 0.4); }

        .img-wrapper img { opacity: 0; transition: opacity 0.3s ease; }
        .img-wrapper img.loaded { opacity: 1; }

        /* ==== CARD PRODUK ==== */
        .tp-card-ui{
            position: relative;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,.25);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background .25s ease;
            cursor: pointer;
            transform-style: preserve-3d;
        }
        .tp-card-ui .tp-imgwrap{ position: relative; background: var(--card-bg); isolation: isolate; }
        .tp-card-ui .tp-imgwrap img{
            display:block;
            width:100%; height:100%;
            object-fit: cover;
            transition: transform .35s ease, filter .35s ease;
            will-change: transform;
        }
        .tp-card-ui .shine{
            position:absolute;
            inset:-20% -40%;
            background: linear-gradient(120deg, rgba(255,255,255,0) 30%, rgba(255,255,255,.18) 45%, rgba(255,255,255,0) 60%);
            transform: translateX(-120%);
            transition: transform .75s ease;
            mix-blend-mode: screen;
            pointer-events:none;
            z-index:2;
        }

        .tp-card-ui:hover{
            transform: translateY(-6px);
            border-color: var(--hover-accent);
            box-shadow:
                0 16px 36px rgba(0,0,0,.45),
                0 0 0 2px rgba(130,130,130,.22) inset;
        }
        .tp-card-ui:hover .tp-imgwrap img{ transform: scale(1.06); }
        .tp-card-ui:hover .shine{ transform: translateX(120%); }

        .tp-card-ui:focus-within{
            border-color: var(--hover-accent);
            box-shadow: 0 0 0 3px rgba(130,130,130,.35) inset, 0 12px 28px rgba(0,0,0,.4);
        }
        .tp-card-ui:active{ transform: translateY(-2px) scale(.995); }

        .tp-meta{ background: var(--card-bg); }
        .tp-meta h3{ transition: color .2s ease; }
        .tp-card-ui:hover .tp-meta h3{ color:#f3f3f3; }

        .chip-btn{
            border-radius: 9999px;
            border: 1px solid #616161;
            color: #616161;
            background: transparent;
            padding: .45rem 1rem;
            font-size: .92rem;
            line-height: 1;
            transition: border-color .2s ease, color .2s ease, background-color .2s ease, transform .15s ease;
        }
        .chip-btn:hover{ border-color: var(--hover-accent); color: var(--hover-accent); background: rgba(130,130,130,.08); }
        .chip-btn:focus-visible{ outline: none; border-color: var(--hover-accent); box-shadow: 0 0 0 3px rgba(130,130,130,.25); }
        .chip-btn:active{ transform: translateY(1px); }

        .svc-card{ border:1px solid rgba(255,255,255,.18); background:#161616; border-radius:16px; transition:.25s ease; }
        .svc-card:hover{ transform: translateY(-4px); box-shadow: 0 10px 24px rgba(0,0,0,.35); }

        .event-img-wrapper { position: relative; overflow: hidden; }
        .event-img-wrapper .img-loading { background: rgba(22, 22, 22, 0.95); }
    </style>
@endpush

@push('scripts')
    <script>
        function setHeaderHeightVar() {
            const header =
                document.querySelector('#site-header') ||
                document.querySelector('[data-header]') ||
                document.querySelector('header');
            const h = header ? header.offsetHeight : 0;
            document.documentElement.style.setProperty('--header-h', h + 'px');
        }

        function setViewportVars() {
            const svh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--svh', svh + 'px');
        }

        function initTopPicksSlider() {
            const scroller = document.getElementById('topPicksScroller');
            const prevBtn = document.getElementById('tpPrev');
            const nextBtn = document.getElementById('tpNext');
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

        function initImageLoading() {
            const images = document.querySelectorAll('img[data-lazy-load]');
            images.forEach(img => {
                const wrapper = img.closest('.img-wrapper, .event-img-wrapper');
                const loader = wrapper?.querySelector('.img-loading');
                if (img.complete) {
                    img.classList.add('loaded');
                    if (loader) loader.classList.add('hidden');
                } else {
                    img.addEventListener('load', function() {
                        this.classList.add('loaded');
                        if (loader) loader.classList.add('hidden');
                    });
                    img.addEventListener('error', function() {
                        this.classList.add('loaded');
                        if (loader) loader.classList.add('hidden');
                    });
                }
            });
        }

        window.addEventListener('load', () => {
            setViewportVars();
            setHeaderHeightVar();
            initTopPicksSlider();
            initImageLoading();
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
        <img src="{{ asset('/images/jumbotron1.png') }}" alt="Billiard promo"
             class="hero-bg absolute inset-0 h-full w-full object-cover object-[78%_center]" />
        <div class="absolute inset-0 z-10 flex items-center">
            <div class="mx-auto w-full max-w-7xl px-6 md:px-20">
                <div class="hero-copy-wrap max-w-xl md:max-w-2xl text-white">
                    <p class="font-semibold mb-3 text-white/90 text-sm md:text-base lg:text-lg">Limited Time Offer</p>
                    <h1 class="hero-title text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] mb-5">
                        20% Off on All Billiard <br class="hidden sm:block" /> Accessories!
                    </h1>
                    <p class="hero-lead text-base md:text-lg text-white/85 leading-relaxed mb-8 max-w-prose">
                        Save 20% on all our premium billiard accessories. Don't miss out—shop now and grab this exclusive offer while it lasts!
                    </p>
                    <a href="{{ route('products.landing') }}"
                       class="hero-btn inline-flex items-center rounded-md bg-[#2a2a2a] px-5 py-2.5 text-sm md:text-base font-medium
                              text-white shadow hover:bg-[#2a2a2a]/90 focus:outline-none focus:ring-2 focus:ring-white/30">
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

    <div class="flex flex-wrap gap-3 mb-8">
      <a href="{{ url()->current() }}"><button class="chip-btn">All</button></a>
      <a href="{{ route('level', ['level' => 'professional']) }}"><button class="chip-btn">Professional Grade</button></a>
      <a href="{{ route('level', ['level' => 'beginner']) }}"><button class="chip-btn">Beginner-Friendly</button></a>
      <a href="{{ route('level', ['level' => 'under50']) }}"><button class="chip-btn">Under $50</button></a>
      <a href="{{ route('level', ['level' => 'cue-cases']) }}"><button class="chip-btn">Cue Cases</button></a>
    </div>

    @php
      use Illuminate\Pagination\LengthAwarePaginator;

      $rawItems = ($products instanceof LengthAwarePaginator)
        ? collect($products->items())
        : collect($products ?? []);

      $ua       = request()->header('User-Agent', '');
      $isMobile = (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod|IEMobile|Opera Mini/i', $ua);

      $itemsMobile = $isMobile ? $rawItems->shuffle() : $rawItems;
      $itemsDesktop = $rawItems;

      $PRODUCT_CDN = 'https://xanderbilliard.site/images/products/';
      $productImg = function ($images) use ($PRODUCT_CDN) {
          $arr = is_array($images) ? $images : ($images ? json_decode($images, true) : []);
          $first = $arr[0] ?? null;
          $file  = $first ? basename((string)$first) : null;
          if (!$file || $file === '/' || $file === '.') {
              return $PRODUCT_CDN . 'placeholder.png';
          }
          return $PRODUCT_CDN . $file;
      };
      $defaultCdnImg = $PRODUCT_CDN . 'placeholder.png';
    @endphp

    <!-- MOBILE -->
    <div class="md:hidden">
      <div class="grid grid-cols-2 gap-4 px-2">
        @forelse ($itemsMobile as $product)
          @php
            $src = $productImg($product->images);
            $hasDisc = !empty($product->discount) && $product->discount > 0;
            $discPct = $hasDisc ? ($product->discount <= 1 ? $product->discount * 100 : $product->discount) : 0;
            $final = $hasDisc
              ? $product->pricing - $product->pricing * ($product->discount <= 1 ? $product->discount : $product->discount / 100)
              : $product->pricing;
            $slug = \Illuminate\Support\Str::slug($product->name);
          @endphp

          <article class="tp-card-ui group">
            <a href="{{ route('products.detail', ['id' => $product->id, 'slug' => $slug]) }}" class="block">
              <div class="relative w-full aspect-[3/4] tp-imgwrap img-wrapper">
                <div class="img-loading"><div class="spinner"></div></div>
                <img src="{{ $src }}" alt="{{ $product->name }}" data-lazy-load
                     onerror="this.onerror=null;this.src='{{ $defaultCdnImg }}'">
                <span class="shine"></span>
                @if ($hasDisc)
                  <span class="absolute top-2 left-2 bg-red-500 text-white text-[11px] font-extrabold px-2 py-1 rounded-full z-10">
                    -{{ number_format($discPct, 0) }}%
                  </span>
                @endif
              </div>
            </a>
            <div class="tp-meta px-4 py-3">
              <a href="{{ route('products.detail', ['id' => $product->id, 'slug' => $slug]) }}">
                <h3 class="text-[14px] font-semibold tracking-tight line-clamp-1">{{ $product->name }}</h3>
              </a>
              <div class="mt-1">
                @if ($hasDisc)
                  <div class="text-gray-400 text-[12px] leading-none mb-1">
                    <span class="opacity-80">Rp</span>
                    <span class="line-through">{{ number_format($product->pricing, 0, ',', '.') }}</span>
                  </div>
                @endif
                <div class="text-white font-extrabold text-[16px] leading-tight">
                  Rp {{ number_format($final, 0, ',', '.') }}
                </div>
              </div>
            </div>
          </article>
        @empty
          <div class="col-span-2 text-center py-8"><p>Tidak ada produk yang tersedia saat ini.</p></div>
        @endforelse
      </div>
    </div>

    <!-- DESKTOP/TABLET -->
    <div class="hidden md:grid grid-cols-2 lg:grid-cols-4 gap-8">
      @forelse ($itemsDesktop as $product)
        @php
          $src = $productImg($product->images);
          $hasDisc = !empty($product->discount) && $product->discount > 0;
          $discPct = $hasDisc ? ($product->discount <= 1 ? $product->discount * 100 : $product->discount) : 0;
          $final = $hasDisc
            ? $product->pricing - $product->pricing * ($product->discount <= 1 ? $product->discount : $product->discount / 100)
            : $product->pricing;
          $slug = \Illuminate\Support\Str::slug($product->name);
        @endphp

        <article class="tp-card-ui group">
          <a href="{{ route('products.detail', ['id' => $product->id, 'slug' => $slug]) }}" class="block">
            <div class="relative aspect-[3/4] w-full tp-imgwrap img-wrapper">
              <div class="img-loading"><div class="spinner"></div></div>
              <img src="{{ $src }}" alt="{{ $product->name }}" data-lazy-load
                   onerror="this.onerror=null;this.src='{{ $defaultCdnImg }}'">
              <span class="shine"></span>
              @if ($hasDisc)
                <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-extrabold px-2 py-1 rounded-full z-10">
                  -{{ number_format($discPct, 0) }}%
                </span>
              @endif
            </div>
          </a>

          <div class="tp-meta px-4 py-3">
            <a href="{{ route('products.detail', ['id' => $product->id, 'slug' => $slug]) }}">
              <h3 class="text-[15px] font-semibold tracking-tight line-clamp-1">{{ $product->name }}</h3>
            </a>
            <div class="mt-1">
              @if ($hasDisc)
                <div class="text-gray-400 text-[13px] leading-none mb-1">
                  <span class="opacity-80">Rp</span>
                  <span class="line-through">{{ number_format($product->pricing, 0, ',', '.') }}</span>
                </div>
              @endif
              <div class="text-white font-extrabold text-[18px] leading-tight">
                Rp {{ number_format($final, 0, ',', '.') }}
              </div>
            </div>
          </div>
        </article>
      @empty
        <div class="col-span-4 text-center py-8"><p>Tidak ada produk yang tersedia saat ini.</p></div>
      @endforelse
    </div>
  </div>
</section>

    <!-- Jumbotron 2 - Guidelines -->
    <section class="relative bg-cover bg-center min-h=[70vh] md:h-screen flex items-center"
             style="background-image: url('/images/jumbotron2.png')">
        <div class="relative max-w-xl px-6 md:px-20 text-white z-10 py-16">
            <p class="font-semibold mb-2 text-sm md:text-base">Billiard Guidelines</p>
            <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-4">Billiard Playing Guide for Beginners</h1>
            <p class="text-sm md:text-base mb-6">
                Learn basic techniques, game rules, and etiquette at the billiard table. Improve your skills and enjoy
                the game with more confidence.
            </p>
            <a href="{{ route('guideline.index') }}"
               class="inline-block px-4 py-2 bg-[#2a2a2a] hover:bg-[#2a2a2a]/90 text-white text-sm font-medium rounded">
                Learn More
            </a>
        </div>
    </section>

    <!-- Services -->
    @php $svcHome = \App\Models\Service::take(6); @endphp
    <section class="relative bg-cover bg-center py-20 px-6 md:px-20"
             style="background-image: url('/images/bg/background_2.png')">
        <div class="relative z-10 text-white w-full">
            <div class="flex items-center justify-between mb-12">
                <h2 class="text-2xl md:text-3xl font-bold">Our Services</h2>
                <a href="{{ route('services.index') }}" class="text-sm text-white/80 hover:text-white">See All →</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                @forelse ($svcHome as $service)
                    <a href="{{ route('services.show', $service->slug) }}"
                       class="border border-white/20 rounded-xl p-6 flex flex-col gap-6 hover:border-white transition svc-card">
                        <div class="text-3xl mb-10 text-[#F8F8F8]"><x-icon.setting class="h-10 w-10" /></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-base font-semibold">{{ $service->title }}</p>
                                <p class="mt-1 text-sm text-white/70 line-clamp-2">{{ $service->short_description }}</p>
                            </div>
                            <span class="ml-3 text-xl">›</span>
                        </div>
                        @if (!empty($service->duration_min) || !empty($service->duration_max))
                            <div class="mt-2 text-sm text-white/80">
                                {{ $service->duration_min ?? '' }}@if(!empty($service->duration_max))–{{ $service->duration_max }}@endif menit
                            </div>
                        @endif
                    </a>
                @empty
                    @php
                        $fallback = ['Tip Installation','Tip Reshaping','Shaft Cleaning','Grip Replacement','Balancing & Refinishing','Ferrule Replacement'];
                    @endphp
                    @foreach ($fallback as $f)
                        <a href="{{ route('services.index') }}"
                           class="border border-white/20 rounded-xl p-6 flex flex-col gap-6 hover:border-white transition svc-card">
                            <div class="text-3xl mb-10 text-[#F8F8F8]"><x-icon.setting class="h-10 w-10" /></div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-base font-semibold">{{ $f }}</p>
                                    <p class="mt-1 text-sm text-white/70">Detail layanan akan segera tersedia.</p>
                                </div>
                                <span class="ml-3 text-xl">›</span>
                            </div>
                        </a>
                    @endforeach
                @endforelse
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
                    Be part of a vibrant and passionate community that shares your love for billiards. Connect with players of all skill levels, participate in events, share tips and tricks, and grow your game together.
                </p>
                <a href="{{ route('community.index') }}"
                   class="inline-block px-4 py-2 bg-[#2a2a2a] hover:bg-[#2a2a2a]/90 text-white text-sm font-medium rounded">
                    Connect Now
                </a>
            </div>
        </div>
    </section>

   <!-- Latest News & Events (UPDATED: images from /home/xanderbilliard.site/public_html/images/event) -->
<section class="relative bg-cover bg-center bg-no-repeat text-white vh-section px-6 md:px-16 py-12 md:py-16 bg-neutral-900"
         style="background-image: url('/images/bg/background_3.png')">

    @php
        use Illuminate\Support\Str;
        use App\Models\Event;
        use Carbon\Carbon;

        // Folder server:
        //   /home/xanderbilliard.site/public_html/images/event
        // URL publik:
        //   {{ asset('images/event/{filename}') }}

        $EVENT_FS_BASE  = '/home/xanderbilliard.site/public_html/images/event';
        $EVENT_WEB_BASE = rtrim(asset('images/event'), '/') . '/';

        /**
         * Ambil nama file gambar dari beberapa kemungkinan field.
         */
        $pickEventFilename = function ($event): ?string {
            $fields = ['image_url','image','banner','thumbnail','cover'];
            foreach ($fields as $f) {
                if (!empty($event->{$f})) {
                    $raw = $event->{$f};

                    // Jika full URL
                    if (filter_var($raw, FILTER_VALIDATE_URL)) {
                        $path = parse_url($raw, PHP_URL_PATH) ?? '';
                        $name = basename(str_replace('\\','/',$path));
                    } else {
                        // Jika path / nama file biasa
                        $name = basename(str_replace('\\','/',$raw));
                    }

                    if ($name && $name !== '.' && $name !== '/') {
                        return $name;
                    }
                }
            }
            return null;
        };

        /**
         * Gambar hero (besar).
         * Cek di /home/xanderbilliard.site/public_html/images/event/{filename}
         * lalu tampilkan sebagai asset('images/event/{filename}')
         */
        $eventHeroImg = function ($event, string $fallback = 'placeholder-hero.png')
            use ($EVENT_FS_BASE, $EVENT_WEB_BASE, $pickEventFilename): string {

            $name = $pickEventFilename($event);

            if ($name) {
                $fs = rtrim($EVENT_FS_BASE, '/') . '/' . $name;
                if (@file_exists($fs)) {
                    return $EVENT_WEB_BASE . $name;
                }
            }

            // fallback
            foreach ([$fallback, 'placeholder.png'] as $fb) {
                $fs = rtrim($EVENT_FS_BASE, '/') . '/' . $fb;
                if (@file_exists($fs)) {
                    return $EVENT_WEB_BASE . $fb;
                }
            }

            return $EVENT_WEB_BASE . ($fallback ?: 'placeholder.png');
        };

        /**
         * Gambar thumbnail (kecil).
         */
        $eventThumbImg = function ($event, string $fallback = 'placeholder-thumb.png')
            use ($EVENT_FS_BASE, $EVENT_WEB_BASE, $pickEventFilename): string {

            $name = $pickEventFilename($event);

            if ($name) {
                $fs = rtrim($EVENT_FS_BASE, '/') . '/' . $name;
                if (@file_exists($fs)) {
                    return $EVENT_WEB_BASE . $name;
                }
            }

            // fallback
            foreach ([$fallback, 'placeholder.png'] as $fb) {
                $fs = rtrim($EVENT_FS_BASE, '/') . '/' . $fb;
                if (@file_exists($fs)) {
                    return $EVENT_WEB_BASE . $fb;
                }
            }

            return $EVENT_WEB_BASE . ($fallback ?: 'placeholder.png');
        };

        $EVENT_HERO_FALLBACK  = 'placeholder-hero.png';
        $EVENT_THUMB_FALLBACK = 'placeholder-thumb.png';

        $heroFallbackUrl  = $EVENT_WEB_BASE . $EVENT_HERO_FALLBACK;
        $thumbFallbackUrl = $EVENT_WEB_BASE . $EVENT_THUMB_FALLBACK;

        // Ambil 5 event acak (atau bisa diganti latest() kalau mau terbaru)
        $latest   = Event::inRandomOrder()->take(5)->get();
        $featured = $latest->first();
        $side     = $latest->skip(1)->take(4);

        $fmtRange = function($e) {
            try{
                $sd = $e->start_date instanceof Carbon ? $e->start_date : ($e->start_date ? Carbon::parse($e->start_date) : null);
                $ed = $e->end_date   instanceof Carbon ? $e->end_date   : ($e->end_date   ? Carbon::parse($e->end_date)   : null);
                if(!$sd && !$ed) return '';
                if($sd && $ed && $sd->format('M') === $ed->format('M')) return $sd->format('M d') . ' - ' . $ed->format('d, Y');
                if($sd && $ed) return $sd->format('M d, Y') . ' - ' . $ed->format('M d, Y');
                return $sd ? $sd->format('M d, Y') : $ed->format('M d, Y');
            }catch(\Throwable $th){ return ''; }
        };

        $showUrl = function($e){ return route('events.show', $e); };

        $shortDesc = function($e, $limit = 160){
            $text = $e->summary ?? $e->short_description ?? $e->description ?? '';
            $text = strip_tags($text);
            return Str::limit($text, $limit);
        };
    @endphp

    <div class="mx-auto max-w-7xl relative z-10 h-full flex flex-col">
        <div class="flex items-center justify-between gap-4 mb-8">
            <h2 class="text-2xl md:text-3xl font-bold">Latest News &amp; Events</h2>

            <a href="{{ route('events.index') }}#all-events"
               class="hidden md:inline-flex items-center gap-2 text-sm font-medium text-blue-300 hover:text-white transition">
                See All Events
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if($featured)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- FEATURED CARD -->
            <article class="relative lg:col-span-2 rounded-xl overflow-hidden h-[420px] md:h-[520px] bg-neutral-800 group">
                <div class="event-img-wrapper absolute inset-0">
                    <div class="img-loading"><div class="spinner"></div></div>
                    <img src="{{ $eventHeroImg($featured, $EVENT_HERO_FALLBACK) }}"
                         alt="{{ $featured->name }}"
                         data-lazy-load
                         class="absolute inset-0 w-full h-full object-cover transition scale-100 group-hover:scale-105 duration-500"
                         onerror="this.onerror=null;this.src='{{ $heroFallbackUrl }}'"/>
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-neutral-900/90 via-neutral-900/30 to-transparent"></div>

                <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                    <div class="flex flex-wrap items-center gap-3 text-xs md:text-sm mb-3 text-gray-300">
                        @if($featured->status)
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 bg-neutral-800/70 ring-1 ring-white/10">
                                {{ $featured->status }}
                            </span>
                        @endif
                        @if($featured->start_date || $featured->end_date)
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 bg-neutral-800/70 ring-1 ring-white/10">
                                {{ $fmtRange($featured) }}
                            </span>
                        @endif
                        @if($featured->location)
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 bg-neutral-800/70 ring-1 ring-white/10">
                                {{ $featured->location }}
                            </span>
                        @endif
                    </div>

                    <h3 class="text-xl md:text-2xl font-semibold">{{ $featured->name }}</h3>
                    <p class="mt-2 text-sm md:text-base text-gray-200 max-w-3xl">
                        {{ $shortDesc($featured, 190) }}
                    </p>
                </div>

                <a href="{{ $showUrl($featured) }}" class="absolute inset-0 z-10" aria-label="Open {{ $featured->name }}"></a>
            </article>

            <!-- SIDE LIST -->
            <aside class="flex flex-col h-[420px] md:h-[520px]">
                <div class="flex flex-col gap-6 h-full overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-neutral-700 scrollbar-track-neutral-800/50">
                    @forelse ($side as $e)
                        <article class="relative flex gap-4 items-start p-3 rounded-xl bg-neutral-800/50 hover:bg-neutral-800 transition group flex-shrink-0">
                            <div class="event-img-wrapper w-24 md:w-28 h-24 md:h-28 rounded-md bg-neutral-700 flex-shrink-0 relative overflow-hidden">
                                <div class="img-loading">
                                    <svg class="camera-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <img src="{{ $eventThumbImg($e, $EVENT_THUMB_FALLBACK) }}"
                                     alt="{{ $e->name }}"
                                     data-lazy-load
                                     class="absolute inset-0 w-full h-full object-cover"
                                     onerror="this.onerror=null;this.src='{{ $thumbFallbackUrl }}'"/>
                            </div>
                            <div class="flex flex-col justify-start flex-1 min-w-0">
                                <h4 class="text-sm md:text-base font-semibold leading-snug group-hover:underline line-clamp-2">{{ $e->name }}</h4>
                                <div class="mt-1 flex flex-wrap gap-1.5 text-[10px] md:text-xs text-gray-300">
                                    @if($e->status)<span class="px-2 py-0.5 rounded-full bg-neutral-700/70">{{ $e->status }}</span>@endif
                                    @if($e->start_date || $e->end_date)<span class="px-2 py-0.5 rounded-full bg-neutral-700/70">{{ $fmtRange($e) }}</span>@endif
                                </div>
                                @if($e->location)
                                <div class="mt-1 text-[10px] md:text-xs text-gray-400 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="truncate">{{ $e->location }}</span>
                                </div>
                                @endif
                                <p class="text-xs text-gray-300 mt-1.5 line-clamp-2">{{ $shortDesc($e, 100) }}</p>
                            </div>
                            <a href="{{ $showUrl($e) }}" class="absolute inset-0 z-10" aria-label="Open {{ $e->name }}"></a>
                        </article>
                    @empty
                        <div class="text-gray-400 text-center py-8">Belum ada event lain.</div>
                    @endforelse
                </div>
            </aside>
        </div>
        @else
            <div class="rounded-xl bg-neutral-800/60 p-8 text-gray-300">Belum ada event untuk ditampilkan.</div>
        @endif

        <div class="mt-8 md:hidden">
            <a href="{{ route('events.index') }}#all-events"
               class="inline-flex items-center gap-2 bg-transparent border border-blue-400 text-blue-300 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg font-medium transition">
                See All Events
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

<style>
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: rgba(38, 38, 38, 0.5); border-radius: 3px; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(64, 64, 64, 0.8); border-radius: 3px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(82, 82, 82, 1); }
</style>

</div>
@endsection
