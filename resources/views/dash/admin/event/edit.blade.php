@extends('app')
@section('title', 'Admin Dashboard - Edit Event')

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
                        Edit Event: {{ $event->name }}
                    </h1>

                    <form method="POST" action="{{ route('admin.event.update', $event) }}" enctype="multipart/form-data"
                        class="flex flex-col lg:flex-row lg:space-x-8" id="eventEditForm">
                        @csrf
                        @method('PUT')

                        <section class="bg-[#262626] rounded-lg p-4 sm:p-8 flex-1 max-w-full lg:max-w-lg space-y-6 sm:space-y-8 mb-6 lg:mb-0">
                            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2">
                                Informasi Event
                            </h2>
                            <div class="space-y-4 sm:space-y-6">
                                {{-- Nama Event --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="name">
                                        Nama Event
                                    </label>
                                    <input name="name" value="{{ old('name', $event->name) }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="name" type="text" placeholder="Masukkan nama event" />
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Lokasi --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="location">
                                        Lokasi
                                    </label>
                                    <input name="location" value="{{ old('location', $event->location) }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="location" type="text" placeholder="Masukkan lokasi event" />
                                    @error('location')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Price Ticket --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="price_ticket">
                                        Biaya (Rp)
                                    </label>
                                    <input name="price_ticket" value="{{ old('price_ticket', $event->price_ticket) }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="price_ticket" type="number" step="0.01" placeholder="Masukkan biaya tiket" />
                                    @error('price_ticket')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Stock --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="stock">
                                        Stok
                                    </label>
                                    <input name="stock" value="{{ old('stock', $event->stock) }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="stock" type="number" placeholder="Masukkan stok tiket" />
                                    @error('stock')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Deskripsi --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="description">
                                        Deskripsi
                                    </label>
                                    <textarea name="description"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="description" rows="3" placeholder="Masukkan deskripsi">{{ old('description', $event->description) }}</textarea>
                                    @error('description')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Game Types --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="game_types">
                                        Jenis Game
                                    </label>
                                    <input name="game_types" value="{{ old('game_types', $event->game_types) }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="game_types" type="text" placeholder="Contoh: 8 Ball, 9 Ball" />
                                    @error('game_types')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Tanggal Mulai & Selesai --}}
                                <div class="flex space-x-4">
                                    <div class="w-1/2">
                                        <label class="block text-xs text-gray-400 mb-1" for="start_date">
                                            Tanggal Mulai
                                        </label>
                                        <input name="start_date" value="{{ old('start_date', $event->start_date->format('Y-m-d')) }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white"
                                            id="start_date" type="date" />
                                        @error('start_date')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="w-1/2">
                                        <label class="block text-xs text-gray-400 mb-1" for="end_date">
                                            Tanggal Selesai
                                        </label>
                                        <input name="end_date" value="{{ old('end_date', $event->end_date->format('Y-m-d')) }}"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white"
                                            id="end_date" type="date" />
                                        @error('end_date')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Total Prize (Rupiah Format) --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="total_prize_money">
                                        Total Hadiah (Rp)
                                    </label>
                                    <input name="total_prize_money" value="{{ old('total_prize_money', $event->total_prize_money) }}"
                                        class="rupiah w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white"
                                        id="total_prize_money" type="text" inputmode="numeric" autocomplete="off" />
                                    @error('total_prize_money')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Prize Breakdown (Rupiah Format) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="champion_prize">
                                            Juara 1 (Rp)
                                        </label>
                                        <input name="champion_prize" value="{{ old('champion_prize', $event->champion_prize) }}"
                                            class="rupiah w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white"
                                            id="champion_prize" type="text" inputmode="numeric" autocomplete="off" />
                                        @error('champion_prize')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-400 mb-1" for="runner_up_prize">
                                            Juara 2 (Rp)
                                        </label>
                                        <input name="runner_up_prize" value="{{ old('runner_up_prize', $event->runner_up_prize) }}"
                                            class="rupiah w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white"
                                            id="runner_up_prize" type="text" inputmode="numeric" autocomplete="off" />
                                        @error('runner_up_prize')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs text-gray-400 mb-1" for="third_place_prize">
                                            Juara 3 (Rp)
                                        </label>
                                        <input name="third_place_prize" value="{{ old('third_place_prize', $event->third_place_prize) }}"
                                            class="rupiah w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white"
                                            id="third_place_prize" type="text" inputmode="numeric" autocomplete="off" />
                                        @error('third_place_prize')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Upload Gambar --}}
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="image">
                                        Gambar Event
                                    </label>
                                    <input name="image" type="file"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white"
                                        id="image" accept="image/*" />
                                    @error('image')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror

                                    @if($event->image_url)
                                        <div class="mt-3">
                                            <p class="text-xs text-gray-400 mb-1">Gambar Saat Ini:</p>
                                            <img src="{{ asset('images/' . $event->image_url) }}" alt="{{ $event->name }}"
                                                class="w-48 rounded-md border border-gray-700">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </section>

                        <section class="flex flex-col justify-between space-y-6 sm:space-y-8 w-full max-w-full lg:max-w-lg">
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-0 sm:space-x-4 sm:justify-end">
                                <a href="{{ route('admin.event.index') }}"
                                    class="w-full sm:w-auto px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-center text-sm order-2 sm:order-1">
                                    Batal
                                </a>
                                <button type="submit"
                                    class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm order-1 sm:order-2">
                                    Update
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
    // ===== Number format (Rupiah) untuk Total Hadiah & Juara 1/2/3 =====
    const nfID = new Intl.NumberFormat('id-ID');
    function onlyDigits(v){ return (v||'').toString().replace(/[^\d]/g,''); }
    function formatRupiahInput(el){
        const raw = onlyDigits(el.value);
        el.value = raw ? nfID.format(parseInt(raw,10)) : '';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // SweetAlert success
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

        // Terapkan format rupiah ke field hadiah
        const rupiahFields = [
            'total_prize_money',
            'champion_prize',
            'runner_up_prize',
            'third_place_prize'
        ].map(id => document.getElementById(id)).filter(Boolean);

        rupiahFields.forEach(el => {
            if(el.value) formatRupiahInput(el);     // inisialisasi
            el.addEventListener('input', () => formatRupiahInput(el));
            el.addEventListener('blur',  () => formatRupiahInput(el));
        });

        // Bersihkan titik sebelum submit agar backend menerima angka murni
        const form = document.getElementById('eventEditForm');
        form.addEventListener('submit', () => {
            rupiahFields.forEach(el => { el.value = onlyDigits(el.value); });
        });
    });
</script>
@endpush
