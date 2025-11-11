@extends('app')
@section('title', $heading . ' - Xander Billiard')

@push('styles')
<style>
  :root { color-scheme: dark; }

  html, body { background:#0a0a0a; color:#fff; }
  #app, main { background:#0a0a0a; }

  /* === Mobile Filter === */
  @media (max-width:1023px) {
    .lg-hidden{display:block!important}
    .sm-hidden{display:none!important}
    .mobile-filter-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:40;display:none}
    .mobile-filter-overlay.active{display:block}
    .mobile-filter-sidebar{position:fixed;top:0;left:-100%;width:85%;max-width:340px;height:100%;background:#171717;z-index:50;transition:left .3s ease;overflow-y:auto;border-right:1px solid rgba(255,255,255,.08)}
    .mobile-filter-sidebar.open{left:0}
  }
  @media (min-width:1024px){ .lg-hidden{display:none!important} }

  /* === Pagination === */
  .pager{display:inline-flex;align-items:center;gap:10px;background:#1f2937;border:1px solid rgba(255,255,255,.06);border-radius:9999px;padding:6px 10px;box-shadow:inset 0 8px 20px rgba(0,0,0,.35),0 4px 14px rgba(0,0,0,.25)}
  .pager-btn{width:44px;height:44px;display:grid;place-items:center;border-radius:9999px;border:1px solid rgba(255,255,255,.15);transition:.15s}
  .pager-prev{background:#e5e7eb;color:#111}
  .pager-next{background:#2563eb;color:#fff}
  .pager-label{min-width:90px;text-align:center;color:#e5e7eb;font-weight:600}
  .pager-btn[aria-disabled="true"]{opacity:.45;pointer-events:none}
  @media(max-width:640px){ .pager-btn{width:40px;height:40px} .pager-label{min-width:80px;font-size:.9rem} }

  /* === Card === */
  .ev-card{background:#1f1f1f;border:1px solid rgba(255,255,255,.1);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;height:100%;transition:.25s ease}
  .ev-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(0,0,0,.35)}
  .ev-thumb{background:#2b2b2b;height:11rem;position:relative;overflow:hidden}
  .ev-body{flex:1;display:flex;flex-direction:column;padding:1rem}
  .ev-title{font-weight:600;font-size:15px}

  /* === Image loader & fallback === */
  .img-wrapper{position:relative;width:100%;height:100%;background:#141414;overflow:hidden}
  .img-wrapper img{width:100%;height:100%;object-fit:cover;display:block;opacity:0;transition:opacity .28s ease}
  .img-wrapper img.loaded{opacity:1}
  .img-loading{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;background:#151515;color:#9ca3af;z-index:1}
  .img-loading.hidden{display:none}
  .spinner{width:28px;height:28px;border:3px solid rgba(130,130,130,.25);border-top-color:#9ca3af;border-radius:50%;animation:spin .8s linear infinite}
  @keyframes spin{to{transform:rotate(360deg)}}
  .camera-icon{width:24px;height:24px;opacity:.6}
  .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
</style>
@endpush

@section('content')
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    /**
     * Gambar fisik disimpan di:
     *   /home/xanderbilliard.site/public_html/images/event
     *
     * Secara URL publik menjadi:
     *   https://xanderbilliard.site/images/event/{filename}
     *
     * Di Laravel: asset('images/event/{filename}')
     */
    $EVENT_IMG_FE_BASE = rtrim(asset('images/event'), '/') . '/';

    /**
     * Normalisasi nilai dari DB ke URL gambar:
     * - Jika sudah http/https → pakai langsung
     * - Jika path fisik /home/.../images/event/... → ambil basename lalu gabung ke $EVENT_IMG_FE_BASE
     * - Jika relatif (images/event/foo.jpg, /images/event/foo.jpg, foo.jpg, dll)
     *   → ambil basename lalu gabung ke $EVENT_IMG_FE_BASE
     */
    $normalizeToEventsFE = function (?string $u) use ($EVENT_IMG_FE_BASE) {
        if (!$u) return null;
        $u = trim($u);

        // Sudah full URL
        if (Str::startsWith($u, ['http://','https://'])) {
            return $u;
        }

        // Path fisik server
        if (Str::startsWith($u, ['/home/xanderbilliard.site/public_html/images/event'])) {
            $basename = basename($u);
            return $basename ? $EVENT_IMG_FE_BASE . $basename : null;
        }

        // Path relatif yang mengarah ke folder event
        if (
            Str::startsWith($u, ['images/event/', '/images/event/']) ||
            Str::contains($u, '/images/event/')
        ) {
            $basename = basename($u);
            return $basename ? $EVENT_IMG_FE_BASE . $basename : null;
        }

        // Hanya nama file atau path lain → pakai basename
        $basename = basename(str_replace('\\', '/', $u));
        if ($basename === '' || $basename === '/' || $basename === '.') {
            return null;
        }

        return $EVENT_IMG_FE_BASE . $basename;
    };

    /** Range tanggal rapi */
    $dateRange = function ($start, $end) {
        $s = $start ? Carbon::parse($start) : null;
        $e = $end ? Carbon::parse($end) : null;
        if ($s && $e) {
            if ($s->isSameMonth($e) && $s->year === $e->year) {
                return $s->format('F j') . '–' . $e->format('j, Y');
            }
            return $s->format('M j, Y') . ' - ' . $e->format('M j, Y');
        }
        return $s ? $s->format('M j, Y') : ($e ? $e->format('M j, Y') : '-');
    };

    /** URL detail event */
    $eventDetailUrl = fn($e) => route('events.show', ['event' => $e->id, 'name' => Str::slug($e->name)]);

    /**
     * Build kandidat URL gambar (prioritas folder /images/event)
     * Urutan:
     * 1) Normalisasi dari image_url/image (termasuk path fisik /home/.../images/event)
     * 2) FE base + {id}.webp/jpg/png
     * 3) Jika image_url absolut → tambahkan (jika belum)
     * 4) Fallback placeholder lokal & remote
     */
    $buildEventImageCandidates = function($e) use ($EVENT_IMG_FE_BASE, $normalizeToEventsFE) {
        $cand = [];

        $raw = $e->image_url ?? $e->image ?? null;
        if ($raw) {
            $norm = $normalizeToEventsFE($raw);
            if ($norm) $cand[] = $norm;
        }

        // Jika DB simpan path, coba juga dari basename
        if ($raw) {
            $basename = basename(parse_url($raw, PHP_URL_PATH) ?? $raw);
            if ($basename) {
                $normBase = $normalizeToEventsFE($basename);
                if ($normBase) $cand[] = $normBase;
            }
        }

        // Berdasarkan ID event (jika file disimpan sebagai {id}.ext)
        $id = $e->id ?? null;
        if ($id) {
            $cand[] = $EVENT_IMG_FE_BASE . $id . '.webp';
            $cand[] = $EVENT_IMG_FE_BASE . $id . '.jpg';
            $cand[] = $EVENT_IMG_FE_BASE . $id . '.png';
        }

        // Jika raw adalah URL absolut eksternal → tambahkan di belakang
        if ($raw && Str::startsWith($raw, ['http://','https://'])) {
            $cand[] = $raw;
        }

        // Fallback lokal & remote
        $cand[] = $EVENT_IMG_FE_BASE . 'placeholder.png';
        $cand[] = asset('images/community/community-1.png');
        $cand[] = 'https://placehold.co/800x500?text=Event';

        // Bersihkan & unik
        return array_values(array_unique(array_filter($cand)));
    };
@endphp

<div class="min-h-screen bg-neutral-900 text-white"
     style="background-image:url('{{ asset('images/bg/background_1.png') }}');background-size:cover;background-position:center;background-repeat:no-repeat;">

  {{-- HERO DESKTOP --}}
  <div class="mb-16 bg-cover bg-center p-24 sm-hidden"
       style="background-image:url('{{ asset('images/bg/product_breadcrumb.png') }}');">
    <p class="text-sm text-gray-400 mt-1">
      <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Events
    </p>
    <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR NEXT CHALLENGE HERE</h2>
  </div>

  {{-- HERO MOBILE/TABLET --}}
  <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden"
       style="background-image:url('{{ asset('images/bg/product_breadcrumb.png') }}');">
    <p class="text-xs sm:text-sm text-gray-400 mt-1">
      <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Events
    </p>
    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">
      FIND YOUR NEXT CHALLENGE HERE
    </h2>
  </div>

  {{-- MOBILE FILTER BUTTON --}}
  <div class="lg-hidden px-4 mb-4">
    <button id="mobileFilterBtn"
            class="w-full bg-transparent border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-400/20">
      <i class="fas fa-filter"></i>
      Filter & Search
    </button>
  </div>
  <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

  <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 lg:py-12">
    {{-- FILTER SIDEBAR --}}
    <aside id="filterEvent"
           class="mobile-filter-sidebar lg:static lg:col-span-1 lg:block lg:bg-transparent lg:border-0">
      <div class="px-4 lg:px-0 space-y-6 text-white text-sm">
        <div class="flex items-center justify-between mb-4 lg-hidden">
          <h3 class="text-lg font-semibold">Filter & Search</h3>
          <button id="closeMobileFilter" class="text-2xl text-gray-400 hover:text-white">&times;</button>
        </div>

        <form method="GET" action="{{ route('events.list') }}" class="space-y-4">
          {{-- SEARCH --}}
          <div>
            <input type="text" name="q" placeholder="Search events..." value="{{ request('q') }}"
              class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm placeholder-gray-400 focus:ring-1 focus:ring-blue-500" />
          </div>

          {{-- STATUS --}}
          <div>
            <div class="font-semibold border-b border-gray-500 pb-1 mb-2">Status</div>
            @foreach (['Upcoming', 'Ongoing', 'Ended'] as $st)
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio"
                       name="status"
                       value="{{ strtolower($st) }}"
                       {{ strtolower(request('status')) == strtolower($st) ? 'checked' : '' }}
                       class="accent-blue-600" />
                <span>{{ $st }}</span>
              </label>
            @endforeach
          </div>

          {{-- GAME TYPE --}}
          <div>
            <div class="font-semibold border-b border-gray-500 pb-1 mb-2">Game Type</div>
            @foreach ($gameTypes as $gt)
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio"
                       name="game_type"
                       value="{{ $gt }}"
                       {{ request('game_type') == $gt ? 'checked' : '' }}
                       class="accent-blue-600" />
                <span>{{ $gt }}</span>
              </label>
            @endforeach
          </div>

          {{-- REGION --}}
          <div>
            <div class="font-semibold border-b border-gray-500 pb-1 mb-2">Region</div>
            <select name="region"
                    class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm text-white focus:ring-1 focus:ring-blue-500">
              <option value="" class="bg-neutral-900 text-sm text-white">All Regions</option>
              @foreach ($regions as $r)
                <option value="{{ $r }}"
                        {{ request('region') == $r ? 'selected' : '' }}
                        class="bg-neutral-900 text-sm text-white">
                  {{ $r }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- BUTTONS --}}
          <div class="flex gap-2 pt-2 sticky bottom-0 bg-[#171717] py-3 border-t border-white/10 lg:static lg:bg-transparent lg:border-0">
            <button type="submit"
                    class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
              Apply
            </button>
            <a href="{{ route('events.list') }}"
               class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-center text-blue-500 hover:bg-blue-500 hover:text-white">
              Reset
            </a>
          </div>
        </form>
      </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <section class="lg:col-span-4 flex flex-col gap-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6">
        @forelse ($events as $ev)
          @php
              $srcs  = $buildEventImageCandidates($ev);
              $link  = $eventDetailUrl($ev);
              $range = $dateRange($ev->start_date, $ev->end_date);
              $first = $srcs[0] ?? ($EVENT_IMG_FE_BASE . 'placeholder.png');
          @endphp

          <a href="{{ $link }}" class="group h-full">
            <div class="ev-card">
              <div class="ev-thumb">
                <div class="img-wrapper">
                  <div class="img-loading">
                    <div class="spinner" aria-hidden="true"></div>
                    <div class="sr-only">Loading image...</div>
                  </div>
                  <img
                    class="js-img-fallback"
                    data-srcs='@json($srcs)'
                    data-idx="0"
                    data-lazy-img
                    src="{{ $first }}"
                    alt="{{ $ev->name }}"
                    loading="lazy"
                    decoding="async">
                </div>
              </div>
              <div class="ev-body">
                <h3 class="ev-title line-clamp-2">{{ $ev->name }}</h3>
                <div class="text-gray-300 text-sm mt-2 space-y-1">
                  <div><i class="far fa-clock mr-1"></i>{{ $range }}</div>
                  <div class="line-clamp-1">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $ev->location }}
                  </div>
                  <div class="line-clamp-1">
                    <i class="fas fa-gamepad mr-1"></i>{{ $ev->game_types }}
                  </div>
                </div>
              </div>
            </div>
          </a>
        @empty
          <div class="col-span-full text-center py-12 text-gray-400">
            No events found.
          </div>
        @endforelse
      </div>

      {{-- CUSTOM PAGINATION --}}
      @php
        $current = $events->currentPage();
        $last    = $events->lastPage();
        $prevUrl = $current > 1
            ? $events->appends(request()->query())->url($current - 1)
            : null;
        $nextUrl = $current < $last
            ? $events->appends(request()->query())->url($current + 1)
            : null;
      @endphp

      <div class="flex justify-center mt-8">
        <nav class="pager" aria-label="Pagination">
          @if ($prevUrl)
            <a class="pager-btn pager-prev" href="{{ $prevUrl }}">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7"
                      stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          @else
            <span class="pager-btn pager-prev" aria-disabled="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7"
                      stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          @endif

          <span class="pager-label">{{ $current }} of {{ $last }}</span>

          @if ($nextUrl)
            <a class="pager-btn pager-next" href="{{ $nextUrl }}">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M9 5l7 7-7 7"
                      stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          @else
            <span class="pager-btn pager-next" aria-disabled="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M9 5l7 7-7 7"
                      stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </span>
          @endif
        </nav>
      </div>
    </section>
  </div>
</div>

<script>
  /* ===== Mobile filter ===== */
  document.addEventListener("DOMContentLoaded", () => {
    const mobileFilterBtn = document.getElementById("mobileFilterBtn");
    const filterEvent = document.getElementById("filterEvent");
    const mobileOverlay = document.getElementById("mobileFilterOverlay");
    const closeMobile = document.getElementById("closeMobileFilter");

    function openFilter() {
      if (!filterEvent) return;
      filterEvent.classList.add("open");
      mobileOverlay && mobileOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }
    function closeFilter() {
      if (!filterEvent) return;
      filterEvent.classList.remove("open");
      mobileOverlay && mobileOverlay.classList.remove("active");
      document.body.style.overflow = "";
    }

    mobileFilterBtn && mobileFilterBtn.addEventListener("click", openFilter);
    closeMobile && closeMobile.addEventListener("click", closeFilter);
    mobileOverlay && mobileOverlay.addEventListener("click", closeFilter);
  });

  /* ===== Lazy image + chained fallback ===== */
  (function(){
    function showCameraFallback(loaderEl){
      if(!loaderEl) return;
      loaderEl.classList.remove('hidden');
      loaderEl.innerHTML = `
        <svg class="camera-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M9 2a1 1 0 0 0-.894.553L7.382 4H5a3 3 0 0 0-3 3v9a3 3 0 0 0 3 3h14a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3h-2.382l-.724-1.447A1 1 0 0 0 14 2H9zm3 6a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
        </svg>
        <div class="text-xs text-gray-400">No image available</div>
      `;
    }

    function initLazyImage(img){
      if(!img) return;
      const wrap = img.closest('.img-wrapper');
      const loader = wrap ? wrap.querySelector('.img-loading') : null;

      // On load: fade-in & hide loader
      const onLoad = () => {
        img.classList.add('loaded');
        if(loader) loader.classList.add('hidden');
      };
      img.addEventListener('load', onLoad, { passive:true });

      // List kandidat src dari data-srcs
      let list = [];
      try {
        list = JSON.parse(img.getAttribute('data-srcs') || '[]') || [];
      } catch (e) {
        list = [];
      }
      if (!Array.isArray(list) || list.length === 0) {
        const currentSrc = img.getAttribute('src');
        if (currentSrc) list = [currentSrc];
      }

      let idx = parseInt(img.getAttribute('data-idx') || '0', 10);
      if (isNaN(idx) || idx < 0) idx = 0;

      // On error: coba kandidat berikutnya
      const onError = () => {
        idx++;
        if (idx < list.length) {
          img.setAttribute('data-idx', String(idx));
          img.src = list[idx];
        } else {
          showCameraFallback(loader);
        }
      };
      img.addEventListener('error', onError, { passive:true });

      // Jika gambar sudah tercache
      if (img.complete && img.naturalWidth > 0) onLoad();
    }

    document.addEventListener('DOMContentLoaded', function(){
      document.querySelectorAll('img[data-lazy-img]').forEach(initLazyImage);
    });
  })();
</script>
@endsection
