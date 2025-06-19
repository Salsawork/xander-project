@extends('app')

@section('title', 'Community')

@section('content')
    <!-- Hero Slider Section -->
    <div class="relative">
        <!-- Slider Navigation -->
        <button class="absolute left-4 top-1/2 z-10 -translate-y-1/2 bg-black/30 p-2 text-white hover:bg-black/50 slider-prev">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button class="absolute right-4 top-1/2 z-10 -translate-y-1/2 bg-black/30 p-2 text-white hover:bg-black/50 slider-next">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Featured News Slider -->
        <div class="relative h-[500px] w-full overflow-hidden">
            <div class="swiper featured-slider h-full">
                <div class="swiper-wrapper">
                    @forelse($featuredNews as $news)
                    <div class="swiper-slide">
                        <div class="absolute inset-0">
                            <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}" class="h-full w-full object-cover">
                            <div class="absolute inset-0 bg-black/50"></div>
                        </div>
                        <div class="absolute inset-0 flex flex-col justify-end p-8 md:p-16">
                            <div class="max-w-3xl">
                                <p class="mb-2 text-sm text-gray-300">{{ $news->published_at->format('d F Y') }}</p>
                                <h1 class="mb-4 text-3xl font-bold text-white md:text-4xl lg:text-5xl">{{ $news->title }}</h1>
                                <p class="mb-6 text-gray-200">{{ Str::limit($news->content, 150) }}</p>
                                <a href="{{ route('community.news.show', $news) }}" class="inline-block bg-blue-600 px-6 py-3 text-white hover:bg-blue-700 transition">
                                    Read More
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="swiper-slide">
                        <div class="absolute inset-0">
                            <img src="{{ asset('images/billiard-hero.jpg') }}" alt="Billiard Championship" class="h-full w-full object-cover">
                            <div class="absolute inset-0 bg-black/50"></div>
                        </div>
                        <div class="absolute inset-0 flex flex-col justify-end p-8 md:p-16">
                            <div class="max-w-3xl">
                                <p class="mb-2 text-sm text-gray-300">{{ now()->format('d F Y') }}</p>
                                <h1 class="mb-4 text-3xl font-bold text-white md:text-4xl lg:text-5xl">Welcome to Xander Billiard Community</h1>
                                <p class="mb-6 text-gray-200">Join our community of billiard enthusiasts and stay updated with the latest news and events.</p>
                                <a href="{{ route('community.news.index') }}" class="inline-block bg-blue-600 px-6 py-3 text-white hover:bg-blue-700 transition">
                                    Browse News
                                </a>
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
    <section class="bg-gray-950 py-12">
        <div class="container mx-auto px-4">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-white">POPULAR NEWS</h2>
                <a href="{{ route('community.news.index') }}" class="text-sm text-blue-400 hover:text-blue-300">view all</a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @forelse($popularNews as $news)
                <!-- News Item -->
                <div class="overflow-hidden rounded-lg bg-gray-900 flex flex-col">
                    <div class="h-48 overflow-hidden {{ $news->image_url ? '' : 'bg-gray-800 flex items-center justify-center' }}">
                        @if($news->image_url)
                            <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}" class="h-full w-full object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="p-4 flex-grow">
                        <h3 class="mb-2 text-lg font-semibold text-white">{{ $news->title }}</h3>
                        <p class="text-sm text-gray-400">{{ $news->published_at->format('d F Y') }}</p>
                    </div>
                    <div class="px-4 pb-4 mt-auto">
                        <a href="{{ route('community.news.show', $news) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                            Read more →
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-10">
                    <p class="text-gray-400">No popular news available at the moment.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="bg-gray-900 py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col items-center justify-between gap-8 md:flex-row">
                <div class="md:w-1/2">
                    <img src="{{ asset('images/billiard-players.jpg') }}" alt="Billiard Players" class="rounded-lg">
                </div>
                <div class="md:w-1/2">
                    <h2 class="mb-4 text-2xl font-bold text-white">Receive Our Latest News Daily!</h2>
                    <p class="mb-6 text-gray-300">We will send you our recent news and event right to your inbox</p>
                    <form class="flex flex-col gap-4 sm:flex-row">
                        <input type="email" placeholder="Your email address"
                            class="flex-grow rounded-md border border-gray-700 bg-gray-800 px-4 py-2 text-white focus:border-blue-500 focus:outline-none">
                        <button type="submit"
                            class="rounded-md bg-blue-600 px-6 py-2 font-medium text-white hover:bg-blue-700">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest News Section -->
    <section class="bg-gray-950 py-12">
        <div class="container mx-auto px-4">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-bold text-white">LATEST NEWS</h2>
                <a href="{{ route('community.news.index') }}" class="text-sm text-blue-400 hover:text-blue-300">view all</a>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                @forelse($recentNews as $news)
                <!-- News Row -->
                <div class="flex gap-4 rounded-lg bg-gray-900 p-4">
                    <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md {{ $news->image_url ? '' : 'bg-gray-800 flex items-center justify-center' }}">
                        @if($news->image_url)
                            <img src="{{ asset($news->image_url) }}" alt="{{ $news->title }}" class="h-full w-full object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="flex-grow flex flex-col">
                        <div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $news->title }}</h3>
                            <p class="text-sm text-gray-400">{{ $news->published_at->format('d F Y') }}</p>
                        </div>
                        <div class="mt-auto pt-2">
                            <a href="{{ route('community.news.show', $news) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                Read more →
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-2 text-center py-10">
                    <p class="text-gray-400">No recent news available at the moment.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Chatroom Section -->
    <section class="bg-gray-900 py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col items-center justify-between gap-8 md:flex-row">
                <div class="md:w-1/2">
                    <img src="{{ asset('images/billiard-community.jpg') }}" alt="Billiard Community" class="rounded-lg">
                </div>
                <div class="md:w-1/2">
                    <h2 class="mb-4 text-2xl font-bold text-white">JOIN OUR CHATROOM NOW!</h2>
                    <p class="mb-6 text-gray-300">Join a growing community of players with real-time discussions.</p>
                    <a href="#"
                        class="inline-block rounded-md bg-blue-600 px-6 py-2 font-medium text-white hover:bg-blue-700">Chat
                        Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Feedback Section -->
    <section class="bg-gray-950 py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col gap-8 md:flex-row">
                <div class="md:w-1/3">
                    <img src="{{ asset('images/billiard-cue.jpg') }}" alt="Billiard Cue" class="rounded-lg">
                </div>
                <div class="md:w-2/3">
                    <h2 class="mb-4 text-2xl font-bold text-white">We Value Your Opinion!</h2>
                    <p class="mb-6 text-gray-300">Your thoughts matter to us! Share your opinions and help us improve to
                        better serve the billiard community.</p>

                    <form class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="email" class="mb-1 block text-sm text-gray-400">Email</label>
                            <input type="email" id="email"
                                class="w-full rounded-md border border-gray-700 bg-gray-800 px-4 py-2 text-white focus:border-blue-500 focus:outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label for="subject" class="mb-1 block text-sm text-gray-400">Topic/Subject</label>
                            <input type="text" id="subject"
                                class="w-full rounded-md border border-gray-700 bg-gray-800 px-4 py-2 text-white focus:border-blue-500 focus:outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="mb-1 block text-sm text-gray-400">Description</label>
                            <textarea id="description" rows="5"
                                class="w-full rounded-md border border-gray-700 bg-gray-800 px-4 py-2 text-white focus:border-blue-500 focus:outline-none"></textarea>
                        </div>
                        <div>
                            <button type="submit"
                                class="rounded-md bg-blue-600 px-6 py-2 font-medium text-white hover:bg-blue-700">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        .swiper-pagination-bullet {
            background: white !important;
            opacity: 0.5;
        }
        .swiper-pagination-bullet-active {
            opacity: 1;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper('.featured-slider', {
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.slider-next',
                    prevEl: '.slider-prev',
                },
            });
        });
    </script>
    @endpush
@endsection
