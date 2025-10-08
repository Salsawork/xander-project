@extends('app')
@section('title', 'Venues Page - Xander Billiard')

@push('styles')
<style>
    label:has(input[type="radio"]:checked) {
        background-color: #2563eb;
        border-color: #2563eb;
        color: white;
    }
</style>
@endpush

@php
$cartProducts = json_decode(request()->cookie('cartProducts') ?? '[]', true);
$cartVenues = json_decode(request()->cookie('cartVenues') ?? '[]', true);
$cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
$cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);
@endphp

@section('content')
<div class="min-h-screen px-6 md:px-20 py-10 bg-neutral-900 text-white">
    <div class="container mx-auto space-y-10">
        {{-- Breadcrumb --}}
        <nav class="text-xs text-gray-400 mb-4">
            <a href="{{ route('index') }}">Home</a> /
            <a href="{{ route('venues.index') }}">Venue</a> /
            <span class="text-white">{{ $detail->name }}</span>
        </nav>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- LEFT: Detail --}}
            <div class="md:col-span-2 space-y-6">
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
                <div class="bg-neutral-800 p-5 rounded-lg shadow-md">
                    <p class="text-sm text-gray-400 mb-1">Start from</p>
                    <h2 id="priceDisplay" class="text-xl font-bold text-white mb-4">
                        Rp. {{ number_format($minPrice, 0, ',', '.') }}
                    </h2>

                    <form id="addToCartForm" action="{{ route('cart.add.venue') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="id" value="{{ $detail->id }}">

                        {{-- Date --}}
                        <div>
                            <label class="text-sm text-gray-400">Date</label>
                            <!-- NOTE: added name="date" so it is included in FormData -->
                            <input type="date" id="datePicker" name="date"
                                class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500">
                        </div>

                        {{-- Schedule --}}
                        <div id="scheduleContainer" class="mt-1">
                            <label class="text-sm text-gray-400">Schedule</label>
                            <div id="scheduleList" class="grid grid-cols-3 gap-3 mt-3"></div>
                        </div>

                        {{-- Table --}}
                        <div id="tableContainer" class="mt-1">
                            <label class="text-sm text-gray-400">Table</label>
                            <div id="tableList" class="grid grid-cols-3 gap-3 mt-3"></div>
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

    {{-- Floating Cart Button --}}
    <button aria-label="Shopping cart with {{ $cartCount }} items" onclick="showCart()"
        class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
        <i class="fas fa-shopping-cart text-white text-3xl"></i>
        @if ($cartCount > 0)
        <span
            class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
            {{ $cartCount }}
        </span>
        @endif
    </button>

    @include('public.cart')
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    document.addEventListener("DOMContentLoaded", function() {
        const datePicker = document.getElementById("datePicker");
        const scheduleList = document.getElementById("scheduleList");
        const tableList = document.getElementById("tableList");
        const form = document.getElementById("addToCartForm");
        const venueId = "{{ $detail->id }}";
        const tables = @json($tables);

        datePicker.addEventListener("change", function() {
            const selectedDate = this.value;
            if (!selectedDate) return;

            form.querySelectorAll("input[name='schedule[start]'], input[name='schedule[end]'], input[name='price'], input[name='table_id']").forEach(el => el.remove());

            fetch(`venues/${venueId}/price-schedules?date=${selectedDate}`)
                .then(res => res.json())
                .then(data => {
                    scheduleList.innerHTML = "";
                    tableList.innerHTML = "";

                    const schedules = Array.isArray(data.schedules) ?
                        data.schedules :
                        Object.values(data.schedules);

                    if (schedules && schedules.length > 0) {
                        schedules.forEach((sch) => {
                            if (sch.schedule && sch.schedule.length > 0) {
                                sch.schedule.forEach((slot) => {
                                    const lbl = document.createElement("label");
                                    lbl.className = "border rounded px-2 py-2 cursor-pointer flex items-center justify-between";
                                    lbl.innerHTML = `
                                        <input type="radio" name="schedule_id"
                                               value="${sch.id}"
                                               data-start="${slot.start}"
                                               data-end="${slot.end}"
                                               data-price="${sch.price}"
                                               class="hidden" required>
                                        ${slot.start} - ${slot.end}
                                    `;

                                    const radio = lbl.querySelector("input[type=radio]");

                                    radio.addEventListener("change", () => {
                                        const rawPrice = radio.dataset.price || "0";
                                        const price = parseInt(rawPrice.replace(/\./g, "").replace(",", "")) || 0;
                                        document.getElementById("priceDisplay").innerText = "Rp " + price.toLocaleString('id-ID');
                                        renderTables(tables || [], sch.tables_applicable || []);
                                        form.querySelectorAll("input[name='schedule[start]'], input[name='schedule[end]'], input[name='price']").forEach(el => el.remove());
                                        const startHidden = document.createElement("input");
                                        startHidden.type = "hidden";
                                        startHidden.name = "schedule[start]";
                                        startHidden.value = radio.dataset.start;
                                        form.appendChild(startHidden);

                                        const endHidden = document.createElement("input");
                                        endHidden.type = "hidden";
                                        endHidden.name = "schedule[end]";
                                        endHidden.value = radio.dataset.end;
                                        form.appendChild(endHidden);

                                        const priceHidden = document.createElement("input");
                                        priceHidden.type = "hidden";
                                        priceHidden.name = "price";
                                        priceHidden.value = price;
                                        form.appendChild(priceHidden);
                                    });

                                    scheduleList.appendChild(lbl);
                                });
                            }
                        });
                    } else {
                        scheduleList.innerHTML = `<p class="text-gray-400 text-sm">No schedules available</p>`;
                    }
                })
                .catch(err => {
                    console.error("Error fetching schedules:", err);
                    scheduleList.innerHTML = `<p class="text-red-500 text-sm">Failed to load schedules</p>`;
                });
        });

        function renderTables(tables, applicableIds) {
            tableList.innerHTML = "";

            if (!applicableIds || applicableIds.length === 0) {
                tableList.innerHTML = `<p class="text-gray-400 text-sm">No tables applicable</p>`;
                return;
            }

            const applicableTableIds = applicableIds;

            const filteredTables = tables.filter(tbl => applicableTableIds.includes(String(tbl.table_number)));

            if (filteredTables.length === 0) {
                tableList.innerHTML = `<p class="text-gray-400 text-sm">No tables found</p>`;
                return;
            }

            filteredTables.forEach(tbl => {
                const btn = document.createElement("label");
                btn.className = "border rounded px-2 py-2 cursor-pointer";
                btn.innerHTML = `
            <input type="radio" name="table" value="${tbl.table_number}" class="hidden">
            <span>${tbl.table_number ?? tbl.name ?? tbl.id}</span>
        `;
                tableList.appendChild(btn);
            });
        }

    });

    // submit handler (tidak diubah selain tetap menggunakan FormData(this))
    document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
        e.preventDefault();


        // 🛑 Cek login dulu sebelum validasi lainnya
        if (!isLoggedIn) {
            Swal.fire({
                title: 'Belum Login!',
                text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
                icon: 'warning',
                confirmButtonText: 'Login Sekarang',
                confirmButtonColor: '#3085d6',
                background: '#1E1E1F',
                color: '#FFFFFF'
            }).then(() => {
                window.location.href = '/login'; // arahkan ke halaman login
            });
            return;
        }

        // ✅ Setelah login, baru validasi form
        const date = document.getElementById('datePicker')?.value;
        const schedule = document.querySelector('input[name="schedule_id"]:checked');
        const table = document.querySelector('input[name="table"]:checked');

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

        // 🌀 Proses kirim data ke backend
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
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Venue berhasil ditambahkan ke keranjang.',
                    icon: 'success'
                });

                this.reset();
                document.getElementById('scheduleList').innerHTML = '';
                document.getElementById('tableList').innerHTML = '';
                location.reload();
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Terjadi kesalahan, coba lagi.',
                    icon: 'error'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan jaringan.',
                icon: 'error'
            });
        });
});

</script>
@endpush