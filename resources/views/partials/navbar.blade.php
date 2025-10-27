{{-- ====== Styles helper (pastikan hamburger hanya mobile) ====== --}}
<style>
  @media (min-width:1024px){ .only-mobile{display:none !important;} }
  @media (max-width:1023.98px){ .only-desktop{display:none !important;} }
</style>

<nav class="bg-[#1a1a1a] text-white px-6 py-3 flex items-center justify-between fixed top-0 left-0 right-0 z-50 shadow-md">
  <!-- Logo kiri -->
  <div class="flex items-center space-x-3">
    <a href="{{ route('index') }}" class="flex items-center space-x-2">
      <img src="{{ asset('images/logo/logo-xander.png') }}" alt="Logo" class="h-12" />
    </a>
  </div>

  <!-- ===== DESKTOP MENU (≥ lg) ===== -->
  <div class="only-desktop hidden lg:flex items-center space-x-10">
    <ul class="flex space-x-6 text-sm text-gray-300">
      <li><a href="{{ route('index') }}"               class="hover:text-white transition duration-200">Home</a></li>
      <li><a href="{{ route('products.landing') }}"    class="hover:text-white transition duration-200">Products</a></li>
      <li><a href="{{ route('venues.index') }}"        class="hover:text-white transition duration-200">Venue</a></li>
      <li><a href="{{ route('sparring.index') }}"      class="hover:text-white transition duration-200">Sparring</a></li>
      <li><a href="{{ route('community.index') }}"     class="hover:text-white transition duration-200">Community</a></li>
      <li><a href="{{ route('events.index') }}"        class="hover:text-white transition duration-200">Event</a></li>
      <li><a href="{{ route('guideline.index') }}"     class="hover:text-white transition duration-200">Guidelines</a></li>
    </ul>

    {{-- Ikon user + dropdown --}}
    <div class="relative">
      <button aria-label="User menu"
              class="flex items-center text-gray-300 hover:text-white focus:outline-none transition duration-200"
              onclick="toggleDropdown()">
        <i class="fas fa-user-circle fa-lg"></i>
      </button>

      <div id="userDropdown"
           class="hidden absolute right-0 top-8 w-48 bg-[#2a2a2a] rounded-lg shadow-lg py-2 z-50 border border-gray-700">
        @guest
          <a href="{{ route('login') }}"
             class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-200 hover:bg-[#3a3a3a] rounded-md whitespace-nowrap">
            <i class="fas fa-sign-in-alt w-4 shrink-0 text-gray-300"></i>
            <span>Login</span>
          </a>
        @endguest

        @auth
        @php
        $role = Auth::user()->roles ?? null;
    
        switch ($role) {
            case 'admin':
                $settingsRoute = route('dashboard');
                break;
            case 'venue':
                $settingsRoute = route('venue.dashboard');
                break;
            case 'athlete':
                $settingsRoute = route('athlete.dashboard');
                break;
            case 'user':
                $settingsRoute = route('profile.edit');
                break;
            case 'player':
                $settingsRoute = route('profile.edit');
                break;
            default:
                $settingsRoute = route('dashboard'); // fallback jika role tidak dikenali
                break;
        }
    @endphp
    
    <a href="{{ $settingsRoute }}"
       class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-200 hover:bg-[#3a3a3a] rounded-md whitespace-nowrap">
        <i class="fas fa-gear w-4 shrink-0 text-gray-300"></i>
        <span>Settings</span>
    </a>
    

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-200 hover:bg-[#3a3a3a] rounded-md whitespace-nowrap text-left">
              <i class="fas fa-sign-out-alt w-4 shrink-0 text-gray-300"></i>
              <span>Logout</span>
            </button>
          </form>
        @endauth
      </div>
    </div>
  </div>

  <!-- ===== MOBILE CONTROLS (< lg): hanya hamburger ===== -->
  <div class="only-mobile flex items-center space-x-4 lg:hidden">
    <button aria-label="Toggle mobile menu"
            class="flex items-center text-gray-300 hover:text-white focus:outline-none transition duration-200"
            onclick="toggleMobileMenu()">
      <i id="hamburgerIcon" class="fas fa-bars text-xl"></i>
    </button>
  </div>
</nav>

<!-- Overlay & Sidebar Mobile Menu -->
<div id="mobileMenuOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" onclick="closeMobileMenu()"></div>

<div id="mobileMenu" class="hidden fixed top-0 right-0 h-full w-80 bg-[#1a1a1a] shadow-lg z-50 transform translate-x-full transition-transform duration-300 lg:hidden">
  <div class="flex items-center justify-between p-6 border-b border-gray-700">
    <h2 class="text-white text-lg font-semibold">Menu</h2>
    <button aria-label="Close mobile menu"
            class="text-gray-300 hover:text-white focus:outline-none transition duration-200"
            onclick="closeMobileMenu()">
      <i class="fas fa-times text-xl"></i>
    </button>
  </div>

  <div class="p-6">
    <ul class="space-y-4">
      <li><a href="{{ route('index') }}"             class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200"><i class="fas fa-home w-5 text-gray-400"></i><span>Home</span></a></li>
      <li><a href="{{ route('products.landing') }}"  class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200"><i class="fas fa-shopping-bag w-5 text-gray-400"></i><span>Products</span></a></li>
      <li><a href="{{ route('venues.index') }}"      class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200"><i class="fas fa-map-marker-alt w-5 text-gray-400"></i><span>Venue</span></a></li>
      <li><a href="{{ route('sparring.index') }}"    class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200"><i class="fas fa-fist-raised w-5 text-gray-400"></i><span>Sparring</span></a></li>
      <li><a href="{{ route('community.index') }}"   class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200"><i class="fas fa-users w-5 text-gray-400"></i><span>Community</span></a></li>
      <li><a href="{{ route('events.index') }}"      class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200"><i class="fas fa-calendar-alt w-5 text-gray-400"></i><span>Event</span></a></li>
      <li><a href="{{ route('guideline.index') }}"   class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200"><i class="fas fa-book w-5 text-gray-400"></i><span>Guidelines</span></a></li>
    </ul>

    <div class="mt-8 pt-6 border-t border-gray-700">
      <h3 class="text-gray-400 text-sm font-medium mb-4">Account</h3>
      @guest
        <a href="{{ route('login') }}" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200">
          <i class="fas fa-sign-in-alt w-5 text-gray-400"></i><span>Login</span>
        </a>
      @endguest
      @auth
        {{-- <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200">
          <i class="fas fa-gear w-5 text-gray-400"></i><span>Settings</span>
        </a> --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
          @csrf
          <button type="submit" class="w-full flex items-center gap-3 text-gray-200 hover:text-white hover:bg-[#2a2a2a] px-3 py-2 rounded-md transition duration-200 text-left">
            <i class="fas fa-sign-out-alt w-5 text-gray-400"></i><span>Logout</span>
          </button>
        </form>
      @endauth
    </div>
  </div>
</div>

<!-- Spacer agar konten tidak ketutup navbar fixed -->
<div class="h-14"></div>

<script>
  // Desktop dropdown
  function toggleDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
    function close(e){
      if (!e.target.closest('#userDropdown') && !e.target.closest('button[aria-label="User menu"]')) {
        dropdown.classList.add('hidden');
        document.removeEventListener('click', close);
      }
    }
    document.addEventListener('click', close);
  }

  // Mobile menu
  function toggleMobileMenu(){ const m=document.getElementById('mobileMenu'); m.classList.contains('hidden')?openMobileMenu():closeMobileMenu(); }
  function openMobileMenu(){
    const m=document.getElementById('mobileMenu'), o=document.getElementById('mobileMenuOverlay'), h=document.getElementById('hamburgerIcon');
    m.classList.remove('hidden'); o.classList.remove('hidden'); setTimeout(()=>m.classList.remove('translate-x-full'),10);
    h.classList.remove('fa-bars'); h.classList.add('fa-times'); document.body.style.overflow='hidden';
  }
  function closeMobileMenu(){
    const m=document.getElementById('mobileMenu'), o=document.getElementById('mobileMenuOverlay'), h=document.getElementById('hamburgerIcon');
    m.classList.add('translate-x-full'); setTimeout(()=>{m.classList.add('hidden'); o.classList.add('hidden');},300);
    h.classList.remove('fa-times'); h.classList.add('fa-bars'); document.body.style.overflow='';
  }
  // Tutup saat resize ke desktop
  window.addEventListener('resize', ()=>{ if (window.innerWidth>=1024) closeMobileMenu(); });
</script>
