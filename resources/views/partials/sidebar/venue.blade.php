<aside class="bg-[#2D2D2D] w-1/6 flex-shrink-0 sticky top-0 h-screen">
    <header class="bg-[#2D2D2D] h-12 flex items-center justify-end px-4 mt-8">
        <a href="{{ route('index') }}" class="flex items-center justify-center w-full">
            <img src="{{ asset('/images/logo.png') }}" alt="Logo" class="h-8" />
        </a>
    </header>
    <ul class="space-y-6 text-sm font-semibold p-6">
        <li>
            <a href="{{ route('venue.dashboard') }}"
                class="block {{ request()->routeIs('venue.dashboard') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('venue.booking') }}"
                class="block {{ request()->routeIs('venue.booking') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Booking Management
            </a>
        </li>
        <li>
            <a href="{{ route('venue.promo') }}"
                class="block {{ request()->routeIs('venue.promo') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Promo Management
            </a>
        </li>
        <li>
            <a href="{{ route('venue.transaction') }}"
                class="block {{ request()->routeIs('venue.transaction') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Transaction History
            </a>
        </li>
    </ul>
</aside>