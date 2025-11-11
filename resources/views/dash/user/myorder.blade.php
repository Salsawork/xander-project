@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
{{-- ===== Anti white flash / rubber-band iOS & Android ===== --}}
<div id="antiBounceBg" aria-hidden="true"></div>

<style>
  :root { color-scheme: dark; }
  :root, html, body { background:#0a0a0a; }
  html, body { height:100%; }
  html, body { overscroll-behavior-y:none; overscroll-behavior-x:none; touch-action:pan-y; -webkit-text-size-adjust:100%; }
  #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:#0a0a0a; z-index:-1; pointer-events:none; }
  #app, main{ background:#0a0a0a; }
  .scroll-root{ overscroll-behavior:contain; background:#0a0a0a; }
  .scroll-inner{ overscroll-behavior:contain; background:#0a0a0a; }
</style>

<script>
  (function(){
    function setSVH(){ const svh = window.innerHeight * 0.01; document.documentElement.style.setProperty('--svh', svh+'px'); }
    setSVH(); window.addEventListener('resize', setSVH);
  })();
</script>

@php
  use App\Models\Order;

  // === CDN base untuk products ===
  // Set di .env: XB_CDN=https://xanderbilliard.site
  $cdnHost      = config('app.xb_cdn', env('XB_CDN', 'https://xanderbilliard.site'));
  $prodCdnBase  = rtrim($cdnHost, '/') . '/images/products/';

  // Fallback file yang pasti ada di ../demo-xanders/images/products/
  $prodFallbacks = ['1.png','2.png','3.png','4.png','5.png'];

  $orders = Order::where('order_type','product')
      ->where('user_id', auth()->id())
      ->with('products')
      ->when(request('status'), function ($query) {
          $status = request('status');
          return $status === 'processing'
              ? $query->whereIn('delivery_status', ['pending','processing','packed'])
              : $query->where('delivery_status', $status);
      })
      ->orderByDesc('created_at')
      ->get();

  $orderCount      = Order::where('order_type','product')->where('user_id',auth()->id())->count();
  $processingCount = Order::where('order_type','product')->where('user_id',auth()->id())->whereIn('delivery_status',['pending','processing','packed'])->count();
  $shippedCount    = Order::where('order_type','product')->where('user_id',auth()->id())->where('delivery_status','shipped')->count();
  $deliveredCount  = Order::where('order_type','product')->where('user_id',auth()->id())->where('delivery_status','delivered')->count();
  $cancelledCount  = Order::where('order_type','product')->where('user_id',auth()->id())->where('delivery_status','cancelled')->count();
@endphp

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans scroll-root">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 my-8 bg-neutral-900 scroll-root">
      @include('partials.topbar')

      <section class="flex-1 overflow-auto space-y-6 mt-16 mx-16 bg-neutral-900 scroll-inner">
        <h1 class="text-3xl font-bold mb-8">My Order</h1>

        <nav class="flex space-x-6 text-gray-400 text-sm font-semibold mb-6 border-b border-gray-700">
          <a href="{{ route('myorder.index') }}" class="flex items-center space-x-1 @if(request('status')===null) text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>All</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $orderCount }}</span>
          </a>
          <a href="{{ route('myorder.index', ['status'=>'processing']) }}" class="flex items-center space-x-1 @if(request('status')==='processing') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Processing</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $processingCount }}</span>
          </a>
          <a href="{{ route('myorder.index', ['status'=>'shipped']) }}" class="flex items-center space-x-1 @if(request('status')==='shipped') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Shipped</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $shippedCount }}</span>
          </a>
          <a href="{{ route('myorder.index', ['status'=>'delivered']) }}" class="flex items-center space-x-1 @if(request('status')==='delivered') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Delivered</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $deliveredCount }}</span>
          </a>
          <a href="{{ route('myorder.index', ['status'=>'cancelled']) }}" class="flex items-center space-x-1 @if(request('status')==='cancelled') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
            <span>Cancelled</span><span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $cancelledCount }}</span>
          </a>
        </nav>

        <div class="space-y-6">
          @forelse($orders as $order)
            <article class="bg-[#222222] rounded-lg p-4 space-y-4 shadow-sm">
              <header class="flex justify-between items-center border-b border-gray-600 pb-2">
                <span class="text-gray-300 text-sm">{{ $order->created_at->format('d F Y') }}</span>
                @php
                  $badgeColor = [
                    'pending'   => 'bg-[#3b82f6] text-white',
                    'processing'=> 'bg-[#fbbf24] text-[#78350f]',
                    'packed'    => 'bg-[#3b82f6] text-white',
                    'shipped'   => 'bg-[#3b82f6] text-white',
                    'delivered' => 'bg-[#22c55e] text-white',
                    'cancelled' => 'bg-[#f87171] text-[#7f1d1d]',
                    'returned'  => 'bg-[#f87171] text-[#7f1d1d]',
                  ][$order->delivery_status] ?? 'bg-[#f59e0b]';
                @endphp
                <span class="{{ $badgeColor }} text-white text-xs font-normal rounded-full px-4 py-1">
                  {{ ucfirst($order->delivery_status) }}
                </span>
              </header>

              <ul class="divide-y divide-gray-600">
                @foreach($order->products as $product)
                  @php
                    // Ambil gambar pertama dari pivot->images (array/JSON/string)
                    $images = $product->pivot->images
                      ? (is_array($product->pivot->images)
                          ? $product->pivot->images
                          : (json_decode($product->pivot->images, true) ?: (is_string($product->pivot->images) ? [$product->pivot->images] : []))
                        )
                      : [];

                    $firstRaw = $images[0] ?? null;

                    // Selalu pakai CDN: /images/products/{basename}
                    $basename = null;
                    if ($firstRaw) {
                      if (filter_var($firstRaw, FILTER_VALIDATE_URL)) {
                        $path = parse_url($firstRaw, PHP_URL_PATH) ?? $firstRaw;
                        $basename = basename(str_replace('\\','/',$path));
                      } else {
                        $basename = basename(str_replace('\\','/',$firstRaw));
                      }
                    }

                    // Tentukan URL final + fallback dari CDN
                    $idx = ($loop->index % count($prodFallbacks));
                    $fallbackCdn = $prodCdnBase . $prodFallbacks[$idx];
                    $imgUrl = $basename ? ($prodCdnBase . $basename) : $fallbackCdn;
                  @endphp

                  <a href="{{ $order->payment_status === 'pending'
                              ? route('checkout.payment', ['order_number' => $order->order_number])
                              : route('order.detail', ['order' => $order->id]) }}">
                    <li class="flex items-center py-3 space-x-4">
                      <img
                        src="{{ $imgUrl }}"
                        alt="{{ $product->name }}"
                        class="w-[60px] h-[90px] rounded-md object-cover bg-gray-700"
                        onerror="this.onerror=null;this.src='{{ $prodCdnBase . $prodFallbacks[0] }}'"/>

                      <div class="flex-1">
                        <p class="font-bold text-white text-sm leading-tight">{{ $product->name }}</p>
                        <p class="text-gray-300 text-xs mt-1">{{ $product->pivot->quantity }}x</p>
                      </div>

                      <p class="text-gray-300 text-sm whitespace-nowrap">
                        Rp. {{ number_format($product->pivot->price, 0, ',', '.') }}
                      </p>
                    </li>
                  </a>
                @endforeach
              </ul>

              <footer class="flex justify-end border-t border-gray-600 pt-3 text-gray-300 text-sm font-semibold">
                <span class="mr-2">Total :</span>
                <span class="text-white font-extrabold text-base">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
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
