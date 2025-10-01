@extends('app')
@section('title', 'Venues Page - Xander Billiard')
@php
    $cartProducts = json_decode(request()->cookie('cartProducts') ?? '[]', true);
    $cartVenues = json_decode(request()->cookie('cartVenues') ?? '[]', true);
    $cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
    $cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);
@endphp

@section('content')
    <div class="min-h-screen px-6 md:px-20 py-10 bg-neutral-900 text-white">
        <div class="container mx-auto space-y-10">
            <nav class="text-xs text-gray-400 mb-4">
                <a href="{{ route('index') }}">Home</a> /
                <a href="{{ route('venues.index') }}">Venue</a> /
                <span class="text-white">{{ $detail->name }}</span>
            </nav>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-6">
                    {{-- Breadcrumb --}}

                    {{-- Gallery --}}
                    <div class="grid grid-cols-3 gap-4">
                        @php
                            $mainImagePath = 'https://placehold.co/800x510?text=No+Image';
                            if (!empty($detail->images) && is_array($detail->images) && !empty($detail->images[0])) {
                                $img = $detail->images[0];
                                if (
                                    !str_starts_with($img, 'http://') &&
                                    !str_starts_with($img, 'https://') &&
                                    !str_starts_with($img, '/storage/')
                                ) {
                                    $mainImagePath = asset('storage/uploads/' . $img);
                                } else {
                                    $mainImagePath = $img;
                                }
                            }
                        @endphp
                        <div class="col-span-2">
                            <img id="mainImage" src="{{ $mainImagePath }}" alt="{{ $detail->name }}"
                                class="rounded-lg w-full h-[300px] object-cover" />
                        </div>
                        <div class="flex flex-col gap-4">
                            <img src="https://placehold.co/400x250?text=Img+1"
                                class="rounded-lg w-full h-[250px] object-cover cursor-pointer"
                                onclick="changeMainImage('https://placehold.co/800x500?text=Img+1', this)" />
                            <img src="https://placehold.co/400x250?text=Img+2"
                                class="rounded-lg w-full h-[250px] object-cover cursor-pointer"
                                onclick="changeMainImage('https://placehold.co/800x500?text=Img+2', this)" />
                        </div>
                    </div>

                    {{-- Venue Info --}}
                    <div class="space-y-6">
                        <div>
                            <h1 class="text-2xl font-extrabold">{{ $detail->name }}</h1>
                            <p class="text-gray-300">{{ $detail->address ?? 'Jakarta Pusat' }}</p>
                        </div>

                        <hr class="border-gray-400">

                        {{-- Facilities --}}
                        <div>
                            <h2 class="font-semibold mb-2">Facilities</h2>
                            <ul class="grid grid-cols-3 gap-2 text-sm text-gray-300">
                                <li>• Food & Drinks</li>
                                <li>• Alcohol Available</li>
                                <li>• Smoking Area</li>
                                <li>• Non-Smoking Area</li>
                                <li>• VIP Lounge</li>
                                <li>• Equipment Rental</li>
                                <li>• Membership Program</li>
                                <li>• Live Tournament Streaming</li>
                                <li>• Private Training Rooms</li>
                            </ul>
                        </div>

                        <hr class="border-gray-400">

                        {{-- Location --}}
                        <div>
                            <h2 class="font-semibold mb-2">Location</h2>
                            <a href="https://maps.google.com" target="_blank" class="text-blue-400 underline text-sm">
                                Jl. MH Thamrin No. 45, Menteng, Jakarta Pusat, DKI Jakarta 10350
                            </a>
                            <div class="mt-3">
                                <iframe
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.601492962324!2d106.822823!3d-6.186487!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5c4dfdf!2sGrand%20Indonesia!5e0!3m2!1sen!2sid!4v1234567890"
                                    width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Booking Card --}}
                <div class="space-y-6">
                    {{-- Booking Box --}}
                    <div class="bg-neutral-800 p-5 rounded-lg shadow-md">
                        <p class="text-sm text-gray-400 mb-1">Start from</p>
                        <h2 class="text-xl font-bold text-white mb-4">
                            Rp. {{ number_format($detail->price, 0, ',', '.') }} / session
                        </h2>
    
                        <form id="addToCartForm" action="{{ route('cart.add.venue') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="id" value="{{ $detail->id }}">
    
                            {{-- Date --}}
                            <div>
                                <label class="text-sm text-gray-400">Date</label>
                                <input type="date" name="date" id="dateSelect"
                                    class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500"
                                    required
                                    min="{{ \Carbon\Carbon::parse($availableDates->max())->format('Y-m-d') }}"
                                    max="{{ \Carbon\Carbon::parse($availableDates->max())->format('Y-m-d') }}"
                                    onfocus="this.showPicker()"
                                    onkeydown="return false">
                            </div>
    
                            {{-- Schedule (akan di-fill oleh JS) --}}
                            <div id="scheduleContainer" class="hidden mt-1">
                                <label class="text-sm text-gray-400">Schedule</label>
                                <div class="grid grid-cols-3 gap-2 mt-1"></div>
                            </div>
    
                            {{-- Promo --}}
                            <div>
                                <label class="text-sm text-gray-400">Promo code (Optional)</label>
                                <input type="text" name="promo"
                                    class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500"
                                    placeholder="Ex. PROMO70%DAY">
                            </div>
    
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold">
                                Add to cart
                            </button>
                        </form>
                    </div>

                    {{-- Terms --}}
                    <div class="bg-neutral-800 p-5 rounded-lg text-sm text-gray-300">
                        <h3 class="font-semibold mb-2">Terms & Conditions</h3>
                        <p class="mb-2">Guests are expected to <span class="font-semibold">follow all venue rules and staff
                                instructions</span> at all times.</p>
                        <p class="mb-2">Any damage to equipment or property caused by negligence or misuse will be the
                            responsibility of the guest.</p>
                        <p class="mb-2">Outside food and beverages are <span class="font-semibold">not permitted</span>
                            unless explicitly allowed by the venue.</p>
                        <p>To maintain a comfortable environment, disruptive behavior, including excessive intoxication or
                            aggression, will result in immediate removal <span class="font-semibold">without a
                                refund</span>.</p>
                    </div>
                </div>
            </div>
        </div>

        <button aria-label="Shopping cart with 3 items" onclick="showCart()"
            class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
            <i class="fas fa-shopping-cart text-white text-3xl">
            </i>
            @if ($cartCount > 0)
                <span
                    class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
                    {{ $cartCount }}
                </span>
            @endif

        </button>
        {{-- Cart Sidebar --}}
        @include('public.cart')
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('addToCartForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Tampilkan SweetAlert
            Swal.fire({
                title: 'Berhasil!',
                text: 'Venue ditambahkan ke keranjang',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                background: '#1E1E1F',
                color: '#FFFFFF',
                iconColor: '#4BB543'
            }).then((result) => {
                // Kirim form setelah user klik OK
                this.submit();
            });
        });
    </script>
    <script>
        const dateSelect = document.getElementById('dateSelect');
        const scheduleContainer = document.getElementById('scheduleContainer');
        const scheduleGrid = scheduleContainer.querySelector('.grid');
        const schedules = @json($sessions); 
    
        // Saat pertama load, sembunyikan container
        scheduleContainer.classList.add('hidden');
        scheduleGrid.innerHTML = '<p class="text-gray-400 text-sm">Pilih tanggal terlebih dahulu.</p>';
    
        dateSelect.addEventListener('change', function () {
            const selectedDate = this.value;
            scheduleGrid.innerHTML = '';
    
            if (!selectedDate) {
                scheduleContainer.classList.add('hidden');
                scheduleGrid.innerHTML = '<p class="text-gray-400 text-sm">Pilih tanggal terlebih dahulu.</p>';
                return;
            }
    
            // Filter schedule by date
            const filtered = schedules.filter(s => s.date === selectedDate);
    
            if (filtered.length === 0) {
                scheduleGrid.innerHTML = '<p class="text-gray-400 text-sm">Tidak ada jadwal tersedia.</p>';
            } else {
                filtered.forEach(schedule => {
                    const start = schedule.start_time;
                    const end   = schedule.end_time;
                    const label = document.createElement('label');
                    label.className = 'cursor-pointer';
                    label.innerHTML = `
                        <input type="radio"
                               name="schedule[start]"
                               value="${start}"
                               class="hidden peer"
                               data-end="${end}"
                               required>
                        <input type="hidden" name="schedule[end]" value="${end}">
                        <span class="block border border-gray-600 rounded text-center py-2 text-sm
                                     hover:bg-blue-600 hover:border-blue-600
                                     peer-checked:bg-blue-600 peer-checked:text-white">
                            ${start} - ${end}
                        </span>`;
                    scheduleGrid.appendChild(label);
                });
            }
    
            scheduleContainer.classList.remove('hidden');
        });
    </script>
    
@endpush
<style>
    label:has(input[type="radio"]:checked) {
    background-color: #2563eb;
    border-color: #2563eb;
    color: white;
    }
</style>