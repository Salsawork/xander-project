@extends('app')
@section('title', 'Admin Dashboard - Tambah Venue')

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
                        Tambah Venue Baru
                    </h1>
                    
                    <form method="POST" action="{{ route('venue.store') }}"  enctype="multipart/form-data" class="flex flex-col lg:flex-row lg:space-x-8">
                        @csrf
                        
                        <section aria-labelledby="user-info-title"
                            class="bg-[#262626] rounded-lg p-4 sm:p-8 flex-1 max-w-full lg:max-w-lg space-y-6 sm:space-y-8 mb-6 lg:mb-0">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="user-info-title">
                                Informasi Akun
                            </h2>
                            <div class="space-y-4 sm:space-y-6">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="name">
                                        Nama Pengelola
                                    </label>
                                    <input name="name" value="{{ old('name') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="name" type="text" placeholder="Masukkan nama pengelola venue" />
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
                            <div aria-labelledby="venue-info-title" class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                                <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="venue-info-title">
                                    Informasi Venue
                                </h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="venue_name">
                                            Nama Venue
                                        </label>
                                        <input name="venue_name" value="{{ old('venue_name') }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="venue_name" type="text" placeholder="Masukkan nama venue" />
                                        @error('venue_name')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    {{-- Image --}}
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="image">
                                            Gambar Venue
                                        </label>
                                        <input name="image" value="{{ old('image') }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="image" type="file" placeholder="Masukkan gambar venue" />
                                        @error('image')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="address">
                                            Alamat
                                        </label>
                                        <textarea name="address"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="address" rows="3" placeholder="Masukkan alamat venue">{{ old('address') }}</textarea>
                                        @error('address')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="phone">
                                            Nomor Telepon
                                        </label>
                                        <input name="phone" value="{{ old('phone') }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="phone" type="text" placeholder="Masukkan nomor telepon" />
                                        @error('phone')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="operating_hours">
                                            Jam Operasional
                                        </label>
                                        <input name="operating_hours" value="{{ old('operating_hours') }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="operating_hours" type="text" placeholder="Contoh: 09:00 - 22:00" />
                                        @error('operating_hours')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="description">
                                            Deskripsi
                                        </label>
                                        <textarea name="description"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="description" rows="4" placeholder="Masukkan deskripsi venue">{{ old('description') }}</textarea>
                                        @error('description')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-0 sm:space-x-4 sm:justify-end">
                                <a href="{{ route('venue.index') }}"
                                    class="w-full sm:w-auto px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-center text-sm order-2 sm:order-1">
                                    Batal
                                </a>
                                <button
                                    class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm order-1 sm:order-2"
                                    type="submit">
                                    Simpan Venue
                                </button>
                            </div>
                        </section>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection
