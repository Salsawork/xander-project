@extends('app')
@section('title', 'Admin Dashboard - Daftar Event')

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
                    Daftar Event
                </h1>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                    <input
                        class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                        name="search" value="{{ request('search') }}" placeholder="Cari event..."
                        type="search"
                        onchange="window.location.href='{{ route('admin.event.index') }}?search=' + encodeURIComponent(this.value)" />
                
                    <div class="flex gap-2">
                        <a href="{{ route('admin.event.export', ['search' => request('search')]) }}"
                           class="flex items-center justify-center gap-1 border border-green-500 text-green-500 rounded px-3 py-2 text-xs sm:text-sm hover:bg-green-500 hover:text-white transition whitespace-nowrap">
                            <i class="fas fa-file-excel"></i>
                            Export Excel
                        </a>
                         
                        <button type="button"
                           onclick="confirmAddEvent('{{ route('admin.event.create') }}')"
                           class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                            <i class="fas fa-plus"></i>
                            Tambah Event
                        </button>
                    </div>
                </div>                

                <!-- Desktop Table -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                        <thead class="bg-[#2c2c2c] text-gray-300">
                            <tr>
                                <th class="px-4 py-3">Image</th>
                                <th class="px-4 py-3">Name Event</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3">Total Prize</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @forelse ($events as $event)
                                <tr class="bg-[#1c1c1c] hover:bg-[#2c2c2c] transition">
                                    <td class="px-4 py-3">
                                        @php
                                            $imgPath = null;
                                            if (!empty($event->image_url)) {
                                                $candidates = [
                                                    $event->image_url,
                                                    'storage/'.$event->image_url,
                                                ];
                                                foreach ($candidates as $rel) {
                                                    if (file_exists(public_path($rel))) { $imgPath = asset($rel); break; }
                                                }
                                            }
                                        @endphp
                                        @if ($imgPath)
                                            <img src="{{ $imgPath }}" class="w-16 h-16 rounded object-cover" alt="{{ $event->name }}">
                                        @else
                                            <div class="w-16 h-16 flex items-center justify-center bg-gray-700 text-xs text-gray-400 rounded">
                                                No Img
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $event->name }}</td>
                                    <td class="px-4 py-3 text-gray-300">
                                        {{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">{{ $event->location ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-300">
                                        Rp {{ number_format($event->total_prize_money ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">{{ $event->status }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex gap-3 text-gray-400 justify-end">
                                            <button onclick="confirmEditEvent('{{ route('admin.event.edit', $event->id) }}')"
                                                    class="hover:text-gray-200" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </button>

                                            <button onclick="confirmDeleteEvent('{{ route('admin.event.destroy', $event->id) }}', '{{ $event->name }}')"
                                                    class="hover:text-gray-200" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-[#1c1c1c]">
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                        Tidak ada data event.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden space-y-4">
                    @forelse ($events as $event)
                        <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                            <div class="flex gap-3 mb-3 pb-3 border-b border-gray-700">
                                <img src="{{ $event->image_url ? asset($event->image_url) : 'https://via.placeholder.com/80' }}"
                                     class="h-16 w-16 rounded object-cover shrink-0" alt="">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-base mb-1">{{ $event->name }}</h3>
                                    <p class="text-xs text-gray-400">
                                        {{ $event->start_date ? \Carbon\Carbon::parse($event->start_date)->format('d M Y') : '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-3 border-t border-gray-700">
                                <button onclick="confirmEditEvent('{{ route('admin.event.edit', $event->id) }}')"
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                    <i class="fas fa-pen text-xs"></i>
                                    Edit
                                </button>

                                <button onclick="confirmDeleteEvent('{{ route('admin.event.destroy', $event->id) }}', '{{ $event->name }}')"
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                            <p class="text-gray-400">Tidak ada data event.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</div>

{{-- SweetAlert Script --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 🔹 Konfirmasi Tambah Event
    function confirmAddEvent(url) {
        Swal.fire({
            title: 'Tambah Event?',
            text: "Kamu akan diarahkan ke halaman tambah event.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjut!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#1e90ff',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    // 🔹 Konfirmasi Edit Event
    function confirmEditEvent(url) {
        Swal.fire({
            title: 'Edit Event?',
            text: "Kamu akan membuka halaman edit event ini.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjut!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#1e90ff',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }

    // 🔹 Konfirmasi Delete Event
    function confirmDeleteEvent(url, name) {
        Swal.fire({
            title: 'Hapus Event?',
            text: `Apakah kamu yakin ingin menghapus event "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // 🔹 Notifikasi dari session
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            confirmButtonColor: '#1e90ff',
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('error') }}",
            confirmButtonColor: '#d33',
        });
    @endif
</script>
@endsection
