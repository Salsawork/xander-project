{{-- resources/views/blog/show.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog Detail — Xander Billiard</title>
  @vite('resources/css/app.css')
  <style>
    :root { color-scheme: dark; }
    html, body {
      height: 100%;
      min-height: 100%;
      background: #0a0a0a;
      overscroll-behavior-y: none;
      overscroll-behavior-x: none;
    }
    #page-root{ min-height:100svh; }
    #app, main { background: #0a0a0a; }
    body::before{
      content:"";
      position:fixed;
      inset:-40vh -40vw;
      background:#0a0a0a;
      z-index:-1;
      pointer-events:none;
    }
    img{ display:block; background:transparent; }
    .wrap{ max-width:1100px; margin:0 auto; padding:0 16px; }
  </style>
</head>
<body class="text-white">
<div id="page-root">
  @include('partials.navbar')

  @php
    use App\Models\Event;
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $slugParam = request()->route('slug');

    // Jika URL seperti "judul-event-123", ambil ID di belakang untuk akurasi
    $id = null;
    if (preg_match('/-(\d+)$/', (string)$slugParam, $m)) {
      $id = (int)$m[1];
    }

    $event = Event::query()
      ->when($id, fn($q)=>$q->where('id',$id))
      ->when(!$id, function($q) use ($slugParam){
          $q->where('slug',$slugParam)
            ->orWhere('name',$slugParam)
            ->orWhere('id',$slugParam);
      })
      ->firstOrFail();

    // Helper tanggal aman
    $sd = null; $ed = null;
    try { $sd = $event->start_date instanceof \Carbon\Carbon ? $event->start_date : ($event->start_date ? Carbon::parse($event->start_date) : null); } catch (\Throwable $th) {}
    try { $ed = $event->end_date   instanceof \Carbon\Carbon ? $event->end_date   : ($event->end_date   ? Carbon::parse($event->end_date)   : null); } catch (\Throwable $th) {}

    // Helper gambar
    $img = !empty($event->image_url)
      ? (Str::startsWith($event->image_url, ['http://','https://','/']) ? $event->image_url : asset($event->image_url))
      : asset('images/placeholder/service.png');
  @endphp

  {{-- ===== HERO / BREADCRUMB (ikuti event detail) ===== --}}
  <div class="mb-8 bg-cover bg-center p-24" style="background-image: url('{{ asset('images/bg/product_breadcrumb.png') }}');">
    <div class="wrap">
      <nav class="text-sm text-gray-400 mt-1" aria-label="Breadcrumb">
        <a href="{{ route('index') }}" class="hover:text-white transition">Home</a>
        <span class="mx-1 opacity-60">/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-white transition">Blog</a>
        <span class="mx-1 opacity-60">/</span>
        <span class="text-gray-200" aria-current="page">{{ $event->name }}</span>
      </nav>
      <h2 class="text-4xl font-bold uppercase text-white mt-3">{{ $event->name }}</h2>
    </div>
  </div>

  {{-- ===== LAYOUT UTAMA (copy pola event detail) ===== --}}
  <div class="container mx-auto px-8 pb-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      {{-- Kiri: konten utama --}}
      <div class="lg:col-span-2">
        <div class="bg-neutral-800 rounded-xl p-6 h-auto">
          {{-- Cover / foto --}}
          <div class="mb-6 rounded-lg overflow-hidden">
            <img
              src="{{ $img }}"
              alt="{{ $event->name }}"
              class="w-full h-auto object-cover rounded-lg"
              onerror="this.onerror=null;this.src='{{ asset('images/placeholder/service.png') }}'">
          </div>

          {{-- Deskripsi dan detail utama --}}
          <div>
            {{-- Judul dan status --}}
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-3xl font-bold">{{ $event->name }}</h1>
              <span class="px-4 py-1 rounded-full text-sm 
                @if(($event->status ?? '') === 'Upcoming') bg-red-600 
                @elseif(($event->status ?? '') === 'Ongoing') bg-green-600 
                @else bg-gray-600 @endif 
                text-white">
                {{ $event->status ?? 'Info' }}
              </span>
            </div>

            {{-- Info dasar dalam grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-neutral-700 p-6 rounded-lg">
              <div>
                <p class="text-gray-400 text-sm mb-1 uppercase">Date:</p>
                <p class="font-semibold">
                  @if($sd && $ed)
                    {{ $sd->format('M d') }} - {{ $ed->format('M d, Y') }}
                  @elseif($sd)
                    {{ $sd->format('M d, Y') }}
                  @elseif($ed)
                    {{ $ed->format('M d, Y') }}
                  @else
                    -
                  @endif
                </p>
              </div>

              <div>
                <p class="text-gray-400 text-sm mb-1 uppercase">Location:</p>
                <p class="font-semibold">{{ $event->location ?? '-' }}</p>
              </div>

              <div>
                <p class="text-gray-400 text-sm mb-1 uppercase">Game Types:</p>
                <p class="font-semibold">{{ $event->game_types ?? '-' }}</p>
              </div>
            </div>

            {{-- Konten berita (gunakan field deskripsi event sebagai isi artikel) --}}
            <article class="prose prose-invert max-w-none">
              {!! nl2br(e($event->description ?? $event->about ?? 'Informasi lengkap akan segera tersedia.')) !!}
            </article>

            {{-- (Opsional) CTA jika ingin selaras dengan event detail --}}
            @if(!empty($event->registration_url) || !empty($event->ticket_url))
              <div class="mt-8 flex flex-wrap gap-4">
                @if(!empty($event->registration_url))
                  <a href="{{ $event->registration_url }}" target="_blank" rel="noopener"
                     class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Register Player
                  </a>
                @endif
                @if(!empty($event->ticket_url))
                  <a href="{{ $event->ticket_url }}" target="_blank" rel="noopener"
                     class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                    Buy Ticket
                  </a>
                @endif
              </div>
            @endif
          </div>
        </div>
      </div>

      {{-- Kanan: sidebar info (meniru event detail) --}}
      <div class="space-y-6">
        {{-- About the Event/News --}}
        <div class="bg-neutral-800 rounded-xl p-6">
          <h3 class="text-xl font-bold mb-4">About</h3>
          <p class="text-gray-300">
            {{ $event->summary ?? 'Update & highlight terbaru terkait acara ini.' }}
          </p>
        </div>

        {{-- Prize Pool & Awards (dijaga kompatibel jika field tersedia) --}}
        <div class="bg-neutral-800 rounded-xl p-6">
          <h3 class="text-xl font-bold mb-4">Prize Pool & Awards</h3>
          <div class="space-y-2">
            <div>
              <p class="font-medium">Total Prize Pool:</p>
              <p class="text-gray-300">
                @if(!empty($event->total_prize_money))
                  ${{ number_format((float)$event->total_prize_money, 0) }}+
                @else
                  -
                @endif
              </p>
            </div>
            <div>
              <p class="font-medium">Champion:</p>
              <p class="text-gray-300">
                @if(!empty($event->champion_prize))
                  ${{ number_format((float)$event->champion_prize, 0) }}
                @else
                  -
                @endif
              </p>
            </div>
            <div>
              <p class="font-medium">Runner-up:</p>
              <p class="text-gray-300">
                @if(!empty($event->runner_up_prize))
                  ${{ number_format((float)$event->runner_up_prize, 0) }}
                @else
                  -
                @endif
              </p>
            </div>
            <div>
              <p class="font-medium">Third Place:</p>
              <p class="text-gray-300">
                @if(!empty($event->third_place_prize))
                  ${{ number_format((float)$event->third_place_prize, 0) }}
                @else
                  -
                @endif
              </p>
            </div>
            <div>
              <p class="font-medium">Top 8 Finalists:</p>
              <p class="text-gray-300">Cash prizes & special recognition</p>
            </div>
          </div>
        </div>

        {{-- Tournament Format (jika ada) --}}
        <div class="bg-neutral-800 rounded-xl p-6">
          <h3 class="text-xl font-bold mb-4">Tournament Format</h3>
          <div class="space-y-2">
            <div>
              <p class="font-medium">Divisions:</p>
              <p class="text-gray-300">{{ $event->divisions ?? '-' }}</p>
            </div>
            <div>
              <p class="font-medium">Match Style:</p>
              <p class="text-gray-300">{{ $event->match_style ?? '-' }}</p>
            </div>
            <div>
              <p class="font-medium">Finals:</p>
              <p class="text-gray-300">{{ $event->finals_format ?? '-' }}</p>
            </div>
          </div>
          @if(Route::has('events.bracket'))
          <div class="mt-4">
            <a href="{{ route('events.bracket', $event) }}"
               class="block text-center bg-transparent border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg font-medium transition-colors">
              View Tournament Bracket
            </a>
          </div>
          @endif
        </div>

        {{-- Broadcast & Live (opsional) --}}
        <div class="bg-neutral-800 rounded-xl p-6">
          <h3 class="text-xl font-bold mb-4">Broadcast & Live Streaming</h3>
          <p class="text-gray-300 mb-4">
            Catch the action live on major sports networks and online streaming platforms.
          </p>
          <p class="text-gray-300 mb-4">
            Whether you're here to compete, watch, or learn, {{ $event->name }} promises a memorable experience.
          </p>
          @if(!empty($event->social_media_handle))
          <p class="text-gray-300">
            Follow for updates:
            <a href="https://twitter.com/{{ $event->social_media_handle }}" class="text-blue-400 hover:underline">
              {{ $event->social_media_handle }}
            </a>
          </p>
          @endif
        </div>
      </div>
    </div>
  </div>

  @include('partials.footer')
</div>
</body>
</html>
