@extends('app')
@section('title', 'Master the Game')

@push('styles')
<style>
  :root, html, body { background-color: #0a0a0a; }
  html, body { height: 100%; }
  :root { color-scheme: dark; }
  body { overscroll-behavior-y: contain; touch-action: pan-y; }

  /* Inputs */
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

  /* Share toast */
  .share-toast{
    position: fixed; left: 50%; transform: translateX(-50%);
    bottom: 24px; background: rgba(17,17,17,.95); color:#fff;
    padding: 10px 14px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.35);
    z-index: 9999; font-size: 14px; line-height: 1; opacity: 0; transition: opacity .2s ease;
  }
  .share-toast.show{ opacity: 1; }

  /* ===== Progressive Image Loader (Spinner + Camera Fallback) ===== */
  .img-frame{ position:relative; background:#111827; }
  .img-el{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .25s ease; }
  .img-frame.loaded .img-el{ opacity:1; }
  .img-frame .spinner, .img-frame .ph{
    position:absolute; inset:0; display:grid; place-items:center; pointer-events:none;
  }
  .img-frame .ph{ display:none; color:#9ca3af; }
  .img-frame.error .ph{ display:grid; }
  .img-frame.loaded .spinner{ display:none; }
  .spinner svg{ animation:spin 1s linear infinite; opacity:.9; }
  @keyframes spin{ from{ transform:rotate(0deg); } to{ transform:rotate(360deg); } }
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Route;

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

    $cards = collect($guidelines ?? [])->concat($extraCards)->shuffle();

    $activeCategory = request('category', 'ALL');
    $q              = trim(request('q', ''));
    $sort           = request('sort', 'newest');

    // Pakai HEX agar tidak tergantung kelas Tailwind dinamis
    $colorMap = [
        'Beginner'     => '#22c55e',
        'Intermediate' => '#f59e0b',
        'Master'       => '#ef4444',
        'General'      => '#6b7280',
    ];

    $resolveImage = function ($path) {
        if (!$path) return null;
        if (Str::startsWith($path, 'guidelines/')) return Storage::url($path);
        if (!file_exists(public_path($path)) && file_exists(public_path('images/guidelines/' . basename($path)))) {
            return asset('images/guidelines/' . basename($path));
        }
        return asset($path);
    };

    $placeholder = asset('images/guidelines/placeholder.jpg');

    $clientItems = $cards->map(function($g) use ($colorMap, $resolveImage) {
        $shareUrl = $g->url
            ?? (Route::has('guideline.show') && !empty($g->slug)
                ? route('guideline.show', ['slug' => $g->slug])
                : url('/guideline/' . Str::slug($g->title ?? 'guideline', '-')));

        $imagePath = $resolveImage($g->featured_image ?? null);

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
            'catColor'  => $colorMap[$cat] ?? '#6b7280',
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

  <!-- Filter + Sort -->
  <section class="max-w-7xl mx-auto px-6 pt-8">
    <form id="filterForm" class="bg-neutral-800/60 ring-1 ring-white/10 rounded-2xl p-4 md:p-5" onsubmit="return false;">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
        <label class="block">
          <span class="block text-xs uppercase tracking-wide text-white/60 mb-1">Category</span>
          @php $catOptions = ['ALL'=>'All','Beginner'=>'Beginner','Intermediate'=>'Intermediate','Master'=>'Master','General'=>'General']; @endphp
          <select id="category" class="input-dark select-dark w-full">
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

  <!-- Articles Grid -->
  <div class="max-w-7xl mx-auto px-6 py-8">
    <div id="emptyState" class="hidden text-center text-white/70 py-16">No articles match your filters.</div>
    <div id="cardsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
  </div>
</div>

{{-- Datasets for JS --}}
<script>
  window.MASTER_ITEMS = @json($clientItems);
  window.MASTER_PLACEHOLDER = @json($placeholder);
</script>

{{-- Render + Filter/Sort + Progressive Images --}}
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
  const PLACEHOLDER = window.MASTER_PLACEHOLDER || '';

  const BLANK = 'data:image/gif;base64,R0lGODlhAQABAAAAACw=';

  function esc(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[c])); }

  function buildCard(it){
    const imgSrc = it.image || PLACEHOLDER;
    return `
<a href="${esc(it.href)}"
   class="block bg-gray-800 rounded-lg overflow-hidden shadow-lg flex flex-col h-full hover:bg-gray-700 transition duration-300">
  <div class="relative h-48 md:h-56 img-frame">
    <img
      class="img-el js-progressive"
      alt="${esc(it.title)}"
      data-src="${esc(imgSrc)}"
      data-fallback="${esc(PLACEHOLDER)}"
      src="${BLANK}"
      loading="lazy"
      decoding="async">
    <div class="spinner" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2">
        <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
        <path class="opacity-90" d="M22 12a10 10 0 0 0-10-10" stroke-linecap="round"></path>
      </svg>
    </div>
    <div class="ph" aria-hidden="true">
      <svg viewBox="0 0 24 24" width="30" height="30" fill="currentColor">
        <path d="M4 7h3l2-2h6l2 2h3a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2zm8 11a5 5 0 100-10 5 5 0 000 10z"/>
      </svg>
    </div>

    ${it.isNew ? `<span class="absolute top-2 left-2 text-xs font-bold px-2 py-0.5 rounded-full" style="background:#3b82f6;">New</span>` : ``}
    <span class="absolute top-2 right-2 text-xs font-bold px-2 py-0.5 rounded-full" style="background:${esc(it.catColor)};">
      ${esc(it.category)}
    </span>
  </div>

  <div class="p-4 bg-black bg-opacity-30 flex-grow flex flex-col justify-end">
    <h3 class="text-sm font-semibold mb-1 line-clamp-2">${esc(it.title)}</h3>
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

    // After render, hook up progressive images
    if (window.setupProgressiveImages) window.setupProgressiveImages(grid);

    // Update URL (no reload)
    const params = new URLSearchParams();
    if (cat && cat!=='ALL') params.set('category', cat);
    if (q) params.set('q', inpQ.value.trim());
    if (sort && sort!=='newest') params.set('sort', sort);
    const qs = params.toString();
    history.replaceState(null, '', qs ? ('?'+qs) : location.pathname);
  }

  // Debounce for search
  let t;
  function debounced(){ clearTimeout(t); t = setTimeout(applyFilters, 400); }

  selCat.addEventListener('change', applyFilters);
  selSort.addEventListener('change', applyFilters);
  inpQ.addEventListener('input', debounced);
  inpQ.addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); applyFilters(); } });

  // Initial render
  applyFilters();
})();
</script>

{{-- Web Share API + fallback copy + toast --}}
<script>
(function () {
  function showToast(msg) {
    const el = document.createElement('div');
    el.className = 'share-toast';
    el.textContent = msg;
    document.body.appendChild(el);
    getComputedStyle(el).opacity; // force reflow
    el.classList.add('show');
    setTimeout(() => {
      el.classList.remove('show');
      setTimeout(() => el.remove(), 250);
    }, 1800);
  }

  document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-share');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const url   = btn.getAttribute('data-url')   || location.href;
    const title = btn.getAttribute('data-title') || document.title;
    const text  = btn.getAttribute('data-text')  || '';

    if (navigator.share) {
      try { await navigator.share({ title, text, url }); }
      catch (err) { /* user cancelled */ }
    } else {
      try {
        if (navigator.clipboard?.writeText) {
          await navigator.clipboard.writeText(url);
        } else {
          const ta = document.createElement('textarea');
          ta.value = url;
          ta.setAttribute('readonly','');
          ta.style.position='fixed'; ta.style.opacity='0';
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

{{-- Progressive image loader core (IO + fallback) --}}
<script>
(function(){
  function setupProgressiveImages(root){
    const scope = root || document;
    const imgs = scope.querySelectorAll('img.js-progressive[data-src]');
    const io = 'IntersectionObserver' in window ? new IntersectionObserver(onIntersect, { rootMargin: '200px 0px' }) : null;

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
      const fallback = img.getAttribute('data-fallback') || img.src;
      if (img.src !== fallback){ img.src = fallback; }
      else { frame.classList.add('error'); }
    }
  }
  window.setupProgressiveImages = setupProgressiveImages;
  document.addEventListener('DOMContentLoaded', ()=> setupProgressiveImages(document));
})();
</script>
@endsection
