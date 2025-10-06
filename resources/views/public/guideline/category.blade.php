@extends('app')
@section('title', 'Master the Game')

@section('content')
    {{-- Anti white flash saat overscroll/top-bounce (iOS/desktop) --}}
    <style>
        :root, html, body { background-color: #0a0a0a; }
        html, body { height: 100%; }
        /* iOS Safari & modern browsers: cegah flash putih saat bounce */
        :root { color-scheme: dark; }
        body { overscroll-behavior-y: contain; touch-action: pan-y; }

        /* Input dark (untuk Filter & Sort) */
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

        /* Toast kecil untuk feedback copy link (fallback share) */
        .share-toast{
            position: fixed; left: 50%; transform: translateX(-50%);
            bottom: 24px; background: rgba(17,17,17,.95); color:#fff;
            padding: 10px 14px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.35);
            z-index: 9999; font-size: 14px; line-height: 1; opacity: 0; transition: opacity .2s ease;
        }
        .share-toast.show{ opacity: 1; }
    </style>

    @php
        use Illuminate\Support\Str;
        use Illuminate\Support\Arr;
        use Illuminate\Support\Facades\Storage;
        use Illuminate\Support\Facades\Route;

        // --- Generate beberapa card tambahan secara random (kategori & urutan acak) ---
        $sampleImages = collect([
            'images/guidelines/guideline-1.png',
            'images/guidelines/guideline-2.png',
        ])->filter(fn($p) => file_exists(public_path($p)))->values();

        $randomCategories = ['Beginner', 'Intermediate', 'Master', 'General'];

        $extraCards = collect(range(1, 8))->map(function ($i) use ($randomCategories, $sampleImages) {
            $cat = Arr::random($randomCategories);
            $title = match ($cat) {
                'Beginner'     => "Starter Tip #$i",
                'Intermediate' => "Progress Drill #$i",
                'Master'       => "Pro Strategy #$i",
                'General'      => "Billiard Insight #$i",
            };
            $img = $sampleImages->isNotEmpty() ? Arr::random($sampleImages->all()) : null;

            return (object)[
                'title'          => $title,
                'slug'           => 'sample-'.$i,
                'url'            => url('/guideline/sample-'.$i),
                'category'       => $cat,
                'featured_image' => $img,
                'is_new'         => (bool) random_int(0, 1),
                'published_at'   => now()->subDays(random_int(0, 45)),
            ];
        });

        // Gabungkan data asli + extra, lalu acak
        $cards = collect($guidelines ?? [])->concat($extraCards)->shuffle();

        // Ambil query awal (untuk mengisi UI)
        $activeCategory = request('category', 'ALL'); // ALL | Beginner | Intermediate | Master | General
        $q              = trim(request('q', ''));
        $sort           = request('sort', 'newest');  // newest | oldest | title-asc | title-desc

        // Map warna kategori (badge)
        $colorMap = [
            'Beginner'     => 'green-500',
            'Intermediate' => 'yellow-500',
            'Master'       => 'red-500',
            'General'      => 'gray-500',
        ];

        // Siapkan dataset untuk client-side render (tanpa reload)
        $clientItems = $cards->map(function($g) use ($colorMap) {
            // URL tujuan
            $shareUrl = $g->url
                ?? (Route::has('guideline.show') && !empty($g->slug)
                    ? route('guideline.show', ['slug' => $g->slug])
                    : url('/guideline/' . Str::slug($g->title ?? 'guideline', '-')));

            // Gambar
            $imagePath = $g->featured_image ?? null;
            if ($imagePath) {
                if (Str::startsWith($imagePath, 'guidelines/')) {
                    $imagePath = Storage::url($imagePath);
                } elseif (!file_exists(public_path($imagePath)) && file_exists(public_path('images/guidelines/' . basename($imagePath)))) {
                    $imagePath = asset('images/guidelines/' . basename($imagePath));
                } else {
                    $imagePath = asset($imagePath);
                }
            }

            // Tanggal
            $date = $g->published_at ?? null;
            try {
                $dateText = $date ? (is_string($date) ? \Carbon\Carbon::parse($date)->format('F j, Y') : $date->format('F j, Y')) : '';
                $dateTs   = $date ? (is_string($date) ? \Carbon\Carbon::parse($date)->timestamp : $date->timestamp) : 0;
            } catch (\Throwable $e) {
                $dateText = ''; $dateTs = 0;
            }

            $cat = $g->category ?? 'General';

            return [
                'title'     => $g->title ?? '',
                'href'      => $shareUrl,
                'image'     => $imagePath,
                'isNew'     => !empty($g->is_new),
                'dateText'  => $dateText,
                'dateTs'    => $dateTs,
                'category'  => $cat,
                'catColor'  => $colorMap[$cat] ?? 'gray-500',
                'shareText' => 'Check out this guideline: ' . ($g->title ?? ''),
            ];
        })->values();
    @endphp

    <div class="bg-neutral-900 text-white min-h-screen">
        <!-- Hero Header -->
        <div class="bg-cover bg-center py-20 px-6" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <a href="{{ route('community.index') }}" class="hover:text-white">Community</a>
                    <span>/</span>
                    <a href="{{ route('community.news.index') }}" class="hover:text-white">News</a>
                </div>
                <h1 class="text-3xl md:text-5xl font-bold uppercase mt-2">MASTER THE GAME</h1>
            </div>
        </div>

        <!-- Filter + Sort (tanpa reload) -->
        <section class="max-w-7xl mx-auto px-6 pt-8">
            <form id="filterForm" class="bg-neutral-800/60 ring-1 ring-white/10 rounded-2xl p-4 md:p-5" onsubmit="return false;">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    <label class="block">
                        <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Category</span>
                        <select id="category" class="input-dark select-dark w-full">
                            @php
                                $catOptions = ['ALL'=>'All','Beginner'=>'Beginner','Intermediate'=>'Intermediate','Master'=>'Master','General'=>'General'];
                            @endphp
                            @foreach ($catOptions as $val => $label)
                                <option value="{{ $val }}" @selected($activeCategory===$val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block md:col-span-1">
                        <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Search</span>
                        <input id="q" type="search" value="{{ $q }}" placeholder="Search title…" class="input-dark w-full">
                    </label>

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

        <!-- Articles Grid (dirender via JS) -->
        <div class="max-w-7xl mx-auto px-6 py-8">
            <div id="emptyState" class="hidden text-center text-white/70 py-16">No articles match your filters.</div>
            <div id="cardsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        </div>
    </div>

    {{-- DATASET untuk JS --}}
    <script>
        window.MASTER_ITEMS = @json($clientItems);
    </script>

    {{-- Render + Filter/Sort tanpa reload --}}
    <script>
        (function(){
            const grid   = document.getElementById('cardsGrid');
            const empty  = document.getElementById('emptyState');
            const count  = document.getElementById('resultCount');
            const form   = document.getElementById('filterForm');
            const selCat = form.querySelector('#category');
            const selSort= form.querySelector('#sort');
            const inpQ   = form.querySelector('#q');

            const items  = Array.isArray(window.MASTER_ITEMS) ? window.MASTER_ITEMS.slice() : [];

            function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[c])); }

            function buildCard(it){
                return `
<a href="${esc(it.href)}"
   class="block bg-gray-800 rounded-lg overflow-hidden shadow-lg flex flex-col h-full hover:bg-gray-700 transition duration-300">
    <div class="relative h-48 md:h-56 bg-gray-700 flex items-center justify-center">
        ${it.image ? `<img src="${esc(it.image)}" alt="${esc(it.title)}" class="object-cover w-full h-full">`
                    : `<svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5h18M3 19h18M3 5v14m18-14v14M8 12h8M8 16h8" /></svg>`}
        ${it.isNew ? `<span class="absolute top-2 left-2 bg-blue-600 text-xs font-bold px-2 py-0.5 rounded-full">New</span>` : ``}
        <span class="absolute top-2 right-2 bg-${esc(it.catColor)} text-xs font-bold px-2 py-0.5 rounded-full">
            ${esc(it.category)}
        </span>
    </div>

    <div class="p-4 bg-black bg-opacity-30 flex-grow flex flex-col justify-end">
        <h3 class="text-sm font-semibold mb-1">${esc(it.title)}</h3>
        <div class="text-xs text-gray-400 flex items-center justify-between mt-auto">
            <span>${esc(it.dateText)}</span>
            <div class="flex gap-2">
                <i class="far fa-bookmark" aria-hidden="true"></i>
                <button type="button"
                        class="btn-share"
                        title="Share"
                        aria-label="Share"
                        data-url="${esc(it.href)}"
                        data-title="${esc(it.title)}"
                        data-text="${esc(it.shareText)}">
                    <i class="fas fa-share-alt" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
</a>`;
            }

            function applyFilters(){
                const cat = selCat.value || 'ALL';
                const q   = (inpQ.value || '').trim().toLowerCase();
                const sort= selSort.value || 'newest';

                let arr = items.slice();

                if (cat !== 'ALL') arr = arr.filter(i => (i.category || '') === cat);
                if (q) arr = arr.filter(i => (i.title || '').toLowerCase().includes(q));

                switch (sort) {
                    case 'oldest':     arr.sort((a,b)=> (a.dateTs||0) - (b.dateTs||0)); break;
                    case 'title-asc':  arr.sort((a,b)=> (a.title||'').localeCompare(b.title||'')); break;
                    case 'title-desc': arr.sort((a,b)=> (b.title||'').localeCompare(a.title||'')); break;
                    default:           arr.sort((a,b)=> (b.dateTs||0) - (a.dateTs||0)); // newest
                }

                grid.innerHTML = arr.map(buildCard).join('');
                const none = arr.length === 0;
                empty.classList.toggle('hidden', !none);
                count.textContent = String(arr.length);

                // Update URL (tanpa reload)
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

    {{-- JS: Web Share API + fallback copy to clipboard + toast --}}
    <script>
        (function () {
            function showToast(msg) {
                const el = document.createElement('div');
                el.className = 'share-toast';
                el.textContent = msg;
                document.body.appendChild(el);
                // force reflow for transition
                getComputedStyle(el).opacity;
                el.classList.add('show');
                setTimeout(() => {
                    el.classList.remove('show');
                    setTimeout(() => el.remove(), 250);
                }, 1800);
            }

            document.addEventListener('click', async function (e) {
                const btn = e.target.closest('.btn-share');
                if (!btn) return;

                // Mencegah klik share men-trigger navigasi parent <a>
                e.preventDefault();
                e.stopPropagation();

                const url   = btn.getAttribute('data-url')   || location.href;
                const title = btn.getAttribute('data-title') || document.title;
                const text  = btn.getAttribute('data-text')  || '';

                if (navigator.share) {
                    try {
                        await navigator.share({ title, text, url });
                    } catch (err) {
                        // User cancels: no toast
                    }
                } else {
                    // Fallback: copy link
                    try {
                        if (navigator.clipboard?.writeText) {
                            await navigator.clipboard.writeText(url);
                        } else {
                            const ta = document.createElement('textarea');
                            ta.value = url;
                            ta.setAttribute('readonly', '');
                            ta.style.position = 'fixed';
                            ta.style.opacity = '0';
                            document.body.appendChild(ta);
                            ta.select();
                            document.execCommand('copy');
                            ta.remove();
                        }
                        showToast('Link copied to clipboard');
                    } catch {
                        showToast('Unable to share');
                    }
                }
            }, { passive: false });
        })();
    </script>
@endsection
