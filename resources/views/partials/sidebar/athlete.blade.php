{{-- resources/views/sidebar/athlete.blade.php --}}
<style>
    :root{
        color-scheme: dark;
        --page-bg:#0a0a0a;
    }

    /* Selalu gelap & cegah overscroll chaining ke body */
    html, body{
        height:100%;
        min-height:100%;
        background:var(--page-bg);
        overscroll-behavior-y: none; /* Chrome/Android/desktop */
        overscroll-behavior-x: none;
        touch-action: pan-y;          /* iOS Safari: tetap bisa scroll vertikal */
        -webkit-text-size-adjust: 100%;
    }

    /* Kanvas gelap anti “white flash” saat rubber-band (atas & bawah) */
    #antiBounceBg{
        position: fixed;
        left: 0; right: 0;
        top: -120svh;      /* pakai svh agar stabil di mobile */
        bottom: -120svh;
        background: var(--page-bg);
        z-index: -1;       /* selalu di belakang */
        pointer-events: none;
    }

    /* ====== utility show/hide ====== */
    @media (min-width: 1024px) { .lg-hidden { display: none !important; } }
    @media (max-width: 1023px) { .sm-hidden { display: none !important; } }

    /* Sidebar desktop: full tinggi viewport tanpa bounce */
    .sidebar-desktop{
        height: 100dvh;            /* lebih stabil daripada 100vh */
        background:#1f1f1f;
        overscroll-behavior: contain;
    }

    /* Sidebar mobile panel: scrollable nyaman + tidak menerus ke body */
    #sidebar{
        background:#1a1a1a;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        overflow-y: auto;          /* panel bisa di-scroll sendiri */
    }

    /* Overlay full gelap; saat bounce tetap gelap */
    #sidebar-overlay{
        background: rgba(0,0,0,.6);
        backdrop-filter: blur(3px);
    }
</style>

<!-- Kanvas gelap anti-bounce -->
<div id="antiBounceBg" aria-hidden="true"></div>

