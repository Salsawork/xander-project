@extends('app')
@section('title', 'Admin Dashboard - Edit Product')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8">
                @include('partials.topbar')
                <div class="flex items-center justify-between px-8 my-8">
                    <h1 class="text-3xl font-extrabold">
                        Edit Product: {{ $product->name }}
                    </h1>
                    <a href="{{ route('products.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke daftar produk</span>
                    </a>
                </div>
                
                <form id="editProductForm" action="{{ route('products.update', $product->id) }}" method="POST" class="px-8" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <!-- Informasi Dasar -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                                    Informasi Dasar
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="product-name">
                                            Nama Produk
                                        </label>
                                        <input
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="name" id="product-name" type="text" value="{{ $product->name }}" />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="product-description">
                                            Deskripsi Produk
                                        </label>
                                        <textarea
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="description" id="product-description" rows="5">{{ $product->description }}</textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Kategori & Brand -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-tag mr-2 text-green-400"></i>
                                    Kategori & Brand
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="category">
                                            Kategori Produk
                                        </label>
                                        <select
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="category_id" id="category">
                                            @foreach($categories ?? [] as $category)
                                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="brand">
                                            Brand
                                        </label>
                                        <select
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="brand" id="brand">
                                            @foreach(['Mezz', 'Predator', 'Cuetec', 'Other'] as $brand)
                                                <option value="{{ $brand }}" {{ $product->brand == $brand ? 'selected' : '' }}>
                                                    {{ $brand }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1">Kondisi</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="condition" value="new" {{ $product->condition == 'new' ? 'checked' : '' }} 
                                                    class="text-blue-500 focus:ring-blue-500 h-4 w-4" />
                                                <span>Baru</span>
                                            </label>
                                            <label class="flex items-center gap-2">
                                                <input type="radio" name="condition" value="used" {{ $product->condition == 'used' ? 'checked' : '' }}
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
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-box mr-2 text-yellow-400"></i>
                                    Inventori
                                </h2>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="quantity">
                                            Jumlah Stok
                                        </label>
                                        <input
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="quantity" id="quantity" type="number" value="{{ $product->quantity }}" />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="sku">
                                            SKU (Opsional)
                                        </label>
                                        <input
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="sku" id="sku" type="text" value="{{ $product->sku }}" />
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Gambar Produk -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-image mr-2 text-indigo-400"></i>
                                    Gambar Produk
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="images">
                                            Upload Gambar (Opsional)
                                        </label>
                                        <input type="file" name="images[]" id="images" multiple
                                            class="w-full border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white rounded-md" />
                                        <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF. Maks: 2MB</p>
                                    </div>
                                    
                                    @if(isset($product->images) && count($product->images) > 0)
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-2">Gambar Saat Ini</label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($product->images as $image)
                                            <div class="relative w-20 h-20">
                                                <img src="{{ $image }}" class="w-full h-full object-cover rounded-md" alt="Product image" />
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Pengiriman & Dimensi -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-truck mr-2 text-purple-400"></i>
                                    Pengiriman & Dimensi
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="weight">
                                            Berat (gram)
                                        </label>
                                        <input
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="weight" id="weight" type="number" value="{{ $product->weight }}" />
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="length">
                                                Panjang (cm)
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="length" id="length" type="number" value="{{ $product->length }}" />
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="breadth">
                                                Lebar (cm)
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="breadth" id="breadth" type="number" value="{{ $product->breadth }}" />
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="width">
                                                Tinggi (cm)
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="width" id="width" type="number" value="{{ $product->width }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Harga -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-tag mr-2 text-red-400"></i>
                                    Harga
                                </h2>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="pricing">
                                            Harga (IDR)
                                        </label>
                                        <input
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="pricing" id="pricing" type="number" value="{{ $product->pricing }}" />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="discount">
                                            Diskon (%)
                                        </label>
                                        <input
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="discount" id="discount" type="number" value="{{ $product->discount }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol Aksi -->
                    <div class="flex justify-end mt-8 space-x-4">
                        <a href="{{ route('products.index') }}"
                            class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                            Batal
                        </a>
                        <button class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-md hover:from-blue-600 hover:to-blue-700 transition"
                            type="submit">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
@endsection
