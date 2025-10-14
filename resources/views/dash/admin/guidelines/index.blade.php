@extends('app')
@section('title', 'Admin Dashboard - Guidelines List')

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
                        Guidelines
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

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 gap-4">
                        <div class="flex flex-col sm:flex-row gap-2 flex-1">
                            <input
                                class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                placeholder="Search" type="search" name="search" value="{{ request('search') }}"
                                onchange="window.location.href='{{ route('admin.guidelines.index') }}?search=' + this.value + '&category=' + document.getElementById('categoryFilter').value" />

                            <select name="category" id="categoryFilter"
                                class="bg-[#2c2c2c] text-gray-500 text-xs sm:text-sm rounded border border-gray-700 px-2 py-2 cursor-pointer"
                                onchange="window.location.href='{{ route('admin.guidelines.index') }}?search=' + document.querySelector('input[name=search]').value + '&category=' + this.value">
                                <option value="">Semua Kategori</option>
                                <option value="BEGINNER" {{ request('category') == 'BEGINNER' ? 'selected' : '' }}>Beginner</option>
                                <option value="INTERMEDIATE" {{ request('category') == 'INTERMEDIATE' ? 'selected' : '' }}>Intermediate</option>
                                <option value="MASTER" {{ request('category') == 'MASTER' ? 'selected' : '' }}>Master</option>
                                <option value="GENERAL" {{ request('category') == 'GENERAL' ? 'selected' : '' }}>General</option>
                            </select>
                        </div>

                        <a href="{{ route('admin.guidelines.create') }}"
                            class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                            <i class="fas fa-plus"></i>
                            Tambah Guideline
                        </a>
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Judul Guideline</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3">Penulis</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse ($guidelines as $guideline)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                                    @if (!empty($guideline->featured_image))
                                                        @php
                                                            $imagePath = $guideline->featured_image;
                                                            if (Str::startsWith($imagePath, 'guidelines/')) {
                                                                $imagePath = Storage::url($imagePath);
                                                            }
                                                            elseif (!file_exists(public_path($imagePath)) && file_exists(public_path('images/guidelines/' . basename($imagePath)))) {
                                                                $imagePath = asset('images/guidelines/' . basename($imagePath));
                                                            }
                                                            else {
                                                                $imagePath = asset($imagePath);
                                                            }
                                                        @endphp
                                                        <img src="{{ $imagePath }}" alt="{{ $guideline->title }}"
                                                            class="object-cover w-full h-full rounded">
                                                    @else
                                                        <i class="fas fa-book text-gray-400"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h3 class="font-medium">{{ $guideline->title }}</h3>
                                                    <p class="text-xs text-gray-400">{{ Str::limit($guideline->description, 60) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                @if ($guideline->category == 'BEGINNER') bg-green-900/50 text-green-400 
                                                @elseif($guideline->category == 'INTERMEDIATE') bg-yellow-900/50 text-yellow-400 
                                                @elseif($guideline->category == 'MASTER') bg-red-900/50 text-red-400 
                                                @else bg-gray-900/50 text-gray-400 @endif">
                                                {{ $guideline->category }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-400">{{ $guideline->author_name }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-1">
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-900/50 text-green-400">
                                                    {{ $guideline->status }}
                                                </span>
                                                @if ($guideline->is_new)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-900/50 text-blue-400">New</span>
                                                @endif
                                                @if ($guideline->is_featured)
                                                    <span class="px-2 py-1 text-xs rounded-full bg-purple-900/50 text-purple-400">Featured</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-400">{{ $guideline->published_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3">
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

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse ($guidelines as $guideline)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <!-- Header with Image and Title -->
                                <div class="flex gap-3 mb-3">
                                    <div class="w-16 h-16 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                        @if (!empty($guideline->featured_image))
                                            @php
                                                $imagePath = $guideline->featured_image;
                                                if (Str::startsWith($imagePath, 'guidelines/')) {
                                                    $imagePath = Storage::url($imagePath);
                                                }
                                                elseif (!file_exists(public_path($imagePath)) && file_exists(public_path('images/guidelines/' . basename($imagePath)))) {
                                                    $imagePath = asset('images/guidelines/' . basename($imagePath));
                                                }
                                                else {
                                                    $imagePath = asset($imagePath);
                                                }
                                            @endphp
                                            <img src="{{ $imagePath }}" alt="{{ $guideline->title }}"
                                                class="object-cover w-full h-full rounded">
                                        @else
                                            <i class="fas fa-book text-gray-400 text-xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-sm mb-1">{{ $guideline->title }}</h3>
                                        <p class="text-xs text-gray-500 line-clamp-2">{{ Str::limit($guideline->description, 80) }}</p>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="space-y-2 text-sm mb-3 pb-3 border-b border-gray-700">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400">Kategori:</span>
                                        <span class="px-2 py-0.5 text-xs rounded-full 
                                            @if ($guideline->category == 'BEGINNER') bg-green-900/50 text-green-400 
                                            @elseif($guideline->category == 'INTERMEDIATE') bg-yellow-900/50 text-yellow-400 
                                            @elseif($guideline->category == 'MASTER') bg-red-900/50 text-red-400 
                                            @else bg-gray-900/50 text-gray-400 @endif">
                                            {{ $guideline->category }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Penulis:</span>
                                        <span class="text-xs">{{ $guideline->author_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Tanggal:</span>
                                        <span class="text-xs">{{ $guideline->published_at->format('d M Y') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-400">Status:</span>
                                        <div class="flex flex-wrap gap-1 justify-end">
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-green-900/50 text-green-400">
                                                {{ $guideline->status }}
                                            </span>
                                            @if ($guideline->is_new)
                                                <span class="px-2 py-0.5 text-xs rounded-full bg-blue-900/50 text-blue-400">New</span>
                                            @endif
                                            @if ($guideline->is_featured)
                                                <span class="px-2 py-0.5 text-xs rounded-full bg-purple-900/50 text-purple-400">Featured</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <a href="{{ route('guideline.show', $guideline->slug) }}" target="_blank"
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                        View
                                    </a>
                                    <a href="{{ route('admin.guidelines.edit', $guideline->id) }}"
                                        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                        <i class="fas fa-edit text-xs"></i>
                                        Edit
                                    </a>
                                    <button type="button" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition delete-btn"
                                        data-id="{{ $guideline->id }}">
                                        <i class="fas fa-trash text-xs"></i>
                                        Delete
                                    </button>
                                    <form id="delete-form-{{ $guideline->id }}"
                                        action="{{ route('admin.guidelines.destroy', $guideline->id) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Belum ada guideline. Silakan tambahkan guideline baru.</p>
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
                        text: "Guideline yang dihapus tidak dapat dikembalikan!",
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
        });

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
                background: '#222',
                color: '#fff'
            });
        @endif
    </script>
@endpush
