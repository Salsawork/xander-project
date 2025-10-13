@extends('app')
@section('title', 'Venues Page - Xander Billiard')

@push('styles')
<style>
  /* ====== GLOBAL BG ====== */
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  :root, html, body{ background:var(--page-bg); }
  html, body{ height:100%; overscroll-behavior: none; touch-action: pan-y; -webkit-text-size-adjust: 100%; }
  #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
  #app, main{ background:var(--page-bg); }
  .scroll-root, .scroll-inner{ overscroll-behavior: contain; background:var(--page-bg); }

  label:has(input[type="radio"]:checked){ background-color:#2563eb; border-color:#2563eb; color:white; }

  /* ====== Reviews (mirip sparring) ====== */
  .reviews-card{background:#171717;border-radius:14px;padding:18px 16px;box-shadow:0 10px 30px rgba(0,0,0,.35);width:100%}
  .reviews-card h3{font-weight:700}
  .reviews-card hr{border-color:rgba(255,255,255,.12);margin:8px 0 14px}
  .rating-row{display:flex;align-items:center;gap:.75rem}
  .rating-stars i{font-size:22px;color:#fbbf24}
  .rating-number{font-size:28px;font-weight:800;letter-spacing:.5px}
  .rating-outof{font-size:12px;color:#9ca3af;margin-left:.35rem}
  .bar-row{display:flex;align-items:center;gap:.6rem;margin-top:.55rem}
  .bar-row .label{width:18px;color:#fbbf24;text-align:center}
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

  /* Card form review di kanan */
  .create-card{background:#1f1f1f;border-radius:14px;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  .stars-input i{font-size:22px;color:#6b7280;cursor:pointer;transition:transform .06s ease}
  .stars-input i.active{color:#f5c518}
  .helper{font-size:.8rem;color:#9ca3af}

  @media (max-width:640px){
    .review-item{--avatar:40px;--gap:12px;padding:16px 14px 14px}
    .review-left{gap:12px}
    .review-avatar{font-size:16px}
    .review-name{font-size:15px!important;line-height:1.2}
    .review-date{font-size:11px!important}
    .review-head .review-stars-row{position:static;margin-top:4px;justify-content:flex-start;pointer-events:none}
    .review-head .user-stars i{font-size:18px}
  }

  /* Nudge kiri sedikit untuk "Buat Review" di desktop */
  @media (min-width:768px){
    #createReviewCard{ margin-left:-8px; } /* kiri dikit */
  }
</style>
@endpush

@php
  use Illuminate\Support\Facades\File;

  $cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);

  // Helper URL gambar: FE -> CMS -> placeholder (tanpa route baru)
  $venueImgUrl = function (?string $pathLike) {
      $pathLike = $pathLike ? trim($pathLike) : '';
      if ($pathLike === '') return asset('images/placeholder/venue.png');
      if (preg_match('~^https?://~i', $pathLike) || str_starts_with($pathLike, '/')) return $pathLike;

      $filename = basename($pathLike);

      $feAbs  = base_path('../demo-xanders/images/venue/' . $filename);
      $feLink = public_path('fe-venue'); // symlink publik → ../demo-xanders/images/venue
      if (File::exists($feAbs) && is_dir($feLink)) return asset('fe-venue/' . $filename);

      $cmsAbs = public_path('images/venue/' . $filename);
      if (File::exists($cmsAbs)) return asset('images/venue/' . $filename);

      $storAbs = public_path('storage/uploads/' . $filename);
      if (File::exists($storAbs)) return asset('storage/uploads/' . $filename);

      return asset('images/placeholder/venue.png');
  };

  // Kumpulkan gambar galeri
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

  // Rating summary
  $avgText   = number_format((float)($averageRating ?? 0), 1, ',', '.');
  $fullStars = floor((float)($averageRating ?? 0));
@endphp

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<script>
  (function(){ const setSVH=()=>document.documentElement.style.setProperty('--svh',(window.innerHeight*0.01)+'px'); setSVH(); window.addEventListener('resize',setSVH); })();
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

        {{-- ================= REVIEWS (ringkasan + list SAJA di kiri) ================= --}}
        <div id="reviewsStart" class="max-w-7xl mx-auto px-0 lg:px-0 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Ringkasan kiri --}}
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
                    <div class="ratebar"><div class="fill" style="width: {{ $pct }}%"></div></div>
                    <div class="count">({{ number_format($cnt, 0, ',', '.') }})</div>
                  </div>
                @endfor
              </div>
            </aside>

            {{-- Daftar review (span 2 kolom) --}}
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
        {{-- ================= END REVIEWS ================= --}}

      </div>

      {{-- RIGHT: Booking + Terms + CREATE REVIEW (di kanan) --}}
      <div class="space-y-6" id="rightCol">
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

        {{-- ==== BUAT REVIEW (diturunkan agar sejajar dengan Customer Reviews) ==== --}}
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
                  {{ session('error') }}
                </div>
              @endif
              @if ($errors->any())
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
        {{-- ==== END BUAT REVIEW ==== --}}
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
  /* ===== Sinkronkan posisi Create Review agar sejajar (lebih bawah dikit & kiri dikit) ===== */
  function alignCreateReview() {
    const mq = window.matchMedia('(min-width: 768px)');
    const anchor = document.getElementById('reviewsAnchor');   // H3 "Customer Reviews"
    const createCard = document.getElementById('createReviewCard');
    if (!anchor || !createCard) return;

    if (!mq.matches) { // mobile: reset
      createCard.style.marginTop = '';
      return;
    }

    // Tambahkan offset kebawah 14px supaya terlihat sedikit di bawah judul "Customer Reviews"
    const extraDown = 14;

    const anchorTop = anchor.getBoundingClientRect().top + window.scrollY;
    const cardTop   = createCard.getBoundingClientRect().top + window.scrollY;

    const delta = anchorTop - cardTop;
    const topMargin = (delta > 0 ? delta : 0) + extraDown;

    createCard.style.marginTop = topMargin + 'px';
    // Nudge kiri dilakukan via CSS (@media min-width:768px => margin-left:-8px)
  }
  // panggil pada berbagai momen agar stabil
  window.addEventListener('resize', alignCreateReview);
  window.addEventListener('load', alignCreateReview);
  setTimeout(alignCreateReview, 250);
  alignCreateReview();

  /* ===== Rating stars input (form kanan) ===== */
  const stars = document.querySelectorAll('#ratingBox i');
  const ratingInput = document.getElementById('ratingInput');
  stars.forEach(st => {
    st.addEventListener('click', () => {
      const v = parseInt(st.dataset.value, 10);
      ratingInput.value = v;
      stars.forEach(s2 => s2.classList.toggle('active', parseInt(s2.dataset.value,10) <= v));
    });
  });

  /* ===== Booking logic (jadwal & meja) ===== */
  const datePicker   = document.getElementById("datePicker");
  const scheduleList = document.getElementById("scheduleList");
  const tableList    = document.getElementById("tableList");
  const form         = document.getElementById("addToCartForm");
  const venueId      = "{{ $detail->id }}";
  let selectedSchedule = null;
  let selectedTableNumber = null;

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
              selectedSchedule = { start: slot.start, end: slot.end, price: sch.price };
              document.getElementById("priceDisplay").innerText = "Rp " + new Intl.NumberFormat("id-ID").format(sch.price);
              renderTables(slot.tables);
            });
            scheduleList.appendChild(lbl);
          });
        });
      })
      .catch(() => {
        scheduleList.innerHTML = `<p class="text-red-500 text-sm">Error loading schedules.</p>`;
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
        selectedTableNumber = tbl.name || ('Table ' + tbl.id);
      });
      tableList.appendChild(lbl);
    });
  }

  form.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!isLoggedIn) {
      Swal.fire({ title: 'Belum Login!', text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.', icon: 'warning' })
        .then(() => { window.location.href = '/login'; });
      return;
    }

    const date    = datePicker.value;
    const schedule= document.querySelector('input[name="schedule"]:checked');
    const table   = document.querySelector('input[name="table_id"]:checked');

    if (!date)     { Swal.fire({ title:'Oops!', text:'Silakan pilih tanggal terlebih dahulu.', icon:'warning' }); return; }
    if (!schedule) { Swal.fire({ title:'Oops!', text:'Silakan pilih jadwal terlebih dahulu.', icon:'warning' }); return; }
    if (!table)    { Swal.fire({ title:'Oops!', text:'Silakan pilih meja terlebih dahulu.', icon:'warning' }); return; }

    const fd = new FormData(this);
    fd.append('schedule[start]', selectedSchedule.start);
    fd.append('schedule[end]',   selectedSchedule.end);
    fd.append('price',           selectedSchedule.price);
    fd.append('table',           selectedTableNumber);

    Swal.fire({ title:'Mohon tunggu...', text:'Sedang memproses permintaan Anda.', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

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
        Swal.fire({ title:'Berhasil!', text:'Venue berhasil ditambahkan ke keranjang.', icon:'success' })
          .then(() => location.reload());
      } else {
        Swal.fire({ title:'Gagal!', text: data.message || 'Terjadi kesalahan, coba lagi.', icon:'error' });
      }
    })
    .catch(() => {
      Swal.close();
      Swal.fire({ title:'Error!', text:'Terjadi kesalahan jaringan.', icon:'error' });
    });
  });
});
</script>
@endpush
