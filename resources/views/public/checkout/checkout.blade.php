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
                @if(isset($venue))
                    <div class="flex items-center space-x-4">
                        <img src="{{ $venue['image'] ?? 'https://placehold.co/400x400?text=No+Image' }}" alt="{{ $venue['name'] }}" class="w-16 h-16 object-cover rounded">
                        <div class="flex-1">
                            <h3 class="font-semibold">{{ $venue['name'] }}</h3>
                            <p class="text-gray-400">{{ $venue['schedule'] }}</p>
                            <p class="text-gray-400">Rp. {{ number_format($venue['price'], 0, ',', '.') }},-</p>
                        </div>
                    </div>
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
                <form action="{{ route('checkout.store') }}" method="POST">
                    @csrf

                    <!-- Detail Penagihan -->
                    <div class="bg-[#2D2D2D] rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-bold mb-4">Billing Details</h2>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-400 mb-1">First Name</label>
                                <input type="text" name="first_name" id="first_name" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. John" required>
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-400 mb-1">Last Name</label>
                                <input type="text" name="last_name" id="last_name" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. Murphy" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-400 mb-1">Email</label>
                                <input type="email" name="email" id="email" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. JohnMurphy@email.com" required>
                                </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-400 mb-1">Phone Number</label>
                                <input type="tel" name="phone" id="phone" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. 08123456789" required>
                                </div>
                        </div>


                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium text-gray-400 mb-1">Address</label>
                            <input type="text" name="address" id="address" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. 789 Greenway Street, Apt 48" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-400 mb-1">Town/City</label>
                                <input type="text" name="city" id="city" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. Los Angeles" required>
                            </div>
                            <div>
                                <label for="zip" class="block text-sm font-medium text-gray-400 mb-1">Zip Code</label>
                                <input type="text" name="zip" id="zip" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. 90015" required>
                            </div>
                        </div>

                        <div>
                            <label for="note" class="block text-sm font-medium text-gray-400 mb-1">Note</label>
                            <textarea name="note" id="note" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. Please add extra bubble wrap"></textarea>
                        </div>
                    </div>

                    <!-- Metode Pengiriman & Pembayaran -->
                    <div class="bg-[#2D2D2D] rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-bold mb-4">Shipping Method</h2>

                        <div class="mb-6">
                            <p class="text-sm font-medium text-gray-400 mb-2">Choose Shipping</p>
                            <div class="flex space-x-4">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="shipping_method" value="express" class="w-4 h-4 text-blue-500 border-gray-600 bg-gray-700 focus:ring-blue-500">
                                    <span>Express</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="shipping_method" value="standard" class="w-4 h-4 text-blue-500 border-gray-600 bg-gray-700 focus:ring-blue-500" checked>
                                    <span>Standard</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-400 mb-2">Payment Method</p>
                            <p class="text-sm text-gray-400 mb-4">Available payment methods that you can choose after clicking the button below.</p>
                            <div class="grid grid-cols-5 gap-2">
                                <div class="flex items-center justify-center border border-gray-600 rounded-md p-2">
                                    <img src="{{ asset('images/payment/mastercard.png') }}" alt="Mastercard" class="h-8">
                                </div>
                                <div class="flex items-center justify-center border border-gray-600 rounded-md p-2">
                                    <img src="{{ asset('images/payment/dana.png') }}" alt="Dana" class="h-8">
                                </div>
                                <div class="flex items-center justify-center border border-gray-600 rounded-md p-2">
                                    <img src="{{ asset('images/payment/ovo.png') }}" alt="OVO" class="h-8">
                                </div>
                                <div class="flex items-center justify-center border border-gray-600 rounded-md p-2">
                                    <img src="{{ asset('images/payment/gopay.png') }}" alt="Gopay" class="h-8">
                                </div>
                                <div class="flex items-center justify-center border border-gray-600 rounded-md p-2">
                                    <span class="text-xs text-center">Dan metode lainnya</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1 space-y-6">
                    <!-- Ringkasan Pembayaran -->
                    <div class="bg-[#2D2D2D] rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-bold mb-4">Payment Summary</h2>

                        <div class="mb-4">
                            <label for="promo_code" class="block text-sm font-medium text-gray-400 mb-1">Promo Code (Optional)</label>
                            <div class="flex">
                                <input type="text" name="promo_code" id="promo_code" class="flex-1 bg-gray-700 border border-gray-600 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex. XANDERDISC">
                                <button type="button" class="bg-blue-500 hover:bg-blue-600 text-white px-4 rounded-md ml-4">Use</button>
                            </div>
                        </div>

                        <div class="space-y-2 mt-4">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Subtotal</span>
                                <span>Rp. {{ number_format($total, 0, ',', '.') }},-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Shipping</span>
                                <span>Rp. {{ number_format($shipping, 0, ',', '.') }},-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Tax</span>
                                <span>Rp. {{ number_format($tax, 0, ',', '.') }},-</span>
                            </div>
                            <div class="border-t border-gray-700 my-2 pt-2 flex justify-between font-bold">
                                <span>Order Total</span>
                                <span>Rp. {{ number_format($grandTotal, 0, ',', '.') }},-</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="condition">
                                Payment Method
                            </label>
                            <select name="payment_method"
                                id="payment_method"
                                required
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option disabled selected>Please choose payment method</option>
                                <option value="manual">Manual</option>
                                <option value="midtrans">Midtrans</option>
                            </select>
                        </div>

                        <button type="button" id="pay-button" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 rounded-md mt-4">
                            Choose Payment Method
                        </button>
                    </div>

                    <!-- Kebijakan Pengembalian -->
                    <div class="bg-[#2D2D2D] rounded-lg p-6">
                        <h2 class="text-xl font-bold mb-2">Return Policy</h2>
                        <p class="text-gray-400 text-sm">
                            If you need to return an item, you may do so within <span class="text-white font-medium">7 days of delivery</span>, provided it is unused, in its original packaging, and accompanied by proof of purchase.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
