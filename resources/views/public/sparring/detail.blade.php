@extends('app')

@section('title', 'Sparring Detail')
@php
$cartCount = count($cartProducts) + count($cartVenues) + count($cartSparrings);

$detail     = $athlete->athleteDetail ?? null;
$years      = $detail->experience_years ?? null;
$yearsText  = $years ? $years . ' Years' : 'N/A';
$specialty  = $detail->specialty ?? 'N/A';
$location   = $detail->location ?? 'N/A';
$bio        = $detail->bio ?? 'Pemain biliar profesional dengan pengalaman mengajar lebih dari 5 tahun. Spesialis dalam teknik kontrol bola dan strategi permainan.';

// Share data
$shareUrlAbs = url()->current();
$shareText   = 'Sparring dengan ' . ($athlete->name ?? 'Athlete') . ' di Xander Billiard';
$fbShareUrl  = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($shareUrlAbs);
$xShareUrl   = 'https://twitter.com/intent/tweet?text=' . urlencode($shareText) . '&url=' . urlencode($shareUrlAbs);

// === WhatsApp (ganti dari Instagram) ===
$waPhone     = '6281284679921'; // +62 812-8467-9921 -> 6281284679921
$waMessage   = $shareText . ' ' . $shareUrlAbs;
$waShareUrl  = 'https://wa.me/' . $waPhone . '?text=' . urlencode($waMessage);

// Available dates
$availableDates = $availableDates ?? [];

// Rating summary
$avgText   = number_format((float)($averageRating ?? 0), 1, ',', '.');
$fullStars = floor((float)($averageRating ?? 0));
@endphp

