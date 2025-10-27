@extends('app')
@section('title', $heading . ' - Xander Billiard')

@push('styles')
<style>
  :root {
    color-scheme: dark;
  }

  html,
  body {
    background: #0a0a0a;
    color: #fff;
  }

  #app,
  main {
    background: #0a0a0a;
  }

  /* === Mobile Filter === */
  @media (max-width:1023px) {
    .lg-hidden {
      display: block !important;
    }

    .sm-hidden {
      display: none !important;
    }

    .mobile-filter-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, .5);
      z-index: 40;
      display: none;
    }

    .mobile-filter-overlay.active {
      display: block;
    }

    .mobile-filter-sidebar {
      position: fixed;
      top: 0;
      left: -100%;
      width: 85%;
      max-width: 340px;
      height: 100%;
      background: #171717;
      z-index: 50;
      transition: left .3s ease;
      overflow-y: auto;
      border-right: 1px solid rgba(255, 255, 255, .08);
    }

    .mobile-filter-sidebar.open {
      left: 0;
    }
  }

  @media (min-width:1024px) {
    .lg-hidden {
      display: none !important;
    }
  }

  /* === Pagination === */
  .pager {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #1f2937;
    border: 1px solid rgba(255, 255, 255, .06);
    border-radius: 9999px;
    padding: 6px 10px;
    box-shadow: inset 0 8px 20px rgba(0, 0, 0, .35), 0 4px 14px rgba(0, 0, 0, .25);
  }

  .pager-btn {
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, .15);
    transition: .15s;
  }

  .pager-prev {
    background: #e5e7eb;
    color: #111;
  }

  .pager-next {
    background: #2563eb;
    color: #fff;
  }

  .pager-label {
    min-width: 90px;
    text-align: center;
    color: #e5e7eb;
    font-weight: 600;
  }

  .pager-btn[aria-disabled="true"] {
    opacity: .45;
    pointer-events: none;
  }

  @media(max-width:640px) {
    .pager-btn {
      width: 40px;
      height: 40px
    }

    .pager-label {
      min-width: 80px;
      font-size: .9rem
    }
  }

  /* === Card === */
  .ev-card {
    background: #1f1f1f;
    border: 1px solid rgba(255, 255, 255, .1);
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: .25s ease;
  }

  .ev-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, .35);
  }

  .ev-thumb {
    background: #2b2b2b;
    height: 11rem;
  }

  .ev-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .ev-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem;
  }

  .ev-title {
    font-weight: 600;
    font-size: 15px;
  }
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
if (Str::startsWith($u, ['http://','https://'])) return $u;
return asset($EVENT_IMG_DIR . basename($u));
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
return $s? $s->format('M j, Y') : ($e? $e->format('M j, Y') : '-');
};
$eventDetailUrl = fn($e) => route('events.show', ['event' => $e->id, 'name' => Str::slug($e->name)]);
@endphp

