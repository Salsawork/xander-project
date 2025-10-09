@extends('app')
@section('title', 'Admin Dashboard - Tournament List')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                
                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                        Tournament
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
                        <input name="search" value="{{ request('search') }}"
                            class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                            placeholder="Cari tournament..." type="search"
                            onchange="window.location.href='{{ route('tournament.index') }}?search=' + this.value" />
                        
                        <a href="{{ route('tournament.create') }}"
                            class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                            <i class="fas fa-plus"></i>
                            Tambah Tournament
                        </a>
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Judul Tournament</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse ($tournaments as $tournament)
                                    <tr>
                                        <td class="px-4 py-3">{{ $tournament->name }}</td>
                                        <td class="px-4 py-3 text-gray-400">{{ $tournament->created_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-3 text-gray-400">
                                                <a href="{{ route('guideline.show', $tournament->slug) }}" target="_blank"
                                                    class="hover:text-white" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('tournament.edit', $tournament) }}"
                                                    class="hover:text-white" title="Edit">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                <button type="button" class="hover:text-white delete-btn"
                                                    data-id="{{ $tournament->id }}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <form id="delete-form-{{ $tournament->id }}"
                                                    action="{{ route('tournament.destroy', $tournament) }}" method="POST"
                                                    class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                            Belum ada tournament. Silakan tambahkan tournament baru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse ($tournaments as $tournament)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <!-- Header -->
                                <div class="mb-3 pb-3 border-b border-gray-700">
                                    <h3 class="font-semibold text-base mb-2">{{ $tournament->name }}</h3>
                                    <p class="text-xs text-gray-400">{{ $tournament->created_at->format('d M Y') }}</p>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <a href="{{ route('guideline.show', $tournament->slug) }}" target="_blank"
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                        View
                                    </a>
                                    <a href="{{ route('tournament.edit', $tournament) }}"
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                        <i class="fas fa-pen text-xs"></i>
                                        Edit
                                    </a>
                                    <button type="button" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition delete-btn"
                                        data-id="{{ $tournament->id }}">
                                        <i class="fas fa-trash text-xs"></i>
                                        Delete
                                    </button>
                                    <form id="delete-form-{{ $tournament->id }}"
                                        action="{{ route('tournament.destroy', $tournament) }}" method="POST"
                                        class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Belum ada tournament. Silakan tambahkan tournament baru.</p>
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
                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Tournament yang dihapus tidak dapat dikembalikan!",
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
                            document.getElementById('delete-form-' + id).submit();
                        }
                    });
                });
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#222',
                    color: '#fff'
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#222',
                    color: '#fff'
                });
            @endif
        });
    </script>
@endpush