@extends('app')
@section('title', 'Admin Dashboard - Daftar Venue')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold mb-6">
                    Daftar Venue
                </h1>

                @if (session('success'))
                    <div class="mx-8 mb-4 bg-green-500 text-white px-4 py-2 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mx-8 mb-4 bg-red-500 text-white px-4 py-2 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4 px-8">
                    <input
                        class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                        id="statusFilter"
                        value="{{ request('search') }}"
                        placeholder="Cari venue..." type="search" onchange="window.location.href = '{{ route('venue.index') }}?search=' + this.value" />
                    <div class="flex gap-2 items-center">
                        <a href="{{ route('venue.create') }}"
                            class="flex items-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-1 text-sm hover:bg-[#1e90ff] hover:text-white transition">
                            <i class="fas fa-plus">
                            </i>
                            Tambah Venue
                        </a>
                    </div>
                </div>
                <div class="px-8 overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                        <thead class="bg-[#2c2c2c] text-gray-300">
                            <tr>
                                <th class="px-4 py-3">
                                    Nama Venue
                                </th>
                                <th class="px-4 py-3">
                                    Alamat
                                </th>
                                <th class="px-4 py-3">
                                    Kontak
                                </th>
                                <th class="px-4 py-3">
                                    Jam Operasional
                                </th>
                                <th class="px-4 py-3">
                                    Rating
                                </th>
                                <th class="px-4 py-3">
                                    Action
                                </th>
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
                                    <td class="px-4 py-3">
                                        {{ $venue->address ?? 'Belum diisi' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $venue->phone ?? 'Belum diisi' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $venue->operating_hours ?? 'Belum diisi' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center">
                                            <span class="text-yellow-400 mr-1">
                                                <i class="fas fa-star"></i>
                                            </span>
                                            {{ number_format($venue->rating, 1) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 flex gap-3 text-gray-400">
                                        <a href="{{ route('venue.edit', $venue->id) }}" aria-label="Edit {{ $venue->name }}" class="hover:text-gray-200">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button aria-label="Delete {{ $venue->name }}" class="hover:text-gray-200" onclick="deleteVenue({{ $venue->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            @if(count($venues) == 0)
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada venue yang terdaftar
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
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
                // Buat form untuk mengirim request DELETE
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/dashboard/venue/${id}`;

                // Tambahkan CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Tambahkan method DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);

                // Tambahkan form ke body dan submit
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Tampilkan SweetAlert untuk pesan sukses
    @if(session('success'))
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

    // Tampilkan SweetAlert untuk pesan error
    @if(session('error'))
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
