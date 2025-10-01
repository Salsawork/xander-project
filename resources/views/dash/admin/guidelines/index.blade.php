@extends('app')
@section('title', 'Admin Dashboard - Guidelines List')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold mb-6">
                    Guidelines
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
                    <form method="GET" action="{{ route('admin.guidelines.index') }}"
                        class="flex flex-col sm:flex-row gap-2 w-full">
                        <input
                            class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                            placeholder="Search" type="search" name="search" value="{{ request('search') }}" />

                        <select name="category"
                            class="bg-[#2c2c2c] text-gray-500 text-xs rounded border border-gray-700 px-2 py-1 cursor-pointer">
                            <option value="">Semua Kategori</option>
                            <option value="BEGINNER" {{ request('category') == 'BEGINNER' ? 'selected' : '' }}>Beginner
                            </option>
                            <option value="INTERMEDIATE" {{ request('category') == 'INTERMEDIATE' ? 'selected' : '' }}>
                                Intermediate</option>
                            <option value="MASTER" {{ request('category') == 'MASTER' ? 'selected' : '' }}>Master</option>
                            <option value="GENERAL" {{ request('category') == 'GENERAL' ? 'selected' : '' }}>General
                            </option>
                        </select>

                        <button type="submit"
                            class="border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-1 text-sm hover:bg-[#1e90ff] hover:text-white transition">
                            Filter
                        </button>
                    </form>

                    <a href="{{ route('admin.guidelines.create') }}"
                        class="flex items-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-1 text-sm hover:bg-[#1e90ff] hover:text-white transition">
                        <i class="fas fa-plus"></i>
                        Tambah Guideline
                    </a>
                </div>


                <div class="px-8 overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                        <thead class="bg-[#2c2c2c] text-gray-300">
                            <tr>
                                <th class="px-4 py-3">
                                    Judul Guideline
                                </th>
                                <th class="px-4 py-3">
                                    Kategori
                                </th>
                                <th class="px-4 py-3">
                                    Penulis
                                </th>
                                <th class="px-4 py-3">
                                    Status
                                </th>
                                <th class="px-4 py-3">
                                    Tanggal
                                </th>
                                <th class="px-4 py-3 text-right">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @forelse ($guidelines as $guideline)
                                <tr>
                                    <td class="flex items-center gap-4 px-4 py-3">
                                        <div
                                            class="w-12 h-12 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                            @if (!empty($guideline->featured_image))
                                                @php
                                                    $imagePath = $guideline->featured_image;
                                                    // Cek apakah path dimulai dengan 'guidelines/' (dari storage)
                                                    if (Str::startsWith($imagePath, 'guidelines/')) {
                                                        $imagePath = Storage::url($imagePath);
                                                    }
                                                    // Cek apakah gambar ada di public path
                                                    elseif (
                                                        !file_exists(public_path($imagePath)) &&
                                                        file_exists(
                                                            public_path('images/guidelines/' . basename($imagePath)),
                                                        )
                                                    ) {
                                                        $imagePath = asset('images/guidelines/' . basename($imagePath));
                                                    }
                                                    // Jika tidak, gunakan asset biasa
                                                    else {
                                                        $imagePath = asset($imagePath);
                                                    }
                                                @endphp
                                                <img src="{{ $imagePath }}" alt="{{ $guideline->title }}"
                                                    class="object-cover w-full h-full">
                                            @else
                                                <i class="fas fa-book text-gray-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h3 class="font-medium">{{ $guideline->title }}</h3>
                                            <p class="text-xs text-gray-400">{{ Str::limit($guideline->description, 60) }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full 
                                            @if ($guideline->category == 'BEGINNER') bg-green-900/50 text-green-400 
                                            @elseif($guideline->category == 'INTERMEDIATE') bg-yellow-900/50 text-yellow-400 
                                            @elseif($guideline->category == 'MASTER') bg-red-900/50 text-red-400 
                                            @else bg-gray-900/50 text-gray-400 @endif">
                                            {{ $guideline->category }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">
                                        {{ $guideline->author_name }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-900/50 text-green-400">
                                                {{ $guideline->status }}
                                            </span>
                                            @if ($guideline->is_new)
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-900/50 text-blue-400">
                                                    New
                                                </span>
                                            @endif
                                            @if ($guideline->is_featured)
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full bg-purple-900/50 text-purple-400">
                                                    Featured
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">
                                        {{ $guideline->published_at->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('guideline.show', $guideline->slug) }}" target="_blank"
                                                class="text-gray-400 hover:text-white" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.guidelines.edit', $guideline->id) }}"
                                                class="text-gray-400 hover:text-white" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="text-gray-400 hover:text-white delete-btn"
                                                data-id="{{ $guideline->id }}" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $guideline->id }}"
                                                action="{{ route('admin.guidelines.destroy', $guideline->id) }}"
                                                method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">
                                        Belum ada guideline. Silakan tambahkan guideline baru.
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
            // Delete confirmation
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Guideline yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('delete-form-' + id).submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
