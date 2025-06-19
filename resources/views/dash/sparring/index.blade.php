@extends('app')

@section('title', 'Sparring')

@section('content')
    <div class="bg-gray-950 text-white min-h-screen">
        <!-- Header Section with Background Image -->
        <div class="relative bg-gray-900">
            <div class="absolute inset-0 overflow-hidden">
                <img src="{{ asset('images/billiard.jpg') }}" alt="Billiard Table"
                    class="w-full h-full object-cover opacity-30">
            </div>
            <div class="relative max-w-7xl mx-auto px-6 py-16">
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center space-x-2 text-sm">
                        <a href="/" class="text-gray-400 hover:text-white">Home</a>
                        <span class="text-gray-600">/</span>
                        <span class="text-gray-400">Sparring</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold mt-2 mb-4">POWER. PRECISION. PLAY.</h1>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8">
           

            <!-- Athletes List -->
            <div class="w-full md:w-3/4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse ($athletes as $athlete)
                    <a href="{{ route('sparring.detail', $athlete->id) }}" class="block">
                        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-md transition-transform hover:scale-105">
                            <div class="relative h-64 overflow-hidden">
                                @if ($athlete->athleteDetail && $athlete->athleteDetail->image)
                                    <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}" 
                                        class="w-full h-full object-cover" 
                                        alt="{{ $athlete->name }}"
                                        onerror="this.src='{{ asset('images/athlete/athlete-1.png') }}'">
                                @else
                                    <img src="{{ asset('images/athlete/athlete-1.png') }}" 
                                        class="w-full h-full object-cover" 
                                        alt="{{ $athlete->name }}">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg">{{ $athlete->name }}</h3>
                                <p class="text-sm text-gray-300">
                                    Rp. {{ number_format($athlete->athleteDetail->price_per_session ?? 0, 0, ',', '.') }} / session
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="md:col-span-4 p-8 text-center">
                        <p class="text-gray-400">No athletes available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Floating Shopping Cart Button -->
        <button aria-label="Shopping cart with {{ count($carts ?? []) + count($sparrings ?? []) }} items" onclick="showCart()"
            class="fixed right-6 bottom-10 bg-gray-800 hover:bg-gray-700 w-16 h-16 rounded-full shadow-xl flex items-center justify-center group transition-transform transform hover:scale-110 z-50">
            <i class="fas fa-shopping-cart text-white text-3xl">
                <!-- Badge -->
                <span
                    class="absolute top-1.5 right-1.5 bg-blue-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse">
                    {{ count($carts ?? []) + count($sparrings ?? []) }}
                </span>
            </i>
        </button>

        @include('public.cart')
    </div>
@endsection
