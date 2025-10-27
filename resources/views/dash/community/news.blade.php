@extends('app')

@section('title', 'News')

@push('styles')
<style>
  :root { color-scheme: dark; }
  html, body {
    min-height: 100%;
    margin: 0;
    background: #0a0a0a;
    overscroll-behavior: none;
  }
  body { overflow-x: hidden; }

  /* =======================
     IMAGE LOADING OVERLAY
     ======================= */
  .img-wrapper{ position: relative; background:#141414; width:100%; height:100%; overflow:hidden; }
  .img-wrapper img{
    width:100%; height:100%; object-fit:cover; display:block;
    opacity:0; transition:opacity .28s ease;
  }
  .img-wrapper img.loaded{ opacity:1; }
  .img-loading{
    position:absolute; inset:0; display:flex; flex-direction:column;
    align-items:center; justify-content:center; gap:10px;
    background:#151515; color:#9ca3af; z-index:1;
  }
  .img-loading.hidden{ display:none; }
  .spinner{
    width:34px; height:34px; border:3px solid rgba(130,130,130,.25);
    border-top-color:#9ca3af; border-radius:50%; animation:spin .8s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .camera-icon{ width:28px; height:28px; opacity:.6; }
</style>
@endpush

@php
  use Illuminate\Support\Str;

  /**
   * Build kandidat URL gambar news:
   * 1) Jika absolute URL → pakai langsung
   * 2) Jika relative:
   *    - storage/... (tetap)
   *    - uploads/... → storage/uploads/...
   *    - string lain → storage/uploads/{string}
   * 3) Fallback FE: images/community/community-1.png
   * 4) Last resort: placehold.co
   */
  $newsImgCandidates = function (?string $raw) {
      $raw = is_string($raw) ? trim($raw) : '';
      // Bersihkan kemungkinan base dev
      $clean = str_replace([
        'http://127.0.0.1:8000','https://127.0.0.1:8000',
        'http://localhost:8000','https://localhost:8000','http://localhost','https://localhost'
      ], '', $raw);

      $c = [];
      if ($clean !== '') {
          if (preg_match('~^https?://~i', $clean)) {
              $c[] = $clean;
          } else {
              $path = ltrim($clean, '/');
              if (Str::startsWith($path, 'storage/')) {
                  $c[] = asset($path);
              } elseif (Str::startsWith($path, 'uploads/')) {
                  $c[] = asset('storage/'.$path);
              } else {
                  $c[] = asset('storage/uploads/'.$path);
              }
          }
      }
      // Fallbacks FE-only
      $c[] = asset('images/community/community-1.png');
      // Last resort (hindari 404)
      $c[] = 'https://placehold.co/1200x800?text=No+Image';
      // Unique
      $uniq = [];
      foreach ($c as $x) if (is_string($x) && $x !== '' && !in_array($x, $uniq, true)) $uniq[] = $x;
      return $uniq;
  };

  // Filter kategori
  $active = Str::lower(request('category', 'all'));
  $filters = [
    ['key'=>'all',          'label'=>'All',          'param'=>null],
    ['key'=>'tips',         'label'=>'Tips',         'param'=>'Tips'],
    ['key'=>'championship', 'label'=>'Championship', 'param'=>'Championship'],
    ['key'=>'event',        'label'=>'Event',        'param'=>'Event'],
  ];
@endphp

@section('content')
    <!-- Hero Section -->
    <div class="relative bg-gray-1200">
        <div class="absolute inset-0 overflow-hidden">
            <img
              src="{{ asset('images/bg/product_breadcrumb.png') }}"
              alt="Billiard News"
              class="w-full h-full object-cover opacity-30">
            <div class="absolute inset-0 bg-black/40"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-6 py-16">
            <div class="flex flex-col space-y-2">
                <div class="flex items-center space-x-2 text-sm">
                    <a href="{{ route('community.index') }}" class="text-gray-400 hover:text-white">Community</a>
                    <span class="text-gray-600">/</span>
                    <span class="text-gray-400">News</span>
                </div>
                <h1 class="text-4xl md:text-5xl text-white font-bold mt-2 mb-4">INSIDE THE GAME: NEWS & UPDATES</h1>
            </div>
        </div>
    </div>

    <!-- Mobile Search (TOP) -->
    <div class="lg:hidden bg-[#2D2D2D] border-b border-[#3A3A3A]">
      <div class="max-w-7xl mx-auto px-4 pt-4 pb-3">
        <form id="newsSearchFormMobile" action="{{ route('community.news.index') }}" method="GET" autocomplete="off">
          <div class="flex rounded-md overflow-hidden ring-1 ring-inset ring-white/10">
            <input
              type="text"
              name="search"
              id="newsSearchInputMobile"
              value="{{ request('search') }}"
              placeholder="Search news..."
              class="flex-grow bg-[#1E1E1E] text-white px-4 py-2.5 outline-none"
              autocomplete="off"
            >
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- News Content Section -->
    <div class="bg-[#2D2D2D] py-6 lg:py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main News Column -->
                <div class="lg:w-3/4">
                    <!-- Filter pills -->
                    <div class="mb-5 flex flex-wrap items-center gap-2 sm:gap-3">
                      @foreach($filters as $f)
                        @php
                          $isActive = $active === $f['key'];
                          $query = array_filter([
                            'category' => $f['param'],
                            'search'   => request('search'),
                          ], fn($v) => filled($v));
                        @endphp
                        <a
                          href="{{ route('community.news.index', $query) }}"
                          aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                          class="px-3 py-1.5 rounded-full text-sm border transition
                                 {{ $isActive
                                    ? 'bg-blue-600 text-white border-transparent'
                                    : 'bg-[#1E1E1E] text-gray-200 border-[#3A3A3A] hover:bg-[#242424]' }}"
                        >
                          {{ $f['label'] }}
                        </a>
                      @endforeach
                    </div>

                    <!-- News Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($allNews as $news)
                        @php
                          $shareUrl   = route('community.news.show', $news);
                          $candidates = $newsImgCandidates($news->image_url ?? '');
                          $primary    = $candidates[0] ?? asset('images/community/community-1.png');
                        @endphp
                        <a href="{{ $shareUrl }}"
                           aria-label="{{ $news->title }}"
                           class="group block rounded-lg overflow-hidden shadow-md transition-transform hover:scale-105 flex flex-col relative
                                  bg-[#2D2D2D] border border-[#3A3A3A] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30">
                            <div class="h-48 overflow-hidden">
                              <div class="img-wrapper w-full h-full">
                                <div class="img-loading">
                                  <div class="spinner" aria-hidden="true"></div>
                                  <div class="sr-only">Loading image...</div>
                                </div>
                                <img
                                  src="{{ $primary }}"
                                  data-src-candidates='@json($candidates)'
                                  data-lazy-img
                                  alt="{{ $news->title }}"
                                  class="transition-transform duration-300 group-hover:scale-[1.03]"
                                  loading="lazy"
                                  decoding="async"
                                />
                              </div>
                            </div>
                            <div class="p-4 flex-grow pr-12">
                                <div class="flex items-center mb-2">
                                    @if($news->category)
                                        <span class="text-xs bg-blue-600 text-white px-2 py-1 rounded mr-2">{{ $news->category }}</span>
                                    @endif
                                    <span class="text-xs text-gray-300">{{ $news->published_at->format('d M Y') }}</span>
                                </div>
                                <h3 class="text-lg font-semibold text-white mb-2 group-hover:underline">{{ $news->title }}</h3>
                                <p class="text-sm text-gray-300 mb-3">{{ Str::limit($news->content, 100) }}</p>
                            </div>

                            <!-- Share button -->
                            <button type="button"
                                    class="absolute z-20 p-2 text-white hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-white/30"
                                    style="right:12px; bottom:12px; background:transparent"
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
                            <p class="text-gray-200">No news articles found.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($allNews->hasPages())
                    <div class="mt-10">
                        {{ $allNews->links() }}
                    </div>
                    @endif
                </div>

                <!-- Sidebar (Desktop only) -->
                <div class="lg:w-1/4 space-y-6">
                    <!-- Search (card) -->
                    <div class="rounded-lg p-5 bg-[#2D2D2D] border border-[#3A3A3A] hidden lg:block">
                        <h2 class="text-xl font-bold text-white mb-4">SEARCH</h2>
                        <form id="newsSearchForm" action="{{ route('community.news.index') }}" method="GET" autocomplete="off">
                            <div class="flex">
                                <input type="text" name="search" id="newsSearchInput" placeholder="Search news..."
                                    value="{{ request('search') }}"
                                    class="flex-grow bg-[#1E1E1E] border border-[#3A3A3A] rounded-l-md px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    autocomplete="off">
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Recent Posts -->
                    <div class="rounded-lg p-5 bg-[#2D2D2D] border border-[#3A3A3A]">
                        <h2 class="text-xl font-bold text-white mb-4">RECENT POSTS</h2>
                        <div class="space-y-4">
                            @foreach($recentNews as $recent)
                            @php
                              $rc = $newsImgCandidates($recent->image_url ?? '');
                              $rp = $rc[0] ?? asset('images/community/community-1.png');
                            @endphp
                            <a href="{{ route('community.news.show', $recent) }}" class="flex gap-3">
                                <div class="flex-shrink-0 w-16 h-16 rounded overflow-hidden border border-[#3A3A3A] bg-[#1E1E1E]">
                                  <div class="img-wrapper w-full h-full">
                                    <div class="img-loading">
                                      <div class="spinner" aria-hidden="true"></div>
                                      <div class="sr-only">Loading image...</div>
                                    </div>
                                    <img
                                      src="{{ $rp }}"
                                      data-src-candidates='@json($rc)'
                                      data-lazy-img
                                      alt="{{ $recent->title }}"
                                      loading="lazy"
                                      decoding="async"
                                    />
                                  </div>
                                </div>
                                <div>
                                  <h3 class="text-sm font-medium text-white hover:text-blue-400">
                                      {{ Str::limit($recent->title, 50) }}
                                  </h3>
                                  <p class="text-xs text-gray-300 mt-1">{{ $recent->published_at->format('d M Y') }}</p>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="rounded-lg p-5 bg-[#2D2D2D] border border-[#3A3A3A]">
                        <h2 class="text-xl font-bold text-white mb-4">TAGS</h2>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $tags = ['Billiard', 'Tournament', 'Championship', 'Players', 'Tips', 'Equipment', 'Rules', 'Events'];
                            @endphp
                            @foreach($tags as $tag)
                            <a href="#"
                                class="bg-[#1E1E1E] border border-[#3A3A3A] text-gray-200 hover:text-white hover:bg-[#242424] px-3 py-1 rounded-full text-sm transition">
                                {{ $tag }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <!-- /Sidebar -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
  // iOS rubber-band guard
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
      if ((atTop && dy > 0) || (atBottom && dy < 0)) {
        e.preventDefault();
      }
    }, { passive: false });
  })();

  // Web Share handler + fallback copy link (toast)
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

  // ===== Unified image loader for all images with [data-lazy-img] =====
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
      loader && loader.classList.add('hidden');
    };
    img.addEventListener('load', onLoad, { passive:true });

    let list = [];
    try { list = JSON.parse(img.getAttribute('data-src-candidates') || '[]') || []; } catch(e){ list = []; }
    let i = 0;

    const onError = () => {
      i++;
      if (i < list.length) {
        if (img.src !== list[i]) img.src = list[i];
      } else {
        showCameraFallback();
      }
    };
    img.addEventListener('error', onError, { passive:true });

    // If already cached
    if (img.complete && img.naturalWidth > 0) onLoad();
  }

  // Auto-search: desktop & mobile + init lazy images
  document.addEventListener('DOMContentLoaded', function() {
    const forms = [
      { input: document.getElementById('newsSearchInput'),       form: document.getElementById('newsSearchForm') },
      { input: document.getElementById('newsSearchInputMobile'), form: document.getElementById('newsSearchFormMobile') },
    ].filter(Boolean);

    forms.forEach(({input, form}) => {
      if (!input || !form) return;
      let timer = null;
      input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(() => form.submit(), 600);
      });
    });

    // Init all lazy images with spinner + fallback chain
    document.querySelectorAll('img[data-lazy-img]').forEach(initLazyImage);
  });
</script>
@endpush
