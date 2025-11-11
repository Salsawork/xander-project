@extends('app')
@section('title', 'Admin Dashboard - Edit Berita')

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
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8 scroll-safe">
                @include('partials.topbar')
                <div class="flex items-center justify-between px-8 my-8">
                    <h1 class="text-3xl font-extrabold">
                        Edit Berita: {{ $news->title }}
                    </h1>
                    <a href="{{ route('comunity.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali ke daftar berita</span>
                    </a>
                </div>
                
                <form id="editNewsForm" action="{{ route('comunity.update', $news->id) }}" method="POST" class="px-8" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Kolom Kiri -->
                        <div class="space-y-6">
                            <!-- Informasi Dasar -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-info-circle mr-2 text-blue-400"></i>
                                    Informasi Berita
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="news-title">
                                            Judul Berita
                                        </label>
                                        <input
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="title" id="news-title" type="text" value="{{ $news->title }}" />
                                        @error('title')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="news-content">
                                            Konten Berita
                                        </label>
                                        <textarea
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="content" id="news-content" rows="10">{{ $news->content }}</textarea>
                                        @error('content')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Kategori & Status -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-tag mr-2 text-green-400"></i>
                                    Kategori & Status
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="category">
                                            Kategori Berita
                                        </label>
                                        <select
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="category" id="category">
                                            @foreach(['Championship', 'Tips', 'Event', 'Tutorial', 'Other'] as $category)
                                                <option value="{{ $category }}" {{ $news->category == $category ? 'selected' : '' }}>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="published_at">
                                            Tanggal Publikasi
                                        </label>
                                        <input type="datetime-local"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            name="published_at" id="published_at" value="{{ $news->published_at->format('Y-m-d\TH:i') }}" />
                                        @error('published_at')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <label class="block text-xs text-gray-400">Status Berita</label>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="is_featured" id="is_featured" 
                                                {{ $news->is_featured ? 'checked' : '' }}
                                                class="rounded text-blue-500 focus:ring-blue-500 h-4 w-4" />
                                            <label for="is_featured">Featured (Tampil di hero utama)</label>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="is_popular" id="is_popular" 
                                                {{ $news->is_popular ? 'checked' : '' }}
                                                class="rounded text-blue-500 focus:ring-blue-500 h-4 w-4" />
                                            <label for="is_popular">Popular (Tampil di bagian Popular News)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Kolom Kanan -->
                        <div class="space-y-6">
                            <!-- Gambar Berita -->
                            <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                                <h2 class="text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                    <i class="fas fa-image mr-2 text-indigo-400"></i>
                                    Gambar Berita
                                </h2>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="image_upload">
                                            Upload Gambar Baru (Opsional)
                                        </label>
                                        <div class="flex items-center gap-4">
                                            <input type="file" name="image_url" id="image_upload" accept="image/*"
                                                class="hidden" onchange="previewImage(event)" />
                                            <div class="w-32 h-32 bg-gray-700 rounded-md overflow-hidden flex items-center justify-center">
                                                @php
                                                    // Normalisasi: jika URL penuh -> pakai apa adanya,
                                                    // jika bukan -> gunakan /images/community/{filename}
                                                    $imagePath = 'https://placehold.co/600x400?text=No+Image';
                                                    if (!empty($news->image_url)) {
                                                        $raw = trim($news->image_url);
                                                        if (preg_match('/^https?:\/\//i', $raw)) {
                                                            $imagePath = $raw;
                                                        } else {
                                                            $filename  = basename($raw);
                                                            $imagePath = asset('demo-xanders/images/community/' . $filename);
                                                        }
                                                    }
                                                @endphp
                                                <img id="image_preview" src="{{ $imagePath }}" 
                                                    alt="Preview gambar" class="w-full h-full object-cover" 
                                                    onerror="this.src='https://placehold.co/600x400?text=No+Image'" />
                                            </div>
                                            <button type="button" onclick="document.getElementById('image_upload').click()"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                                Pilih Gambar
                                            </button>
                                        </div>
                                        @error('image_url')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                        <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, GIF. Ukuran maksimal: 4MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol Aksi -->
                    <div class="flex justify-end mt-8 space-x-4">
                        <a href="javascript:history.back()"
                            class="px-6 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-700 transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('image_preview');
            preview.src = reader.result;
        }
        if (event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>
@endpush
