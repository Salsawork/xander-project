@extends('app')
@section('title', 'Services - Xander Billiard')

@push('styles')
<style>
  :root { color-scheme: dark; }

  /* ===== Anti-overscroll (atas-bawah) & cegah tepi hitam/putih ===== */
  html, body {
    height: 100%;
    background:#0a0a0a;
    overscroll-behavior-y: none;   /* no rubber-band vertical */
    overscroll-behavior-x: none;   /* no horizontal chaining  */
    overflow-x: hidden;
    -webkit-text-size-adjust: 100%;
  }
  /* Kanvas gelap tetap menutup area di luar viewport saat bounce */
  body::before{
    content:"";
    position: fixed;
    inset: -50vh;                  /* lebih besar dari viewport */
    background:#0a0a0a;
    z-index:-1;
    pointer-events:none;
  }

  /* ===== (Asli) ===== */
  html, body { background:#0a0a0a; }
  .svc-card{ background:#161616; border:1px solid #2a2a2a; border-radius:16px; overflow:hidden; transition:.25s ease; }
  .svc-card:hover{ transform:translateY(-4px); box-shadow:0 10px 24px rgba(0,0,0,.35); }
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
<div class="min-h-screen bg-neutral-900 text-white">
  <section class="max-w-7xl mx-auto px-6 md:px-16 py-10 md:py-16">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl md:text-3xl font-bold">Our Services</h1>
      <a href="{{ route('index') }}" class="text-sm text-white/70 hover:text-white">← Back to Home</a>
    </div>

    @if(empty($services))
      <p class="text-white/80">Belum ada layanan.</p>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($services as $svc)
          <a href="{{ route('services.show', $svc->slug) }}" class="svc-card block">
            <div class="relative aspect-[16/10] bg-neutral-800">
              <img src="{{ asset($svc->image ?: 'images/placeholder/service.png') }}"
                   alt="{{ $svc->title }}" class="absolute inset-0 w-full h-full object-cover"
                   onerror="this.onerror=null;this.src='{{ asset('images/placeholder/service.png') }}'">
            </div>
            <div class="p-5">
              <h3 class="text-lg font-semibold mb-1">{{ $svc->title }}</h3>
              <p class="text-sm text-white/70 line-clamp-2">{{ $svc->short_description }}</p>

              <div class="mt-4 flex items-center justify-between text-sm text-white/80">
                <span>
                  @if(!empty($svc->price_min))
                    Rp {{ number_format($svc->price_min,0,',','.') }}
                    @if(!empty($svc->price_max)) - Rp {{ number_format($svc->price_max,0,',','.') }} @endif
                  @else
                    Custom
                  @endif
                </span>
                @if(!empty($svc->duration_min))
                  <span>{{ $svc->duration_min }}–{{ $svc->duration_max ?? $svc->duration_min }} menit</span>
                @endif
              </div>
            </div>
          </a>
        @endforeach
      </div>
    @endif
  </section>
</div>
@endsection
