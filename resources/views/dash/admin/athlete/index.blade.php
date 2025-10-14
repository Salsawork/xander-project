@extends('app')
@section('title', 'Admin Dashboard - Daftar Athlete')

@push('styles')
<style>
    /* ====== Anti overscroll / white bounce ====== */
    :root{ color-scheme: dark; --page-bg:#0a0a0a; }
    html, body{
        height:100%;
        min-height:100%;
        background:var(--page-bg);
        overscroll-behavior-y: none;   /* cegah rubber-band ke body */
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust:100%;
    }
    /* Kanvas gelap tetap di belakang konten */
    #antiBounceBg{
        position: fixed;
        left:0; right:0;
        top:-120svh; bottom:-120svh;   /* svh stabil di mobile */
        background:var(--page-bg);
        z-index:-1;
        pointer-events:none;
    }
    /* Pastikan area scroll utama tidak meneruskan overscroll ke body */
    .scroll-safe{
        background-color:#171717;      /* senada dengan bg-neutral-900 */
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

                        Daftar Athlete
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
                            name="search" value="{{ request('search') }}" placeholder="Cari athlete..." type="search"
                            onchange="window.location.href='{{ route('athlete.index') }}?search=' + this.value" />

                        <a href="{{ route('athlete.create') }}"
                            class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                            <i class="fas fa-plus"></i>
                            Tambah Athlete
                        </a>
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Foto</th>
                                    <th class="px-4 py-3">Nama</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Spesialisasi</th>
                                    <th class="px-4 py-3">Lokasi</th>
                                    <th class="px-4 py-3">Harga/Sesi</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @forelse ($athletes as $athlete)
                                    <tr class="bg-[#1c1c1c] hover:bg-[#2c2c2c] transition">
                                        <td class="px-4 py-3">
                                            @if ($athlete->athleteDetail && $athlete->athleteDetail->image && Storage::disk('public')->exists($athlete->athleteDetail->image))
                                                <img src="{{ asset('storage/' . $athlete->athleteDetail->image) }}"
                                                    alt="{{ $athlete->name }}" class="h-10 w-10 rounded-full object-cover">
                                            @elseif ($athlete->athleteDetail && $athlete->athleteDetail->image)
                                                <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}"
                                                    alt="{{ $athlete->name }}" class="h-10 w-10 rounded-full object-cover">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-700 flex items-center justify-center">
                                                    <span class="text-xs text-gray-300">No Img</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-medium">{{ $athlete->name }}</td>
                                        <td class="px-4 py-3 text-gray-300">{{ $athlete->email }}</td>
                                        <td class="px-4 py-3 text-gray-300">
                                            {{ $athlete->athleteDetail ? $athlete->athleteDetail->specialty : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-300">
                                            {{ $athlete->athleteDetail ? $athlete->athleteDetail->location : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-300">
                                            Rp {{ number_format($athlete->athleteDetail ? $athlete->athleteDetail->price_per_session : 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-3 text-gray-400 justify-end">
                                                @if ($athlete->athleteDetail?->id)
                                                    <a href="{{ route('athlete.edit', $athlete->athleteDetail->id) }}"
                                                        class="hover:text-gray-200" title="Edit">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                @endif
                                                @if ($athlete->athleteDetail?->id)
                                                    <button class="hover:text-gray-200 delete-btn"
                                                        data-id="{{ $athlete->athleteDetail->id }}"
                                                        data-name="{{ $athlete->name }}" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-[#1c1c1c]">
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                            Tidak ada data athlete.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse ($athletes as $athlete)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <!-- Header with Photo and Name -->
                                <div class="flex gap-3 mb-3 pb-3 border-b border-gray-700">
                                    @if ($athlete->athleteDetail && $athlete->athleteDetail->image && Storage::disk('public')->exists($athlete->athleteDetail->image))
                                        <img src="{{ asset('storage/' . $athlete->athleteDetail->image) }}"
                                            alt="{{ $athlete->name }}" class="h-16 w-16 rounded-full object-cover shrink-0">
                                    @elseif ($athlete->athleteDetail && $athlete->athleteDetail->image)
                                        <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}"
                                            alt="{{ $athlete->name }}" class="h-16 w-16 rounded-full object-cover shrink-0">
                                    @else
                                        <div class="h-16 w-16 rounded-full bg-gray-700 flex items-center justify-center shrink-0">
                                            <span class="text-xs text-gray-300">No Img</span>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-base mb-1">{{ $athlete->name }}</h3>
                                        <p class="text-xs text-gray-400 break-all">{{ $athlete->email }}</p>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="space-y-2 text-sm mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Spesialisasi:</span>
                                        <span class="text-xs text-right">{{ $athlete->athleteDetail ? $athlete->athleteDetail->specialty : '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Lokasi:</span>
                                        <span class="text-xs text-right">{{ $athlete->athleteDetail ? $athlete->athleteDetail->location : '-' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Harga/Sesi:</span>
                                        <span class="text-xs font-medium">Rp {{ number_format($athlete->athleteDetail ? $athlete->athleteDetail->price_per_session : 0, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                @if ($athlete->athleteDetail?->id)
                                    <div class="flex gap-2 pt-3 border-t border-gray-700">
                                        <a href="{{ route('athlete.edit', $athlete->athleteDetail->id) }}" 
                                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                            <i class="fas fa-pen text-xs"></i>
                                            Edit
                                        </a>
                                        <button class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition delete-btn"
                                            data-id="{{ $athlete->athleteDetail->id }}"
                                            data-name="{{ $athlete->name }}">
                                            <i class="fas fa-trash text-xs"></i>
                                            Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Tidak ada data athlete.</p>
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
                        title: 'Hapus Athlete?',
                        text: `Apakah kamu yakin ingin menghapus athlete "${name}"?`,
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
