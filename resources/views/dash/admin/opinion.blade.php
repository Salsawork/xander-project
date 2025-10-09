@extends('app')
@section('title', 'Admin Dashboard - Opinion List')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                
                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                        Data Opinion
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
                            placeholder="Cari opinion..." type="search"
                            onchange="window.location.href='{{ route('dash.admin.opinion') }}?search=' + this.value" />
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Subject</th>
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse ($opinions as $opinion)
                                    <tr>
                                        <td class="px-4 py-3">{{ $opinion->email }}</td>
                                        <td class="px-4 py-3">{{ $opinion->subject }}</td>
                                        <td class="px-4 py-3">{{ $opinion->description }}</td>
                                        <td class="px-4 py-3 text-gray-400">{{ $opinion->created_at->format('d M Y') }}</td>
                                    
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                            Belum ada opinion
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse ($opinions as $opinion)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <!-- Header -->
                                <div class="mb-3 pb-3 border-b border-gray-700">
                                    <h3 class="font-semibold text-base mb-2">{{ $opinion->email }}</h3>
                                    <p class="text-xs text-gray-400">{{ $opinion->subject }}</p>
                                    <p class="text-xs text-gray-400">{{ $opinion->description }}</p>
                                    <p class="text-xs text-gray-400">{{ $opinion->created_at->format('d M Y') }}</p>
                                </div>

                        
                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Belum ada opinion.</p>
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
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "opinion yang dihapus tidak dapat dikembalikan!",
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
    </script> --}}
@endpush