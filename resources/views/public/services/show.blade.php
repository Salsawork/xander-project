@extends('app')
@section('title', $service->title . ' - Xander Billiard')

@push('styles')
<style>
  :root { color-scheme: dark; }
  html, body { height: 100%; background:#0a0a0a; }

  /* Tag chip */
  .chip{
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.35rem .7rem; border-radius:999px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.06);
    font-size:.8rem;
  }

  /* Panel umum */
  .panel{
    border:1px solid rgba(255,255,255,.10);
    background:#1f1f1f;
    border-radius:16px;
  }

  /* Kolom agar panel bisa grow hingga bawah */
  .col-stack{ display:flex; flex-direction:column; min-height:100%; }
  .grow-panel{ flex:1 1 auto; }

  /* Item related (tanpa gambar) */
  .rel-item{
    display:flex; flex-direction:column; gap:.25rem;
    padding:.75rem .5rem; border-radius:.75rem;
    transition:.2s ease; text-decoration:none;
  }
  .rel-item:hover{ background:rgba(255,255,255,.05); }
  .rel-title{ font-weight:600; }
  .rel-desc{ font-size:.85rem; color:rgba(255,255,255,.75); }

  /* CTA */
  .btn{ display:inline-flex; align-items:center; border-radius:.6rem; padding:.6rem 1rem; font-weight:500; }
  .btn-primary{ background:#3b82f6; color:#fff; }
  .btn-primary:hover{ background:#2563eb; }
  .btn-ghost{ border:1px solid rgba(255,255,255,.2); color:#fff; }
  .btn-ghost:hover{ background:rgba(255,255,255,.08); }
</style>
@endpush

@push('scripts')
<script>
  // Samakan posisi vertikal teks "Related Services" dengan judul service (H1)
  function alignRelatedHeading(){
    const title = document.getElementById('svcTitle');
    const rel   = document.getElementById('relTitle');
    if(!title || !rel) return;

    // Desktop saja
    const isDesktop = window.matchMedia('(min-width:1024px)').matches;
    if(!isDesktop){ rel.style.marginTop = ''; return; }

    rel.style.marginTop = ''; // reset
    const titleTop = title.getBoundingClientRect().top + window.scrollY;
    const relTop   = rel.getBoundingClientRect().top   + window.scrollY;
    const delta    = titleTop - relTop;

    if (Math.abs(delta) > 1) rel.style.marginTop = delta + 'px';
  }

  window.addEventListener('load',   alignRelatedHeading, {passive:true});
  window.addEventListener('resize', alignRelatedHeading, {passive:true});
</script>
@endpush

@section('content')
<div class="bg-neutral-900 text-white min-h-screen">

  <!-- Satu section: header + konten; aside disejajarkan dengan TITLE -->
  <section class="relative bg-neutral-900">
    <div class="relative max-w-7xl mx-auto px-6 md:px-16 pt-8 pb-16">
      <!-- items-stretch penting supaya grid item punya tinggi sama -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">

        <!-- Kolom kiri: header + about -->
        <div class="lg:col-span-2 col-stack">
          <a href="{{ route('services.index') }}" class="text-sm text-white/70 hover:text-white">← All Services</a>
          <h1 id="svcTitle" class="mt-3 text-3xl md:text-4xl font-extrabold">{{ $service->title }}</h1>

          @php
            $waNumber = '628123456789'; // ganti nomor WA
            $waText = rawurlencode("Halo, saya ingin request layanan: {$service->title}. Mohon info lebih lanjut. Terima kasih.");
          @endphp

          @if(!empty($service->short_description))
            <p class="mt-3 text-white/85 max-w-2xl">{{ $service->short_description }}</p>
          @endif

          @if(!empty($service->tags))
            <div class="mt-4 flex flex-wrap gap-2">
              @foreach($service->tags as $t)
                <span class="chip">#{{ $t }}</span>
              @endforeach
            </div>
          @endif

          <div class="mt-6 flex gap-3">
            <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank" rel="noopener" class="btn btn-primary">
              Request This Service
            </a>
            <a href="{{ route('guideline.index') }}" class="btn btn-ghost">
              Read Guidelines
            </a>
          </div>

          <!-- About -->
          <h2 class="mt-10 text-xl font-semibold mb-3">About This Service</h2>
          <div class="panel p-5 md:p-6 grow-panel">
            <div class="prose prose-invert max-w-none">
              {!! nl2br(e($service->description ?? 'Detail layanan akan segera tersedia.')) !!}
            </div>
          </div>
        </div>

        <!-- Kolom kanan: judul related DI LUAR panel (disejajarkan) + panel grow hingga bawah -->
        <aside class="lg:col-span-1 col-stack">
          <h3 id="relTitle" class="text-lg font-semibold mb-3">Related Services</h3>
          <div class="panel p-5 md:p-6 grow-panel">
            <ul class="space-y-2">
              @foreach($related as $r)
                <li>
                  <a href="{{ route('services.show', $r->slug) }}" class="rel-item">
                    <span class="rel-title">{{ $r->title }}</span>
                    @if(!empty($r->short_description))
                      <span class="rel-desc">{{ $r->short_description }}</span>
                    @endif
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        </aside>

      </div>
    </div>
  </section>

</div>
@endsection
