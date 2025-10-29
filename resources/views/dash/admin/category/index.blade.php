@extends('app')
@section('title', 'Admin Dashboard - Category Product')

@push('styles')
<style>
    :root {
        color-scheme: dark;
        --page-bg: #0a0a0a;
        --surface: #1b1b1b;
        --surface-light: #2c2c2c;
        --text-color: #e5e5e5;
        --border-color: #333;
        --green: #16a34a;
        --green-hover: #15803d;
        --red: #dc2626;
        --red-hover: #b91c1c;
    }

    html, body {
        height: 100%;
        min-height: 100%;
        background: var(--page-bg);
        color: var(--text-color);
        font-family: 'Inter', sans-serif;
        overscroll-behavior: none;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.55rem 0.9rem;
        border-radius: 0.55rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: background 0.2s ease;
    }

    .btn-primary {
        background: var(--green);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--green-hover);
    }

    .btn-danger {
        background: var(--red);
        color: #fff;
    }

    .btn-danger:hover {
        background: var(--red-hover);
    }

    .table-wrapper {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.25);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background: var(--surface-light);
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.03em;
    }

    th, td {
        padding: 0.9rem 1rem;
        text-align: left;
    }

    tbody tr {
        border-top: 1px solid var(--border-color);
        transition: background 0.2s;
    }

    tbody tr:hover {
        background: #262626;
    }

    input[type="search"] {
        width: 100%;
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 0.55rem;
        padding: 0.55rem 0.8rem;
        color: var(--text-color);
    }

    input[type="search"]:focus {
        border-color: var(--green);
        outline: none;
    }

    /* Modal */
    .modal-bg {
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(3px);
    }

    .modal-box {
        background: var(--surface);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1.5rem;
        width: 92%;
        max-width: 420px;
        animation: fadeIn 0.2s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="flex flex-col min-h-screen">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')

            <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                    Category Product
                </h1>

                @if (session('success'))
                    <div class="mb-4 bg-green-600/80 text-white px-4 py-2 rounded text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-600/80 text-white px-4 py-2 rounded text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <input name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari kategori..."
                            type="search"
                            onchange="window.location.href='{{ route('category.index') }}?search=' + encodeURIComponent(this.value)">
                    </div>

                    <button type="button" onclick="openCreateModal()" class="btn btn-primary whitespace-nowrap">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
                </div>

                <div class="table-wrapper hidden sm:block">
                    <table>
                        <thead>
                            <tr>
                                <th class="w-16">#</th>
                                <th>Nama Kategori</th>
                                <th class="text-center w-40">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr class="hover:bg-[#262626] transition-colors">
                                    <td class="px-4 py-3 text-gray-400">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-3">
                                            <button 
                                                onclick="openEditModal({{ $category->id }}, '{{ $category->name }}')" 
                                                class="btn btn-primary text-xs px-3 py-1.5 flex items-center gap-1 shadow-sm">
                                                <i class="fas fa-edit"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button 
                                                onclick="confirmDelete({{ $category->id }})" 
                                                class="btn btn-danger text-xs px-3 py-1.5 flex items-center gap-1 shadow-sm">
                                                <i class="fas fa-trash"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
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
                        <div class="bg-[#1e1e1e] rounded-lg p-4 border border-gray-700">
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
    <div id="createModal" class="hidden fixed inset-0 modal-bg flex items-center justify-center z-50">
        <div class="modal-box">
            <h2 class="text-lg font-bold mb-4">Tambah Kategori</h2>
            <input name="name" class="w-full border border-gray-600 bg-transparent px-3 py-2 rounded-md mb-4 focus:ring-1 focus:ring-green-500 focus:outline-none"
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
    <div id="editModal" class="hidden fixed inset-0 modal-bg flex items-center justify-center z-50">
        <div class="modal-box">
            <h2 class="text-lg font-bold mb-4">Edit Kategori</h2>
            <input id="editName" name="name" class="w-full border border-gray-600 bg-transparent px-3 py-2 rounded-md mb-4 focus:ring-1 focus:ring-green-500 focus:outline-none" required>
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
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
}
function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
}
function openEditModal(id, name) {
    const form = document.getElementById('editForm');
    form.action = `/dashboard/category/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
function confirmDelete(id) {
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
        if (result.isConfirmed) {
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
