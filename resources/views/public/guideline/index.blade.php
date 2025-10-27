@extends('app')
@section('title', 'Guideline - Xander Billiard')

@section('content')
    {{-- ================= Anti white flash / rubber-band iOS ================= --}}
    <div id="antiBounceBg" aria-hidden="true"></div>

    {{-- Anti white flash + styles --}}
    <style>
        :root { color-scheme: dark; }

        /* Pastikan SEMUA root gelap */
        :root, html, body { background-color: #0a0a0a; }
        html, body { height: 100%; }

        /* Nonaktifkan overscroll glow/bounce menembus body */
        html, body {
            overscroll-behavior-y: none;
            overscroll-behavior-x: none;
            touch-action: pan-y;
            -webkit-text-size-adjust: 100%;
        }

        /* Elemen kanvas gelap "di belakang segalanya" */
        #antiBounceBg{
            position: fixed;
            left: 0; right: 0;
            top: -120svh;
            bottom: -120svh;
            background: #0a0a0a;
            z-index: -1;
            pointer-events: none;
        }

        /* Jika ada wrapper utama dari layout, pastikan gelap juga */
        #app, main { background:#0a0a0a; }

        /* ====== sisa CSS punyamu (dipertahankan) ====== */
        .badge-pill {
            position: absolute; z-index: 10; display: inline-flex; align-items: center; justify-content: center;
            padding: .45rem .95rem; border-radius: 9999px; font-weight: 700; font-size: 13.5px; line-height: 1; letter-spacing: .2px;
            color: #fff; background: var(--bg, #444); border: 1px solid rgba(255, 255, 255, .18); box-shadow: 0 10px 25px rgba(0, 0, 0, .35);
        }
        @media (min-width:768px) {
            .badge-pill { font-size: 14px; padding: .55rem 1.1rem; }
        }
        .badge-blue { --bg: #2E90FF; }
        .badge-green { --bg: #22c55e; }
        .badge-yellow { --bg: #FDB022; }
        .badge-red { --bg: #F05252; }
        .badge-gray { --bg: #6B7280; }

        .title-2lines { min-height: 3.25rem; }
        @media (min-width:768px) { .title-2lines { min-height: 3.75rem; } }

        .input-dark {
            background: #1f2937; color: #fff; border: 1px solid rgba(255, 255, 255, .12);
            border-radius: .65rem; padding: .6rem .8rem; outline: none;
        }
        .input-dark:focus { border-color: rgba(255, 255, 255, .25); box-shadow: 0 0 0 3px rgba(255, 255, 255, .08); }
        .select-dark {
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, #aaa 50%), linear-gradient(135deg, #aaa 50%, transparent 50%);
            background-position: calc(100% - 18px) calc(1.1em), calc(100% - 13px) calc(1.1em);
            background-size: 6px 6px, 6px 6px; background-repeat: no-repeat; padding-right: 2.25rem;
        }
        .auto-rows-fr { grid-auto-rows: 1fr; }

        /* ===== MOBILE FILTER MODAL ===== */
        @media (max-width: 767px) {
            .filter-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 50; display: none; }
            .filter-modal {
                position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%);
                background: #18181b; border-radius: 1.25rem; box-shadow: 0 2px 32px rgba(0,0,0,0.18); z-index: 51;
                width: 92vw; max-width: 420px; padding: 1.5rem 1.25rem 1.25rem 1.25rem; display: none;
            }
            .filter-modal.active, .filter-modal-overlay.active { display: block; }
            .filter-modal .modal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; }
            .filter-modal .modal-header h3 { font-size: 1.15rem; font-weight: 700; color: #fff; }
            .filter-modal .close-btn { background: none; border: none; color: #fff; font-size: 1.5rem; line-height: 1; cursor: pointer; padding: 0 0.25rem; }
            .filter-modal label span { font-size: 0.95rem; margin-bottom: 0.25rem; }
            .filter-modal .input-dark, .filter-modal .select-dark { font-size: 1rem; padding: 0.7rem 1rem; border-radius: 0.85rem; }
            .filter-modal .grid { grid-template-columns: 1fr !important; gap: 1.25rem !important; }
            .filter-modal .mt-3 { margin-top: 1rem !important; font-size: 0.95rem; }
            #filterForm { display: none !important; }           /* Hide desktop filter form */
            .filter-trigger-btn { display: flex !important; }   /* Show filter button */
        }
        @media (min-width: 768px) {
            .filter-modal, .filter-modal-overlay, .filter-trigger-btn { display: none !Important; }
            #filterForm { display: block !important; }
        }
        .filter-trigger-btn{
            display:none; width:100%; margin:0 auto 1.5rem auto; background:#18181b; color:#fff; border:1px solid #444; border-radius:1rem;
            font-size:1.15rem; font-weight:600; padding:.85rem 0; justify-content:center; align-items:center; gap:.5rem; cursor:pointer; transition:background .18s;
        }
        .filter-trigger-btn:hover{ background:#23232a; }
        .filter-trigger-btn svg{ width:1.25em; height:1.25em; margin-right:.25em; }

        /* ====== MOBILE CATEGORY SWIPER ====== */
        @media (max-width: 767.98px){
            .cat-swiper { padding: .25rem .25rem 2rem .25rem; }
        }
        .cat-pagination .swiper-pagination-bullet{ background:#e5e7eb; opacity:.7; }
        .cat-pagination .swiper-pagination-bullet-active{ background:#2563eb; opacity:1; }

        /* ====== Progressive Image Loader untuk KARTU (bukan hero) ====== */
        .img-frame{ position:relative; background:#111827; }
        .img-el{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .25s ease; }
        .img-frame.loaded .img-el{ opacity:1; }
        .img-frame .spinner, .img-frame .ph{
            position:absolute; inset:0; display:grid; place-items:center; pointer-events:none;
        }
        .img-frame .ph{ display:none; color:#9ca3af; }
        .img-frame.loaded .spinner{ display:none; }
        .img-frame.error .spinner{ display:none; }
        .img-frame.error .ph{ display:grid; }
        .spinner svg{ animation:spin 1s linear infinite; opacity:.9; }
        @keyframes spin{ from{ transform:rotate(0deg); } to{ transform:rotate(360deg); } }
    </style>

    {{-- Swiper CSS (muat jika belum ada) --}}
    <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />

    <div class="bg-neutral-900 text-white min-h-dvh overflow-x-hidden">
        @php
            use Illuminate\Support\Str;
            use Illuminate\Support\Facades\Storage;
            $resolveImage = function (?string $rawPath, string $default = '/images/guidelines/placeholder.jpg') {
                if (!empty($rawPath)) {
                    $path = $rawPath;
                    if (Str::startsWith($path, 'guidelines/')) return Storage::url($path);
                    if (file_exists(public_path($path))) return asset($path);
                    $basename = basename($path);
                    if (file_exists(public_path('images/guidelines/' . $basename))) return asset('images/guidelines/' . $basename);
                }
                return asset($default);
            };
            $displayNames = ['BEGINNER'=>'Beginner','INTERMEDIATE'=>'Intermediate','MASTER'=>'Master','GENERAL'=>'General'];
            $pillClassMap = ['BEGINNER'=>'badge-green','INTERMEDIATE'=>'badge-yellow','MASTER'=>'badge-red','GENERAL'=>'badge-gray'];
            $activeCategory = strtoupper(request('category', 'ALL'));
            $q = trim(request('q', ''));
            $sort = request('sort', 'newest');
            $latestGuideline = ($guidelines ?? collect())->first();
        @endphp

        {{-- ======================= HERO ======================= --}}
        @php
            $staticHero = collect([
                (object)['featured_image'=>'images/guidelines/guideline-1.png','title'=>$latestGuideline->title ?? 'Guideline','description'=>$latestGuideline->description ?? '','published_at'=>$latestGuideline->published_at ?? null,'slug'=>$latestGuideline->slug ?? null],
                (object)['featured_image'=>'images/guidelines/guideline-2.png','title'=>$latestGuideline->title ?? 'Guideline','description'=>$latestGuideline->description ?? '','published_at'=>$latestGuideline->published_at ?? null,'slug'=>$latestGuideline->slug ?? null],
            ])->filter(fn($s)=>file_exists(public_path($s->featured_image)));
            $heroItems = $staticHero->isNotEmpty() ? $staticHero : collect($guidelines ?? [])->take(5);
        @endphp

        @if ($heroItems->count() > 0)
            <style>
                .vh-section { height: clamp(540px, 84svh, 960px); }
                @media (min-width:768px){ .vh-section { height: clamp(660px, 88svh, 1020px); } }
                @media (min-width:1280px){ .vh-section { height: clamp(740px, 92svh, 1100px); } }
                :root{ --hero-mobile-min: 320px; --hero-mobile-ideal: 58svh; --hero-mobile-max: 520px; }
                @media (max-width: 767.98px) {
                    .vh-section { height: clamp(var(--hero-mobile-min), var(--hero-mobile-ideal), var(--hero-mobile-max)) !important; }
                    .hero-img { object-position: 60% center !important; }
                    .hero-title { font-size: clamp(1.4rem, 7.5vw, 2.2rem) !important; line-height: 1.15 !important; margin-bottom: .6rem !important; }
                    .hero-text { font-size: clamp(.9rem, 3.5vw, 1rem) !important; }
                    .hero-prev, .hero-next { width: 2.25rem !important; height: 2.25rem !important; }
                    .hero-pagination { bottom: .5rem !important; }
                    .hero-pagination .swiper-pagination-bullet { width: 6px; height: 6px; }
                }
            </style>
            <section class="relative">
                <div class="swiper hero-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($heroItems as $i => $g)
                            @php
                                $img = $resolveImage($g->featured_image ?? null, '/images/hero/guideline-main.jpg');
                                $title = $g->title ?? 'Guideline';
                                $desc = $g->description ?? '';
                                $date = optional($g->published_at)->format('d F Y');
                            @endphp
                            <div class="swiper-slide">
                                {{-- Pakai inline background-image sebagai fallback agar banner tetap terlihat --}}
                                <div class="relative isolate w-full overflow-hidden bg-neutral-900 vh-section"
                                     style="background-image:url('{{ $img }}'); background-size:cover; background-position:78% center;">
                                    {{-- IMG langsung dengan src (bukan progressive) agar tidak hilang --}}
                                    <img src="{{ $img }}"
                                         alt="{{ $title }}"
                                         class="hero-img absolute inset-0 h-full w-full object-cover object-[78%_center]"
                                         loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                                         @if($i===0) fetchpriority="high" @endif
                                         decoding="async" sizes="100vw" />
                                    <div class="absolute inset-0 pointer-events-none z-[1]">
                                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent"></div>
                                    </div>
                                    <div class="absolute inset-0 z-10 flex items-center">
                                        <div class="mx-auto w-full max-w-7xl px-6 md:px-20">
                                            <div class="max-w-xl md:max-w-2xl text-white">
                                                @if ($date)
                                                    <p class="hero-text font-semibold mb-3 text-white/90 text-sm md:text-base lg:text-lg">{{ $date }}</p>
                                                @endif
                                                <h1 class="hero-title text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] mb-5">{{ $title }}</h1>
                                                @if ($desc)
                                                    <p class="hero-text text-base md:text-lg text-white/85 leading-relaxed mb-8 max-w-prose">
                                                        {{ Str::limit(strip_tags($desc), 260) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="hero-prev absolute left-4 md:left-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 md:w-12 md:h-12 grid place-items-center rounded-full bg-white/20 hover:bg-white/30 transition" aria-label="Slide sebelumnya" type="button">‹</button>
                    <button class="hero-next absolute right-4 md:right-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 md:w-12 md:h-12 grid place-items-center rounded-full bg-white/20 hover:bg-white/30 transition" aria-label="Slide berikutnya" type="button">›</button>
                    <div class="hero-pagination absolute bottom-4 md:bottom-6 left-0 right-0 z-30"></div>
                </div>
            </section>
        @endif

        {{-- ======================= FILTER & SORT (dirapikan sejajar headings) ======================= --}}
        <section class="px-6 lg:px-24 pt-7">
            <!-- Mobile filter button -->
            <button type="button" class="filter-trigger-btn" id="openFilterModal" style="font-size:1rem; padding:0.65rem 0; margin-top:2.5rem;">
                <svg viewBox="0 0 20 20" fill="currentColor" style="width:1em;height:1em;margin-right:0.18em;">
                    <path d="M2.5 5a1 1 0 0 1 1-1h13a1 1 0 0 1 .8 1.6l-4.2 5.6V16a1 1 0 0 1-1.5.87l-3-2A1 1 0 0 1 8 14v-2.8L3.2 6.6A1 1 0 0 1 2.5 5z"/>
                </svg>
                <span style="font-size:1.08rem;">Filter &amp; Search</span>
            </button>

            <!-- Desktop filter form -->
            <form id="filterForm" class="bg-neutral-800/60 ring-1 ring-white/10 rounded-xl p-3 md:p-4" onsubmit="return false;">
                <!-- Row disusun sama seperti heading kategori: kiri & kanan -->
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
                    <!-- Kiri: Category + Search -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:gap-3 flex-1">
                        <label class="block">
                            <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Category</span>
                            @php
                                $catOptions = ['ALL'=>'All','BEGINNER'=>'Beginner','INTERMEDIATE'=>'Intermediate','MASTER'=>'Master','GENERAL'=>'General'];
                            @endphp
                            <select id="category" class="input-dark select-dark w-full" style="font-size:0.98rem; padding:0.5rem 0.7rem;">
                                @foreach ($catOptions as $val => $label)
                                    <option value="{{ $val }}" @selected($activeCategory === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Search</span>
                            <input id="q" type="search" value="{{ $q }}" placeholder="Search title or description…" class="input-dark w-full" style="font-size:0.98rem; padding:0.5rem 0.7rem;">
                        </label>
                    </div>

                    <!-- Kanan: Sort -->
                    <div class="md:w-auto md:min-w-[220px]">
                        <label class="block w-full">
                            <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Sort</span>
                            <select id="sort" class="input-dark select-dark w-full md:w-[220px]" style="font-size:0.98rem; padding:0.5rem 0.7rem;">
                                <option value="newest" @selected($sort === 'newest')>Newest</option>
                                <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                                <option value="title-asc" @selected($sort === 'title-asc')>Title A–Z</option>
                                <option value="title-desc" @selected($sort === 'title-desc')>Title Z–A</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="mt-2 text-sm text-white/70 md:text-right">
                    Showing <strong id="resultCount">0</strong> results.
                </div>
            </form>

            <!-- Mobile filter modal -->
            <div class="filter-modal-overlay" id="filterModalOverlay"></div>
            <div class="filter-modal" id="filterModal" style="max-width:340px; padding:1rem 0.7rem 0.7rem 0.7rem;">
                <div class="modal-header" style="margin-bottom:0.8rem;">
                    <h3 style="font-size:1rem;">Filter &amp; Search</h3>
                    <button type="button" class="close-btn" id="closeFilterModal" aria-label="Close" style="font-size:1.2rem;">&times;</button>
                </div>
                <form id="filterFormMobile" onsubmit="return false;">
                    <div class="grid grid-cols-1 gap-2">
                        <label class="block">
                            <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Category</span>
                            @foreach ($catOptions as $val => $label) @endforeach
                            <select id="categoryMobile" class="input-dark select-dark w-full" style="font-size:0.98rem; padding:0.5rem 0.7rem;">
                                @foreach ($catOptions as $val => $label)
                                    <option value="{{ $val }}" @selected($activeCategory === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Search</span>
                            <input id="qMobile" type="search" value="{{ $q }}" placeholder="Search title or description…" class="input-dark w-full" style="font-size:0.98rem; padding:0.5rem 0.7rem;">
                        </label>
                        <label class="block">
                            <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Sort</span>
                            <select id="sortMobile" class="input-dark select-dark w-full" style="font-size:0.98rem; padding:0.5rem 0.7rem;">
                                <option value="newest" @selected($sort === 'newest')>Newest</option>
                                <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                                <option value="title-asc" @selected($sort === 'title-asc')>Title A–Z</option>
                                <option value="title-desc" @selected($sort === 'title-desc')>Title Z–A</option>
                            </select>
                        </label>
                    </div>
                    <div class="mt-2 text-sm text-white/70">
                        Showing <strong id="resultCountMobile">0</strong> results.
                    </div>
                </form>
            </div>
        </section>

        {{-- ======================= SECTION PER KATEGORI ======================= --}}
        @foreach (['BEGINNER', 'INTERMEDIATE', 'MASTER', 'GENERAL'] as $category)
            @php $display = $displayNames[$category] ?? $category; @endphp
            <section id="section-{{ $category }}" class="px-6 lg:px-24 py-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold uppercase tracking-wide">{{ $display }}</h2>
                    <a href="{{ route('guideline.category', ['category' => Str::slug($display, '-')]) }}"
                       class="text-sm text-gray-400 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-300 rounded px-2 py-1"
                       aria-label="View all {{ $display }} guidelines">
                        view all
                    </a>
                </div>

                {{-- MOBILE: slider --}}
                <div class="md:hidden">
                    <div class="swiper cat-swiper" id="swiper-{{ $category }}">
                        <div class="swiper-wrapper" id="slides-{{ $category }}"></div>
                        <div class="swiper-pagination cat-pagination" id="pag-{{ $category }}"></div>
                    </div>
                </div>

                {{-- DESKTOP: grid --}}
                <div id="grid-{{ $category }}" class="hidden md:grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 auto-rows-fr gap-4 md:gap-6"></div>

                <div id="empty-{{ $category }}" class="hidden text-center text-white/60 py-8">No matching items in {{ $display }}.</div>
            </section>
        @endforeach
    </div>

    {{-- ======================= DATASET JS ======================= --}}
    @php
        $clientItems = collect($guidelines ?? [])
            ->map(function ($g) use ($resolveImage) {
                $published = optional($g->published_at);
                return [
                    'title' => $g->title ?? '',
                    'description' => strip_tags($g->description ?? ''),
                    'category' => strtoupper($g->category ?? 'GENERAL'),
                    'image' => $resolveImage($g->featured_image, '/images/guidelines/placeholder.jpg'),
                    'dateTs' => $published ? $published->timestamp : 0,
                    'dateText' => $published ? $published->format('F j, Y') : '',
                    'isNew' => (bool) (($g->is_new ?? false) || ($published && $published->gt(now()->subDays(14)))),
                    'href' => route('guideline.show', ['slug' => $g->slug]),
                ];
            })
            ->values();
    @endphp

    <script>
        /* Stabilkan unit tinggi viewport di mobile (fix toolbar resize) */
        (function(){
            function setSVH() {
                const svh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--svh', svh + 'px');
            }
            setSVH();
            window.addEventListener('resize', setSVH);
        })();

        window.G_ITEMS   = @json($clientItems);
        window.PILL_CLASS= @json($pillClassMap);
        window.DISP_NAME = @json($displayNames);
        window.CAT_ORDER = ['BEGINNER', 'INTERMEDIATE', 'MASTER', 'GENERAL'];
        window.PLACEHOLDER_IMG = @json(asset('images/guidelines/placeholder.jpg'));
    </script>

    {{-- Swiper JS (muat hanya jika belum ada) --}}
    <script>
        (function ensureSwiper(){
            if (!window.Swiper) {
                var s = document.createElement('script');
                s.src = 'https://unpkg.com/swiper@10/swiper-bundle.min.js';
                document.head.appendChild(s);
            }
        })();
    </script>

    {{-- ======================= Logika filter/sort/render + SWIPER MOBILE ======================= --}}
    <script>
        // Modal logic for mobile filter
        document.addEventListener('DOMContentLoaded', function() {
            const openBtn = document.getElementById('openFilterModal');
            const closeBtn = document.getElementById('closeFilterModal');
            const modal = document.getElementById('filterModal');
            const overlay = document.getElementById('filterModalOverlay');
            function openModal(){ modal.classList.add('active'); overlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
            function closeModal(){ modal.classList.remove('active'); overlay.classList.remove('active'); document.body.style.overflow = ''; }
            openBtn && openBtn.addEventListener('click', openModal);
            closeBtn && closeBtn.addEventListener('click', closeModal);
            overlay && overlay.addEventListener('click', closeModal);

            // Sync desktop & mobile filter values
            function syncDesktopToMobile() {
                document.getElementById('categoryMobile').value = document.getElementById('category').value;
                document.getElementById('qMobile').value = document.getElementById('q').value;
                document.getElementById('sortMobile').value = document.getElementById('sort').value;
            }
            function syncMobileToDesktop() {
                document.getElementById('category').value = document.getElementById('categoryMobile').value;
                document.getElementById('q').value = document.getElementById('qMobile').value;
                document.getElementById('sort').value = document.getElementById('sortMobile').value;
            }
            openBtn && openBtn.addEventListener('click', syncDesktopToMobile);

            // Mobile filter logic
            const resultCountMobile = document.getElementById('resultCountMobile');
            function triggerDesktopFilter() {
                syncMobileToDesktop();
                document.getElementById('category').dispatchEvent(new Event('change'));
                document.getElementById('sort').dispatchEvent(new Event('change'));
                document.getElementById('q').dispatchEvent(new Event('input'));
                resultCountMobile.textContent = document.getElementById('resultCount').textContent;
            }
            ['categoryMobile','sortMobile','qMobile'].forEach(id=>{
                const el = document.getElementById(id);
                el && el.addEventListener('change', triggerDesktopFilter);
                el && el.addEventListener('input', triggerDesktopFilter);
            });
            document.getElementById('filterFormMobile')?.addEventListener('submit', function(e){
                e.preventDefault(); triggerDesktopFilter(); closeModal();
            });
        });

        // Desktop + Mobile render
        (function() {
            const countEl = document.getElementById('resultCount');
            const form = document.getElementById('filterForm');
            if (!form) return;

            const catSwipers = {};
            const selCat = form.querySelector('#category');
            const selSort = form.querySelector('#sort');
            const inpQ = form.querySelector('#q');
            const items = Array.isArray(window.G_ITEMS) ? window.G_ITEMS.slice() : [];
            const PLACEHOLDER = window.PLACEHOLDER_IMG || '';

            function esc(s) {
                return (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]));
            }
            function buildCard(it) {
                const pillCls = (window.PILL_CLASS && window.PILL_CLASS[it.category]) || 'badge-gray';
                const disp = (window.DISP_NAME && window.DISP_NAME[it.category]) || 'General';
                return `
                <a href="${esc(it.href)}"
                   class="group relative h-full rounded-2xl overflow-hidden bg-neutral-800 ring-1 ring-white/5 shadow-lg hover:shadow-xl transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-300 flex flex-col"
                   aria-label="Open guideline: ${esc(it.title)}">
                    <div class="relative h-48 md:h-56 img-frame">
                        <img
                            class="absolute inset-0 w-full h-full object-cover img-el js-progressive"
                            alt="${esc(it.title)}"
                            src="data:image/gif;base64,R0lGODlhAQABAAAAACw="
                            data-src="${esc(it.image)}"
                            data-fallback="${esc(PLACEHOLDER)}"
                            loading="lazy"
                            decoding="async">
                        <div class="spinner" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
                                <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                                <path d="M22 12a10 10 0 0 0-10-10" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <div class="ph" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor">
                                <path d="M4 7h3l2-2h6l2 2h3a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2zm8 11a5 5 0 100-10 5 5 0 000 10z"/>
                            </svg>
                        </div>

                        ${it.isNew ? `<span class="badge-pill badge-blue top-4 left-4">New</span>` : ``}
                        <span class="badge-pill ${pillCls} top-4 right-4">${esc(disp)}</span>
                        <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    </div>
                    <div class="p-4 bg-black/30 flex flex-col grow">
                        <h3 class="title-2lines text-base md:text-lg font-semibold text-white line-clamp-2">${esc(it.title)}</h3>
                        <div class="mt-3 flex items-center justify-between text-xs text-white/70 mt-auto">
                            <span>${esc(it.dateText)}</span>
                            <div class="flex items-center gap-2 opacity-95">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-md ring-1 ring-white/10 hover:bg-white/10">
                                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="18" cy="8" r="2.25"></circle>
                                        <circle cx="6"  cy="12" r="2.25"></circle>
                                        <circle cx="18" cy="16" r="2.25"></circle>
                                        <path d="M8.1 12.7l7.8 3.6M15.9 7.7L8.1 11.3"></path>
                                    </svg>
                                    <span class="sr-only">Share</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>`;
            }
            const buildSlide = (it) => `<div class="swiper-slide">${buildCard(it)}</div>`;

            function initOrUpdateSwiper(cat, count) {
                const isMobile = window.matchMedia('(max-width: 767.98px)').matches;
                const el = document.getElementById('swiper-' + cat);
                if (!el) return;

                if (!count) {
                    if (catSwipers[cat]) { catSwipers[cat].destroy(true, true); delete catSwipers[cat]; }
                    return;
                }
                if (!isMobile) {
                    if (catSwipers[cat]) { catSwipers[cat].destroy(true, true); delete catSwipers[cat]; }
                    return;
                }
                if (catSwipers[cat]) { catSwipers[cat].update(); return; }

                function create() {
                    if (!window.Swiper) { return setTimeout(create, 50); }
                    catSwipers[cat] = new Swiper('#swiper-' + cat, {
                        slidesPerView: 1.1,
                        spaceBetween: 12,
                        centeredSlides: false,
                        pagination: { el: '#pag-' + cat, clickable: true },
                        keyboard: { enabled: true },
                        a11y: { enabled: true },
                    });
                }
                create();
            }

            function applyFilters() {
                const selCat = form.querySelector('#category');
                const selSort = form.querySelector('#sort');
                const inpQ = form.querySelector('#q');

                const cat = selCat.value || 'ALL';
                const q = (inpQ.value || '').trim().toLowerCase();
                const sort = selSort.value || 'newest';

                let base = items.filter(i=>{
                    if (!q) return true;
                    return (i.title||'').toLowerCase().includes(q) || (i.description||'').toLowerCase().includes(q);
                });

                const cmp = {
                    'oldest':     (a,b)=> (a.dateTs||0) - (b.dateTs||0),
                    'title-asc':  (a,b)=> (a.title||'').localeCompare(b.title||''),
                    'title-desc': (a,b)=> (b.title||'').localeCompare(a.title||''),
                    'newest':     (a,b)=> (b.dateTs||0) - (a.dateTs||0),
                }[sort] || ((a,b)=> (b.dateTs||0) - (a.dateTs||0));

                let totalShown = 0;
                (Array.isArray(window.CAT_ORDER) ? window.CAT_ORDER : ['BEGINNER','INTERMEDIATE','MASTER','GENERAL']).forEach(C=>{
                    const sec   = document.getElementById('section-' + C);
                    const grid  = document.getElementById('grid-' + C);
                    const empty = document.getElementById('empty-' + C);
                    const slidesWrap = document.getElementById('slides-' + C);

                    if (!sec || !grid || !empty || !slidesWrap) return;

                    let arr = (cat === 'ALL' || cat === C) ? base.filter(i=> i.category === C) : [];
                    arr.sort(cmp);
                    arr = arr.slice(0, 3);

                    grid.innerHTML = arr.map(buildCard).join('');
                    slidesWrap.innerHTML = arr.map(buildSlide).join('');

                    const show = arr.length > 0;
                    if (cat !== 'ALL' && C !== cat) {
                        sec.classList.add('hidden');
                    } else {
                        sec.classList.remove('hidden');
                        empty.classList.toggle('hidden', show);
                    }

                    initOrUpdateSwiper(C, arr.length);

                    totalShown += arr.length;
                });

                countEl.textContent = String(totalShown);
                const resultCountMobile = document.getElementById('resultCountMobile');
                if (resultCountMobile) resultCountMobile.textContent = countEl.textContent;

                // Hook progressive loader setelah DOM update (untuk kartu)
                if (window.setupProgressiveImages) window.setupProgressiveImages(document);

                const params = new URLSearchParams();
                if (cat && cat !== 'ALL') params.set('category', cat);
                if (q) params.set('q', inpQ.value.trim());
                if (sort && sort !== 'newest') params.set('sort', sort);
                const qs = params.toString();
                history.replaceState(null, '', qs ? ('?' + qs) : location.pathname);
            }

            let t;
            function debounced(){ clearTimeout(t); t = setTimeout(applyFilters, 400); }

            form.querySelector('#category').addEventListener('change', applyFilters);
            form.querySelector('#sort').addEventListener('change', applyFilters);
            form.querySelector('#q').addEventListener('input', debounced);
            form.querySelector('#q').addEventListener('keydown', (e)=>{ if (e.key === 'Enter'){ e.preventDefault(); applyFilters(); } });

            window.addEventListener('resize', () => applyFilters());

            applyFilters();

            (function initHero(){
                if (!document.querySelector('.hero-swiper')) return;
                function make(){
                    if (!window.Swiper) return setTimeout(make, 50);
                    new Swiper('.hero-swiper', {
                        loop:true,
                        speed:700,
                        autoplay:{delay:5000, disableOnInteraction:false},
                        pagination:{ el:'.hero-pagination', clickable:true },
                        navigation:{ nextEl:'.hero-next', prevEl:'.hero-prev' },
                        keyboard:{ enabled:true },
                        a11y:{ enabled:true },
                    });
                }
                make();
            })();
        })();
    </script>

    {{-- ===================== Progressive image loader core (IO + fallback) — untuk KARTU ===================== --}}
    <script>
        (function(){
            function setupProgressiveImages(root){
                const scope = root || document;
                const imgs = scope.querySelectorAll('img.js-progressive[data-src]');
                if (!imgs.length) return;

                const io = 'IntersectionObserver' in window
                    ? new IntersectionObserver(onIntersect, { rootMargin: '200px 0px' })
                    : null;

                imgs.forEach(img=>{
                    const frame = img.closest('.img-frame') || img.parentElement;
                    if (!frame) return;
                    if (io) io.observe(img); else loadNow(img, frame);
                    img.addEventListener('error', ()=> handleError(img, frame));
                    img.addEventListener('load',  ()=> handleLoad(img, frame));
                });

                function onIntersect(entries, obs){
                    entries.forEach(entry=>{
                        if (!entry.isIntersecting) return;
                        const img = entry.target;
                        const frame = img.closest('.img-frame') || img.parentElement;
                        loadNow(img, frame);
                        obs.unobserve(img);
                    });
                }
                function loadNow(img, frame){
                    const src = img.getAttribute('data-src');
                    if (src && img.src !== src) img.src = src;
                }
                function handleLoad(img, frame){
                    frame.classList.add('loaded');
                    frame.classList.remove('error');
                }
                function handleError(img, frame){
                    const fallback = img.getAttribute('data-fallback') || img.src || window.PLACEHOLDER_IMG || '';
                    if (fallback && img.src !== fallback){ img.src = fallback; }
                    else { frame.classList.add('error'); }
                }
            }
            window.setupProgressiveImages = setupProgressiveImages;
            document.addEventListener('DOMContentLoaded', ()=> setupProgressiveImages(document));
        })();
    </script>

    {{-- ===================== NEWSLETTER SECTION (Enhanced) ===================== --}}
    <section class="relative isolate overflow-hidden bg-[#0f0f10] text-white py-16 px-6 md:px-20 border-t border-white/10">
      <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-25"
             style="background: radial-gradient(60% 60% at 50% 50%, #fb923c55 0%, #f9731655 35%, transparent 70%);">
        </div>
        <svg class="absolute inset-0 h-full w-full opacity-[0.08]" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
              <path d="M 32 0 L 0 0 0 32" fill="none" stroke="white" stroke-width="0.5"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
      </div>

      <div class="relative max-w-3xl mx-auto">
        <div class="text-center">
          <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs uppercase tracking-wide text-white/80">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 8l9 6 9-6" /><path d="M21 8v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8" />
            </svg>
            Newsletter
          </span>
          <h2 class="mt-4 text-3xl md:text-4xl font-bold leading-tight">
            Subscribe & stay in the loop
          </h2>
          <p class="mt-3 text-sm md:text-base text-white/70">
            Get updates on new gear, events, and community stories from Xander Billiard. No spam—unsubscribe anytime.
          </p>
        </div>

        @if(session('success'))
          <div class="mt-6 rounded-xl border border-emerald-400/30 bg-emerald-500/10 text-emerald-200 px-4 py-3">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <p class="text-sm">{{ session('success') }}</p>
            </div>
          </div>
        @endif

        @error('email')
          <div class="mt-6 rounded-xl border border-red-400/30 bg-red-500/10 text-red-200 px-4 py-3">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-.75-11.5a.75.75 0 011.5 0v5a.75.75 0 01-1.5 0v-5zm.75 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
              </svg>
              <p class="text-sm">{{ $message }}</p>
            </div>
          </div>
        @enderror

        <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 backdrop-blur supports-[backdrop-filter]:bg-white/5 p-4 sm:p-5 md:p-6">
          <form id="newsletterForm" action="{{ route('subscribe.store') }}" method="POST"
                class="flex flex-col sm:flex-row items-center gap-3">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
            <label for="newsletter-email" class="sr-only">Email address</label>
            <input id="newsletter-email" type="email" name="email" required inputmode="email" autocomplete="email"
                   placeholder="Enter your email address"
                   class="w-full flex-1 rounded-full bg-white text-gray-900 placeholder-gray-500 px-5 py-3
                          focus:outline-none focus:ring-4 ring-orange-500/30 border border-white/10"/>
            <button type="submit" data-submit
              class="inline-flex items-center justify-center gap-2 rounded-full px-7 py-3 font-semibold
                     bg-gradient-to-r from-orange-500 via-amber-500 to-rose-500
                     hover:brightness-110 active:brightness-95 transition duration-200 shadow-lg shadow-orange-500/20">
              <svg data-spinner class="h-5 w-5 animate-spin hidden" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
              </svg>
              <span>Subscribe</span>
            </button>
          </form>

          <div class="mt-3 flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-4 text-xs text-white/60">
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white/50" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 1.75a7.25 7.25 0 00-7.25 7.25c0 5.5 7.25 13.25 7.25 13.25S19.25 14.5 19.25 9A7.25 7.25 0 0012 1.75zm0 9.75a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/>
              </svg>
              No spam, unsubscribe anytime.
            </div>
            <span class="hidden sm:inline-block">•</span>
            <div>By subscribing you agree to our <a href="{{ url('/terms') }}" class="underline decoration-dotted hover:decoration-solid">Terms</a> &amp; <a href="{{ url('/privacy') }}" class="underline decoration-dotted hover:decoration-solid">Privacy</a>.</div>
          </div>
        </div>
      </div>

      <script>
        (function(){
          const form = document.getElementById('newsletterForm');
          if(!form) return;
          form.addEventListener('submit', function(){
            const btn = form.querySelector('[data-submit]');
            const spinner = form.querySelector('[data-spinner]');
            if(btn){
              btn.disabled = true;
              btn.classList.add('opacity-80','cursor-not-allowed');
            }
            if(spinner) spinner.classList.remove('hidden');
          }, { passive:true });
        })();
      </script>
    </section>
@endsection