@push('styles')
<style>
  /* ====== GLOBAL BACKGROUND: #171717 ====== */
  :root{color-scheme:dark}
  *,*::before,*::after{box-sizing:border-box}
  html,body,#app,main{background:#171717}
  html,body{width:100%;max-width:100%;overflow-x:hidden;overscroll-behavior-y:none;overscroll-behavior-x:none;scrollbar-gutter:stable both-edges}
  .page-wrap{overflow-x:clip;background:#171717}

  /* ====== UI Elements ====== */
  .btn-share{width:2.25rem;height:2.25rem;display:flex;align-items:center;justify-content:center;border-radius:9999px;background:#374151;transition:.2s}
  .btn-share:hover{background:#4b5563}
  /* Cards use #171717 to match the requested background */
  .card{background:#171717;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  .booking-card{padding:25px!important;border-radius:12px}
  .booking-card hr{margin-top:8px;margin-bottom:14px}
  .booking-card .price{font-size:20px!important;line-height:1}
  .booking-card p,.booking-card label,.booking-card span{font-size:12px}
  .booking-card button[type="submit"]{height:42px;font-size:15px;border-radius:10px}

  .input-pill{width:100%;padding:.80rem 2.75rem .80rem .9rem;border-radius:12px;background:#222222;color:#fff;border:1.5px solid rgba(255,255,255,.2);outline:none;appearance:none;font-size:13px}
  .input-pill:focus{box-shadow:0 0 0 2px #3b82f6;border-color:#3b82f6}
  .input-pill::placeholder{color:#9ca3af}
  input[type="date"]::-webkit-calendar-picker-indicator{opacity:0;display:none}
  input[type="date"]::-webkit-inner-spin-button,input[type="date"]::-webkit-clear-button{display:none}
  input[type="date"]{-moz-appearance:textfield}

  .slot{display:flex;align-items:center;justify-content:center;padding:.38rem .5rem;border-radius:9px;font-weight:800;background:#2a2a2a;color:#fff;border:1.5px solid rgba(255,255,255,.15);transition:.18s;cursor:pointer;user-select:none;font-size:.88rem}
  .slot:hover{background:#3b82f6;border-color:#3b82f6}
  .slot--active{background:#3b82f6!important;border-color:#3b82f6!important}
  .slot--disabled{background:#6b7280!important;color:#d1d5db!important;border-color:transparent!important;cursor:not-allowed!important}

  /* ====== Reviews: Summary (left) ====== */
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

  /* ====== Review Item & Create Card use #171717 ====== */
  .review-item{--avatar:48px;--gap:16px;--indent:calc(var(--avatar) + var(--gap));position:relative;padding:22px;background:#171717;border-radius:14px}
  .review-item::before{content:"";position:absolute;left:var(--indent);right:0;top:0;height:1px;background:rgba(255,255,255,.08)}
  .review-head{position:relative}
  .review-left{display:flex;align-items:center;gap:16px}
  .review-avatar{width:var(--avatar);height:var(--avatar);border-radius:9999px;background:#2f2f2f;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;overflow:hidden}
  .review-stars-row{position:absolute;left:var(--indent);right:0;top:2px;display:flex;justify-content:flex-end;pointer-events:none}
  .user-stars i{font-size:26px;color:#e5e7eb}

  .create-card{background:#171717;border-radius:14px;padding:16px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  .stars-input i{font-size:22px;color:#6b7280;cursor:pointer;transition:transform .06s ease}
  .stars-input i.active{color:#f5c518}
  .helper{font-size:.8rem;color:#9ca3af}

  /* ====== Mobile tweaks ====== */
  @media (max-width:640px){
    .review-item{--avatar:40px;--gap:12px;padding:16px 14px 14px}
    .review-left{gap:12px}
    .review-avatar{font-size:16px}
    .review-name{font-size:15px!important;line-height:1.2}
    .review-date{font-size:11px!important}
    .review-head .review-stars-row{position:static;margin-top:4px;justify-content:flex-start;pointer-events:none}
    .review-head .user-stars i{font-size:18px}
  }
</style>
@endpush

@section('content')
<div class="page-wrap text-white min-h-screen"><!-- background now pure #171717 -->

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
      <img src="{{ $photo }}" alt="{{ $athlete->name }}" class="w-full h-[430px] object-cover rounded-lg shadow-lg" onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
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
          <a href="{{ $fbShareUrl }}" target="_blank" rel="noopener" class="btn-share" aria-label="Share on Facebook" title="Share on Facebook">
            <i class="fab fa-facebook-f text-white"></i>
          </a>
          <a href="{{ $xShareUrl }}" target="_blank" rel="noopener" class="btn-share" aria-label="Share on X" title="Share on X">
            <i class="fab fa-x-twitter text-white"></i>
          </a>
          {{-- GANTI: Instagram -> WhatsApp --}}
          <a href="{{ $waShareUrl }}" target="_blank" rel="noopener" class="btn-share" aria-label="Chat on WhatsApp" title="Chat on WhatsApp">
            <i class="fab fa-whatsapp text-white"></i>
          </a>
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
            <div class="field-wrap mt-2 relative">
              <input id="dateInput" name="date" type="date" class="input-pill pr-12" placeholder="YYYY-MM-DD">
              <button type="button" id="openDateBtn" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white" aria-label="Open date picker" title="Pick a date">
                <i class="far fa-calendar-alt"></i>
              </button>
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

          <button
          type="button"
          id="addToCartButton"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold">
          <i class="fas fa-shopping-cart mr-2"></i>
          Add to cart
        </button>
        </form>
      </div>
    </div>
  </div>

  <!-- REVIEWS + CREATE REVIEW -->
  <div class="max-w-7xl mx-auto px-4 lg:px-6 pb-16">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <!-- Ringkasan -->
      <aside class="reviews-card md:col-span-1">
        <h3 class="text-base">Customer Reviews</h3>
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

      <!-- Daftar review -->
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
            <p class="mt-4 text-gray-300">{{ $review->comment }}</p>
          </article>
        @empty
          <div class="card p-6 text-gray-300">Belum ada ulasan untuk atlet ini.</div>
        @endforelse
      </section>

      <!-- Create review -->
      <aside class="md:col-span-1">
        <div class="create-card">
          <h3 class="text-base font-semibold">Buat Review</h3>

          @auth
            @if ($alreadyReviewed)
              <div class="mt-3 text-sm text-gray-300">Kamu sudah memberi review untuk atlet ini. Terima kasih! 🙌</div>
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

              <form action="{{ route('sparring.review.store', ['id' => $athlete->id]) }}" method="POST" class="mt-3">
                @csrf

                <label class="block text-sm text-gray-300 mb-2">Rating</label>
                <div class="flex items-center gap-2 stars-input mb-3" id="ratingBox">
                  @for ($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star" data-value="{{ $i }}"></i>
                  @endfor
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', 0) }}">

                <label class="block text-sm text-gray-300 mb-2">Komentar</label>
                <textarea name="comment" rows="5" required class="w-full rounded-md bg-[#1f1f1f] border border-neutral-700 px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Tulis pengalamanmu sparring dengan atlet ini...">{{ old('comment') }}</textarea>

                <p class="helper mt-2">Gunakan bahasa yang sopan. Reviewmu membantu user lain 😊</p>

                <button type="submit" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm py-2.5 rounded-md">Kirim Review</button>
              </form>
            @endif
          @else
            <div class="mt-3 text-sm text-gray-300">Kamu harus login untuk membuat review.</div>
            <a href="{{ route('login') }}" class="inline-flex items-center mt-3 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm px-4 py-2 rounded-md">Login Sekarang</a>
          @endauth
        </div>
      </aside>
    </div>
  </div>

  <!-- Cart -->
  @if (Auth::check() && Auth::user()->roles === 'user')
  <button aria-label="Shopping cart" onclick="showCart()" class="fixed right-6 top-[60%] bg-neutral-700/90 backdrop-blur rounded-full w-16 h-16 flex items-center justify-center shadow-xl ring-1 ring-black/30">
    <i class="fas fa-shopping-cart text-white text-2xl"></i>
    @if ($cartCount > 0)
      <span class="absolute -top-1.5 -right-1.5 bg-blue-600 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">{{ $cartCount }}</span>
    @endif
  </button>
  @endif

  @include('public.cart')
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};

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

  const dateInput   = document.getElementById('dateInput');
  const openDateBtn = document.getElementById('openDateBtn');
  const scheduleGrid= document.getElementById('scheduleGrid');

  openDateBtn?.addEventListener('click', () => {
    if (dateInput?.showPicker) dateInput.showPicker(); else dateInput.focus();
  });

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

  const firstDate = @json($availableDates[0] ?? null);
  if (dateInput && firstDate) { dateInput.value = firstDate; renderSchedules(firstDate); }

  dateInput?.addEventListener('change', function(){ renderSchedules(this.value); });

  scheduleGrid?.addEventListener('click', function(e) {
    const label = e.target.closest('label[data-slot-id]');
    if (!label || label.classList.contains('slot--disabled')) return;
    [...scheduleGrid.querySelectorAll('label.slot')].forEach(l => l.classList.remove('slot--active'));
    label.classList.add('slot--active');
    const input = label.querySelector('input[type="radio"]'); if (input) input.checked = true;
  });

  document.getElementById('addToCartForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    if (!isLoggedIn) {
      Swal.fire({
        title: 'Belum Login!',
        text: 'Silakan login terlebih dahulu untuk memesan atau menambahkan ke keranjang.',
        icon: 'warning',
        confirmButtonText: 'Login Sekarang',
        confirmButtonColor: '#3085d6',
        background: '#1E1E1F',
        color: '#FFFFFF'
      }).then(() => { window.location.href = '/login'; });
      return;
    }

    const date = document.getElementById('dateInput')?.value;
    const schedule = document.querySelector('input[name="schedule_id"]:checked');
    if (!date) {
      Swal.fire({ title:'Oops!', text:'Silakan pilih tanggal terlebih dahulu.', icon:'warning', confirmButtonColor:'#3085d6', background:'#1E1E1F', color:'#FFFFFF' });
      return;
    }
    if (!schedule) {
      Swal.fire({ title:'Oops!', text:'Silakan pilih jadwal terlebih dahulu.', icon:'warning', confirmButtonColor:'#3085d6', background:'#1E1E1F', color:'#FFFFFF' });
      return;
    }

    Swal.fire({ title:'Mohon tunggu...', text:'Sedang memproses permintaan Anda.', allowOutsideClick:false, didOpen:()=>Swal.showLoading(), background:'#1E1E1F', color:'#FFFFFF' });

    fetch(this.action, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: new FormData(this)
    })
    .then(async res => {
      if (res.status === 401) {
        Swal.close();
        Swal.fire({
          title: 'Belum Login!',
          text: 'Silakan login terlebih dahulu untuk menambahkan ke keranjang.',
          icon: 'warning',
          confirmButtonText: 'Login Sekarang',
          confirmButtonColor: '#3085d6',
          background: '#1E1E1F',
          color: '#FFFFFF'
        }).then(() => { window.location.href = '/login'; });
        return;
      }
      const data = await res.json();
      Swal.close();

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
          const badge = document.querySelector('.fixed.right-6.top-\\[60\\%\\] > span');
          if (badge) {
            badge.textContent = data.cartCount;
            badge.style.display = data.cartCount > 0 ? 'flex' : 'none';
          }
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
    .catch(() => {
      Swal.close();
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

  const addBtn = document.getElementById('addToCartButton');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
      const userRole = "{{ Auth::check() ? Auth::user()->roles : '' }}";

      if (!isLoggedIn) {
        Swal.fire({
          title: 'Belum Login!',
          text: 'Silakan login terlebih dahulu untuk menambahkan produk ke keranjang.',
          icon: 'warning',
          confirmButtonText: 'Login Sekarang',
          confirmButtonColor: '#3085d6',
          background: '#1E1E1F',
          color: '#FFFFFF'
        }).then(() => { window.location.href = '/login'; });
        return;
      }

      if (userRole !== 'user') {
        Swal.fire({
          title: 'Akses Ditolak!',
          text: 'Hanya user yang bisa menambahkan ke keranjang.',
          icon: 'error',
          confirmButtonColor: '#3085d6',
          background: '#1E1E1F',
          color: '#FFFFFF'
        });
        return;
      }

      // Kalau sudah login dan role = user, baru submit form
      document.getElementById('addToCartForm').requestSubmit();
    });
  }
});
</script>
@endpush
