<aside class="bg-gray-900 w-1/6 flex-shrink-0 sticky top-0 h-screen">
    <header class="bg-gray-900 h-12 flex items-center justify-end px-4 mt-8">
        <a href="{{ route('index') }}" class="flex items-center justify-center w-full">
            <img src="{{ asset('/images/logo.png') }}" alt="Logo" class="h-8" />
        </a>
    </header>
    <ul class="space-y-6 text-sm font-semibold p-6">
        <li>
            <a href="{{ route('dashboard') }}"
                class="block {{ request()->routeIs('dashboard') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Profile
            </a>
        </li>
        <li>
            <a href="{{ route('notification.index') }}"
                class="block {{ request()->routeIs('notification.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Notification
            </a>
        </li>
        <li>
            <a href="{{ route('myorder.index') }}"
                class="block {{ request()->routeIs('myorder.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                My Order
            </a>
        </li>
        <li>
            <a href="{{ route('booking.index') }}"
                class="block {{ request()->routeIs('booking.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Booking
            </a>
        </li>
    </ul>
</aside>
