@extends('app')
@section('title', $event->name . ' - Xander Billiard')

@section('content')
    <style>
        :root { color-scheme: dark; }

        /* ===== Base (mobile-first) ===== */
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

        /* ===== Hero (responsive) ===== */
        .hero{
            position:relative;
            background-size:cover;
            background-position:center;
            padding: 40px 16px; /* mobile */
        }
        .hero::after{
            content:"";
            position:absolute; inset:0;
            background: radial-gradient(120% 120% at 0% 0%, rgba(0,0,0,.35), transparent 60%),
                        linear-gradient(180deg, rgba(0,0,0,.45), rgba(0,0,0,.1));
            pointer-events:none;
        }
        .hero-inner{ position:relative; z-index:1; }
        .breadcrumb{
            color:#9ca3af;
            font-size:12px;
            max-width: 92vw;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .hero-title{ font-size:1.625rem; line-height:1.2; font-weight:800; text-transform:uppercase; }
        @media (min-width:640px){
            .hero{ padding: 48px 24px; }
            .hero-title{ font-size:2.25rem; }
        }
        @media (min-width:1024px){
            .hero{ padding: 6rem; }
            .hero-title{ font-size:2.5rem; }
        }

        /* ===== Cards & sections ===== */
        .section-card{ background:#1f1f1f; border-radius:14px; padding:16px; }
        @media (min-width:640px){ .section-card{ padding:20px; } }
        @media (min-width:768px){ .section-card{ padding:24px; } }

        /* ===== Image block (aspect-ratio friendly) ===== */
        .img-wrapper{ position:relative; width:100%; background:#141414; overflow:hidden; border-radius:12px; }
        .media-16x9{ aspect-ratio: 16 / 9; }
        @supports not (aspect-ratio: 16/9){
            .media-16x9{ position:relative; padding-top:56.25%; }
            .media-16x9 > img, .media-16x9 > .img-loading{ position:absolute; inset:0; }
        }
        .img-wrapper img{
            width:100%; height:100%; object-fit:cover; display:block;
            opacity:0; transition:opacity .28s ease;
        }
        .img-wrapper img.loaded{ opacity:1; }
        .img-loading{
            position:absolute; inset:0;
            display:flex; flex-direction:column;
            align-items:center; justify-content:center; gap:10px;
            background:#151515; color:#9ca3af; z-index:1;
        }
        .img-loading.hidden{ display:none; }
        .spinner{
            width:32px; height:32px;
            border:3px solid rgba(130,130,130,.25);
            border-top-color:#9ca3af;
            border-radius:50%;
            animation:spin .8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .camera-icon{ width:26px; height:26px; opacity:.6; }
        .sr-only{
            position:absolute; width:1px; height:1px; padding:0; margin:-1px;
            overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0;
        }

        /* ===== Status pill ===== */
        .status-pill{
            padding:.35rem .75rem;
            border-radius:9999px;
            font-size:.75rem;
            font-weight:700;
        }

        /* ===== Meta row ===== */
        .meta-grid{
            display:grid; gap:12px;
            grid-template-columns: 1fr;
            background:#202226; border-radius:12px; padding:14px;
        }
        .meta-item-title{
            color:#9ca3af;
            font-size:.75rem;
            text-transform:uppercase;
            margin-bottom:4px;
        }
        .meta-item-value{
            font-weight:700;
            font-size:.95rem;
        }
        @media (min-width:480px){
            .meta-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
        }
        @media (min-width:768px){
            .meta-grid{
                grid-template-columns: repeat(3, minmax(0,1fr));
                padding:18px;
            }
        }

        /* ===== Info & warn cards ===== */
        .card-info{
            border:1px solid rgba(59,130,246,.35);
            background: rgba(59,130,246,.08);
            border-radius:10px;
        }
        .card-warn{
            border:1px solid rgba(245,158,11,.35);
            background: rgba(245,158,11,.08);
            border-radius:10px;
        }
        .badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:6px 10px;
            border-radius:9999px;
            font-size:.75rem;
            line-height:1;
            letter-spacing:.2px;
        }
        .badge-viewer{
            background:rgba(16,185,129,.12);
            border:1px solid rgba(16,185,129,.35);
            color:#a7f3d0;
        }
        .badge-player{
            background:rgba(59,130,246,.12);
            border:1px solid rgba(59,130,246,.35);
            color:#bfdbfe;
        }

        /* ===== Buttons ===== */
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:.5rem;
            border-radius:10px;
            padding:10px 16px;
            font-weight:600;
            transition:.25s background-color, .25s color, .25s border-color;
        }
        .btn-primary{ background:#3b82f6; color:#fff; }
        .btn-primary:hover{ background:#2563eb; }
        .btn-outline{
            background:transparent;
            color:#60a5fa;
            border:1px solid #2563eb;
        }
        .btn-outline:hover{ background:#2563eb; color:#fff; }
        .btn-block-sm{ width:100%; }
        @media (min-width:640px){ .btn-block-sm{ width:auto; } }

        /* ===== Modal ===== */
        .modal{
            display:none;
            position:fixed;
            z-index:9999;
            inset:0;
            width:100%;
            height:100%;
            overflow:auto;
            background:rgba(0,0,0,.7);
            backdrop-filter:blur(4px);
        }
        .modal.active{
            display:flex;
            align-items:flex-end;
            justify-content:center;
        }
        .modal-content{
            background:#1f1f1f;
            margin:0 12px 12px;
            padding:0;
            border-radius:16px;
            width:calc(100% - 24px);
            max-width:520px;
            box-shadow:0 8px 24px rgba(0,0,0,.5);
            animation:slideUp .28s ease-out;
            overflow:hidden;
            display:flex;
            flex-direction:column;
            max-height:calc(100svh - 24px);
        }
        @keyframes slideUp{
            from{opacity:0; transform:translateY(24px);}
            to{opacity:1; transform:translateY(0);}
        }
        .modal-header{
            padding:14px 16px;
            border-bottom:1px solid #333;
            display:flex;
            justify-content:space-between;
            align-items:center;
            position:sticky;
            top:0;
            background:#1f1f1f;
            z-index:1;
        }
        .modal-body{ padding:16px; overflow:auto; }
        .modal-footer{
            padding:12px 16px;
            border-top:1px solid #333;
            display:flex;
            gap:10px;
            justify-content:flex-end;
            position:sticky;
            bottom:0;
            background:#1f1f1f;
        }
        .close{
            color:#aaa;
            font-size:22px;
            font-weight:bold;
            cursor:pointer;
            transition:color .2s;
        }
        .close:hover,.close:focus{ color:#fff; }

        .form-group{ margin-bottom:14px; }
        .form-label{
            display:block;
            margin-bottom:6px;
            font-weight:500;
            color:#e5e5e5;
            font-size:14px;
        }
        .form-input,.form-select{
            width:100%;
            padding:12px 14px;
            background:#2a2a2a;
            border:1px solid #404040;
            border-radius:10px;
            color:#fff;
            font-size:14px;
            transition:all .2s;
        }
        .form-input:focus,.form-select:focus{
            outline:none;
            border-color:#3b82f6;
            box-shadow:0 0 0 3px rgba(59,130,246,.1);
        }
        .form-input::placeholder{ color:#666; }
        .btn-secondary{
            background:transparent;
            color:#9ca3af;
            padding:10px 16px;
            border:1px solid #404040;
            border-radius:10px;
            font-weight:600;
        }
        .btn-secondary:hover{ background:#2a2a2a; color:#fff; }
        .hint{ font-size:12px; color:#9ca3af; }

        /* ===== Annual Pass Toast ===== */
        .ap-toast{
            position: fixed;
            left: 50%;
            transform: translateX(-50%);
            bottom: calc(12px + env(safe-area-inset-bottom));
            width: min(920px, 96%);
            z-index: 9998;
            pointer-events: none;
            animation: apToastIn .28s ease-out;
        }
        @keyframes apToastIn{
            from{ opacity:0; transform: translateX(-50%) translateY(10px); }
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
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:10px 12px;
            border-bottom:1px solid rgba(255,255,255,.08);
        }
        .ap-toast-body{
            padding:12px;
            display:flex;
            gap:14px;
            align-items:center;
            justify-content:space-between;
            flex-wrap:wrap;
        }
        .ap-title{
            font-size:16px;
            font-weight:800;
            letter-spacing:.2px;
        }
        .ap-left{
            display:flex;
            flex-direction:column;
            gap:6px;
            min-width: 220px;
        }
        .ap-row{
            display:grid;
            grid-template-columns:110px 1fr;
            gap:6px;
            font-size:13px;
        }
        .ap-kv{ opacity:.75; }
        .ap-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:6px 12px;
            border-radius:9999px;
            font-weight:700;
            letter-spacing:.2px;
            font-size:12px;
            border:1px solid rgba(255,255,255,.14);
            background:rgba(255,255,255,.05);
        }
        .ap-badge.viewer{
            color:#a7f3d0;
            border-color:rgba(16,185,129,.35);
            background:rgba(16,185,129,.08);
        }
        .ap-badge.player{
            color:#bfdbfe;
            border-color:rgba(59,130,246,.35);
            background:rgba(59,130,246,.08);
        }
        .ap-actions{
            display:flex;
            gap:8px;
            flex-wrap:wrap;
        }
        .ap-btn{
            appearance:none;
            border:none;
            border-radius:10px;
            padding:10px 14px;
            font-weight:700;
            cursor:pointer;
        }
        .ap-btn.primary{ background:#2563eb; color:#fff; }
        .ap-btn.primary:hover{ background:#1d4ed8; }
        .ap-btn.ghost{
            background:transparent;
            color:#e5e7eb;
            border:1px solid rgba(255,255,255,.2);
        }
        .ap-btn.ghost:hover{ background:rgba(255,255,255,.06); }
        .ap-close{
            appearance:none;
            border:none;
            background:transparent;
            color:#9ca3af;
            font-size:18px;
            cursor:pointer;
            padding:6px 8px;
        }
        .ap-toast.closing{ animation: apToastOut .22s ease-in forwards; }
        @keyframes apToastOut{
            from{ opacity:1; transform: translateX(-50%) translateY(0); }
            to{ opacity:0; transform: translateX(-50%) translateY(12px); }
        }

        .ap-right{
            flex:0 0 auto;
            width: 240px;
            height: 140px;
            display:flex;
            align-items:center;
            justify-content:center;
            position: relative;
        }
        .billiard-icon{
            position: relative;
            width: 200px;
            height: 200px;
            opacity:.18;
            filter: drop-shadow(0 0 30px rgba(59,130,246,.4));
        }
        .cue-stick{
            position: absolute;
            width: 10px;
            height: 170px;
            background:
                linear-gradient(180deg,
                    rgba(139,92,246,.8) 0%,
                    rgba(219,234,254,.9) 15%,
                    rgba(219,234,254,.9) 85%,
                    rgba(234,179,8,.8) 100%);
            border-radius: 6px;
            top: 50%;
            left: 50%;
            transform-origin: center center;
        }
        .cue-stick:nth-child(1){
            transform: translate(-50%, -50%) rotate(-45deg);
        }
        .cue-stick:nth-child(2){
            transform: translate(-50%, -50%) rotate(45deg);
        }
        .billiard-ball{
            position: absolute;
            width: 52px;
            height: 52px;
            background: radial-gradient(circle at 30% 30%, #1f1f1f, #000);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 3px solid rgba(255,255,255,.2);
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:
                inset -4px -4px 8px rgba(255,255,255,.1),
                0 8px 20px rgba(0,0,0,.6);
        }
        .ball-number{
            width: 28px;
            height: 28px;
            background: white;
            border-radius: 50%;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:900;
            font-size:16px;
            color:#000;
            box-shadow: inset 0 2px 4px rgba(0,0,0,.2);
        }
        @media (max-width: 640px){
            .ap-right{ display:none; }
        }

        .container-pad{ padding-left:16px; padding-right:16px; }
        @media (min-width:640px){
            .container-pad{ padding-left:32px; padding-right:32px; }
        }
        @media (min-width:1024px){
            .container-pad{ padding-left:2rem; padding-right:2rem; }
        }
    </style>

    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;

        $pretty = Str::slug($event->name);
        $now = Carbon::now();

        $startDate = $event->start_date instanceof Carbon
            ? $event->start_date->copy()->startOfDay()
            : Carbon::parse($event->start_date)->startOfDay();

        $endDate = $event->end_date instanceof Carbon
            ? $event->end_date->copy()->startOfDay()
            : Carbon::parse($event->end_date)->startOfDay();

        $ticketPrice       = isset($ticket) ? (float)($ticket->price ?? 0) : (float)($event->price_ticket ?? 0);
        $ticketPricePlayer = (float)($event->price_ticket_player ?? 0);
        $stockLeft         = isset($ticket) ? (int)($ticket->stock ?? 0)   : (int)($event->stock ?? 0);
        $slotPlayerLeft    = (int)($event->player_slots ?? 0);

        $showRegister  = $now->lt($startDate);
        $showBuyTicket = $now->lt($endDate);

        /**
         * Folder fisik:
         *   /home/xanderbilliard.site/public_html/images/event/{filename}
         *
         * URL publik:
         *   https://xanderbilliard.site/images/event/{filename}
         *
         * Di Blade:
         *   asset('images/event/'.$filename)
         */
        $EVENT_IMG_FE_BASE = rtrim(asset('images/event'), '/') . '/';

        /**
         * Normalisasi nilai dari DB menjadi URL:
         * - Jika http/https -> pakai langsung
         * - Jika mengandung /images/event/ -> pakai basename lalu gabung ke FE base
         * - Selain itu -> ambil basename lalu FE base + basename
         */
        $normalizeToEventsUrl = function (?string $u) use ($EVENT_IMG_FE_BASE) {
            if (!$u) return null;
            $u = trim($u);

            if (preg_match('#^https?://#i', $u)) {
                return $u;
            }

            if (\Illuminate\Support\Str::startsWith($u, ['/images/event/'])) {
                $basename = basename($u);
                return $basename ? $EVENT_IMG_FE_BASE . $basename : null;
            }

            $basename = basename(str_replace('\\', '/', $u));
            if ($basename === '' || $basename === '.' || $basename === '/') {
                return null;
            }

            return $EVENT_IMG_FE_BASE . $basename;
        };

        /**
         * Build kandidat URL gambar utama event dengan prioritas folder /images/event
         */
        $raw      = $event->image_url ?? $event->image ?? null;
        $rawPath  = $raw ? (parse_url($raw, PHP_URL_PATH) ?? $raw) : null;
        $filename = $rawPath ? basename($rawPath) : null;
        $eventId  = $event->id ?? 'unknown';

        $placeholderFe    = $EVENT_IMG_FE_BASE . 'placeholder.png';
        $placeholderLocal = asset('images/event/placeholder.png');

        $imgCandidates = [];

        // 1) Dari DB (normalize ke /images/event jika bukan URL penuh)
        if ($raw) {
            $norm = $normalizeToEventsUrl($raw);
            if ($norm) $imgCandidates[] = $norm;
        }

        // 2) FE base + basename (jika DB simpan nama file saja)
        if ($filename) {
            $norm = $normalizeToEventsUrl($filename);
            if ($norm) $imgCandidates[] = $norm;
        }

        // 3) FE base + id.(webp|jpg|png)
        if ($eventId) {
            $imgCandidates[] = $EVENT_IMG_FE_BASE . $eventId . '.webp';
            $imgCandidates[] = $EVENT_IMG_FE_BASE . $eventId . '.jpg';
            $imgCandidates[] = $EVENT_IMG_FE_BASE . $eventId . '.png';
        }

        // 4) Jika DB sudah URL absolut eksternal, tambahkan di belakang (jaga-jaga)
        if ($raw && preg_match('#^https?://#i', $raw)) {
            $imgCandidates[] = $raw;
        }

        // 5) Cadangan lokal lain
        if ($filename) {
            $imgCandidates[] = asset('images/event/' . $filename);
            $imgCandidates[] = asset('images/' . $filename);
            $imgCandidates[] = asset('storage/event/' . $filename);
            $imgCandidates[] = asset('storage/' . $filename);
            $imgCandidates[] = asset($filename);
        }

        // 6) Placeholder
        $imgCandidates[] = $placeholderFe;
        $imgCandidates[] = $placeholderLocal;
        $imgCandidates[] = asset('images/community/community-1.png');
        $imgCandidates[] = 'https://placehold.co/1200x800?text=Event';

        $imgCandidates = array_values(array_unique(array_filter($imgCandidates)));
        $primaryImage  = $imgCandidates[0] ?? $placeholderFe ?? $placeholderLocal;

        $descSource = $event->description ?? ($event->deskripsi ?? null);
        $descInline = $descSource
            ? trim(preg_replace('/\s+/', ' ', strip_tags($descSource)))
            : null;

        $role = auth()->check() ? auth()->user()->roles : null;
    @endphp

    <div class="min-h-screen bg-neutral-900 text-white">
        <!-- HERO / BREADCRUMB -->
        <div class="hero mb-6 sm:mb-8" style="background-image: url('{{ asset('images/bg/product_breadcrumb.png') }}');">
            <div class="hero-inner">
                <nav class="breadcrumb mb-2" aria-label="Breadcrumb">
                    <a href="{{ route('index') }}" class="hover:text-white transition">Home</a>
                    <span class="mx-1 opacity-60">/</span>
                    <a href="{{ route('events.index') }}" class="hover:text-white transition">Event</a>
                    <span class="mx-1 opacity-60">/</span>
                    <span class="text-gray-200" aria-current="page">{{ $event->name }}</span>
                </nav>
                <h2 class="hero-title">{{ $event->name }}</h2>
            </div>
        </div>

        <!-- ALERTS -->
        <div class="container mx-auto container-pad">
            @if (session('success'))
                <div class="mb-4 sm:mb-6 rounded-lg bg-green-600/15 border border-green-600/40 text-green-300 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 sm:mb-6 rounded-lg bg-red-600/15 border border-red-600/40 text-red-300 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 sm:mb-6 rounded-lg bg-red-600/15 border border-red-600/40 text-red-300 px-4 py-3">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="list-disc list-inside text-sm mt-2">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Annual Pass Toast (server-rendered, jika ada) --}}
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
                                    <span class="ap-badge {{ strtolower($ap['type']) === 'viewer' ? 'viewer' : 'player' }}">
                                        {{ strtoupper($ap['type']) }}
                                    </span>
                                </div>
                                <div class="ap-kv">Event</div><div>{{ $ap['event'] }}</div>
                                <div class="ap-kv">Valid</div>
                                <div>
                                    {{ \Carbon\Carbon::parse($ap['valid_from'])->translatedFormat('d M Y') }}
                                    —
                                    {{ \Carbon\Carbon::parse($ap['valid_to'])->translatedFormat('d M Y') }}
                                </div>
                            </div>
                            <div class="ap-actions" style="margin-top:6px">
                                <button id="btnDownloadPass" class="ap-btn primary" type="button">Download PNG</button>
                            </div>
                            <div class="ap-note text-xs opacity-80 mt-1">
                                Setelah diunduh, panel ini akan tertutup otomatis.
                            </div>
                        </div>

                        <div class="ap-right" aria-hidden="true">
                            <div class="billiard-icon">
                                <div class="cue-stick"></div>
                                <div class="cue-stick"></div>
                                <div class="billiard-ball">
                                    <div class="ball-number">8</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Layout utama -->
        <div class="container mx-auto container-pad pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kiri (main) -->
                <div class="lg:col-span-2">
                    <div class="section-card">
                        <!-- Image -->
                        <div class="mb-4 sm:mb-6 rounded-lg overflow-hidden">
                            <div class="img-wrapper media-16x9">
                                <div class="img-loading">
                                    <div class="spinner" aria-hidden="true"></div>
                                    <div class="sr-only">Loading image...</div>
                                </div>
                                <img
                                    src="{{ $primaryImage }}"
                                    alt="{{ $event->name }}"
                                    class="w-full h-full object-cover rounded-lg js-img-fallback"
                                    data-lazy-img
                                    data-src-candidates='@json($imgCandidates)'
                                    loading="lazy"
                                    decoding="async"
                                >
                            </div>
                        </div>

                        <!-- Title + status -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                            <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight">
                                {{ $event->name }}
                            </h1>
                            <span class="status-pill
                                @if ($event->status == 'Upcoming') bg-red-600
                                @elseif ($event->status == 'Ongoing') bg-green-600
                                @else bg-gray-600 @endif
                                text-white">
                                {{ $event->status }}
                            </span>
                        </div>

                        <!-- Meta -->
                        <div class="meta-grid mb-6">
                            <div>
                                <p class="meta-item-title">Date</p>
                                <p class="meta-item-value">
                                    {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="meta-item-title">Location</p>
                                <p class="meta-item-value">{{ $event->location }}</p>
                            </div>
                            <div>
                                <p class="meta-item-title">Game Types</p>
                                <p class="meta-item-value">{{ $event->game_types }}</p>
                            </div>
                        </div>

                        <!-- Registration & Tickets -->
                        <div class="mb-6 sm:mb-8">
                            <h3 class="text-lg sm:text-xl font-extrabold mb-3 sm:mb-4">
                                Registration & Tickets
                            </h3>
                            <div class="space-y-4 section-card" style="background:#202226">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div>
                                        <p class="font-semibold mb-1 text-sm">Player Registration</p>
                                        <p class="text-gray-300 text-sm">
                                            Until {{ $startDate->copy()->subDay()->format('F d, Y') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-1 text-sm">Ticket Price (Viewer)</p>
                                        <p class="text-gray-300 text-sm">
                                            Rp {{ number_format($ticketPrice, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-1 text-sm">Stock Left</p>
                                        <p class="text-gray-300 text-sm">{{ $stockLeft }}</p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-1 text-sm">Ticket Price (Player)</p>
                                        <p class="text-gray-300 text-sm">
                                            Rp {{ number_format($ticketPricePlayer, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="font-semibold mb-1 text-sm">Slot Player Left</p>
                                        <p class="text-gray-300 text-sm">
                                            {{ $slotPlayerLeft }}
                                        </p>
                                    </div>
                                </div>

                                @if ($role == 'user')
                                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 pt-2">
                                        @if ($showRegister)
                                            @guest
                                                <a href="{{ route('login') }}"
                                                   class="btn btn-primary btn-block-sm">
                                                    <span class="badge badge-player">Player</span>
                                                    Register Player
                                                </a>
                                            @else
                                                <button type="button"
                                                        onclick="openModal('#registrationModal')"
                                                        class="btn btn-primary btn-block-sm">
                                                    <span class="badge badge-player">Player</span>
                                                    Register Player
                                                </button>
                                            @endguest
                                        @endif

                                        @if ($showBuyTicket)
                                            @guest
                                                <a href="{{ route('login') }}"
                                                   class="btn btn-primary btn-block-sm">
                                                    <span class="badge badge-viewer">Viewer</span>
                                                    Buy Ticket
                                                </a>
                                            @else
                                                <button type="button"
                                                        onclick="openModal('#buyTicketModal')"
                                                        class="btn btn-primary btn-block-sm">
                                                    <span class="badge badge-viewer">Viewer</span>
                                                    Buy Ticket
                                                </button>
                                            @endguest
                                        @endif

                                        <button id="btnShowAnnualPass" type="button"
                                                class="hidden btn btn-outline btn-block-sm"
                                                title="Show your Annual Pass again">
                                            🎟️ Show Annual Pass
                                        </button>
                                    </div>
                                @endif

                                <div class="mt-3 rounded-lg p-4 card-info text-blue-100 text-sm leading-relaxed">
                                    <p class="font-semibold mb-2">Catatan penting:</p>
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li><strong>Buy Ticket (Penonton/Viewer)</strong> khusus untuk masuk venue dan menonton pertandingan. <em>Tidak</em> memberi hak bertanding.</li>
                                        <li><strong>Register Player (Buy Ticket Player)</strong> adalah pendaftaran pemain berbayar dengan hak bertanding.</li>
                                        <li>Jika sudah <strong>terdaftar sebagai pemain</strong>, tidak perlu membeli tiket penonton untuk diri sendiri.</li>
                                        <li>Semua pembelian mengikuti kuota & jadwal event serta kebijakan panitia.</li>
                                        <li><strong>Annual Pass</strong> berlaku khusus untuk event ini, tidak dapat dipindahtangankan, dan wajib ditunjukkan saat check-in.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- /Registration & Tickets -->
                    </div>
                </div>

                <!-- Kanan (sidebar) -->
                <div class="space-y-6">
                    <div class="section-card">
                        <h3 class="text-lg sm:text-xl font-extrabold mb-3 sm:mb-4">About the Event</h3>
                        <p class="text-gray-300 text-sm sm:text-base">
                            The {{ $event->name }}
                            {{ $descInline ? ' ' . $descInline : '' }}
                            <strong>
                                Rp {{ number_format((float) ($event->total_prize_money ?? 0), 0, ',', '.') }}
                            </strong>.
                        </p>
                    </div>

                    <div class="section-card">
                        <h3 class="text-lg sm:text-xl font-extrabold mb-3 sm:mb-4">Prize Pool & Awards</h3>
                        <div class="space-y-2 text-sm sm:text-base">
                            <div>
                                <p class="font-semibold">Total Prize Pool:</p>
                                <p class="text-gray-300">
                                    Rp {{ number_format((float) ($event->total_prize_money ?? 0), 0, ',', '.') }}+
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Champion:</p>
                                <p class="text-gray-300">
                                    Rp {{ number_format((float) ($event->champion_prize ?? 0), 0, ',', '.') }}
                                    + National Champion Trophy
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Runner-up:</p>
                                <p class="text-gray-300">
                                    Rp {{ number_format((float) ($event->runner_up_prize ?? 0), 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Third Place:</p>
                                <p class="text-gray-300">
                                    Rp {{ number_format((float) ($event->third_place_prize ?? 0), 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <p class="font-semibold">Top 8 Finalists:</p>
                                <p class="text-gray-300">Cash prizes & special recognition</p>
                            </div>
                        </div>
                    </div>

                    <div class="section-card">
                        <h3 class="text-lg sm:text-xl font-extrabold mb-3 sm:mb-4">Tournament Format</h3>
                        <div class="space-y-2 text-sm sm:text-base">
                            <div>
                                <p class="font-semibold">Divisions:</p>
                                <p class="text-gray-300">{{ $event->divisions }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Match Style:</p>
                                <p class="text-gray-300">{{ $event->match_style }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Finals:</p>
                                <p class="text-gray-300">{{ $event->finals_format }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('events.bracket', ['event' => $event->id, 'name' => $pretty]) }}"
                               class="btn btn-outline btn-block-sm text-center">
                                View Tournament Bracket
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /sidebar -->
            </div>
        </div>
    </div>

    {{-- MODAL: Registration (Player) --}}
    @auth
        @php $showRegisterModal = $showRegister; @endphp
        @if ($showRegisterModal)
            <div id="registrationModal" class="modal" aria-hidden="true">
                <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="regTitle">
                    <div class="modal-header">
                        <h2 id="regTitle" class="text-xl sm:text-2xl font-extrabold text-white">
                            Event Registration (Player)
                        </h2>
                        <button class="close" type="button" aria-label="Close"
                                onclick="closeModal('#registrationModal')">&times;</button>
                    </div>

                    <div class="px-4 pt-3">
                        <div class="rounded-lg p-3 card-warn text-amber-100 text-xs leading-relaxed">
                            <strong>Catatan:</strong> Form ini khusus <strong>pemain</strong> (Buy Ticket Player).
                            Setelah terverifikasi, Anda mendapatkan hak bertanding dan tidak perlu tiket penonton terpisah.
                        </div>
                    </div>

                    <form id="registrationForm"
                          action="{{ route('events.register', $event->id) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_from" value="event-registration">
                        <input type="hidden" name="price" value="{{ $event->price_ticket_player ?? 0 }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label" for="username">
                                    Username <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="username" name="username"
                                       class="form-input"
                                       value="{{ old('username', auth()->user()->name ?? '') }}"
                                       required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="email">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="email" name="email"
                                       class="form-input"
                                       value="{{ old('email', auth()->user()->email ?? '') }}"
                                       required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="phone" name="phone"
                                       class="form-input"
                                       value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                       required>
                            </div>

                            <div class="form-group mt-3">
                                <label class="form-label">Player Ticket Price</label>
                                <input type="text"
                                       class="form-input bg-gray-800 text-white cursor-not-allowed"
                                       value="Rp {{ number_format($event->price_ticket_player ?? 0, 0, ',', '.') }}"
                                       readonly>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bank_id">
                                    Bank <span class="text-red-500">*</span>
                                </label>
                                <select id="bank_id" name="bank_id" class="form-select" required>
                                    <option value="">Pilih bank</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id_bank }}">{{ $b->nama_bank }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="no_rek_reg">
                                    Nomor Rekening Pengirim <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
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
                                       required>
                                <p class="hint mt-2">
                                    Masukkan angka saja (6–25 digit). Gunakan rekening yang dipakai untuk transfer.
                                </p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bukti_payment">
                                    Bukti Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <input type="file"
                                       id="bukti_payment"
                                       name="bukti_payment"
                                       accept="image/*"
                                       class="form-input"
                                       required>
                                <p class="hint mt-2">
                                    Format: JPG/PNG/WEBP, maks 2MB.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button"
                                    class="btn-secondary"
                                    onclick="closeModal('#registrationModal')">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Submit Registration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    {{-- MODAL: Buy Ticket (Viewer) --}}
    @auth
        @if ($showBuyTicket)
            <div id="buyTicketModal" class="modal" aria-hidden="true">
                <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="buyTitle">
                    <div class="modal-header">
                        <h2 id="buyTitle" class="text-xl sm:text-2xl font-extrabold text-white">
                            Buy Ticket (Viewer)
                        </h2>
                        <button class="close" type="button" aria-label="Close"
                                onclick="closeModal('#buyTicketModal')">&times;</button>
                    </div>
                    <div class="px-4 pt-3">
                        <div class="rounded-lg p-3 card-warn text-amber-100 text-xs leading-relaxed">
                            <strong>Catatan:</strong> Form ini khusus <strong>penonton</strong>.
                            Tiket ini tidak memberikan hak bertanding. Untuk bertanding, gunakan
                            <strong>Register Player</strong>.
                        </div>
                    </div>
                    <form id="buyTicketForm"
                          action="{{ route('events.buy', $event->id) }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_from" value="event-buy">
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id ?? '' }}">
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label">Ticket Price</label>
                                <input type="text"
                                       class="form-input"
                                       value="Rp {{ number_format($ticketPrice, 0, ',', '.') }}"
                                       readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="qty">
                                    Quantity <span class="text-red-500">*</span>
                                </label>
                                <input type="number"
                                       min="1"
                                       max="{{ $stockLeft }}"
                                       value="1"
                                       id="qty"
                                       name="qty"
                                       class="form-input"
                                       required>
                                <p class="hint mt-2">Sisa stok: {{ $stockLeft }}</p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bank_id_buy">
                                    Bank <span class="text-red-500">*</span>
                                </label>
                                <select id="bank_id_buy" name="bank_id" class="form-select" required>
                                    <option value="">Pilih bank</option>
                                    @foreach ($banks as $b)
                                        <option value="{{ $b->id_bank }}">{{ $b->nama_bank }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="no_rek_buy">
                                    Nomor Rekening Pengirim <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
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
                                       required>
                                <p class="hint mt-2">
                                    Masukkan angka saja (6–25 digit). Gunakan rekening yang dipakai untuk transfer.
                                </p>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="bukti_payment_buy">
                                    Bukti Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <input type="file"
                                       id="bukti_payment_buy"
                                       name="bukti_payment"
                                       accept="image/*"
                                       class="form-input"
                                       required>
                                <p class="hint mt-2">
                                    Format: JPG/PNG/WEBP, maks 2MB.
                                </p>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Total Payment</label>
                                <input type="text"
                                       id="totalPaymentDisplay"
                                       class="form-input"
                                       value="Rp {{ number_format($ticketPrice, 0, ',', '.') }}"
                                       readonly>
                                <input type="hidden" id="unitPrice" value="{{ $ticketPrice }}">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button"
                                    class="btn-secondary"
                                    onclick="closeModal('#buyTicketModal')">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                Submit Purchase
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endauth

    <!-- html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        /* Modal helpers */
        function openModal(sel){
            const m=document.querySelector(sel);
            if(!m) return;
            m.classList.add('active');
            document.body.style.overflow='hidden';
        }
        function closeModal(sel){
            const m=document.querySelector(sel);
            if(!m) return;
            m.classList.remove('active');
            document.body.style.overflow='auto';
        }

        /* Lazy image + fallback chain */
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

                const onLoad = () => {
                    img.classList.add('loaded');
                    if(loader) loader.classList.add('hidden');
                };
                img.addEventListener('load', onLoad, { passive:true });

                let list = [];
                try {
                    list = JSON.parse(
                        img.getAttribute('data-src-candidates') ||
                        img.getAttribute('data-srcs') || '[]'
                    ) || [];
                } catch(e){ list = []; }

                if(!Array.isArray(list) || list.length === 0){
                    const first = img.getAttribute('src');
                    if(first) list = [first];
                }

                let idx = parseInt(img.getAttribute('data-idx') || '0', 10);
                if(isNaN(idx) || idx < 0) idx = 0;

                const onError = () => {
                    idx++;
                    if(idx < list.length){
                        img.setAttribute('data-idx', String(idx));
                        img.src = list[idx];
                    } else {
                        showCameraFallback(loader);
                    }
                };
                img.addEventListener('error', onError, { passive:true });

                if(img.complete && img.naturalWidth > 0) onLoad();
            }

            document.addEventListener('DOMContentLoaded', function(){
                document.querySelectorAll('img[data-lazy-img]').forEach(initLazyImage);
            });
        })();

        /* Auto-calc total payment (viewer ticket) */
        (function(){
            const qtyEl = document.getElementById('qty');
            const priceEl = document.getElementById('unitPrice');
            const outEl = document.getElementById('totalPaymentDisplay');
            function formatRupiah(n){
                n = Math.floor(n || 0);
                return 'Rp ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.');
            }
            function recalc(){
                if(!qtyEl || !priceEl || !outEl) return;
                const qty = Math.max(1, parseInt(qtyEl.value || '1', 10));
                const price = parseFloat(priceEl.value || '0');
                outEl.value = formatRupiah(qty * price);
            }
            if(qtyEl) qtyEl.addEventListener('input', recalc);
            recalc();
        })();

        /* Annual Pass local storage + toast */
        (function(){
            const EVENT_ID = {{ $event->id }};
            const AP_KEY = `xb_ap_${EVENT_ID}`;
            const btnShow = document.getElementById('btnShowAnnualPass');

            function validDate(d){
                if(!d) return false;
                const x = new Date(d);
                return !isNaN(x.getTime());
            }

            function loadAP(){
                try{
                    const raw = localStorage.getItem(AP_KEY);
                    if(!raw) return null;
                    const data = JSON.parse(raw);
                    if(data.downloaded) return null;
                    if(validDate(data.valid_to)){
                        const today = new Date(); today.setHours(0,0,0,0);
                        const vt = new Date(data.valid_to); vt.setHours(0,0,0,0);
                        if(vt < today) return null;
                    }
                    return data;
                }catch(e){ return null; }
            }

            function saveAP(ds){
                if(!ds) return;
                const payload = Object.assign({}, ds, { event_id:EVENT_ID, downloaded:false });
                localStorage.setItem(AP_KEY, JSON.stringify(payload));
            }

            function removeAP(){ localStorage.removeItem(AP_KEY); }
            function setShowBtnVisible(flag){
                if(btnShow) btnShow.classList.toggle('hidden', !flag);
            }

            function wireToastHandlers(toastEl, ds){
                const btn = toastEl.querySelector('#btnDownloadPass');
                const close = toastEl.querySelector('.ap-close');
                btn && btn.addEventListener('click', () => generateClientPNG(ds));
                close && close.addEventListener('click', hideAnnualPassToast);
            }

            window.hideAnnualPassToast = function(){
                const toast = document.getElementById('annualPassToast');
                if(!toast) return;
                toast.classList.add('closing');
                toast.addEventListener('animationend', () => toast.remove(), { once:true });
            };

            async function generateClientPNG(ds){
                const card = document.createElement('div');
                card.style.cssText = `
                    position:fixed; left:-9999px; top:-9999px;
                    width:900px; aspect-ratio:90/48;
                    border-radius:20px;
                    background:
                        radial-gradient(120% 120% at 0% 0%, rgba(59,130,246,.28), transparent 60%),
                        radial-gradient(120% 120% at 100% 100%, rgba(16,185,129,.28), transparent 60%),
                        linear-gradient(135deg,#0f172a,#0b1020);
                    border:1px solid rgba(255,255,255,.1);
                    color:#e5e7eb;
                    display:flex; align-items:center; justify-content:space-between;
                    padding:30px 36px;
                    box-shadow:0 25px 60px rgba(0,0,0,.5);
                `;
                card.innerHTML = `
                    <div style="display:flex;flex-direction:column;gap:8px;z-index:2;flex:1;">
                        <div style="font-weight:800;letter-spacing:.5px;font-size:19px;opacity:.95;">
                            Xander Billiard
                        </div>
                        <div style="font-size:32px;font-weight:900;line-height:1.1;margin-top:4px;">
                            Annual Pass ${ds?.year || ''}
                        </div>
                        <div style="font-size:13px;opacity:.8;margin-top:2px;">
                            Valid ${ds?.valid_from || ''} — ${ds?.valid_to || ''}
                        </div>
                        <div style="display:grid;grid-template-columns:140px 1fr;gap:8px;margin-top:12px;font-size:14px;">
                            <div>Name</div><div>${ds?.name || 'Guest'}</div>
                            <div>Pass No</div><div>${ds?.number || ''}</div>
                            <div>Type</div>
                            <div>
                                <span style="
                                    display:inline-flex;align-items:center;gap:8px;
                                    padding:6px 12px;border-radius:9999px;
                                    font-weight:700;letter-spacing:.2px;font-size:12px;
                                    border:1px solid rgba(255,255,255,.14);
                                    background:${(String(ds?.type||'').toLowerCase()==='viewer')
                                        ?'rgba(16,185,129,.08)':'rgba(59,130,246,.08)'};
                                    color:${(String(ds?.type||'').toLowerCase()==='viewer')
                                        ?'#a7f3d0':'#bfdbfe'};
                                ">
                                    ${String(ds?.type||'').toUpperCase()}
                                </span>
                            </div>
                            <div>Event</div><div>${ds?.event || 'Event'}</div>
                        </div>
                        <div style="font-size:12px;opacity:.75;margin-top:8px;font-style:italic;">
                            Simpan kartu ini untuk verifikasi ke panitia.
                        </div>
                    </div>
                    <div style="position:relative;width:280px;height:280px;display:flex;align-items:center;justify-content:center;opacity:.18;">
                        <div style="position:absolute;width:200px;height:200px;filter:drop-shadow(0 0 30px rgba(59,130,246,.4));">
                            <div style="position:absolute;width:10px;height:170px;background:linear-gradient(180deg, rgba(139,92,246,.8) 0%, rgba(219,234,254,.9) 15%, rgba(219,234,254,.9) 85%, rgba(234,179,8,.8) 100% );border-radius:6px;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-45deg);"></div>
                            <div style="position:absolute;width:10px;height:170px;background:linear-gradient(180deg, rgba(139,92,246,.8) 0%, rgba(219,234,254,.9) 15%, rgba(219,234,254,.9) 85%, rgba(234,179,8,.8) 100% );border-radius:6px;top:50%;left:50%;transform:translate(-50%,-50%) rotate(45deg);"></div>
                            <div style="position:absolute;width:52px;height:52px;background:radial-gradient(circle at 30% 30%, #1f1f1f, #000);border-radius:50%;top:50%;left:50%;transform:translate(-50%,-50%);border:3px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;box-shadow: inset -4px -4px 8px rgba(255,255,255,.1), 0 8px 20px rgba(0,0,0,.6);">
                                <div style="width:28px;height:28px;background:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:16px;color:#000;box-shadow: inset 0 2px 4px rgba(0,0,0,.2);">
                                    8
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(card);
                try{
                    const canvas = await html2canvas(card, {
                        scale: 2,
                        backgroundColor: null,
                        logging: false,
                        width: 900
                    });
                    const dataUrl = canvas.toDataURL('image/png');
                    const a = document.createElement('a');
                    const fname = `AnnualPass-${ds?.year || ''}-${(ds?.type || 'VIEWER').toUpperCase()}-${(ds?.number || 'XB')}.png`;
                    a.href = dataUrl;
                    a.download = fname;
                    a.style.display = 'none';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
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

            document.addEventListener('DOMContentLoaded', function(){
                @if (session('annual_pass'))
                    const dsFlash = @json(session('annual_pass'));
                    saveAP(dsFlash);
                    setShowBtnVisible(true);
                    (function(){
                        const toast = document.getElementById('annualPassToast');
                        if(toast) {
                            const btn = toast.querySelector('#btnDownloadPass');
                            const close = toast.querySelector('.ap-close');
                            btn && btn.addEventListener('click', () => generateClientPNG(dsFlash));
                            close && close.addEventListener('click', hideAnnualPassToast);
                        }
                    })();
                @else
                    const ds = loadAP();
                    setShowBtnVisible(!!ds);
                @endif

                btnShow && btnShow.addEventListener('click', function(){
                    const ds = loadAP();
                    if(!ds){ setShowBtnVisible(false); return; }

                    const existing = document.getElementById('annualPassToast');
                    if(existing) existing.remove();

                    const wrap = document.createElement('div');
                    wrap.id = 'annualPassToast';
                    wrap.className = 'ap-toast';
                    wrap.setAttribute('role','status');
                    wrap.setAttribute('aria-live','polite');
                    wrap.innerHTML = `
                        <div class="ap-toast-inner">
                          <div class="ap-toast-header">
                            <div class="ap-title">Annual Pass ${ds.year || ''} diterbitkan</div>
                            <button class="ap-close" type="button" aria-label="Close">✕</button>
                          </div>
                          <div class="ap-toast-body">
                            <div class="ap-left">
                              <div style="font-weight:800">Xander Billiard</div>
                              <div class="ap-row">
                                <div class="ap-kv">Name</div><div>${ds.name || 'Guest'}</div>
                                <div class="ap-kv">Pass No</div><div>${ds.number || ''}</div>
                                <div class="ap-kv">Type</div>
                                <div>
                                  <span class="ap-badge ${(String(ds.type||'').toLowerCase()==='viewer')?'viewer':'player'}">
                                    ${(ds.type || '').toUpperCase()}
                                  </span>
                                </div>
                                <div class="ap-kv">Event</div><div>${ds.event || 'Event'}</div>
                                <div class="ap-kv">Valid</div><div>${ds.valid_from || ''} — ${ds.valid_to || ''}</div>
                              </div>
                              <div class="ap-actions" style="margin-top:6px">
                                <button id="btnDownloadPass" class="ap-btn primary" type="button">Download PNG</button>
                              </div>
                              <div class="ap-note">Setelah diunduh, panel ini akan tertutup otomatis.</div>
                            </div>
                            <div class="ap-right" aria-hidden="true">
                              <div class="billiard-icon">
                                <div class="cue-stick"></div>
                                <div class="cue-stick"></div>
                                <div class="billiard-ball"><div class="ball-number">8</div></div>
                              </div>
                            </div>
                          </div>
                        </div>`;
                    document.body.appendChild(wrap);
                    const btn = wrap.querySelector('#btnDownloadPass');
                    const close = wrap.querySelector('.ap-close');
                    btn && btn.addEventListener('click', () => generateClientPNG(ds));
                    close && close.addEventListener('click', hideAnnualPassToast);
                });
            });
        })();

        /* Reopen modal on validation error */
        @if ($errors->any())
            @if (old('_from') === 'event-registration')
                window.addEventListener('load', () => openModal('#registrationModal'));
            @endif
            @if (old('_from') === 'event-buy')
                window.addEventListener('load', () => openModal('#buyTicketModal'));
            @endif
        @endif

        /* Only-digits helper */
        (function(){
            function onlyDigitsHandler(e){
                const before = e.target.value || '';
                const after = before.replace(/\D+/g, '');
                if(before !== after){
                    const pos = e.target.selectionStart;
                    e.target.value = after;
                    if(typeof pos === 'number'){
                        const newPos = Math.max(0, pos - (before.length - after.length));
                        e.target.setSelectionRange(newPos, newPos);
                    }
                }
            }
            document.addEventListener('input', function(e){
                if(e.target && e.target.classList && e.target.classList.contains('only-digits')){
                    onlyDigitsHandler(e);
                }
            }, { passive:true });
        })();
    </script>
@endsection
