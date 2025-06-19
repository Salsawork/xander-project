<aside class="bg-[#2D2D2D] w-1/6 flex-shrink-0 sticky top-0 h-screen">
    <header class="bg-[#2D2D2D] h-12 flex items-center justify-end px-4 mt-8">
        <a href="{{ route('index') }}" class="flex items-center justify-center w-full">
            <img src="{{ asset('/images/logo.png') }}" alt="Logo" class="h-8" />
        </a>
    </header>
    <ul class="space-y-6 text-sm font-semibold p-6">
        <li>
            <a href="{{ route('athlete.dashboard') }}"
                class="block {{ request()->routeIs('athlete.dashboard') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Dashboard
            </a>
        </li>
        <li class="py-2">
            <div class="flex items-center justify-between cursor-pointer {{ request()->routeIs('athlete.sparring*') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                <span>Sparring</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
            <ul class="pl-4 mt-2 space-y-2">
                <li>
                    <a href="{{ route('athlete.match.create') }}" 
                        class="block {{ request()->routeIs('athlete.match.create') ? 'text-[#0a8aff]' : 'text-gray-400 hover:text-white' }}">
                        Create Session
                    </a>
                </li>
                <li>
                    <a href="{{ route('athlete.match') }}" 
                        class="block {{ request()->routeIs('athlete.match') ? 'text-[#0a8aff]' : 'text-gray-400 hover:text-white' }}">
                        Match History
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
