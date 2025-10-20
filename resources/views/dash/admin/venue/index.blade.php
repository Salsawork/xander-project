@extends('app')
@section('title', 'Admin Dashboard - Daftar Venue')

@push('styles')
<style>
    :root{ color-scheme: dark; --page-bg:#0a0a0a; }
    html, body{
        height:100%;
        min-height:100%;
        background:var(--page-bg);
        overscroll-behavior-y: none;
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust:100%;
    }
    #antiBounceBg{
        position: fixed;
        left:0; right:0;
        top:-120svh; bottom:-120svh;
        background:var(--page-bg);
        z-index:-1;
        pointer-events:none;
    }
    .scroll-safe{
        background-color:#171717;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
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
                    <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                        Daftar Venue
                    </h1>

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

                    <!-- Bagian Search + Tambah + Export -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <input
                                class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Cari venue..."
                                type="search"
                                onchange="window.location.href='{{ route('venue.index') }}?search=' + this.value" />
                        </div>

                        <div class="flex gap-3">
                            <!-- Tombol Export Excel -->
                            <a href="{{ route('venue.export', ['search' => request('search')]) }}"
                                class="flex items-center justify-center gap-2 border border-green-500 text-green-400 rounded px-3 py-2 text-xs sm:text-sm hover:bg-green-500 hover:text-white transition whitespace-nowrap">
                                <i class="fas fa-file-excel"></i>
                                Export Excel
                            </a>

                            <!-- Tombol Tambah Venue -->
                            <a href="{{ route('venue.create') }}"
                                class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                                <i class="fas fa-plus"></i>
                                Tambah Venue
                            </a>
                        </div>
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Nama Venue</th>
                                    <th class="px-4 py-3">Alamat</th>
                                    <th class="px-4 py-3">Kontak</th>
                                    <th class="px-4 py-3">Jam Operasional</th>
                                    <th class="px-4 py-3">Rating</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach ($venues as $venue)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div>
                                                <a href="{{ route('venue.edit', $venue->id) }}"
                                                    class="hover:text-blue-400 font-medium">{{ $venue->name }}</a>
                                                <p class="text-xs text-gray-500">{{ $venue->user->name }} ({{ $venue->user->email }})</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">{{ $venue->address ?? 'Belum diisi' }}</td>
                                        <td class="px-4 py-3">{{ $venue->phone ?? 'Belum diisi' }}</td>
                                        <td class="px-4 py-3">{{ $venue->operating_hours ?? 'Belum diisi' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <span class="text-yellow-400 mr-1"><i class="fas fa-star"></i></span>
                                                {{ number_format($venue->rating, 1) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-3 text-gray-400">
                                                <a href="{{ route('venue.orders', $venue->id) }}" aria-label="Lihat Pesanan" class="hover:text-gray-200">
                                                    <i class="fas fa-receipt"></i>
                                                </a>
                                                
                                                <a href="{{ route('venue.edit', $venue->id) }}" aria-label="Edit {{ $venue->name }}" class="hover:text-gray-200">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <button aria-label="Delete {{ $venue->name }}" class="hover:text-gray-200"
                                                    onclick="deleteVenue({{ $venue->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if (count($venues) == 0)
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                            Belum ada venue yang terdaftar
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @foreach ($venues as $venue)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <div class="mb-3 pb-3 border-b border-gray-700">
                                    <h3 class="font-semibold text-base mb-1">{{ $venue->name }}</h3>
                                    <p class="text-xs text-gray-500">{{ $venue->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $venue->user->email }}</p>
                                </div>

                                <div class="space-y-2 text-sm mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Alamat:</span>
                                        <span class="text-right text-xs max-w-[60%]">{{ $venue->address ?? 'Belum diisi' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Kontak:</span>
                                        <span class="text-xs">{{ $venue->phone ?? 'Belum diisi' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Jam Operasional:</span>
                                        <span class="text-xs">{{ $venue->operating_hours ?? 'Belum diisi' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400">Rating:</span>
                                        <div class="flex items-center">
                                            <span class="text-yellow-400 mr-1">
                                                <i class="fas fa-star text-xs"></i>
                                            </span>
                                            <span class="text-xs">{{ number_format($venue->rating, 1) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2 pt-3 border-t border-gray-700">
                                    <a href="{{ route('venue.orders', $venue->id) }}" aria-label="Lihat Pesanan" class="hover:text-gray-200 flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                    
                                    <a href="{{ route('venue.edit', $venue->id) }}" 
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                        <i class="fas fa-pen text-xs"></i> Edit
                                    </a>
                                    <button onclick="deleteVenue({{ $venue->id }})"
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                        <i class="fas fa-trash text-xs"></i> Delete
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        @if (count($venues) == 0)
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Belum ada venue yang terdaftar</p>
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteVenue(id) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: "Venue yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            background: '#222',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/dashboard/venue/${id}`;
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 3000,
            background: '#222',
            color: '#fff'
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            showConfirmButton: true,
            background: '#222',
            color: '#fff'
        });
    @endif
</script>
@endpush
