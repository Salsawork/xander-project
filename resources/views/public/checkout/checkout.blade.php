@extends('app')

@section('title', 'Checkout - Xander Billiard')

@section('content')
<div class="flex min-h-screen bg-[#1E1E1F] text-white">
    <!-- Kolom Kiri: Order Summary -->
    <div class="w-1/4 bg-[#2D2D2D] p-8 border-r border-gray-700">
        <div class="flex items-center mb-8">
            <a href="javascript:history.back()" class="text-gray-500 hover:text-gray-400 mr-4">
                <i class="fas fa-chevron-left"></i>
            </a>
            <h1 class="text-2xl font-bold">Order Summary</h1>
        </div>

        <hr class="my-4 border-gray-600">

        <div class="space-y-8">
            <!-- Produk -->
            <div>
                <h2 class="text-lg font-bold mb-4">Product</h2>
                @forelse ($carts as $cart)
                <div class="flex items-center space-x-4 mb-4">
                    <img src="{{ $cart['image'] ?? 'https://placehold.co/400x400?text=No+Image' }}" alt="{{ $cart['name'] }}" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $cart['name'] }}</h3>
                        <p class="text-gray-400">Rp. {{ number_format($cart['price'], 0, ',', '.') }},-</p>
                    </div>
                </div>
                @empty
                <p class="text-gray-400">No products in cart</p>
                @endforelse
            </div>

            <!-- Sparring -->
            <div>
                <h2 class="text-lg font-bold mb-4">Sparring</h2>
                @if(isset($sparrings) && count($sparrings) > 0)
                @foreach($sparrings as $sparring)
                <div class="flex items-center space-x-4 mb-4">
                    <img src="{{ $sparring['image'] ? asset('images/athlete/' . $sparring['image']) : 'https://placehold.co/400x400?text=No+Image' }}"
                        alt="{{ $sparring['name'] }}"
                        class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $sparring['name'] }}</h3>
                        <p class="text-gray-400">{{ $sparring['schedule'] }}</p>
                        <p class="text-gray-400">Rp. {{ number_format($sparring['price'], 0, ',', '.') }},-</p>
                    </div>
                </div>
                @endforeach
                @else
                <p class="text-gray-400">No sparring session selected</p>
                @endif
            </div>

            <!-- Venue -->
            <div>
                <h2 class="text-lg font-bold mb-4">Venue</h2>
                @if(isset($venues) && count($venues) > 0)
                @foreach($venues as $venue)
                <div class="flex items-center space-x-4">
                    <img src="{{ $venue['image'] ?? 'https://placehold.co/400x400?text=No+Image' }}" alt="{{ $venue['name'] }}" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $venue['name'] }}</h3>
                        <p class="text-gray-400">{{ $venue['start'] }} - {{ $venue['end'] }}</p>
                        <p class="text-gray-400">Rp. {{ number_format($venue['price'], 0, ',', '.') }},-</p>
                    </div>
                </div>
                @endforeach
                @else
                <p class="text-gray-400">No venue selected</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Checkout Form -->
    <div class="w-2/3 p-8">
        <h1 class="text-3xl font-bold mb-8">Checkout</h1>

        <hr class="my-4 border-gray-600">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Form Checkout -->
            <div class="md:col-span-1 space-y-6">
                <form id="checkout-form" enctype="multipart/form-data" method="POST">
                    @csrf

                    <!-- Hidden input untuk produk -->
                    @foreach($carts as $index => $cart)
                    <input type="hidden" name="products[{{ $index }}][id]" value="{{ $cart['id'] }}">
                    <input type="hidden" name="products[{{ $index }}][qty]" value="1">
                    @endforeach

                    <!-- Hidden input untuk sparring -->
                    @if(isset($sparrings))
                    @foreach($sparrings as $index => $sparring)
                    <input type="hidden" name="sparrings[{{ $index }}][athlete_id]" value="{{ $sparring['athlete_id'] }}">
                    <input type="hidden" name="sparrings[{{ $index }}][schedule_id]" value="{{ $sparring['schedule_id'] }}">
                    <input type="hidden" name="sparrings[{{ $index }}][price]" value="{{ $sparring['price'] }}">
                    @endforeach
                    @endif

                    <!-- Hidden input untuk venue -->
                    @if(isset($venue))
                    <input type="hidden" name="venues[0][id]" value="{{ $venue['id'] }}">
                    <input type="hidden" name="venues[0][price]" value="{{ $venue['price'] }}">
                    <input type="hidden" name="venues[0][date]" value="{{ $venue['date'] }}">
                    <input type="hidden" name="venues[0][start]" value="{{ $venue['start'] }}">
                    <input type="hidden" name="venues[0][end]" value="{{ $venue['end'] }}">
                    @endif

                    <!-- Billing Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="firstname" class="block font-semibold mb-1">First Name</label>
                            <input type="text" name="firstname" id="firstname"
                                value="{{ old('firstname', $user->firstname ?? '') }}"
                                class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label for="lastname" class="block font-semibold mb-1">Last Name</label>
                            <input type="text" name="lastname" id="lastname"
                                value="{{ old('lastname', $user->lastname ?? '') }}"
                                class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label for="email" class="block font-semibold mb-1">Email</label>
                            <input type="email" name="email" id="email"
                                value="{{ old('email', $user->email ?? '') }}"
                                class="w-full border rounded p-2" required>
                        </div>
                        <div>
                            <label for="phone" class="block font-semibold mb-1">Phone</label>
                            <input type="tel" name="phone" id="phone"
                                value="{{ old('phone', $user->phone ?? '') }}"
                                class="w-full border rounded p-2" required>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-6">
                        <label class="block font-semibold mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full border rounded p-2" required>
                            <option value="transfer_manual">Bank Transfer (Manual)</option>
                        </select>
                    </div>

                    <!-- FIle -->
                    <div class="mb-6">
                        <label class="block font-semibold mb-1">File</label>
                        <input type="file" name="file" class="w-full border rounded p-2" required>
                    </div>

                    <!-- Order Summary -->
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold mb-2">Order Summary</h2>
                        <p>Subtotal: Rp {{ number_format($total, 0, ',', '.') }}</p>
                        <p>Shipping: Rp {{ number_format($shipping, 0, ',', '.') }}</p>
                        <p>Tax: Rp {{ number_format($tax, 0, ',', '.') }}</p>
                        <p class="font-bold">Grand Total: Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                    </div>

                    <!-- Kebijakan Pengembalian -->
                    <div class="bg-[#2D2D2D] rounded-lg p-6">
                        <h2 class="text-xl font-bold mb-2">Return Policy</h2>
                        <p class="text-gray-400 text-sm">
                            If you need to return an item, you may do so within <span class="text-white font-medium">7 days of delivery</span>, provided it is unused, in its original packaging, and accompanied by proof of purchase.
                        </p>
                    </div>
                    <div class="p-6">
                        <button type="button" id="pay-button"
                            class="w-full bg-blue-600 text-white py-3 rounded font-semibold hover:bg-blue-700">
                            Place Order
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('pay-button').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Processing...';

        const form = document.getElementById('checkout-form');
        const formData = new FormData(form);

        try {
            const res = await fetch('{{ route("checkout.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                console.log("Redirecting to payment page with order:", data.order_number);
                const redirectUrl = '{{ route("checkout.payment") }}?order_number=' + data.order_number;
                window.location.href = redirectUrl; // ✅ langsung redirect
            } else {
                alert(data.message || 'Order failed');
                btn.disabled = false;
                btn.textContent = 'Place Order';
            }
        } catch (err) {
            console.error(err);
            alert('Something went wrong');
            btn.disabled = false;
            btn.textContent = 'Place Order';
        }
    });
</script>



@endsection