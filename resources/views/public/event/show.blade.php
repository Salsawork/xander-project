@extends('app')
@section('title', 'All Events - Xander Billiard')

@push('styles')
<style>
  :root { color-scheme: dark; }
  html, body { background:#0a0a0a; }

  /* Utility for responsive hide */
  @media (min-width: 1024px){ .lg-hidden{ display:none !important; } }
  @media (max-width: 1023px){ .lg-only{ display:none !important; } }

  /* ====== HERO ====== */
  .hero{
    background:
      linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.75)),
      url('{{ asset('images/bg/product_breadcrumb.png') }}'); /* <- diganti */
    background-size: cover; background-position: center;
    padding: 56px 0; color:#fff;
  }
  .hero .wrap{ max-width: 1120px; margin:0 auto; padding:0 20px; }
  .breadcrumb{ font-size: 13px; color:#bbb; margin-bottom: 10px; }
  .hero h1{ font-weight: 800; font-size: clamp(28px, 4.8vw, 48px); letter-spacing:.4px; }

  /* Napas ekstra di mobile */
  @media (max-width:1023px){
    .hero{ padding-bottom: 64px; }
  }

  /* ====== LAYOUT ====== */
  .page-wrap{
    max-width: 1280px; margin: 0 auto; padding: 16px 20px 60px;
    display:grid; grid-template-columns: 260px 1fr; gap: 32px;
  }
  @media (max-width: 1023px){ .page-wrap{ grid-template-columns: 1fr; } }

  /* ====== DESKTOP SIDEBAR ====== */
  .sidebar{ position: sticky; top: 80px; align-self: start; }
  .search-box{
    width:100%; padding:10px 14px; border-radius:8px;
    background:#111; border:1px solid #3a3a3a; color:#fff; outline: none;
  }
  .filter-section{ margin-top: 22px; }
  .filter-section h3{
    font-size: 14px; font-weight: 700; margin-bottom: 10px; color:#e7e7e7;
    display:flex; align-items:center; justify-content:space-between;
  }
  .filter-option{ display:flex; align-items:center; gap:8px; color:#ddd; font-size: 14px; margin:6px 0; }
  .filter-option input{ accent-color:#d4af37; }
  .dropdown{
    width:100%; background:#111; border:1px solid #3a3a3a; color:#fff;
    padding:10px 12px; border-radius:8px;
  }
  .filter-actions{ display:flex; gap:10px; margin-top: 16px; }
  .btn{
    padding:10px 16px; border-radius:8px; cursor:pointer; font-weight:600; border:1px solid transparent;
    transition: .2s ease;
  }
  .btn-primary{ background:#0b6bcb; color:#fff; }
  .btn-primary:hover{ background:#095ba9; }
  .btn-secondary{ background:transparent; color:#0b6bcb; border-color:#0b6bcb; }
  .btn-secondary:hover{ background:#0b6bcb; color:#fff; }

  /* ====== GRID ====== */
  .event-grid{
    display:grid; gap: 22px;
    grid-template-columns: repeat(2,minmax(0,1fr));
  }
  @media (max-width: 1024px){ .event-grid{ grid-template-columns: 1fr; } }

  .ev-card{
    background:#1a1a1a; border:1px solid rgba(255,255,255,.12);
    border-radius:12px; overflow:hidden; transition:.25s ease;
  }
  .ev-card:hover{ transform: translateY(-3px); box-shadow:0 12px 28px rgba(0,0,0,.35); }
  .ev-thumb{ height: 210px; background:#2b2b2b; position:relative; display:grid; place-items:center; overflow:hidden; }
  .ev-thumb img{ width:100%; height:100%; object-fit:cover; display:block; }
  .ev-thumb.featured::after{
    content:""; position:absolute; inset:0;
    background: radial-gradient(60% 60% at 50% 50%, rgba(0,0,0,.0), rgba(0,0,0,.65));
  }
  .badge-center{
    position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center;
  }
  .badge-title{ font-size:42px; line-height:1; letter-spacing:3px; color:#d4af37; font-weight:900; text-transform:uppercase; }
  .badge-sub{ font-size:28px; font-style:italic; margin-top: -6px; color:#fff; }
  .badge-date{ margin-top: 8px; color:#d4af37; font-weight:700; font-size:14px; }

  .ev-body{ padding:16px; }
  .ev-title{ font-size:16px; font-weight:800; line-height:1.35; margin-bottom: 10px; }
  .ev-line{ border:0; border-top:1px solid #333; margin: 12px 0; }
  .ev-meta{ display:grid; gap:8px; color:#bdbdbd; font-size: 13px; }
  .ev-meta i{ width: 18px; color:#fff; }

  /* ====== PAGINATION ====== */
  .pagination-wrap{ margin-top: 26px; display:flex; justify-content:center; }

  /* ====== MOBILE FILTER (overlay + drawer) ====== */
  @media (max-width:1023px){
    .mobile-trigger-wrap{ margin-top: 40px; }
    .mobile-filter-overlay{
      position: fixed; inset:0; background: rgba(0,0,0,.5);
      z-index: 40; display:none;
    }
    .mobile-filter-overlay.active{ display:block; }
    .mobile-filter-sidebar{
      position: fixed; top:0; left:-100%;
      width: 85%; max-width: 320px; height: 100%;
      background: #171717; z-index: 50; color:#fff;
      transition: left .3s ease; overflow-y:auto; -webkit-overflow-scrolling:touch;
      border-right: 1px solid rgba(255,255,255,.08);
      padding: 16px;
    }
    .mobile-filter-sidebar.open{ left:0; }
    .section-title{
      display:flex; align-items:center; justify-content:space-between;
      margin: 12px 0 6px; font-weight: 700; border-bottom:1px solid #3f3f3f; padding-bottom:6px;
    }
    .toggleBtn{ cursor:pointer; font-size: 18px; color:#cfcfcf; }
    .max-h-0{ max-height:0 !important; overflow:hidden !important; }
  }
</style>
@endpush

@section('content')
@php
  use Illuminate\Support\Str;
  use Carbon\Carbon;

  $resolveImage = function ($path) {
      if (empty($path)) return null;
      return Str::startsWith($path, ['http://','https://','/']) ? $path : asset($path);
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

  $q       = request('q');
  $status  = request('status');
  $gtype   = request('game_type');
  $region  = request('region');
@endphp

  <!-- HERO -->
  <section class="hero">
    <div class="wrap">
      <div class="breadcrumb">
        <a href="{{ route('index') }}" class="hover:underline text-white/80">Home</a> / Event
      </div>
      <h1>FIND YOUR NEXT CHALLENGE HERE</h1>
    </div>
  </section>

  <!-- MOBILE filter trigger -->
  <div class="mobile-trigger-wrap lg-hidden px-4 mb-4">
    <button id="evMobileFilterBtn" class="w-full bg-transparent rounded border border-gray-400 text-white px-4 py-3 font-medium flex items-center justify-center gap-2 hover:bg-gray-400/20">
      <i class="fas fa-filter"></i> Filter & Search
    </button>
  </div>
  <div id="evMobileFilterOverlay" class="mobile-filter-overlay lg-hidden"></div>

  <!-- BODY -->
  <div class="page-wrap">
    <!-- SIDEBAR (DESKTOP) -->
    <aside class="sidebar lg-only">
      <form action="{{ route('events.list') }}" method="GET">
        <input type="text" class="search-box" name="q" value="{{ $q }}" placeholder="Search">

        <div class="filter-section">
          <h3>Status Tournament <span>—</span></h3>
          <label class="filter-option">
            <input type="radio" name="status" value="upcoming" {{ $status==='upcoming' ? 'checked' : '' }}>
            <span>Upcoming</span>
          </label>
          <label class="filter-option">
            <input type="radio" name="status" value="ongoing" {{ $status==='ongoing' ? 'checked' : '' }}>
            <span>Ongoing</span>
          </label>
          <label class="filter-option">
            <input type="radio" name="status" value="ended" {{ $status==='ended' ? 'checked' : '' }}>
            <span>Ended</span>
          </label>
          <label class="filter-option">
            <input type="radio" name="status" value="" {{ empty($status) ? 'checked' : '' }}>
            <span>Any</span>
          </label>
        </div>

        <div class="filter-section">
          <h3>Game Type</h3>
          <select class="dropdown" name="game_type">
            <option value="">All types</option>
            @foreach (($gameTypes ?? ['9-Ball','8-Ball','10-Ball']) as $gt)
              <option value="{{ $gt }}" {{ $gtype===$gt ? 'selected' : '' }}>{{ $gt }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-section">
          <h3>Region</h3>
          <select class="dropdown" name="region">
            <option value="">All regions</option>
            @foreach (($regions ?? ['Los Angeles, CA','New York, NY','Chicago, IL']) as $rg)
              <option value="{{ $rg }}" {{ $region===$rg ? 'selected' : '' }}>{{ $rg }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-actions">
          <button class="btn btn-primary" type="submit">Filter</button>
          <a class="btn btn-secondary" href="{{ route('events.list') }}">Reset</a>
        </div>
      </form>
    </aside>

    <!-- SIDEBAR (MOBILE DRAWER) -->
    <aside id="evMobileFilter" class="mobile-filter-sidebar lg-hidden">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-semibold">Filter & Search</h3>
        <button id="evCloseMobileFilter" class="text-2xl text-gray-400 hover:text-white" aria-label="Close filter">&times;</button>
      </div>

      <form action="{{ route('events.list') }}" method="GET">
        <div class="mb-3">
          <input type="text" class="search-box" name="q" value="{{ $q }}" placeholder="Search">
        </div>

        <div class="section-title">
          <span>Status Tournament</span>
          <span class="toggleBtn">–</span>
        </div>
        <div class="toggleContent">
          <label class="filter-option">
            <input type="radio" name="status" value="upcoming" {{ $status==='upcoming' ? 'checked' : '' }}>
            <span>Upcoming</span>
          </label>
          <label class="filter-option">
            <input type="radio" name="status" value="ongoing" {{ $status==='ongoing' ? 'checked' : '' }}>
            <span>Ongoing</span>
          </label>
          <label class="filter-option">
            <input type="radio" name="status" value="ended" {{ $status==='ended' ? 'checked' : '' }}>
            <span>Ended</span>
          </label>
          <label class="filter-option">
            <input type="radio" name="status" value="" {{ empty($status) ? 'checked' : '' }}>
            <span>Any</span>
          </label>
        </div>

        <div class="section-title">
          <span>Game Type</span>
          <span class="toggleBtn">–</span>
        </div>
        <div class="toggleContent">
          <select class="dropdown" name="game_type">
            <option value="">All types</option>
            @foreach (($gameTypes ?? ['9-Ball','8-Ball','10-Ball']) as $gt)
              <option value="{{ $gt }}" {{ $gtype===$gt ? 'selected' : '' }}>{{ $gt }}</option>
            @endforeach
          </select>
        </div>

        <div class="section-title">
          <span>Region</span>
          <span class="toggleBtn">–</span>
        </div>
        <div class="toggleContent">
          <select class="dropdown" name="region">
            <option value="">All regions</option>
            @foreach (($regions ?? ['Los Angeles, CA','New York, NY','Chicago, IL']) as $rg)
              <option value="{{ $rg }}" {{ $region===$rg ? 'selected' : '' }}>{{ $rg }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-actions" style="position:sticky; bottom:0; background:#171717; padding:12px 0;">
          <button class="btn btn-primary" type="submit" style="flex:1">Filter</button>
          <a class="btn btn-secondary" href="{{ route('events.list') }}" style="flex:1; text-align:center">Reset</a>
        </div>
      </form>
      <div style="height:12px"></div>
    </aside>

    <!-- GRID LIST -->
    <section>
      @if (!empty($heading))
        <h2 class="text-xl font-extrabold mb-3">{{ $heading }}</h2>
      @endif

      <div class="event-grid">
        {{-- FEATURED --}}
        @isset($featured)
          @php
            $fImg   = $resolveImage($featured->image_url ?? null);
            $fRange = $dateRange($featured->start_date ?? null, $featured->end_date ?? null);
            $fUrl   = route('events.show', ['event' => $featured->id, 'name' => Str::slug($featured->name)]);
          @endphp
          <a href="{{ $fUrl }}" class="ev-card" style="grid-column: span 2;">
            <div class="ev-thumb featured" style="height:260px;">
              @if ($fImg)
                <img src="{{ $fImg }}" alt="{{ $featured->name }}" onerror="this.style.display='none'">
              @else
                <i class="far fa-image" style="font-size:60px;color:#666;"></i>
              @endif
              <div class="badge-center">
                <div class="badge-title">BILLIARDS</div>
                <div class="badge-sub">Tournament</div>
                <div class="badge-date">{{ $fRange }}</div>
              </div>
            </div>
            <div class="ev-body">
              <div class="ev-title">{{ $featured->name }}</div>
              <hr class="ev-line">
              <div class="ev-meta">
                <div class="flex items-center gap-2"><i class="far fa-clock"></i><span>{{ $fRange }}</span></div>
                <div class="flex items-center gap-2"><i class="fas fa-map-marker-alt"></i><span class="line-clamp-1">{{ $featured->location }}</span></div>
                <div class="flex items-center gap-2"><i class="fas fa-circle"></i><span>{{ $featured->game_types }}</span></div>
              </div>
            </div>
          </a>
        @endisset

        {{-- LIST EVENTS --}}
        @forelse ($events as $ev)
          @php
            $img   = $resolveImage($ev->image_url ?? null);
            $range = $dateRange($ev->start_date ?? null, $ev->end_date ?? null);
            $url   = route('events.show', ['event' => $ev->id, 'name' => Str::slug($ev->name)]);
          @endphp
          <a href="{{ $url }}" class="ev-card">
            <div class="ev-thumb">
              @if ($img)
                <img src="{{ $img }}" alt="{{ $ev->name }}" onerror="this.style.display='none'">
              @else
                <i class="far fa-image" style="font-size:50px;color:#666;"></i>
              @endif
            </div>
            <div class="ev-body">
              <div class="ev-title">{{ $ev->name }}</div>
              <hr class="ev-line">
              <div class="ev-meta">
                <div class="flex items-center gap-2"><i class="far fa-clock"></i><span>{{ $range }}</span></div>
                <div class="flex items-center gap-2"><i class="fas fa-map-marker-alt"></i><span class="line-clamp-1">{{ $ev->location }}</span></div>
                <div class="flex items-center gap-2"><i class="fas fa-circle"></i><span>{{ $ev->game_types }}</span></div>
              </div>
            </div>
          </a>
        @empty
          <div class="col-span-full text-center text-white/60 py-10">No events found.</div>
        @endforelse
      </div>

      {{-- PAGINATION --}}
      @if (method_exists($events, 'links'))
        <div class="pagination-wrap">
          {{ $events->appends(request()->query())->links() }}
        </div>
      @endif
    </section>
  </div>
@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    // Toggle sections (mobile)
    document.querySelectorAll("#evMobileFilter .toggleBtn").forEach((btn) => {
      const content = btn.closest(".section-title").nextElementSibling;
      btn.addEventListener("click", () => {
        if (content.classList.contains("max-h-0")) {
          content.classList.remove("max-h-0"); btn.textContent = "–";
        } else {
          content.classList.add("max-h-0"); btn.textContent = "+";
        }
      });
    });

    // Drawer open/close (mobile)
    const openBtn   = document.getElementById("evMobileFilterBtn");
    const closeBtn  = document.getElementById("evCloseMobileFilter");
    const drawer    = document.getElementById("evMobileFilter");
    const overlay   = document.getElementById("evMobileFilterOverlay");

    function openDrawer() {
      drawer.classList.add("open");
      overlay.classList.add("active");
      document.body.style.overflow = "hidden";
    }
    function closeDrawer() {
      drawer.classList.remove("open");
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    }

    openBtn?.addEventListener("click", openDrawer);
    closeBtn?.addEventListener("click", closeDrawer);
    overlay?.addEventListener("click", closeDrawer);
  });
</script>
@endpush
