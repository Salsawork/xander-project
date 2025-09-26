@extends('app')
@section('title', 'Admin Dashboard - Detail Order')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-2xl font-bold p-8 mt-12">Detail Order</h1>
                
                @if(isset($order))
                <section class="bg-[#292929] rounded-lg p-6 mx-8 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-xl font-bold">Order #{{ $order->id }}</h2>
                            <p class="text-gray-400 text-sm">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <span class="{{ $statusClass[$order->delivery_status] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full inline-block min-w-[80px] text-center">
                                {{ ucfirst($order->delivery_status) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Customer Information</h3>
                            <div class="bg-[#1e1e1e] rounded-lg p-4">
                                <p><span class="text-gray-400">Name:</span> {{ $order->user->name ?? 'Guest' }}</p>
                                <p><span class="text-gray-400">Email:</span> {{ $order->user->email ?? 'N/A' }}</p>
                                <p><span class="text-gray-400">Phone:</span> {{ $order->user->phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Payment Information</h3>
                            <div class="bg-[#1e1e1e] rounded-lg p-4">
                                <p><span class="text-gray-400">Payment Method:</span> {{ $order->payment_method }}</p>
                                <p><span class="text-gray-400">Payment Status:</span> 
                                    <span class="{{ $order->payment_status == 'paid' ? 'text-green-500' : ($order->payment_status == 'pending' ? 'text-yellow-500' : 'text-red-500') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </p>
                                <p><span class="text-gray-400">Total:</span> Rp. {{ number_format($order->total, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-semibold mb-2">Order Items</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-gray-300 border-collapse">
                            <thead class="bg-[#1e1e1e] text-sm font-normal">
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3">Price</th>
                                    <th class="px-4 py-3">Quantity</th>
                                    <th class="px-4 py-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm font-normal">
                                @forelse($order->products as $product)
                                    <tr class="border-b border-gray-700">
                                        <td class="px-4 py-3 flex items-center gap-3">
                                            @php
                                                $imagePath = 'https://placehold.co/100x100?text=No+Image';
                                                if ($product->images) {
                                                    $images = is_array($product->images) ? $product->images : json_decode($product->images, true);
                                                    if ($images && count($images) > 0) {
                                                        $imagePath = asset('storage/uploads/' . $images[0]);
                                                    }
                                                }
                                            @endphp
                                            <img src="{{ $imagePath }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-md">
                                            <div>
                                                <p class="font-medium">{{ $product->name }}</p>
                                                <p class="text-xs text-gray-400">ID: {{ $product->id }}</p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">Rp. {{ number_format($product->pivot->price, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3">{{ $product->pivot->quantity }}</td>
                                        <td class="px-4 py-3">Rp. {{ number_format($product->pivot->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr class="border-b border-gray-700">
                                        <td colspan="4" class="px-4 py-3 text-center">No items found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-[#1e1e1e] font-medium">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right">Subtotal:</td>
                                    <td class="px-4 py-3">Rp. {{ number_format($order->total - 30000, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right">Shipping:</td>
                                    <td class="px-4 py-3">Rp. 30.000</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold">Total:</td>
                                    <td class="px-4 py-3 font-bold">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="mt-6 flex justify-end gap-4">
                        <a href="{{ route('order.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-md">Back to Orders</a>
                    </div>
                </section>
                @else
                <div class="bg-[#292929] rounded-lg p-6 mx-8 mb-8 text-center">
                    <p>No order selected. Please select an order from the <a href="{{ route('order.index') }}" class="text-blue-400 hover:underline">order list</a>.</p>
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
                text: '{{ session('success') }}',
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