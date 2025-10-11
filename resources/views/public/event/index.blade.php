@extends('app')
@section('title', 'Events - Xander Billiard')

@push('styles')
    <style>
        :root {
            color-scheme: dark;
            --hero-mobile-min: 300px;
            --hero-mobile-ideal: 56svh;
            --hero-mobile-max: 480px;
        }

        html, body {
            height: 100%;
            background: #0a0a0a;
            overscroll-behavior-y: none;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: #0a0a0a;
            pointer-events: none;
            z-index: -1;
        }

        #app, main { background: #0a0a0a; }

        .vh-section { height: clamp(540px, 84svh, 960px); }
        @media (min-width:768px){ .vh-section { height: clamp(660px, 88svh, 1020px); } }
        @media (min-width:1280px){ .vh-section { height: clamp(740px, 92svh, 1100px); } }
        @media (max-width:767.98px){
            .vh-section { height: clamp(var(--hero-mobile-min), var(--hero-mobile-ideal), var(--hero-mobile-max)) !important; }
            .hero-img { object-position: 60% center !important; }
            .hero-title { font-size: clamp(1.25rem, 6.8vw, 1.9rem) !important; line-height: 1.2 !important; }
        }

        /* ==== CARD & GRID EQUAL HEIGHT ==== */
        .grid-equal { grid-auto-rows: 1fr; }              /* semua item pada baris sama tinggi */
        .ev-card {
            background: #1f1f1f;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 14px;
            overflow: hidden;
            transition: .25s ease;
            display: flex;                 /* kolom fleksibel */
            flex-direction: column;
            height: 100%;                  /* penuhi tinggi cell grid */
        }
        .ev-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0, 0, 0, .35); }

        .ev-thumb { background: #2b2b2b; height: 11rem; }  /* h-44 default */
        @media (min-width: 768px){ .ev-thumb { height: 11rem; } }
        .ev-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .ev-body { flex: 1; display: flex; flex-direction: column; padding: 1rem; }
        .ev-title { font-weight: 600; font-size: 15px; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        .ev-info { margin-top: auto; }     /* dorong ke bawah agar rata */
        .ev-rule { border-color: #374151; }/* border-gray-700 */
    </style>
@endpush

@section('content')
    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;

        $resolveImage = function ($path) {
            if (empty($path)) return null;
            return Str::startsWith($path, ['http://', 'https://', '/']) ? $path : asset($path);
        };

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

        $eventDetailUrl = function ($e) {
            return route('events.show', ['event' => $e->id, 'name' => Str::slug($e->name)]);
        };
    @endphp

    <main class="min-h-screen bg-neutral-900 text-white">

        <!-- HERO -->
        <section class="relative isolate">
            <div class="relative overflow-hidden vh-section">
                <img src="{{ asset('images/event/bg-event-1.png') }}" alt="Events Hero"
                     class="hero-img absolute inset-0 h-full w-full object-cover object-center"
                     loading="eager" decoding="async"
                     onerror="this.src='https://via.placeholder.com/1600x800?text=Events+Banner'"/>
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

        <!-- UPCOMING -->
        <section class="max-w-7xl mx-auto px-4 md:px-12 lg:px-16 py-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-extrabold">Upcoming Event</h2>
                <a href="{{ route('events.list', ['status' => 'upcoming']) }}" class="text-sm text-gray-300 hover:text-white">view all</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 grid-equal">
                @forelse (($upcomingEvents ?? collect()) as $ev)
                    @php
                        $img   = $resolveImage($ev->image_url ?? null);
                        $range = $dateRange($ev->start_date ?? null, $ev->end_date ?? null);
                        $link  = $eventDetailUrl($ev);
                    @endphp

                    <a href="{{ $link }}" class="group h-full">
                        <div class="ev-card">
                            <div class="ev-thumb relative">
                                @if ($img)
                                  <img src="{{ $img }}" alt="{{ $ev->name }}" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                @else
                                  <div class="w-full h-full grid place-items-center text-gray-400">
                                    <i class="far fa-image text-3xl"></i>
                                  </div>
                                @endif
                            </div>

                            <div class="ev-body">
                                <h3 class="ev-title">{{ $ev->name }}</h3>

                                <div class="ev-info">
                                    <hr class="my-3 ev-rule">
                                    <ul class="space-y-2 text-[13px] text-gray-300">
                                        <li class="flex items-center gap-2">
                                            <i class="far fa-clock w-4 text-gray-400"></i><span>{{ $range }}</span>
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
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-gray-400 py-10 text-center">No upcoming events.</div>
                @endforelse
            </div>
        </section>

        <!-- CURRENT -->
        <section class="max-w-7xl mx-auto px-4 md:px-12 lg:px-16 pb-14">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-extrabold">Current Tournaments</h2>
                <a href="{{ route('events.list', ['status' => 'ongoing']) }}" class="text-sm text-gray-300 hover:text-white">view all</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 grid-equal">
                @forelse (($currentEvents ?? collect()) as $ev)
                    @php
                        $img   = $resolveImage($ev->image_url ?? null);
                        $range = $dateRange($ev->start_date ?? null, $ev->end_date ?? null);
                        $link  = $eventDetailUrl($ev);
                    @endphp

                    <a href="{{ $link }}" class="group h-full">
                        <div class="ev-card">
                            <div class="ev-thumb relative">
                                @if ($img)
                                  <img src="{{ $img }}" alt="{{ $ev->name }}" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                @else
                                  <div class="w-full h-full grid place-items-center text-gray-400">
                                      <i class="far fa-image text-3xl"></i>
                                  </div>
                                @endif
                            </div>

                            <div class="ev-body">
                                <h3 class="ev-title">{{ $ev->name }}</h3>

                                <div class="ev-info">
                                    <hr class="my-3 ev-rule">
                                    <ul class="space-y-2 text-[13px] text-gray-300">
                                        <li class="flex items-center gap-2">
                                            <i class="far fa-clock w-4 text-gray-400"></i><span>{{ $range }}</span>
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
                        </div>
                    </a>
                @empty
                    <div class="col-span-full text-gray-400 py-10 text-center">No current tournaments.</div>
                @endforelse
            </div>
        </section>

    </main>
@endsection
