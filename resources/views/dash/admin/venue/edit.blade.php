@extends('app')
@section('title', 'Admin Dashboard - Edit Venue')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                
                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                        <h1 class="text-2xl sm:text-3xl font-extrabold">
                            Edit Venue: {{ $venue->name }}
                        </h1>
                        <a href="{{ route('venue.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm">
                            <i class="fas fa-arrow-left"></i>
                            <span>Kembali ke daftar venue</span>
                        </a>
                    </div>
                    
                    <form id="editVenueForm" action="{{ route('venue.update', $venue->id) }}" method="POST" enctype="multipart/form-data" >
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
                                                Nama Pengelola
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="name" id="name" type="text" value="{{ $venue->user->name }}" />
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
                                                name="email" id="email" type="email" value="{{ $venue->user->email }}" />
                                            @error('email')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="password">
                                                Password (Kosongkan jika tidak ingin mengubah)
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
                                <!-- Informasi Venue -->
                                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                                    <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                                        <i class="fas fa-store mr-2 text-green-400"></i>
                                        Informasi Venue
                                    </h2>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="venue_name">
                                                Nama Venue
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="venue_name" id="venue_name" type="text" value="{{ $venue->name }}" />
                                            @error('venue_name')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="image">
                                                Gambar Venue
                                            </label>
                                            <input name="image" value="{{ old('image') }}"
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                id="image" type="file" placeholder="Masukkan gambar venue" value="{{ $venue->image }}" />
                                            @error('image')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="address">
                                                Alamat
                                            </label>
                                            <textarea
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="address" id="address" rows="3">{{ $venue->address }}</textarea>
                                            @error('address')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="phone">
                                                Nomor Telepon
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="phone" id="phone" type="text" value="{{ $venue->phone }}" />
                                            @error('phone')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="operating_hours">
                                                Jam Operasional
                                            </label>
                                            <input
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="operating_hours" id="operating_hours" type="text" value="{{ $venue->operating_hours }}" />
                                            @error('operating_hours')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1" for="description">
                                                Deskripsi
                                            </label>
                                            <textarea
                                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                name="description" id="description" rows="4">{{ $venue->description }}</textarea>
                                            @error('description')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tombol Aksi -->
                        <div class="flex flex-col sm:flex-row justify-end mt-6 sm:mt-8 gap-3 sm:gap-0 sm:space-x-4">
                            <a href="{{ route('venue.index') }}"
                                class="w-full sm:w-auto px-6 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-700 transition text-center text-sm order-2 sm:order-1">
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
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 3000,
            background: '#222',
            color: '#fff'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
            showConfirmButton: true,
            background: '#222',
            color: '#fff'
        });
    @endif
</script>
@endpush