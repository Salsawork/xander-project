@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
    {{-- ===== Anti white flash / rubber-band iOS & Android ===== --}}
    <div id="antiBounceBg" aria-hidden="true"></div>

    <style>
        :root { color-scheme: dark; }

        /* Pastikan root gelap */
        :root, html, body { background:#0a0a0a; }
        html, body { height:100%; }

        /* Matikan overscroll glow/bounce tembus body */
        html, body {
            overscroll-behavior-y: none;
            overscroll-behavior-x: none;
            touch-action: pan-y;
            -webkit-text-size-adjust: 100%;
        }

        /* Kanvas gelap fixed di belakang semua konten */
        #antiBounceBg{
            position: fixed;
            left:0; right:0;
            top:-120svh; bottom:-120svh;   /* extend ke atas/bawah */
            background:#0a0a0a;
            z-index:-1;
            pointer-events:none;
        }

        /* Pastikan wrapper layout juga gelap */
        #app, main { background:#0a0a0a; }

        /* ===== Scroll containers: cegah chaining + wajib background gelap ===== */
        .scroll-root {
            overscroll-behavior: contain;    /* stop chain ke body */
            background:#0a0a0a;              /* kalau bounce, tetap gelap */
        }
        .scroll-inner {
            overscroll-behavior: contain;
            background:#0a0a0a;
        }
    </style>

    {{-- Stabilkan unit tinggi viewport di mobile (toolbar naik/turun) --}}
    <script>
        (function(){
            function setSVH(){
                const svh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--svh', svh + 'px');
            }
            setSVH();
            window.addEventListener('resize', setSVH);
        })();
    </script>

    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans scroll-root">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')

            {{-- tambahkan bg & class scroll-root pada main yang scroll --}}
            <main class="flex-1 overflow-y-auto min-w-0 my-8 bg-neutral-900 scroll-root">
                @include('partials.topbar')

                {{-- section juga scrollable -> beri bg & cegah chaining --}}
                <section class="flex-1 overflow-auto space-y-6 mt-16 mx-16 bg-neutral-900 scroll-inner">
                    <h1 class="text-3xl font-bold mb-8">
                        Booking
                    </h1>

                    <nav class="flex space-x-6 text-gray-400 text-sm font-semibold mb-6 border-b border-gray-700">
                        <a href="{{ route('booking.index') }}"
                           class="flex items-center space-x-1 @if(request('status') === null) text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                            <span>All</span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $orderCount ?? 10 }}</span>
                        </a>
                        <a href="{{ route('booking.index', ['status' => 'processing']) }}"
                           class="flex items-center space-x-1 @if(request('status') === 'processing') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                            <span>Processing</span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $processingCount ?? 0 }}</span>
                        </a>
                        <a href="{{ route('booking.index', ['status' => 'booked']) }}"
                           class="flex items-center space-x-1 @if(request('status') === 'booked') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                            <span>Booked</span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $bookedCount ?? 0 }}</span>
                        </a>
                        <a href="{{ route('booking.index', ['status' => 'shipped']) }}"
                           class="flex items-center space-x-1 @if(request('status') === 'shipped') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                            <span>Shipped</span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $shippedCount ?? 0 }}</span>
                        </a>
                        <a href="{{ route('booking.index', ['status' => 'delivered']) }}"
                           class="flex items-center space-x-1 @if(request('status') === 'delivered') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                            <span>Delivered</span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $deliveredCount ?? 0 }}</span>
                        </a>
                        <a href="{{ route('booking.index', ['status' => 'cancelled']) }}"
                           class="flex items-center space-x-1 @if(request('status') === 'cancelled') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                            <span>Cancelled</span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $cancelledCount ?? 0 }}</span>
                        </a>
                    </nav>

                    <div class="space-y-6">
                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>22 February 2025</span>
                                <span class="bg-green-500 text-white text-xs font-medium rounded-full px-4 py-1 select-none">Completed</span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Modern building exterior with glass walls and blue sky"
                                     class="w-14 h-14 rounded-md object-cover flex-shrink-0" height="56"
                                     src="https://storage.googleapis.com/a1aa/image/ce5ef24b-6337-455a-001c-66d0af5e8b0c.jpg" width="56" />
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 my-8">
            @include('partials.topbar')
            <section class="flex-1 overflow-auto space-y-6 mt-16 mx-16">
                <h1 class="text-3xl font-bold mb-8">
                    Sparring
                </h1>
                @php
                use App\Models\Order;
                $orders = Order::where('order_type', 'venue')->where('user_id', auth()->id())->with('bookings')->when(request('status'), function ($q, $status) {
                return $q->where('payment_status', $status);
                })->get();
                $orderCount = Order::where('order_type', 'venue')->where('user_id', auth()->id())->count();
                $pendingCount = Order::where('order_type', 'venue')->where('user_id', auth()->id())->where('payment_status', 'pending')->count();
                $processingCount = Order::where('order_type', 'venue')->where('user_id', auth()->id())->where('payment_status', 'processing')->count();
                $paidCount = Order::where('order_type', 'venue')->where('user_id', auth()->id())->where('payment_status', 'paid')->count();
                $failedCount = Order::where('order_type', 'venue')->where('user_id', auth()->id())->where('payment_status', 'failed')->count();
                $refundedCount = Order::where('order_type', 'venue')->where('user_id', auth()->id())->where('payment_status', 'refunded')->count();
                @endphp
                <nav class="flex space-x-6 text-gray-400 text-sm font-semibold mb-6 border-b border-gray-700">
                    <a href="{{ route('sparring.index') }}"
                        class="flex items-center space-x-1 @if(request('status') === null) text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>All</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $orderCount }}</span>
                    </a>
                    <a href="{{ route('sparring.index', ['status' => 'pending']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'pending') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Pending</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $pendingCount }}</span>
                    </a>
                    <a href="{{ route('sparring.index', ['status' => 'processing']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'processing') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Processing</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $processingCount }}</span>
                    </a>
                    <a href="{{ route('sparring.index', ['status' => 'paid']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'paid') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Paid</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $paidCount }}</span>
                    </a>
                    <a href="{{ route('sparring.index', ['status' => 'failed']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'failed') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Failed</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $failedCount }}</span>
                    </a>
                    <a href="{{ route('sparring.index', ['status' => 'refunded']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'refunded') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Refunded</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $refundedCount }}</span>
                    </a>
                </nav>
                <div class="space-y-6">
                    @forelse($orders as $order)
                    <article class="bg-[#222222] rounded-lg p-4 space-y-4 shadow-sm">
                        <header class="flex justify-between items-center border-b border-gray-600 pb-2">
                            <span class="text-gray-300 text-sm">
                                {{ $order->created_at->format('d F Y') }}
                            </span>
                        </header>
                        <ul class="divide-y divide-gray-600">
                            @foreach($order->bookings as $booking)
                            <li class="flex items-center py-3 space-x-4">
                                @php
                                $imagePath = 'https://placehold.co/400x600?text=No+Image';

                                if (!empty($booking->venue->images) && is_array($booking->venue->images)) {
                                foreach ($booking->venue->images as $img) {
                                if (!empty($img)) {
                                $imagePath = asset('storage/uploads/' . $img);
                                break;
                                }
                                }
                                }
                                @endphp
                                <img src="{{ $imagePath }}"
                                    alt="{{ $booking->venue->name }}"
                                    class="object-cover"
                                    style="width: 60px; height: 90px; object-fit: cover;"
                                    onerror="this.src='https://placehold.co/400x600?text=No+Image'" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">The Cue Lounge</p>
                                    <p class="text-gray-300 text-xs">27 June 2025, 12.00-13.00</p>
                                    <p class="font-bold text-white text-sm leading-tight">
                                        {{ $booking->venue->name }}
                                    </p>
                                    <p class="text-gray-300 text-xs mt-1">
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                    </p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">50.000</div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">Total :</span>
                                <span class="text-white text-lg">50.000</span>
                            </footer>
                        </section>

                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>22 February 2025</span>
                                <span class="bg-[#3b82f6] text-white text-xs font-medium rounded-full px-4 py-1 select-none">Confirmed</span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Placeholder image icon in gray square"
                                     class="w-14 h-14 rounded-md object-cover flex-shrink-0 bg-gray-600" height="56"
                                     src="https://storage.googleapis.com/a1aa/image/a356a268-a5f4-435f-182e-4afa4c919e13.jpg" width="56" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">Golden Eight Billiards</p>
                                    <p class="text-gray-300 text-xs">27 June 2025, 12.00-13.00</p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">120.000</div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">Total :</span>
                                <span class="text-white text-lg">120.000</span>
                            </footer>
                        </section>

                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>22 February 2025</span>
                                <span class="bg-[#fbbf24] text-white text-xs font-medium rounded-full px-4 py-1 select-none">Pending</span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Placeholder image icon in gray square"
                                     class="w-14 h-14 rounded-md object-cover flex-shrink-0 bg-gray-600" height="56"
                                     src="https://storage.googleapis.com/a1aa/image/a356a268-a5f4-435f-182e-4afa4c919e13.jpg" width="56" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">Elite Cue Arena</p>
                                    <p class="text-gray-300 text-xs">27 June 2025, 12.00-13.00</p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">90.000</div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">Total :</span>
                                <span class="text-white text-lg">90.000</span>
                            </footer>
                        </section>

                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>22 February 2025</span>
                                <span class="bg-red-500 text-white text-xs font-medium rounded-full px-4 py-1 select-none">Cancelled</span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Placeholder image icon in gray square"
                                     class="w-14 h-14 rounded-md object-cover flex-shrink-0 bg-gray-600" height="56"
                                     src="https://storage.googleapis.com/a1aa/image/a356a268-a5f4-435f-182e-4afa4c919e13.jpg" width="56" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">Elite Cue Arena</p>
                                    <p class="text-gray-300 text-xs">27 June 2025, 12.00-13.00</p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">90.000</div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">Total :</span>
                                <span class="text-white text-lg">90.000</span>
                            </footer>
                        </section>
                    </div>
                </section>
            </main>
        </div>
    </div>
                                <p class="text-gray-300 text-sm whitespace-nowrap">
                                    Rp. {{ number_format($booking->price, 0, ',', '.') }}
                                </p>
                            </li>
                            @endforeach
                        </ul>
                        <footer
                            class="flex justify-end border-t border-gray-600 pt-3 text-gray-300 text-sm font-semibold">
                            <span class="mr-2">
                                Total :
                            </span>
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