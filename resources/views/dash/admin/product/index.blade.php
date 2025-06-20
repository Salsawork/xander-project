@extends('app')
@section('title', 'Admin Dashboard - Products List')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold mb-6">
                    My Product
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
                        onchange="window.location.href = '{{ route('products.index') }}?search=' + this.value + '&status=' + document.getElementById('statusFilter').value + '&category=' + document.getElementById('categoryFilter').value;"
                        value="{{ request('search') }}"
                        placeholder="Search" type="search" />
                    <div class="flex gap-2 items-center">
                        <select aria-label="Status filter"
                            id="statusFilter"
                            onchange="
                                window.location.href = '{{ route('products.index') }}?search=' + document.querySelector('input[type=search]').value + '&status=' + this.value + '&category=' + document.getElementById('categoryFilter').value;
                            "
                            class="bg-[#2c2c2c] text-gray-500 text-xs rounded border border-gray-700 px-2 py-1 cursor-pointer">
                            <option value="">
                                -- Status --
                            </option>
                            <option value="in-stock" {{ request('status') == 'in-stock' ? 'selected' : '' }}>
                                In Stock
                            </option>
                            <option value="out-of-stock" {{ request('status') == 'out-of-stock' ? 'selected' : '' }}>
                                Out of Stock
                            </option>
                        </select>
                        <select aria-label="Category filter"
                            id="categoryFilter"
                            onchange="
                                window.location.href = '{{ route('products.index') }}?search=' + document.querySelector('input[type=search]').value + '&status=' + document.getElementById('statusFilter').value + '&category=' + this.value;
                            "
                            class="bg-[#2c2c2c] text-gray-500 text-xs rounded border border-gray-700 px-2 py-1 cursor-pointer">
                            <option value="">
                                -- Category --
                            </option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('products.create') }}"
                            class="flex items-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-1 text-sm hover:bg-[#1e90ff] hover:text-white transition">
                            <i class="fas fa-plus">
                            </i>
                            Add Product
                        </a>
                    </div>
                </div>
                <div class="px-8 overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                        <thead class="bg-[#2c2c2c] text-gray-300">
                            <tr>
                                <th class="px-4 py-3">
                                    Product Name
                                </th>
                                <th class="px-4 py-3">
                                    Category
                                </th>
                                <th class="px-4 py-3">
                                    Price
                                </th>
                                <th class="px-4 py-3">
                                    Stock
                                </th>
                                <th class="px-4 py-3">
                                    Status
                                </th>
                                <th class="px-4 py-3">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($products as $product)
                                <tr>
                                    <td class="flex items-center gap-4 px-4 py-3">
                                        <div
                                            class="w-12 h-12 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                            @php
                                                $imagePath = 'https://placehold.co/600x400';
                                                if (!empty($product->images) && is_array($product->images)) {
                                                    foreach ($product->images as $img) {
                                                        if (!empty($img)) {
                                                            // Jika path tidak dimulai dengan http:// atau https:// atau /storage/
                                                            if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/storage/')) {
                                                                $imagePath = asset('storage/uploads/' . $img);
                                                            } else {
                                                                $imagePath = $img;
                                                            }
                                                            break;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <img alt="Placeholder image for {{ $product->name }}"
                                                class="w-10 h-10 object-cover rounded"
                                                src="{{ $imagePath }}"
                                                width="40" height="40"
                                                onerror="this.src='https://placehold.co/600x400'" />
                                        </div>
                                        <div>
                                            <a href="{{ route('products.edit', $product->id) }}"
                                                class="hover:text-blue-400">{{ $product->name }}</a>
                                            <p class="text-xs text-gray-500">{{ $product->brand }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 space-y-1">
                                        <span
                                            class="inline-block border border-gray-600 rounded-full px-2 text-xs text-gray-300">
                                            {{ $product->category->name }}
                                        </span>
                                        <br />
                                        <span
                                            class="inline-block border border-gray-600 rounded-full px-2 text-xs text-gray-300">
                                            {{ $product->condition }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">Rp. {{ number_format($product->pricing, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">{{ $product->quantity }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-block {{ $product->quantity > 0 ? 'bg-green-500' : 'bg-red-500' }} rounded-full px-4 py-1 text-xs font-semibold">
                                            {{ $product->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 flex gap-3 text-gray-400">
                                        <a href="{{ route('products.edit', $product->id) }}" aria-label="Edit {{ $product->name }}" class="hover:text-gray-200">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button aria-label="Delete {{ $product->name }}" class="hover:text-gray-200" onclick="deleteProduct({{ $product->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
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
    function deleteProduct(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            background: '#222',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                // Buat form untuk mengirim request DELETE
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/dashboard/products/${id}`;

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
            title: 'Success!',
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
            background: '#222',
            color: '#fff'
        });
    @endif
</script>
@endpush
