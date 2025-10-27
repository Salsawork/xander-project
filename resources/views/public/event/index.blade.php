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
        .grid-equal { grid-auto-rows: 1fr; }
        .ev-card {
            background: #1f1f1f;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 14px;
            overflow: hidden;
            transition: .25s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .ev-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0, 0, 0, .35); }

        .ev-thumb { background: #2b2b2b; height: 11rem; }
        @media (min-width: 768px){ .ev-thumb { height: 11rem; } }
        .ev-body { flex: 1; display: flex; flex-direction: column; padding: 1rem; }
        .ev-title { font-weight: 600; font-size: 15px; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        .ev-info { margin-top: auto; }
        .ev-rule { border-color: #374151; }

        /* =======================
           IMAGE LOADING OVERLAY
           ======================= */
        .img-wrapper{ position: relative; background:#141414; width:100%; height:100%; overflow:hidden; }
        .img-wrapper img{
            width:100%; height:100%; object-fit:cover; display:block;
            opacity:0; transition:opacity .28s ease;
        }
        .img-wrapper img.loaded{ opacity:1; }
        .img-loading{
            position:absolute; inset:0; display:flex; flex-direction:column;
            align-items:center; justify-content:center; gap:10px;
            background:#151515; color:#9ca3af; z-index:1;
        }
        .img-loading.hidden{ display:none; }
        .spinner{
            width:34px; height:34px; border:3px solid rgba(130,130,130,.25);
            border-top-color:#9ca3af; border-radius:50%; animation:spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .camera-icon{ width:28px; height:28px; opacity:.6; }

        .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
    </style>
@endpush

@section('content')
    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;

        $EVENT_IMG_DIR = 'images/events/';

        $normalizeToEventsDir = function (?string $u) use ($EVENT_IMG_DIR) {
            if (!$u) return null;
            $u = trim($u);
            if (Str::startsWith($u, ['http://','https://'])) {
                return $u;
            }
            $basename = basename($u);
            return asset($EVENT_IMG_DIR . $basename);
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

        $buildEventImageCandidates = function ($e) use ($EVENT_IMG_DIR, $normalizeToEventsDir) {
            $cand = [];
            if (!empty($e->image_url)) {
                $cand[] = $normalizeToEventsDir($e->image_url);
            }
            $id = $e->id ?? 'unknown';
            $cand[] = asset($EVENT_IMG_DIR . $id . '.webp');
            $cand[] = asset($EVENT_IMG_DIR . $id . '.jpg');
            $cand[] = asset($EVENT_IMG_DIR . $id . '.png');
            // last resort FE fallback (optional)
            $cand[] = asset('images/community/community-1.png');
            // and a remote placeholder
            $cand[] = 'https://placehold.co/1200x800?text=Event';
            return array_values(array_unique(array_filter($cand)));
        };
    @endphp

    <main class="min-h-screen bg-neutral-900 text-white">

        <!-- HERO -->
        <section class="relative isolate">
            <div class="relative overflow-hidden vh-section">
                <div class="absolute inset-0">
                    <div class="img-wrapper absolute inset-0">
                        <div class="img-loading">
                            <div class="spinner" aria-hidden="true"></div>
                            <div class="sr-only">Loading banner...</div>
                        </div>
                        <img
                            class="hero-img"
                            data-lazy-img
                            data-src-candidates='@json([asset('images/event/bg-event-1.png'), asset('images/community/community-1.png'), 'https://placehold.co/1600x800?text=Events+Banner'])'
                            src="{{ asset('images/event/bg-event-1.png') }}"
                            alt="Events Hero"
                            loading="eager"
                            decoding="async"
                            style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover; object-position:center;"
                        />
                    </div>
                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/55 to-transparent"></div>
                    </div>
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
                <h2 class="text-2xl font-extrabold">Upcoming Events</h2>
                <a href="{{ route('events.list', ['status' => 'upcoming']) }}" class="text-sm text-gray-300 hover:text-white">view all</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 grid-equal">
                @forelse (($upcomingEvents ?? collect()) as $ev)
                    @php
                        $srcs  = $buildEventImageCandidates($ev);
                        $range = $dateRange($ev->start_date ?? null, $ev->end_date ?? null);
                        $link  = $eventDetailUrl($ev);
                    @endphp

                    <a href="{{ $link }}" class="group h-full">
                        <div class="ev-card">
                            <div class="ev-thumb relative">
                                @if (!empty($srcs))
                                    <div class="img-wrapper w-full h-full">
                                        <div class="img-loading">
                                            <div class="spinner" aria-hidden="true"></div>
                                            <div class="sr-only">Loading image...</div>
                                        </div>
                                        <img
                                            class="js-img-fallback"
                                            data-srcs='@json($srcs)'
                                            data-idx="0"
                                            data-lazy-img
                                            src="{{ $srcs[0] }}"
                                            alt="{{ $ev->name }}"
                                            loading="lazy"
                                            decoding="async">
                                    </div>
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

        <!-- ONGOING -->
        <section class="max-w-7xl mx-auto px-4 md:px-12 lg:px-16 pb-14">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-extrabold">Ongoing Tournaments</h2>
                <a href="{{ route('events.list', ['status' => 'ongoing']) }}" class="text-sm text-gray-300 hover:text-white">view all</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 grid-equal">
                @forelse (($ongoingEvents ?? collect()) as $ev)
                    @php
                        $srcs  = $buildEventImageCandidates($ev);
                        $range = $dateRange($ev->start_date ?? null, $ev->end_date ?? null);
                        $link  = $eventDetailUrl($ev);
                    @endphp

                    <a href="{{ $link }}" class="group h-full">
                        <div class="ev-card">
                            <div class="ev-thumb relative">
                                @if (!empty($srcs))
                                    <div class="img-wrapper w-full h-full">
                                        <div class="img-loading">
                                            <div class="spinner" aria-hidden="true"></div>
                                            <div class="sr-only">Loading image...</div>
                                        </div>
                                        <img
                                            class="js-img-fallback"
                                            data-srcs='@json($srcs)'
                                            data-idx="0"
                                            data-lazy-img
                                            src="{{ $srcs[0] }}"
                                            alt="{{ $ev->name }}"
                                            loading="lazy"
                                            decoding="async">
                                    </div>
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
                    <div class="col-span-full text-gray-400 py-10 text-center">No ongoing tournaments.</div>
                @endforelse
            </div>
        </section>

    </main>

    {{-- Unified lazy image loader + chained fallback + camera icon --}}
    <script>
        (function () {
            function showCameraFallback(loaderEl) {
                if (!loaderEl) return;
                loaderEl.classList.remove('hidden');
                loaderEl.innerHTML = `
                  <svg class="camera-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M9 2a1 1 0 0 0-.894.553L7.382 4H5a3 3 0 0 0-3 3v9a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3h-2.382l-.724-1.447A1 1 0 0 0 14 2H9zm3 6a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                  </svg>
                  <div class="text-xs text-gray-400">No image available</div>
                `;
            }

            function initLazyImage(img) {
                if (!img) return;

                const wrap   = img.closest('.img-wrapper');
                const loader = wrap ? wrap.querySelector('.img-loading') : null;

                const onLoad = () => {
                    img.classList.add('loaded');
                    if (loader) loader.classList.add('hidden');
                };
                img.addEventListener('load', onLoad, { passive: true });

                let list = [];
                try { list = JSON.parse(img.getAttribute('data-src-candidates') || img.getAttribute('data-srcs') || '[]') || []; }
                catch(e){ list = []; }

                let idx = parseInt(img.getAttribute('data-idx') || '0', 10);
                if (isNaN(idx)) idx = 0;

                const onError = () => {
                    idx++;
                    if (idx < list.length) {
                        img.setAttribute('data-idx', String(idx));
                        img.src = list[idx];
                    } else {
                        // Exhausted all candidates
                        showCameraFallback(loader);
                    }
                };
                img.addEventListener('error', onError, { passive: true });

                // Cached image quick path
                if (img.complete && img.naturalWidth > 0) onLoad();
            }

            document.addEventListener('DOMContentLoaded', function(){
                document.querySelectorAll('img[data-lazy-img]').forEach(initLazyImage);
            });
        })();
    </script>
@endsection
