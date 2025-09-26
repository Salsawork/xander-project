@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <section class="flex-1 overflow-auto p-6 space-y-6 mt-12 mx-8">
                    <h1 class="text-3xl font-extrabold">
                        My Order
                    </h1>
                    @php
                        use App\Models\Order;
                        $orders = Order::where('user_id', auth()->id())->with('products')->when(
                            request('status'),
                            function ($query) {
                                $status = request('status');
                                if ($status === 'processing') {
                                    return $query->whereIn('delivery_status', ['pending', 'processing', 'packed']);
                                } else {
                                    return $query->where('delivery_status', $status);
                                }
                            }
                        )->get();
                        $orderCount = Order::where('user_id', auth()->id())->count();
                        $processingCount = Order::where('user_id', auth()->id())->whereIn('delivery_status', ['pending', 'processing', 'packed'])->count();
                        $shippedCount = Order::where('user_id', auth()->id())->where('delivery_status', 'shipped')->count();
                        $deliveredCount = Order::where('user_id', auth()->id())->where('delivery_status', 'delivered')->count();
                        $cancelledCount = Order::where('user_id', auth()->id())->where('delivery_status', 'cancelled')->count();
                    @endphp
                    <nav class="flex space-x-6 border-b border-gray-700 text-sm font-semibold text-gray-500">
                        <a
                            class="relative after:left-0 after:right-0 after:h-[2px] after:bg-[#0ea5e9] after:rounded
                                @if(request('status') === null) text-[#0ea5e9]  @endif
                            "
                            href="{{ route('myorder.index') }}">
                            All
                            <span
                                class="ml-2 inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                {{ $orderCount }}
                            </span>
                        </a>
                        <a class="flex items-center space-x-1 cursor-default
                            @if(request('status') === 'processing') text-[#0ea5e9]  @endif"
                            href="{{ route('myorder.index', ['status' => 'processing']) }}">
                            <span>
                                Processing
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                {{ $processingCount }}
                            </span>
                        </a>
                        <a class="flex items-center space-x-1 cursor-default
                            @if(request('status') === 'shipped') text-[#0ea5e9]  @endif"
                            href="{{ route('myorder.index', ['status' => 'shipped']) }}">
                            <span>
                                Shipped
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                {{ $shippedCount }}
                            </span>
                        </a>
                        <a class="flex items-center space-x-1 cursor-default
                            @if(request('status') === 'delivered') text-[#0ea5e9]  @endif"
                            href="{{ route('myorder.index', ['status' => 'delivered']) }}">
                            <span>
                                Delivered
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                {{ $deliveredCount }}
                            </span>
                        </a>
                        <a class="flex items-center space-x-1 cursor-default
                            @if(request('status') === 'cancelled') text-[#0ea5e9]  @endif"
                            href="{{ route('myorder.index', ['status' => 'cancelled']) }}">
                            <span>
                                Cancelled
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                {{ $cancelledCount }}
                            </span>
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
