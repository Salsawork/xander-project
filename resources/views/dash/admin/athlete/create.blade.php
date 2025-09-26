@extends('app')
@section('title', 'Admin Dashboard - Tambah Athlete')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold my-8 px-8">
                    Tambah Athlete Baru
                </h1>
                <form method="POST" action="{{ route('athlete.store') }}" enctype="multipart/form-data" class="flex flex-col lg:flex-row lg:space-x-8 px-8">
                    @csrf
                    <section aria-labelledby="user-info-title"
                        class="bg-[#262626] rounded-lg p-8 flex-1 max-w-lg space-y-8">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="user-info-title">
                            Informasi Akun
                        </h2>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="name">
                                    Nama Athlete
                                </label>
                                <input name="name" value="{{ old('name') }}"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="name" type="text" placeholder="Masukkan nama athlete" />
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="username">
                                    Email
                                </label>
                                <input name="username" value="{{ old('username') }}"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="username" type="email" placeholder="Masukkan email" />
                                @error('username')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="password">
                                    Password
                                </label>
                                <input name="password"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="password" type="password" placeholder="Masukkan password" />
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>
                    <section class="flex flex-col space-y-8 mt-8 lg:mt-0 w-full max-w-lg">
                        <div aria-labelledby="athlete-info-title" class="bg-[#262626] rounded-lg p-6 space-y-4">
                            <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="athlete-info-title">
                                Informasi Athlete
                            </h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="handicap">
                                        Handicap
                                    </label>
                                    <input name="handicap" value="{{ old('handicap') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="handicap" type="number" step="0.1" placeholder="Masukkan handicap" />
                                    @error('handicap')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="years_experience">
                                        Pengalaman (tahun)
                                    </label>
                                    <input name="years_experience" value="{{ old('years_experience') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="years_experience" type="number" placeholder="Masukkan lama pengalaman" />
                                    @error('years_experience')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="specialty">
                                        Spesialisasi
                                    </label>
                                    <input name="specialty" value="{{ old('specialty') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="specialty" type="text" placeholder="Masukkan spesialisasi" />
                                    @error('specialty')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="location">
                                        Lokasi
                                    </label>
                                    <input name="location" value="{{ old('location') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="location" type="text" placeholder="Masukkan lokasi" />
                                    @error('location')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="bio">
                                        Bio
                                    </label>
                                    <textarea name="bio"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="bio" rows="3" placeholder="Masukkan bio">{{ old('bio') }}</textarea>
                                    @error('bio')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="price_per_session">
                                        Harga per Sesi (Rp)
                                    </label>
                                    <input name="price_per_session" value="{{ old('price_per_session') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="price_per_session" type="number" placeholder="Masukkan harga per sesi" />
                                    @error('price_per_session')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="image">
                                        Foto Athlete
                                    </label>
                                    <input name="image" type="file"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="image" accept="image/*" />
                                    @error('image')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-4 justify-end">
                            <a href="{{ route('athlete.index') }}"
                                class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Simpan
                            </button>
                        </div>
                    </section>
                </form>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tampilkan SweetAlert untuk pesan sukses
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                background: '#262626',
                color: '#fff'
            });
        @endif
        
        // Tampilkan SweetAlert untuk pesan error
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                timer: 3000,
                showConfirmButton: false,
                background: '#262626',
                color: '#fff'
            });
        @endif
    });
</script>
@endpush