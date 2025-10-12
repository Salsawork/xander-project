@extends('app')
@section('title', 'Venues Page - Xander Billiard')

@push('styles')
<style>
    :root{ color-scheme: dark; --page-bg:#0a0a0a; }
    :root, html, body{ background:var(--page-bg); }
    html, body{ height:100%; overscroll-behavior: none; touch-action: pan-y; -webkit-text-size-adjust: 100%; }
    #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
    #app, main{ background:var(--page-bg); }
    .scroll-root, .scroll-inner{ overscroll-behavior: contain; background:var(--page-bg); }

    label:has(input[type="radio"]:checked) {
        background-color: #2563eb;
        border-color: #2563eb;
        color: white;
    }
</style>
@endpush

@php
$cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);
@endphp

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<script>
  (function(){
    function setSVH(){ const svh=window.innerHeight*0.01; document.documentElement.style.setProperty('--svh',svh+'px'); }
    setSVH(); window.addEventListener('resize',setSVH);
  })();
</script>

<div class="min-h-screen px-6 md:px-20 py-10 bg-neutral-900 text-white scroll-root">
    <div class="container mx-auto space-y-10 scroll-inner">
        {{-- Breadcrumb --}}
        <nav class="text-xs text-gray-400 mb-4">
            <a href="{{ route('index') }}">Home</a> /
            <a href="{{ route('venues.index') }}">Venue</a> /
            <a href="{{ route('venues.detail', ['venue' => $detail->id, 'slug' => $detail->name]) }}" class="text-white">
                {{ $detail->name }}
            </a>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- LEFT --}}
            <div class="md:col-span-2 space-y-6">
                {{-- Gallery --}}
                <div class="grid grid-cols-3 gap-4">
                    @php
                        $mainImagePath = 'https://placehold.co/800x510?text=No+Image';
                        if (!empty($detail->images) && is_array($detail->images) && !empty($detail->images[0])) {
                            $img = $detail->images[0];
                            if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/storage/')) {
                                $mainImagePath = asset('storage/uploads/' . $img);
                            } else { $mainImagePath = $img; }
                        }
                    @endphp
                    <div class="col-span-2">
                        <img id="mainImage" src="{{ $mainImagePath }}" alt="{{ $detail->name }}" class="rounded-lg w-full h-[300px] object-cover" />
                    </div>
                    <div class="flex flex-col gap-4">
                        <img src="https://placehold.co/400x250?text=Img+1" class="rounded-lg w-full h-[250px] object-cover cursor-pointer"
                             onclick="changeMainImage('https://placehold.co/800x500?text=Img+1')" />
                        <img src="https://placehold.co/400x250?text=Img+2" class="rounded-lg w-full h-[250px] object-cover cursor-pointer"
                             onclick="changeMainImage('https://placehold.co/800x500?text=Img+2')" />
                    </div>
                </div>

                {{-- Info --}}
                <div class="space-y-6">
                    <div>
                        <h1 class="text-2xl font-extrabold">{{ $detail->name }}</h1>
                        <p class="text-gray-300">{{ $detail->address ?? 'Jakarta Pusat' }}</p>
                    </div>
                    <hr class="border-gray-400">
                    <div>
                        <h2 class="font-semibold mb-2">Facilities</h2>
                        <ul class="grid grid-cols-3 gap-2 text-sm text-gray-300">
                            <li>• Food & Drinks</li>
                            <li>• Smoking Area</li>
                            <li>• VIP Lounge</li>
                            <li>• Equipment Rental</li>
                        </ul>
                    </div>
                    <hr class="border-gray-400">
                    <div>
                        <h2 class="font-semibold mb-2">Location</h2>
                        <a href="https://maps.google.com" target="_blank" class="text-blue-400 underline text-sm">
                            {{ $detail->address ?? 'No address available' }}
                        </a>
                        <div class="mt-3">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.601492962324!2d106.822823!3d-6.186487!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5c4dfdf!2sGrand%20Indonesia!5e0!3m2!1sen!2sid!4v1234567890"
                                width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Booking --}}
            <div class="space-y-6">
                <div class="bg-neutral-800 p-5 rounded-lg shadow-md">
                    <p class="text-sm text-gray-400 mb-1">Start from</p>
                    <h2 id="priceDisplay" class="text-xl font-bold text-white mb-4">
                        Rp. {{ number_format($minPrice, 0, ',', '.') }}
                    </h2>

                    <form id="addToCartForm" action="{{ route('cart.add.venue') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="id" value="{{ $detail->id }}">

                        <div>
                            <label class="text-sm text-gray-400">Date</label>
                            <input type="date" id="datePicker" name="date" class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500">
                        </div>

                        <div id="scheduleContainer" class="mt-1">
                            <label class="text-sm text-gray-400">Schedule</label>
                            <div id="scheduleList" class="grid grid-cols-3 gap-3 mt-3"></div>
                        </div>

                        <div id="tableContainer" class="mt-1">
                            <label class="text-sm text-gray-400">Table</label>
                            <div id="tableList" class="grid grid-cols-3 gap-3 mt-3"></div>
                        </div>

                        <div>
                            <label class="text-sm text-gray-400">Promo code (Optional)</label>
                            <input type="text" name="promo"
                                class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500"
                                placeholder="Ex. PROMO70%DAY">
                        </div>

                        @if (Auth::check() && Auth::user()->roles === 'user')
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold">
                            Add to cart
                        </button>
                        @endif
                    </form>
                </div>

                <div class="bg-neutral-800 p-5 rounded-lg text-sm text-gray-300">
                    <h3 class="font-semibold mb-2">Terms & Conditions</h3>
                    <p class="mb-2">Guests are expected to follow all venue rules and staff instructions.</p>
                    <p class="mb-2">Any damage due to negligence is guest responsibility.</p>
                    <p class="mb-2">Outside food and beverages are not permitted unless explicitly allowed.</p>
                    <p>Disruptive behavior may result in removal without refund.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating Cart --}}
    @if (Auth::check() && Auth::user()->roles === 'user')
    <button aria-label="Shopping cart with {{ $cartCount }} items" onclick="showCart()"
        class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
        <i class="fas fa-shopping-cart text-white text-3xl"></i>
        @if ($cartCount > 0)
        <span class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
            {{ $cartCount }}
        </span>
        @endif
    </button>
    @endif
    @include('public.cart')
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
function changeMainImage(src){ document.getElementById('mainImage').src = src; }

