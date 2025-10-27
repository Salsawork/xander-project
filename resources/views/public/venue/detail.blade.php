@extends('app')
@section('title', 'Venues Page - Xander Billiard')

@push('styles')
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""
/>
<style>
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  :root, html, body{ background:var(--page-bg); }
  html, body{ height:100%; overscroll-behavior: none; touch-action: pan-y; -webkit-text-size-adjust: 100%; }
  #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
  #app, main{ background:var(--page-bg); }
  .scroll-root, .scroll-inner{ overscroll-behavior: contain; background:var(--page-bg); }

  .card{background:#171717;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  .booking-card{padding:25px;border-radius:12px}
  .booking-card hr{margin-top:8px;margin-bottom:14px}
  .booking-card .price{font-size:20px;line-height:1}
  .booking-card p,.booking-card label,.booking-card span{font-size:12px}
  .booking-card button[type="submit"]{height:42px;font-size:15px;border-radius:10px}

  .input-pill{width:100%;padding:.80rem 2.75rem .80rem .9rem;border-radius:12px;background:#222222;color:#fff;border:1.5px solid rgba(255,255,255,.2);outline:none;appearance:none;font-size:13px}
  .input-pill:focus{box-shadow:0 0 0 2px #3b82f6;border-color:#3b82f6}
  input[type="date"]::-webkit-calendar-picker-indicator{opacity:0;pointer-events:none;position:absolute;right:0;top:0;width:42px;height:100%;}
  input[type="date"]::-webkit-inner-spin-button,input[type="date"]::-webkit-clear-button{display:none}
  input[type="date"]{-moz-appearance:textfield;-webkit-appearance:none;color-scheme:dark;pointer-events:none;}

  .slot{display:flex;align-items:center;justify-content:center;padding:.38rem .5rem;border-radius:9px;font-weight:800;background:#2a2a2a;color:#fff;border:1.5px solid rgba(255,255,255,.15);transition:.18s;cursor:pointer;user-select:none;font-size:.88rem}
  .slot:hover{background:#3b82f6;border-color:#3b82f6}
  .slot--active{background:#3b82f6;border-color:#3b82f6}
  .slot--disabled{background:#6b7280;color:#d1d5db;border-color:transparent;cursor:not-allowed}

  label:has(input[type="radio"]:checked){ background-color:#2563eb; border-color:#2563eb; color:white; }

  .reviews-card{background:#171717;border-radius:14px;padding:18px 16px;box-shadow:0 10px 30px rgba(0,0,0,.35);width:100%}
  .reviews-card h3{font-weight:700}
  .reviews-card hr{border-color:rgba(255,255,255,.12);margin:8px 0 14px}

  .rating-row{ display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }
  .rating-stars{ display:inline-flex; gap:6px; white-space:nowrap; line-height:1; flex:0 0 auto; }
  .rating-stars i{ font-size:22px; color:#fbbf24; }
  .rating-number{ font-size:28px; font-weight:800; letter-spacing:.5px; flex:0 0 auto; }
  .rating-outof{ font-size:12px; color:#9ca3af; margin-left:.35rem; line-height:1.2; flex:0 0 auto; }
  .bar-row{display:flex;align-items:center;gap:.6rem;margin-top:.55rem}
  .bar-row .label{width:18px;color:#fbbf24;text-align:center;line-height:1}
  .bar-row .ratebar{flex:1;height:10px;background:#2a2a2a;border-radius:9999px;overflow:hidden}
  .bar-row .ratebar .fill{height:100%;background:#e5e7eb;border-radius:9999px}
  .bar-row .count{width:70px;text-align:right;font-size:12px;color:#9ca3af}

  .review-item{--avatar:48px;--gap:16px;--indent:calc(var(--avatar) + var(--gap));position:relative;padding:22px;background:#171717;border-radius:14px}
  .review-item::before{content:"";position:absolute;left:var(--indent);right:0;top:0;height:1px;background:rgba(255,255,255,.08)}
  .review-head{position:relative}
  .review-left{display:flex;align-items:center;gap:16px}
  .review-avatar{width:var(--avatar);height:var(--avatar);border-radius:9999px;background:#2f2f2f;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;overflow:hidden}
  .review-stars-row{position:absolute;left:var(--indent);right:0;top:2px;display:flex;justify-content:flex-end;pointer-events:none}
  .user-stars i{font-size:26px;color:#e5e7eb}

  .create-card{background:#1f1f1f;border-radius:14px;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  .stars-input i{font-size:22px;color:#6b7280;cursor:pointer;transition:transform .06s ease}
  .stars-input i.active{color:#f5c518}
  .helper{font-size:.8rem;color:#9ca3af}

  @media (max-width:640px){
    .review-item{--avatar:40px;--gap:12px;padding:16px 14px 14px}
    .review-left{gap:12px}
    .review-avatar{font-size:16px}
    .review-name{font-size:15px;line-height:1.2}
    .review-date{font-size:11px}
    .review-head .review-stars-row{position:static;margin-top:4px;justify-content:flex-start;pointer-events:none}
    .rating-row{ gap:.6rem; }
    .rating-stars i{ font-size:20px; }
    .rating-number{ font-size:26px; }
    .rating-outof{ flex:1 1 100%; order:3; margin-left:0; margin-top:2px; text-align:left; font-size:12px; }
  }
  @media (max-width:380px){ .rating-stars i{font-size:18px} .rating-number{font-size:24px} }
  @media (min-width:768px){ #createReviewCard{ margin-left:-8px; } }

  .leaflet-map{ height:260px; border-radius:12px; overflow:hidden; border:1px solid #3a3a3a; }
  .muted{ color:#9ca3af; }
</style>
@endpush

@php
  use Illuminate\Support\Facades\File;

  $cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);

  // Resolusi gambar: FE link -> CMS -> storage -> placeholder
  $venueImgUrl = function (?string $pathLike) {
      $pathLike = $pathLike ? trim($pathLike) : '';
      if ($pathLike === '') return asset('images/placeholder/venue.png');
      if (preg_match('~^https?://~i', $pathLike) || str_starts_with($pathLike, '/')) return $pathLike;

      $filename = basename($pathLike);

      $feAbs  = base_path('../demo-xanders/images/venue/' . $filename);
      $feLink = public_path('fe-venue');
      if (File::exists($feAbs) && is_dir($feLink)) return asset('fe-venue/' . $filename);

      $cmsAbs = public_path('images/venue/' . $filename);
      if (File::exists($cmsAbs)) return asset('images/venue/' . $filename);

      $storAbs = public_path('storage/uploads/' . $filename);
      if (File::exists($storAbs)) return asset('storage/uploads/' . $filename);

      return asset('images/placeholder/venue.png');
  };

  $rawImages = [];
  if (!empty($detail->images)) {
      $rawImages = is_array($detail->images)
          ? $detail->images
          : (is_string($detail->images) ? (json_decode($detail->images, true) ?? []) : []);
  }
  if (empty($rawImages) && !empty($detail->image)) $rawImages = [$detail->image];

  $resolvedImages = collect($rawImages)->filter()->map(fn($x) => $venueImgUrl($x))->values()->all();
  if (!$resolvedImages) $resolvedImages = [asset('images/placeholder/venue.png')];

  $mainImage = $resolvedImages[0];
  $thumbs = array_slice($resolvedImages, 1, 2);
  while (count($thumbs) < 2) { $thumbs[] = asset('images/placeholder/venue.png'); }

  $avgText   = number_format((float)($averageRating ?? 0), 1, ',', '.');
  $fullStars = floor((float)($averageRating ?? 0));

  $lat = (float) ($detail->latitude ?? 0);
  $lng = (float) ($detail->longitude ?? 0);
  $hasCoords = ($lat !== 0.0 || $lng !== 0.0);

  // Facilities dari DB bila ada
  $facilities = [];
  if (is_array($detail->facilities))        $facilities = $detail->facilities;
  elseif (is_string($detail->facilities))   $facilities = json_decode($detail->facilities, true) ?? [];
  $facilities = array_values(array_filter(array_map(fn($x)=>trim((string)$x), $facilities)));
@endphp

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<script>
  (function(){ const setSVH=()=>document.documentElement.style.setProperty('--svh',(window.innerHeight*0.01)+'px'); setSVH(); window.addEventListener('resize',setSVH); })();
</script>

<div class="min-h-screen px-6 md:px-20 py-10 bg-neutral-900 text-white scroll-root">
  <div class="container mx-auto space-y-10 scroll-inner">
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
          <div class="col-span-2">
            <img id="mainImage"
                 src="{{ $mainImage }}"
                 alt="{{ $detail->name }}"
                 class="rounded-lg w-full h-[300px] md:h-[360px] object-cover"
                 onerror="this.onerror=null;this.src='{{ asset('images/placeholder/venue.png') }}';" />
          </div>
          <div class="flex flex-col gap-4">
            @foreach ($thumbs as $t)
              <img src="{{ $t }}"
                   alt="Thumbnail {{ $loop->iteration }} - {{ $detail->name }}"
                   class="rounded-lg w-full h-[140px] md:h-[170px] object-cover cursor-pointer"
                   loading="lazy"
                   onclick="changeMainImage('{{ $t }}')"
                   onerror="this.onerror=null;this.src='{{ asset('images/placeholder/venue.png') }}';" />
            @endforeach
          </div>
        </div>

        {{-- Info Venue --}}
        <div class="space-y-6">
          <div>
            <h1 class="text-2xl font-extrabold">{{ $detail->name }}</h1>
            <p class="text-gray-300">{{ $detail->address ?? 'Alamat belum tersedia' }}</p>
          </div>
          <hr class="border-gray-400">
          <div>
            <h2 class="font-semibold mb-2">Facilities</h2>
            @if($facilities)
              <ul class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm text-gray-300">
                @foreach($facilities as $f) <li>• {{ $f }}</li> @endforeach
              </ul>
            @else
              <p class="text-gray-400 text-sm">Belum ada data fasilitas.</p>
            @endif
          </div>
          <hr class="border-gray-400">

          {{-- Location --}}
          <div>
            <h2 class="font-semibold mb-2">Location</h2>
            <div class="text-sm muted mb-2 flex items-start gap-2">
              <i class="fas fa-map-marker-alt mt-0.5"></i>
              <span>{{ $detail->address ?? 'No address available' }}</span>
            </div>
            <div id="mapDetail" class="leaflet-map"></div>
            <p id="mapInfo" class="text-xs muted mt-2"></p>
          </div>
        </div>

        {{-- Reviews --}}
        <div id="reviewsStart" class="max-w-7xl mx-auto px-0 lg:px-0 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <aside class="reviews-card">
              <h3 id="reviewsAnchor" class="text-base">Customer Reviews</h3>
              <hr>
              <div class="rating-row">
                <div class="rating-stars">
                  @for ($s=1;$s<=5;$s++)
                    <i class="{{ $s <= $fullStars ? 'fas' : 'far' }} fa-star"></i>
                  @endfor
                </div>
                <div class="rating-number">{{ $avgText }}</div>
                <div class="rating-outof">out of 5</div>
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
                    <div class="ratebar"><div class="fill" style="width: {{ $pct }}%"></div></div>
                    <div class="count">({{ number_format($cnt, 0, ',', '.') }})</div>
                  </div>
                @endfor
              </div>
            </aside>

            <section class="md:col-span-2 space-y-6">
              @forelse ($reviews as $review)
                <article class="review-item shadow-md ring-1 ring-black/20 hover:shadow-lg transition-shadow">
                  <header class="review-head">
                    <div class="review-left">
                      <div class="review-avatar">
                        {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                      </div>
                      <div>
                        <p class="font-semibold text-[18px] leading-tight review-name">{{ $review->user->name ?? 'User' }}</p>
                        <p class="text-xs text-gray-400 review-date">{{ \Carbon\Carbon::parse($review->created_at)->format('d/m/Y') }}</p>
                      </div>
                    </div>
                    <div class="review-stars-row">
                      <div class="user-stars">
                        @for ($s=1;$s<=5;$s++)
                          <i class="{{ $s <= (int)$review->rating ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                      </div>
                    </div>
                  </header>
                  @if($review->comment)
                    <p class="mt-4 text-gray-300">{{ $review->comment }}</p>
                  @endif
                </article>
              @empty
                <div class="reviews-card text-gray-300">Belum ada ulasan untuk venue ini.</div>
              @endforelse
            </section>
          </div>
        </div>
      </div>

      {{-- RIGHT: Booking --}}
      <div class="space-y-6" id="rightCol">
        <div class="card booking-card">
          <p class="text-sm text-gray-300">start from</p>
          <div class="flex items-baseline gap-2 mt-1">
            <div class="price font-extrabold tracking-tight" id="priceDisplay">Rp {{ number_format($minPrice, 0, ',', '.') }}</div>
          </div>
          <hr class="border-white/20">

          <form id="addToCartForm" action="{{ route('cart.add.venue') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="id" value="{{ $detail->id }}">

            {{-- Hidden diisi otomatis sebelum submit --}}
            <input type="hidden" name="start" id="startInput">
            <input type="hidden" name="end" id="endInput">
            <input type="hidden" name="price" id="priceInput">
            <input type="hidden" name="table_number" id="tableNumberInput">

            <div>
              <label class="text-sm text-gray-300">Date</label>
              <div class="field-wrap mt-2 relative">
                <input id="datePicker" name="date" type="date" class="input-pill pr-12" placeholder="YYYY-MM-DD" autocomplete="off">
                <button type="button" id="openDateBtn" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white" aria-label="Open date picker" title="Pick a date">
                  <i class="far fa-calendar-alt"></i>
                </button>
              </div>
            </div>

            <div id="scheduleContainer">
              <label class="text-sm text-gray-300">Schedule</label>
              <div id="scheduleList" class="grid grid-cols-3 gap-3 mt-3"></div>
            </div>

            <div id="tableContainer">
              <label class="text-sm text-gray-300">Table</label>
              <div id="tableList" class="grid grid-cols-3 gap-3 mt-3"></div>
            </div>

            <div>
              <label class="text-sm text-gray-300">Promo code (Optional)</label>
              <input type="text" name="code_promo" id="codePromoInput" placeholder="Ex. PROMO70%DAY" class="input-pill mt-2">
            </div>

            <button type="button"
              id="addToCartButton" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-md font-medium text-sm">
              <i class="fas fa-shopping-cart mr-2"></i>
              Add to cart
            </button>
          </form>
        </div>

        <div class="bg-neutral-800 p-5 rounded-lg text-sm text-gray-300">
          <h3 class="font-semibold mb-2">Terms & Conditions</h3>
          <p class="mb-2">Guests are expected to follow all venue rules and staff instructions.</p>
          <p class="mb-2">Any damage due to negligence is guest responsibility.</p>
          <p class="mb-2">Outside food and beverages are not permitted unless explicitly allowed.</p>
          <p>Disruptive behavior may result in removal without refund.</p>
        </div>

        {{-- CREATE REVIEW --}}
        <div id="createReviewCard" class="create-card text-sm text-gray-300">
          <h3 class="text-base font-semibold text-white">Buat Review</h3>

          @auth
            @if (!$userHasBooking)
              <div class="mt-3">Kamu belum memiliki booking di venue ini, jadi belum bisa membuat review.</div>
            @elseif ($alreadyReviewed)
              <div class="mt-3">Kamu sudah memberi review untuk venue ini. Terima kasih! 🙌</div>
            @else
              @if (session('success'))
                <div class="mt-3 rounded-md bg-green-600/20 text-green-300 px-3 py-2 border border-green-600/30">
                  {{ session('success') }}
                </div>
              @endif
              @if (session('error'))
                <div class="mt-3 rounded-md bg-red-600/20 text-red-300 px-3 py-2 border border-green-600/30">
                  {{ $errors->first() }}
                </div>
              @endif

              <form action="{{ route('venues.reviews.store', ['venue' => $detail->id]) }}" method="POST" class="mt-3" id="reviewForm">
                @csrf
                <label class="block text-gray-300 mb-2">Rating</label>
                <div class="flex items-center gap-2 stars-input mb-3" id="ratingBox">
                  @for ($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star" data-value="{{ $i }}"></i>
                  @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', 0) }}">

                <label class="block text-gray-300 mb-2">Komentar</label>
                <textarea name="comment" rows="5" class="w-full rounded-md bg-[#151515] border border-neutral-700 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Tulis pengalamanmu di venue ini...">{{ old('comment') }}</textarea>

                <p class="helper mt-2">Gunakan bahasa yang sopan. Reviewmu membantu user lain 😊</p>

                <button type="submit" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm py-2.5 rounded-md">Kirim Review</button>
              </form>
            @endif
          @else
            <div class="mt-3">Kamu harus login untuk membuat review.</div>
            <a href="{{ route('login') }}" class="inline-flex items-center mt-3 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-md">Login Sekarang</a>
          @endauth
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
        <span id="cartCountBadge" class="absolute top-1 right-1 bg-blue-600 text-white text-xs font-semibold rounded-full w-5 h-5 flex items-center justify-center">
          {{ $cartCount }}
        </span>
      @else
        <span id="cartCountBadge" class="hidden"></span>
      @endif
    </button>
  @endif

  @include('public.cart')
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const isLoggedIn    = @json(auth()->check());
  const userRole      = @json(Auth::check() ? Auth::user()->roles : null);
  const venueId       = @json($detail->id);
  const baseVenuesUrl = @json(url('/venues'));
  const addVenueUrl   = @json(route('cart.add.venue'));
  const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  const hasCoordsDb   = @json($hasCoords);
  const latDb         = parseFloat(@json($lat));
  const lngDb         = parseFloat(@json($lng));
  const venueName     = {!! json_encode($detail->name) !!};
  const venueAddress  = {!! json_encode($detail->address ?? '') !!};

  function changeMainImage(src){ document.getElementById('mainImage').src = src; }

  function updateCartBadge(n){
    const badge = document.getElementById('cartCountBadge');
    if (!badge) return;
    const num = Number(n||0);
    if (num > 0){
      badge.textContent = num;
      badge.classList.remove('hidden');
      badge.classList.add('flex');
    } else {
      badge.classList.add('hidden');
    }
  }

  document.addEventListener("DOMContentLoaded", function() {
    /* ====== MAP (Leaflet + Photon) ====== */
    const mapInfoEl = document.getElementById('mapInfo');
    function initMap(lat, lng){
      const m = L.map('mapDetail', { zoomControl:true, scrollWheelZoom:true }).setView([lat, lng], 16);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom:19, attribution:'&copy; OpenStreetMap'
      }).addTo(m);
      L.marker([lat,lng]).addTo(m).bindPopup(venueName);
      return m;
    }
    if (hasCoordsDb) { initMap(latDb, lngDb); mapInfoEl.textContent = ''; }
    else if (venueAddress) {
      mapInfoEl.textContent = 'Mencari lokasi dari alamat…';
      fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(venueAddress)}&limit=1`)
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(json => {
          const f = (json && json.features && json.features[0]) ? json.features[0] : null;
          if (!f) throw new Error('Alamat tidak ditemukan');
          const [lng, lat] = f.geometry.coordinates;
          initMap(lat, lng); mapInfoEl.textContent = '';
        })
        .catch(() => { mapInfoEl.textContent = 'Gagal memuat peta dari alamat. Menampilkan area default.'; initMap(-6.2, 106.816666); });
    } else { mapInfoEl.textContent = 'Alamat belum tersedia. Menampilkan area default.'; initMap(-6.2, 106.816666); }

    /* ====== BOOKING & CART ====== */
    const datePicker   = document.getElementById('datePicker');
    const openDateBtn  = document.getElementById('openDateBtn');
    const addBtn       = document.getElementById('addToCartButton');
    const scheduleList = document.getElementById("scheduleList");
    const tableList    = document.getElementById("tableList");
    const form         = document.getElementById("addToCartForm");
    const priceDisplay = document.getElementById("priceDisplay");

    let selectedSchedule = null;
    let selectedTableNumber = null;

    if (openDateBtn && datePicker) openDateBtn.addEventListener('click', () => { datePicker.showPicker(); });

    function createScheduleSlot(slot, price) {
      const lbl = document.createElement("label");
      const isBooked = !!slot.is_booked;
      lbl.className = `slot${isBooked ? ' slot--disabled' : ''}`;
      lbl.innerHTML = `
        <input type="radio" name="schedule" value="${slot.start}-${slot.end}"
               class="hidden" required ${isBooked ? 'disabled' : ''}>
        ${slot.start} - ${slot.end}
      `;
      if (!isBooked) {
        const radio = lbl.querySelector("input");
        radio.addEventListener("change", () => {
          document.querySelectorAll('.slot').forEach(s => s.classList.remove('slot--active'));
          lbl.classList.add('slot--active');
          selectedSchedule = { start: slot.start, end: slot.end, price: price, tables: slot.tables || [] };
          if (priceDisplay) {
            priceDisplay.innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
          }
          renderTables(slot.tables || []);
        });
      }
      return lbl;
    }

    async function loadSchedules(selectedDate) {
      if (!selectedDate || !scheduleList) return;
      scheduleList.innerHTML = `<p class="text-gray-400 text-sm">Loading schedules...</p>`;
      tableList.innerHTML = "";
      selectedSchedule = null;
      selectedTableNumber = null;

      try {
        const response = await fetch(`${baseVenuesUrl}/${encodeURIComponent(venueId)}/price-schedules?date=${encodeURIComponent(selectedDate)}`);
        if (!response.ok) throw new Error('Network response was not ok');
        const data = await response.json();
        scheduleList.innerHTML = "";
        const schedules = data.schedules || [];
        if (schedules.length === 0) {
          scheduleList.innerHTML = `<p class="text-gray-400 text-sm">No schedules available for this date.</p>`;
          return;
        }
        schedules.forEach(sch => {
          (sch.schedule || []).forEach(slot => scheduleList.appendChild(createScheduleSlot(slot, sch.price)));
        });
      } catch (error) {
        console.error('Error loading schedules:', error);
        scheduleList.innerHTML = `<p class="text-red-400 text-sm">Failed to load schedules. Please try again.</p>`;
      }
    }

    function renderTables(tables = []) {
      tableList.innerHTML = "";
      if (!tables || tables.length === 0) {
        tableList.innerHTML = `<p class="text-gray-400 text-sm">No tables available.</p>`;
        return;
      }
      tables.forEach(tbl => {
        const lbl = document.createElement("label");
        const disabledClass = tbl.is_booked ? 'opacity-40 pointer-events-none bg-gray-700' : '';
        lbl.className = `slot ${disabledClass}`;
        lbl.innerHTML = `
          <input type="radio" name="table_id" value="${tbl.id}" class="hidden" ${tbl.is_booked ? 'disabled' : ''}>
          ${tbl.name || ("Table " + tbl.id)}
        `;
        const radio = lbl.querySelector("input");
        radio.addEventListener("change", () => {
          selectedTableNumber = tbl.name || ('Table ' + tbl.id);
        });
        tableList.appendChild(lbl);
      });
    }

    if (datePicker) {
      datePicker.addEventListener("change", function() { loadSchedules(this.value); });
      // init to today
      const t = new Date();
      const yyyy = t.getFullYear(); const mm = String(t.getMonth() + 1).padStart(2, '0'); const dd = String(t.getDate()).padStart(2, '0');
      const todayStr = `${yyyy}-${mm}-${dd}`;
      datePicker.min = todayStr; datePicker.value = todayStr;
      loadSchedules(todayStr);
    }

    // Visual stars input
    const stars = document.querySelectorAll('#ratingBox i');
    const ratingInput = document.getElementById('ratingInput');
    stars.forEach(st => {
      st.addEventListener('click', () => {
        const v = parseInt(st.dataset.value, 10);
        ratingInput.value = v;
        stars.forEach(s2 => s2.classList.toggle('active', parseInt(s2.dataset.value,10) <= v));
      });
    });

    // Button (pre-validate then submit)
    if (addBtn) {
      addBtn.addEventListener('click', () => {
        if (!isLoggedIn) {
          Swal.fire({ title:'Belum Login!', text:'Silakan login terlebih dahulu untuk menambahkan ke keranjang.', icon:'warning', confirmButtonText:'Login Sekarang', confirmButtonColor:'#3085d6', background:'#1E1E1F', color:'#FFFFFF' }).then(() => { window.location.href = '/login'; });
          return;
        }
        if (userRole !== 'user') {
          Swal.fire({ title:'Akses Ditolak!', text:'Hanya user yang bisa menambahkan ke keranjang.', icon:'error', confirmButtonColor:'#3085d6', background:'#1E1E1F', color:'#FFFFFF' });
          return;
        }

        const date = datePicker?.value;
        const schedule = document.querySelector('input[name="schedule"]:checked');
        const table = document.querySelector('input[name="table_id"]:checked');

        if (!date)    { Swal.fire({ title:'Oops!', text:'Silakan pilih tanggal terlebih dahulu.', icon:'warning', background:'#1E1E1F', color:'#FFFFFF' }); return; }
        if (!schedule || !selectedSchedule) { Swal.fire({ title:'Oops!', text:'Silakan pilih jadwal terlebih dahulu.', icon:'warning', background:'#1E1E1F', color:'#FFFFFF' }); return; }
        if (!table)   { Swal.fire({ title:'Oops!', text:'Silakan pilih meja terlebih dahulu.', icon:'warning', background:'#1E1E1F', color:'#FFFFFF' }); return; }

        // Isi hidden untuk fallback submit (kalau AJAX gagal)
        document.getElementById('priceInput').value       = (selectedSchedule?.price ?? 0);
        document.getElementById('tableNumberInput').value = (selectedTableNumber ?? '');
        document.getElementById('startInput').value       = selectedSchedule.start;
        document.getElementById('endInput').value         = selectedSchedule.end;

        form.requestSubmit();
      });
    }

    // Hijack form submit -> AJAX post seperti product detail
    if (form) {
      form.addEventListener('submit', function(e){
        e.preventDefault();

        const date    = datePicker?.value;
        const table   = document.querySelector('input[name="table_id"]:checked');

        if (!isLoggedIn) { Swal.fire({ title:'Belum Login!', text:'Silakan login terlebih dahulu.', icon:'warning' }).then(()=>location.href='/login'); return; }
        if (!date || !selectedSchedule || !table) { Swal.fire({ title:'Oops!', text:'Lengkapi tanggal, jadwal, dan meja.', icon:'warning' }); return; }

        // Build payload (dukung 2 skema: hidden-fields & field langsung)
        const fd = new FormData(form);
        fd.set('start', selectedSchedule.start);
        fd.set('end',   selectedSchedule.end);
        fd.set('price', selectedSchedule.price ?? 0);
        fd.set('table_number', selectedTableNumber ?? '');
        fd.set('table_id', String(parseInt(table.value, 10)));
        // optional extra kompatibilitas:
        fd.set('schedule[start]', selectedSchedule.start);
        fd.set('schedule[end]',   selectedSchedule.end);
        fd.set('table',           selectedTableNumber ?? '');

        Swal.fire({ title:'Memproses…', allowOutsideClick:false, didOpen:()=>Swal.showLoading(), background:'#1E1E1F', color:'#FFFFFF' });

        fetch(addVenueUrl, {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
          body: fd
        })
        .then(async (res) => {
          let json = null;
          try { json = await res.json(); } catch(_) {}
          return { ok: res.ok, json };
        })
        .then(({ ok, json }) => {
          Swal.close();
          const success = !!(json && (json.success || json.ok));
          if (ok && success) {
            updateCartBadge(json.cart_count ?? null);
            Swal.fire({ icon:'success', title:'Berhasil!', text: json.message || 'Ditambahkan ke cart.', showConfirmButton:false, timer:1300, background:'#1E1E1F', color:'#FFFFFF' });
            setTimeout(() => { if (typeof showCart === 'function') showCart(); }, 200);
          } else {
            const msg = (json && (json.message || json.error)) || 'Terjadi kesalahan, coba lagi.';
            Swal.fire({ icon:'error', title:'Gagal!', text: msg, background:'#1E1E1F', color:'#FFFFFF' });
          }
        })
        .catch((err) => {
          console.error(err);
          Swal.close();
          // fallback kirim normal
          form.submit();
        });
      });
    }

    // Align create review card
    function alignCreateReview() {
      const mq = window.matchMedia('(min-width: 768px)');
      const anchor = document.getElementById('reviewsAnchor');
      const createCard = document.getElementById('createReviewCard');
      if (!anchor || !createCard) return;
      if (!mq.matches) { createCard.style.marginTop = ''; return; }
      const extraDown = 14;
      const anchorTop = anchor.getBoundingClientRect().top + window.scrollY;
      const cardTop   = createCard.getBoundingClientRect().top + window.scrollY;
      const delta = anchorTop - cardTop;
      createCard.style.marginTop = ((delta>0?delta:0)+extraDown) + 'px';
    }
    window.addEventListener('resize', alignCreateReview);
    window.addEventListener('load', alignCreateReview);
    setTimeout(alignCreateReview, 250);
    alignCreateReview();
  });
</script>
@endpush
