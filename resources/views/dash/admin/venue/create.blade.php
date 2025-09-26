@extends('app')
@section('title', 'Admin Dashboard - Tambah Venue')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold my-8 px-8">
                    Tambah Venue Baru
                </h1>
                <form method="POST" action="{{ route('venue.store') }}" class="flex flex-col lg:flex-row lg:space-x-8 px-8">
                    @csrf
                    <section aria-labelledby="user-info-title"
                        class="bg-[#262626] rounded-lg p-8 flex-1 max-w-lg space-y-8">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="user-info-title">
                            Informasi Akun
                        </h2>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="name">
                                    Nama Pengelola
                                </label>
                                <input name="name" value="{{ old('name') }}"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="name" type="text" placeholder="Masukkan nama pengelola venue" />
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
                        <div aria-labelledby="venue-info-title" class="bg-[#262626] rounded-lg p-6 space-y-4">
                            <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="venue-info-title">
                                Informasi Venue
                            </h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="venue_name">
                                        Nama Venue
                                    </label>
                                    <input name="venue_name" value="{{ old('venue_name') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="venue_name" type="text" placeholder="Masukkan nama venue" />
                                    @error('venue_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="address">
                                        Alamat
                                    </label>
                                    <textarea name="address"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="description" rows="4" placeholder="Masukkan deskripsi venue">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-4 justify-end">
                            <a href="{{ route('venue.index') }}"
                                class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                                Batal
                            </a>
                            <button
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                                type="submit">
                                Simpan Venue
                            </button>
                        </div>
                    </section>
                </form>
            </main>
        </div>
    </div>
@endsection