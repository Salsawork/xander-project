@extends('app')
@section('title', 'Admin Dashboard - Edit Guideline')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <div class="flex items-center mb-6">
                    <a href="{{ route('admin.guidelines.index') }}" class="text-gray-400 hover:text-white mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-3xl font-extrabold mt-20">
                        Edit Guideline
                    </h1>
                </div>
                
                @if ($errors->any())
                    <div class="mx-8 mb-4 bg-red-500 text-white px-4 py-2 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="px-8">
                    <form action="{{ route('admin.guidelines.update', $guideline->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kolom Kiri -->
                            <div class="space-y-6">
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Judul Guideline <span class="text-red-500">*</span></label>
                                    <input type="text" name="title" id="title" value="{{ old('title', $guideline->title) }}" required
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">
                                </div>
                                
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Deskripsi Singkat <span class="text-red-500">*</span></label>
                                    <textarea name="description" id="description" rows="3" required
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">{{ old('description', $guideline->description) }}</textarea>
                                </div>
                                
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                                    <select name="category" id="category" required
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">
                                        <option value="">Pilih Kategori</option>
                                        <option value="BEGINNER" {{ old('category', $guideline->category) == 'BEGINNER' ? 'selected' : '' }}>Beginner</option>
                                        <option value="INTERMEDIATE" {{ old('category', $guideline->category) == 'INTERMEDIATE' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="MASTER" {{ old('category', $guideline->category) == 'MASTER' ? 'selected' : '' }}>Master</option>
                                        <option value="GENERAL" {{ old('category', $guideline->category) == 'GENERAL' ? 'selected' : '' }}>General</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="skill_level" class="block text-sm font-medium text-gray-300 mb-1">Level Skill <span class="text-red-500">*</span></label>
                                    <select name="skill_level" id="skill_level" required
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">
                                        <option value="">Pilih Level</option>
                                        <option value="beginner" {{ old('skill_level', $guideline->skill_level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="intermediate" {{ old('skill_level', $guideline->skill_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="master" {{ old('skill_level', $guideline->skill_level) == 'master' ? 'selected' : '' }}>Master</option>
                                        <option value="general" {{ old('skill_level', $guideline->skill_level) == 'general' ? 'selected' : '' }}>General</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="tags" class="block text-sm font-medium text-gray-300 mb-1">Tags (pisahkan dengan koma)</label>
                                    <input type="text" name="tags" id="tags" value="{{ old('tags', $guideline->tags) }}"
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                        placeholder="billiards, tutorial, cue">
                                </div>
                                
                                <div>
                                    <label for="author_name" class="block text-sm font-medium text-gray-300 mb-1">Nama Penulis <span class="text-red-500">*</span></label>
                                    <input type="text" name="author_name" id="author_name" value="{{ old('author_name', $guideline->author_name) }}" required
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">
                                </div>
                                
                                <div>
                                    <label for="reading_time_minutes" class="block text-sm font-medium text-gray-300 mb-1">Waktu Baca (menit)</label>
                                    <input type="number" name="reading_time_minutes" id="reading_time_minutes" value="{{ old('reading_time_minutes', $guideline->reading_time_minutes) }}" min="1"
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">
                                </div>
                            </div>
                            
                            <!-- Kolom Kanan -->
                            <div class="space-y-6">
                                <div>
                                    <label for="featured_image" class="block text-sm font-medium text-gray-300 mb-1">Gambar Utama</label>
                                    <div class="flex items-center gap-2">
                                        <input type="text" name="featured_image" id="featured_image" value="{{ old('featured_image', $guideline->featured_image) }}"
                                            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                            placeholder="guideline-1.png">
                                        <button type="button" id="upload-btn"
                                            class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-2 rounded">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                    </div>
                                    <div id="image-preview" class="mt-2 {{ $guideline->featured_image ? '' : 'hidden' }}">
                                        @php
                                            $imagePath = $guideline->featured_image;
                                            // Cek apakah gambar ada di storage
                                            if (!empty($imagePath) && !file_exists(public_path($imagePath)) && file_exists(public_path('images/guidelines/' . basename($imagePath)))) {
                                                $imagePath = 'images/guidelines/' . basename($imagePath);
                                            }
                                        @endphp
                                        <img src="{{ !empty($imagePath) ? asset($imagePath) : '' }}" alt="Preview" class="h-32 rounded border border-gray-600">
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="youtube_url" class="block text-sm font-medium text-gray-300 mb-1">URL YouTube (opsional)</label>
                                    <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url', $guideline->youtube_url) }}"
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                        placeholder="https://youtube.com/watch?v=...">
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_new" id="is_new" value="1" {{ old('is_new', $guideline->is_new) ? 'checked' : '' }}
                                            class="rounded border-gray-600 bg-transparent text-blue-500 focus:ring-blue-500 focus:ring-offset-gray-900">
                                        <label for="is_new" class="ml-2 text-sm text-gray-300">Tandai sebagai Baru</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $guideline->is_featured) ? 'checked' : '' }}
                                            class="rounded border-gray-600 bg-transparent text-purple-500 focus:ring-purple-500 focus:ring-offset-gray-900">
                                        <label for="is_featured" class="ml-2 text-sm text-gray-300">Tampilkan di Featured</label>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="content" class="block text-sm font-medium text-gray-300 mb-1">Konten <span class="text-red-500">*</span></label>
                                    <textarea name="content" id="content" rows="12" required
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">{{ old('content', $guideline->content) }}</textarea>
                                    <p class="text-xs text-gray-400 mt-1">Kamu bisa menggunakan HTML untuk formatting.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('admin.guidelines.index') }}"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded">
                                Batal
                            </a>
                            <button type="submit" class="px-4 py-2 bg-[#1e90ff] hover:bg-blue-600 text-white rounded">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadBtn = document.getElementById('upload-btn');
        const featuredImage = document.getElementById('featured_image');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = imagePreview.querySelector('img');
        
        // Update preview when image path changes
        featuredImage.addEventListener('input', function() {
            if (this.value) {
                let imagePath = this.value;
                // Check if it's a relative path or full URL
                if (!imagePath.startsWith('http')) {
                    imagePath = '/images/guidelines/' + imagePath;
                }
                previewImg.src = imagePath;
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }
        });
        
        // Handle image upload
        uploadBtn.addEventListener('click', function() {
            // Here you would typically open a file picker or media library
            // For simplicity, we'll just show an alert
            alert('Fitur upload akan segera tersedia. Untuk saat ini, masukkan nama file gambar yang sudah ada di folder public/images/guidelines/');
        });
    });
</script>
@endpush