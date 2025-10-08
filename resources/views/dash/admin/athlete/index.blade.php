@extends('app')
@section('title', 'Admin Dashboard - Daftar Athlete')

@section('content')
    <div class="flex flex-col min-h-screen bg-[#1E1E1F] text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <div class="p-4 sm:p-8 mt-12">
                    <h1 class="text-3xl font-extrabold mb-6">
                        Daftar Athlete
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
                    <form action="{{ route('athlete.index') }}" method="GET" class="flex w-full sm:w-auto gap-2">
                        <input
                            class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                            name="search" value="{{ request('search') }}" placeholder="Cari athlete..." type="search" />
                        <button type="submit"
                            class="px-3 py-2 rounded-md border border-[#1e90ff] text-[#1e90ff] hover:bg-[#1e90ff] hover:text-white transition text-sm">
                            Cari
                        </button>
                    </form>

                    <div class="flex gap-2 items-center">
                        <a href="{{ route('athlete.create') }}"
                            class="flex items-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-1 text-sm hover:bg-[#1e90ff] hover:text-white transition">
                            <i class="fas fa-plus">
                            </i>
                            Tambah Athlete
                        </a>
                    </div>
                </div>
                <div class="px-8 overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                        <thead class="bg-[#2c2c2c] text-gray-300">
                            <tr>
                                <th class="px-4 py-3">
                                    Foto
                                </th>
                                <th class="px-4 py-3">
                                    Nama
                                </th>
                                <th class="px-4 py-3">
                                    Email
                                </th>
                                <th class="px-4 py-3">
                                    Spesialisasi
                                </th>
                                <th class="px-4 py-3">
                                    Lokasi
                                </th>
                                <th class="px-4 py-3">
                                    Harga/Sesi
                                </th>
                                <th class="px-4 py-3 text-right">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @forelse ($athletes as $athlete)
                                <tr class="bg-[#1c1c1c] hover:bg-[#2c2c2c] transition">
                                    <td class="px-4 py-3">
                                        @if (
                                            $athlete->athleteDetail &&
                                                $athlete->athleteDetail->image &&
                                                Storage::disk('public')->exists($athlete->athleteDetail->image))
                                            <img src="{{ asset('storage/' . $athlete->athleteDetail->image) }}"
                                                alt="{{ $athlete->name }}" class="h-10 w-10 rounded-full object-cover">
                                        @elseif ($athlete->athleteDetail && $athlete->athleteDetail->image)
                                            <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}"
                                                alt="{{ $athlete->name }}" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-700 flex items-center justify-center">
                                                <span class="text-xs text-gray-300">No Img</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        {{ $athlete->name }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">
                                        {{ $athlete->email }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">
                                        {{ $athlete->athleteDetail ? $athlete->athleteDetail->specialty : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">
                                        {{ $athlete->athleteDetail ? $athlete->athleteDetail->location : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">
                                        Rp
                                        {{ number_format($athlete->athleteDetail ? $athlete->athleteDetail->price_per_session : 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        @if ($athlete->athleteDetail?->id)
                                            <a href="{{ route('athlete.edit', $athlete->athleteDetail->id) }}"
                                                class="text-blue-400 hover:text-blue-300">
                                                <i class="fas fa-edit"></i>
                                                Edit
                                            </a>
                                        @endif
                                        @if ($athlete->athleteDetail?->id)
                                            <button class="text-red-400 hover:text-red-300 delete-btn"
                                                data-id="{{ $athlete->athleteDetail->id }}"
                                                data-name="{{ $athlete->name }}">
                                                <i class="fas fa-trash"></i>
                                                Hapus
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-[#1c1c1c]">
                                    <td colspan="7" class="px-4 py-3 text-center text-gray-400">
                                        Tidak ada data athlete.
                                    </td>
                                </tr>
                            @endforelse
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
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi tombol hapus
            const deleteButtons = document.querySelectorAll('.delete-btn');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    Swal.fire({
                        title: 'Hapus Athlete?',
                        text: `Apakah kamu yakin ingin menghapus athlete "${name}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        background: '#262626',
                        color: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Buat form untuk delete request
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = "{{ url('dashboard/athlete') }}/" + id;
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

            // Tampilkan SweetAlert untuk pesan sukses
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#262626',
                    color: '#fff'
                });
            @endif

            // Tampilkan SweetAlert untuk pesan error
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: '{{ session('error') }}',
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#262626',
                    color: '#fff'
                });
            @endif
        });
    </script>
@endpush
