@extends('app')
@section('title', 'Admin Dashboard - Detail Order')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')
            <h1 class="text-2xl font-bold p-4 sm:p-8 mt-12">Detail Order {{ $order->order_type === 'product' ? 'Produk' :  ($order->order_type === 'sparring' ? 'Sparring' : 'Booking') }}</h1>

            @if(isset($order))
            <section class="bg-[#292929] rounded-lg p-4 sm:p-6 mx-4 sm:mx-8 mb-8">
                <!-- Order Header -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-xl font-bold">Order #{{ $order->id }}</h2>
                        <p class="text-gray-400 text-sm">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($order->order_type === 'product')
                    <div>
                        @php
                        $statusClass = [
                        'pending' => 'bg-yellow-500 text-white',
                        'paid' => 'bg-green-500 text-white',
                        'shipped' => 'bg-blue-500 text-white',
                        'delivered' => 'bg-purple-500 text-white',
                        'cancelled' => 'bg-red-500 text-white',
                        ];
                        @endphp
                        <span class="{{ $statusClass[$order->delivery_status] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full inline-block min-w-[80px] text-center text-sm">
                            {{ ucfirst($order->delivery_status) }}
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Customer & Payment Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
                    <!-- Customer Information -->
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold mb-2">Customer Information</h3>
                        <div class="bg-[#1e1e1e] rounded-lg p-4 space-y-2">
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Name:</span> {{ $order->user->name ?? 'Guest' }}</p>
                            <p class="text-sm sm:text-base break-all"><span class="text-gray-400">Email:</span> {{ $order->user->email ?? 'N/A' }}</p>
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Phone:</span> {{ $order->user->phone ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold mb-2">Payment Information</h3>
                        <div class="bg-[#1e1e1e] rounded-lg p-4 space-y-2">
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Payment Method:</span> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Payment Status:</span>
                                <span class="{{ $order->payment_status == 'paid' ? 'text-green-500' : ($order->payment_status == 'pending' ? 'text-yellow-500' : 'text-red-500') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </p>
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Total:</span> Rp. {{ number_format($order->total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <h3 class="text-base sm:text-lg font-semibold mb-2">Order Items</h3>

                <!-- Desktop & Tablet Table View -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full text-left text-gray-300 border-collapse">
                        <thead class="bg-[#1e1e1e] text-sm font-normal">
                            <tr>
                                <th class="px-4 py-3">Item</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3">Quantity</th>
                                <th class="px-4 py-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-normal">
                            @forelse($order->products as $product)
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @php
                                        $images = $product->images ? (is_array($product->images) ? $product->images : json_decode($product->images, true)) : [];
                                        $firstImage = !empty($images) ? $images[0] : null;
                                        $idx = ($loop->index % 5) + 1;
                                        $defaultImg = asset("images/products/{$idx}.png");
                                        $imagePath = $firstImage ? asset('storage/uploads/' . $firstImage) : $defaultImg;
                                        @endphp
                                        <img src="{{ $imagePath }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-md flex-shrink-0">
                                        <div>
                                            <p class="font-medium">{{ $product->name }}</p>
                                            <p class="text-xs text-gray-400">ID: {{ $product->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">Product</td>
                                <td class="px-4 py-3">
                                    <div>Rp. {{ number_format($product->pivot->price, 0, ',', '.') }}</div>
                                    @if($product->pivot->discount > 0)
                                    <div class="text-xs text-green-400">Disc: -Rp. {{ number_format($product->pivot->discount, 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $product->pivot->quantity }}</td>
                                <td class="px-4 py-3">Rp. {{ number_format($product->pivot->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            @endforelse

                            @forelse($order->bookings as $booking)
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="https://placehold.co/100x100?text=Court" alt="{{ $booking->court->name ?? 'Court' }}" class="w-12 h-12 object-cover rounded-md flex-shrink-0">
                                        <div>
                                            <p class="font-medium">{{ $booking->venue->name ?? 'Court' }}</p>
                                            <p class="text-xs text-gray-400">
                                                {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
                                                {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                            </p>
                                            <p class="text-xs text-gray-400">Table: {{ $booking->table->table_number ?? 'Table' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">Booking</td>
                                <td class="px-4 py-3">Rp. {{ number_format($booking->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">1</td>
                                <td class="px-4 py-3">Rp. {{ number_format($booking->price, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            @endforelse

                            @forelse($order->orderSparrings as $sparring)
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="https://placehold.co/100x100?text=Sparring" alt="Sparring" class="w-12 h-12 object-cover rounded-md flex-shrink-0">
                                        <div>
                                            <p class="font-medium">{{ $sparring->athlete->name ?? 'Sparring' }}</p>
                                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($sparring->schedule->date)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($sparring->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sparring->schedule->end_time)->format('H:i') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">Sparring</td>
                                <td class="px-4 py-3">Rp. {{ number_format($sparring->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">1</td>
                                <td class="px-4 py-3">Rp. {{ number_format($sparring->price, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            @endforelse
                            @if(optional($order->products)->isEmpty() && optional($order->bookings)->isEmpty() && optional($order->orderSparrings)->isEmpty())
                            <tr class="border-b border-gray-700">
                                <td colspan="5" class="px-4 py-3 text-center">No items found</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-[#1e1e1e] font-medium">
                            @php
                                $subtotal = 0;
                                $shipping = 0;
                                $tax = 0;
                                if ($order->products->isNotEmpty()) {
                                    $subtotal = $order->products->sum('pivot.subtotal');
                                    $shipping = $order->products->first()->pivot->shipping ?? 0;
                                    $tax = $order->products->first()->pivot->tax ?? 0;
                                }
                            @endphp
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right">Subtotal:</td>
                                <td class="px-4 py-3">Rp. {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right">Shipping:</td>
                                <td class="px-4 py-3">Rp. {{ number_format($shipping, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right">Tax:</td>
                                <td class="px-4 py-3">Rp. {{ number_format($tax, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right font-bold">Total:</td>
                                <td class="px-4 py-3 font-bold">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden space-y-4">
                    @forelse($order->products as $product)
                    <div class="bg-[#1e1e1e] rounded-lg p-4">
                        <div class="flex gap-3 mb-3">
                            @php
                            $imagePath = 'https://placehold.co/100x100?text=No+Image';
                            if ($product->images) {
                            $images = is_array($product->images) ? $product->images : json_decode($product->images, true);
                            if ($images && count($images) > 0) {
                            $imagePath = asset('storage/uploads/' . $images[0]);
                            }
                            }
                            @endphp
                            <img src="{{ $imagePath }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-md flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400">ID: {{ $product->id }}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Price:</span>
                                <span>Rp. {{ number_format($product->pivot->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Quantity:</span>
                                <span>{{ $product->pivot->quantity }}</span>
                            </div>
                            <div class="flex justify-between font-medium">
                                <span class="text-gray-400">Subtotal:</span>
                                <span>Rp. {{ number_format($product->pivot->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="bg-[#1e1e1e] rounded-lg p-4 text-center text-gray-400">
                        No items found
                    </div>
                    @endforelse

                    <!-- Mobile Order Summary -->
                    <div class="bg-[#1e1e1e] rounded-lg p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Subtotal:</span>
                            <span>Rp. {{ number_format($order->total - 30000, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Shipping:</span>
                            <span>Rp. 30.000</span>
                        </div>
                        <div class="border-t border-gray-700 pt-2 mt-2"></div>
                        <div class="flex justify-between font-bold">
                            <span>Total:</span>
                            <span>Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end">
                    <a href="javascript:history.back()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-md text-sm sm:text-base">
                        Back
                    </a>
                </div>
            </section>
            @else
            <div class="bg-[#292929] rounded-lg p-6 mx-4 sm:mx-8 mb-8 text-center">
                <p class="text-sm sm:text-base">No order selected. Please select an order from the <a href="{{ route('order.index') }}" class="text-blue-400 hover:underline">order list</a>.</p>
            </div>
            @endif
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
        Swal.fire({
            title: 'Berhasil!',
            text: '{{ session('
            success ') }}',
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
            background: '#1E1E1F',
            color: '#FFFFFF'
        });
        @endif
    });
</script>
@endpush