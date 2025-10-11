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
                    My Order
                </h1>
                @php
                use App\Models\Order;
                $orders = Order::where('order_type', 'product')->where('user_id', auth()->id())->with('products', 'orderSparrings')->when(
                request('status'),
                function ($query) {
                $status = request('status');
                if ($status === 'processing') {
                return $query->whereIn('payment_status', ['pending', 'processing', 'packed']);
                } else {
                return $query->where('payment_status', $status);
                }
                }
                )->get();
                $orderCount = Order::where('order_type', 'product')->where('user_id', auth()->id())->count();
                $processingCount = Order::where('order_type', 'product')->where('user_id', auth()->id())->whereIn('delivery_status', ['pending', 'processing', 'packed'])->count();
                $shippedCount = Order::where('order_type', 'product')->where('user_id', auth()->id())->where('delivery_status', 'shipped')->count();
                $deliveredCount = Order::where('order_type', 'product')->where('user_id', auth()->id())->where('delivery_status', 'delivered')->count();
                $cancelledCount = Order::where('order_type', 'product')->where('user_id', auth()->id())->where('delivery_status', 'cancelled')->count();
                @endphp
                <nav class="flex space-x-6 text-gray-400 text-sm font-semibold mb-6 border-b border-gray-700">
                    <a href="{{ route('myorder.index') }}"
                        class="flex items-center space-x-1 @if(request('status') === null) text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>All</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $orderCount }}</span>
                    </a>
                    <a href="{{ route('myorder.index', ['status' => 'processing']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'processing') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Processing</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $processingCount }}</span>
                    </a>
                    <a href="{{ route('myorder.index', ['status' => 'shipped']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'shipped') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Shipped</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $shippedCount }}</span>
                    </a>
                    <a href="{{ route('myorder.index', ['status' => 'delivered']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'delivered') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Delivered</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $deliveredCount }}</span>
                    </a>
                    <a href="{{ route('myorder.index', ['status' => 'cancelled']) }}"
                        class="flex items-center space-x-1 @if(request('status') === 'cancelled') text-white border-b-2 border-[#0ea5e9] pb-1 @endif">
                        <span>Cancelled</span>
                        <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">{{ $cancelledCount }}</span>
                    </a>
                </nav>
                <div class="space-y-6">
                    @forelse($orders as $order)
                    <article class="bg-[#222222] rounded-lg p-4 space-y-4 shadow-sm">
                        <header class="flex justify-between items-center border-b border-gray-600 pb-2">
                            <span class="text-gray-300 text-sm">
                                {{ $order->created_at->format('d F Y') }}
                            </span>
                            <span class="bg-[#f59e0b] text-white text-xs font-normal rounded-full px-4 py-1">
                                {{ ucfirst($order->delivery_status) }}
                            </span>
                        </header>
                        <ul class="divide-y divide-gray-600">
                            @foreach($order->products as $product)
                            <li class="flex items-center py-3 space-x-4">
                                @php
                                $imagePath = 'https://placehold.co/400x600?text=No+Image';

                                if (!empty($product->images) && is_array($product->images)) {
                                foreach ($product->images as $img) {
                                if (!empty($img)) {
                                $imagePath = asset('storage/uploads/' . $img);
                                break;
                                }
                                }
                                }
                                @endphp
                                <img src="{{ $imagePath }}"
                                    alt="{{ $product->name }}"
                                    class="object-cover"
                                    style="width: 60px; height: 90px; object-fit: cover;"
                                    onerror="this.src='https://placehold.co/400x600?text=No+Image'" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">
                                        {{ $product->name }}
                                    </p>
                                    <p class="text-gray-300 text-xs mt-1">
                                        {{ $product->pivot->quantity }}x
                                    </p>
                                </div>
                                <p class="text-gray-300 text-sm whitespace-nowrap">
                                    Rp. {{ number_format($product->pivot->price, 0, ',', '.') }}
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