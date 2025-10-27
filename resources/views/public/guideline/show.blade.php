@extends('app')
@section('title', $guideline->title . ' - Xander Billiard')

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

  /* ===== Typography for article content (mobile-first) ===== */
  .article-content {
    --c-text: #e5e7eb;
    --c-muted: #9ca3af;
    --c-hr: #1f2937;
    --radius: 14px;
    color: var(--c-text);
    line-height: 1.8;
    font-size: 1rem;
  }
  .article-content h2,
  .article-content h3,
  .article-content h4 {
    font-weight: 800;
    line-height: 1.25;
    margin: 1.25em 0 .6em;
  }
  .article-content h2 { font-size: clamp(1.25rem, 4.5vw, 1.75rem); }
  .article-content h3 { font-size: clamp(1.15rem, 4.2vw, 1.4rem); }
  .article-content h4 { font-size: clamp(1.05rem, 3.8vw, 1.15rem); }
  .article-content p { margin: .9em 0; color: var(--c-text); }
  .article-content ul, .article-content ol { margin: .9em 0 .9em 1.25em; }
  .article-content li + li { margin-top: .25em; }
  .article-content blockquote{
    margin: 1.1em 0;
    padding: .9em 1em;
    background: rgba(255,255,255,.04);
    border-left: 3px solid rgba(255,255,255,.18);
    border-radius: 10px;
    color: #d1d5db;
  }
  .article-content hr {
    border: 0;
    border-top: 1px solid var(--c-hr);
    margin: 1.5em 0;
  }
  .article-content img {
    width: 100%;
    height: auto;
    border-radius: var(--radius);
  }
  .article-content pre, .article-content code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
  }
  .article-content pre {
    overflow: auto;
    padding: .9rem 1rem;
    background: #0b1020;
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 12px;
    line-height: 1.6;
    font-size: .95rem;
  }
  .article-content code {
    background: rgba(255,255,255,.06);
    padding: .15rem .4rem;
    border-radius: 6px;
    font-size: .9em;
  }

  /* ===== Responsive containers & helpers ===== */
  .container-narrow { max-width: 1200px; margin: 0 auto; }
  .shadow-soft { box-shadow: 0 8px 30px rgba(0,0,0,.35); }

  /* Responsive 16:9 wrapper (untuk YouTube) */
  .video-wrap {
    position: relative;
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    background: #0f172a;
    border: 1px solid rgba(255,255,255,.06);
  }
  .video-wrap::before { content:""; display:block; padding-top:56.25%; }
  .video-wrap iframe { position:absolute; inset:0; width:100%; height:100%; }

  /* ===== Sidebar ===== */
  .sidebar-card { border: 1px solid rgba(255,255,255,.06); border-radius: 16px; }
  .rec-thumb { width: 88px; height: 88px; background: #1f2937; border-radius: 12px; overflow: hidden; flex-shrink: 0; }
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Desktop adjustments */
  @media (min-width: 1024px){
    .article-content { font-size: 1.06rem; line-height: 1.9; }
    .sticky-desktop { position: sticky; top: 88px; }
  }

  /* Breadcrumb overflow fix */
  .breadcrumbs a, .breadcrumbs span { white-space: nowrap; }

  /* ====== Progressive Image Loader (Spinner + Camera Fallback) ====== */
  .img-frame{ position:relative; background:#111827; }
  .img-el{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .25s ease; }
  .img-frame.loaded .img-el{ opacity:1; }
  .img-frame .spinner, .img-frame .ph{
    position:absolute; inset:0; display:grid; place-items:center; pointer-events:none;
  }
  .img-frame .ph{ display:none; color:#9ca3af; }
  .img-frame.error .ph{ display:grid; }
  .img-frame.loaded .spinner{ display:none; }

  .spinner svg{ animation:spin 1s linear infinite; opacity:.9; }
  @keyframes spin{ from{ transform:rotate(0deg); } to{ transform:rotate(360deg); } }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    /* ====== Hapus kata-kata terlarang dari output ====== */
    $removeWords = ['billiards','basics','beginner','cue','balls'];
    $pattern = '/\b(' . implode('|', array_map(fn($w)=>preg_quote($w,'/'), $removeWords)) . ')\b/iu';

    // Untuk teks biasa (rapikan spasi setelah dihapus)
    $stripWordsPlain = function ($text) use ($pattern) {
        $text = preg_replace($pattern, '', (string)$text);
        $text = preg_replace('/\s{2,}/', ' ', $text);
        $text = preg_replace('/\s+([.,;:!?])/', '$1', $text);
        return trim($text ?? '');
    };
    // Untuk HTML: hanya hapus kata, tanpa normalisasi spasi agar aman untuk atribut/tag
    $stripWordsHtml = function ($html) use ($pattern) {
        return preg_replace($pattern, '', (string)$html);
    };

    $pubDate = optional($guideline->published_at)->format('d F Y');

    // resolve featured image
    $imagePath = null;
    if (!empty($guideline->featured_image)) {
        $raw = $guideline->featured_image;
        if (Str::startsWith($raw, 'guidelines/')) {
            $imagePath = Storage::url($raw);
        } elseif (!file_exists(public_path($raw)) && file_exists(public_path('images/guidelines/' . basename($raw)))) {
            $imagePath = asset('images/guidelines/' . basename($raw));
        } else {
            $imagePath = asset($raw);
        }
    }
    $placeholder = asset('images/guidelines/placeholder.jpg');

    // ==== Content fallback: jika user tidak pakai HTML, tetap hormati newline ====
    $contentRaw = (string) ($guideline->content ?? '');
    $looksHtml = Str::contains(Str::lower($contentRaw), ['<p','<br','<h','<ul','<ol','<div','<section','<table','</']);
    // Terapkan filter kata terlarang
    if ($looksHtml) {
        $contentFiltered = $stripWordsHtml($contentRaw);
        $contentSafe = $contentFiltered; // sudah HTML
    } else {
        $contentFiltered = $stripWordsPlain($contentRaw);
        $contentSafe = nl2br(e($contentFiltered));
    }

    // Title & description yang sudah difilter
    $titleSan = $stripWordsPlain($guideline->title ?? '');
    $descSan  = $stripWordsPlain($guideline->description ?? '');
@endphp

<div class="bg-neutral-900 text-white min-h-screen">
    <!-- Breadcrumbs -->
    <nav class="px-4 sm:px-6 lg:px-24 pt-5 pb-2 text-sm">
        <div class="container-narrow">
            <div class="breadcrumbs flex items-center gap-2 text-gray-400 overflow-x-auto no-scrollbar">
                <a href="{{ route('community.index') }}" class="hover:text-white">Community</a>
                <span>/</span>
                <a href="{{ route('guideline.index') }}" class="hover:text-white">Guideline</a>
                <span>/</span>
                <span class="text-white line-clamp-2">{{ $titleSan }}</span>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="px-4 sm:px-6 lg:px-24 pt-4">
        <div class="container-narrow">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight mb-2">
                {{ $titleSan }}
            </h1>
            @if($pubDate)
                <p class="text-sm sm:text-base text-gray-400">{{ $pubDate }}</p>
            @endif
        </div>
    </header>

    <!-- Featured Image (with spinner & camera fallback) -->
    <section class="px-4 sm:px-6 lg:px-24 mt-5 mb-6">
        <div class="container-narrow">
            <div class="img-frame shadow-soft" style="border-radius:16px; height: clamp(220px, 48vh, 520px);">
                <img
                    class="img-el js-progressive"
                    alt="{{ $titleSan }}"
                    data-src="{{ $imagePath ?: $placeholder }}"
                    data-fallback="{{ $placeholder }}"
                    src="data:image/gif;base64,R0lGODlhAQABAAAAACw="
                    loading="eager"
                    decoding="async">
                <div class="spinner" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="2">
                        <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                        <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke-linecap="round"></path>
                    </svg>
                </div>
                <div class="ph" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor">
                        <path d="M4 7h3l2-2h6l2 2h3a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2zm8 11a5 5 0 100-10 5 5 0 000 10z"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Content & Sidebar -->
    <main class="px-4 sm:px-6 lg:px-24 pb-16">
        <div class="container-narrow">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
                <!-- Main -->
                <article class="lg:col-span-2">
                    <div class="article-content">
                        {{-- Short description: HORMATI NEWLINE (setelah filter) --}}
                        @if(!empty($guideline->description))
                            <div class="text-[0.98rem] sm:text-base text-gray-300 mb-4 whitespace-pre-line break-words">
                                {!! nl2br(e($descSan)) !!}
                            </div>
                        @endif

                        {{-- Main content (HTML atau plain text fallback) --}}
                        <div class="mt-4">
                            {!! $contentSafe !!}
                        </div>

                        {{-- Video --}}
                        @if (!empty($guideline->youtube_url))
                            @php
                                $embed = preg_replace('~^(https?://)?(www\.)?(youtube\.com/watch\?v=|youtu\.be/)~i', 'https://www.youtube.com/embed/', $guideline->youtube_url);
                            @endphp
                            <div class="mt-8">
                                <h3 class="text-lg sm:text-xl font-bold mb-3">Video Tutorial</h3>
                                <div class="video-wrap shadow-soft">
                                    <iframe
                                        src="{{ $embed }}"
                                        title="YouTube video"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        @endif

                        {{-- Author --}}
                        <div class="mt-10 pt-6 border-top border-white/10" style="border-top:1px solid rgba(255,255,255,.1);">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 grid place-items-center ring-1 ring-white/10">
                                    <span class="text-lg font-bold">
                                        {{ strtoupper(mb_substr($guideline->author_name ?? 'A', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-semibold leading-tight">{{ $guideline->author_name }}</p>
                                    <p class="text-xs text-gray-400">Author</p>
                                </div>
                            </div>
                        </div>

                        {{-- Back button (mobile-first) --}}
                        <div class="mt-10">
                            <a href="{{ route('guideline.index') }}"
                               class="inline-flex items-center gap-2 rounded-lg px-4 py-2 bg-white/10 hover:bg-white/15 transition ring-1 ring-white/10">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Back to Guidelines</span>
                            </a>
                        </div>
                    </div>
                </article>

                <!-- Sidebar -->
                <aside class="lg:col-span-1">
                  <div class="sidebar-card p-5 lg:p-6 sticky-desktop bg-[#1f1f1f] rounded-xl text-white">
                    <h3 class="text-lg sm:text-xl font-bold mb-5">Recommended</h3>

                    <div class="space-y-5">
                      @foreach ($relatedGuidelines as $related)
                        @php
                            $rImage = null;
                            if (!empty($related->featured_image)) {
                                $rawR = $related->featured_image;
                                if (!file_exists(public_path($rawR)) && file_exists(public_path('images/guidelines/' . basename($rawR)))) {
                                    $rImage = asset('images/guidelines/' . basename($rawR));
                                } else {
                                    $rImage = asset($rawR);
                                }
                            }
                            $rDate = optional($related->published_at)->format('d F Y');
                            $relatedTitleSan = $stripWordsPlain($related->title ?? '');
                        @endphp

                        <div class="flex gap-4">
                          <div class="rec-thumb relative">
                            <div class="img-frame" style="border-radius:12px; width:100%; height:100%;">
                                <img
                                    class="img-el js-progressive"
                                    alt="{{ $relatedTitleSan }}"
                                    data-src="{{ $rImage ?: $placeholder }}"
                                    data-fallback="{{ $placeholder }}"
                                    src="data:image/gif;base64,R0lGODlhAQABAAAAACw="
                                    loading="lazy"
                                    decoding="async">
                                <div class="spinner" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                                        <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke-linecap="round"></path>
                                    </svg>
                                </div>
                                <div class="ph" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor">
                                        <path d="M4 7h3l2-2h6l2 2h3a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2zm8 11a5 5 0 100-10 5 5 0 000 10z"/>
                                    </svg>
                                </div>
                            </div>
                          </div>

                          <div class="min-w-0">
                            <a href="{{ route('guideline.show', ['slug' => $related->slug]) }}"
                               class="font-medium text-white/90 hover:text-gray-200 transition line-clamp-2">
                              {{ $relatedTitleSan }}
                            </a>
                            @if($rDate)
                              <p class="text-[12px] text-gray-400 mt-1">{{ $rDate }}</p>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>

                    <div class="mt-7">
                      <a href="{{ route('guideline.index') }}"
                         class="block w-full text-center rounded-lg bg-[#2a2a2a] hover:bg-[#333] transition px-4 py-2.5 text-white font-medium">
                        View All Guidelines
                      </a>
                    </div>
                  </div>
                </aside>

            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
/* iOS rubber-band guard: cegah putih saat overscroll tepi */
(function () {
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

/* Progressive image loader (spinner + camera fallback) */
(function(){
  function setupProgressiveImages(root){
    const scope = root || document;
    const imgs = scope.querySelectorAll('img.js-progressive[data-src]');
    const io = 'IntersectionObserver' in window ? new IntersectionObserver(onIntersect, { rootMargin: '200px 0px' }) : null;

    imgs.forEach(img=>{
      const frame = img.closest('.img-frame') || img.parentElement;
      if (!frame) return;
      if (io) io.observe(img); else loadNow(img, frame);
      img.addEventListener('error', ()=> handleError(img, frame));
      img.addEventListener('load',  ()=> handleLoad(img, frame));
    });

    function onIntersect(entries, obs){
      entries.forEach(entry=>{
        if (!entry.isIntersecting) return;
        const img = entry.target;
        const frame = img.closest('.img-frame') || img.parentElement;
        loadNow(img, frame);
        obs.unobserve(img);
      });
    }
    function loadNow(img, frame){
      const src = img.getAttribute('data-src');
      if (src && img.src !== src) img.src = src;
    }
    function handleLoad(img, frame){
      frame.classList.add('loaded');
      frame.classList.remove('error');
    }
    function handleError(img, frame){
      const fallback = img.getAttribute('data-fallback') || img.src;
      if (img.src !== fallback){ img.src = fallback; }
      else { frame.classList.add('error'); }
    }
  }
  // Expose and run
  window.setupProgressiveImages = setupProgressiveImages;
  document.addEventListener('DOMContentLoaded', ()=> setupProgressiveImages(document));
})();
</script>
@endpush
