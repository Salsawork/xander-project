{{-- resources/views/sidebar/athlete.blade.php --}}
<style>
  @media (min-width: 1024px) {
    .lg-hidden { display: none !important; }
  }
  @media (max-width: 1023px) {
    .sm-hidden { display: none !important; }
  }
</style>

<!-- DESKTOP -->
<aside class="sm-hidden bg-[#1f1f1f] w-64 min-w-[256px] flex-shrink-0 sticky top-0 h-[100dvh] border-r border-white/10">
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
        <a href="{{ route('athlete.match') }}"
           class="group flex items-center justify-between rounded-lg px-3 py-2
           {{ request()->routeIs('athlete.match') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <span>Match History</span>
          @if(request()->routeIs('athlete.match'))
            <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
          @endif
        </a>
      </li>

      <li>
        <a href="{{ route('athlete.sparring') }}"
           class="group flex items-center justify-between rounded-lg px-3 py-2
           {{ request()->routeIs('athlete.sparring') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <span>Sparring Schedule</span>
          @if(request()->routeIs('athlete.sparring'))
            <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
          @endif
        </a>
      </li>

      <li>
        <a href="{{ route('athlete.transaction') }}"
           class="group flex items-center justify-between rounded-lg px-3 py-2
           {{ request()->routeIs('athlete.transaction') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <span>Transaction Management</span>
          @if(request()->routeIs('athlete.transaction'))
            <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
          @endif
        </a>
      </li>
    </ul>
  </nav>
</aside>

<!-- MOBILE TOPBAR -->
<nav class="lg-hidden bg-[#1a1a1a] text-white px-6 py-3 flex items-center justify-between fixed top-0 left-0 right-0 z-50 shadow-md">
  <div class="flex items-center space-x-3">
    <a href="{{ route('index') }}" class="flex items-center space-x-2">
      <img src="{{ asset('images/logo/logo-xander.png') }}" alt="Logo" class="h-12" />
    </a>
  </div>
  <div class="flex items-center space-x-4 lg-hidden">
    <button id="mobile-menu-button" aria-label="Toggle mobile menu"
            class="flex items-center text-gray-300 hover:text-white focus:outline-none transition duration-200">
      <i class="fas fa-bars text-xl"></i>
    </button>
  </div>
</nav>

<!-- Overlay (Mobile) -->
<div id="sidebar-overlay"
     class="lg-hidden hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 transition-opacity duration-300"
     onclick="closeMobileSidebar()"></div>

<!-- Sidebar Mobile (slide dari kanan) -->
<aside id="sidebar"
       class="lg-hidden hidden fixed top-0 right-0 h-full w-80 bg-[#1a1a1a] shadow-lg z-50 transform translate-x-full transition-transform duration-300">
  <div class="flex items-center justify-between p-6 border-b border-gray-700">
    <h2 class="text-white text-lg font-semibold">Menu</h2>
    <button aria-label="Close mobile menu"
            class="text-gray-300 hover:text-white focus:outline-none transition duration-200"
            onclick="closeMobileSidebar()">
      <i class="fas fa-times text-xl"></i>
    </button>
  </div>

  <nav class="p-6">
    <ul class="space-y-4">
      <li>
        <a href="{{ route('athlete.dashboard') }}"
           class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200
           {{ request()->routeIs('athlete.dashboard') ? 'text-[#0a8aff] bg-[#2a2a2a]' : '' }}">
          <i class="fas fa-user w-5 text-gray-400"></i>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="{{ route('athlete.match') }}"
           class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200
           {{ request()->routeIs('athlete.match') ? 'text-[#0a8aff] bg-[#2a2a2a]' : '' }}">
          <i class="fas fa-bell w-5 text-gray-400"></i>
          <span>Match History</span>
        </a>
      </li>
      <li>
        <a href="{{ route('athlete.sparring') }}"
           class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200
           {{ request()->routeIs('athlete.sparring') ? 'text-[#0a8aff] bg-[#2a2a2a]' : '' }}">
          <i class="fas fa-shopping-bag w-5 text-gray-400"></i>
          <span>Sparring Schedule</span>
        </a>
      </li>
      <li>
        <a href="{{ route('athlete.transaction') }}"
           class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200
           {{ request()->routeIs('athlete.transaction') ? 'text-[#0a8aff] bg-[#2a2a2a]' : '' }}">
          <i class="fas fa-shopping-bag w-5 text-gray-400"></i>
          <span>Transaction Management</span>
        </a>
      </li>
    </ul>

    <div class="mt-8 pt-6 border-t border-gray-700">
      <h3 class="text-gray-400 text-sm font-medium mb-4">Account</h3>
      <a href="{{ route('index') }}" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition">
        <i class="fas fa-home w-5 text-gray-400"></i>
        <span>Home</span>
      </a>
      <form method="POST" action="{{ route('logout') }}" class="mt-2">
        @csrf
        <button type="submit" class="w-full flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition text-left">
          <i class="fas fa-sign-out-alt w-5 text-gray-400"></i>
          <span>Logout</span>
        </button>
      </form>
    </div>
  </nav>
</aside>

<script>
  const menuButton = document.getElementById('mobile-menu-button');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');

  function toggleMobileSidebar() {
    if (sidebar.classList.contains('hidden')) {
      openMobileSidebar();
    } else {
      closeMobileSidebar();
    }
  }
  function openMobileSidebar() {
    sidebar.classList.remove('hidden');
    overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(() => { sidebar.classList.remove('translate-x-full'); }, 10);
  }
  function closeMobileSidebar() {
    sidebar.classList.add('translate-x-full');
    setTimeout(() => {
      sidebar.classList.add('hidden');
      overlay.classList.add('hidden');
      document.body.style.overflow = '';
    }, 300);
  }
  menuButton.addEventListener('click', toggleMobileSidebar);

  // Close on link click (mobile)
  const menuLinks = sidebar.querySelectorAll('nav a');
  menuLinks.forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth < 1024) closeMobileSidebar();
    });
  });

  // Close when resized to desktop
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) closeMobileSidebar();
  });
</script>
