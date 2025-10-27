@extends('app')

@section('title', $news->title)

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

@section('content')
    @php
      use Illuminate\Support\Str;

      $shareUrl = route('community.news.show', $news);

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

      $mainCandidates = $newsImgCandidates($news->image_url ?? '');
      $mainPrimary    = $mainCandidates[0] ?? asset('images/community/community-1.png');
    @endphp

    <!-- BG gambar -->
    <section
      class="relative min-h-screen text-white bg-cover bg-center"
      style="background-image: url('{{ asset('images/bg/background_1.png') }}');"
    >
        <div class="absolute inset-0 bg-black/70 pointer-events-none" aria-hidden="true"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Article -->
            <div class="md:col-span-2 space-y-4">
                <div class="text-sm text-gray-300/80 flex items-center space-x-2">
                    <a href="{{ route('community.index') }}" class="hover:text-white">Community</a>
                    <span>/</span>
                    <a href="{{ route('community.news.index') }}" class="hover:text-white">News</a>
                    <span>/</span>
                    <span class="text-gray-200">{{ \Illuminate\Support\Str::limit($news->title, 30) }}</span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $news->title }}</h1>
                <p class="text-gray-300 text-sm">{{ $news->published_at->format('d F Y') }}</p>

                <!-- Gambar utama dengan loader + fallback berantai -->
                <div class="rounded-lg w-full overflow-hidden shadow-md">
                  <div class="img-wrapper w-full" style="height:auto; max-height:520px;">
                    <div class="img-loading">
                      <div class="spinner" aria-hidden="true"></div>
                      <div class="sr-only">Loading image...</div>
                    </div>
                    <img
                      src="{{ $mainPrimary }}"
                      data-src-candidates='@json($mainCandidates)'
                      data-lazy-img
                      alt="{{ $news->title }}"
                      loading="lazy"
                      decoding="async"
                      style="max-height:520px; width:100%; object-fit:cover;"
                    />
                  </div>
                </div>

                <!-- Deskripsi -->
                <div class="text-gray-200 text-sm leading-relaxed space-y-4">
                    {!! nl2br(e($news->content)) !!}
                </div>

                <!-- Share di bawah deskripsi (tanpa background) -->
                <div class="pt-4 border-t border-white/10 text-right">
                  <button
                    type="button"
                    class="inline-flex items-center gap-2 text-white hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-white/30 rounded"
                    aria-label="Share {{ $news->title }}"
                    data-share-url="{{ $shareUrl }}"
                    data-share-title="{{ $news->title }}"
                    onclick="handleShare(this)">
                    <svg viewBox="0 0 24 24" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <circle cx="18" cy="8" r="2.25"></circle>
                      <circle cx="6"  cy="12" r="2.25"></circle>
                      <circle cx="18" cy="16" r="2.25"></circle>
                      <path d="M8.1 12.7l7.8 3.6M15.9 7.7L8.1 11.3"></path>
                    </svg>
                    <span class="text-sm">Share</span>
                  </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="md:col-span-1 space-y-8">
                <div>
                    <h3 class="text-lg font-semibold border-b border-white/10 pb-2">Recent News</h3>
                    @foreach($recentNews as $recent)
                        @php
                          $rc = $newsImgCandidates($recent->image_url ?? '');
                          $rp = $rc[0] ?? asset('images/community/community-1.png');
                        @endphp
                        <a href="{{ route('community.news.show', $recent) }}" class="flex space-x-4 items-start hover:bg-white/5 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-black/30 rounded overflow-hidden">
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
                            <div class="text-sm">
                                <div class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($recent->title, 50) }}
                                </div>
                                <p class="text-xs text-gray-300 mt-1">{{ $recent->published_at->format('d F Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div>
                    <h3 class="text-lg font-semibold border-b border-white/10 pb-2">Popular News</h3>
                    @php
                      $popularList = (isset($popularNews) && $popularNews && $popularNews->count())
                                     ? $popularNews
                                     : (isset($recentNews) ? $recentNews->take(5) : collect());
                    @endphp
                    @forelse($popularList as $popular)
                        @php
                          $pc = $newsImgCandidates($popular->image_url ?? '');
                          $pp = $pc[0] ?? asset('images/community/community-1.png');
                        @endphp
                        <a href="{{ route('community.news.show', $popular) }}" class="flex space-x-4 items-start hover:bg-white/5 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-black/30 rounded overflow-hidden">
                              <div class="img-wrapper w-full h-full">
                                <div class="img-loading">
                                  <div class="spinner" aria-hidden="true"></div>
                                  <div class="sr-only">Loading image...</div>
                                </div>
                                <img
                                  src="{{ $pp }}"
                                  data-src-candidates='@json($pc)'
                                  data-lazy-img
                                  alt="{{ $popular->title }}"
                                  loading="lazy"
                                  decoding="async"
                                />
                              </div>
                            </div>
                            <div class="text-sm">
                                <div class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($popular->title, 50) }}
                                </div>
                                <p class="text-xs text-gray-300 mt-1">{{ optional($popular->published_at)->format('d F Y') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-gray-300 mt-3">No popular articles yet.</p>
                    @endforelse
                </div>
                
                @if($relatedNews->count() > 0)
                <div>
                    <h3 class="text-lg font-semibold border-b border-white/10 pb-2">Related News</h3>
                    @foreach($relatedNews as $related)
                        @php
                          $rc2 = $newsImgCandidates($related->image_url ?? '');
                          $rp2 = $rc2[0] ?? asset('images/community/community-1.png');
                        @endphp
                        <a href="{{ route('community.news.show', $related) }}" class="flex space-x-4 items-start hover:bg-white/5 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-black/30 rounded overflow-hidden">
                              <div class="img-wrapper w-full h-full">
                                <div class="img-loading">
                                  <div class="spinner" aria-hidden="true"></div>
                                  <div class="sr-only">Loading image...</div>
                                </div>
                                <img
                                  src="{{ $rp2 }}"
                                  data-src-candidates='@json($rc2)'
                                  data-lazy-img
                                  alt="{{ $related->title }}"
                                  loading="lazy"
                                  decoding="async"
                                />
                              </div>
                            </div>
                            <div class="text-sm">
                                <div class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($related->title, 50) }}
                                </div>
                                <p class="text-xs text-gray-300 mt-1">{{ $related->published_at->format('d F Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </section>
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
      if ((atTop && dy > 0) || (atBottom && dy < 0)) e.preventDefault();
    }, { passive: false });
  })();

  // Share handler + fallback copy link
  async function handleShare(btn){
    const url = btn.getAttribute('data-share-url') || window.location.href;
    const title = btn.getAttribute('data-share-title') || document.title;

    if (navigator.share) {
      try {
        await navigator.share({ title, url });
      } catch (err) {
        try { await navigator.clipboard.writeText(url); showShareToast('Link copied to clipboard'); } catch (_e) {}
      }
    } else if (navigator.clipboard) {
      try { await navigator.clipboard.writeText(url); showShareToast('Link copied to clipboard'); }
      catch (e) { prompt('Copy this link:', url); }
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

  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('img[data-lazy-img]').forEach(initLazyImage);
  });
</script>
@endpush
