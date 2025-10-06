@extends('app')
@section('title', 'Admin Dashboard - Edit Product')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-6 sm:py-8">
            @include('partials.topbar')

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-8 my-6 sm:my-8 gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold">
                    Edit Product: {{ $product->name }}
                </h1>
                <a href="{{ route('products.index') }}"
                   class="flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm sm:text-base">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali ke daftar produk</span>
                </a>
            </div>

            <!-- Form -->
            <form id="editProductForm"
                  action="{{ route('products.update', $product->id) }}"
                  method="POST"
                  class="px-4 sm:px-8"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                    <!-- Kolom Kiri -->
                    <div class="space-y-6">
                        <!-- Informasi Dasar -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                                Informasi Dasar
                            </h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Nama Produk</label>
                                    <input name="name" type="text" value="{{ $product->name }}"
                                           class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Deskripsi Produk</label>
                                    <textarea name="description" rows="5"
                                              class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-blue-500">{{ $product->description }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Kategori & Brand -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                <i class="fas fa-tag mr-2 text-green-400"></i>
                                Kategori & Brand
                            </h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Kategori Produk</label>
                                    <select name="category_id"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        @foreach($categories ?? [] as $category)
                                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Brand</label>
                                    <select name="brand"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        @foreach(['Mezz', 'Predator', 'Cuetec', 'Other'] as $brand)
                                            <option value="{{ $brand }}" {{ $product->brand == $brand ? 'selected' : '' }}>
                                                {{ $brand }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Kondisi</label>
                                    <div class="flex flex-wrap gap-4">
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="radio" name="condition" value="new"
                                                   {{ $product->condition == 'new' ? 'checked' : '' }}
                                                   class="text-blue-500 focus:ring-blue-500 h-4 w-4" />
                                            <span>Baru</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="radio" name="condition" value="used"
                                                   {{ $product->condition == 'used' ? 'checked' : '' }}
                                                   class="text-blue-500 focus:ring-blue-500 h-4 w-4" />
                                            <span>Bekas</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="space-y-6">
                        <!-- Inventori -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                <i class="fas fa-box mr-2 text-yellow-400"></i>
                                Inventori
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Jumlah Stok</label>
                                    <input name="quantity" type="number" value="{{ $product->quantity }}"
                                           class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">SKU (Opsional)</label>
                                    <input name="sku" type="text" value="{{ $product->sku }}"
                                           class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Gambar Produk -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                <i class="fas fa-image mr-2 text-indigo-400"></i>
                                Gambar Produk
                            </h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Upload Gambar (Opsional)</label>
                                    <input type="file" name="images[]" multiple
                                           class="w-full border border-gray-600 bg-[#262626] px-3 py-2 text-sm rounded-md" />
                                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF. Maks: 2MB</p>
                                </div>
                                @if(isset($product->images) && count($product->images) > 0)
                                <div>
                                    <label class="block text-xs text-gray-400 mb-2">Gambar Saat Ini</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($product->images as $image)
                                        <div class="relative w-20 h-20">
                                            <img src="{{ asset('storage/uploads/' . $image) }}"
                                                 class="w-full h-full object-cover rounded-md" />
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Pengiriman & Dimensi -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                <i class="fas fa-truck mr-2 text-purple-400"></i>
                                Pengiriman & Dimensi
                            </h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Berat (gram)</label>
                                    <input name="weight" type="number" value="{{ $product->weight }}"
                                           class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">Panjang (cm)</label>
                                        <input name="length" type="number" value="{{ $product->length }}"
                                               class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">Lebar (cm)</label>
                                        <input name="breadth" type="number" value="{{ $product->breadth }}"
                                               class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">Tinggi (cm)</label>
                                        <input name="width" type="number" value="{{ $product->width }}"
                                               class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Harga -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                <i class="fas fa-tag mr-2 text-red-400"></i>
                                Harga
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Harga (IDR)</label>
                                    <input name="pricing" type="number" value="{{ $product->pricing }}"
                                           class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Diskon (%)</label>
                                    <input name="discount" type="number" value="{{ $product->discount }}"
                                           class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex flex-col sm:flex-row justify-end mt-8 gap-3 sm:gap-4">
                    <a href="{{ route('products.index') }}"
                       class="px-5 py-2 border border-red-600 text-red-600 rounded-md text-center hover:bg-red-600 hover:text-white transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-md hover:from-blue-600 hover:to-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>
@endsection
