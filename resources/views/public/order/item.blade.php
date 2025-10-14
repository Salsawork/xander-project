@extends('app')
@section('title', 'Order Detail - Xander Billiard')

@section('content')
<div class="min-h-screen bg-neutral-900 text-gray-100 font-sans py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Header -->
        <div class="flex items-center mb-6">
            <i class="fas fa-chevron-left text-xl mr-4 cursor-pointer hover:text-blue-400 transition" onclick="window.history.back();"></i>
            <h1 class="text-xl sm:text-2xl font-semibold items-center">
                Order Detail
                <span class="text-blue-500 font-bold">{{ $order->order_number }}</span>
                <span>|</span>
                <span class="text-sm text-gray-400 my-auto">{{ $order->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y \a\t H:i') }}</span>
            </h1>
        </div>

        @php
        $subtotal = 0;
        $discountTotal = 0;
        $tax = $order->products->first()->pivot->tax ?? 0;
        $shipping = $order->products->first()->pivot->shipping ?? 0;
        @endphp

        <!-- Main Grid: Order + Customer -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Order Details -->
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Products Ordered</h2>
                <div class="space-y-5">
                    @foreach($order->products as $product)
                    @php
                    $itemSubtotal = $product->pivot->price * $product->pivot->quantity - $product->pivot->discount;
                    $subtotal += $itemSubtotal;
                    $discountTotal += $product->pivot->discount;
                    @endphp
                    <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                        <div class="flex items-center gap-4">
                            <img src="{{ is_array($product->images) ? ($product->images[0] ?? '/images/elite-cue.png') : ($product->images ?? '/images/elite-cue.png') }}"
                                alt="{{ $product->name ?? 'Product' }}"
                                class="w-16 h-16 rounded-lg object-cover shadow-sm">
                            <div>
                                <p class="font-medium text-white">{{ $product->name ?? 'Product Name' }}</p>
                                <p class="text-gray-400 text-sm">{{ $product->category->name ?? 'Category' }}</p>
                                <p class="text-gray-400 text-sm">Qty: {{ $product->pivot->quantity }}</p>
                            </div>
                        </div>
                        <p class="font-medium text-right text-white">
                            Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                        </p>
                    </div>
                    @endforeach
                </div>

                <!-- Delivery -->
                <h2 class="text-lg font-semibold mt-6 mb-3">Delivery Details</h2>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="/images/fedex.png" alt="Courier" class="w-20">
                        <div>
                            <p class="font-medium">{{ strtoupper($order->products->first()->pivot->courier ?? '-') }}</p>
                            <p class="text-gray-400 text-sm">Standard Shipping (3–5 Business Days)</p>
                            <a href="#" class="text-blue-400 text-sm hover:underline">FD123456789US</a>
                        </div>
                    </div>
                    <p class="font-medium">Rp {{ number_format($shipping, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                <div class="text-sm space-y-1">
                    <p class="font-semibold text-base">{{ $user->name ?? 'Alex Johnson' }}</p>
                    <a href="mailto:{{ $user->email ?? 'alex.johnson@email.com' }}" class="text-gray-300 hover:text-white block">{{ $user->email ?? 'alex.johnson@email.com' }}</a>
                    <p class="text-gray-300">{{ $user->phone ?? '+1 555-789-1234' }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-1">Address:</p>
                    <p class="text-gray-300">{{ $order->products->first()->pivot->address ?? '-' }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-1">Payment Details:</p>
                    <p class="text-gray-300 capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                    <p class="text-gray-300">Status: <span class="text-green-400 font-semibold">{{ $order->payment_status ?? 'Paid' }}</span></p>
                    <p class="text-gray-400 text-xs mt-1">Transaction ID: <span class="text-gray-300">{{ $order->transaction_id ?? '-' }}</span></p>
                </div>
                @if(($order->payment_status ?? '') === 'paid')
                <a href="{{ route('invoice.product', $order->id) }}" class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 rounded-md font-medium transition inline-block text-center">Download Invoice</a>
                @endif
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-3">Payment Summary</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">Subtotal</span><span class="text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Tax</span><span class="text-white">Rp {{ number_format($tax, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Discount</span><span class="text-white">Rp {{ number_format($discountTotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Shipping</span><span class="text-white">Rp {{ number_format($shipping, 0, ',', '.') }}</span></div>
                </div>
                <hr class="border-neutral-800 my-3">
                <div class="flex justify-between text-base font-semibold">
                    <span>Grand Total</span>
                    <span class="text-white">Rp {{ number_format($subtotal + $tax + $shipping, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <p class="text-sm text-gray-300 mb-4">
                    Need help with your order? Contact our support team for updates or refund assistance:
                </p>
                <div class="space-y-2 text-sm text-gray-300">
                    <p><span class="text-gray-400">Phone:</span> +1 234 567 890</p>
                    <p><span class="text-gray-400">Email:</span> <a href="mailto:support@xanderbilliard.com" class="text-blue-500 hover:underline">support@xanderbilliard.com</a></p>
                    <p><span class="text-gray-400">Address:</span> 4568 Greenway Street, Los Angeles, CA</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection