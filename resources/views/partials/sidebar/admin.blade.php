<style>
    .scrollbar-hidden::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hidden {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @media (min-width:1024px) {
        .only-mobile {
            display: none !important;
        }
    }

    @media (max-width:1023.98px) {
        .only-desktop {
            display: none !important;
        }
    }
</style>

<!-- ========== DESKTOP SIDEBAR ========== -->
<aside class="only-desktop bg-[#2D2D2D] w-1/6 flex-shrink-0 h-screen sticky top-0 overflow-y-auto scrollbar-hidden">
    <header class="bg-[#2D2D2D] h-16 flex items-center justify-center px-4 border-b border-gray-700"></header>
    <!-- Menu -->
    <ul class="space-y-6 text-sm font-semibold p-6">
        <!-- Shop Overview -->
        <li>
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <!-- Icon Home -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m0-8h8m-8 0H5" />
                </svg>
                Shop Overview
            </a>
        </li>

        <!-- Product -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Product</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('products.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('products.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon List -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        My Products
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('products.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Product
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.edit', $product->id ?? 1) }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('products.edit') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Pencil -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536M9 13l-6 6h6v-6z" />
                        </svg>
                        Edit Product
                    </a>
                </li>
            </ul>
        </li>

        <!-- Order -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Order</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('order.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('order.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Clipboard -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m2 0a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2-2H9a2 2 0 00-2 2H5a2 2 0 00-2 2v4a2 2 0 002 2h2" />
                        </svg>
                        Order List
                    </a>
                </li>
                <li>
                    <a href="{{ route('order.detail.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('order.detail.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Document -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 16h10M7 12h10M5 4h14v16H5z" />
                        </svg>
                        Detail Order
                    </a>
                </li>
            </ul>
        </li>

        <!-- Promo -->
        <li>
            <a href="{{ route('promo.index') }}"
                class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('promo.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <!-- Icon Tag -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M3 3h6l11 11-6 6L3 9V3z" />
                </svg>
                Promo Management
            </a>
        </li>

        <!-- Community -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Community</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('comunity.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('comunity.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Newspaper -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V7h16v11a2 2 0 002 2z" />
                        </svg>
                        Daftar Berita
                    </a>
                </li>
                <li>
                    <a href="{{ route('comunity.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('comunity.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus Circle -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4m16 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                        Tambah Berita
                    </a>
                </li>
            </ul>
        </li>

        <!-- Guidelines -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Guidelines</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('admin.guidelines.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('admin.guidelines.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Book -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v12m-9-6h18" />
                        </svg>
                        Daftar Guidelines
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.guidelines.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('admin.guidelines.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Guideline
                    </a>
                </li>
            </ul>
        </li>

        <!-- Venue/Partner -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Venue/Partner Management</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('venue.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('venue.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Office -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7M3 7h18M3 7V5a1 1 0 011-1h16a1 1 0 011 1v2" />
                        </svg>
                        Daftar Venue
                    </a>
                </li>
                <li>
                    <a href="{{ route('venue.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('venue.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Venue
                    </a>
                </li>
            </ul>
        </li>

        <!-- Athlete -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Athlete Management</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('athlete.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('athlete.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon User Group -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-5-3.87M9 20h6M6 10a4 4 0 118 0 4 4 0 01-8 0z" />
                        </svg>
                        Daftar Athlete
                    </a>
                </li>
                <li>
                    <a href="{{ route('athlete.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('athlete.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon User Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11h6m-3-3v6M5 20a7 7 0 0014 0H5z" />
                        </svg>
                        Tambah Athlete
                    </a>
                </li>
            </ul>
        </li>

        <!-- Tournament -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Tournament</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('tournament.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('tournament.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Trophy -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 21h8m-4-4v4m-7-8a7 7 0 0014 0V5H5v8z" />
                        </svg>
                        Daftar Tournament
                    </a>
                </li>
                <li>
                    <a href="{{ route('tournament.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('tournament.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Tournament
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>

<nav
    class="only-mobile bg-[#1a1a1a] text-white px-6 py-3 flex items-center justify-between fixed top-0 left-0 right-0 z-50 shadow-md">
    <!-- Logo kiri -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('index') }}" class="flex items-center space-x-2">
            <img src="{{ asset('images/logo/logo-xander.png') }}" alt="Logo" class="h-10" />
        </a>
    </div>
    <div class="flex items-center space-x-4">
        <button id="mobile-menu-button" aria-label="Toggle mobile menu"
            class="flex items-center text-gray-300 hover:text-white focus:outline-none transition duration-200">
            <i id="hamburgerIcon" class="fas fa-bars text-xl"></i>
        </button>
    </div>
</nav>

<!-- Overlay -->
<div id="sidebar-overlay"
    class="only-mobile hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 transition-opacity duration-300"
    onclick="closeMobileSidebar()"></div>

<!-- ========== MOBILE SIDEBAR SLIDE ========== -->
<aside id="sidebar"
    class="only-mobile hidden fixed top-0 right-0 h-full w-72 bg-[#2D2D2D] shadow-lg z-50 transform translate-x-full transition-transform duration-300 overflow-y-auto scrollbar-hidden">

    <!-- Header dengan tombol close -->
    <div class="flex items-center justify-between p-6 border-b border-gray-700">
        <h2 class="text-white text-lg font-semibold">Menu</h2>
        <button aria-label="Close mobile menu"
            class="text-gray-300 hover:text-white focus:outline-none transition duration-200"
            onclick="closeMobileSidebar()">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Isi Menu Sidebar (COPY PASTE ISI DESKTOP MENU) -->
      <ul class="space-y-6 text-sm font-semibold p-6">
        <!-- Shop Overview -->
        <li>
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <!-- Icon Home -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7m-9 2v8m0-8h8m-8 0H5" />
                </svg>
                Shop Overview
            </a>
        </li>

        <!-- Product -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Product</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('products.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('products.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon List -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        My Products
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('products.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Product
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.edit', $product->id ?? 1) }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('products.edit') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Pencil -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536M9 13l-6 6h6v-6z" />
                        </svg>
                        Edit Product
                    </a>
                </li>
            </ul>
        </li>

        <!-- Order -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Order</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('order.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('order.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Clipboard -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m2 0a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2-2H9a2 2 0 00-2 2H5a2 2 0 00-2 2v4a2 2 0 002 2h2" />
                        </svg>
                        Order List
                    </a>
                </li>
                <li>
                    <a href="{{ route('order.detail.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('order.detail.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Document -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 16h10M7 12h10M5 4h14v16H5z" />
                        </svg>
                        Detail Order
                    </a>
                </li>
            </ul>
        </li>

        <!-- Promo -->
        <li>
            <a href="{{ route('promo.index') }}"
                class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('promo.index') ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <!-- Icon Tag -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M3 3h6l11 11-6 6L3 9V3z" />
                </svg>
                Promo Management
            </a>
        </li>

        <!-- Community -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Community</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('comunity.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('comunity.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Newspaper -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V7h16v11a2 2 0 002 2z" />
                        </svg>
                        Daftar Berita
                    </a>
                </li>
                <li>
                    <a href="{{ route('comunity.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('comunity.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus Circle -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4m16 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                        Tambah Berita
                    </a>
                </li>
            </ul>
        </li>

        <!-- Guidelines -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Guidelines</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('admin.guidelines.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('admin.guidelines.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Book -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v12m-9-6h18" />
                        </svg>
                        Daftar Guidelines
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.guidelines.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('admin.guidelines.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Guideline
                    </a>
                </li>
            </ul>
        </li>

        <!-- Venue/Partner -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Venue/Partner Management</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('venue.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('venue.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Office -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7M3 7h18M3 7V5a1 1 0 011-1h16a1 1 0 011 1v2" />
                        </svg>
                        Daftar Venue
                    </a>
                </li>
                <li>
                    <a href="{{ route('venue.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('venue.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Venue
                    </a>
                </li>
            </ul>
        </li>

        <!-- Athlete -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Athlete Management</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('athlete.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('athlete.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon User Group -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-5-3.87M9 20h6M6 10a4 4 0 118 0 4 4 0 01-8 0z" />
                        </svg>
                        Daftar Athlete
                    </a>
                </li>
                <li>
                    <a href="{{ route('athlete.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('athlete.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon User Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11h6m-3-3v6M5 20a7 7 0 0014 0H5z" />
                        </svg>
                        Tambah Athlete
                    </a>
                </li>
            </ul>
        </li>

        <!-- Tournament -->
        <li>
            <div class="mb-2 text-gray-400 uppercase tracking-wider text-xs">Tournament</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('tournament.index') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('tournament.index') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Trophy -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 21h8m-4-4v4m-7-8a7 7 0 0014 0V5H5v8z" />
                        </svg>
                        Daftar Tournament
                    </a>
                </li>
                <li>
                    <a href="{{ route('tournament.create') }}"
                        class="flex items-center gap-2 py-1 px-2 rounded-md {{ request()->routeIs('tournament.create') ? 'bg-gray-800 text-white' : 'hover:bg-gray-700 hover:text-white' }}">
                        <!-- Icon Plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Tournament
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>

<!-- ========== JAVASCRIPT TOGGLE ========== -->
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
        setTimeout(() => {
            sidebar.classList.remove('translate-x-full');
        }, 10);
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

    // Tutup saat klik link di mobile
    const menuLinks = sidebar.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                closeMobileSidebar();
            }
        });
    });

    // Tutup saat resize ke desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            closeMobileSidebar();
        }
    });

function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    const icon = document.getElementById(id + 'Icon');
    dropdown.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}


</script>