<!-- DEKSTOP -->
<aside class="sm-hidden sidebar-desktop w-64 min-w-[256px] flex-shrink-0 sticky top-0 border-r border-white/10">
    <header class="h-16 flex items-center justify-center border-b border-white/10">
        <a href="{{ route('index') }}" class="inline-flex items-center">
            <img src="{{ asset('/images/logo/logo-xander.png') }}" alt="Logo" class="h-9">
        </a>
    </header>

    <nav class="p-6">
        <ul class="space-y-4 text-[15px] font-semibold">
            <li>
                <a href="{{ route('athlete.dashboard') }}"
                   class="group flex items-center justify-between rounded-lg px-3 py-2
                    {{ request()->routeIs('athlete.dashboard') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <span>Dashboard</span>
                    @if(request()->routeIs('athlete.dashboard'))
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
                    @endif
                </a>
            </li>

            <li>
                <a href="{{ route('athlete.match.create') }}"
                   class="group flex items-center justify-between rounded-lg px-3 py-2
                    {{ request()->routeIs('athlete.match.create') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <span>Create Session</span>
                    @if(request()->routeIs('athlete.match.create'))
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
                    @endif
                </a>
            </li>

            <li>
                <a href="{{ route('athlete.match') }}"
                   class="group flex items-center justify-between rounded-lg px-3 py-2
                    {{ request()->routeIs('athlete.match') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
                    <span>Match History</span>
                    @if(request()->routeIs('athlete.match'))
                        <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
                    @endif
                </a>
            </li>
        </ul>
    </nav>
</aside>

<!-- MOBILE: Top bar (TIDAK FIXED, jadi tidak ikut saat scroll) -->
<nav class="lg-hidden bg-[#1a1a1a] text-white px-6 py-3 flex items-center justify-between shadow-md">
    <div class="flex items-center space-x-3">
        <a href="{{ route('index') }}" class="flex items-center space-x-2">
            <img src="{{ asset('images/logo/logo-xander.png') }}" alt="Logo" class="h-12" />
        </a>
    </div>
    <div class="flex items-center space-x-4 lg:hidden">
        <button id="mobile-menu-button" aria-label="Toggle mobile menu"
                class="flex items-center text-gray-300 hover:text-white focus:outline-none transition duration-200">
            <i id="hamburgerIcon" class="fas fa-bars text-xl"></i>
        </button>
    </div>
</nav>

<!-- Overlay (Mobile Only) -->
<div id="sidebar-overlay"
     class="lg-hidden hidden fixed inset-0 z-40 transition-opacity duration-300"
     onclick="closeMobileSidebar()"></div>

<!-- Sidebar Mobile (slide dari kanan) -->
<aside id="sidebar"
       class="lg-hidden hidden fixed top-0 right-0 h-full w-80 shadow-lg z-50 transform translate-x-full transition-transform duration-300">

    <!-- Header dengan tombol close -->
    <div class="flex items-center justify-between p-6 border-b border-gray-700">
        <h2 class="text-white text-lg font-semibold">Menu</h2>
        <button aria-label="Close mobile menu"
                class="text-gray-300 hover:text-white focus:outline-none transition duration-200"
                onclick="closeMobileSidebar()">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="p-6">
        <ul class="space-y-4">
            <li>
                <a href="{{ route('athlete.dashboard') }}"
                   class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200
                    {{ request()->routeIs('athlete.dashboard') ? 'text-[#0a8aff] bg-[#2a2a2a]' : '' }}">
                    <i class="fas fa-chart-line w-5 text-gray-400"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ route('athlete.match.create') }}"
                   class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200
                    {{ request()->routeIs('athlete.match.create') ? 'text-[#0a8aff] bg-[#2a2a2a]' : '' }}">
                    <i class="fas fa-calendar-alt w-5 text-gray-400"></i>
                    <span>Create Session</span>
                </a>
            </li>

            <li>
                <a href="{{ route('athlete.match') }}"
                   class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200
                    {{ request()->routeIs('athlete.match') ? 'text-[#0a8aff] bg-[#2a2a2a]' : '' }}">
                    <i class="fas fa-tag w-5 text-gray-400"></i>
                    <span>Match History</span>
                </a>
            </li>
        </ul>

        <div class="mt-8 pt-6 border-t border-gray-700">
            <h3 class="text-gray-400 text-sm font-medium mb-4">Account</h3>
            <a href="{{ route('index') }}" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200">
                <i class="fas fa-home w-5 text-gray-400"></i>
                <span>Home</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200 text-left">
                    <i class="fas fa-sign-out-alt w-5 text-gray-400"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>
</aside>

<!-- JavaScript untuk Toggle Menu + anti-bounce friendly -->
<script>
    (function(){
        const menuButton = document.getElementById('mobile-menu-button');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const icon = document.getElementById('hamburgerIcon');

        function lockBody(lock){
            document.body.style.overflow = lock ? 'hidden' : '';
            // iOS: cegah scroll chaining saat panel terbuka
            document.documentElement.style.overscrollBehaviorY = lock ? 'none' : '';
        }

        function openMobileSidebar(){
            sidebar.classList.remove('hidden');
            overlay.classList.remove('hidden');
            // delay kecil agar transition bekerja
            requestAnimationFrame(()=> sidebar.classList.remove('translate-x-full'));
            // ubah ikon jadi close
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
            lockBody(true);
        }

        function closeMobileSidebar(){
            sidebar.classList.add('translate-x-full');
            // setelah animasi selesai, baru sembunyikan node
            setTimeout(() => {
                sidebar.classList.add('hidden');
                overlay.classList.add('hidden');
                lockBody(false);
                // kembalikan ikon
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }, 300);
        }

        function toggleMobileSidebar(){
            if (sidebar.classList.contains('hidden') || sidebar.classList.contains('translate-x-full')){
                openMobileSidebar();
            } else {
                closeMobileSidebar();
            }
        }

        menuButton?.addEventListener('click', toggleMobileSidebar);
        overlay?.addEventListener('click', closeMobileSidebar);

        // Tutup saat klik link menu (mobile)
        const menuLinks = sidebar.querySelectorAll('nav a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) closeMobileSidebar();
            });
        });

        // Tutup saat resize ke desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeMobileSidebar();
        });
    })();
</script>
