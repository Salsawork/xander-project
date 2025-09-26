@extends('app')

@section('title', $news->title)

@section('content')
    <div class="bg-gray-950 text-white min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Article Content -->
            <div class="md:col-span-2 space-y-4">
                <div class="text-sm text-gray-400 flex items-center space-x-2">
                    <a href="{{ route('community.index') }}" class="hover:text-white">Community</a>
                    <span>/</span>
                    <a href="{{ route('community.news.index') }}" class="hover:text-white">News</a>
                    <span>/</span>
                    <span class="text-gray-300">{{ Str::limit($news->title, 30) }}</span>
                </div>

                <h1 class="text-3xl md:text-4xl font-bold leading-tight">{{ $news->title }}</h1>
                <p class="text-gray-400 text-sm">{{ $news->published_at->format('d F Y') }}</p>

                @if($news->image_url)
                    <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}"
                        class="rounded-lg w-full object-cover shadow-md">
                @else
                    <div class="rounded-lg w-full h-64 bg-gray-800 flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif

                <div class="text-gray-300 text-sm leading-relaxed space-y-4">
                    {!! nl2br(e($news->content)) !!}
                </div>
            </div>

            <!-- Sidebar -->
            <div class="md:col-span-1 space-y-6">
                <!-- Recent News -->
                <div>
                    <h3 class="text-lg font-semibold border-b border-gray-700 pb-2">Recent News</h3>
                    @foreach($recentNews as $recent)
                        <div class="flex space-x-4 items-start hover:bg-gray-800 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-gray-700 rounded overflow-hidden">
                                @if($recent->image_url)
                                    <img src="{{ asset($recent->image_url) }}" alt="{{ $recent->title }}" 
                                        class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="text-sm">
                                <a href="{{ route('community.news.show', $recent) }}" class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($recent->title, 50) }}
                                </a>
                                <p class="text-xs text-gray-400 mt-1">{{ $recent->published_at->format('d F Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Categories -->
                <div>
                    <h3 class="text-lg font-semibold border-b border-gray-700 pb-2">Categories</h3>
                    <div class="mt-3 space-y-2">
                        @foreach($categories as $category)
                            <div class="flex justify-between items-center">
                                <a href="{{ route('community.news.index', ['category' => $category]) }}" 
                                   class="text-gray-300 hover:text-blue-400">
                                    {{ $category }}
                                </a>
                                <span class="text-gray-500 text-sm">{{ $categoryCount[$category] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Related News -->
                @if($relatedNews->count() > 0)
                <div>
                    <h3 class="text-lg font-semibold border-b border-gray-700 pb-2">Related News</h3>
                    @foreach($relatedNews as $related)
                        <div class="flex space-x-4 items-start hover:bg-gray-800 p-2 rounded transition mt-3">
                            <div class="w-16 h-16 bg-gray-700 rounded overflow-hidden">
                                @if($related->image_url)
                                    <img src="{{ asset($related->image_url) }}" alt="{{ $related->title }}" 
                                        class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="text-sm">
                                <a href="{{ route('community.news.show', $related) }}" class="text-gray-100 font-medium leading-tight hover:text-blue-400">
                                    {{ Str::limit($related->title, 50) }}
                                </a>
                                <p class="text-xs text-gray-400 mt-1">{{ $related->published_at->format('d F Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