<script>
    // Fungsi untuk menangani checkbox payment method
    document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Reset border untuk semua
            document.querySelectorAll('input[name="payment_method"]').forEach(function(r) {
                r.parentElement.classList.remove('border-blue-500');
                r.parentElement.classList.add('border-gray-600');
            });

            // Set border untuk yang dipilih
            if (this.checked) {
                this.parentElement.classList.remove('border-gray-600');
                this.parentElement.classList.add('border-blue-500');
            }
        });
    });

    // Handle pay button
    let payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function() {
        // Disable the button during processing
        payButton.disabled = true;
        payButton.textContent = 'Processing...';

        // Get form data
        let form = document.querySelector('form');
        let formData = new FormData(form);

        // Send AJAX request to create order
        fetch('{{ route('checkout.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.snap_token == 'MANUAL_PAYMENT') {
                    // Handle manual payment
                    alert('Please complete the payment manually. Your order ID is: ' + data.order_id);
                    // Redirect to finish page with order ID
                    window.location.href = '{{ route('checkout.payment') }}?order_id=' + data.order_id;
                } else {
                    // Open Snap payment page with the token
                    snap.pay(data.snap_token, {
                        // Optional: Customize appearance
                        onSuccess: function(result) {
                            // Handle success, redirect to success page
                            window.location.href = '{{ route('checkout.finish') }}?order_id=' + data.order_id + '&transaction_status=capture';
                        },
                        onPending: function(result) {
                            // Handle pending, redirect to pending page
                            window.location.href = '{{ route('checkout.finish') }}?order_id=' + data.order_id + '&transaction_status=pending';
                        },
                        onError: function(result) {
                            // Handle error, redirect to error page
                            window.location.href = '{{ route('checkout.finish') }}?order_id=' + data.order_id + '&transaction_status=error';
                        },
                        onClose: function() {
                            // Handle customer closed the popup without finishing payment
                            payButton.disabled = false;
                            payButton.textContent = 'Choose Payment Method';
                            console.log('Customer closed the popup without finishing payment');
                            // Tidak perlu redirect, biarkan user tetap di halaman checkout
                        }
                    });
                }
            } else {
                // Handle error
                alert('Error: ' + (data.message || 'Failed to process payment'));
                payButton.disabled = false;
                payButton.textContent = 'Choose Payment Method';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            payButton.disabled = false;
            payButton.textContent = 'Choose Payment Method';
        });
    });
</script>
@endpush
