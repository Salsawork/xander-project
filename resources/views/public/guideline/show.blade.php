@extends('app')
@section('title', $guideline->title . ' - Xander Billiard')

@push('styles')
<style>
  :root { color-scheme: dark; }
  /* Kunci latar gelap & cegah flash putih saat overscroll */
  html, body {
    min-height: 100%;
    margin: 0;
    background: #0a0a0a;     /* gelap stabil sebagai fallback */
    overscroll-behavior: none; /* stop scroll chaining/overscroll glow */
  }
  body { overflow-x: hidden; }
</style>
@endpush

@section('content')
    <div class="bg-neutral-900 text-white min-h-screen">
        <!-- Navigation -->
        <div class="px-6 lg:px-24 py-4 text-sm text-gray-400 ">
            <div class="container mx-auto mt-8">
                <div class="flex items-center gap-2">
                    <a href="{{ route('community.index') }}" class="hover:text-white">Community</a>
                    <span>/</span>
                    <a href="{{ route('guideline.index') }}" class="hover:text-white">Guideline</a>
                    <span>/</span>
                    <span class="text-white">{{ $guideline->title }}</span>
                </div>
            </div>
        </div>

        <!-- Article Header -->
        <div class="container mx-auto px-6 lg:px-24 py-8">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">{{ $guideline->title }}</h1>
            <p class="text-gray-400">{{ $guideline->published_at->format('d F Y') }}</p>
        </div>

        <!-- Featured Image -->
        <div class="container mx-auto px-6 lg:px-24 mb-8">
            @if (!empty($guideline->featured_image))
                @php
                    $imagePath = $guideline->featured_image;
                    if (Str::startsWith($imagePath, 'guidelines/')) {
                        $imagePath = Storage::url($imagePath);
                    } elseif (!file_exists(public_path($imagePath)) && file_exists(public_path('images/guidelines/' . basename($imagePath)))) {
                        $imagePath = asset('images/guidelines/' . basename($imagePath));
                    } else {
                        $imagePath = asset($imagePath);
                    }
                @endphp
                <img src="{{ $imagePath }}" alt="{{ $guideline->title }}" class="object-cover w-full h-full">
            @else
                <div class="w-full h-[400px] bg-gray-800 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M21 15V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10" />
                        <rect width="20" height="16" x="2" y="4" rx="2" ry="2" />
                        <circle cx="8" cy="10" r="1" />
                        <path d="m21 15-5-5L5 21" />
                    </svg>
                </div>
            @endif
        </div>

        <!-- Article Content and Sidebar -->
        <div class="container mx-auto px-6 lg:px-24 py-8">
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Main Content -->
                <div class="lg:w-2/3">
                    <div class="prose prose-lg prose-invert max-w-none">
                        <p class="text-lg text-gray-300 mb-6">{{ $guideline->description }}</p>

                        {!! $guideline->content !!}

                        @if (!empty($guideline->youtube_url))
                            <div class="my-8">
                                <h3 class="text-xl font-bold mb-4">Video Tutorial</h3>
                                <div class="aspect-w-16 aspect-h-9">
                                    <iframe src="{{ str_replace('watch?v=', 'embed/', $guideline->youtube_url) }}"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen class="w-full h-[400px]"></iframe>
                                </div>
                            </div>
                        @endif

                        <!-- Tags -->
                        @if (!empty($guideline->tags))
                            <div class="mt-8">
                                <div class="flex flex-wrap gap-2">
                                    @foreach (explode(',', $guideline->tags) as $tag)
                                        <span class="bg-gray-800 text-sm px-3 py-1 rounded-full">{{ trim($tag) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Author -->
                        <div class="mt-12 border-t border-gray-800 pt-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center">
                                    <span class="text-lg font-bold">{{ substr($guideline->author_name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $guideline->author_name }}</p>
                                    <p class="text-sm text-gray-400">Author</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:w-1/3 mt-8 lg:mt-0">
                    <div class="bg-gray-800 rounded-lg p-6">
                        <h3 class="text-xl font-bold mb-6">Recommended</h3>

                        <div class="space-y-6">
                            @foreach ($relatedGuidelines as $related)
                                <div class="flex gap-4">
                                    <div class="w-20 h-20 bg-gray-700 rounded flex-shrink-0">
                                        @if (!empty($related->featured_image))
                                            @php
                                                $imagePath = $related->featured_image;
                                                if (!file_exists(public_path($imagePath)) &&
                                                    file_exists(public_path('images/guidelines/' . basename($imagePath)))) {
                                                    $imagePath = 'images/guidelines/' . basename($imagePath);
                                                }
                                            @endphp
                                            <img src="{{ asset($imagePath) }}" alt="{{ $related->title }}"
                                                class="w-full h-full object-cover rounded">
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('guideline.show', ['slug' => $related->slug]) }}"
                                            class="font-medium hover:text-blue-400">
                                            {{ $related->title }}
                                        </a>
                                        <p class="text-xs text-gray-400 mt-1">{{ $related->published_at->format('d F Y') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            <a href="{{ route('guideline.index') }}"
                                class="block w-full bg-blue-600 text-center py-2 rounded-lg hover:bg-blue-700 transition">
                                View All Guidelines
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
/* iOS rubber-band guard: cegah putih-putih saat overscroll */
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

    // Cegah bounce di tepi atas/bawah
    if ((atTop && dy > 0) || (atBottom && dy < 0)) {
      e.preventDefault();
    }
  }, { passive: false });
})();
</script>
@endpush
