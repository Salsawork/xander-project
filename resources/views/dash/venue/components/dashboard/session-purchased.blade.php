@push('styles')
    <style>
        :root { color-scheme: dark; }
        html, body { height:100%; overscroll-behavior-y:none; }

        .max-h-0 { max-height: 0 !important; }
        @media (min-width: 1024px) { .lg-hidden { display:none !important; } }
        @media (max-width: 1023px) { .sm-hidden { display:none !important; } }

        @media (max-width:1023px){
            .mobile-filter-overlay{ position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:40; display:none; }
            .mobile-filter-overlay.active{ display:block; }
            .mobile-filter-sidebar{ position:fixed; top:0; left:-100%; width:85%; max-width:340px; height:100%; background:rgb(23,23,23); z-index:50; transition:left .3s ease; overflow-y:auto; -webkit-overflow-scrolling:touch; padding-bottom:24px; }
            .mobile-filter-sidebar.open{ left:0; }
        }
        .toggleContent{ overflow:hidden; transition:max-height .3s ease; max-height:1000px; }
        .toggleContent.max-h-0{ max-height:0; }

        .pager { display:inline-flex; align-items:center; gap:10px; background:#1f2937; border:1px solid rgba(255,255,255,.06); border-radius:9999px; padding:6px 10px; box-shadow: 0 8px 20px rgba(0,0,0,.35) inset, 0 4px 14px rgba(0,0,0,.25); }
        .pager-label { min-width:90px; text-align:center; color:#e5e7eb; font-weight:600; letter-spacing:.2px; }
        .pager-btn { width:44px; height:44px; display:grid; place-items:center; border-radius:9999px; line-height:0; text-decoration:none; border:1px solid rgba(255,255,255,.15); box-shadow:0 2px 6px rgba(0,0,0,.35); transition: transform .15s ease, opacity .15s ease; }
        .pager-btn:hover { transform: translateY(-1px); }
        .pager-prev { background:#e5e7eb; color:#0f172a; }
        .pager-next { background:#2563eb; color:#fff; }
        .pager-btn[aria-disabled="true"] { opacity:.45; pointer-events:none; filter:grayscale(20%); }
        @media (max-width:640px){
            .pager { padding:4px 8px; gap:8px; }
            .pager-btn { width:40px; height:40px; }
            .pager-label { min-width:80px; font-size:.9rem; }
        }
    </style>
@endpush

<div class="w-full h-full flex flex-col justify-between">
    <span class="text-white text-lg font-semibold mb-2">Session Purchased</span>
    <div class="flex items-end mb-1">
        <span class="text-2xl md:text-4xl font-bold mr-2">{{ number_format($sessionPurchased) }}</span>
    </div>
    <div class="flex items-end justify-between">
        <span class="text-gray-500 text-sm">{{ number_format($lastYearSessions) }} last year</span>
        <span class="{{ $sessionPercentageChange >= 0 ? 'text-green-400' : 'text-red-400' }}">
            {{ $sessionPercentageChange >= 0 ? '+' : '' }}{{ $sessionPercentageChange }}%
        </span>
    </div>
</div>
