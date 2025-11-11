@extends('app')

@section('title', 'Community')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
<style>
  :root { color-scheme: dark; }

  html, body {
    min-height: 100%;
    margin: 0;
    background: #0a0a0a;
    overscroll-behavior: none;
  }
  body { overflow-x: hidden; }

  .featured-slider,
  .featured-slider .swiper-wrapper,
  .featured-slider .swiper-slide {
    background:#0a0a0a;
    height: 100%;
  }
  .featured-slider .swiper-slide { position: relative; }

  .vh-section { min-height: calc(100vh - var(--header-h, 0px)); }

  .hero-viewport{
    height: calc(95vh - var(--header-h, 0px));
    min-height: 750px;
  }

  @media (max-width: 640px){
    .hero-viewport{
      height: min(calc(48vh - var(--header-h, 0px)), 420px);
      min-height: 260px;
    }
    .featured-slider .swiper-slide img {
      object-position: 60% center;
    }
    .slider-prev, .slider-next {
      padding: 6px;
      transform: translateY(-50%) scale(0.9);
    }
    .swiper-pagination-bullet { width: 6px; height: 6px; }
    .featured-slider .swiper-slide h1 {
      font-size: clamp(1.1rem, 5.2vw, 1.5rem);
      line-height: 1.25;
      margin-bottom: .5rem;
    }
    .featured-slider .swiper-slide p {
      font-size: .9rem;
    }
  }

  .swiper-pagination-bullet { background: #ffffff !important; opacity: .5; }
  .swiper-pagination-bullet-active { opacity: 1; }

  .img-wrapper{
    position: relative;
    background:#141414;
    width:100%;
    height:100%;
    overflow:hidden;
  }
  .img-wrapper img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
    opacity:0;
    transition:opacity .28s ease;
  }
  .img-wrapper img.loaded{ opacity:1; }
  .img-loading{
    position:absolute;
    inset:0;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:10px;
    background:#151515;
    color:#9ca3af;
    z-index:1;
  }
  .img-loading.hidden{ display:none; }
  .spinner{
    width:42px;
    height:42px;
    border:3px solid rgba(130,130,130,.25);
    border-top-color:#9ca3af;
    border-radius:50%;
    animation:spin .8s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .camera-icon{ width:38px; height:38px; opacity:.6; }
</style>
@endpush

@php
    /**
     * NEWS IMAGE RESOLVER
     * ===================
     * Folder server:
     *   /home/xanderbilliard.site/public_html/images/community
     *
     * URL publik:
     *   {{ asset('images/community/{filename}') }}
     *
     * Logika:
     * - Ambil nama file dari field: image_url, image, banner, thumbnail, cover (berurutan).
     * - Jika value adalah URL absolut → ambil basename path-nya.
     * - Jika file tsb ada di /images/community → pakai asset('images/community/filename').
     * - Jika tidak ada → fallback ke:
     *      - community-1.png (jika ada)
     *      - placeholder.png (jika ada)
     *      - terakhir: asset('images/community/community-1.png')
     */

    $COMMUNITY_FS_BASE  = '/home/xanderbilliard.site/public_html/images/community';
    $COMMUNITY_WEB_BASE = rtrim(asset('images/community'), '/') . '/';

    $pickNewsFilename = function ($news): ?string {
        $fields = ['image_url', 'image', 'banner', 'thumbnail', 'cover'];
        foreach ($fields as $f) {
            if (!empty($news->{$f})) {
                $raw = $news->{$f};

                if (filter_var($raw, FILTER_VALIDATE_URL)) {
                    $path = parse_url($raw, PHP_URL_PATH) ?? '';
                    $name = basename(str_replace('\\','/',$path));
                } else {
                    $name = basename(str_replace('\\','/',$raw));
                }

                if ($name && $name !== '.' && $name !== '/') {
                    return $name;
                }
            }
        }
        return null;
    };

    $newsImg = function ($news, string $fallback = 'community-1.png')
        use ($COMMUNITY_FS_BASE, $COMMUNITY_WEB_BASE, $pickNewsFilename): string {

        $name = $pickNewsFilename($news);

        if ($name) {
            $fs = rtrim($COMMUNITY_FS_BASE, '/') . '/' . $name;
            if (@file_exists($fs)) {
                return $COMMUNITY_WEB_BASE . $name;
            }
        }

        $fallbacks = array_filter([$fallback, 'placeholder.png']);
        foreach ($fallbacks as $fb) {
            $fs = rtrim($COMMUNITY_FS_BASE, '/') . '/' . $fb;
            if (@file_exists($fs)) {
                return $COMMUNITY_WEB_BASE . $fb;
            }
        }

        return asset('images/community/community-1.png');
    };
@endphp

@section('content')
  <!-- Hero Slider Section -->
  <div class="relative">
    <!-- Slider Navigation -->
    <button class="absolute left-4 top-1/2 z-30 -translate-y-1/2 bg-black/30 p-2 text-white hover:bg-black/50 slider-prev">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>
    <button class="absolute right-4 top-1/2 z-30 -translate-y-1/2 bg-black/30 p-2 text-white hover:bg-black/50 slider-next">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>

    <!-- Featured News Slider -->
    <div class="relative w-full overflow-hidden hero-viewport">
      <div class="swiper featured-slider h-full">
        <div class="swiper-wrapper">
          @forelse($featuredNews as $news)
            @php
              $primary = $newsImg($news);
            @endphp

            <div class="swiper-slide">
              <div class="absolute inset-0">
                <div class="img-wrapper absolute inset-0 z-10">
                  <div class="img-loading">
                    <div class="spinner" aria-hidden="true"></div>
                    <div class="sr-only">Loading image...</div>
                  </div>
                  <img
                    src="{{ $primary }}"
                    data-lazy-img
                    alt="{{ $news->title }}"
                    loading="eager"
                    decoding="async"
                  />
                </div>
                <div class="absolute inset-0 bg-black/45 z-0 pointer-events-none"></div>
              </div>

              <a href="{{ route('community.news.show', $news) }}"
                 class="absolute inset-0 z-20" aria-label="{{ $news->title }}"></a>

              <div class="absolute inset-0 z-30 flex items-center">
                <div class="mx-auto w-full max-w-7xl px-6 md:px-16">
                  <div class="max-w-3xl translate-y-6 md:translate-y-10">
                    <p class="mb-2 text-sm text-gray-300">{{ $news->published_at->format('d F Y') }}</p>
                    <h1 class="mb-4 text-3xl font-bold text-white md:text-4xl lg:text-5xl">{{ $news->title }}</h1>
                    <p class="mb-6 text-gray-200">{{ \Illuminate\Support\Str::limit($news->content, 150) }}</p>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="swiper-slide">
              <div class="absolute inset-0">
                <div class="img-wrapper absolute inset-0 z-10">
                  <div class="img-loading hidden"></div>
                  <img src="{{ asset('images/community/community-1.png') }}" class="loaded" alt="Community Banner" />
                </div>
                <div class="absolute inset-0 bg-black/45 z-0 pointer-events-none"></div>
              </div>
              <div class="absolute inset-0 z-30 flex items-center">
                <div class="mx-auto w-full max-w-7xl px-6 md:px-16">
                  <div class="max-w-3xl translate-y-6 md:translate-y-10">
                    <p class="mb-2 text-sm text-gray-300">{{ now()->format('d F Y') }}</p>
                    <h1 class="mb-4 text-3xl font-bold text-white md:text-4xl lg:text-5xl">
                      Welcome to Xander Billiard Community
                    </h1>
                    <p class="mb-6 text-gray-200">
                      Join our community of billiard enthusiasts and stay updated with the latest news and events.
                    </p>
                    <a href="{{ route('community.news.index') }}"
                       class="inline-block bg-blue-600 px-6 py-3 text-white hover:bg-blue-700 transition">
                      Browse News
                    </a>
                  </div>
                </div>
              </div>
            </div>
          @endforelse
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </div>

  <!-- Popular News Section -->
  <section class="relative bg-cover bg-center bg-no-repeat py-12"
           style="background-image: url('{{ asset('images/bg/background_2.png') }}')">
    <div class="container mx-auto px-4">
      <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-white">POPULAR NEWS</h2>
        <a href="{{ route('community.news.index') }}" class="text-sm text-white hover:text-white/80">view all</a>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($popularNews as $news)
          @php
            $shareUrl = route('community.news.show', $news);
            $primary  = $newsImg($news);
          @endphp

          <a href="{{ $shareUrl }}"
             class="group relative overflow-hidden rounded-xl border border-[#3A3A3A] bg-[#2D2D2D] flex flex-col hover:bg-[#303030] transition">
            <div class="h-48 overflow-hidden">
              <div class="img-wrapper relative w-full h-full">
                <div class="img-loading">
                  <div class="spinner" aria-hidden="true"></div>
                  <div class="sr-only">Loading image...</div>
                </div>
                <img
                  src="{{ $primary }}"
                  data-lazy-img
                  alt="{{ $news->title }}"
                  loading="lazy"
                  decoding="async"
                />
              </div>
            </div>

            <div class="p-4 pb-6">
              <h3 class="mb-2 text-lg font-semibold text-white group-hover:underline">{{ $news->title }}</h3>
              <p class="text-sm text-gray-300">{{ $news->published_at->format('d F Y') }}</p>
            </div>

            <button type="button"
                    class="absolute z-20 p-2 text-white hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-white/30 right-3 bottom-3"
                    style="right:12px;bottom:12px;background:transparent"
                    aria-label="Share {{ $news->title }}"
                    data-share-url="{{ $shareUrl }}"
                    data-share-title="{{ $news->title }}"
                    onclick="event.preventDefault(); event.stopPropagation(); handleShare(this);">
              <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="18" cy="8" r="2.25"></circle>
                <circle cx="6"  cy="12" r="2.25"></circle>
                <circle cx="18" cy="16" r="2.25"></circle>
                <path d="M8.1 12.7l7.8 3.6M15.9 7.7L8.1 11.3"></path>
              </svg>
            </button>
          </a>
        @empty
          <div class="col-span-3 text-center py-10">
            <p class="text-gray-200">No popular news available at the moment.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <!-- Newsletter Section -->
  <section
    class="relative isolate overflow-hidden bg-cover bg-center bg-no-repeat
           min-h-[40vh] md:min-h-[46vh] lg:min-h-[52vh]"
    style="background-image: url('{{ asset('images/community/background-1.png') }}')">

    <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-0"
         style="background:linear-gradient(to right,
            rgba(0,0,0,.88) 0%,
            rgba(0,0,0,.76) 18%,
            rgba(0,0,0,.62) 36%,
            rgba(0,0,0,.45) 55%,
            rgba(0,0,0,.28) 74%,
            rgba(0,0,0,0) 100%);"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-6 py-16 md:py-18 lg:py-20">
      <div class="max-w-3xl">
        <h2 class="text-3xl sm:text-4xl md:text-[52px] leading-tight font-extrabold text-white tracking-tight">
          Receive Our Latest News Daily!
        </h2>
        <p class="mt-3 text-white/85 text-base md:text-lg">
          We will send you our recent news and event right to your inbox
        </p>

        <form id="newsletterForm"
              action="{{ route('subscribe.store') }}"
              method="POST"
              class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-2xl">
          @csrf
          <input type="email" name="email" placeholder="Enter your Email"
                 class="h-12 sm:h-[56px] w-full sm:flex-1 rounded-md border border-white/30 bg-white/10 backdrop-blur-sm
                        px-5 text-white placeholder-white/70 caret-white shadow
                        focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button type="submit"
                  class="h-12 sm:h-[56px] shrink-0 rounded-md bg-gray-600 px-8 font-semibold text-white transition-colors
                         hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white/20">
            Subscribe
          </button>
        </form>
      </div>
    </div>

    <script>
      (function(){
        const form = document.getElementById('newsletterForm');
        if(!form) return;
        form.addEventListener('submit', function(){
          const btn = form.querySelector('button[type="submit"]');
          if(btn){
            btn.disabled = true;
            btn.classList.add('opacity-80','cursor-not-allowed');
          }
        }, { passive:true });
      })();
    </script>
  </section>

  <!-- Latest News Section -->
  <section class="relative bg-cover bg-center bg-no-repeat py-12"
           style="background-image: url('{{ asset('images/bg/background_1.png') }}')">
    <div class="container mx-auto px-4">
      <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-white">LATEST NEWS</h2>
        <a href="{{ route('community.news.index') }}" class="text-sm text-white hover:text-white/80">view all</a>
      </div>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        @forelse($recentNews as $news)
          @php
            $shareUrl = route('community.news.show', $news);
            $primary  = $newsImg($news);
          @endphp

          <a href="{{ $shareUrl }}"
             class="group relative flex gap-4 rounded-xl border border-[#3A3A3A] bg-[#2D2D2D] p-4 hover:bg-[#303030] transition">
            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md">
              <div class="img-wrapper relative w-full h-full">
                <div class="img-loading">
                  <div class="spinner" aria-hidden="true"></div>
                  <div class="sr-only">Loading image...</div>
                </div>
                <img
                  src="{{ $primary }}"
                  data-lazy-img
                  alt="{{ $news->title }}"
                  loading="lazy"
                  decoding="async"
                />
              </div>
            </div>
            <div class="flex-grow flex flex-col pr-12">
              <h3 class="mb-2 text-lg font-semibold text-white group-hover:underline">{{ $news->title }}</h3>
              <p class="text-sm text-gray-300">{{ $news->published_at->format('d F Y') }}</p>
            </div>

            <button type="button"
                    class="absolute z-20 p-2 text-white hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-white/30 right-3 bottom-3"
                    style="right:12px;bottom:12px;background:transparent"
                    aria-label="Share {{ $news->title }}"
                    data-share-url="{{ $shareUrl }}"
                    data-share-title="{{ $news->title }}"
                    onclick="event.preventDefault(); event.stopPropagation(); handleShare(this);">
              <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="18" cy="8" r="2.25"></circle>
                <circle cx="6"  cy="12" r="2.25"></circle>
                <circle cx="18" cy="16" r="2.25"></circle>
                <path d="M8.1 12.7l7.8 3.6M15.9 7.7L8.1 11.3"></path>
              </svg>
            </button>
          </a>
        @empty
          <div class="col-span-2 text-center py-10">
            <p class="text-gray-400">No recent news available at the moment.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <!-- Chatroom Section -->
  <section
    class="relative isolate overflow-hidden bg-cover bg-center bg-no-repeat
           min-h-[40vh] md:min-h-[46vh] lg:min-h-[52vh]"
    style="background-image: url('{{ asset('images/community/background-2.png') }}')">

    <div aria-hidden="true" class="pointer-events-none absolute inset-0 z-0"
         style="background:linear-gradient(to right,
            rgba(0,0,0,.88) 0%,
            rgba(0,0,0,.76) 18%,
            rgba(0,0,0,.62) 36%,
            rgba(0,0,0,.45) 55%,
            rgba(0,0,0,.28) 74%,
            rgba(0,0,0,0) 100%);"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-6 py-16 md:py-18 lg:py-20">
      <div class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-center">
        <div class="md:w-1/2">
          <h2 class="mb-3 text-3xl sm:text-4xl md:text-[52px] leading-tight font-extrabold text-white tracking-tight">
            JOIN OUR CHATROOM NOW!
          </h2>
          <p class="mb-6 text-white/90 text-base md:text-lg">
            Join a growing community of players with real-time discussions.
          </p>
          <a href="#"
             class="inline-block rounded-md bg-gray-600 px-8 py-3 font-semibold text-white
                    hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-white/20">
            Chat Now
          </a>
        </div>

        <div class="md:w-1/2 hidden md:block">
          <!-- Optional illustration -->
        </div>
      </div>
    </div>
  </section>

  <!-- Feedback Section -->
  <section class="relative isolate bg-cover bg-center bg-no-repeat py-12 md:py-16"
           style="background-image:url('{{ asset('images/bg/background_3.png') }}')">
    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-6">
      <div class="grid md:grid-cols-2 gap-8 md:gap-12 md:items-stretch">
        <figure class="relative overflow-hidden rounded-2xl shadow-2xl min-h-[520px] md:min-h-[620px] h-full">
          <img src="{{ asset('images/community/img-1.png') }}" alt="Billiard Cue"
               class="absolute inset-0 h-full w-full object-cover" />
          <div class="pointer-events-none absolute inset-0"
               style="background:linear-gradient(135deg,
                 rgba(0,0,0,.85) 0%,
                 rgba(0,0,0,.65) 28%,
                 rgba(0,0,0,.40) 52%,
                 rgba(0,0,0,.18) 74%,
                 rgba(255,255,255,0) 96%);"></div>
        </figure>

        <div class="flex flex-col h-full">
          <header class="pb-6">
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-white">
              We Value Your Opinion!
            </h2>
            <p class="mt-3 max-w-2xl text-lg text-white/80">
              Your thoughts matter to us! Share your opinions and help us improve to better serve the billiard community.
            </p>
          </header>

          <div class="flex-1">
            <div class="h-full rounded-3xl border border-[#3A3A3A] bg-[#2D2D2D]
                        shadow-[0_25px_80px_rgba(0,0,0,.55)] p-6 md:p-8">
              <form action="{{ route('opinion.store') }}" method="POST" class="grid grid-cols-1 gap-5 h-full">
                @csrf
                <label class="block">
                  <span class="mb-1 block text-sm text-white/80">Email</span>
                  <input type="email" name="email" placeholder="Enter your email address"
                         class="w-full h-12 rounded-xl border border-[#3A3A3A] bg-[#1E1E1E] px-4
                                text-white placeholder-white/60
                                focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-transparent" />
                </label>

                <label class="block">
                  <span class="mb-1 block text-sm text-white/80">Topic/Subject</span>
                  <input type="text" name="subject" placeholder="Enter topic/subject"
                         class="w-full h-12 rounded-xl border border-[#3A3A3A] bg-[#1E1E1E] px-4
                                text-white placeholder-white/60
                                focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-transparent" />
                </label>

                <label class="block flex-1">
                  <span class="mb-1 block text-sm text-white/80">Description</span>
                  <textarea rows="8" name="description" placeholder="Enter your description"
                            class="h-full min-h[200px] w-full rounded-xl border border-[#3A3A3A] bg-[#1E1E1E] px-4 py-3
                                   text-white placeholder-white/60
                                   focus:outline-none focus:ring-2 focus:ring-white/20 focus:border-transparent"></textarea>
                </label>

                <div class="mt-8 md:mt-6 pt-2">
                  <button type="submit"
                          class="inline-flex h-12 items-center justify-center rounded-xl bg-neutral-700 px-6
                                 font-medium text-white transition-colors hover:bg-neutral-600">
                    Submit
                  </button>
                </div>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script>
  function setHeaderHeightVar() {
    const header =
      document.querySelector('#site-header') ||
      document.querySelector('[data-header]') ||
      document.querySelector('header');
    const h = header ? header.offsetHeight : 0;
    document.documentElement.style.setProperty('--header-h', h + 'px');
  }
  window.addEventListener('load', setHeaderHeightVar);
  window.addEventListener('resize', setHeaderHeightVar);

  async function handleShare(btn){
    const url = btn.getAttribute('data-share-url');
    const title = btn.getAttribute('data-share-title') || document.title;

    if (navigator.share) {
      try {
        await navigator.share({ title, url });
      } catch (err) {
        try {
          await navigator.clipboard.writeText(url);
          showShareToast('Link copied to clipboard');
        } catch (_e) {}
      }
    } else if (navigator.clipboard) {
      try {
        await navigator.clipboard.writeText(url);
        showShareToast('Link copied to clipboard');
      } catch (e) {
        prompt('Copy this link:', url);
      }
    } else {
      prompt('Copy this link:', url);
    }
  }

  function showShareToast(msg){
    let t = document.getElementById('share-toast');
    if (!t){
      t = document.createElement('div');
      t.id = 'share-toast';
      t.style.position = 'fixed';
      t.style.left = '50%';
      t.style.bottom = '24px';
      t.style.transform = 'translateX(-50%)';
      t.style.padding = '10px 14px';
      t.style.background = 'rgba(0,0,0,.75)';
      t.style.color = '#fff';
      t.style.borderRadius = '10px';
      t.style.fontSize = '14px';
      t.style.opacity = '0';
      t.style.transition = 'opacity .25s ease';
      t.style.zIndex = '9999';
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.opacity = '1';
    setTimeout(() => { t.style.opacity = '0'; }, 1600);
  }

  function initLazyImage(img){
    if (!img) return;
    const wrap   = img.closest('.img-wrapper');
    const loader = wrap ? wrap.querySelector('.img-loading') : null;

    function showCameraFallback(){
      if (!loader) return;
      loader.classList.remove('hidden');
      loader.innerHTML = `
        <svg class="camera-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M9 2a1 1 0 0 0-.894.553L7.382 4H5a3 3 0 0 0-3 3v9a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3h-2.382l-.724-1.447A1 1 0 0 0 14 2H9zm3 6a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
        </svg>
        <div class="text-xs text-gray-400">No image available</div>
      `;
    }

    const onLoad = () => {
      img.classList.add('loaded');
      if (loader) loader.classList.add('hidden');
    };
    img.addEventListener('load', onLoad, { passive:true });

    const onError = () => {
      showCameraFallback();
    };
    img.addEventListener('error', onError, { passive:true });

    if (img.complete && img.naturalWidth > 0) onLoad();
  }

  document.addEventListener('DOMContentLoaded', function() {
    new Swiper('.featured-slider', {
      loop: true,
      resistanceRatio: 0,
      autoplay: { delay: 5000, disableOnInteraction: false },
      pagination: { el: '.swiper-pagination', clickable: true },
      navigation: { nextEl: '.slider-next', prevEl: '.slider-prev' },
    });

    document.querySelectorAll('img[data-lazy-img]').forEach(initLazyImage);

    (function(){
      const isIOS = /iP(ad|hone|od)/.test(navigator.userAgent);
      if (!isIOS) return;

      let startY = 0;
      window.addEventListener('touchstart', (e) => {
        if (e.touches && e.touches.length) startY = e.touches[0].clientY;
      }, { passive: true });

      window.addEventListener('touchmove', (e) => {
        if (!e.touches || !e.touches.length) return;
        const scroller = document.scrollingElement || document.documentElement;
        const atTop = scroller.scrollTop <= 0;
        const atBottom = (scroller.scrollTop + window.innerHeight) >= (scroller.scrollHeight - 1);
        const dy = e.touches[0].clientY - startY;
        if ((atTop && dy > 0) || (atBottom && dy < 0)) e.preventDefault();
      }, { passive: false });
    })();
  });
</script>
@endpush
