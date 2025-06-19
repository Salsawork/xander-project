@extends('app')

@section('title', 'News')

@section('content')
    <!-- Hero Section -->
    <div class="relative bg-gray-900">
        <div class="absolute inset-0 overflow-hidden">
            <img src="{{ asset('images/billiard-news-hero.jpg') }}" alt="Billiard News"
                class="w-full h-full object-cover opacity-30">
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

    <!-- News Content Section -->
    <div class="bg-gray-950 py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main News Grid -->
                <div class="lg:w-3/4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($allNews as $news)
                        <div class="bg-gray-900 rounded-lg overflow-hidden shadow-md transition-transform hover:scale-105 flex flex-col">
                            <div class="h-48 overflow-hidden {{ $news->image_url ? '' : 'bg-gray-800 flex items-center justify-center' }}">
                                @if($news->image_url)
                                    <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="p-4 flex-grow">
                                <div class="flex items-center mb-2">
                                    @if($news->category)
                                        <span class="text-xs bg-blue-600 text-white px-2 py-1 rounded mr-2">{{ $news->category }}</span>
                                    @endif
                                    <span class="text-xs text-gray-400">{{ $news->published_at->format('d M Y') }}</span>
                                </div>
                                <h3 class="text-lg font-semibold text-white mb-2">{{ $news->title }}</h3>
                                <p class="text-sm text-gray-400 mb-3">{{ Str::limit($news->content, 100) }}</p>
                            </div>
                            <div class="px-4 pb-4 mt-auto">
                                <a href="{{ route('community.news.show', $news) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                    Read more →
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-3 text-center py-10">
                            <p class="text-gray-400">No news articles found.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($allNews->hasPages())
                    <div class="mt-10">
                        <div class="">
                            {{ $allNews->links() }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:w-1/4">
                    <!-- Search -->
                    <div class="bg-gray-900 rounded-lg p-5 mb-6">
                        <h2 class="text-xl font-bold text-white mb-4">SEARCH</h2>
                        <form action="{{ route('community.news.index') }}" method="GET">
                            <div class="flex">
                                <input type="text" name="search" placeholder="Search news..."
                                    class="flex-grow bg-gray-800 border border-gray-700 rounded-l-md px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <div class="bg-gray-900 rounded-lg p-5 mb-6">
                        <h2 class="text-xl font-bold text-white mb-4">RECENT POSTS</h2>
                        <div class="space-y-4">
                            @foreach($recentNews as $recent)
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-16 h-16 bg-gray-800 rounded overflow-hidden">
                                    @if($recent->image_url)
                                        <img src="{{ asset($recent->image_url) }}" alt="{{ $recent->title }}"
                                            class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-600" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-white">
                                        <a href="{{ route('community.news.show', $recent) }}" class="hover:text-blue-400">
                                            {{ Str::limit($recent->title, 50) }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-1">{{ $recent->published_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="bg-gray-900 rounded-lg p-5 mb-6">
                        <h2 class="text-xl font-bold text-white mb-4">CATEGORIES</h2>
                        <div class="space-y-2">
                            @foreach($categories as $category)
                            <a href="{{ route('community.news.index', ['category' => $category]) }}" 
                               class="block text-gray-300 hover:text-white">
                                {{ $category }} ({{ $categoryCount[$category] ?? 0 }})
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="bg-gray-900 rounded-lg p-5">
                        <h2 class="text-xl font-bold text-white mb-4">TAGS</h2>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $tags = ['Billiard', 'Tournament', 'Championship', 'Players', 'Tips', 'Equipment', 'Rules', 'Events'];
                            @endphp
                            @foreach($tags as $tag)
                            <a href="#"
                                class="bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white px-3 py-1 rounded-full text-sm">
                                {{ $tag }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
