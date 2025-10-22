@extends('app')
@section('title', 'Admin Dashboard - Category Product')

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
    .btn{
        display:inline-flex; align-items:center; gap:.5rem;
        padding:.6rem .9rem; border-radius:.55rem; font-weight:600; font-size:.9rem;
        transition:.15s ease;
    }
    .btn-primary{ background:#16a34a; color:#fff; }
    .btn-primary:hover{ background:#15803d; }
    .btn-danger{ background:#dc2626; color:#fff; }
    .btn-danger:hover{ background:#b91c1c; }
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
                    Category Product
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
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <input name="search" value="{{ request('search') }}"
                            class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                            placeholder="Cari kategori..." type="search"
                            onchange="window.location.href='{{ route('category.index') }}?search=' + encodeURIComponent(this.value)" />
                    </div>

                    <button type="button" onclick="openCreateModal()"
                        class="btn btn-primary whitespace-nowrap">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
                </div>

                <!-- Desktop Table -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                        <thead class="bg-[#2c2c2c] text-gray-300">
                            <tr>
                                <th class="px-4 py-3 w-16">#</th>
                                <th class="px-4 py-3">Nama Kategori</th>
                                <th class="px-4 py-3 text-center w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3">{{ $category->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <button onclick="openEditModal({{ $category->id }}, '{{ $category->name }}')"
                                            class="btn btn-primary text-xs px-3 py-1.5">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button onclick="confirmDelete({{ $category->id }})"
                                            class="btn btn-danger text-xs px-3 py-1.5">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                        Belum ada kategori.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card -->
                <div class="sm:hidden space-y-4">
                    @forelse ($categories as $category)
                        <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-semibold text-base">{{ $category->name }}</h3>
                                <div class="flex gap-2">
                                    <button onclick="openEditModal({{ $category->id }}, '{{ $category->name }}')" class="btn btn-primary text-xs px-2 py-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete({{ $category->id }})" class="btn btn-danger text-xs px-2 py-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                            <p class="text-gray-400">Belum ada kategori.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Create -->
<form id="createForm" method="POST" action="{{ route('category.store') }}">
    @csrf
    <div id="createModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <div class="bg-[#1f1f1f] rounded-lg p-6 w-11/12 sm:w-96 border border-gray-700">
            <h2 class="text-lg font-bold mb-4">Tambah Kategori</h2>
            <input name="name" class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 mb-4 focus:ring-1 focus:ring-green-500 focus:outline-none"
                placeholder="Nama kategori..." required>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-danger" onclick="closeCreateModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</form>

<!-- Modal Edit -->
<form id="editForm" method="POST">
    @csrf
    @method('PUT')
    <div id="editModal" class="hidden fixed inset-0 bg-black/70 flex items-center justify-center z-50">
        <div class="bg-[#1f1f1f] rounded-lg p-6 w-11/12 sm:w-96 border border-gray-700">
            <h2 class="text-lg font-bold mb-4">Edit Kategori</h2>
            <input id="editName" name="name" class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 mb-4 focus:ring-1 focus:ring-green-500 focus:outline-none"
                required>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-danger" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openCreateModal(){ document.getElementById('createModal').classList.remove('hidden'); }
function closeCreateModal(){ document.getElementById('createModal').classList.add('hidden'); }

function openEditModal(id, name){
    const form = document.getElementById('editForm');
    form.action = `/dashboard/category/${id}`; // pastikan route sesuai
    document.getElementById('editName').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal(){ document.getElementById('editModal').classList.add('hidden'); }

function confirmDelete(id){
    Swal.fire({
        title: 'Hapus kategori?',
        text: 'Data kategori akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        background: '#1f1f1f',
        color: '#fff'
    }).then((result) => {
        if(result.isConfirmed){
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/dashboard/category/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
