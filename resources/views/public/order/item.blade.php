@extends('app')
@section('title', 'Order Detail - Xander Billiard')

@section('content')
<div class="min-h-screen bg-neutral-900 text-gray-100 font-sans py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Header -->
        <div class="flex items-center mb-6">
            <i class="fas fa-chevron-left text-xl mr-4 cursor-pointer hover:text-blue-400 transition" onclick="window.history.back();"></i>
            <h1 class="text-xl sm:text-2xl font-semibold items-center flex-1">
                Order Detail
                <span class="text-blue-500 font-bold">{{ $order->order_number }}</span>
                <span class="text-gray-500 mx-2">|</span>
                <span class="text-sm text-gray-400 my-auto">{{ $order->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</span>
            </h1>
            <div class="flex gap-2 ml-auto">
                @if($hasSparring)
                <a href="{{ route('order.sparring', $order) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-sm font-medium transition">Sparring</a>
                @endif
                @if($hasBooking)
                <a href="{{ route('order.booking', $order) }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-md text-sm font-medium transition">Booking</a>
                @endif
            </div>
        </div>

        <!-- Main Grid -->
        @foreach($order->items as $item)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left: Order Info -->
            <div class="md:col-span-2 space-y-5">
                <!-- Product Ordered -->
                <div class="bg-neutral-800 rounded-lg p-5 border border-neutral-700">
                    <h2 class="text-lg font-semibold mb-1">Product Ordered</h2>
                    <hr class="border-neutral-700 mb-4">
                    <div class="space-y-4 mb-4">
                        <!-- Product 1 -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <img src="{{ $item->product->image_url ?? '/images/elite-cue.png' }}" alt="{{ $item->product->name ?? 'Product' }}" class="w-16 h-16 rounded-md object-cover">
                                <div>
                                    <p class="font-medium text-white">{{ $item->product->name ?? 'Product Name' }}</p>
                                    <p class="text-gray-400 text-sm">{{ $item->product->category->name ?? 'Category' }}</p>
                                    <p class="text-gray-400 text-sm">{{ $item->quantity }}x</p>
                                </div>
                            </div>
                            <p class="font-medium">Rp. {{ number_format($item->price * $item->quantity - $item->discount , 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <h2 class="text-lg font-semibold mb-3">Delivery Details</h2>
                    <hr class="border-neutral-700 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="/images/fedex.png" alt="FedEx" class="w-20">
                            <div>
                                <p class="font-medium">{{ $item->courier }}</p>
                                <p class="text-gray-400 text-sm">Standard Shipping (3–5 Business Days)</p>
                                <a href="#" class="text-blue-400 text-sm hover:underline">FD123456789US</a>
                            </div>
                        </div>
                        <p class="font-medium">Rp. {{ number_format($item->shipping, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-neutral-800 rounded-lg p-5 border border-neutral-700">
                    <h2 class="text-lg font-semibold mb-3">Payment Summary</h2>
                    <hr class="border-neutral-700 my-2">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span>Rp. {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tax 10%</span>
                            <span>Rp. {{ number_format($item->tax, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Discount</span>
                            <span>- Rp. {{ number_format($item->discount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <hr class="border-neutral-700 my-2">
                    <div class="flex justify-between text-base font-semibold">
                        <span>Grand Total</span>
                        <span class="text-white">Rp. {{ number_format($item->price * $item->quantity - $item->discount + $item->tax + $item->shipping, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-neutral-800 rounded-lg p-5 border border-neutral-700">
                    <h2 class="text-lg font-semibold mb-3">Customer Information</h2>
                    <hr class="border-neutral-700 my-2">
                    <div class="flex items-stretch gap-4 text-sm">
                        <div class="flex-1 space-y-1">
                            <p><span class="text-gray-400">Name:</span> Alex Johnson</p>
                            <p><span class="text-gray-400">Phone:</span> +1 555-789-1234</p>
                            <p><span class="text-gray-400">Address:</span> 789 Greenway Street, Apt 4B, Los Angeles, CA 90015, USA</p>
                        </div>
                        <div class="hidden md:block border-l border-neutral-700 self-stretch"></div>
                        <div class="flex-1 space-y-1">
                            <p><span class="text-gray-400">Payment Method:</span> Credit Card (Visa)</p>
                            <p><span class="text-gray-400">Transaction ID:</span> TXN-987654321</p>
                            <p><span class="text-gray-400">Payment Status:</span> <span class="text-green-400 font-semibold">Paid</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Activities + Policy -->
            <div class="space-y-5">
                <!-- Activities Timeline -->
                <div class="bg-neutral-800 rounded-lg p-5 border border-neutral-700">
                    <h2 class="text-lg font-semibold mb-4">Activities</h2>
                    <div class="relative border-l border-neutral-600 ml-4">
                        <!-- Item 1 -->
                        <div class="mb-8 ml-6">
                            <span class="absolute -left-[9px] w-4 h-4 bg-neutral-500 rounded-full border border-neutral-800"></span>
                            <p class="font-semibold text-gray-100">Order Placed</p>
                            <p class="text-gray-400 text-xs mb-1">March 2, 2025, at 20:00</p>
                            <p class="text-gray-400 text-sm">Customer placed an order. Awaiting admin confirmation.</p>
                        </div>

                        <!-- Item 2 -->
                        <div class="mb-8 ml-6">
                            <span class="absolute -left-[9px] w-4 h-4 bg-neutral-500 rounded-full border border-neutral-800"></span>
                            <p class="font-semibold text-gray-100">Being Packed</p>
                            <p class="text-gray-400 text-xs mb-1">March 3, 2025, at 12:30</p>
                            <p class="text-gray-400 text-sm">Admin confirmed the order. Proceed to packing.</p>
                        </div>

                        <!-- Item 3 -->
                        <div class="mb-8 ml-6">
                            <span class="absolute -left-[9px] w-4 h-4 bg-neutral-500 rounded-full border border-neutral-800"></span>
                            <p class="font-semibold text-gray-100">Picked Up by Courier</p>
                            <p class="text-gray-400 text-xs mb-1">March 3, 2025, at 20:00</p>
                            <p class="text-gray-400 text-sm">Courier has collected the package. Tracking number assigned.</p>
                        </div>

                        <!-- Item 4 -->
                        <div class="mb-8 ml-6">
                            <span class="absolute -left-[9px] w-4 h-4 bg-neutral-500 rounded-full border border-neutral-800"></span>
                            <p class="font-semibold text-gray-100">In Transit</p>
                            <p class="text-gray-400 text-xs mb-1">March 5, 2025, at 20:00</p>
                            <p class="text-gray-400 text-sm">Order is on the way to the customer's location.</p>
                        </div>

                        <!-- Item 5 (Active / Delivered) -->
                        <div class="mb-2 ml-6">
                            <span class="absolute -left-[9px] w-4 h-4 bg-blue-500 rounded-full border border-neutral-800"></span>
                            <p class="font-semibold text-gray-100">Delivered</p>
                            <p class="text-gray-400 text-xs mb-1">March 7, 2025, at 20:00</p>
                            <p class="text-gray-400 text-sm">Order successfully delivered to the customer.</p>
                        </div>
                    </div>
                </div>


                <!-- Return Policy -->
                <div class="bg-neutral-800 rounded-lg p-5 border border-neutral-700">
                    <h2 class="text-lg font-semibold mb-3">Return & Refund Policy</h2>
                    <p class="text-sm text-gray-300 mb-3">
                        If you are not satisfied with your purchase, you may return the item within <span class="text-white font-medium">14 days</span> of delivery for a refund or exchange.
                    </p>
                    <p class="text-sm text-gray-300 mb-3">
                        Once we receive the returned item, the refund process will begin, and the amount will be credited back to your original payment method within <span class="text-white font-medium">7 business days</span>.
                    </p>
                    <p class="text-sm text-gray-300">
                        For any questions, contact our support team.
                    </p>
                </div>

                <!-- Help Box -->
                <div class="bg-neutral-800 rounded-lg p-5 border border-neutral-700 text-sm text-gray-300">
                    <p class="mb-2">Need Help? <span class="text-white font-medium">Our team is here to help!</span></p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection