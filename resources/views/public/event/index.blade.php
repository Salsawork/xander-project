@extends('app')
@section('title', 'Events - Xander Billiard')

@push('styles')
<style>
  /* ===== Anti white overscroll (tanpa ubah tampilan) ===== */
  :root { 
    color-scheme: dark; 
    /* Variabel tinggi hero khusus mobile */
    --hero-mobile-min: 300px;
    --hero-mobile-ideal: 56svh; /* sekitar 52–60% tinggi layar */
    --hero-mobile-max: 480px;
  }
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

  /* ===== Hero size (default tablet/desktop) ===== */
  .vh-section { height: clamp(540px, 84svh, 960px); }
  @media (min-width:768px) { .vh-section { height: clamp(660px, 88svh, 1020px); } }
  @media (min-width:1280px) { .vh-section { height: clamp(740px, 92svh, 1100px); } }

  /* ===== OVERRIDE MOBILE: supaya banner tidak terlalu tinggi ===== */
  @media (max-width: 767.98px) {
    .vh-section {
      height: clamp(var(--hero-mobile-min), var(--hero-mobile-ideal), var(--hero-mobile-max)) !important;
    }
    /* Fokus gambar sedikit geser ke kanan agar subjek tidak kepotong */
    .hero-img { object-position: 60% center !important; }
    /* Judul lebih kecil & rapat di mobile */
    .hero-title {
      font-size: clamp(1.25rem, 6.8vw, 1.9rem) !important;
      line-height: 1.2 !important;
    }
  }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    /** Resolve URL gambar (mendukung http/https, absolute path, atau asset()) */
    $resolveImage = function ($path) {
        if (empty($path)) return null;
        return Str::startsWith($path, ['http://','https://','/']) ? $path : asset($path);
    };

    /** Format rentang tanggal menjadi “Mar 15–17, 2025” atau “Mar 30, 2025 – Apr 2, 2025” */
    $dateRange = function ($start, $end) {
        $s = $start ? Carbon::parse($start) : null;
        $e = $end ? Carbon::parse($end) : null;
        if ($s && $e) {
            if ($s->isSameMonth($e) && $s->year === $e->year) {
                return $s->format('F j') . '–' . $e->format('j, Y');
            }
            return $s->format('M j, Y') . ' - ' . $e->format('M j, Y');
        }
        if ($s) return $s->format('M j, Y');
        if ($e) return $e->format('M j, Y');
        return '-';
    };

    /** Dummy ketika controller tidak mengirim data */
    $upcomingEvents = $upcomingEvents ?? collect([
        (object)[
            'name'       => 'Masters of the Cue: National Billiards Championship 2025',
            'image_url'  => 'images/event/bg-event-1.png',
            'start_date' => '2025-03-15',
            'end_date'   => '2025-03-17',
            'location'   => 'Grand Arena, Los Angeles, CA',
            'game_types' => '9-Ball, 8-Ball',
            'status'     => 'Upcoming',
            'slug'       => 'masters-of-the-cue-2025',
            'id'         => 1,
        ],
        (object)[
            'name'       => 'The Grand 8-Ball Open Tournament',
            'image_url'  => null,
            'start_date' => '2025-04-20',
            'end_date'   => '2025-04-22',
            'location'   => 'Empire Billiards Club, New York, NY',
            'game_types' => '8-Ball',
            'status'     => 'Upcoming',
            'slug'       => 'grand-8ball-open',
            'id'         => 2,
        ],
        (object)[
            'name'       => 'International Snooker Challenge: Battle of the Pros',
            'image_url'  => null,
            'start_date' => '2025-06-08',
            'end_date'   => '2025-06-10',
            'location'   => 'Royal Snooker Hall, London, UK',
            'game_types' => 'Snooker',
            'status'     => 'Upcoming',
            'slug'       => 'snooker-battle-pros',
            'id'         => 3,
        ],
    ]);

    $currentEvents  = $currentEvents ?? collect([
        (object)[
            'name'       => 'Masters of the Cue: National Billiards Championship 2025',
            'image_url'  => null,
            'start_date' => '2025-03-15',
            'end_date'   => '2025-03-17',
            'location'   => 'Grand Arena, Los Angeles, CA',
            'game_types' => '9-Ball, 8-Ball',
            'status'     => 'Ongoing',
            'slug'       => 'masters-of-the-cue-2025',
            'id'         => 4,
        ],
        (object)[
            'name'       => 'The Grand 8-Ball Open Tournament',
            'image_url'  => null,
            'start_date' => '2025-04-20',
            'end_date'   => '2025-04-22',
            'location'   => 'Empire Billiards Club, New York, NY',
            'game_types' => '8-Ball',
            'status'     => 'Ongoing',
            'slug'       => 'grand-8ball-open',
            'id'         => 5,
        ],
        (object)[
            'name'       => 'International Snooker Challenge: Battle of the Pros',
            'image_url'  => null,
            'start_date' => '2025-06-08',
            'end_date'   => '2025-06-10',
            'location'   => 'Royal Snooker Hall, London, UK',
            'game_types' => 'Snooker',
            'status'     => 'Ongoing',
            'slug'       => 'snooker-battle-pros',
            'id'         => 6,
        ],
        // baris kedua (dummy agar tampilan seperti contoh)
        (object)[
            'name'       => 'Masters of the Cue: National Billiards Championship 2025',
            'image_url'  => null,
            'start_date' => '2025-03-15',
            'end_date'   => '2025-03-17',
            'location'   => 'Grand Arena, Los Angeles, CA',
            'game_types' => '9-Ball, 8-Ball',
            'status'     => 'Ongoing',
            'slug'       => 'masters-of-the-cue-2025-2',
            'id'         => 7,
        ],
        (object)[
            'name'       => 'The Grand 8-Ball Open Tournament',
            'image_url'  => null,
            'start_date' => '2025-04-20',
            'end_date'   => '2025-04-22',
            'location'   => 'Empire Billiards Club, New York, NY',
            'game_types' => '8-Ball',
            'status'     => 'Ongoing',
            'slug'       => 'grand-8ball-open-2',
            'id'         => 8,
        ],
        (object)[
            'name'       => 'International Snooker Challenge: Battle of the Pros',
            'image_url'  => null,
            'start_date' => '2025-06-08',
            'end_date'   => '2025-06-10',
            'location'   => 'Royal Snooker Hall, London, UK',
            'game_types' => 'Snooker',
            'status'     => 'Ongoing',
            'slug'       => 'snooker-battle-pros-2',
            'id'         => 9,
        ],
    ]);
