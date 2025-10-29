@extends('app')
@section('title', $event->name . ' - Xander Billiard')

@section('content')
    <style>
        :root { color-scheme: dark; }

        html, body{
            height:100%;
            min-height:100%;
            background:#0a0a0a;
            overscroll-behavior-y:none;
            overscroll-behavior-x:none;
        }
        #app, main{ background:#0a0a0a; }
        body::before{ content:""; position:fixed; inset:-40vh -40vw; background:#0a0a0a; z-index:-1; pointer-events:none; }
        body{ -webkit-overflow-scrolling:touch; touch-action:pan-y; }
        img{ display:block; background:transparent; }

        /* ===== Modal (Register & Buy Ticket) ===== */
        .modal{ display:none; position:fixed; z-index:9999; inset:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,.7); backdrop-filter:blur(4px); }
        .modal.active{ display:flex; align-items:center; justify-content:center; }
        .modal-content{ background:#1f1f1f; margin:auto; padding:0; border-radius:12px; width:90%; max-width:520px; box-shadow:0 4px 6px rgba(0,0,0,.3); animation:slideDown .3s ease-out; }
        @keyframes slideDown{ from{opacity:0;transform:translateY(-50px);} to{opacity:1;transform:translateY(0);} }
        .modal-header{ padding:20px 24px; border-bottom:1px solid #333; display:flex; justify-content:space-between; align-items:center; }
        .modal-body{ padding:24px; }
        .modal-footer{ padding:16px 24px; border-top:1px solid #333; display:flex; justify-content:flex-end; gap:12px; }
        .close{ color:#aaa; font-size:28px; font-weight:bold; cursor:pointer; transition:color .3s; }
        .close:hover,.close:focus{ color:#fff; }
        .form-group{ margin-bottom:16px; }
        .form-label{ display:block; margin-bottom:8px; font-weight:500; color:#e5e5e5; }
        .form-input,.form-select{ width:100%; padding:12px 14px; background:#2a2a2a; border:1px solid #404040; border-radius:8px; color:#fff; font-size:14px; transition:all .3s; }
        .form-input:focus,.form-select:focus{ outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
        .form-input::placeholder{ color:#666; }
        .btn-primary{ background:#3b82f6; color:#fff; padding:10px 24px; border:none; border-radius:8px; font-weight:500; cursor:pointer; transition:background-color .3s; }
        .btn-primary:hover{ background:#2563eb; }
        .btn-secondary{ background:transparent; color:#9ca3af; padding:10px 24px; border:1px solid #404040; border-radius:8px; font-weight:500; cursor:pointer; transition:all .3s; }
        .btn-secondary:hover{ background:#2a2a2a; color:#fff; }
        .hint{ font-size:12px; color:#9ca3af; }

        /* ===== Unified image loading UI ===== */
        .img-wrapper{ position:relative; width:100%; height:100%; background:#141414; overflow:hidden; }
        .img-wrapper img{ width:100%; height:100%; object-fit:cover; display:block; opacity:0; transition:opacity .28s ease; }
        .img-wrapper img.loaded{ opacity:1; }
        .img-loading{ position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:10px; background:#151515; color:#9ca3af; z-index:1; }
        .img-loading.hidden{ display:none; }
        .spinner{ width:34px; height:34px; border:3px solid rgba(130,130,130,.25); border-top-color:#9ca3af; border-radius:50%; animation:spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .camera-icon{ width:28px; height:28px; opacity:.6; }
        .sr-only{ position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }

        /* ===== Info/Alert cards ===== */
        .card-info{ border:1px solid rgba(59,130,246,.35); background: rgba(59,130,246,.08); }
        .card-warn{ border:1px solid rgba(245,158,11,.35); background: rgba(245,158,11,.08); }
        .badge{ display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:9999px; font-size:.75rem; line-height:1; letter-spacing:.2px; }
        .badge-viewer{ background:rgba(16,185,129,.12); border:1px solid rgba(16,185,129,.35); color:#a7f3d0; }
        .badge-player{ background:rgba(59,130,246,.12); border:1px solid rgba(59,130,246,.35); color:#bfdbfe; }

        /* ===== Annual Pass Toast (BOTTOM) ===== */
        .ap-toast{
            position: fixed;
            left: 50%;
            transform: translateX(-50%);
            bottom: 20px;
            width: min(920px, 96%);
            z-index: 9998;
            pointer-events: none;
            animation: apToastIn .35s ease-out;
        }
        @keyframes apToastIn{
            from{ opacity:0; transform: translateX(-50%) translateY(20px); }
            to{ opacity:1; transform: translateX(-50%) translateY(0); }
        }
        .ap-toast-inner{
            pointer-events: auto;
            border-radius: 16px;
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(59,130,246,.25), transparent 60%),
                radial-gradient(120% 120% at 100% 100%, rgba(16,185,129,.25), transparent 60%),
                linear-gradient(135deg,#0f172a,#0b1020);
            border:1px solid rgba(255,255,255,.08);
            box-shadow:0 18px 60px rgba(0,0,0,.5);
            color:#e5e7eb;
            overflow:hidden;
        }
        .ap-toast-header{
            display:flex; align-items:center; justify-content:space-between;
            padding:12px 16px; border-bottom:1px solid rgba(255,255,255,.08);
        }
        .ap-toast-body{
            padding:16px;
            display:flex; gap:16px; align-items:center; justify-content:space-between; flex-wrap:wrap;
        }
        .ap-title{ font-size:18px; font-weight:800; letter-spacing:.2px; }
        .ap-left{ display:flex; flex-direction:column; gap:6px; min-width: 240px; }
        .ap-row{ display:grid; grid-template-columns:120px 1fr; gap:6px; font-size:14px; }
        .ap-kv{ opacity:.75; }
        .ap-badge{ display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:9999px; font-weight:700; letter-spacing:.2px; font-size:12px; border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.05); }
        .ap-badge.viewer{ color:#a7f3d0; border-color:rgba(16,185,129,.35); background:rgba(16,185,129,.08); }
        .ap-badge.player{ color:#bfdbfe; border-color:rgba(59,130,246,.35); background:rgba(59,130,246,.08); }
        .ap-actions{ display:flex; gap:10px; flex-wrap:wrap; }
        .ap-btn{ appearance:none; border:none; border-radius:10px; padding:10px 14px; font-weight:600; cursor:pointer; }
        .ap-btn.primary{ background:#2563eb; color:#fff; }
        .ap-btn.primary:hover{ background:#1d4ed8; }
        .ap-btn.ghost{ background:transparent; color:#e5e7eb; border:1px solid rgba(255,255,255,.2); }
        .ap-btn.ghost:hover{ background:rgba(255,255,255,.06); }
        .ap-close{ appearance:none; border:none; background:transparent; color:#9ca3af; font-size:18px; cursor:pointer; padding:6px 8px; }
        .ap-toast.closing{ animation: apToastOut .28s ease-in forwards; }
        @keyframes apToastOut{
            from{ opacity:1; transform: translateX(-50%) translateY(0); }
            to{ opacity:0; transform: translateX(-50%) translateY(16px); }
        }

        /* ===== Kartu Annual Pass dengan Icon Billiard (dipakai juga di toast kanan) ===== */
        .ap-card{
            position:relative;
            width:900px;
            aspect-ratio: 90/48;
            border-radius:20px;
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(59,130,246,.28), transparent 60%),
                radial-gradient(120% 120% at 100% 100%, rgba(16,185,129,.28), transparent 60%),
                linear-gradient(135deg,#0f172a,#0b1020);
            border:1px solid rgba(255,255,255,.1);
            box-shadow:0 25px 60px rgba(0,0,0,.5);
            overflow:hidden; 
            color:#e5e7eb;
            display:flex; 
            align-items:center; 
            justify-content:space-between; 
            padding:30px 36px;
        }
        .ap-card-left{ flex: 1; display: flex; flex-direction: column; gap: 8px; z-index: 2; }
        .ap-card-right{ position: relative; width: 280px; height: 280px; display: flex; align-items: center; justify-content: center; z-index: 1; }

        .billiard-icon{ position: relative; width: 240px; height: 240px; opacity: 0.15; filter: drop-shadow(0 0 30px rgba(59,130,246,.4)); }
        .cue-stick{ position: absolute; width: 12px; height: 200px; background: linear-gradient(180deg, rgba(139,92,246,.8) 0%, rgba(219,234,254,.9) 15%, rgba(219,234,254,.9) 85%, rgba(234,179,8,.8) 100% ); border-radius: 6px; top: 50%; left: 50%; transform-origin: center center; }
        .cue-stick:nth-child(1){ transform: translate(-50%, -50%) rotate(-45deg); }
        .cue-stick:nth-child(2){ transform: translate(-50%, -50%) rotate(45deg); }

        .billiard-ball{
            position: absolute; width: 60px; height: 60px; background: radial-gradient(circle at 30% 30%, #1f1f1f, #000);
            border-radius: 50%; top: 50%; left: 50%; transform: translate(-50%, -50%);
            border: 3px solid rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center;
            box-shadow: inset -4px -4px 8px rgba(255,255,255,.1), 0 8px 20px rgba(0,0,0,.6);
        }
        .ball-number{ width: 32px; height: 32px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 18px; color: #000; box-shadow: inset 0 2px 4px rgba(0,0,0,.2); }

        .ap-brand{ font-weight:800; letter-spacing:.5px; font-size:19px; opacity:.95; }
        .ap-bigtitle{ font-size:32px; font-weight:900; line-height:1.1; margin-top:4px; }
        .ap-sub{ font-size:13px; opacity:.8; margin-top:2px; }
        .ap-card-row{ display:grid; grid-template-columns:140px 1fr; gap:8px; margin-top:12px; font-size:14px; }
        .ap-note{ font-size:12px; opacity:.75; margin-top:8px; font-style:italic; }

        .ap-card::before{ content: ''; position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(59,130,246,.15) 0%, transparent 70%); top: -200px; right: -100px; border-radius: 50%; pointer-events: none; }
        .ap-card::after{ content: ''; position: absolute; width: 300px; height: 300px; background: radial-gradient(circle, rgba(16,185,129,.12) 0%, transparent 70%); bottom: -150px; left: -50px; border-radius: 50%; pointer-events: none; }

        /* ===== Tambahan khusus ikon di sisi kanan Toast ===== */
        .ap-right{
            flex:0 0 auto;
            width: 260px;
            height: 160px;
            display:flex;
            align-items:center;
            justify-content:center;
            position: relative;
        }
        .ap-right .billiard-icon{
            width: 200px;
            height: 200px;
            opacity:.22; /* sedikit lebih terlihat di toast */
        }
        .ap-right .cue-stick{ height: 170px; width: 10px; }
        .ap-right .billiard-ball{ width: 52px; height: 52px; }
        .ap-right .ball-number{ width: 28px; height: 28px; font-size:16px; }
        @media (max-width: 640px){
            .ap-right{ display:none; } /* sembunyikan di mobile biar tidak sempit */
        }
    </style>

    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;

        $pretty = Str::slug($event->name);
        $now = Carbon::now();

        $startDate = $event->start_date instanceof Carbon ? $event->start_date->copy()->startOfDay() : Carbon::parse($event->start_date)->startOfDay();
        $endDate   = $event->end_date   instanceof Carbon ? $event->end_date->copy()->startOfDay()   : Carbon::parse($event->end_date)->startOfDay();

        $ticketPrice        = isset($ticket) ? (float)($ticket->price ?? 0)            : (float)($event->price_ticket ?? 0);
        $ticketPricePlayer  = (float)($event->price_ticket_player ?? 0);
        $stockLeft          = isset($ticket) ? (int)($ticket->stock ?? 0)              : (int)($event->stock ?? 0);
        $slotPlayerLeft     = (int)($event->player_slots ?? 0);

        $showRegister  = $now->lt($startDate);
        $showBuyTicket = $now->lt($endDate);

        // Image fallback chain
        $raw = $event->image_url ?? null;
        $rawPath = $raw ? parse_url($raw, PHP_URL_PATH) ?? $raw : null;
        $filename = $rawPath ? basename($rawPath) : null;
        $placeholder = asset('images/placeholder.jpg');

        $imgCandidates = [];
        if ($raw && preg_match('#^https?://#i', $raw)) {
            $host = strtolower(parse_url($raw, PHP_URL_HOST) ?? '');
            if ($host && $host !== 'demo-xanders.ptbmn.id') $imgCandidates[] = $raw;
        }
        if ($raw && !preg_match('#^https?://#i', $raw)) { $imgCandidates[] = asset($raw); }
        if ($filename) {
            $imgCandidates[] = asset('images/events/' . $filename);
            $imgCandidates[] = asset('images/' . $filename);
            $imgCandidates[] = asset('storage/events/' . $filename);
            $imgCandidates[] = asset('storage/' . $filename);
            $imgCandidates[] = asset($filename);
        }
        $imgCandidates[] = $placeholder;
        $imgCandidates[] = 'https://placehold.co/1200x800?text=Event';

        $imgCandidates = array_values(array_unique(array_filter($imgCandidates)));
        $primaryImage = $imgCandidates[0] ?? $placeholder;

        $descSource = $event->description ?? ($event->deskripsi ?? null);
        $descInline = $descSource ? trim(preg_replace('/\s+/', ' ', strip_tags($descSource))) : null;

        $role = auth()->check() ? auth()->user()->roles : null;
    @endphp

    <div class="min-h-screen bg-neutral-900 text-white">
        <!-- HERO / BREADCRUMB -->
        <div class="mb-8 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <nav class="text-sm text-gray-400 mt-1" aria-label="Breadcrumb">
                <a href="{{ route('index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-1 opacity-60">/</span>
                <a href="{{ route('events.index') }}" class="hover:text-white transition">Event</a>
                <span class="mx-1 opacity-60">/</span>
                <span class="text-gray-200" aria-current="page">{{ $event->name }}</span>
            </nav>
            <h2 class="text-4xl font-bold uppercase text-white">{{ $event->name }}</h2>
        </div>

        <!-- ALERTS -->
        <div class="container mx-auto px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-600/15 border border-green-600/40 text-green-300 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-lg bg-red-600/15 border border-red-600/40 text-red-300 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-600/15 border border-red-600/40 text-red-300 px-4 py-3">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="list-disc list-inside text-sm mt-2">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- ===== TOAST ANNUAL PASS (server-rendered) ===== --}}
        @if (session('annual_pass'))
            @php $ap = session('annual_pass'); @endphp
            <div id="annualPassToast" class="ap-toast" role="status" aria-live="polite">
                <div class="ap-toast-inner">
                    <div class="ap-toast-header">
                        <div class="ap-title">Annual Pass {{ $ap['year'] }} diterbitkan</div>
                        <button class="ap-close" type="button" aria-label="Close" onclick="hideAnnualPassToast()">✕</button>
                    </div>
                    <div class="ap-toast-body">
                        <div class="ap-left">
                            <div style="font-weight:800">Xander Billiard</div>
                            <div class="ap-row">
                                <div class="ap-kv">Name</div><div>{{ $ap['name'] }}</div>
                                <div class="ap-kv">Pass No</div><div>{{ $ap['number'] }}</div>
                                <div class="ap-kv">Type</div>
                                <div>
                                    <span class="ap-badge {{ strtolower($ap['type']) === 'viewer' ? 'viewer' : 'player' }}">{{ strtoupper($ap['type']) }}</span>
                                </div>
                                <div class="ap-kv">Event</div><div>{{ $ap['event'] }}</div>
                                <div class="ap-kv">Valid</div>
                                <div>{{ \Carbon\Carbon::parse($ap['valid_from'])->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($ap['valid_to'])->translatedFormat('d M Y') }}</div>
                            </div>
                            <div class="ap-actions" style="margin-top:8px">
                                <button id="btnDownloadPass" class="ap-btn primary" type="button">Download PNG</button>
                            </div>
                            <div class="ap-note">Setelah diunduh, panel ini akan tertutup otomatis.</div>
                        </div>

                        {{-- === NEW: Ikon billiard di sisi kanan toast === --}}
                        <div class="ap-right" aria-hidden="true">
                            <div class="billiard-icon">
                                <div class="cue-stick"></div>
                                <div class="cue-stick"></div>
                                <div class="billiard-ball">
                                    <div class="ball-number">8</div>
                                </div>
                            </div>
                        </div>
                        {{-- === END NEW === --}}
                    </div>
                </div>
            </div>
        @endif

        <!-- Layout utama -->
        <div class="container mx-auto px-8 pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kiri -->
                <div class="lg:col-span-2">
                    <div class="bg-neutral-800 rounded-xl p-6 h-auto">
                        <div class="mb-6 rounded-lg overflow-hidden">
                            <div class="img-wrapper w-full h-auto">
                                <div class="img-loading">
                                    <div class="spinner" aria-hidden="true"></div>
                                    <div class="sr-only">Loading image...</div>
                                </div>
                                <img
                                    src="{{ $primaryImage }}"
                                    alt="{{ $event->name }}"
                                    class="w-full h-auto object-cover rounded-lg js-img-fallback"
                                    data-lazy-img
                                    data-src-candidates='@json($imgCandidates)'
                                    loading="lazy"
                                    decoding="async">
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <h1 class="text-3xl font-bold">{{ $event->name }}</h1>
                                <span
                                    class="px-4 py-1 rounded-full text-sm
                                    @if ($event->status == 'Upcoming') bg-red-600
                                    @elseif($event->status == 'Ongoing') bg-green-600
                                    @else bg-gray-600 @endif text-white">
                                    {{ $event->status }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-neutral-700 p-6 rounded-lg">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Date:</p>
                                    <p class="font-semibold">
                                        {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Location:</p>
                                    <p class="font-semibold">{{ $event->location }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Game Types:</p>
                                    <p class="font-semibold">{{ $event->game_types }}</p>
                                </div>
                            </div>

                            <div class="mb-8">
                                <h3 class="text-xl font-bold mb-4">Registration & Tickets</h3>
                                <div class="space-y-4 bg-neutral-700 p-6 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="font-medium mb-1">Player Registration</p>
                                            <p class="text-gray-300">Until {{ $startDate->copy()->subDay()->format('F d, Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="font-medium mb-1">Ticket Price (Viewer)</p>
                                            <p class="text-gray-300">Rp {{ number_format($ticketPrice, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="font-medium mb-1">Stock Left</p>
                                            <p class="text-gray-300">{{ $stockLeft }}</p>
                                        </div>
                                        <div>
                                            <p class="font-medium mb-1">Ticket Price (Player)</p>
                                            <p class="text-gray-300">Rp {{ number_format($ticketPricePlayer, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="font-medium mb-1">Slot Player Left</p>
                                            <p class="text-gray-300">{{ $slotPlayerLeft }}</p>
                                        </div>
                                    </div>

                                    {{-- ===== BUTTONS ===== --}}
                                    @if ($role == 'user')
                                        <div class="flex flex-wrap items-center gap-3 pt-4">
                                            @if ($showRegister)
                                                @guest
                                                    <a href="{{ route('login') }}"
                                                       class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                                                        <span class="badge badge-player">Player</span>
                                                        Register Player
                                                    </a>
                                                @else
                                                    <button type="button" onclick="openModal('#registrationModal')"
                                                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                                                        <span class="badge badge-player">Player</span>
                                                        Register Player
                                                    </button>
                                                @endguest
                                            @endif

                                            @if ($showBuyTicket)
                                                @guest
                                                    <a href="{{ route('login') }}"
                                                       class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                                                        <span class="badge badge-viewer">Viewer</span>
                                                        Buy Ticket
                                                    </a>
                                                @else
                                                    <button type="button" onclick="openModal('#buyTicketModal')"
                                                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                                                        <span class="badge badge-viewer">Viewer</span>
                                                        Buy Ticket
                                                    </button>
                                                @endguest
                                            @endif

                                            {{-- Tombol untuk menampilkan ulang Annual Pass bila belum didownload --}}
                                            <button id="btnShowAnnualPass" type="button"
                                                class="hidden border border-emerald-500 text-emerald-400 hover:bg-emerald-500 hover:text-white px-6 py-2 rounded-lg font-medium transition-colors inline-flex items-center gap-2"
                                                title="Show your Annual Pass again">
                                                🎟️ Show Annual Pass
                                            </button>
                                        </div>
                                    @endif

                                    <div class="mt-4 rounded-lg p-4 card-info text-blue-100 text-sm leading-relaxed">
                                        <p class="font-semibold mb-2">Catatan penting:</p>
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li><strong>Buy Ticket (Penonton/Viewer)</strong> khusus untuk masuk venue dan menonton pertandingan. <em>Tidak</em> memberi hak bertanding atau akses area pemain.</li>
                                            <li><strong>Register Player (Buy Ticket Player)</strong> adalah pendaftaran pemain berbayar. Termasuk hak bertanding, verifikasi data, dan akses area pemain.</li>
                                            <li>Jika sudah <strong>terdaftar sebagai pemain</strong>, Anda <em>tidak perlu</em> membeli tiket penonton terpisah untuk diri sendiri.</li>
                                            <li>Semua pembelian bersifat mengikuti kuota & jadwal event. Kebijakan refund/exchange mengikuti aturan panitia.</li>
                                            <li>
                                                <strong>Annual Pass</strong>: Berlaku hanya untuk <em>{{ $event->name }}</em> pada tanggal yang tertera,
                                                <strong>tidak dapat dipindahtangankan</strong>, dan wajib ditunjukkan bersama identitas saat check-in.
                                                Setelah pembelian, gunakan tombol <em>“Show Annual Pass”</em> untuk mengunduh kartu (PNG) ke perangkat Anda.
                                                <strong>Simpan file tersebut dengan aman</strong> untuk keperluan verifikasi panitia.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Kanan -->
                <div class="space-y-6">
                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">About the Event</h3>
                        <p class="text-gray-300">
                            The {{ $event->name }}
                            {{ $descInline ? ' ' . $descInline : '' }}
                            <strong>Rp {{ number_format((float) ($event->total_prize_money ?? 0), 0, ',', '.') }}</strong>.
                        </p>
                    </div>

                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Prize Pool & Awards</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="font-medium">Total Prize Pool:</p>
                                <p class="text-gray-300">Rp {{ number_format((float) ($event->total_prize_money ?? 0), 0, ',', '.') }}+</p>
                            </div>
                            <div>
                                <p class="font-medium">Champion:</p>
                                <p class="text-gray-300">Rp {{ number_format((float) ($event->champion_prize ?? 0), 0, ',', '.') }} + National Champion Trophy</p>
                            </div>
                            <div>
                                <p class="font-medium">Runner-up:</p>
                                <p class="text-gray-300">Rp {{ number_format((float) ($event->runner_up_prize ?? 0), 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Third Place:</p>
                                <p class="text-gray-300">{{ number_format((float) ($event->third_place_prize ?? 0), 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Top 8 Finalists:</p>
                                <p class="text-gray-300">Cash prizes & special recognition</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Tournament Format</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="font-medium">Divisions:</p>
                                <p class="text-gray-300">{{ $event->divisions }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Match Style:</p>
                                <p class="text-gray-300">{{ $event->match_style }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Finals:</p>
                                <p class="text-gray-300">{{ $event->finals_format }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('events.bracket', ['event' => $event->id, 'name' => $pretty]) }}"
                               class="block text-center bg-transparent border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                View Tournament Bracket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== MODAL: Registration (role player) ========== --}}
    @auth
        @php $showRegisterModal = $showRegister; @endphp
        @if ($showRegisterModal)
            <div id="registrationModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="text-2xl font-bold text-white">Event Registration (Player)</h2>
                        <span class="close" onclick="closeModal('#registrationModal')">&times;</span>
                    </div>
                    <div class="px-6 pt-4">
                        <div class="rounded-lg p-3 card-warn text-amber-100 text-xs leading-relaxed">
                            <strong>Catatan:</strong> Form ini khusus <strong>pemain</strong> (Buy Ticket Player). Tidak untuk penonton.
                            Setelah terdaftar & terverifikasi, Anda mendapatkan hak bertanding dan tidak perlu membeli tiket penonton untuk diri sendiri.
                        </div>
                    </div>
                    <form id="registrationForm" action="{{ route('events.register', $event->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_from" value="event-registration">
                        <input type="hidden" name="price" value="{{ $event->price_ticket_player ?? 0 }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label" for="username">Username <span class="text-red-500">*</span></label>
                                <input type="text" id="username" name="username" class="form-input" value="{{ old('username', auth()->user()->name ?? '') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" class="form-input" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">Phone Number <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" name="phone" class="form-input" value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                            </div>

                            <div class="form-group mt-4">
                                <label class="form-label">Player Ticket Price</label>
                                <input type="text" class="form-input bg-gray-800 text-white cursor-not-allowed" value="Rp {{ number_format($event->price_ticket_player ?? 0, 0, ',', '.') }}" readonly>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bank_id">Bank <span class="text-red-500">*</span></label>
                                <select id="bank_id" name="bank_id" class="form-select" required>
                                    <option value="">Pilih bank</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id_bank }}">{{ $b->nama_bank }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nomor Rekening (Registration) --}}
                            <div class="form-group">
                                <label class="form-label" for="no_rek_reg">Nomor Rekening Pengirim <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    id="no_rek_reg"
                                    name="no_rek"
                                    class="form-input only-digits"
                                    inputmode="numeric"
                                    pattern="\d{6,25}"
                                    minlength="6"
                                    maxlength="25"
                                    autocomplete="off"
                                    placeholder="Contoh: 1234567890"
                                    value="{{ old('no_rek') }}"
                                    required
                                >
                                <p class="hint mt-2">Masukkan angka saja (6–25 digit). Gunakan rekening yang dipakai untuk transfer.</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bukti_payment">Bukti Pembayaran <span class="text-red-500">*</span></label>
                                <input type="file" id="bukti_payment" name="bukti_payment" accept="image/*" class="form-input" required>
                                <p class="hint mt-2">Format: JPG/PNG/WEBP, maks 2MB.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" onclick="closeModal('#registrationModal')">Cancel</button>
                            <button type="submit" class="btn-primary">Submit Registration</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    {{-- ========== MODAL: Buy Ticket (Viewer) ========== --}}
    @auth
        @if ($showBuyTicket)
            <div id="buyTicketModal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="text-2xl font-bold text-white">Buy Ticket (Viewer)</h2>
                        <span class="close" onclick="closeModal('#buyTicketModal')">&times;</span>
                    </div>
                    <div class="px-6 pt-4">
                        <div class="rounded-lg p-3 card-warn text-amber-100 text-xs leading-relaxed">
                            <strong>Catatan:</strong> Form ini khusus <strong>penonton</strong>. Tiket ini tidak memberikan hak bertanding
                            dan tidak menggantikan pendaftaran pemain. Jika Anda ingin bertanding, gunakan menu <strong>Register Player</strong>.
                        </div>
                    </div>
                    <form id="buyTicketForm" action="{{ route('events.buy', $event->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_from" value="event-buy">
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id ?? '' }}" />
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Ticket Price</label>
                                <input type="text" class="form-input" value="Rp {{ number_format($ticketPrice, 0, ',', '.') }}" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="qty">Quantity <span class="text-red-500">*</span></label>
                                <input type="number" min="1" max="{{ $stockLeft }}" value="1" id="qty" name="qty" class="form-input" required>
                                <p class="hint mt-2">Sisa stok: {{ $stockLeft }}</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bank_id_buy">Bank <span class="text-red-500">*</span></label>
                                <select id="bank_id_buy" name="bank_id" class="form-select" required>
                                    <option value="">Pilih bank</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id_bank }}">{{ $b->nama_bank }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Nomor Rekening (Buy Ticket) --}}
                            <div class="form-group">
                                <label class="form-label" for="no_rek_buy">Nomor Rekening Pengirim <span class="text-red-500">*</span></label>
                                <input
                                    type="text"
                                    id="no_rek_buy"
                                    name="no_rek"
                                    class="form-input only-digits"
                                    inputmode="numeric"
                                    pattern="\d{6,25}"
                                    minlength="6"
                                    maxlength="25"
                                    autocomplete="off"
                                    placeholder="Contoh: 1234567890"
                                    value="{{ old('no_rek') }}"
                                    required
                                >
                                <p class="hint mt-2">Masukkan angka saja (6–25 digit). Gunakan rekening yang dipakai untuk transfer.</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bukti_payment_buy">Bukti Pembayaran <span class="text-red-500">*</span></label>
                                <input type="file" id="bukti_payment_buy" name="bukti_payment" accept="image/*" class="form-input" required>
                                <p class="hint mt-2">Format: JPG/PNG/WEBP, maks 2MB.</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Total Payment</label>
                                <input type="text" id="totalPaymentDisplay" class="form-input" value="Rp {{ number_format($ticketPrice, 0, ',', '.') }}" readonly>
                                <input type="hidden" id="unitPrice" value="{{ $ticketPrice }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" onclick="closeModal('#buyTicketModal')">Cancel</button>
                            <button type="submit" class="btn-primary">Submit Purchase</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <!-- html2canvas untuk Annual Pass -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        function openModal(sel){ const m=document.querySelector(sel); if(!m) return; m.classList.add('active'); document.body.style.overflow='hidden'; }
        function closeModal(sel){ const m=document.querySelector(sel); if(!m) return; m.classList.remove('active'); document.body.style.overflow='auto'; }

        // Lazy image + fallbacks
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

                const onLoad = () => { img.classList.add('loaded'); if(loader) loader.classList.add('hidden'); };
                img.addEventListener('load', onLoad, { passive:true });

                let list = [];
                try { list = JSON.parse(img.getAttribute('data-src-candidates') || img.getAttribute('data-srcs') || '[]') || []; }
                catch(e){ list = []; }
                let idx = parseInt(img.getAttribute('data-idx') || '0', 10);
                if (isNaN(idx)) idx = 0;

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

                if (img.complete && img.naturalWidth > 0) onLoad();
            }
            document.addEventListener('DOMContentLoaded', function(){
                document.querySelectorAll('img[data-lazy-img]').forEach(initLazyImage);
            });
        })();

        // Auto-calc total payment (Viewer ticket)
        (function(){
            const qtyEl = document.getElementById('qty');
            const priceEl = document.getElementById('unitPrice');
            const outEl = document.getElementById('totalPaymentDisplay');
            function formatRupiah(n){ n=Math.floor(n||0); return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.'); }
            function recalc(){
                if(!qtyEl || !priceEl || !outEl) return;
                const qty = Math.max(1, parseInt(qtyEl.value || '1', 10));
                const price = parseFloat(priceEl.value || '0');
                outEl.value = formatRupiah(qty * price);
            }
            qtyEl && qtyEl.addEventListener('input', recalc);
            recalc();
        })();

        // ====== Annual Pass persistence & re-open button logic ======
        (function(){
            const EVENT_ID = {{ $event->id }};
            const AP_KEY = `xb_ap_${EVENT_ID}`;
            const btnShow = document.getElementById('btnShowAnnualPass');

            function validDate(dateStr){
                if(!dateStr) return false;
                const d = new Date(dateStr);
                return !isNaN(d.getTime());
            }

            function loadAP(){
                try{
                    const raw = localStorage.getItem(AP_KEY);
                    if(!raw) return null;
                    const data = JSON.parse(raw);
                    // hide if downloaded or expired
                    if (data.downloaded) return null;
                    if (validDate(data.valid_to)) {
                        const today = new Date(); today.setHours(0,0,0,0);
                        const vt = new Date(data.valid_to); vt.setHours(0,0,0,0);
                        if (vt < today) return null;
                    }
                    return data;
                }catch(e){ return null; }
            }

            function saveAP(ds){
                if(!ds) return;
                const payload = Object.assign({}, ds, { event_id: EVENT_ID, downloaded:false });
                localStorage.setItem(AP_KEY, JSON.stringify(payload));
            }

            function removeAP(){
                localStorage.removeItem(AP_KEY);
            }

            function setShowBtnVisible(show){
                if(!btnShow) return;
                btnShow.classList.toggle('hidden', !show);
            }

            // Build toast DOM (same style) from DS
            function buildToast(ds){
                const existing = document.getElementById('annualPassToast');
                if (existing) existing.remove();

                const wrap = document.createElement('div');
                wrap.id = 'annualPassToast';
                wrap.className = 'ap-toast';
                wrap.setAttribute('role','status');
                wrap.setAttribute('aria-live','polite');

                wrap.innerHTML = `
                  <div class="ap-toast-inner">
                    <div class="ap-toast-header">
                      <div class="ap-title">Annual Pass ${ds?.year || ''} diterbitkan</div>
                      <button class="ap-close" type="button" aria-label="Close">✕</button>
                    </div>
                    <div class="ap-toast-body">
                      <div class="ap-left">
                        <div style="font-weight:800">Xander Billiard</div>
                        <div class="ap-row">
                          <div class="ap-kv">Name</div><div>${ds?.name || 'Guest'}</div>
                          <div class="ap-kv">Pass No</div><div>${ds?.number || ''}</div>
                          <div class="ap-kv">Type</div>
                          <div><span class="ap-badge ${(String(ds?.type||'').toLowerCase()==='viewer')?'viewer':'player'}">${String(ds?.type||'').toUpperCase()}</span></div>
                          <div class="ap-kv">Event</div><div>${ds?.event || 'Event'}</div>
                          <div class="ap-kv">Valid</div><div>{{ \Carbon\Carbon::parse(session('annual_pass.valid_from') ?? now()->toDateString())->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse(session('annual_pass.valid_to') ?? now()->endOfYear()->toDateString())->translatedFormat('d M Y') }}</div>
                        </div>
                        <div class="ap-actions" style="margin-top:8px">
                          <button id="btnDownloadPass" class="ap-btn primary" type="button">Download PNG</button>
                        </div>
                        <div class="ap-note">Setelah diunduh, panel ini akan tertutup otomatis.</div>
                      </div>

                      <!-- NEW: Ikon kanan pada toast (JS-rendered) -->
                      <div class="ap-right" aria-hidden="true">
                        <div class="billiard-icon">
                          <div class="cue-stick"></div>
                          <div class="cue-stick"></div>
                          <div class="billiard-ball">
                            <div class="ball-number">8</div>
                          </div>
                        </div>
                      </div>
                      <!-- END NEW -->
                    </div>
                  </div>
                `;
                document.body.appendChild(wrap);
                wireToastHandlers(wrap, ds);
                return wrap;
            }

            // Hide animation
            window.hideAnnualPassToast = function(){
                const toast = document.getElementById('annualPassToast');
                if(!toast) return;
                toast.classList.add('closing');
                toast.addEventListener('animationend', () => { toast.remove(); }, { once:true });
            };

            // Common: generate PNG and mark downloaded
            async function generateClientPNG(ds){
                const card = document.createElement('div');
                card.className = 'ap-card';
                card.innerHTML = `
                    <div class="ap-card-left">
                        <div class="ap-brand">Xander Billiard</div>
                        <div class="ap-bigtitle">Annual Pass ${ds?.year || ''}</div>
                        <div class="ap-sub">Valid {{ \Carbon\Carbon::parse(session('annual_pass.valid_from') ?? now()->toDateString())->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse(session('annual_pass.valid_to') ?? now()->endOfYear()->toDateString())->translatedFormat('d M Y') }}</div>
                        <div class="ap-card-row">
                            <div class="ap-kv">Name</div><div>${ds?.name || 'Guest'}</div>
                            <div class="ap-kv">Pass No</div><div>${ds?.number || ''}</div>
                            <div class="ap-kv">Type</div><div><span class="ap-badge ${(String(ds?.type||'').toLowerCase()==='viewer')?'viewer':'player'}">${String(ds?.type||'').toUpperCase()}</span></div>
                            <div class="ap-kv">Event</div><div>${ds?.event || 'Event'}</div>
                        </div>
                        <div class="ap-note">Simpan kartu ini untuk verifikasi ke panitia.</div>
                    </div>
                    <div class="ap-card-right">
                        <div class="billiard-icon">
                            <div class="cue-stick"></div>
                            <div class="cue-stick"></div>
                            <div class="billiard-ball">
                                <div class="ball-number">8</div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(card);
                try{
                    const canvas = await html2canvas(card, { scale: 2, backgroundColor: null, logging: false, width: 900 });
                    const dataUrl = canvas.toDataURL('image/png');
                    const a = document.createElement('a');
                    const fname = `AnnualPass-${ds?.year || ''}-${(ds?.type || 'VIEWER').toUpperCase()}-${(ds?.number || 'XB')}.png`;
                    a.href = dataUrl; a.download = fname; a.style.display='none';
                    document.body.appendChild(a); a.click(); a.remove();
                    // mark downloaded & UI
                    markDownloaded();
                } catch(e){
                    console.error('Error generating Annual Pass:', e);
                    alert('Gagal membuat Annual Pass. Silakan coba kembali.');
                } finally {
                    card.remove();
                }
            }

            function markDownloaded(){
                removeAP();
                setShowBtnVisible(false);
                hideAnnualPassToast();
            }

            // Attach handlers to a toast element
            function wireToastHandlers(toastEl, ds){
                const btnClient = toastEl.querySelector('#btnDownloadPass');
                const btnClose  = toastEl.querySelector('.ap-close');

                if(btnClient){
                    btnClient.addEventListener('click', function(){
                        generateClientPNG(ds);
                    });
                }
                if(btnClose){
                    btnClose.addEventListener('click', hideAnnualPassToast);
                }
            }

            // On first render with flash, simpan ke localStorage
            document.addEventListener('DOMContentLoaded', function(){
                // Jika server mengirim flash annual_pass, simpan ke localStorage
                @if (session('annual_pass'))
                    const dsFlash = @json(session('annual_pass'));
                    saveAP(dsFlash);
                    setShowBtnVisible(true);
                    (function(){
                        const toast = document.getElementById('annualPassToast');
                        if (toast) wireToastHandlers(toast, dsFlash);
                    })();
                @else
                    const ds = loadAP();
                    setShowBtnVisible(!!ds);
                @endif

                // Tombol "Show Annual Pass"
                if (btnShow){
                    btnShow.addEventListener('click', function(){
                        const ds = loadAP();
                        if(!ds){
                            setShowBtnVisible(false);
                            return;
                        }
                        buildToast(ds);
                    });
                }
            });
        })();

        // Reopen modal on validation error
        @if ($errors->any())
            @if (old('_from') === 'event-registration')
                window.addEventListener('load', () => openModal('#registrationModal'));
            @endif
            @if (old('_from') === 'event-buy')
                window.addEventListener('load', () => openModal('#buyTicketModal'));
            @endif
        @endif

        // Digits-only helper untuk Nomor Rekening
        (function(){
            function onlyDigitsHandler(e){
                const before = e.target.value;
                const after = before.replace(/\D+/g,'');
                if (before !== after) {
                    const pos = e.target.selectionStart;
                    e.target.value = after;
                    if (typeof pos === 'number') e.target.setSelectionRange(pos - 1, pos - 1);
                }
            }
            document.addEventListener('input', function(e){
                if (e.target && e.target.classList && e.target.classList.contains('only-digits')) {
                    onlyDigitsHandler(e);
                }
            }, { passive: true });
        })();
    </script>
@endsection
