@extends('app')

@section('title', 'Sparring Detail')
@php
$cartProducts = json_decode(request()->cookie('cartProducts') ?? '[]', true);
$cartVenues = json_decode(request()->cookie('cartVenues') ?? '[]', true);
$cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
$cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);

$detail = $athlete->athleteDetail ?? null;
$years = $detail->experience_years ?? null;
$yearsText = $years ? $years . ' Years' : 'N/A';
$specialty = $detail->specialty ?? 'N/A';
$location = $detail->location ?? 'N/A';
$bio = $detail->bio ?? 'Pemain biliar profesional dengan pengalaman mengajar lebih dari 5 tahun. Spesialis dalam teknik kontrol bola dan strategi permainan.';

// Share data
$shareUrlAbs = url()->current();
$shareText = 'Sparring dengan ' . ($athlete->name ?? 'Athlete') . ' di Xander Billiard';
$fbShareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareUrlAbs);
$xShareUrl = 'https://twitter.com/intent/tweet?text=' . urlencode($shareText) . '&url=' . urlencode($shareUrlAbs);

$availableDates = $availableDates ?? [];

// Rating summary
$avgText = number_format((float)($averageRating ?? 0), 1, ',', '.');
$fullStars = floor((float)($averageRating ?? 0));
@endphp

