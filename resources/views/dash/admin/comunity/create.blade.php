@extends('app')
@section('title', 'Admin Dashboard - Tambah Berita')

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
                
                <div class="mt-20 sm:mt-0 px-4 sm:px-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold my-6 sm:my-8">
                        Tambah Berita Baru
                    </h1>
                    
                    <form method="POST" action="{{ route('comunity.store') }}" enctype="multipart/form-data"
                        class="flex flex-col lg:flex-row lg:space-x-8">
                        @csrf
                        
                        <section aria-labelledby="general-info-title"
                            class="bg-[#262626] rounded-lg p-4 sm:p-8 flex-1 max-w-full lg:max-w-lg space-y-6 sm:space-y-8 mb-6 lg:mb-0">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">
                                Informasi Berita
                            </h2>
                            <div class="space-y-4 sm:space-y-6">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="news-title">
                                        Judul Berita
                                    </label>
                                    <input name="title"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="news-title" type="text" placeholder="Masukkan judul berita" />
                                    @error('title')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="news-content">
                                        Konten Berita
                                    </label>
                                    <textarea name="content"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-xs text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="news-content" rows="10" placeholder="Masukkan konten berita"></textarea>
                                    @error('content')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <h3 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 mb-4">
                                    Kategori & Status
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="category">
                                            Kategori Berita
                                        </label>
                                        <select name="category"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="category">
                                            <option disabled selected>Pilih kategori</option>
                                            @foreach (['Championship', 'Tips', 'Event', 'Tutorial', 'Other'] as $category)
                                                <option value="{{ $category }}">{{ $category }}</option>
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
                                        <input type="datetime-local" name="published_at"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="published_at" value="{{ now()->format('Y-m-d\TH:i') }}" />
                                        @error('published_at')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="space-y-2">
                                        <label class="block text-xs text-gray-400">Status Berita</label>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="is_featured" id="is_featured" class="rounded text-blue-500 focus:ring-blue-500" />
                                            <label for="is_featured" class="text-xs sm:text-sm">Featured (Tampil di hero utama)</label>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="is_popular" id="is_popular" class="rounded text-blue-500 focus:ring-blue-500" />
                                            <label for="is_popular" class="text-xs sm:text-sm">Popular (Tampil di bagian Popular News)</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="flex flex-col space-y-6 sm:space-y-8 lg:mt-0 lg:max-w-lg">
                            <div aria-labelledby="news-image-title" class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                                <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="news-image-title">
                                    Gambar Berita
                                </h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="image_upload">
                                            Upload Gambar
                                        </label>
                                        <div class="flex flex-col sm:flex-row items-center gap-4">
                                            <input type="file" name="image_url" id="image_upload" accept="image/*"
                                                class="hidden" onchange="previewImage(event)" />
                                            <div class="w-full sm:w-32 h-32 bg-gray-700 rounded-md overflow-hidden flex items-center justify-center">
                                                <img id="image_preview" src="https://placehold.co/600x400?text=No+Image" 
                                                    alt="Preview gambar" class="w-full h-full object-cover" />
                                            </div>
                                            <button type="button" onclick="document.getElementById('image_upload').click()"
                                                class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition">
                                                Pilih Gambar
                                            </button>
                                        </div>
                                        @error('image_url')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                        <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, GIF. Ukuran maksimal: 2MB</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-0 sm:space-x-4 sm:justify-end">
                                <a
                                    href="javascript:history.back()"
                                    class="w-full sm:w-auto px-6 py-2 border border-red-600 text-red-600 text-sm rounded-md hover:bg-red-600 hover:text-white transition order-2 sm:order-1 text-center"
                                    role="button">
                                    Batal
                                </a>
                                <button
                                    class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition order-1 sm:order-2"
                                    type="submit">
                                    Simpan Berita
                                </button>
                            </div>
                        </section>
                    </form>
                </div>
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
