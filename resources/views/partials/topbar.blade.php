<header class="bg-[#161617] h-16 flex items-center justify-between px-10 fixed top-0 w-5/6">
    <div>
        <p class="text-sm text-gray-400">
            <a href="{{ route('index') }}" class="cursor-pointer hover:text-white">
                <i class="fas fa-chevron-left mr-2"></i>Home
            </a>
        </p>
    </div>
    <div class="relative">
        <button aria-label="User profile"
            class="flex items-center space-x-2 text-gray-400 mr-4 hover:text-white focus:outline-none"
            onclick="toggleDropdown()">
            <i class="fas fa-user-circle fa-lg"></i>
        </button>
        <div id="userDropdown" class="hidden absolute right-0 top-4 w-32 bg-white rounded-md shadow-lg py-1 z-50">
            <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="fas fa-user mr-2"></i> Profile
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>

            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </div>
    </div>
</header>