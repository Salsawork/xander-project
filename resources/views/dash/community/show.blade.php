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
</style>
@endpush

@section('content')
    @php
      $shareUrl = route('community.news.show', $news);
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
                    <span class="text-gray-200">{{ Str::limit($news->title, 30) }}</span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $news->title }}</h1>
                <p class="text-gray-300 text-sm">{{ $news->published_at->format('d F Y') }}</p>

                @if($news->image_url)
                    <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}"
                         class="rounded-lg w-full object-cover shadow-md">
                @else
                    <div class="rounded-lg w-full h-64 bg-gray-800/70 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-500" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif

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
                        <div class="flex space-x-4 items-start hover:bg-white/5 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-black/30 rounded overflow-hidden">
                                @if($recent->image_url)
                                    <img src="{{ asset($recent->image_url) }}" alt="{{ $recent->title }}" 
                                         class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="text-sm">
                                <a href="{{ route('community.news.show', $recent) }}" class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($recent->title, 50) }}
                                </a>
                                <p class="text-xs text-gray-300 mt-1">{{ $recent->published_at->format('d F Y') }}</p>
                            </div>
                        </div>
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
                        <div class="flex space-x-4 items-start hover:bg-white/5 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-black/30 rounded overflow-hidden">
                                @if($popular->image_url)
                                    <img src="{{ asset($popular->image_url) }}" alt="{{ $popular->title }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full grid place-items-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14l4-4h12a2 2 0 0 0 2-2z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="text-sm">
                                <a href="{{ route('community.news.show', $popular) }}" class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($popular->title, 50) }}
                                </a>
                                <p class="text-xs text-gray-300 mt-1">{{ optional($popular->published_at)->format('d F Y') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-300 mt-3">No popular articles yet.</p>
                    @endforelse
                </div>
                
                @if($relatedNews->count() > 0)
                <div>
                    <h3 class="text-lg font-semibold border-b border-white/10 pb-2">Related News</h3>
                    @foreach($relatedNews as $related)
                        <div class="flex space-x-4 items-start hover:bg-white/5 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-black/30 rounded overflow-hidden">
                                @if($related->image_url)
                                    <img src="{{ asset($related->image_url) }}" alt="{{ $related->title }}" 
                                         class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="text-sm">
                                <a href="{{ route('community.news.show', $related) }}" class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($related->title, 50) }}
                                </a>
                                <p class="text-xs text-gray-300 mt-1">{{ $related->published_at->format('d F Y') }}</p>
                            </div>
                        </div>
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
</script>
@endpush