@push('styles')
<style>
    /* ====== ANTI OVERFLOW & SCROLL ====== */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        overscroll-behavior-y: none;
        overscroll-behavior-x: none;
        scrollbar-gutter: stable both-edges;
    }

    .page-wrap {
        overflow-x: clip;
    }

    :root {
        color-scheme: dark;
    }

    html,
    body {
        background: #0a0a0a;
    }

    #app,
    main {
        background: #0a0a0a;
    }

    .btn-share {
        width: 2.25rem;
        height: 2.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        background: #374151;
        transition: .2s;
    }

    .btn-share:hover {
        background: #4b5563;
    }

    .card {
        background: rgba(38, 38, 38, .95);
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
    }

    .input-pill {
        width: 100%;
        padding: .80rem 2.75rem .80rem .9rem;
        border-radius: 12px;
        background: #3f3f46;
        color: #fff;
        border: 1.5px solid rgba(255, 255, 255, .35);
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        font-size: 13px;
    }

    .input-pill:focus {
        box-shadow: 0 0 0 2px #3b82f6;
        border-color: #3b82f6;
    }

    .input-pill::placeholder {
        color: #9ca3af;
    }

    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        display: none;
    }

    input[type="date"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-clear-button {
        display: none;
    }

    input[type="date"] {
        -moz-appearance: textfield;
    }

    .slot {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: .38rem .5rem;
        border-radius: 9px;
        font-weight: 800;
        background: #4b5563;
        color: #fff;
        border: 1.5px solid rgba(255, 255, 255, .6);
        transition: .18s;
        cursor: pointer;
        user-select: none;
        font-size: .88rem;
    }

    .slot:hover {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .slot--active {
        background: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }

    .slot--disabled {
        background: #6b7280 !important;
        color: #d1d5db !important;
        border-color: transparent !important;
        cursor: not-allowed !important;
    }

    .booking-card {
        padding: 25px !important;
        border-radius: 12px;
    }

    .booking-card hr {
        margin-top: 8px;
        margin-bottom: 14px;
    }

    .booking-card .price {
        font-size: 20px !important;
        line-height: 1;
    }

    .booking-card p,
    .booking-card label,
    .booking-card span {
        font-size: 12px;
    }

    .booking-card button[type="submit"] {
        height: 42px;
        font-size: 15px;
        border-radius: 10px;
    }

    .field-wrap {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
    }

    .field-wrap .date-btn {
        position: absolute;
        right: 6px;
        top: 0;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-inline: 6px;
        color: #d1d5db;
        font-size: 13px;
        background: transparent;
        border: 0;
        cursor: pointer;
    }

    .field-wrap .date-btn:hover {
        color: #ffffff;
    }

    /* ====== Ringkasan Customer Reviews (kiri) ====== */
    .reviews-card {
        background: rgba(23, 23, 23, .95);
        border-radius: 14px;
        padding: 18px 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
        width: 100%;
    }

    .reviews-card h3 {
        font-weight: 700;
    }

    .reviews-card hr {
        border-color: rgba(255, 255, 255, .12);
        margin: 8px 0 14px;
    }

    .rating-row {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .rating-stars i {
        font-size: 22px;
        color: #fbbf24;
    }

    .rating-number {
        font-size: 28px;
        font-weight: 800;
        letter-spacing: .5px;
    }

    .rating-outof {
        font-size: 12px;
        color: #9ca3af;
        margin-left: .35rem;
    }

    .bar-row {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-top: .55rem;
    }

    .bar-row .label {
        width: 18px;
        color: #fbbf24;
        text-align: center;
    }

    .bar-row .ratebar {
        flex: 1;
        height: 10px;
        background: #4b5563;
        border-radius: 9999px;
        overflow: hidden;
    }

    .bar-row .ratebar .fill {
        height: 100%;
        background: #e5e7eb;
        border-radius: 9999px;
    }

    .bar-row .count {
        width: 70px;
        text-align: right;
        font-size: 12px;
        color: #9ca3af;
    }

    /* ====== REVIEW ITEM (desktop default) ====== */
    .review-item {
        --avatar: 48px;
        --gap: 16px;
        --indent: calc(var(--avatar) + var(--gap));
        position: relative;
        padding-top: 22px;
    }

    .review-item::before {
        content: "";
        position: absolute;
        left: var(--indent);
        right: 0;
        top: 0;
        height: 1px;
        background: rgba(255, 255, 255, .18);
    }

    .review-head {
        position: relative;
    }

    .review-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .review-avatar {
        width: var(--avatar);
        height: var(--avatar);
        border-radius: 9999px;
        background: #374151;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 18px;
        overflow: hidden;
    }

    .review-stars-row {
        position: absolute;
        left: var(--indent);
        right: 0;
        top: 2px;
        display: flex;
        justify-content: flex-end;
        pointer-events: none;
    }

    .user-stars i {
        font-size: 26px;
        color: #e5e7eb;
    }

    /* ====== MOBILE ONLY: rapihin rating & header komentar ====== */
    @media (max-width: 640px) {
        .review-item {
            --avatar: 40px;
            --gap: 12px;
            padding: 16px 14px 14px;
        }

        .review-left {
            gap: 12px;
        }

        .review-avatar {
            font-size: 16px;
        }

        .review-name {
            font-size: 15px !important;
            line-height: 1.2;
        }

        .review-date {
            font-size: 11px !important;
        }

        /* Bintang pindah ke flow normal (tak overlap) */
        .review-head .review-stars-row {
            position: static;
            margin-top: 4px;
            justify-content: flex-start;
            pointer-events: none;
        }

        .review-head .user-stars i {
            font-size: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-wrap text-white min-h-screen" style="background-image:url('{{ asset('images/bg/background_3.png') }}'); background-size:cover; background-position:center; background-repeat:no-repeat;">

    <!-- TOP: Foto / Bio / Booking -->
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 grid grid-cols-1 md:grid-cols-3 gap-10">
        <!-- Foto -->
        <div>
            <nav id="breadcrumbTop" class="flex items-center gap-2 text-sm text-gray-300 mb-3">
                <a href="/" class="hover:text-white">Home</a><span class="text-gray-500">/</span>
                <a href="{{ route('sparring.index') }}" class="hover:text-white">Sparring</a><span class="text-gray-500">/</span>
                <span class="text-white">{{ $athlete->name }}</span>
            </nav>
            @php
            $photo = ($detail && $detail->image) ? asset('images/athlete/' . $detail->image) : asset('images/placeholder.jpg');
            @endphp
            <img src="{{ $photo }}" alt="{{ $athlete->name }}"
                class="w-full h-[430px] object-cover rounded-lg shadow-lg"
                onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
        </div>

        <!-- Bio -->
        <div id="titleStart" class="mt-0">
            <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">{{ $athlete->name }}</h1>
            <p class="text-xl text-gray-300 mt-1">Handicap {{ $detail->handicap ?? 'N/A' }}</p>
            <hr class="border-white/30 mt-6 mb-6">

            <div class="space-y-5 text-[16px] md:text-[17px]">
                <div class="flex items-baseline">
                    <div class="w-56 md:w-64 font-semibold text-gray-200">Years of experience</div>
                    <div class="px-3 text-gray-300">:</div>
                    <div class="flex-1 text-gray-100">{{ $yearsText }}</div>
                </div>
                <div class="flex items-baseline">
                    <div class="w-56 md:w-64 font-semibold text-gray-200">Specialty</div>
                    <div class="px-3 text-gray-300">:</div>
                    <div class="flex-1 text-gray-100">{{ $specialty }}</div>
                </div>
                <div class="flex items-baseline">
                    <div class="w-56 md:w-64 font-semibold text-gray-200">Location</div>
                    <div class="px-3 text-gray-300">:</div>
                    <div class="flex-1 text-gray-100">{{ $location }}</div>
                </div>
            </div>

            <p class="text-gray-100/90 text-[15.5px] md:text-[16px] leading-7 mt-6">{{ $bio }}</p>

            <hr class="border-white/30 mt-6 mb-4">
            <div>
                <span class="text-sm text-gray-300">Share :</span>
                <div class="mt-4 flex items-center gap-3">
                    <a href="{{ $fbShareUrl }}" target="_blank" rel="noopener" class="btn-share" aria-label="Share on Facebook" title="Share on Facebook"><i class="fab fa-facebook-f text-white"></i></a>
                    <a href="{{ $xShareUrl }}" target="_blank" rel="noopener" class="btn-share" aria-label="Share on X" title="Share on X"><i class="fab fa-x-twitter text-white"></i></a>
                    <button type="button" id="igShareBtn" class="btn-share" aria-label="Share to Instagram" title="Share to Instagram"><i class="fab fa-instagram text-white"></i></button>
                </div>
            </div>
            <hr class="border-white/30 mt-6">
        </div>

        <!-- Booking -->
        <div class="md:sticky md:top-20" id="bookingStart">
            <div class="card booking-card">
                <p class="text-sm text-gray-300">start from</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <div class="price font-extrabold tracking-tight">Rp. {{ number_format($detail->price_per_session ?? 0, 0, ',', '.') }}.-</div>
                    <span class="text-sm text-gray-300">/ session</span>
                </div>
                <hr class="border-white/20">

                <form id="addToCartForm" method="POST" action="{{ route('cart.add.sparring') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="athlete_id" value="{{ $athlete->id }}">

                    <div>
                        <label class="text-sm text-gray-300">Date</label>
                        <div class="field-wrap mt-2">
                            <input id="dateInput" name="date" type="date" class="input-pill pr-12" placeholder="YYYY-MM-DD">
                            <button type="button" id="openDateBtn" class="date-btn" aria-label="Open date picker" title="Pick a date"><i class="far fa-calendar-alt"></i></button>
                        </div>
                    </div>

                    <div id="scheduleContainer">
                        <label class="text-sm text-gray-300">Schedule</label>
                        <div class="grid grid-cols-3 gap-3 mt-3" id="scheduleGrid"></div>
                    </div>

                    <div>
                        <label class="text-sm text-gray-300">Promo code (Optional)</label>
                        <input type="text" name="promo" placeholder="Ex. PROMO70%DAY" class="input-pill mt-2">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold">Add to cart</button>
                </form>
            </div>
        </div>
    </div>

    <!-- CUSTOMER REVIEWS -->
    <div class="max-w-7xl mx-auto px-4 lg:px-6 pb-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Ringkasan -->
            <aside class="reviews-card md:col-span-1">
                <h3 class="text-base">Customer Reviews</h3>
                <hr>
                <div class="rating-row">
                    <div class="rating-stars">
                        @for ($s=1; $s<=5; $s++)
                            <i class="{{ $s <= $fullStars ? 'fas' : 'far' }} fa-star"></i>
                            @endfor
                    </div>
                    <div class="rating-number">{{ $avgText }}</div>
                    <div class="rating-outof">out of&nbsp;5</div>
                </div>

                <div class="mt-3">
                    @for ($i = 5; $i >= 1; $i--)
                    @php
                    $pct = (float)($percents[$i] ?? 0);
                    $cnt = (int)($counts[$i] ?? 0);
                    @endphp
                    <div class="bar-row">
                        <div class="label"><i class="fas fa-star"></i></div>
                        <div class="w-5 text-sm text-gray-300" style="text-align:center;">{{ $i }}</div>
                        <div class="ratebar">
                            <div class="fill" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="count">({{ number_format($cnt, 0, ',', '.') }})</div>
                    </div>
                    @endfor
                </div>
            </aside>

            <!-- Daftar review -->
            <section class="md:col-span-2 space-y-6">
                @foreach ($reviews as $review)
                <article class="review-item bg-gray-900/95 p-6 rounded-lg shadow-md ring-1 ring-black/20 hover:shadow-lg transition-shadow">
                    <header class="review-head">
                        <div class="review-left">
                            <div class="review-avatar">
                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-[18px] leading-tight review-name">{{ $review->user->name }}</p>
                                <p class="text-xs text-gray-400 review-date">{{ $review->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <!-- BARIS RATING -->
                        <div class="review-stars-row">
                            <div class="user-stars">
                                @for ($s=1; $s<=5; $s++)
                                    <i class="{{ $s <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                            </div>
                        </div>
                    </header>

                    <p class="mt-4 text-gray-300">{{ $review->comment }}</p>
                </article>
                @endforeach
            </section>
        </div>
    </div>

    <!-- Cart -->
    <button aria-label="Shopping cart" onclick="showCart()" class="fixed right-6 top-[60%] bg-neutral-700/90 backdrop-blur rounded-full w-16 h-16 flex items-center justify-center shadow-xl ring-1 ring-black/30">
        <i class="fas fa-shopping-cart text-white text-2xl"></i>
        @if ($cartCount > 0)
        <span class="absolute -top-1.5 -right-1.5 bg-blue-600 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">{{ $cartCount }}</span>
        @endif
    </button>

    @include('public.cart')
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== Ratakan judul & kartu booking =====
        function alignTitleAndCard() {
            const mq = window.matchMedia('(min-width: 768px)');
            const crumb = document.getElementById('breadcrumbTop');
            const titleStart = document.getElementById('titleStart');
            const bookingStart = document.getElementById('bookingStart');
            if (!crumb || !titleStart || !bookingStart) return;

            if (mq.matches) {
                const styles = getComputedStyle(crumb);
                const total = crumb.getBoundingClientRect().height + parseFloat(styles.marginBottom || '0');
                titleStart.style.marginTop = total + 'px';
                bookingStart.style.marginTop = total + 'px';
            } else {
                titleStart.style.marginTop = '0px';
                bookingStart.style.marginTop = '0px';
            }
        }
        window.addEventListener('resize', alignTitleAndCard);
        alignTitleAndCard();

        // ===== Elemen DOM =====
        const dateInput = document.getElementById('dateInput');
        const openDateBtn = document.getElementById('openDateBtn');
        const scheduleGrid = document.getElementById('scheduleGrid');

        // Open date picker
        openDateBtn?.addEventListener('click', () => {
            if (dateInput?.showPicker) dateInput.showPicker();
            else dateInput.focus();
        });

        // ===== Jadwal dari controller =====
        const SCHEDULES = @json($schedules);

        function renderSchedules(selectedDate) {
            if (!scheduleGrid) return;
            scheduleGrid.innerHTML = '';

            const slots = SCHEDULES.filter(s => s.date === selectedDate);

            if (!slots.length) {
                scheduleGrid.innerHTML = '<p class="text-gray-400 col-span-full">No available schedules for this date.</p>';
                return;
            }

            slots.forEach(s => {
                const lbl = document.createElement('label');
                lbl.className = 'slot' + (s.is_booked ? ' slot--disabled' : '');
                lbl.dataset.slotId = s.id;
                lbl.innerHTML = `<input type="radio" name="schedule_id" class="hidden" value="${s.id}" ${s.is_booked ? 'disabled' : 'required'}> ${s.start_time}–${s.end_time}`;
                scheduleGrid.appendChild(lbl);
            });
        }

        // Render awal
        const firstDate = @json($availableDates[0] ?? null);
        if (dateInput && firstDate) {
            dateInput.value = firstDate;
            renderSchedules(firstDate);
        }

        // Update jadwal saat tanggal berubah
        dateInput?.addEventListener('change', function() {
            renderSchedules(this.value);
        });

        // Pilih slot
        scheduleGrid?.addEventListener('click', function(e) {
            const label = e.target.closest('label[data-slot-id]');
            if (!label || label.classList.contains('slot--disabled')) return;

            [...scheduleGrid.querySelectorAll('label.slot')].forEach(l => l.classList.remove('slot--active'));
            label.classList.add('slot--active');

            const input = label.querySelector('input[type="radio"]');
            if (input) input.checked = true;
        });

        // Submit form dengan SweetAlert
        document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const date = document.getElementById('dateInput')?.value;
            const schedule = document.querySelector('input[name="schedule_id"]:checked');

            if (!date) {
                Swal.fire({
                    title: 'Oops!',
                    text: 'Silakan pilih tanggal terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    background: '#1E1E1F',
                    color: '#FFFFFF'
                });
                return;
            }

            if (!schedule) {
                Swal.fire({
                    title: 'Oops!',
                    text: 'Silakan pilih jadwal terlebih dahulu.',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    background: '#1E1E1F',
                    color: '#FFFFFF'
                });
                return;
            }

            // Tampilkan loading sebelum fetch
            Swal.fire({
                title: 'Mohon tunggu...',
                text: 'Sedang memproses permintaan Anda.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                background: '#1E1E1F',
                color: '#FFFFFF'
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
                    Swal.close(); // tutup loading
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Sparring ditambahkan ke keranjang',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF',
                            iconColor: '#4BB543'
                        }).then(() => {
                            // Update cart counter
                            const badge = document.querySelector('.fixed.right-6.top-\\[60\\%\\] > span');
                            if (badge) {
                                badge.textContent = data.cartCount;
                                badge.style.display = data.cartCount > 0 ? 'flex' : 'none';
                            }
                            // Reset form
                            this.reset();
                            document.getElementById('scheduleGrid').innerHTML = '';
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Terjadi kesalahan, coba lagi.',
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF'
                        });
                    }
                })
                .catch(err => {
                    Swal.close(); // tutup loading
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan jaringan. Silakan coba beberapa saat lagi.',
                        icon: 'error',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF'
                    });
                });
        });

        // Instagram share
        (function() {
            const igBtn = document.getElementById('igShareBtn');
            if (!igBtn) return;
            const shareData = {
                title: @json($shareText),
                text: @json($shareText),
                url: @json($shareUrlAbs)
            };
            igBtn.addEventListener('click', async () => {
                try {
                    if (navigator.share) await navigator.share(shareData);
                    else if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(`${shareData.text} ${shareData.url}`);
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            timer: 2200,
                            showConfirmButton: false,
                            icon: 'success',
                            title: 'Link disalin. Buka Instagram dan tempel tautannya.'
                        });
                    } else {
                        const t = document.createElement('textarea');
                        t.value = `${shareData.text} ${shareData.url}`;
                        document.body.appendChild(t);
                        t.select();
                        document.execCommand('copy');
                        document.body.removeChild(t);
                        alert('Link disalin. Buka Instagram dan tempel tautannya.');
                    }
                } catch (e) {}
            });
        })();
    });
</script>

@endpush