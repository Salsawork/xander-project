@extends('app')
@section('title', 'Landing Page - Xander Billiard')

@section('content')
    <!-- Jumbotron -->
    <section class="relative bg-cover bg-center min-h-[70vh] md:h-screen flex items-center"
        style="background-image: url('/images/jumbotron1.png')">
        <div class="absolute inset-0 bg-gradient-to-tr from-black via-black/50 to-transparent"></div>

        <div class="relative max-w-xl px-6 md:px-20 text-white z-10 py-16">
            <p class="font-semibold mb-2 text-sm md:text-base">Limited Time Offer</p>
            <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-4">
                20% Off on All Billiard Accessories!
            </h1>
            <p class="text-sm md:text-base mb-6">
                Save 20% on all our premium billiard accessories. Don’t miss out—shop now and grab this exclusive
                offer while it lasts!
            </p>
            <a href="{{route('products.landing')}}"
                class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded">Buy
                Now</a>
        </div>
    </section>

    <!-- Top Picks -->
    <section class="relative bg-cover bg-center py-20 px-6 md:px-20"
        style="background-image: url('/images/bg/background_1.png')">
        <div class="relative z-10 text-white">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl md:text-3xl font-bold">Top Picks</h2>
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-3 mb-8">
                <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Professional
                    Grade</button>
                <button
                    class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Beginner-Friendly</button>
                <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Under
                    $50</button>
                <button class="px-4 py-1 border border-[#616161] text-[#616161] rounded-full text-sm">Cue
                    Cases</button>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @forelse ($products as $product)
                    <div>
                        @if ($product->images)
                            @php
                                $images = is_array($product->images) ? $product->images : json_decode($product->images);
                                $firstImage = is_array($images) && !empty($images) ? $images[0] : null;
                            @endphp

                            @if ($firstImage)
                                <a href="{{ route('products.detail', $product->id) }}">
                                    <img src="{{ str_replace('http://127.0.0.1:8000', '', $firstImage) }}" alt="{{ $product->name }}"
                                        class="rounded-lg w-full object-cover h-48" onerror="this.src='{{ asset('images/products/1.png') }}'"/>
                                </a>
                            @else
                                <a href="{{ route('products.detail', $product->id) }}">
                                    <img src="{{ asset('images/products/1.png') }}" alt="{{ $product->name }}"
                                        class="rounded-lg w-full object-cover h-48" />
                                </a>
                            @endif
                        @else
                            <a href="{{ route('products.detail', $product->id) }}">
                                <img src="{{ asset('images/products/1.png') }}" alt="{{ $product->name }}"
                                    class="rounded-lg w-full object-cover h-48" />
                            </a>
                        @endif
                        <div class="mt-2">
                            <a href="{{ route('products.detail', $product->id) }}" class="hover:text-blue-400">
                                <h3 class="font-bold underline">{{ $product->name }}</h3>
                            </a>
                            <p class="text-sm text-white/80">
                                @if ($product->discount > 0)
                                    <span class="line-through text-gray-400">Rp {{ number_format($product->pricing, 0, ',', '.') }}</span>
                                    Rp {{ number_format($product->pricing - ($product->pricing * $product->discount), 0, ',', '.') }}
                                @else
                                    Rp {{ number_format($product->pricing, 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-4 text-center py-8">
                        <p>Tidak ada produk yang tersedia saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Jumbotron 2 - Guidelines -->
    <section class="relative bg-cover bg-center min-h-[70vh] md:h-screen flex items-center"
        style="background-image: url('/images/jumbotron2.png')">
        <div class="absolute inset-0 bg-gradient-to-tr from-black via-black/50 to-transparent"></div>

        <div class="relative max-w-xl px-6 md:px-20 text-white z-10 py-16">
            <p class="font-semibold mb-2 text-sm md:text-base">Billiard Guidelines</p>
            <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-4">
                Billiard Playing Guide for Beginners
            </h1>
            <p class="text-sm md:text-base mb-6">
                Learn basic techniques, game rules, and etiquette at the billiard table. Improve
                your skills and enjoy the game with more confidence.
            </p>
            <a href="#"
                class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded">Learn More</a>
        </div>
    </section>

    <!-- Services -->
    <section class="relative bg-cover bg-center py-20 px-6 md:px-20"
        style="background-image: url('/images/bg/background_2.png')">
        <div class="relative z-10 text-white w-full">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-12">Our Services</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                @php
                    $services = [
                        'Tip Installation',
                        'Tip Reshaping',
                        'Shaft Cleaning',
                        'Grip Replacement',
                        'Balancing & Refinishing',
                        'Ferrule Replacement',
                    ];
                @endphp
                @foreach ($services as $service)
                    <div class="border border-white/20 rounded-xl p-6 flex flex-col gap-6 hover:border-white transition">
                        <div class="text-3xl mb-10 text-[#F8F8F8]"><x-icon.setting class="h-10 w-10" /></div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-[#F8F8F8]">{{ $service }} <span class="ml-3">></span></p>
                        </div>
                    </div>
                @endforeach
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
                    Be part of a vibrant and passionate community that shares your love for billiards. Connect with
                    players of all skill levels, participate in events, share tips and tricks, and grow your game
                    together.
                </p>
                <a href="#"
                    class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded">
                    Connect Now
                </a>
            </div>
        </div>
    </section>

    <!-- Latest Events -->
    <section class="relative bg-cover bg-center bg-no-repeat text-white py-20 px-6 md:px-16"
        style="background-image: url('/images/bg/background_3.png')">
        <div class="relative z-10">
            <h2 class="text-2xl md:text-3xl font-bold mb-8">Latest Events</h2>

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Main Event -->
                <div class="lg:w-2/3">
                    <img src="{{ asset('images/latest-event/1.png') }}" alt="Main Event"
                        class="w-full h-80 object-cover rounded" />
                    <h3 class="mt-4 font-semibold text-base md:text-lg">
                        Master the Game: A Comprehensive Billiard Workshop with Experts
                    </h3>
                    <p class="text-sm text-gray-300 mt-2">
                        Take your billiard skills to the next level with our in-depth workshop led by professional
                        players and coaches.
                    </p>
                </div>

                <!-- Sub Events -->
                <div class="lg:w-1/3 flex flex-col gap-4">
                    @for ($i = 2; $i <= 4; $i++)
                        <div class="flex gap-4">
                            <img src="{{ asset("images/latest-event/$i.png") }}" alt="Event {{ $i }}"
                                class="w-24 h-20 object-cover rounded" />
                            <div>
                                <h4 class="text-sm font-semibold leading-snug">
                                    Event Title {{ $i }}
                                </h4>
                                <p class="text-xs text-gray-400 mt-1">
                                    Short description for event {{ $i }}.
                                </p>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </section>
@endsection
