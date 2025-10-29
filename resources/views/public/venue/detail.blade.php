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

  .muted{ color:#9ca3af; }
  .iframe-wrap{ position: relative; width:100%; border:1px solid #3a3a3a; border-radius:12px; overflow:hidden; background:#111; }
  .iframe-wrap::before{content:"";display:block;padding-top:56.25%;}
  .iframe-wrap iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }

  .img-clickable{ cursor: pointer; }

  /* ===================== */
  /*   IMAGE LOADING UI    */
  /* ===================== */
  .img-wrapper{ position:relative; background:#171717; overflow:hidden; border-radius:12px; }
  .img-wrapper > img{ display:block; width:100%; height:100%; object-fit:cover; opacity:0; transition:opacity .28s ease; }
  .img-wrapper > img.is-loaded{ opacity:1; }
  .img-loading{ position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:#171717; z-index:1; }
  .img-loading.is-hidden{ display:none; }
  .spinner{ width:40px; height:40px; border:3px solid rgba(255,255,255,.18); border-top-color:#9ca3af; border-radius:50%; animation:spin .8s linear infinite; }
  @keyframes spin{ to{ transform: rotate(360deg); } }
  @media (prefers-reduced-motion: reduce){ .spinner{ animation:none; } .img-wrapper>img{ transition:none; } }
</style>
@endpush

@php
  // FE base untuk gambar venue
  $feBaseVenue = 'https://demo-xanders.ptbmn.id/images/venue/';

  // Placeholder aman: data URI (SVG) → tidak ada network request
  $placeholderDataUri = 'data:image/svg+xml;utf8,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="100%" height="100%" fill="#111"/></svg>');

  // Normalizer ke FE base: https://demo-xanders.ptbmn.id/images/venue/<filename>
  $normalizeToFeVenue = function ($s) use ($feBaseVenue) {
      $s = is_string($s) ? trim($s) : '';
      if ($s === '') return null;
      if (preg_match('#^https?://#i', $s)) {
          $path = parse_url($s, PHP_URL_PATH) ?: '';
          $name = basename($path);
          return $name ? ($feBaseVenue . $name) : null;
      }
      $name = basename($s);
      if ($name && $name !== '/' && $name !== '.') return $feBaseVenue . $name;
      return null;
  };

  $cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);

  // Kumpulkan gambar venue
  $rawImages = [];
  if (!empty($detail->images)) {
      if (is_array($detail->images)) {
          $rawImages = $detail->images;
      } elseif (is_string($detail->images)) {
          $decoded = json_decode($detail->images, true);
          $rawImages = is_array($decoded) ? $decoded : [];
      }
  }
  if (empty($rawImages) && !empty($detail->image)) $rawImages = [$detail->image];

  $resolvedImages = [];
  foreach ($rawImages as $ri) {
      $url = $normalizeToFeVenue($ri);
      if ($url && !in_array($url, $resolvedImages, true)) $resolvedImages[] = $url;
  }
  if (!$resolvedImages) $resolvedImages = [$placeholderDataUri];

  // Main + thumbnails
  $mainImage = $resolvedImages[0];
  $thumbs = array_slice($resolvedImages, 1, 2);
  while (count($thumbs) < 2) { $thumbs[] = $placeholderDataUri; }

  // Kandidat untuk main image (precompute, tanpa arrow function)
  $mainCandidates = $resolvedImages;
  $idxMain = array_search($mainImage, $mainCandidates, true);
  if ($idxMain !== false) {
      array_splice($mainCandidates, $idxMain, 1);
      array_unshift($mainCandidates, $mainImage);
  }
  if (!in_array($placeholderDataUri, $mainCandidates, true)) {
      $mainCandidates[] = $placeholderDataUri;
  }

  // Rating
  $averageRating = $averageRating ?? 0;
  $avgText   = number_format((float)$averageRating, 1, ',', '.');
  $fullStars = (int)floor((float)$averageRating);

  // Address
  $displayAddressRaw = trim((string)($detail->address ?? ''));
  $displayAddress = $displayAddressRaw !== '' ? $displayAddressRaw : 'Alamat belum tersedia';

  // Map embed
  $mapRaw = trim((string)($detail->map_embed ?? ''));
  $extractSrc = function($input) {
      if ($input === '') return '';
      if (stripos($input, '<iframe') !== false) {
          if (preg_match('~src\s*=\s*"(.*?)"~i', $input, $m)) return trim($m[1]);
          if (preg_match("~src\s*=\s*'(.*?)'~i", $input, $m)) return trim($m[1]);
      }
      return $input;
  };
  $src = $extractSrc($mapRaw);
  if ($src === '' || !preg_match('~^https?://~i', $src)) {
      $query = trim(($detail->name ?? '') . ' ' . $displayAddressRaw);
      $src = 'https://www.google.com/maps?hl=id&q=' . rawurlencode($query) . '&ie=UTF8&output=embed';
  }
  $mapsSrc = $src;

  // Facilities
  $facilities = [];
  if (is_array($detail->facilities)) {
      $facilities = $detail->facilities;
  } elseif (is_string($detail->facilities) && $detail->facilities !== '') {
      $decodedF = json_decode($detail->facilities, true);
      $facilities = is_array($decodedF) ? $decodedF : [];
  }
  $facilities = array_values(array_filter(array_map(function($x){ return trim((string)$x); }, $facilities)));

  // Kirim filename jika mainImage bukan data URI
  $mainFilename = (function($u){
      if (strpos((string)$u, 'data:') === 0) return null;
      $path = parse_url($u, PHP_URL_PATH);
      $bn = basename($path ?: (string)$u);
      return ($bn && $bn !== '/' && $bn !== '.') ? $bn : null;
  })($mainImage);
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
            <div class="img-wrapper h-[300px] md:h-[360px] rounded-lg">
              <div class="img-loading" aria-hidden="true" role="progressbar"><div class="spinner" aria-hidden="true"></div></div>
              <img id="mainImage"
                   src="{{ $mainImage }}"
                   alt="{{ $detail->name }}"
                   class="w-full h-full object-cover img-clickable"
                   data-lazy-load
                   data-src-candidates='@json($mainCandidates)'
                   decoding="async"
                   onclick="onMainClick()" />
            </div>
          </div>
          <div class="flex flex-col gap-4" id="thumbsWrap">
            @foreach ($thumbs as $t)
              <div class="img-wrapper w-full h-[140px] md:h-[170px] rounded-lg">
                <div class="img-loading" aria-hidden="true" role="progressbar"><div class="spinner" aria-hidden="true"></div></div>
                <img id="thumb{{ $loop->index }}"
                     data-index="{{ $loop->index + 1 }}"
                     src="{{ $t }}"
                     alt="Thumbnail {{ $loop->iteration }} - {{ $detail->name }}"
                     class="w-full h-full object-cover cursor-pointer"
                     data-lazy-load
                     data-src-candidates='@json([$t, $placeholderDataUri])'
                     loading="lazy"
                     decoding="async"
                     onclick="onThumbClick(event)" />
              </div>
            @endforeach
          </div>
        </div>

        {{-- Info Venue --}}
        <div class="space-y-6">
          <div>
            <h1 class="text-2xl font-extrabold">{{ $detail->name }}</h1>
            <p class="text-gray-300">{{ $displayAddress }}</p>
          </div>
          <hr class="border-gray-400">
          <div>
            <h2 class="font-semibold mb-2">Facilities</h2>

            @if (!empty($facilities))
              <ul class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm text-gray-300">
                @foreach ($facilities as $f)
                  <li>• {{ $f }}</li>
                @endforeach
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
              <span>{{ $displayAddress }}</span>
            </div>

            <div class="mt-3">
              <div class="iframe-wrap">
                <iframe
                  src="{{ $mapsSrc }}"
                  loading="lazy"
                  allowfullscreen
                  referrerpolicy="no-referrer-when-downgrade">
                </iframe>
              </div>
            </div>
          </div>
          {{-- /Location --}}
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

            {{-- Hidden diisi otomatis --}}
            <input type="hidden" name="start" id="startInput">
            <input type="hidden" name="end" id="endInput">
            <input type="hidden" name="price" id="priceInput">
            <input type="hidden" name="table_number" id="tableNumberInput">

            {{-- Kirim filename gambar utama ke backend (kalau ada) --}}
            <input type="hidden" name="image" id="imageInput" value="{{ $mainFilename ?? '' }}">

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
                <div class="mt-3 rounded-md bg-red-600/20 text-red-300 px-3 py-2 border border-red-600/30">
                  {{ session('error') }}
                </div>
              @endif
              @if ($errors->any())
                <div class="mt-3 rounded-md bg-red-600/20 text-red-300 px-3 py-2 border border-red-600/30">
                  {{ $errors->first() }}
                </div>
              @endif

              <form action="{{ route('venues.reviews.store', ['venue' => $detail->id]) }}" method="POST" class="mt-3" id="reviewForm">
                @csrf
                <label class="block text-gray-300 mb-2">Rating</label>
                <div class="flex items-center gap-2 stars-input mb-3" id="ratingBox" role="radiogroup" aria-label="Rating">
                  @for ($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star" data-value="{{ $i }}" tabindex="0" role="radio" aria-checked="false"></i>
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
    <button aria-label="Shopping cart with {{ $cartCount }} items" onclick="showCart()" class="fixed right-6 top-[60%] bg-[#2a2a2a] rounded-full w-16 h-16 flex items-center justify-center shadow-lg">
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  /* ========== GALLERY (rotate) + IMAGE LOADER (no 404 placeholder) ========== */
  const images = @json($resolvedImages);
  const placeholder = @json($placeholderDataUri); // data URI → tidak ada request
  function $(sel){ return document.querySelector(sel); }
  const mainEl = $('#mainImage'); const thumb0 = $('#thumb0'); const thumb1 = $('#thumb1'); const thumbsWrap = $('#thumbsWrap');
  let currentIndex = 0;

  function getNextIndices(){
    if (images.length <= 1) return [];
    if (images.length === 2) return [(currentIndex + 1) % 2];
    return [(currentIndex + 1) % images.length, (currentIndex + 2) % images.length];
  }

  function refreshImage(img, newSrc, candidates){
    if (!img) return;
    const wrapper = img.closest('.img-wrapper');
    const loader  = wrapper ? wrapper.querySelector('.img-loading') : null;

    // Simpan src sebelumnya agar bisa revert jika gagal
    if (!img.dataset.prev || (!img.dataset.prev.startsWith('data:') && img.src)) {
      img.dataset.prev = img.src || '';
    }

    img.classList.remove('is-loaded');
    if (loader) loader.classList.remove('is-hidden');

    try {
      const list = Array.isArray(candidates) ? candidates.slice(0) : [newSrc];
      if (list.indexOf(placeholder) === -1) list.push(placeholder); // fallback aman (data URI)
      img.setAttribute('data-src-candidates', JSON.stringify(list));
    } catch(e) {}

    img.src = newSrc;
  }

  function renderGallery(){
    if (!mainEl) return;

    // Kandidat untuk main: semua gambar mulai dari index saat ini + placeholder
    const mainCandidates = images.slice(currentIndex).concat(images.slice(0, currentIndex));
    refreshImage(mainEl, images[currentIndex] || placeholder, mainCandidates);

    if (!thumb0 || !thumb1) return;
    const idxs = getNextIndices();

    if (images.length <= 1){
      if (thumbsWrap) thumbsWrap.style.display = 'none';
      return;
    } else {
      if (thumbsWrap) thumbsWrap.style.display = '';
    }

    if (idxs[0] !== undefined){
      thumb0.dataset.index = String(idxs[0]);
      refreshImage(thumb0, images[idxs[0]] || placeholder, [images[idxs[0]] || placeholder, placeholder]);
      thumb0.style.pointerEvents = '';
      thumb0.style.opacity = '';
    } else {
      thumb0.removeAttribute('data-index');
      thumb0.style.pointerEvents = 'none';
      thumb0.style.opacity = '.6';
    }

    if (idxs[1] !== undefined){
      thumb1.dataset.index = String(idxs[1]);
      refreshImage(thumb1, images[idxs[1]] || placeholder, [images[idxs[1]] || placeholder, placeholder]);
      thumb1.style.pointerEvents = '';
      thumb1.style.opacity = '';
    } else {
      thumb1.removeAttribute('data-index');
      thumb1.style.pointerEvents = 'none';
      thumb1.style.opacity = '.6';
    }
  }

  function onMainClick(){ if (images.length <= 1) return; currentIndex = (currentIndex + 1) % images.length; renderGallery(); }
  function onThumbClick(e){ const t = e.currentTarget; const idx = parseInt(t && t.dataset ? t.dataset.index : '', 10); if (isNaN(idx)) return; currentIndex = idx; renderGallery(); }
  window.onMainClick = onMainClick; window.onThumbClick = onThumbClick;

  function initImageLoadingWithFallback(selector = '.img-wrapper img[data-lazy-load]'){
    const nodeList = document.querySelectorAll(selector);
    Array.prototype.forEach.call(nodeList, function(img){
      const wrapper = img.closest('.img-wrapper');
      const loader  = wrapper ? wrapper.querySelector('.img-loading') : null;

      var list = [];
      try { list = JSON.parse(img.getAttribute('data-src-candidates') || '[]'); } catch(e) { list = []; }
      if (!Array.isArray(list) || list.length === 0) { list = [img.getAttribute('src')].filter(Boolean); }
      if (list.indexOf(placeholder) === -1) list.push(placeholder); // jaga-jaga

      var idx = Math.max(0, list.indexOf(img.getAttribute('src') || ''));
      const showLoader = function(){ if (loader) loader.classList.remove('is-hidden'); };
      const hideLoader = function(){ if (loader) loader.classList.add('is-hidden'); };
      const markLoaded = function(){ img.classList.add('is-loaded'); hideLoader(); };

      if (img.complete && img.naturalWidth > 0) { markLoaded(); } else { showLoader(); }

      img.addEventListener('load', function(){ if (img.naturalWidth > 0) markLoaded(); }, { passive: true });
      img.addEventListener('error', function(){
        if (idx < list.length - 1) {
          idx++;
          showLoader();
          const nextSrc = list[idx];
          if (nextSrc && img.src !== nextSrc) { img.src = nextSrc; }
        } else {
          // Tidak ada kandidat lain → revert ke gambar sebelumnya jika ada
          const prev = img.dataset.prev || '';
          if (prev && prev !== img.src) {
            img.src = prev;
          }
          markLoaded();
        }
      }, { passive: true });
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    renderGallery();
    initImageLoadingWithFallback();
  });
</script>

<script>
  /* ========== BOOKING + ADD TO CART (AJAX) ========== */
  const isLoggedIn   = @json(auth()->check());
  const userRole     = @json(Auth::check() ? Auth::user()->roles : null);
  const venueId      = @json($detail->id);
  const baseVenuesUrl= @json(url('/venues'));

  document.addEventListener("DOMContentLoaded", function() {
    const datePicker   = document.getElementById('datePicker');
    const openDateBtn  = document.getElementById('openDateBtn');
    const addBtn       = document.getElementById('addToCartButton');
    const scheduleList = document.getElementById("scheduleList");
    const tableList    = document.getElementById("tableList");
    const priceDisplay = document.getElementById("priceDisplay");
    const form         = document.getElementById("addToCartForm");

    let selectedSchedule = null;
    let selectedTableNumber = null;

    if (openDateBtn && datePicker) openDateBtn.addEventListener('click', function(){ datePicker.showPicker(); });

    function createScheduleSlot(slot, price) {
      const lbl = document.createElement("label");
      const isBooked = !!slot.is_booked;
      lbl.className = "slot" + (isBooked ? " slot--disabled" : "");
      lbl.innerHTML = `
        <input type="radio" name="schedule" value="${slot.start}-${slot.end}"
               class="hidden" required ${isBooked ? 'disabled' : ''}>
        ${slot.start} - ${slot.end}
      `;
      if (!isBooked) {
        const radio = lbl.querySelector("input");
        radio.addEventListener("change", function() {
          document.querySelectorAll('.slot').forEach(function(s){ s.classList.remove('slot--active'); });
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
        schedules.forEach(function(sch){
          (sch.schedule || []).forEach(function(slot){
            const scheduleSlot = createScheduleSlot(slot, sch.price);
            scheduleList.appendChild(scheduleSlot);
          });
        });
      } catch (error) {
        console.error('Error loading schedules:', error);
        scheduleList.innerHTML = `<p class="text-red-400 text-sm">Failed to load schedules. Please try again.</p>`;
      }
    }

    function renderTables(tables) {
      tables = tables || [];
      tableList.innerHTML = "";
      if (tables.length === 0) {
        tableList.innerHTML = `<p class="text-gray-400 text-sm">No tables available.</p>`;
        return;
      }
      tables.forEach(function(tbl){
        const lbl = document.createElement("label");
        const disabledClass = tbl.is_booked ? 'opacity-40 pointer-events-none bg-gray-700' : '';
        lbl.className = `slot ${disabledClass}`;
        lbl.innerHTML = `
          <input type="radio" name="table_id" value="${tbl.id}" class="hidden" ${tbl.is_booked ? 'disabled' : ''}>
          ${tbl.name || ("Table " + tbl.id)}
        `;
        const radio = lbl.querySelector("input");
        radio.addEventListener("change", function(){
          selectedTableNumber = tbl.name || ('Table ' + tbl.id);
        });
        tableList.appendChild(lbl);
      });
    }

    if (datePicker) {
      datePicker.addEventListener("change", function() { loadSchedules(this.value); });
    }

    (function initDate() {
      if (!datePicker) return;
      const t = new Date();
      const yyyy = t.getFullYear(); const mm = String(t.getMonth() + 1).padStart(2, '0'); const dd = String(t.getDate()).padStart(2, '0');
      const todayStr = `${yyyy}-${mm}-${dd}`;
      datePicker.min = todayStr; datePicker.value = todayStr;
      loadSchedules(todayStr);
    })();

    if (addBtn) {
      addBtn.addEventListener('click', function() {
        if (!isLoggedIn) {
          Swal.fire({ title:'Belum Login!', text:'Silakan login terlebih dahulu untuk menambahkan ke keranjang.', icon:'warning', confirmButtonText:'Login Sekarang', confirmButtonColor:'#3085d6', background:'#1E1E1F', color:'#FFFFFF' }).then(function(){ window.location.href = '/login'; });
          return;
        }
        if (userRole !== 'user') {
          Swal.fire({ title:'Akses Ditolak!', text:'Hanya user yang bisa menambahkan ke keranjang.', icon:'error', confirmButtonColor:'#3085d6', background:'#1E1E1F', color:'#FFFFFF' });
          return;
        }

        const schedule = document.querySelector('input[name="schedule"]:checked');
        const table    = document.querySelector('input[name="table_id"]:checked');

        if (!datePicker || !datePicker.value) { Swal.fire({ title:'Oops!', text:'Silakan pilih tanggal terlebih dahulu.', icon:'warning', background:'#1E1E1F', color:'#FFFFFF' }); return; }
        if (!schedule || !selectedSchedule) { Swal.fire({ title:'Oops!', text:'Silakan pilih jadwal terlebih dahulu.', icon:'warning', background:'#1E1E1F', color:'#FFFFFF' }); return; }
        if (!table) { Swal.fire({ title:'Oops!', text:'Silakan pilih meja terlebih dahulu.', icon:'warning', background:'#1E1E1F', color:'#FFFFFF' }); return; }

        document.getElementById('startInput').value       = selectedSchedule.start;
        document.getElementById('endInput').value         = selectedSchedule.end;
        document.getElementById('priceInput').value       = selectedSchedule.price || 0;
        document.getElementById('tableNumberInput').value = selectedTableNumber || '';

        const main = document.getElementById('mainImage') ? document.getElementById('mainImage').getAttribute('src') : '';
        try {
          if (main && main.indexOf('data:') !== 0) {
            const url = new URL(main, window.location.origin);
            const parts = url.pathname.split('/');
            const fname = parts[parts.length - 1] || '';
            if (fname) document.getElementById('imageInput').value = fname;
          }
        } catch (e) {}

        document.getElementById("addToCartForm").requestSubmit();
      });
    }

    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        const date     = datePicker ? datePicker.value : null;
        const schedule = document.querySelector('input[name="schedule"]:checked');
        const table    = document.querySelector('input[name="table_id"]:checked');

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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
          },
          body: fd
        })
        .then(function(res){ return res.json().then(function(json){ return { ok: res.ok, json: json }; }); })
        .then(function(resp){
          Swal.close();
          if (resp.ok && resp.json && resp.json.success) {
            const badge = document.getElementById('cartCountBadge');
            if (badge && resp.json.cart_count) {
              badge.textContent = resp.json.cart_count;
              badge.classList.remove('hidden');
            }
            Swal.fire({ title:'Berhasil!', text:'Venue berhasil ditambahkan ke keranjang.', icon:'success' })
              .then(function(){ location.reload(); });
          } else {
            Swal.fire({ title:'Gagal!', text: (resp.json && resp.json.message) ? resp.json.message : 'Terjadi kesalahan, coba lagi.', icon:'error' });
          }
        })
        .catch(function(err){
          console.error(err);
          Swal.close();
          Swal.fire({ title:'Error!', text:'Terjadi kesalahan jaringan.', icon:'error' });
        });
      });
    }

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

{{-- ========== STAR RATING (click + keyboard, sets hidden input) ========== --}}
<script>
document.addEventListener('DOMContentLoaded', function(){
  const box   = document.getElementById('ratingBox');
  const input = document.getElementById('ratingInput');
  if (!box || !input) return;

  const stars = Array.prototype.slice.call(box.querySelectorAll('i[data-value]'));

  function paint(n){
    stars.forEach(function(s){
      const v = parseInt(s.getAttribute('data-value') || '0', 10);
      s.classList.toggle('active', v <= n);
      s.setAttribute('aria-checked', v <= n ? 'true' : 'false');
    });
    input.value = n;
  }

  stars.forEach(function(s){
    s.addEventListener('click', function(){ paint(parseInt(s.getAttribute('data-value') || '0', 10)); });
    s.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        paint(parseInt(s.getAttribute('data-value') || '0', 10));
      }
    });
  });

  const initial = parseInt(input.value || '0', 10);
  if (initial > 0) paint(initial);
});
</script>
@endpush
