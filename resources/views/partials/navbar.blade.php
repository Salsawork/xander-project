<nav
    class="bg-[#1a1a1a] text-white px-6 py-3 flex items-center justify-between fixed top-0 left-0 right-0 z-50 shadow-md">
    <!-- Logo di kiri -->
    <div class="flex items-center space-x-3">
        <a href="{{ route('index') }}" class="flex items-center space-x-2">
            <img src="{{ asset('images/logo/logo-xander.png') }}" alt="Logo" class="h-12" />
        </a>
    </div>

    <!-- Semua menu di kanan -->
    <div class="flex items-center space-x-10">
        <!-- Menu utama -->
        <ul class="flex space-x-6 text-sm text-gray-300">
            <li><a href="{{ route('index') }}" class="hover:text-white transition duration-200">Home</a></li>
            <li><a href="{{ route('products.landing') }}" class="hover:text-white transition duration-200">Products</a>
            </li>
            <li><a href="{{ route('venues.index') }}" class="hover:text-white transition duration-200">Venue</a></li>
            <li><a href="{{ route('sparring.index') }}" class="hover:text-white transition duration-200">Sparring</a>
            </li>
        </ul>

        <!-- Menu tambahan -->
        <ul class="flex space-x-6 text-sm text-gray-300">
            <li><a href="{{ route('community.index') }}" class="hover:text-white transition duration-200">Community</a>
            </li>
            <li><a href="{{ route('events.index') }}" class="hover:text-white transition duration-200">Event</a></li>
            <li><a href="{{ route('guideline.index') }}" class="hover:text-white transition duration-200">Guidelines</a>
            </li>
        </ul>


        <!-- Login / Dropdown -->
        @guest
            <a href="{{ route('login') }}"
                class="text-sm text-white bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 transition duration-200">Login</a>
        @endguest

        @auth
            <div class="relative">
                <button aria-label="User profile"
                    class="flex items-center space-x-2 text-gray-400 hover:text-white focus:outline-none transition duration-200"
                    onclick="toggleDropdown()">
                    <i class="fas fa-user-circle fa-lg"></i>
                </button>
                <div id="userDropdown"
                    class="hidden absolute right-0 top-8 w-40 bg-[#2a2a2a] rounded-md shadow-lg py-1 z-50 border border-gray-700">
                    <a href="{{ route('dashboard') }}"
                        class="block px-4 py-2 text-sm text-gray-300 hover:bg-[#3a3a3a] transition duration-200">
                        <i class="fas fa-gear mr-2"></i> Settings
                    </a>
                    <a href="{{ route('logout') }}"
                        class="block px-4 py-2 text-sm text-gray-300 hover:bg-[#3a3a3a] transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        @endauth
    </div>
</nav>


<!-- Spacer to prevent content from hiding behind fixed navbar -->
<div class="h-14"></div>

