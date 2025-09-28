@extends('app')
@section('title', 'Guideline - Xander Billiard')

@section('content')
    {{-- Anti white flash + styles --}}
    <style>
        :root, html, body { background-color:#0a0a0a; }
        html, body { height:100%; }
        :root { color-scheme: dark; }
        body { overscroll-behavior-y: contain; touch-action: pan-y; }

        /* Badge pills (custom CSS, aman dari purge) */
        .badge-pill{
            position:absolute; z-index:10; display:inline-flex; align-items:center; justify-content:center;
            padding:.45rem .95rem; border-radius:9999px; font-weight:700; font-size:13.5px; line-height:1; letter-spacing:.2px;
            color:#fff; background:var(--bg,#444); border:1px solid rgba(255,255,255,.18); box-shadow:0 10px 25px rgba(0,0,0,.35);
        }
        @media (min-width:768px){ .badge-pill{ font-size:14px; padding:.55rem 1.1rem; } }
        .badge-blue{--bg:#2E90FF;} .badge-green{--bg:#22c55e;} .badge-yellow{--bg:#FDB022;}
        .badge-red{--bg:#F05252;} .badge-gray{--bg:#6B7280;}

        /* Judul 2 baris agar tinggi konsisten */
        .title-2lines{ min-height:3.25rem; }
        @media (min-width:768px){ .title-2lines{ min-height:3.75rem; } }

        /* Input dark (untuk filter & sort) */
        .input-dark{
            background:#1f2937; color:#fff; border:1px solid rgba(255,255,255,.12);
            border-radius:.65rem; padding:.6rem .8rem; outline:none;
        }
        .input-dark:focus{ border-color:rgba(255,255,255,.25); box-shadow:0 0 0 3px rgba(255,255,255,.08); }
        .select-dark{
            appearance:none;
            background-image: linear-gradient(45deg,transparent 50%,#aaa 50%),linear-gradient(135deg,#aaa 50%,transparent 50%);
            background-position: calc(100% - 18px) calc(1.1em), calc(100% - 13px) calc(1.1em);
            background-size: 6px 6px, 6px 6px; background-repeat:no-repeat; padding-right:2.25rem;
        }

        /* Grid rows seragam */
        .auto-rows-fr{ grid-auto-rows: 1fr; }
    </style>

    <div class="bg-neutral-900 text-white min-h-dvh overflow-x-hidden">
        @php
            use Illuminate\Support\Str;
            use Illuminate\Support\Facades\Storage;

            // Helper gambar dari storage/public/fallback
            $resolveImage = function (?string $rawPath, string $default = '/images/hero/guideline-main.jpg') {
                if (!empty($rawPath)) {
                    $path = $rawPath;
                    if (Str::startsWith($path, 'guidelines/')) return Storage::url($path);
                    if (file_exists(public_path($path))) return asset($path);
                    $basename = basename($path);
                    if (file_exists(public_path('images/guidelines/' . $basename))) return asset('images/guidelines/' . $basename);
                }
                return asset($default);
            };

            // Teks & warna badge per kategori
            $displayNames = ['BEGINNER'=>'Beginner','INTERMEDIATE'=>'Intermediate','MASTER'=>'Master','GENERAL'=>'General'];
            $pillClassMap = ['BEGINNER'=>'badge-green','INTERMEDIATE'=>'badge-yellow','MASTER'=>'badge-red','GENERAL'=>'badge-gray'];

            // Nilai awal dari query (untuk mengisi UI)
            $activeCategory = strtoupper(request('category', 'ALL'));
            $q      = trim(request('q', ''));
            $sort   = request('sort', 'newest');

            // Data hero
            $latestGuideline = ($guidelines ?? collect())->first();
        @endphp

        {{-- ======================= HERO ======================= --}}
        @php
            $staticHero = collect([
                (object)[
                    'featured_image'=>'images/guidelines/guideline-1.png',
                    'title'=>$latestGuideline->title ?? 'Guideline',
                    'description'=>$latestGuideline->description ?? '',
                    'published_at'=>$latestGuideline->published_at ?? null,
                    'slug'=>$latestGuideline->slug ?? null,
                ],
                (object)[
                    'featured_image'=>'images/guidelines/guideline-2.png',
                    'title'=>$latestGuideline->title ?? 'Guideline',
                    'description'=>$latestGuideline->description ?? '',
                    'published_at'=>$latestGuideline->published_at ?? null,
                    'slug'=>$latestGuideline->slug ?? null,
                ],
            ])->filter(fn($s)=>file_exists(public_path($s->featured_image)));
            $heroItems = $staticHero->isNotEmpty() ? $staticHero : collect($guidelines ?? [])->take(5);
        @endphp

        @if ($heroItems->count() > 0)
            <link rel="stylesheet" href="https://unpkg.com/swiper@10/swiper-bundle.min.css" />
            <style>
                .vh-section { height: clamp(540px, 84svh, 960px); }
                @media (min-width:768px){ .vh-section { height: clamp(660px, 88svh, 1020px); } }
                @media (min-width:1280px){ .vh-section { height: clamp(740px, 92svh, 1100px); } }
            </style>
            <section class="relative">
                <div class="swiper hero-swiper">
                    <div class="swiper-wrapper">
                        @foreach ($heroItems as $i => $g)
                            @php
                                $img   = $resolveImage($g->featured_image ?? null, '/images/hero/guideline-main.jpg');
                                $title = $g->title ?? 'Guideline';
                                $desc  = $g->description ?? '';
                                $date  = optional($g->published_at)->format('d F Y');
                            @endphp
                            <div class="swiper-slide">
                                <div class="relative isolate w-full overflow-hidden bg-neutral-900 vh-section">
                                    <img src="{{ $img }}" alt="{{ $title }}" class="absolute inset-0 h-full w-full object-cover object-[78%_center]"
                                         loading="{{ $i === 0 ? 'eager' : 'lazy' }}" decoding="async" sizes="100vw" />
                                    <div class="absolute inset-0 pointer-events-none z-[1]">
                                        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent"></div>
                                    </div>
                                    <div class="absolute inset-0 z-10 flex items-center">
                                        <div class="mx-auto w-full max-w-7xl px-6 md:px-20">
                                            <div class="max-w-xl md:max-w-2xl text-white">
                                                @if ($date)
                                                    <p class="font-semibold mb-3 text-white/90 text-sm md:text-base lg:text-lg">{{ $date }}</p>
                                                @endif
                                                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-[1.1] mb-5">{{ $title }}</h1>
                                                @if ($desc)
                                                    <p class="text-base md:text-lg text-white/85 leading-relaxed mb-8 max-w-prose">
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

            <script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    new Swiper('.hero-swiper', {
                        loop:true, speed:700,
                        autoplay:{ delay:5000, disableOnInteraction:false },
                        pagination:{ el:'.hero-pagination', clickable:true },
                        navigation:{ nextEl:'.hero-next', prevEl:'.hero-prev' },
                        keyboard:{ enabled:true }, a11y:{ enabled:true },
                    });
                });
            </script>
        @endif

        {{-- ======================= FILTER & SORT (diletakkan DI ATAS daftar kategori) ======================= --}}
        <section class="px-6 lg:px-24 pt-10">
            <form id="filterForm" class="bg-neutral-800/60 ring-1 ring-white/10 rounded-2xl p-4 md:p-5" onsubmit="return false;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    {{-- Category --}}
                    <label class="block">
                        <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Category</span>
                        <select id="category" class="input-dark select-dark w-full">
                            @php
                                $catOptions = ['ALL'=>'All','BEGINNER'=>'Beginner','INTERMEDIATE'=>'Intermediate','MASTER'=>'Master','GENERAL'=>'General'];
                            @endphp
                            @foreach($catOptions as $val => $label)
                                <option value="{{ $val }}" @selected($activeCategory===$val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    {{-- Search --}}
                    <label class="block md:col-span-1">
                        <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Search</span>
                        <input id="q" type="search" value="{{ $q }}" placeholder="Search title or description…" class="input-dark w-full">
                    </label>

                    {{-- Sort --}}
                    <label class="block">
                        <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Sort</span>
                        <select id="sort" class="input-dark select-dark w-full">
                            <option value="newest"     @selected($sort==='newest')>Newest</option>
                            <option value="oldest"     @selected($sort==='oldest')>Oldest</option>
                            <option value="title-asc"  @selected($sort==='title-asc')>Title A–Z</option>
                            <option value="title-desc" @selected($sort==='title-desc')>Title Z–A</option>
                        </select>
                    </label>
                </div>

                <div class="mt-3 text-sm text-white/70">
                    Showing <strong id="resultCount">0</strong> results.
                </div>
            </form>
        </section>

        {{-- ======================= SECTION PER KATEGORI (tetap 1 baris 3 item; view all tetap) ======================= --}}
        @foreach (['BEGINNER','INTERMEDIATE','MASTER','GENERAL'] as $category)
            @php
                $display = $displayNames[$category] ?? $category;
            @endphp

            <section id="section-{{ $category }}" class="px-6 lg:px-24 py-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold uppercase tracking-wide">{{ $display }}</h2>
                    <a href="{{ route('guideline.category', ['category' => Str::slug($display, '-')]) }}"
                       class="text-sm text-gray-400 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-300 rounded px-2 py-1"
                       aria-label="View all {{ $display }} guidelines">
                        view all
                    </a>
                </div>

                {{-- GRID akan diisi via JS agar filter/sort bekerja tanpa reload --}}
                <div id="grid-{{ $category }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 auto-rows-fr gap-4 md:gap-6"></div>
                <div id="empty-{{ $category }}" class="hidden text-center text-white/60 py-8">No matching items in {{ $display }}.</div>
            </section>
        @endforeach
    </div>

    {{-- ======================= DATASET JS ======================= --}}
    @php
        // siapkan dataset klien (semua item)
        $clientItems = collect($guidelines ?? [])->map(function($g) use ($resolveImage) {
            $published = optional($g->published_at);
            return [
                'title'       => $g->title ?? '',
                'description' => strip_tags($g->description ?? ''),
                'category'    => strtoupper($g->category ?? 'GENERAL'),
                'image'       => $resolveImage($g->featured_image, '/images/guidelines/placeholder.jpg'),
                'dateTs'      => $published ? $published->timestamp : 0,
                'dateText'    => $published ? $published->format('F j, Y') : '',
                'isNew'       => (bool)(($g->is_new ?? false) || ($published && $published->gt(now()->subDays(14)))),
                'href'        => route('guideline.show', ['slug' => $g->slug]),
            ];
        })->values();
    @endphp

    <script>
        window.G_ITEMS    = @json($clientItems);
        window.PILL_CLASS = @json($pillClassMap);
        window.DISP_NAME  = @json($displayNames);
        window.CAT_ORDER  = ['BEGINNER','INTERMEDIATE','MASTER','GENERAL'];
    </script>

    {{-- ======================= Logika filter/sort/render (tanpa reload) ======================= --}}
    <script>
        (function(){
            const count  = document.getElementById('resultCount');
            const form   = document.getElementById('filterForm');
            if(!form) return;

            const selCat = form.querySelector('#category');
            const selSort= form.querySelector('#sort');
            const inpQ   = form.querySelector('#q');

            const items  = Array.isArray(window.G_ITEMS) ? window.G_ITEMS.slice() : [];
            const order  = Array.isArray(window.CAT_ORDER) ? window.CAT_ORDER : ['BEGINNER','INTERMEDIATE','MASTER','GENERAL'];

            function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[c])); }

            function buildCard(it){
                const pillCls = (window.PILL_CLASS && window.PILL_CLASS[it.category]) || 'badge-gray';
                const disp    = (window.DISP_NAME && window.DISP_NAME[it.category])  || 'General';
                return `
<a href="${esc(it.href)}"
   class="group relative h-full rounded-2xl overflow-hidden bg-neutral-800 ring-1 ring-white/5 shadow-lg hover:shadow-xl transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-300 flex flex-col"
   aria-label="Open guideline: ${esc(it.title)}">

    <div class="relative h-48 md:h-56 bg-gray-700">
        <img src="${esc(it.image)}" alt="${esc(it.title)}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
        ${it.isNew ? `<span class="badge-pill badge-blue top-4 left-4">New</span>` : ``}
        <span class="badge-pill ${pillCls} top-4 right-4">${esc(disp)}</span>
        <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
    </div>

    <div class="p-4 bg-black/30 flex flex-col grow">
        <h3 class="title-2lines text-base md:text-lg font-semibold text-white line-clamp-2">
            ${esc(it.title)}
        </h3>
        <div class="mt-3 flex items-center justify-between text-xs text-white/70 mt-auto">
            <span>${esc(it.dateText)}</span>
            <div class="flex items-center gap-2 opacity-95">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-md ring-1 ring-white/10 hover:bg-white/10">
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M7 4h10a2 2 0 0 1 2 2v14l-7-4-7 4V6a2 2 0 0 1 2-2z"/>
                    </svg>
                    <span class="sr-only">Bookmark</span>
                </span>
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

            function applyFilters(){
                const cat  = selCat.value || 'ALL';
                const q    = (inpQ.value || '').trim().toLowerCase();
                const sort = selSort.value || 'newest';

                // Filter global (search)
                let base = items.filter(i => {
                    if (!q) return true;
                    return (i.title || '').toLowerCase().includes(q) ||
                           (i.description || '').toLowerCase().includes(q);
                });

                // Sort comparator
                const cmp = {
                    'oldest'    : (a,b)=> (a.dateTs||0) - (b.dateTs||0),
                    'title-asc' : (a,b)=> (a.title||'').localeCompare(b.title||''),
                    'title-desc': (a,b)=> (b.title||'').localeCompare(a.title||''),
                    'newest'    : (a,b)=> (b.dateTs||0) - (a.dateTs||0),
                }[sort] || ((a,b)=> (b.dateTs||0) - (a.dateTs||0));

                let totalShown = 0;

                order.forEach(C => {
                    const sec   = document.getElementById('section-'+C);
                    const grid  = document.getElementById('grid-'+C);
                    const empty = document.getElementById('empty-'+C);
                    if(!sec || !grid || !empty) return;

                    // Jika user memilih kategori spesifik, hanya render untuk kategori itu
                    let arr = (cat==='ALL' || cat===C) ? base.filter(i => i.category === C) : [];

                    arr.sort(cmp);
                    arr = arr.slice(0, 3); // 1 baris, max 3

                    grid.innerHTML = arr.map(buildCard).join('');
                    const show = arr.length > 0;

                    // Tampilkan/sembunyikan section:
                    // - Jika cat === 'ALL', tampilkan section walau kosong -> tampilkan pesan kosong
                    // - Jika cat spesifik, sembunyikan section lain
                    if (cat !== 'ALL' && C !== cat) {
                        sec.classList.add('hidden');
                    } else {
                        sec.classList.remove('hidden');
                        empty.classList.toggle('hidden', show);
                    }

                    totalShown += arr.length;
                });

                count.textContent = String(totalShown);

                // Update URL tanpa reload
                const params = new URLSearchParams();
                if (cat && cat!=='ALL') params.set('category', cat);
                if (q) params.set('q', inpQ.value.trim());
                if (sort && sort!=='newest') params.set('sort', sort);
                const qs = params.toString();
                history.replaceState(null, '', qs ? ('?'+qs) : location.pathname);
            }

            // Debounce untuk search
            let t;
            function debounced(){ clearTimeout(t); t = setTimeout(applyFilters, 400); }

            selCat.addEventListener('change', applyFilters);
            selSort.addEventListener('change', applyFilters);
            inpQ.addEventListener('input', debounced);
            inpQ.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); applyFilters(); } });

            // Render awal
            applyFilters();
        })();
    </script>
@endsection