document.addEventListener("DOMContentLoaded", function() {
    const datePicker = document.getElementById("datePicker");
    const scheduleList = document.getElementById("scheduleList");
    const tableList = document.getElementById("tableList");
    const form = document.getElementById("addToCartForm");
    const venueId = "{{ $detail->id }}";
    let selectedSchedule = null; // keep selected schedule data
    let selectedTableNumber = null; // keep selected table number

    datePicker.addEventListener("change", function() {
        const selectedDate = this.value;
        if (!selectedDate) return;
        scheduleList.innerHTML = `<p class="text-gray-400 text-sm">Loading schedules...</p>`;
        tableList.innerHTML = "";

        fetch(`{{ url('/venues') }}/${venueId}/price-schedules?date=${encodeURIComponent(selectedDate)}`)
            .then(res => res.json())
            .then(data => {
                scheduleList.innerHTML = "";
                const schedules = data.schedules || [];

                if (schedules.length === 0) {
                    scheduleList.innerHTML = `<p class="text-gray-400 text-sm">No schedules available.</p>`;
                    return;
                }

                schedules.forEach(sch => {
                    sch.schedule.forEach(slot => {
                        const lbl = document.createElement("label");
                        lbl.className = "border rounded px-2 py-2 cursor-pointer flex items-center justify-center";
                        lbl.innerHTML = `
                            <input type="radio" name="schedule" value="${slot.start}-${slot.end}" class="hidden" required>
                            ${slot.start} - ${slot.end}
                        `;

                        const radio = lbl.querySelector("input");
                        radio.addEventListener("change", () => {
                            selectedSchedule = {
                                start: slot.start,
                                end: slot.end,
                                price: sch.price
                            };
                            document.getElementById("priceDisplay").innerText = "Rp " + sch.price;
                            renderTables(slot.tables);
                        });

                        scheduleList.appendChild(lbl);
                    });
                });
            })
            .catch(err => {
                scheduleList.innerHTML = `<p class="text-red-500 text-sm">Error loading schedules.</p>`;
                console.error(err);
            });
    });

    function renderTables(tables = []) {
        tableList.innerHTML = "";
        if (tables.length === 0) {
            tableList.innerHTML = `<p class="text-gray-400 text-sm">No tables available.</p>`;
            return;
        }

        tables.forEach(tbl => {
            const lbl = document.createElement("label");
            lbl.className = `border rounded px-2 py-2 cursor-pointer flex justify-center items-center ${tbl.is_booked ? 'opacity-40 pointer-events-none bg-gray-700' : ''}`;
            lbl.innerHTML = `
                <input type="radio" name="table_id" value="${tbl.id}" class="hidden">
                ${tbl.name || 'Table ' + tbl.id}
            `;
            const radio = lbl.querySelector("input");
            radio.addEventListener("change", () => {
                selectedTableNumber = tbl.name || 'Table ' + tbl.id;
            });
            tableList.appendChild(lbl);
        });
    }

    // Form submit handler with validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!isLoggedIn) {
            Swal.fire({
                title: 'Belum Login!',
                text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
                icon: 'warning'
            }).then(() => {
                window.location.href = '/login';
            });
            return;
        }

        const date = datePicker.value;
        const schedule = document.querySelector('input[name="schedule"]:checked');
        const table = document.querySelector('input[name="table_id"]:checked');

        if (!date) {
            Swal.fire({
                title: 'Oops!',
                text: 'Silakan pilih tanggal terlebih dahulu.',
                icon: 'warning'
            });
            return;
        }
        if (!schedule) {
            Swal.fire({
                title: 'Oops!',
                text: 'Silakan pilih jadwal terlebih dahulu.',
                icon: 'warning'
            });
            return;
        }
        if (!table) {
            Swal.fire({
                title: 'Oops!',
                text: 'Silakan pilih meja terlebih dahulu.',
                icon: 'warning'
            });
            return;
        }

        // Append schedule & price fields to FormData
        const fd = new FormData(this);
        fd.append('schedule[start]', selectedSchedule.start);
        fd.append('schedule[end]', selectedSchedule.end);
        fd.append('price', selectedSchedule.price);
        fd.append('table', selectedTableNumber);

        Swal.fire({
            title: 'Mohon tunggu...',
            text: 'Sedang memproses permintaan Anda.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch(this.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Venue berhasil ditambahkan ke keranjang.',
                        icon: 'success'
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.message || 'Terjadi kesalahan, coba lagi.',
                        icon: 'error'
                    });
                }
            })
            .catch(() => {
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan jaringan.',
                    icon: 'error'
                });
            });
    });
});
</script>
@endpush