@extends('app')
@section('title', $service->title . ' - Xander Billiard')

@push('styles')
<style>
  :root { color-scheme: dark; }

  /* ===== Anti-overscroll (atas-bawah) & cegah tepi hitam/putih ===== */
  html, body {
    height: 100%;
    background:#0a0a0a;
    overscroll-behavior-y: none;
    overscroll-behavior-x: none;
    overflow-x: hidden;
    -webkit-text-size-adjust: 100%;
  }
  body::before{
    content:"";
    position: fixed;
    inset: -50vh;
    background:#0a0a0a;
    z-index:-1;
    pointer-events:none;
  }

  /* ===== (Asli) ===== */
  html, body { background:#0a0a0a; }
  .chip{ display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .7rem; border-radius:999px;
         border:1px solid rgba(255,255,255,.15); background:rgba(255,255,255,.06); font-size:.8rem; }
</style>
@endpush

@push('scripts')
<script>
  // Set --svh agar tinggi viewport akurat pada mobile
  (function(){
    function setSVH(){ document.documentElement.style.setProperty('--svh', (window.innerHeight*0.01)+'px'); }
    setSVH();
    window.addEventListener('resize', setSVH, {passive:true});
    window.addEventListener('orientationchange', setSVH, {passive:true});
  })();
</script>
@endpush

@section('content')
<div class="bg-neutral-900 text-white min-h-screen">
  <!-- Hero -->
  <section class="relative bg-neutral-900">
    <div class="relative max-w-7xl mx-auto px-6 md:px-16 pt-8 pb-4">
      <a href="{{ route('services.index') }}" class="text-sm text-white/70 hover:text-white">← All Services</a>
      <h1 class="mt-3 text-3xl md:text-4xl font-extrabold">{{ $service->title }}</h1>

      <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="rounded-xl overflow-hidden border border-white/10 bg-neutral-800">
          <img src="{{ asset($service->image ?: 'images/placeholder/service.png') }}"
               alt="{{ $service->title }}" class="w-full h-[360px] md:h-[440px] object-cover"
               onerror="this.onerror=null;this.src='{{ asset('images/placeholder/service.png') }}'">
        </div>

        <div class="p-0 lg:p-2">
          <p class="text-white/85">{{ $service->short_description }}</p>

          <div class="mt-4 flex flex-wrap gap-2">
            @if(!empty($service->tags))
              @foreach($service->tags as $t)
                <span class="chip">#{{ $t }}</span>
              @endforeach
            @endif
          </div>

          <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div class="rounded-xl border border-white/10 bg-neutral-800 p-4">
              <div class="text-white/60">Estimasi Harga</div>
              <div class="mt-1 font-semibold">
                @if(!empty($service->price_min))
                  Rp {{ number_format($service->price_min,0,',','.') }}
                  @if(!empty($service->price_max)) - Rp {{ number_format($service->price_max,0,',','.') }} @endif
                @else
                  Custom
                @endif
              </div>
            </div>
            <div class="rounded-xl border border-white/10 bg-neutral-800 p-4">
              <div class="text-white/60">Durasi</div>
              <div class="mt-1 font-semibold">
                @if(!empty($service->duration_min))
                  {{ $service->duration_min }}–{{ $service->duration_max ?? $service->duration_min }} menit
                @else
                  -
                @endif
              </div>
            </div>
          </div>

          <div class="mt-6 flex gap-3">
            <a href="{{ url('/contact') }}"
               class="inline-flex items-center rounded-md bg-blue-500 px-4 py-2 text-sm font-medium hover:bg-blue-600">
              Request This Service
            </a>
            <a href="{{ route('guideline.index') }}"
               class="inline-flex items-center rounded-md border border-white/20 px-4 py-2 text-sm font-medium hover:bg-white/10">
              Read Guidelines
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Description -->
  <section class="mt-10 md:mt-14 max-w-7xl mx-auto px-6 md:px-16 pb-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <h2 class="text-xl font-semibold mb-3">Service Details</h2>
        <div class="prose prose-invert max-w-none">
          {!! nl2br(e($service->description ?? 'Detail layanan akan segera tersedia.')) !!}
        </div>
      </div>

      <aside class="lg:col-span-1">
        <div class="rounded-xl border border-white/10 bg-neutral-800 p-5">
          <h3 class="font-semibold mb-3">Related Services</h3>
          <ul class="space-y-3">
            @foreach($related as $r)
              <li>
                <a href="{{ route('services.show', $r->slug) }}" class="flex gap-3 group">
                  <img src="{{ asset($r->image ?: 'images/placeholder/service.png') }}"
                       class="w-14 h-14 rounded-md object-cover border border-white/10"
                       onerror="this.onerror=null;this.src='{{ asset('images/placeholder/service.png') }}'">
                  <div>
                    <div class="font-medium group-hover:underline">{{ $r->title }}</div>
                    <div class="text-xs text-white/70 line-clamp-2">{{ $r->short_description }}</div>
                  </div>
                </a>
              </li>
            @endforeach
          </ul>
        </div>
      </aside>
    </div>
  </section>
</div>
@endsection
