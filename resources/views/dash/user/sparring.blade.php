@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
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
                $orders = Order::where('order_type', 'sparring')->where('user_id', auth()->id())->with('orderSparrings')->when(request('status'), function ($q, $status) {
                return $q->where('payment_status', $status);
                })->get();
                $orderCount = Order::where('order_type', 'sparring')->where('user_id', auth()->id())->count();
                $pendingCount = Order::where('order_type', 'sparring')->where('user_id', auth()->id())->where('payment_status', 'pending')->count();
                $processingCount = Order::where('order_type', 'sparring')->where('user_id', auth()->id())->where('payment_status', 'processing')->count();
                $paidCount = Order::where('order_type', 'sparring')->where('user_id', auth()->id())->where('payment_status', 'paid')->count();
                $failedCount = Order::where('order_type', 'sparring')->where('user_id', auth()->id())->where('payment_status', 'failed')->count();
                $refundedCount = Order::where('order_type', 'sparring')->where('user_id', auth()->id())->where('payment_status', 'refunded')->count();
                @endphp
                <nav class="flex space-x-6 text-gray-400 text-sm font-semibold mb-6 border-b border-gray-700">
                    <a href="{{ route('user.sparring.index') }}"
                        class="flex items-center space-x-1 @if(request('status') === null) text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>All</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $orderCount }}</span>
                    </a>
                    <a href="{{ route('user.sparring.index', ['status' => 'pending']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'pending') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Pending</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $pendingCount }}</span>
                    </a>
                    <a href="{{ route('user.sparring.index', ['status' => 'processing']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'processing') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Processing</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $processingCount }}</span>
                    </a>
                    <a href="{{ route('user.sparring.index', ['status' => 'paid']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'paid') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Paid</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $paidCount }}</span>
                    </a>
                    <a href="{{ route('user.sparring.index', ['status' => 'failed']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'failed') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Failed</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $failedCount }}</span>
                    </a>
                    <a href="{{ route('user.sparring.index', ['status' => 'refunded']) }}"
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
                            @php
                            $badgeColor = [
                            'pending' => 'bg-blue-600 text-white',
                            'processing' => 'bg-yellow-400 text-gray-900',
                            'paid' => 'bg-green-600 text-white',
                            'failed' => 'bg-red-600 text-white',
                            'refunded' => 'bg-gray-600 text-white',
                            ][$order->payment_status] ?? 'bg-[#f59e0b]';
                            @endphp
                            <span class="{{ $badgeColor }} text-white text-xs font-normal rounded-full px-4 py-1">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </header>
                        <ul class="divide-y divide-gray-600">
                            @foreach($order->orderSparrings as $sparring)
                            <a href="{{ $order->payment_status === 'pending' ? route('checkout.payment', ['order_number' => $order->order_number]) : route('order.detail', ['order' => $order->id]) }}">
                                <li class="flex items-center py-3 space-x-4">
                                    <img src="{{ $sparring->athlete_image ? asset('storage/uploads/' . $sparring->athlete_image) : 'https://placehold.co/400x600?text=No+Image' }}"
                                        alt="{{ $sparring->athlete->name }}"
                                        class="object-cover"
                                        style="width: 60px; height: 90px; object-fit: cover;"
                                        onerror="this.src='https://placehold.co/400x600?text=No+Image'" />
                                    <div class="flex-1">
                                        <p class="font-bold text-white text-sm leading-tight">
                                            {{ $sparring->athlete->name }}
                                        </p>
                                        <p class="text-gray-300 text-xs mt-1">
                                            {{ \Carbon\Carbon::parse($sparring->schedule->date)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($sparring->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sparring->schedule->end_time)->format('H:i') }}
                                        </p>
                                    </div>
                                    <p class="text-gray-300 text-sm whitespace-nowrap">
                                        Rp. {{ number_format($sparring->price, 0, ',', '.') }}
                                    </p>
                                </li>
                            </a>
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