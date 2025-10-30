@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
{{-- ===== Anti white flash / rubber-band iOS & Android ===== --}}
<div id="antiBounceBg" aria-hidden="true"></div>

<style>
  :root{ color-scheme: dark; }
  :root, html, body{ background:#0a0a0a; }
  html, body{ height:100%; overscroll-behavior-y:none; overscroll-behavior-x:none; touch-action:pan-y; -webkit-text-size-adjust:100%; }
  #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:#0a0a0a; z-index:-1; pointer-events:none; }
  #app, main{ background:#0a0a0a; }
  .scroll-root{ overscroll-behavior:contain; background:#0a0a0a; }
  .scroll-inner{ overscroll-behavior:contain; background:#0a0a0a; }
</style>

<script>
(function(){function setSVH(){const svh=window.innerHeight*0.01;document.documentElement.style.setProperty('--svh',svh+'px');}
setSVH();window.addEventListener('resize',setSVH);})();
</script>

@php
  use App\Models\Order;

  // CDN base (override-able via .env XB_CDN)
  $cdnHost = config('app.xb_cdn', env('XB_CDN', 'https://demo-xanders.ptbmn.id'));
  $cdn = [
    'venue' => rtrim($cdnHost, '/') . '/images/venue/',
  ];
  $venueFallbacks = ['venue-1.png','venue-2.png','venue-3.png','venue-4.png'];

  $orders = Order::where('order_type','venue')
    ->where('user_id', auth()->id())
    ->with(['bookings.venue']) // penting: pre-load venue
    ->when(request('status'), function ($query) {
      $status = request('status');
      if ($status === 'booked') {
        $query->whereHas('bookings', fn($q)=>$q->where('status','booked'));
      } elseif ($status) {
        $query->where('payment_status', $status);
      }
    })
    ->orderByDesc('created_at')
    ->get();

  $orderCount     = Order::where('order_type','venue')->where('user_id',auth()->id())->count();
  $pendingCount   = Order::where('order_type','venue')->where('user_id',auth()->id())->where('payment_status','pending')->count();
  $processingCount= Order::where('order_type','venue')->where('user_id',auth()->id())->where('payment_status','processing')->count();
  $paidCount      = Order::where('order_type','venue')->where('user_id',auth()->id())->where('payment_status','paid')->count();
  $failedCount    = Order::where('order_type','venue')->where('user_id',auth()->id())->where('payment_status','failed')->count();
  $refundedCount  = Order::where('order_type','venue')->where('user_id',auth()->id())->where('payment_status','refunded')->count();
  $bookedCount    = Order::where('order_type','venue')->where('user_id',auth()->id())->whereHas('bookings', fn($q)=>$q->where('status','booked'))->count();
@endphp

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans scroll-root">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 my-8 bg-neutral-900 scroll-root">
      @include('partials.topbar')

      <section class="flex-1 overflow-auto space-y-6 mt-16 mx-16 bg-neutral-900 scroll-inner">
        <h1 class="text-3xl font-bold mb-8">Booking</h1>

        <nav class="flex space-x-6 text-gray-400 text-sm font-semibold mb-6 border-b border-gray-700">
          <a href="{{ route('booking.index') }}" class="flex items-center space-x-1 @if(request('status')===null) text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>All</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $orderCount ?? 0 }}</span>
          </a>
          <a href="{{ route('booking.index', ['status'=>'pending']) }}" class="flex items-center space-x-1 @if(request('status')==='pending') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Pending</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $pendingCount ?? 0 }}</span>
          </a>
          <a href="{{ route('booking.index', ['status'=>'processing']) }}" class="flex items-center space-x-1 @if(request('status')==='processing') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Processing</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $processingCount ?? 0 }}</span>
          </a>
          <a href="{{ route('booking.index', ['status'=>'booked']) }}" class="flex items-center space-x-1 @if(request('status')==='booked') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Booked</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $bookedCount ?? 0 }}</span>
          </a>
          <a href="{{ route('booking.index', ['status'=>'paid']) }}" class="flex items-center space-x-1 @if(request('status')==='paid') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Paid</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $paidCount ?? 0 }}</span>
          </a>
          <a href="{{ route('booking.index', ['status'=>'failed']) }}" class="flex items-center space-x-1 @if(request('status')==='failed') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Failed</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $failedCount ?? 0 }}</span>
          </a>
          <a href="{{ route('booking.index', ['status'=>'refunded']) }}" class="flex items-center space-x-1 @if(request('status')==='refunded') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Refunded</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $refundedCount ?? 0 }}</span>
          </a>
        </nav>

        <div class="space-y-6">
          @forelse($orders as $order)
            <article class="bg-[#222222] rounded-lg p-4 space-y-4 shadow-sm">
              <header class="flex justify-between items-center border-b border-gray-600 pb-2">
                <span class="text-gray-300 text-sm">{{ $order->created_at->format('d F Y') }}</span>
                @php
                  $badgeColor = [
                    'pending'   => 'bg-blue-600 text-white',
                    'processing'=> 'bg-yellow-400 text-gray-900',
                    'paid'      => 'bg-green-600 text-white',
                    'failed'    => 'bg-red-600 text-white',
                    'refunded'  => 'bg-gray-600 text-white',
                  ][$order->payment_status] ?? 'bg-[#f59e0b]';
                @endphp
                <span class="{{ $badgeColor }} text-white text-xs font-normal rounded-full px-4 py-1">
                  {{ ucfirst($order->payment_status) }}
                </span>
              </header>

              <ul class="divide-y divide-gray-600">
                @foreach($order->bookings as $booking)
                  @php
                    // Ambil gambar pertama venue (image / images)
                    $raw = $booking->venue?->image ?? null;
                    if (!$raw && !empty($booking->venue?->images)) {
                      $imgs = $booking->venue->images;
                      if (is_array($imgs)) { $raw = $imgs[0] ?? null; }
                      elseif (is_string($imgs)) {
                        $maybe = json_decode($imgs, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) { $raw = $maybe[0] ?? null; }
                      }
                    }
                    // Selalu pakai CDN /images/venue/{basename}
                    if ($raw) {
                      $path = filter_var($raw, FILTER_VALIDATE_URL) ? (parse_url($raw, PHP_URL_PATH) ?? $raw) : $raw;
                      $basename = basename(str_replace('\\','/',$path));
                      $imgUrl = $cdn['venue'] . $basename;
                    } else {
                      $idx = ($loop->index % count($venueFallbacks));
                      $imgUrl = $cdn['venue'] . $venueFallbacks[$idx];
                    }
                    $fallbackUrl = $cdn['venue'] . $venueFallbacks[0];
                  @endphp

                  <a href="{{ $order->payment_status === 'pending'
                              ? route('checkout.payment', ['order_number' => $order->order_number])
                              : route('order.detail', ['order' => $order->id]) }}">
                    <li class="flex items-center py-3 space-x-4">
                      <img
                        src="{{ $imgUrl }}"
                        alt="{{ $booking->venue->name }}"
                        class="w-[60px] h-[90px] rounded-md object-cover bg-gray-700"
                        onerror="this.onerror=null;this.src='{{ $fallbackUrl }}'"/>

                      <div class="flex-1">
                        <p class="font-bold text-white text-sm leading-tight">{{ $booking->venue->name }}</p>
                        <p class="text-gray-300 text-xs mt-1">
                          {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
                          {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}
                          - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                        </p>
                      </div>

                      <p class="text-gray-300 text-sm whitespace-nowrap">
                        Rp. {{ number_format($booking->price, 0, ',', '.') }}
                      </p>
                    </li>
                  </a>
                @endforeach
              </ul>

              <footer class="flex justify-end border-t border-gray-600 pt-3 text-gray-300 text-sm font-semibold">
                <span class="mr-2">Total :</span>
                <span class="text-white font-extrabold text-base">
                  Rp {{ number_format($order->total, 0, ',', '.') }}
                </span>
              </footer>
            </article>
          @empty
            <div class="text-center text-gray-500">
              <p class="text-lg font-semibold">No orders found.</p>
            </div>
          @endforelse
        </div>
      </section>
    </main>
  </div>
</div>
@endsection
