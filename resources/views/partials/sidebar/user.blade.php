<aside class="bg-[#1f1f1f] w-64 min-w-[256px] flex-shrink-0 sticky top-0 h-[100dvh] border-r border-white/10">
  <header class="h-16 flex items-center justify-center border-b border-white/10">
    <a href="{{ route('index') }}" class="inline-flex items-center">
      <img src="{{ asset('/images/logo/logo-xander.png') }}" alt="Logo" class="h-9">
    </a>
  </header>

  <nav class="p-6">
    <ul class="space-y-4 text-[15px] font-semibold">
      <li>
        <a href="{{ route('dashboard') }}"
           class="group flex items-center justify-between rounded-lg px-3 py-2
                  {{ request()->routeIs('dashboard') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <span>Profile</span>
          @if(request()->routeIs('dashboard'))
            <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
          @endif
        </a>
      </li>

      <li>
        <a href="{{ route('notification.index') }}"
           class="group flex items-center justify-between rounded-lg px-3 py-2
                  {{ request()->routeIs('notification.index') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <span>Notification</span>
          @if(request()->routeIs('notification.index'))
            <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
          @endif
        </a>
      </li>

      <li>
        <a href="{{ route('myorder.index') }}"
           class="group flex items-center justify-between rounded-lg px-3 py-2
                  {{ request()->routeIs('myorder.index') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <span>My Order</span>
          @if(request()->routeIs('myorder.index'))
            <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
          @endif
        </a>
      </li>

      <li>
        <a href="{{ route('booking.index') }}"
           class="group flex items-center justify-between rounded-lg px-3 py-2
                  {{ request()->routeIs('booking.index') ? 'text-[#0a8aff] bg-white/5' : 'text-gray-300 hover:text-white hover:bg-white/5' }}">
          <span>Booking</span>
          @if(request()->routeIs('booking.index'))
            <span class="w-1.5 h-1.5 rounded-full bg-[#0a8aff]"></span>
          @endif
        </a>
      </li>
  </ul>
  </nav>
</aside>
