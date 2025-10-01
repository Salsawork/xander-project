@extends('app')
@section('title', 'Venues Page - Xander Billiard')

@php
    $cartProducts  = json_decode(request()->cookie('cartProducts') ?? '[]', true);
    $cartVenues    = json_decode(request()->cookie('cartVenues') ?? '[]', true);
    $cartSparrings = json_decode(request()->cookie('cartSparrings') ?? '[]', true);
    $cartCount     = count($cartProducts) + count($cartVenues) + count($cartSparrings);
@endphp

@push('styles')
<style>
  /* ===== Dark base + anti white overscroll ===== */
  :root { color-scheme: dark; }
  html, body{
    height:100%;
    background-color:#0a0a0a;
    overscroll-behavior-y:none;
  }
  #app, main{ background-color:#0a0a0a; }
  body::before{ content:""; position:fixed; inset:0; background:#0a0a0a; pointer-events:none; z-index:-1; }
  body{ -webkit-overflow-scrolling:touch; touch-action:pan-y; }
  img{ color:transparent; }

  /* ===== Mobile-only tweaks (≤640px). Desktop tidak diubah. ===== */
  @media (max-width: 640px){
    /* Grid gallery jadi 1 kolom: gambar utama lalu deretan thumbnail */
    .gallery-grid{ display:grid; grid-template-columns:1fr !important; gap:12px !important; }
    .venue-main-img{ height:min(56vw, 320px) !important; }
    .gallery-thumbs{
      display:flex !important; flex-direction:row !important; gap:10px !important;
      overflow-x:auto; padding-bottom:6px; -webkit-overflow-scrolling:touch; scrollbar-width:none;
    }
    .gallery-thumbs::-webkit-scrollbar{ display:none; }
    .gallery-thumbs img{ width:96px; height:96px; flex:0 0 auto; border-radius:10px; object-fit:cover; }
    .thumb-active{ outline:2px solid #3b82f6; outline-offset:0; }

    /* Info venue lebih ringkas */
    .venue-title{ font-size:1.25rem; } /* text-xl */
    .venue-address{ font-size:.9rem; }

    /* Schedule 2 kolom di HP (3 kolom tetap untuk ≥sm) */
    .schedule-grid{ grid-template-columns:repeat(2,minmax(0,1fr)) !important; }

    /* Map lebih proporsional di HP */
    .map-embed{ height:200px !important; }

    /* Floating cart di pojok bawah kanan supaya tidak menutupi konten */
    .float-cart{
      top:auto !important; right:16px; bottom:16px; width:56px !important; height:56px !important;
    }
  }
</style>
@endpush

@section('content')
  <main
    class="min-h-screen px-6 md:px-20 py-10 bg-neutral-900 text-white"
    style="
      background-image: url('{{ asset('images/bg/background_2.png') }}');
      background-size: cover; background-position: center; background-repeat: no-repeat;
    "
  >
    <div class="container mx-auto space-y-10">
      <nav class="text-xs text-gray-400 mb-4">
        <a href="{{ route('index') }}">Home</a> /
        <a href="{{ route('venues.index') }}">Venue</a> /
        <span class="text-white">{{ $detail->name }}</span>
      </nav>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-2 space-y-6">
          {{-- Gallery --}}
          <div class="grid grid-cols-3 gap-4 gallery-grid">
            @php
                $mainImagePath = 'https://placehold.co/800x510?text=No+Image';
                if (!empty($detail->images) && is_array($detail->images) && !empty($detail->images[0])) {
                    $img = $detail->images[0];
                    if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://') && !str_starts_with($img, '/storage/')) {
                        $mainImagePath = asset('storage/uploads/' . $img);
                    } else {
                        $mainImagePath = $img;
                    }
                }
            @endphp

            <div class="col-span-2">
              <img id="mainImage"
                   src="{{ $mainImagePath }}"
                   alt="{{ $detail->name }}"
                   class="rounded-lg w-full h-[300px] object-cover bg-neutral-800 venue-main-img"
                   onerror="this.src='https://placehold.co/800x510?text=No+Image'"/>
            </div>

            <div class="flex flex-col gap-4 gallery-thumbs" id="thumbs">
              <img src="https://placehold.co/400x250?text=Img+1"
                   class="rounded-lg w-full h-[250px] object-cover cursor-pointer bg-neutral-800"
                   onclick="changeMainImage('https://placehold.co/800x500?text=Img+1', this)" />
              <img src="https://placehold.co/400x250?text=Img+2"
                   class="rounded-lg w-full h-[250px] object-cover cursor-pointer bg-neutral-800"
                   onclick="changeMainImage('https://placehold.co/800x500?text=Img+2', this)" />
            </div>
          </div>

          {{-- Venue Info --}}
          <div class="space-y-6">
            <div>
              <h1 class="text-2xl font-extrabold venue-title">{{ $detail->name }}</h1>
              <p class="text-gray-300 venue-address">{{ $detail->address ?? 'Jakarta Pusat' }}</p>
            </div>

            <hr class="border-gray-700">

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

            <hr class="border-gray-700">

            {{-- Location --}}
            <div>
              <h2 class="font-semibold mb-2">Location</h2>
              <a href="https://maps.google.com" target="_blank" class="text-blue-400 underline text-sm">
                Jl. MH Thamrin No. 45, Menteng, Jakarta Pusat, DKI Jakarta 10350
              </a>
              <div class="mt-3">
                <iframe
                  class="map-embed"
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
                                <select name="date" id="dateSelect"
                                    class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500"
                                    required>
                                    <option value="">-- Select Date --</option>
                                    @foreach ($availableDates as $date)
                                        <option value="{{ $date }}">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</option>
                                    @endforeach
                                </select>
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

    <!-- Floating Cart -->
    <button
      aria-label="Shopping cart with {{ $cartCount }} items"
      onclick="showCart()"
      class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg z-[60] float-cart"
    >
      <i class="fas fa-shopping-cart text-white text-3xl"></i>
      @if ($cartCount > 0)
        <span class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
          {{ $cartCount }}
        </span>
      @endif
    </button>

    {{-- Cart Sidebar / Drawer + fungsi showCart() --}}
    @include('public.cart')
  </main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Ganti gambar utama dari thumbnail/dummy + highlight aktif
  function changeMainImage(url, el) {
    const target = document.getElementById('mainImage');
    if (target) target.src = url;

    // aktifkan highlight di mobile thumbs (jika ada)
    const thumbs = document.getElementById('thumbs');
    if (thumbs) {
      thumbs.querySelectorAll('img').forEach(img => img.classList.remove('thumb-active'));
    }
    if (el) el.classList.add('thumb-active');
  }

  // SweetAlert konfirmasi add to cart
  const form = document.getElementById('addToCartForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Berhasil!',
        text: 'Venue ditambahkan ke keranjang',
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6',
        background: '#1E1E1F',
        color: '#FFFFFF',
        iconColor: '#4BB543'
      }).then(() => this.submit());
    });
  }
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
                    const slot = `${schedule.start_time} - ${schedule.end_time}`;
    
                    const label = document.createElement('label');
                    label.className =
                        'border border-gray-600 rounded text-center py-2 text-sm cursor-pointer hover:bg-blue-600 hover:border-blue-600';
                    label.innerHTML = `<input type="radio" name="schedule_id" value="${schedule.id}" class="hidden" required>${slot}`;
                    scheduleGrid.appendChild(label);
                });
            }
    
            scheduleContainer.classList.remove('hidden');
        });
    </script>
    
@endpush
@push('styles')
    <style>
        label:has(input[type="radio"]:checked) {
        background-color: #2563eb;
        border-color: #2563eb;
        color: white;
        }
    </style>
@endpush
