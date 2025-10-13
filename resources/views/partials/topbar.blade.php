<header class="bg-[#161617] h-16 flex items-center justify-between fixed top-0 w-5/6" style="padding: 0 24px">
    <div class="flex-1 flex justify-start">
        <p class="text-sm text-gray-400">
            <a href="{{ route('index') }}" class="cursor-pointer hover:text-white">
                <i class="fas fa-chevron-left mr-2"></i>Home
            </a>
        </p>
    </div>

    <div class="relative" x-data="{ open: false }">
        <button aria-label="User menu"
            class="flex items-center text-gray-300 hover:text-white focus:outline-none transition duration-200"
            @click="open = !open">
            <i class="fas fa-user-circle fa-lg"></i>
        </button>

        <div x-show="open"
            @click.away="open = false"
            x-transition
            class="absolute right-0 top-8 w-48 bg-[#2a2a2a] rounded-lg shadow-lg py-2 z-50 border border-gray-700">
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
            
                $settingsRoute = match($role) {
                    'admin'   => route('dashboard'),
                    'venue'   => route('venue.dashboard'),
                    'athlete' => route('athlete.dashboard'),
                    'user'    => route('profile.edit'),
                };
            @endphp
            
            <a href="{{ $settingsRoute }}"
            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-200 hover:bg-[#3a3a3a] rounded-md whitespace-nowrap">
                <i class="fas fa-gear w-4 shrink-0 text-gray-300"></i>
                <span>Settings</span>
            </a>
        

            {{-- Logout untuk semua user yang login --}}
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
</header>