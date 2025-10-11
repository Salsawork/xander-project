@extends('app')
@section('title', 'Admin Dashboard - Tambah Event')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')

            <div class="mt-20 sm:mt-0 px-4 sm:px-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold my-6 sm:my-8">
                    Tambah Event Baru
                </h1>

                <form method="POST" action="{{ route('admin.event.store') }}" enctype="multipart/form-data"
                    class="bg-[#262626] rounded-lg p-6 sm:p-8 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="name">Nama Event</label>
                            <input name="name" value="{{ old('name') }}" id="name" type="text"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Masukkan nama event">
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="location">Lokasi</label>
                            <input name="location" value="{{ old('location') }}" id="location" type="text"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Masukkan lokasi event">
                            @error('location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            {{-- Price --}}
                            <label class="block text-xs text-gray-400 mb-1" for="price_ticket">Biaya (Rp)</label>
                            <input name="price_ticket" value="{{ old('price') }}" id="price_ticket" type="number" step="0.01"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('price_ticket') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Stock --}}
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="stock">Stok</label>
                            <input name="stock" value="{{ old('stock') }}" id="stock" type="number"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="start_date">Tanggal Mulai</label>
                            <input name="start_date" value="{{ old('start_date') }}" id="start_date" type="date"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('start_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="end_date">Tanggal Selesai</label>
                            <input name="end_date" value="{{ old('end_date') }}" id="end_date" type="date"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('end_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="game_types">Jenis Permainan</label>
                            <input name="game_types" value="{{ old('game_types') }}" id="game_types" type="text"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Contoh: 9 Ball, 10 Ball">
                            @error('game_types') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="status">Status</label>
                            <select name="status" id="status"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="">Pilih status</option>
                                <option value="Upcoming" {{ old('status') == 'Upcoming' ? 'selected' : '' }}>Upcoming</option>
                                <option value="Ongoing" {{ old('status') == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="Ended" {{ old('status') == 'Ended' ? 'selected' : '' }}>Ended</option>
                            </select>
                            @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 mb-1" for="description">Deskripsi</label>
                        <textarea name="description" id="description" rows="4"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Tulis deskripsi event">{{ old('description') }}</textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="total_prize_money">Total Hadiah (Rp)</label>
                            <input name="total_prize_money" value="{{ old('total_prize_money') }}" id="total_prize_money"
                                type="number" step="0.01"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('total_prize_money') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="champion_prize">Juara 1 (Rp)</label>
                            <input name="champion_prize" value="{{ old('champion_prize') }}" id="champion_prize" type="number"
                                step="0.01"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('champion_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="runner_up_prize">Juara 2 (Rp)</label>
                            <input name="runner_up_prize" value="{{ old('runner_up_prize') }}" id="runner_up_prize"
                                type="number" step="0.01"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('runner_up_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="third_place_prize">Juara 3 (Rp)</label>
                            <input name="third_place_prize" value="{{ old('third_place_prize') }}" id="third_place_prize"
                                type="number" step="0.01"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                            @error('third_place_prize') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="match_style">Format Pertandingan</label>
                            <input name="match_style" value="{{ old('match_style') }}" id="match_style" type="text"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Contoh: Single Elimination">
                            @error('match_style') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="finals_format">Format Final</label>
                            <input name="finals_format" value="{{ old('finals_format') }}" id="finals_format" type="text"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Contoh: Best of 5">
                            @error('finals_format') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 mb-1" for="divisions">Divisi (opsional)</label>
                        <input name="divisions" value="{{ old('divisions') }}" id="divisions" type="text"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Contoh: Open, Master, Junior">
                        @error('divisions') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 mb-1" for="social_media_handle">Akun Sosial Media</label>
                        <input name="social_media_handle" value="{{ old('social_media_handle') }}" id="social_media_handle" type="text"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Contoh: @official_event">
                        @error('social_media_handle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 mb-1" for="image_url">Gambar Event</label>
                        <input name="image_url" id="image_url" type="file"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                            accept="image/*">
                        @error('image_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                        <a href="{{ route('admin.event.index') }}"
                            class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-sm">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
