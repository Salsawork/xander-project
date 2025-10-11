@extends('app')
@section('title', 'Admin Dashboard - Daftar Event')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')

        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')

            <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                    Daftar Event
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

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                    <input
                        class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                        name="search" value="{{ request('search') }}" placeholder="Cari event..."
                        type="search"
                        onchange="window.location.href='{{ route('admin.event.index') }}?search=' + this.value" />

                    <a href="{{ route('admin.event.create') }}"
                        class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                        <i class="fas fa-plus"></i>
                        Tambah Event
                    </a>
                </div>

                <!-- Desktop & Tablet Table View -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                        <thead class="bg-[#2c2c2c] text-gray-300">
                            <tr>
                                <th class="px-4 py-3">Image</th>
                                <th class="px-4 py-3">Name Event</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Lokasi</th>
                                <th class="px-4 py-3">Total Prize</th>
                                {{-- <th class="px-4 py-3">Champhion Prize</th>
                                <th class="px-4 py-3">Champhion Prize</th> --}}
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @forelse ($events as $event)
                                <tr class="bg-[#1c1c1c] hover:bg-[#2c2c2c] transition">
                                    <td class="px-4 py-3">
                                        @if ($event->image && file_exists(public_path('event_images/' . $event->image)))
                                            <img src="{{ asset('event_images/' . $event->image) }}"
                                                class="w-16 h-16 rounded object-cover" alt="{{ $event->name }}">
                                        @else
                                            <div class="w-16 h-16 flex items-center justify-center bg-gray-700 text-xs text-gray-400 rounded">
                                                No Img
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $event->name }}</td>
                                    <td class="px-4 py-3 text-gray-300">{{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-gray-300">{{ $event->location ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-300">
                                        Rp {{ number_format($event->total_prize_money ?? 0, 0, ',', '.') }}
                                    </td>
                                    {{-- <td class="px-4 py-3 text-gray-300">
                                        Rp {{ number_format($event->champion_prize ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">
                                        Rp {{ number_format($event->champion_prize ?? 0, 0, ',', '.') }}
                                    </td> --}}
                                    <td class="px-4 py-3 text-gray-300">
                                        {{$event->status}}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex gap-3 text-gray-400 justify-end">
                                            <a href="{{ route('admin.event.edit', $event->id) }}"
                                                class="hover:text-gray-200" title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <button class="hover:text-gray-200 delete-btn"
                                                data-id="{{ $event->id }}"
                                                data-name="{{ $event->name }}" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-[#1c1c1c]">
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">
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
                                @if ($event->image && file_exists(public_path('event_images/' . $event->image)))
                                    <img src="{{ asset('event_images/' . $event->image) }}"
                                        alt="{{ $event->name }}" class="h-16 w-16 rounded object-cover shrink-0">
                                @else
                                    <div class="h-16 w-16 rounded bg-gray-700 flex items-center justify-center shrink-0">
                                        <span class="text-xs text-gray-300">No Img</span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-base mb-1">{{ $event->name }}</h3>
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}</p>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm mb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Lokasi:</span>
                                    <span class="text-xs text-right">{{ $event->location ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Harga:</span>
                                    <span class="text-xs font-medium">Rp {{ number_format($event->price ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2 pt-3 border-t border-gray-700">
                                <a href="{{ route('admin.event.edit', $event->id) }}" 
                                    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                    <i class="fas fa-pen text-xs"></i>
                                    Edit
                                </a>
                                <button class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition delete-btn"
                                    data-id="{{ $event->id }}"
                                    data-name="{{ $event->name }}">
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');

            Swal.fire({
                title: 'Hapus Event?',
                text: `Apakah kamu yakin ingin menghapus event "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#222',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ url('dashboard/event') }}/" + id;
                    form.style.display = 'none';

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(method);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
