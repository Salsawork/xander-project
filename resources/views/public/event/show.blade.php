@extends('app')
@section('title', ($event->name ?? 'Event') . ' - Xander Billiard')

@push('styles')
<style>
  :root { color-scheme: dark; }
  html, body {
    min-height: 100%;
    margin: 0;
    background: #0a0a0a;
    overscroll-behavior: none;
  }
  body { overflow-x: hidden; }

  /* ===== Article typography ===== */
  .article-content {
    --c-text: #e5e7eb;
    --c-muted: #9ca3af;
    --c-hr: #1f2937;
    --radius: 14px;
    color: var(--c-text);
    line-height: 1.8;
    font-size: 1rem;
  }
  .article-content h2,
  .article-content h3,
  .article-content h4 {
    font-weight: 800;
    line-height: 1.25;
    margin: 1.25em 0 .6em;
  }
  .article-content h2 { font-size: clamp(1.25rem, 4.5vw, 1.75rem); }
  .article-content h3 { font-size: clamp(1.15rem, 4.2vw, 1.4rem); }
  .article-content h4 { font-size: clamp(1.05rem, 3.8vw, 1.15rem); }
  .article-content p { margin: .9em 0; color: var(--c-text); }
  .article-content hr {
    border: 0;
    border-top: 1px solid var(--c-hr);
    margin: 1.5em 0;
  }

  .container-narrow { max-width: 1200px; margin: 0 auto; }
  .shadow-soft { box-shadow: 0 8px 30px rgba(0,0,0,.35); }
  .img-hero {
    width: 100%;
    display: block;
    border-radius: 16px;
    background: #111827;
    object-fit: cover;
  }
  .sidebar-card {
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
  }
  .pill {
    display: inline-flex; align-items:center; gap:.5rem;
    border-radius: 9999px; padding: .25rem .6rem; font-size:.75rem; font-weight:700;
    background: #1f2937; color: #e5e7eb; border:1px solid rgba(255,255,255,.08);
  }
  .muted { color:#9ca3af; }

  @media (min-width: 1024px){
    .article-content { font-size: 1.06rem; line-height: 1.9; }
    .sticky-desktop { position: sticky; top: 88px; }
  }

  .breadcrumbs a, .breadcrumbs span { white-space: nowrap; }
</style>
@endpush

@section('content')
@php
    use Carbon\Carbon;

    $fmtDate = function ($date) {
        if (empty($date)) return null;
        try {
            $c = $date instanceof Carbon ? $date : Carbon::parse($date);
            return $c->translatedFormat('d F Y');
        } catch (\Throwable $e) { return null; }
    };

    // Resolve hero image
    $hero = null;
    if (!empty($event->image_url)) {
        $raw = trim($event->image_url);
        $hero = preg_match('/^https?:\/\//i', $raw) ? $raw : asset('images/events/' . basename($raw));
    }

    $startDate = $fmtDate($event->start_date ?? null);
    $endDate   = $fmtDate($event->end_date ?? null);
    $dateRange = $startDate && $endDate ? ($startDate . ' — ' . $endDate) : ($startDate ?: ($endDate ?: null));
@endphp

<div class="bg-neutral-900 text-white min-h-screen">
  <!-- Breadcrumbs -->
  <nav class="px-4 sm:px-6 lg:px-24 pt-5 pb-2 text-sm">
    <div class="container-narrow">
      <div class="breadcrumbs flex items-center gap-2 text-gray-400 overflow-x-auto no-scrollbar">
        <a href="{{ route('events.list') }}" class="hover:text-white">Events</a>
        <span>/</span>
        <span class="text-white line-clamp-1">{{ $event->name ?? 'Event' }}</span>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="px-4 sm:px-6 lg:px-24 pt-4">
    <div class="container-narrow">
      <div class="flex flex-wrap items-center gap-3 mb-2">
        @if(!empty($event->status))
          <span class="pill">{{ $event->status }}</span>
        @endif
      </div>
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight mb-2">
        {{ $event->name ?? 'Event' }}
      </h1>
      <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm sm:text-base text-gray-400">
        @if($dateRange)
          <div class="flex items-center gap-2"><i class="far fa-calendar"></i><span>{{ $dateRange }}</span></div>
        @endif
        @if(!empty($event->location))
          <div class="flex items-center gap-2"><i class="fas fa-map-marker-alt"></i><span>{{ $event->location }}</span></div>
        @endif
        @if(!empty($event->game_types))
          <div class="flex items-center gap-2"><i class="fas fa-bullseye"></i><span class="muted">{{ $event->game_types }}</span></div>
        @endif
      </div>
    </div>
  </header>

  <!-- Hero Image -->
  <section class="px-4 sm:px-6 lg:px-24 mt-5 mb-6">
    <div class="container-narrow">
      @if ($hero)
        <img src="{{ $hero }}" alt="{{ $event->name ?? 'Event' }}" class="img-hero shadow-soft" style="max-height: 520px;">
      @else
        <div class="w-full h-[220px] sm:h-[320px] md:h-[420px] bg-gray-800 rounded-2xl grid place-items-center shadow-soft">
          <i class="far fa-image text-5xl text-gray-600"></i>
        </div>
      @endif
    </div>
  </section>

  <!-- Content & Sidebar -->
  <main class="px-4 sm:px-6 lg:px-24 pb-16">
    <div class="container-narrow">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">
        <!-- Main -->
        <article class="lg:col-span-2">
          <div class="article-content">
            {{-- Deskripsi: pertahankan line break --}}
            @if(!empty($event->description))
              <h2 class="text-xl sm:text-2xl font-bold">Tentang Event</h2>
              <div class="text-[0.98rem] sm:text-base text-gray-300 mb-6">
                {!! nl2br(e($event->description)) !!}
              </div>
            @endif

            {{-- Info Ringkas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
              <div class="bg-[#111827] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Biaya Tiket</div>
                <div class="text-lg font-semibold">
                  @php
                    $rp = (int)($event->price_ticket ?? 0);
                    echo $rp > 0 ? 'Rp. ' . number_format($rp,0,',','.') . ',-' : '—';
                  @endphp
                </div>
              </div>
              <div class="bg-[#111827] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Stok Tiket</div>
                <div class="text-lg font-semibold">{{ (int)($event->stock ?? 0) > 0 ? (int)$event->stock : '—' }}</div>
              </div>
              <div class="bg-[#111827] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Divisi</div>
                <div class="text-base">{{ $event->divisions ?: '—' }}</div>
              </div>
              <div class="bg-[#111827] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Sosial Media</div>
                <div class="text-base">{{ $event->social_media_handle ?: '—' }}</div>
              </div>
            </div>

            {{-- Format Pertandingan --}}
            @if(!empty($event->match_style) || !empty($event->finals_format))
              <h3 class="text-lg sm:text-xl font-bold mb-3">Format Pertandingan</h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="bg-gradient-to-b from-[#111827] to-[#0f172a] border border-white/10 rounded-xl p-4">
                  <div class="text-xs text-gray-400 mb-1">Format Umum</div>
                  <div class="font-semibold">{{ $event->match_style ?? '—' }}</div>
                </div>
                <div class="bg-gradient-to-b from-[#111827] to-[#0f172a] border border-white/10 rounded-xl p-4">
                  <div class="text-xs text-gray-400 mb-1">Format Final</div>
                  <div class="font-semibold">{{ $event->finals_format ?? '—' }}</div>
                </div>
              </div>
            @endif

            {{-- Hadiah --}}
            <h3 class="text-lg sm:text-xl font-bold mb-3">Hadiah & Breakdown</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="sm:col-span-3 bg-gradient-to-b from-[#111827] to-[#0f172a] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Total Hadiah</div>
                <div class="text-xl font-extrabold">
                  @php $n=(int)($event->total_prize_money ?? 0); echo $n>0?'Rp. '.number_format($n,0,',','.').',-':'—'; @endphp
                </div>
              </div>
              <div class="bg-gradient-to-b from-[#111827] to-[#0f172a] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Juara 1</div>
                <div class="text-lg font-semibold">
                  @php $n=(int)($event->champion_prize ?? 0); echo $n>0?'Rp. '.number_format($n,0,',','.').',-':'—'; @endphp
                </div>
              </div>
              <div class="bg-gradient-to-b from-[#111827] to-[#0f172a] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Juara 2</div>
                <div class="text-lg font-semibold">
                  @php $n=(int)($event->runner_up_prize ?? 0); echo $n>0?'Rp. '.number_format($n,0,',','.').',-':'—'; @endphp
                </div>
              </div>
              <div class="bg-gradient-to-b from-[#111827] to-[#0f172a] border border-white/10 rounded-xl p-4">
                <div class="text-xs text-gray-400 mb-1">Juara 3</div>
                <div class="text-lg font-semibold">
                  @php $n=(int)($event->third_place_prize ?? 0); echo $n>0?'Rp. '.number_format($n,0,',','.').',-':'—'; @endphp
                </div>
              </div>
            </div>

            <div class="mt-10">
              <a href="{{ route('events.list') }}"
                 class="inline-flex items-center gap-2 rounded-lg px-4 py-2 bg-white/10 hover:bg-white/15 transition ring-1 ring-white/10">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Kembali ke Events</span>
              </a>
            </div>
          </div>
        </article>

        <!-- Sidebar -->
        <aside class="lg:col-span-1">
          <div class="sidebar-card p-5 lg:p-6 sticky-desktop bg-[#1f1f1f] rounded-xl text-white">
            <h3 class="text-lg sm:text-xl font-bold mb-5">Recommended Events</h3>

            @if(!empty($relatedEvents) && $relatedEvents->count())
              <div class="space-y-5">
                @foreach ($relatedEvents as $rel)
                  @php
                    $relImg = null;
                    if (!empty($rel->image_url)) {
                      $rawR = trim($rel->image_url);
                      $relImg = preg_match('/^https?:\/\//i', $rawR) ? $rawR : asset('images/events/' . basename($rawR));
                    }
                    $rs = $fmtDate($rel->start_date ?? null);
                    $re = $fmtDate($rel->end_date ?? null);
                    $rDates = $rs && $re ? ($rs.' — '.$re) : ($rs ?: ($re ?: null));
                  @endphp
                  <div class="flex gap-4">
                    <div class="w-20 h-20 bg-gray-800 rounded-lg overflow-hidden flex-shrink-0">
                      @if ($relImg)
                        <img src="{{ $relImg }}" alt="{{ $rel->name ?? 'Event' }}" class="w-full h-full object-cover">
                      @endif
                    </div>
                    <div class="min-w-0">
                      <a href="{{ route('events.show', ['event' => $rel->id, 'name' => \Illuminate\Support\Str::slug($rel->name)]) }}"
                         class="font-medium text-white/90 hover:text-gray-200 transition line-clamp-2">
                        {{ $rel->name ?? 'Event' }}
                      </a>
                      @if($rDates)
                        <p class="text-[12px] text-gray-400 mt-1">{{ $rDates }}</p>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <p class="text-sm text-gray-400">Belum ada rekomendasi.</p>
            @endif

            <div class="mt-7">
              <a href="{{ route('events.list') }}"
                 class="block w-full text-center rounded-lg bg-[#2a2a2a] hover:bg-[#333] transition px-4 py-2.5 text-white font-medium">
                Lihat Semua Event
              </a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </main>
</div>
@endsection

@push('scripts')
<script>
/* iOS rubber-band guard */
(function () {
  const isIOS = /iP(ad|hone|od)/.test(navigator.userAgent);
  if (!isIOS) return;
  let startY = 0;
  window.addEventListener('touchstart', (e) => {
    if (e.touches && e.touches.length) startY = e.touches[0].clientY;
  }, { passive: true });
  window.addEventListener('touchmove', (e) => {
    if (!e.touches || !e.touches.length) return;
    const scroller = document.scrollingElement || document.documentElement;
    const atTop = scroller.scrollTop <= 0;
    const atBottom = (scroller.scrollTop + window.innerHeight) >= (scroller.scrollHeight - 1);
    const dy = e.touches[0].clientY - startY;
    if ((atTop && dy > 0) || (atBottom && dy < 0)) e.preventDefault();
  }, { passive: false });
})();
</script>
@endpush