<div class="min-h-screen bg-neutral-900 text-white"
  style="background-image:url('{{ asset('images/bg/background_1.png') }}');background-size:cover;background-position:center;background-repeat:no-repeat;">

  {{-- HERO --}}
  <div class="mb-16 bg-cover bg-center p-24 sm-hidden" style="background-image:url('/images/bg/product_breadcrumb.png');">
    <p class="text-sm text-gray-400 mt-1">
      <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Events
    </p>
    <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR NEXT CHALLENGE HERE</h2>
  </div>
  <div class="mb-8 bg-cover bg-center p-6 sm:p-12 lg-hidden" style="background-image:url('/images/bg/product_breadcrumb.png');">
    <p class="text-xs sm:text-sm text-gray-400 mt-1">
      <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Events
    </p>
    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold uppercase text-white mt-2">FIND YOUR NEXT CHALLENGE HERE</h2>
  </div>
  {{-- MOBILE FILTER BUTTON --}}
  <div class="lg-hidden px-4 mb-4">
    <button id="mobileFilterBtn" class="w-full bg-transparent border border-gray-400 text-white px-4 py-3 rounded-lg font-medium flex items-center justify-center gap-2 hover:bg-gray-400/20">
      <i class="fas fa-filter"></i>
      Filter & Search
    </button>
  </div>
  <div id="mobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

  <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-4 sm:px-6 lg:px-24 py-6 lg:py-12">
    {{-- FILTER SIDEBAR --}}
    <aside id="filterEvent" class="mobile-filter-sidebar lg:static lg:col-span-1 lg:block lg:bg-transparent lg:border-0">
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
              <input type="radio" name="status" value="{{ strtolower($st) }}"
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
              <input type="radio" name="game_type" value="{{ $gt }}" {{ request('game_type') == $gt ? 'checked' : '' }} class="accent-blue-600" />
              <span>{{ $gt }}</span>
            </label>
            @endforeach
          </div>

          {{-- REGION --}}
          <div>
            <div class="font-semibold border-b border-gray-500 pb-1 mb-2">Region</div>
            <select name="region" class="w-full rounded border border-gray-400 bg-transparent px-3 py-2 text-sm text-white focus:ring-1 focus:ring-blue-500">
              <option value="" class="bg-neutral-900 text-sm text-white">All Regions</option>
              @foreach ($regions as $r)
              <option value="{{ $r }}" {{ request('region') == $r ? 'selected' : '' }} class="bg-neutral-900 text-sm text-white">{{ $r }}</option>
              @endforeach
            </select>
          </div>

          {{-- BUTTONS --}}
          <div class="flex gap-2 pt-2 sticky bottom-0 bg-[#171717] py-3 border-t border-white/10 lg:static lg:bg-transparent lg:border-0">
            <button type="submit" class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
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
        $src = $normalizeToEventsDir($ev->image_url);
        $link = $eventDetailUrl($ev);
        $range = $dateRange($ev->start_date, $ev->end_date);
        @endphp
        <a href="{{ $link }}">
          <div class="ev-card">
            <div class="ev-thumb">
              <!-- <img src="{{ $src }}" alt="{{ $ev->name }}" onerror="this.src='https://via.placeholder.com/400x250?text=Event';"> -->
            </div>
            <div class="ev-body">
              <h3 class="ev-title">{{ $ev->name }}</h3>
              <div class="text-gray-300 text-sm mt-2 space-y-1">
                <div><i class="far fa-clock mr-1"></i>{{ $range }}</div>
                <div><i class="fas fa-map-marker-alt mr-1"></i>{{ $ev->location }}</div>
                <div><i class="fas fa-gamepad mr-1"></i>{{ $ev->game_types }}</div>
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
      $last = $events->lastPage();
      $prevUrl = $current > 1 ? $events->appends(request()->query())->url($current - 1) : null;
      $nextUrl = $current < $last ? $events->appends(request()->query())->url($current + 1) : null;
        @endphp
        <div class="flex justify-center mt-8">
          <nav class="pager" aria-label="Pagination">
            @if ($prevUrl)
            <a class="pager-btn pager-prev" href="{{ $prevUrl }}">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            @else
            <span class="pager-btn pager-prev" aria-disabled="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M15 19l-7-7 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            @endif

            <span class="pager-label">{{ $current }} of {{ $last }}</span>

            @if ($nextUrl)
            <a class="pager-btn pager-next" href="{{ $nextUrl }}">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </a>
            @else
            <span class="pager-btn pager-next" aria-disabled="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                <path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            @endif
          </nav>
        </div>
    </section>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const mobileFilterBtn = document.getElementById("mobileFilterBtn");
    const filterEvent = document.getElementById("filterEvent");
    const mobileOverlay = document.getElementById("mobileFilterOverlay");
    const closeMobile = document.getElementById("closeMobileFilter");

    function openFilter() {
      filterEvent.classList.add("open");
      mobileOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }

    function closeFilter() {
      filterEvent.classList.remove("open");
      mobileOverlay.classList.remove("active");
      document.body.style.overflow = "";
    }
    mobileFilterBtn?.addEventListener("click", openFilter);
    closeMobile?.addEventListener("click", closeFilter);
    mobileOverlay?.addEventListener("click", closeFilter);
  });
</script>
@endsection