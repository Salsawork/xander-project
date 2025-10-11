@extends('app')
@section('title', 'Admin Dashboard - Player List')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                
                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                        Data Player
                    </h1>

                    {{-- Flash messages --}}
                    @if (session('success'))
                        <div class="mb-4 bg-green-500 text-white px-4 py-2 rounded text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-500 text-white px-4 py-2 rounded text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Search bar --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                        <input name="search" value="{{ request('search') }}"
                            class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                            placeholder="Cari player..." type="search"
                            onchange="window.location.href='{{ route('admin.users.index') }}?search=' + this.value" />
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Status Player</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse ($players as $player)
                                    <tr>
                                        <td class="px-4 py-3">{{ $player->name }}</td>
                                        <td class="px-4 py-3">{{ $player->email }}</td>
                                        <td class="px-4 py-3">
                                            @if ($player->status_player == 1)
                                                <span class="text-green-400 font-semibold">Terverifikasi</span>
                                            @else
                                                <span class="text-yellow-400">Menunggu Verifikasi</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($player->status_player == 0)
                                                <form action="{{ route('admin.users.verify', $player->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="event_id" value="{{ $event_id ?? 1 }}">
                                                    <button type="submit"
                                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition">
                                                        Verify
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400">✔</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                            Belum ada player yang terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse ($players as $player)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <div class="mb-3 pb-3 border-b border-gray-700">
                                    <h3 class="font-semibold text-base mb-2">{{ $player->name }}</h3>
                                    <p class="text-xs text-gray-400">{{ $player->email }}</p>
                                    <p class="text-xs mt-2">
                                        Status:
                                        @if ($player->status_player == 1)
                                            <span class="text-green-400 font-semibold">Terverifikasi</span>
                                        @else
                                            <span class="text-yellow-400">Menunggu Verifikasi</span>
                                        @endif
                                    </p>
                                </div>

                                @if ($player->status_player == 0)
                                    <form action="{{ route('admin.users.verify', $player->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $event_id ?? 1 }}">
                                        <button type="submit"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded transition">
                                            Verify
                                        </button>
                                    </form>
                                @else
                                    <div class="text-gray-400 text-center">✔ Terverifikasi</div>
                                @endif
                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Belum ada player yang terdaftar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
