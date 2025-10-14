@extends('app')
@section('title', 'Admin Dashboard - Tambah Athlete')

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
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
                @include('partials.topbar')
                
                <div class="mt-20 sm:mt-0 px-4 sm:px-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold my-6 sm:my-8">
                        Tambah Athlete Baru
                    </h1>
                    
                    <form method="POST" action="{{ route('athlete.store') }}" enctype="multipart/form-data" class="flex flex-col lg:flex-row lg:space-x-8">
                        @csrf
                        
                        <section aria-labelledby="user-info-title"
                            class="bg-[#262626] rounded-lg p-4 sm:p-8 flex-1 max-w-full lg:max-w-lg space-y-6 sm:space-y-8 mb-6 lg:mb-0">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="user-info-title">
                                Informasi Akun
                            </h2>
                            <div class="space-y-4 sm:space-y-6">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="name">
                                        Nama Athlete
                                    </label>
                                    <input name="name" value="{{ old('name') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="name" type="text" placeholder="Masukkan nama athlete" />
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="email">
                                        Email
                                    </label>
                                    <input name="email" value="{{ old('email') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="email" type="email" placeholder="Masukkan email" />
                                    @error('email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="password">
                                        Password
                                    </label>
                                    <input name="password"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="password" type="password" placeholder="Masukkan password" />
                                    @error('password')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>
                        
                        <section class="flex flex-col space-y-6 sm:space-y-8 w-full max-w-full lg:max-w-lg">
                            <div aria-labelledby="athlete-info-title" class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                                <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="athlete-info-title">
                                    Informasi Athlete
                                </h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="handicap">
                                            Handicap
                                        </label>
                                        <input name="handicap" value="{{ old('handicap') }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="bio" rows="3" placeholder="Masukkan bio">{{ old('bio') }}</textarea>
                                        @error('bio')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Harga per Sesi (format ribuan) --}}
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="price_display">
                                            Harga per Sesi (Rp)
                                        </label>
                                        {{-- Input tampilan (diformat) --}}
                                        <input
                                            id="price_display"
                                            type="text"
                                            inputmode="numeric"
                                            placeholder="Rp 0"
                                            value="{{ old('price_per_session') ? number_format((int)preg_replace('/\D/','', old('price_per_session')), 0, ',', '.') : '' }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                        {{-- Nilai asli yang dikirim ke server --}}
                                        <input
                                            id="price_per_session"
                                            name="price_per_session"
                                            type="hidden"
                                            value="{{ old('price_per_session') }}">
                                        @error('price_per_session')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="image">
                                            Foto Athlete
                                        </label>
                                        <input name="image" type="file"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="image" accept="image/*" />
                                        @error('image')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-0 sm:space-x-4 sm:justify-end">
                                <a href="{{ route('athlete.index') }}"
                                    class="w-full sm:w-auto px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-center text-sm order-2 sm:order-1">
                                    Batal
                                </a>
                                <button type="submit"
                                    class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm order-1 sm:order-2">
                                    Simpan
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ===== Format Harga per Sesi (Rp) =====
    (function () {
        const disp = document.getElementById('price_display');
        const raw  = document.getElementById('price_per_session');
        const nfID = new Intl.NumberFormat('id-ID');

        function onlyDigits(str){ return (str||'').replace(/\D+/g,''); }

        function syncFromDisplay() {
            const digits = onlyDigits(disp.value);
            raw.value = digits; // simpan angka murni ke input hidden
            disp.value = digits ? nfID.format(Number(digits)) : '';
        }

        // Inisialisasi (format old value)
        if (raw.value) {
            disp.value = nfID.format(Number(onlyDigits(raw.value)));
        }

        // Format saat pengguna mengetik/paste
        disp.addEventListener('input', syncFromDisplay);
        disp.addEventListener('blur', syncFromDisplay);

        // Pastikan sebelum submit nilai tersembunyi sudah terisi angka murni
        const form = disp.closest('form');
        if (form) {
            form.addEventListener('submit', function(){
                const digits = onlyDigits(disp.value);
                raw.value = digits;
            });
        }
    })();

    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                background: '#222',
                color: '#fff'
            });
        @endif
        
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                timer: 3000,
                showConfirmButton: false,
                background: '#222',
                color: '#fff'
            });
        @endif
    });
</script>
@endpush
