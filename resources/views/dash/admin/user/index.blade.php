@extends('app')
@section('title', 'Admin Dashboard - Player List')

@push('styles')
    <style>
        :root { color-scheme: dark; --page-bg:#0a0a0a; }
        html, body {
            height:100%;
            min-height:100%;
            background:var(--page-bg);
            overscroll-behavior-y:none;
            overscroll-behavior-x:none;
            touch-action:pan-y;
            -webkit-text-size-adjust:100%;
        }
        #antiBounceBg{
            position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg);
            z-index:-1; pointer-events:none;
        }
        .scroll-safe{ background-color:#171717; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
        .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.55rem .9rem; border-radius:.55rem; font-weight:600; font-size:.9rem; transition:.15s ease; }
        .btn-green{ background:#16a34a; color:#fff; }
        .btn-green:hover{ background:#15803d; }
    </style>
@endpush

@section('content')
    <div id="antiBounceBg" aria-hidden="true"></div>

    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
                @include('partials.topbar')

                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <!-- Header hanya judul -->
                    <div class="mb-4">
                        <h1 class="text-2xl sm:text-3xl font-extrabold">Data Player</h1>
                    </div>

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

                    {{-- Search bar + Export sejajar --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
                        <div class="w-full sm:w-auto">
                            <input
                                name="search"
                                value="{{ request('search') }}"
                                class="w-full sm:w-72 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                placeholder="Cari player..."
                                type="search"
                                onchange="window.location.href='{{ route('admin.users.index') }}?search=' + encodeURIComponent(this.value)"
                            />
                        </div>

                        <div class="w-full sm:w-auto">
                            <a
                                href="{{ route('admin.users.export', request()->only('search')) }}"
                                class="flex items-center justify-center gap-1 border border-green-500 text-green-500 rounded px-3 py-2 text-xs sm:text-sm hover:bg-green-500 hover:text-white transition whitespace-nowrap">
                                <i class="fas fa-file-excel"></i>
                                Export Excel
                            </a>
                        </div>
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

