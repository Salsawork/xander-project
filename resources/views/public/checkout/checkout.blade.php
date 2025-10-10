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
                @php
                $images = $cart['images'] ? (is_array($cart['images']) ? $cart['images'] : json_decode($cart['images'], true)) : [];
                $firstImage = !empty($images) ? $images[0] : null;
                $idx = ($loop->index % 5) + 1;
                $defaultImg = asset("images/products/{$idx}.png");
                @endphp
                <div class="flex items-center space-x-4 mb-4">
                    <img src="{{ $firstImage ?? $defaultImg }}" alt="{{ $cart['name'] }}" class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $cart['name'] }}</h3>
                        @if(isset($cart['discount']) && $cart['discount'] > 0)
                        <p class="text-gray-400 line-through">Rp. {{ number_format($cart['price'], 0, ',', '.') }}</p>
                        <p class="text-green-400">Rp. {{ number_format($cart['price'] - ($cart['price'] * $cart['discount']), 0, ',', '.') }}</p>
                        @else
                        <p class="text-gray-400">Rp. {{ number_format($cart['price'], 0, ',', '.') }}</p>
                        @endif
                        <p class="text-gray-400">Quantity: {{ $cart['stock'] }}</p>
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
                    <img src="{{ $sparring['athlete_image'] ? asset('images/athlete/' . $sparring['athlete_image']) : 'https://placehold.co/400x400?text=No+Image' }}"
                        alt="{{ $sparring['athlete_name'] }}"
                        class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $sparring['athlete_name'] }}</h3>
                        <p class="text-gray-400">{{ \Carbon\Carbon::parse($sparring['date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($sparring['start'])->format('H:i') }} - {{ \Carbon\Carbon::parse($sparring['end'])->format('H:i') }}</p>
                        <p class="text-gray-400">Rp. {{ number_format($sparring['price'], 0, ',', '.') }}</p>
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
                <div class="flex items-center space-x-4 mb-4">
                    <img src="{{ $venue['image'] ?? 'https://placehold.co/400x400?text=No+Image' }}"
                        alt="{{ $venue['name'] }}"
                        class="w-16 h-16 object-cover rounded">
                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $venue['name'] }}</h3>

                        @if(isset($venue['table']))
                        <p class="text-gray-400">Table {{ $venue['table'] }}</p>
                        @endif

                        {{-- Jadwal dan Tanggal --}}
                        @if(isset($venue['date']) && isset($venue['start']) && isset($venue['end']))
                        <p class="text-gray-400">{{ \Carbon\Carbon::parse($venue['date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($venue['start'])->format('H:i') }} - {{ \Carbon\Carbon::parse($venue['end'])->format('H:i') }}</p>
                        @endif

                        {{-- Harga --}}
                        @if(isset($venue['price']))
                        <p class="text-gray-400">
                            Rp. {{ number_format($venue['price'], 0, ',', '.') }}
                        </p>
                        @endif
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Kolom Kiri: Form Checkout -->
            <div class="bg-[#2a2a2a] rounded-2xl shadow-xl p-6 space-y-6">
                <form id="checkout-form" enctype="multipart/form-data" method="POST">
                    @csrf
                    
                    <input type="hidden" name="tax" id="tax" value="{{ $tax }}">

                    <!-- Hidden input untuk produk -->
                    @foreach($carts as $index => $cart)
                    <input type="hidden" name="products[{{ $index }}][id]" value="{{ $cart['product_id'] }}">
                    <input type="hidden" name="products[{{ $index }}][quantity]" value="{{ $cart['quantity'] }}">
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
                    <input type="hidden" name="venues[0][table]" value="{{ $venue['table'] }}">
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

                    <!-- Shipping Information -->
                    @if(count($carts) > 0)
                    <div class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="province" class="block font-semibold mb-1">Province</label>
                                <select id="province" class="bg-neutral-800 text-white rounded p-2 w-full border">
                                    <option value="">Select Province</option>
                                </select>
                                <input type="hidden" name="province_name" id="province_name">
                            </div>
                            <div>
                                <label for="city" class="block font-semibold mb-1">City</label>
                                <select name="city" id="city" class="w-full border rounded p-2 bg-neutral-800 text-white">
                                    <option value="" disabled selected>Select City</option>
                                </select>
                                <input type="hidden" name="city_name" id="city_name">
                            </div>
                            <div>
                                <label for="district" class="block font-semibold mb-1">District</label>
                                <select id="district" class="bg-neutral-800 text-white rounded p-2 w-full border">
                                    <option value="">Select District</option>
                                </select>
                                <input type="hidden" name="district_name" id="district_name">
                            </div>
                            <div>
                                <label for="subdistrict" class="block font-semibold mb-1">Subdistrict</label>
                                <select name="subdistrict" id="subdistrict" class="w-full border rounded p-2 bg-neutral-800 text-white">
                                    <option value="" disabled selected>Select Subdistrict</option>
                                </select>
                                <input type="hidden" name="subdistrict_name" id="subdistrict_name">
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4">
                            <div>
                                <label for="courier" class="block font-semibold mb-1">Courier</label>
                                <select name="courier" id="courier" class="w-full border rounded p-2 bg-neutral-800 text-white">
                                    <option value="">Select Courier</option>
                                    <option value="jne">JNE</option>
                                    <option value="sicepat">SiCepat</option>
                                    <option value="jnt">JNT</option>
                                    <option value="anteraja">AnterAja</option>
                                    <option value="pos">POS</option>
                                </select>
                            </div>
                        </div>
                        <!-- Hidden input untuk cost -->
                        <input type="hidden" name="weight" id="weight" value="{{ $cart['weight'] }}">
                        <input type="hidden" name="shipping" id="shipping" value="0">
                    </div>
                    @endif

                    <!-- Payment Method -->
                    <div class="mb-6">
                        <label class="block font-semibold mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full border rounded p-2" required>
                            <option value="transfer_manual">Bank Transfer (Manual)</option>
                        </select>
                    </div>

                    <!-- Place Order Button -->
                    <div>
                        <button type="button" id="pay-button"
                            class="w-full bg-blue-600 text-white py-3 rounded font-semibold hover:bg-blue-700">
                            Place Order
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kolom Kanan: Order Summary & Return Policy -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="bg-[#2a2a2a] rounded-2xl ring-1 ring-white/10 shadow-xl p-6">
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                    <p>Subtotal: Rp {{ number_format($total, 0, ',', '.') }}</p>
                    <p>Shipping: <span class="shipping-value">Rp {{ number_format($shipping, 0, ',', '.') }}</span></p>
                    <p>Tax: Rp {{ number_format($tax, 0, ',', '.') }}</p>
                    <p class="font-bold mt-2">Grand Total: <span class="grand-total">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span></p>
                </div>


                <!-- Return Policy -->
                <div class="bg-[#2D2D2D] rounded-lg p-6">
                    <h2 class="text-xl font-bold mb-2">Return Policy</h2>
                    <p class="text-gray-400 text-sm">
                        If you need to return an item, you may do so within <span class="text-white font-medium">7 days of delivery</span>, provided it is unused, in its original packaging, and accompanied by proof of purchase.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", async () => {
        const provinceSelect = document.getElementById('province');
        const destinationSelect = document.getElementById('city');
        const districtSelect = document.getElementById('district');
        const subdistrictSelect = document.getElementById('subdistrict');
        const checkShippingBtn = document.getElementById('check-shipping');
        const resultDiv = document.getElementById('shipping-result');
        const payButton = document.getElementById('pay-button');
        const weightInput = document.getElementById('weight');
        const courierSelect = document.getElementById('courier');
        const hasProduct = {{count($carts) > 0 ? 'true' : 'false'}};
        if (hasProduct) {
            try {
                const res = await fetch('{{ route("rajaongkir.provinces") }}');
                const provinces = await res.json();
                provinceSelect.innerHTML = '<option value="">Select Province</option>';
                provinces.forEach(prov => {
                    const opt = document.createElement('option');
                    opt.value = prov.id;
                    opt.setAttribute('data-province', prov.name);
                    opt.textContent = prov.name;
                    provinceSelect.appendChild(opt);
                });
            } catch (error) {
                console.error('Gagal memuat provinsi:', error);
                provinceSelect.innerHTML = '<option value="">Failed to load provinces</option>';
            }

            provinceSelect.addEventListener('change', async () => {
                const provinceId = provinceSelect.value;
                const provinceName = provinceSelect.options[provinceSelect.selectedIndex]?.getAttribute('data-province') || '';

                document.getElementById('province_name').value = provinceName;

                if (!provinceId) {
                    destinationSelect.innerHTML = '<option value="">Select province first</option>';
                    return;
                }

                try {
                    const res = await fetch(`{{ route("rajaongkir.cities") }}?province=${provinceId}`);
                    const cities = await res.json();

                    destinationSelect.innerHTML = '<option value="">Select City</option>';
                    cities.forEach(city => {
                        const opt = document.createElement('option');
                        opt.value = city.id;
                        opt.setAttribute('data-city', city.name);
                        opt.textContent = city.name;
                        destinationSelect.appendChild(opt);
                    });
                } catch (error) {
                    console.error('Gagal memuat kota:', error);
                    destinationSelect.innerHTML = '<option value="">Failed to load cities</option>';
                }
            });

            destinationSelect.addEventListener('change', async () => {
                const cityId = destinationSelect.value;
                const cityName = destinationSelect.options[destinationSelect.selectedIndex]?.getAttribute('data-city') || '';

                document.getElementById('city_name').value = cityName;

                if (!cityId) {
                    districtSelect.innerHTML = '<option value="">Select city first</option>';
                    return;
                }

                try {
                    const res = await fetch(`{{ route("rajaongkir.districts") }}?city=${cityId}`);
                    const districts = await res.json();

                    districtSelect.innerHTML = '<option value="">Select district</option>';
                    districts.forEach(district => {
                        const opt = document.createElement('option');
                        opt.value = district.id;
                        opt.setAttribute('data-district', district.name);
                        opt.textContent = district.name;
                        districtSelect.appendChild(opt);
                    });
                } catch (error) {
                    console.error('Gagal memuat distrik:', error);
                    districtSelect.innerHTML = '<option value="">Failed to load districts</option>';
                }
            });

            districtSelect.addEventListener('change', async () => {
                const districtId = districtSelect.value;
                const districtName = districtSelect.options[districtSelect.selectedIndex]?.getAttribute('data-district') || '';

                document.getElementById('district_name').value = districtName;

                if (!districtId) {
                    subdistrictSelect.innerHTML = '<option value="">Select district first</option>';
                    return;
                }

                try {
                    const res = await fetch(`{{ route("rajaongkir.subdistricts") }}?district=${districtId}`);
                    const subdistricts = await res.json();

                    subdistrictSelect.innerHTML = '<option value="">Select subdistrict</option>';
                    subdistricts.forEach(subdistrict => {
                        const opt = document.createElement('option');
                        opt.value = subdistrict.id;
                        opt.setAttribute('data-subdistrict', subdistrict.name);
                        opt.textContent = subdistrict.name;
                        subdistrictSelect.appendChild(opt);
                    });
                } catch (error) {
                    console.error('Gagal memuat sub distrik:', error);
                    subdistrictSelect.innerHTML = '<option value="">Failed to load subdistricts</option>';
                }
            });

            subdistrictSelect.addEventListener('change', () => {
                const subdistrictName = subdistrictSelect.options[subdistrictSelect.selectedIndex]?.getAttribute('data-subdistrict') || '';
                document.getElementById('subdistrict_name').value = subdistrictName;
            });

            courierSelect.addEventListener('change', async () => {
                const courier = courierSelect.value;
                const districtId = districtSelect.value;
                const weight = weightInput.value;

                if (!courier || !districtId) return;

                try {
                    const res = await fetch('{{ route("rajaongkir.cost") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            origin: 1391,
                            destination: districtId,
                            weight,
                            courier
                        })
                    });

                    const data = await res.json();

                    if (data?.status === 'success' && Array.isArray(data.data) && data.data.length) {
                        const firstService = data.data[0];
                        const shippingCost = firstService.cost ?? 0;

                        // Update tampilan
                        const shippingValue = document.querySelector('.shipping-value');
                        const grandTotalElement = document.querySelector('.grand-total');
                        const currentSubtotal = {{$total}};
                        const currentTax = {{$tax}};
                        const grandTotal = currentSubtotal + currentTax + shippingCost;

                        if (shippingValue) shippingValue.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(shippingCost)}`;
                        if (grandTotalElement) grandTotalElement.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(grandTotal)}`;

                        document.getElementById('shipping').value = shippingCost;
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Shipping Unavailable',
                            text: 'Tidak ada layanan pengiriman tersedia untuk kurir ini.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                } catch (error) {
                    console.error('Gagal menghitung ongkir:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Shipping Error',
                        text: 'Terjadi kesalahan saat menghitung ongkir. Silakan coba lagi.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        }

        if (payButton) {
            payButton.addEventListener('click', async function() {
                if (hasProduct) {
                    const province = document.getElementById('province').value;
                    const city = document.getElementById('city').value;
                    const district = document.getElementById('district').value;
                    const subdistrict = document.getElementById('subdistrict').value;
                    const courier = document.getElementById('courier').value;
                    const shipping = document.getElementById('shipping').value;

                    if (!province || !city || !district || !subdistrict || !courier || !shipping || shipping === '0' ) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Shipping Required',
                            text: 'Please complete shipping information before placing order.',
                            confirmButtonColor: '#3085d6'
                        });
                        return;
                    }
                }

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
                        window.location.href = redirectUrl; // redirect ke halaman pembayaran
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Order Error',
                            text: data.message || 'Order gagal diproses.',
                            confirmButtonColor: '#3085d6'
                        });
                        btn.disabled = false;
                        btn.textContent = 'Place Order';
                    }
                } catch (err) {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Order Error',
                        text: 'Terjadi kesalahan saat memproses order. Silakan coba lagi.',
                        confirmButtonColor: '#3085d6'
                    });
                    btn.disabled = false;
                    btn.textContent = 'Place Order';
                }
            });
        }
    });
</script>

@endpush