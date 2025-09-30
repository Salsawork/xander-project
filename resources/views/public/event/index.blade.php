@extends('app')
@section('title', 'Events - Xander Billiard')

@push('styles')
<style>
  /* ===== Anti white overscroll (tanpa ubah tampilan) ===== */
  :root { color-scheme: dark; }
  html, body {
    height: 100%;
    background: #0a0a0a;          /* latar gelap saat bounce */
    overscroll-behavior-y: none;  /* cegah chain overscroll (Chrome/Android/iOS modern) */
  }
  /* iOS Safari rubber-band: kanvas gelap di belakang konten */
  body::before {
    content: "";
    position: fixed;
    inset: 0;
    background: #0a0a0a;
    pointer-events: none;
    z-index: -1; /* selalu di belakang */
  }
  /* Kalau layout punya wrapper, pastikan juga gelap */
  #app, main { background: #0a0a0a; }
</style>
@endpush

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">Home / Event</p>
            <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR NEXT CHALLENGE HERE</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-24 py-18">
            <!-- <div class="w-full space-y-6 rounded-xl bg-neutral-900 p-4 text-white text-sm">
                <div>
                    <input type="text" placeholder="Search"
                        class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Status Tournament</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="status" value="Upcoming"
                                class="h-4 w-4 appearance-none rounded-full border border-white checked:border-white checked:ring-4 checked:ring-white/10 focus:ring-0" />
                            <span>Upcoming</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="status" value="Ongoing"
                                class="h-4 w-4 appearance-none rounded-full border border-white checked:border-white checked:ring-4 checked:ring-white/10 focus:ring-0" />
                            <span>Ongoing</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="radio" name="status" value="Ended"
                                class="h-4 w-4 appearance-none rounded-full border border-white checked:border-white checked:ring-4 checked:ring-white/10 focus:ring-0" />
                            <span>Ended</span>
                        </label>
                    </div>
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Game Type</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="w-full">
                        <select class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm text-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Game Type</option>
                            <option value="9-ball">9-Ball</option>
                            <option value="8-ball">8-Ball</option>
                            <option value="10-ball">10-Ball</option>
                            <option value="straight-pool">Straight Pool</option>
                        </select>
                    </div>
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Region</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="w-full">
                        <select class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm text-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">Select Region</option>
                            <option value="jakarta">Jakarta</option>
                            <option value="bandung">Bandung</option>
                            <option value="surabaya">Surabaya</option>
                            <option value="bali">Bali</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                        Filter
                    </button>
                    <button
                        class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">
                        Reset
                    </button>
                </div>
            </div> -->
            <section class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse ($events as $event)
                <!-- Event Card -->
                <div class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg flex flex-col">
                    <div class="relative">
                        <img src="{{ $event->image_url ? asset($event->image_url) : 'https://via.placeholder.com/600x400' }}" 
                             alt="{{ $event->name }}" 
                             class="w-full h-48 object-cover"
                             onerror="this.src='https://via.placeholder.com/600x400'">
                        <div class="absolute top-4 right-4 
                            @if($event->status == 'Upcoming') bg-red-600 
                            @elseif($event->status == 'Ongoing') bg-green-600 
                            @else bg-gray-600 @endif 
                            text-white text-xs font-bold px-3 py-1 rounded-full">
                            {{ $event->status }}
                        </div>
                    </div>
                    <div class="p-6 flex-grow">
                        <h3 class="text-xl font-bold mb-2">{{ $event->name }}</h3>
                        <div class="flex items-center text-sm text-gray-400 mb-2">
                            <i class="far fa-calendar-alt mr-2"></i>
                            <span>{{ $event->start_date->format('M d') }} - {{ $event->end_date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-400 mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span>{{ $event->location }}</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-400 mb-4">
                            <i class="fas fa-gamepad mr-2"></i>
                            <span>{{ $event->game_types }}</span>
                        </div>
                    </div>
                    <div class="px-6 pb-6 mt-auto text-center">
                        <a href="{{ route('events.show', $event) }}" class="inline-block w-full py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded transition-colors">
                            View Details <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="lg:col-span-2 text-center py-12">
                    <p class="text-gray-400">No events found.</p>
                </div>
                @endforelse
            </section>
        </div>
    </div>
@endsection
