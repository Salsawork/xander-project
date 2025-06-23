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
                Shop Overview
            </a>
        </li>
        <li>
            <div class="mb-2">Product</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('products.index') }}"
                        class="block {{ request()->routeIs('products.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        My Products
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.create') }}"
                        class="block {{ request()->routeIs('products.create') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Add New Product
                    </a>
                </li>
                <li>
                    <a href="{{ route('products.edit', $product->id ?? 1) }}"
                        class="block {{ request()->routeIs('products.edit') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Edit Product
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <div class="mb-2">Order</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('order.index') }}"
                        class="block {{ request()->routeIs('order.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Order List
                    </a>
                </li>
                <li>
                    <a href="{{ route('order.detail.index') }}"
                        class="block {{ request()->routeIs('order.detail.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Detail Order
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <a href="{{ route('promo.index') }}"
                class="block {{ request()->routeIs('promo.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                Promo Management
            </a>
        </li>
        <li>
            <div class="mb-2">Community</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('comunity.index') }}"
                        class="block {{ request()->routeIs('comunity.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Daftar Berita
                    </a>
                </li>
                <li>
                    <a href="{{ route('comunity.create') }}"
                        class="block {{ request()->routeIs('comunity.create') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Tambah Berita
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <div class="mb-2">Guidelines</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('admin.guidelines.index') }}"
                        class="block {{ request()->routeIs('admin.guidelines.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Daftar Guidelines
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.guidelines.create') }}"
                        class="block {{ request()->routeIs('admin.guidelines.create') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Tambah Guideline
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <div class="mb-2">Venue/Partner Management</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('venue.index') }}"
                        class="block {{ request()->routeIs('venue.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Daftar Venue
                    </a>
                </li>
                <li>
                    <a href="{{ route('venue.create') }}"
                        class="block {{ request()->routeIs('venue.create') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Tambah Venue
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <div class="mb-2">Athlete Management</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('athlete.index') }}"
                        class="block {{ request()->routeIs('athlete.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Daftar Athlete
                    </a>
                </li>
                <li>
                    <a href="{{ route('athlete.create') }}"
                        class="block {{ request()->routeIs('athlete.create') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Tambah Athlete
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <div class="mb-2">Tournament</div>
            <ul class="text-xs font-normal text-gray-300 space-y-1 ml-3">
                <li>
                    <a href="{{ route('tournament.index') }}"
                        class="block {{ request()->routeIs('tournament.index') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Daftar Tournament
                    </a>
                </li>
                <li>
                    <a href="{{ route('tournament.create') }}"
                        class="block {{ request()->routeIs('tournament.create') ? 'text-[#0a8aff]' : 'text-gray-300 hover:text-white' }}">
                        Tambah Tournament
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>
