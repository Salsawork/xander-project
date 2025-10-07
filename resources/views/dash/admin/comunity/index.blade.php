@extends('app')
@section('title', 'Admin Dashboard - News List')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <div class="p-4 sm:p-8 mt-12">
                    <h1 class="text-3xl font-extrabold mb-6">
                        Community
                    </h1>
                    <div class="mt-20 sm:mt-28 px-4 sm:px-8">

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
                                placeholder="Search" type="search" value="{{ request('search') }}" onchange="location.href='{{ route('comunity.index') }}?search=' + this.value + '&category={{ request('category') }}'">
                            <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
                                <select aria-label="Category filter"
                                    onchange="location.href='{{ route('comunity.index') }}?category=' + this.value + '&search={{ request('search') }}'"
                                    class="bg-[#2c2c2c] text-gray-500 text-xs rounded border border-gray-700 px-2 py-2 cursor-pointer w-full sm:w-auto">
                                    <option value="">
                                        -- Kategori --
                                    </option>
                                    @foreach (['Championship', 'Tips', 'Event', 'Tutorial', 'Other'] as $category)
                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category }}</option>
                                    @endforeach
                                </select>
                                <a href="{{ route('comunity.create') }}"
                                    class="flex items-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap justify-center">
                                    <i class="fas fa-plus"></i>
                                    Tambah Berita
                                </a>
                            </div>
                        </div>

                        <!-- Desktop & Tablet Table View -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                                <thead class="bg-[#2c2c2c] text-gray-300">
                                    <tr>
                                        <th class="px-4 py-3">Judul Berita</th>
                                        <th class="px-4 py-3">Kategori</th>
                                        <th class="px-4 py-3">Tanggal Publikasi</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach ($news as $item)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-12 h-12 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                                        @php
                                                            $imagePath = 'https://placehold.co/600x400';
                                                            if (!empty($item->image_url)) {
                                                                if (!str_starts_with($item->image_url, 'http://') && !str_starts_with($item->image_url, 'https://') && !str_starts_with($item->image_url, '/storage/')) {
                                                                    $imagePath = asset('storage/uploads/' . $item->image_url);
                                                                } else {
                                                                    $imagePath = $item->image_url;
                                                                }
                                                            }
                                                        @endphp
                                                        <img alt="Placeholder image for {{ $item->title }}"
                                                            class="w-10 h-10 object-cover rounded"
                                                            src="{{ $imagePath }}"
                                                            width="40" height="40"
                                                            onerror="this.src='https://placehold.co/600x400'" />
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('comunity.edit', $item->id) }}"
                                                            class="hover:text-blue-400">{{ $item->title }}</a>
                                                        <p class="text-xs text-gray-500">{{ Str::limit(strip_tags($item->content), 50) }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-block border border-gray-600 rounded-full px-2 text-xs text-gray-300">
                                                    {{ $item->category ?? 'Uncategorized' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">{{ $item->published_at->format('d M Y') }}</td>
                                            <td class="px-4 py-3">
                                                <div class="flex gap-2">
                                                    @if($item->is_featured)
                                                        <span class="inline-block bg-blue-500 rounded-full px-3 py-1 text-xs font-semibold">
                                                            Featured
                                                        </span>
                                                    @endif
                                                    @if($item->is_popular)
                                                        <span class="inline-block bg-purple-500 rounded-full px-3 py-1 text-xs font-semibold">
                                                            Popular
                                                        </span>
                                                    @endif
                                                    @if(!$item->is_featured && !$item->is_popular)
                                                        <span class="inline-block bg-gray-500 rounded-full px-3 py-1 text-xs font-semibold">
                                                            Normal
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex gap-3 text-gray-400">
                                                    <a href="{{ route('comunity.edit', $item->id) }}" aria-label="Edit {{ $item->title }}" class="hover:text-gray-200">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    <button aria-label="Delete {{ $item->title }}" class="hover:text-gray-200" onclick="deleteNews({{ $item->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="sm:hidden space-y-4">
                            @foreach ($news as $item)
                                <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                    <!-- Header with Image and Title -->
                                    <div class="flex gap-3 mb-3">
                                        <div class="w-16 h-16 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                            @php
                                                $imagePath = 'https://placehold.co/600x400';
                                                if (!empty($item->image_url)) {
                                                    if (!str_starts_with($item->image_url, 'http://') && !str_starts_with($item->image_url, 'https://') && !str_starts_with($item->image_url, '/storage/')) {
                                                        $imagePath = asset('storage/uploads/' . $item->image_url);
                                                    } else {
                                                        $imagePath = $item->image_url;
                                                    }
                                                }
                                            @endphp
                                            <img alt="Placeholder image for {{ $item->title }}"
                                                class="w-full h-full object-cover rounded"
                                                src="{{ $imagePath }}"
                                                onerror="this.src='https://placehold.co/600x400'" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-sm mb-1">{{ $item->title }}</h3>
                                            <p class="text-xs text-gray-500 line-clamp-2">{{ Str::limit(strip_tags($item->content), 80) }}</p>
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="space-y-2 text-sm mb-3 pb-3 border-b border-gray-700">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-400">Kategori:</span>
                                            <span class="inline-block border border-gray-600 rounded-full px-2 py-0.5 text-xs text-gray-300">
                                                {{ $item->category ?? 'Uncategorized' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-400">Publikasi:</span>
                                            <span class="text-xs">{{ $item->published_at->format('d M Y') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-400">Status:</span>
                                            <div class="flex gap-1 flex-wrap justify-end">
                                                @if($item->is_featured)
                                                    <span class="inline-block bg-blue-500 rounded-full px-2 py-0.5 text-xs font-semibold">
                                                        Featured
                                                    </span>
                                                @endif
                                                @if($item->is_popular)
                                                    <span class="inline-block bg-purple-500 rounded-full px-2 py-0.5 text-xs font-semibold">
                                                        Popular
                                                    </span>
                                                @endif
                                                @if(!$item->is_featured && !$item->is_popular)
                                                    <span class="inline-block bg-gray-500 rounded-full px-2 py-0.5 text-xs font-semibold">
                                                        Normal
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-2">
                                        <a href="{{ route('comunity.edit', $item->id) }}" 
                                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                            <i class="fas fa-pen text-xs"></i>
                                            Edit
                                        </a>
                                        <button onclick="deleteNews({{ $item->id }})"
                                            class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                            <i class="fas fa-trash text-xs"></i>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteNews(id) {
        Swal.fire({
            title: 'Kamu yakin?',
            text: "Berita yang dihapus tidak dapat dikembalikan!",
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
                form.action = `/dashboard/comunity/${id}`;

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