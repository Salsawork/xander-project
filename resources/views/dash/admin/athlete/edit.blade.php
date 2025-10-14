@extends('app')
@section('title', 'Admin Dashboard - Edit Athlete')

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
                
                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                        <h1 class="text-2xl sm:text-3xl font-extrabold">
                            Edit Athlete: {{ $athlete->user->name }}
                        </h1>
                        <a href="{{ route('athlete.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali ke daftar athlete</span>
                        </a>
                    </div>
                    
                    <form id="editAthleteForm" action="{{ route('athlete.update', $athlete->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                            <!-- Kolom Kiri -->
                            <div class="space-y-6">
                                <!-- Informasi Akun -->
                                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                                    <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                        <i class="fas fa-user-circle mr-2 text-blue-400"></i>
                                        Informasi Akun
                                    </h2>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="name">
                                                Nama Athlete
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="name" id="name" type="text" value="{{ $athlete->user->name }}" />
                                            @error('name')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="email">
                                                Email
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="email" id="email" type="email" value="{{ $athlete->user->email }}" />
                                            @error('email')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="password">
                                                Password <span class="text-gray-500">(Kosongkan jika tidak ingin mengubah)</span>
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="password" id="password" type="password" placeholder="Masukkan password baru" />
                                            @error('password')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Kolom Kanan -->
                            <div class="space-y-6">
                                <!-- Informasi Athlete -->
                                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                                    <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                        <i class="fas fa-user-tie mr-2 text-green-400"></i>
                                        Informasi Athlete
                                    </h2>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="handicap">
                                                Handicap
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="handicap" id="handicap" type="text" value="{{ $athlete->handicap }}" />
                                            @error('handicap')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="experience_years">
                                                Pengalaman (tahun)
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="experience_years" id="experience_years" type="number" value="{{ $athlete->experience_years }}" />
                                            @error('experience_years')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="specialty">
                                                Spesialisasi
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="specialty" id="specialty" type="text" value="{{ $athlete->specialty }}" />
                                            @error('specialty')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="location">
                                                Lokasi
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="location" id="location" type="text" value="{{ $athlete->location }}" />
                                            @error('location')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="bio">
                                                Bio
                                            </label>
                                            <textarea
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                                                name="bio" id="bio" rows="3">{{ $athlete->bio }}</textarea>
                                            @error('bio')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        {{-- Harga per Sesi dengan number format --}}
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="price_display">
                                                Harga per Sesi (Rp)
                                            </label>
                                            {{-- Input tampilan (terformat ribuan) --}}
                                            <input
                                                id="price_display"
                                                type="text"
                                                inputmode="numeric"
                                                placeholder="Rp 0"
                                                value="{{ $athlete->price_per_session ? number_format($athlete->price_per_session, 0, ',', '.') : '' }}"
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                            {{-- Nilai murni yang dikirim ke server --}}
                                            <input
                                                id="price_per_session"
                                                name="price_per_session"
                                                type="hidden"
                                                value="{{ $athlete->price_per_session }}">
                                            @error('price_per_session')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="image">
                                                Foto Athlete
                                            </label>
                                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                                @if($athlete->image && Storage::disk('public')->exists($athlete->image))
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ asset('storage/' . $athlete->image) }}" alt="{{ $athlete->user->name }}" 
                                                            class="h-16 w-16 rounded-full object-cover border border-gray-600">
                                                    </div>
                                                @endif
                                                <input
                                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                    name="image" id="image" type="file" accept="image/*" />
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah foto</p>
                                            @error('image')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tombol Aksi -->
                        <div class="flex flex-col sm:flex-row justify-end mt-6 sm:mt-8 gap-3 sm:gap-0 sm:space-x-4">
                            <a href="{{ route('athlete.index') }}"
                                class="w-full sm:w-auto px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-center text-sm order-2 sm:order-1">
                                Batal
                            </a>
                            <button type="submit"
                                class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm order-1 sm:order-2">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // ===== Format Harga per Sesi (Rp) pada halaman Edit =====
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

        // Inisialisasi (format nilai awal)
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
