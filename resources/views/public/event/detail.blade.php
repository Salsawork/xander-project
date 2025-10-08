@extends('app')
@section('title', $event->name . ' - Xander Billiard')

@section('content')
    {{-- ====== Anti "putih-putih" saat scroll (iOS Safari & browser lain) ====== --}}
    <style>
        :root { color-scheme: dark; }

        /* Pastikan latar global selalu gelap */
        html, body {
            height: 100%;
            min-height: 100%;
            background: #0a0a0a;          /* warna gelap global */
            overscroll-behavior-y: none;  /* cegah bounce/scroll chaining (Chrome/Firefox/Edge/iOS 16+) */
            overscroll-behavior-x: none;
        }

        /* Beberapa layout pakai wrapper #app/main – samakan warna untuk jaga-jaga */
        #app, main { background: #0a0a0a; }

        /* iOS Safari lama tidak full support overscroll-behavior:
           trik: bentangkan kanvas gelap tak terlihat di belakang viewport */
        body::before{
            content: "";
            position: fixed;
            /* "inset" negatif untuk menutupi area elastis saat rubber-banding */
            inset: -40vh -40vw;
            background: #0a0a0a;
            z-index: -1;
            pointer-events: none;
        }

        /* Smooth touch scrolling tetap aktif */
        body { -webkit-overflow-scrolling: touch; touch-action: pan-y; }

        /* Hindari flash putih pada gambar PNG transparan */
        img { display: block; background: transparent; }
    </style>

    <div class="min-h-screen bg-neutral-900 text-white">
        <!-- HERO / BREADCRUMB -->
        <div class="mb-8 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <nav class="text-sm text-gray-400 mt-1" aria-label="Breadcrumb">
                <a href="{{ route('index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-1 opacity-60">/</span>
                <a href="{{ route('events.index') }}" class="hover:text-white transition">Event</a>
                <span class="mx-1 opacity-60">/</span>
                <span class="text-gray-200" aria-current="page">{{ $event->name }}</span>
            </nav>
            <h2 class="text-4xl font-bold uppercase text-white">{{ $event->name }}</h2>
        </div>

        <!-- Layout utama -->
        <div class="container mx-auto px-8 pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Layout Kiri (2/3 lebar pada desktop) -->
                <div class="lg:col-span-2">
                    <!-- Card foto dan deskripsi (tinggi sesuai 4 card kanan) -->
                    <div class="bg-neutral-800 rounded-xl p-6 h-auto">
                        <!-- Foto event -->
                        <div class="mb-6 rounded-lg overflow-hidden">
                            <img
                                src="{{ $event->image_url ? asset($event->image_url) : 'https://via.placeholder.com/1200x600' }}"
                                alt="{{ $event->name }}"
                                class="w-full h-auto object-cover rounded-lg">
                        </div>
                        
                        <!-- Deskripsi dan detail utama -->
                        <div>
                            <!-- Judul dan status -->
                            <div class="flex justify-between items-center mb-6">
                                <h1 class="text-3xl font-bold">{{ $event->name }}</h1>
                                <span class="px-4 py-1 rounded-full text-sm 
                                    @if($event->status == 'Upcoming') bg-red-600 
                                    @elseif($event->status == 'Ongoing') bg-green-600 
                                    @else bg-gray-600 @endif 
                                    text-white">
                                    {{ $event->status }}
                                </span>
                            </div>
                            
                            <!-- Informasi dasar dalam grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-neutral-700 p-6 rounded-lg">
                                <!-- Tanggal -->
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Date:</p>
                                    <p class="font-semibold">
                                        {{ $event->start_date->format('M d') }} - {{ $event->end_date->format('M d, Y') }}
                                    </p>
                                </div>
                                
                                <!-- Lokasi -->
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Location:</p>
                                    <p class="font-semibold">{{ $event->location }}</p>
                                </div>
                                
                                <!-- Game Types -->
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Game Types:</p>
                                    <p class="font-semibold">{{ $event->game_types }}</p>
                                </div>
                            </div>
                            
                            <!-- Registration & Tickets -->
                            <div class="mb-8">
                                <h3 class="text-xl font-bold mb-4">Registration & Tickets</h3>
                                <div class="space-y-4 bg-neutral-700 p-6 rounded-lg">
                                    <div>
                                        <p class="font-medium mb-1">Player Registration:</p>
                                        <p class="text-gray-300">Open until June 15, 2025 (Limited Slots)</p>
                                    </div>
                                    <div>
                                        <p class="font-medium mb-1">Spectator Tickets:</p>
                                        <p class="text-gray-300">Available online starting April 1, 2025</p>
                                    </div>
                                    <div class="flex flex-wrap gap-4 pt-4">
                                        <a href="#" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                            Register Now
                                        </a>
                                        <a href="#" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                            Buy Ticket
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Layout Kanan (1/3 lebar pada desktop) -->
                <div class="space-y-6">
                    <!-- Card 1: About the Event -->
                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">About the Event</h3>
                        <p class="text-gray-300">
                            The {{ $event->name }} is the ultimate battleground for elite billiard players across the country. 
                            This annual event brings together top-ranked professionals, rising stars, and passionate cue sports 
                            enthusiasts to compete for national glory and a prize pool of over ${{ number_format($event->total_prize_money, 0) }}.
                        </p>
                    </div>
                    
                    <!-- Card 2: Prize Pool & Awards -->
                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Prize Pool & Awards</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="font-medium">Total Prize Pool:</p>
                                <p class="text-gray-300">${{ number_format($event->total_prize_money, 0) }}+</p>
                            </div>
                            <div>
                                <p class="font-medium">Champion:</p>
                                <p class="text-gray-300">${{ number_format($event->champion_prize, 0) }} + National Champion Trophy</p>
                            </div>
                            <div>
                                <p class="font-medium">Runner-up:</p>
                                <p class="text-gray-300">${{ number_format($event->runner_up_prize, 0) }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Third Place:</p>
                                <p class="text-gray-300">${{ number_format($event->third_place_prize, 0) }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Top 8 Finalists:</p>
                                <p class="text-gray-300">Cash prizes & special recognition</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card 3: Tournament Format -->
                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Tournament Format</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="font-medium">Divisions:</p>
                                <p class="text-gray-300">{{ $event->divisions }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Match Style:</p>
                                <p class="text-gray-300">{{ $event->match_style }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Finals:</p>
                                <p class="text-gray-300">{{ $event->finals_format }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('events.bracket', $event) }}"
                               class="block text-center bg-transparent border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                View Tournament Bracket
                            </a>
                        </div>
                    </div>
                    
                    <!-- Card 4: Broadcast & Live Streaming -->
                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Broadcast & Live Streaming</h3>
                        <p class="text-gray-300 mb-4">
                            Can't make it in person? Catch all the action live on major sports 
                            networks and online streaming platforms.
                        </p>
                        <p class="text-gray-300 mb-4">
                            Don't miss the chance to witness history in the making! Whether you're
                            here to compete, watch, or learn, {{ $event->name }} promises 
                            an unforgettable experience for every billiards fan.
                        </p>
                        <p class="text-gray-300">
                            Follow us for updates:
                            <a href="https://twitter.com/{{ $event->social_media_handle }}" class="text-blue-400 hover:underline">
                                {{ $event->social_media_handle }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