@endphp

<main class="min-h-screen bg-neutral-900 text-white">

    {{-- =============== HERO BANNER (dikembalikan) =============== --}}
    <section class="relative isolate">
        <div class="relative overflow-hidden vh-section">
            <img
                src="{{ asset('images/event/bg-event-1.png') }}"
                alt="Events Hero"
                class="hero-img absolute inset-0 h-full w-full object-cover object-center"
                loading="eager"
                decoding="async"
                onerror="this.src='https://via.placeholder.com/1600x800?text=Events+Banner'"/>

            {{-- Overlay gradasi agar teks terbaca --}}
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-transparent"></div>
            </div>

            <div class="relative z-10 h-full flex items-center">
                <div class="w-full max-w-7xl mx-auto px-6 md:px-20">
                    <p class="text-sm md:text-base text-white/80 mb-2">
                        <a href="{{ route('index') }}" class="hover:underline">Home</a> / Event
                    </p>
                    <h1 class="hero-title text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold uppercase leading-tight">
                        FIND YOUR NEXT CHALLENGE HERE
                    </h1>
                </div>
            </div>
        </div>
    </section>

    {{-- ====================== UPCOMING ====================== --}}
    <section class="max-w-7xl mx-auto px-4 md:px-12 lg:px-16 py-10">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-extrabold">Upcoming Event</h2>
            <a href="{{ route('events.index', ['status' => 'upcoming']) }}" class="text-sm text-gray-300 hover:text-white">view all</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            @forelse ($upcomingEvents as $ev)
                @php
                    $img = $resolveImage($ev->image_url);
                    $range = $dateRange($ev->start_date ?? null, $ev->end_date ?? null);
                @endphp
                <a href="{{ route('events.show', $ev->id ?? $ev) }}" class="group">
                    <div class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition">
                        <div class="relative h-40 md:h-44 bg-neutral-700">
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $ev->name }}" class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" decoding="async"
                                     onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            @endif
                            {{-- Placeholder icon --}}
                            <div class="absolute inset-0 hidden md:grid place-items-center text-gray-400">
                                <i class="far fa-image text-3xl"></i>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-semibold text-[15px] leading-snug line-clamp-2">{{ $ev->name }}</h3>

                            <hr class="my-3 border-gray-700">

                            <ul class="space-y-2 text-[13px] text-gray-300">
                                <li class="flex items-center gap-2">
                                    <i class="far fa-clock w-4 text-gray-400"></i>
                                    <span>{{ $range }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt w-4 text-gray-400"></i>
                                    <span class="line-clamp-1">{{ $ev->location }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fas fa-gamepad w-4 text-gray-400"></i>
                                    <span>{{ $ev->game_types }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-gray-400 py-10 text-center">No upcoming events.</div>
            @endforelse
        </div>
    </section>

    {{-- ====================== CURRENT TOURNAMENTS ====================== --}}
    <section class="max-w-7xl mx-auto px-4 md:px-12 lg:px-16 pb-14">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-extrabold">Current Tournaments</h2>
            <a href="{{ route('events.index', ['status' => 'ongoing']) }}" class="text-sm text-gray-300 hover:text-white">view all</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            @forelse ($currentEvents as $ev)
                @php
                    $img = $resolveImage($ev->image_url);
                    $range = $dateRange($ev->start_date ?? null, $ev->end_date ?? null);
                @endphp
                <a href="{{ route('events.show', $ev->id ?? $ev) }}" class="group">
                    <div class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition">
                        <div class="relative h-40 md:h-44 bg-neutral-700">
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $ev->name }}" class="absolute inset-0 w-full h-full object-cover"
                                     loading="lazy" decoding="async"
                                     onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            @endif
                            {{-- Placeholder icon --}}
                            <div class="absolute inset-0 hidden md:grid place-items-center text-gray-400">
                                <i class="far fa-image text-3xl"></i>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-semibold text-[15px] leading-snug line-clamp-2">{{ $ev->name }}</h3>

                            <hr class="my-3 border-gray-700">

                            <ul class="space-y-2 text-[13px] text-gray-300">
                                <li class="flex items-center gap-2">
                                    <i class="far fa-clock w-4 text-gray-400"></i>
                                    <span>{{ $range }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt w-4 text-gray-400"></i>
                                    <span class="line-clamp-1">{{ $ev->location }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fas fa-gamepad w-4 text-gray-400"></i>
                                    <span>{{ $ev->game_types }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-gray-400 py-10 text-center">No current tournaments.</div>
            @endforelse
        </div>
    </section>

</main>
@endsection
