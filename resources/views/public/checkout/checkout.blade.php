@extends('app')

@section('title', 'Checkout - Xander Billiard')

@push('styles')
<style>
  :root{
    color-scheme: dark;
    --page-bg:#1E1E1F;     /* warna latar halaman */
    --panel-bg:#2D2D2D;    /* warna panel kiri */
  }
  /* Matikan rubber-band & pastikan background tidak pernah putih */
  html, body{
    height:100%;
    min-height:100%;
    background:var(--page-bg);
    overscroll-behavior: none;        /* cegah bounce di viewport */
    touch-action: pan-y;
    -webkit-text-size-adjust:100%;
  }
  body{ overflow-x:hidden; }

  /* Latar tetap yang meluber melewati viewport agar tidak ada putih saat bounce */
  #antiBounceBg{
    position:fixed;
    left:0; right:0;
    top:-120svh;       /* svh untuk iOS dynamic toolbar */
    bottom:-120svh;
    background:var(--page-bg);
    z-index:-1;
    pointer-events:none;
  }

  /* Kontainer utama: jadikan scroll-safe */
  #checkoutPage{
    background:var(--page-bg);
    overscroll-behavior: contain;    /* tahan bounce di dalam kontainer */
    -webkit-overflow-scrolling: touch;
  }
</style>
@endpush

@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\CartItem;
    use Carbon\Carbon;

    $user = Auth::user();

    // --- Parse selections from GET ---
    $selected = collect(request()->get('selected_items', []))
        ->map(function ($raw) {
            [$type, $id] = array_pad(explode(':', (string)$raw, 2), 2, null);
            return ['type' => $type, 'id' => is_numeric($id) ? (int)$id : null];
        })
        ->filter(fn($x) => in_array($x['type'], ['product','venue','sparring']) && $x['id'])
        ->values();

    $selectedImages = (array) request()->get('selected_images', []); // key "venue:<cart_id>" => "filename.ext"

    $idsProduct  = $selected->where('type','product')->pluck('id')->all();
    $idsVenue    = $selected->where('type','venue')->pluck('id')->all();
    $idsSparring = $selected->where('type','sparring')->pluck('id')->all();

    // --- Fetch Cart Items ---
    $cartProducts = collect();
    $cartVenues   = collect();
    $cartSparrings= collect();

    if (!empty($idsProduct)) {
        $cartProducts = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->whereIn('id', $idsProduct)
            ->get()
            ->map(function ($item) {
                $first = null;
                $raw = $item->product?->images ?? null;
                if (is_string($raw)) {
                    $maybe = json_decode($raw, true);
                    $first = (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) ? ($maybe[0] ?? null) : $raw;
                } elseif (is_array($raw)) {
                    $first = $raw[0] ?? null;
                }
                return [
                    'cart_id'    => $item->id,
                    'product_id' => $item->product?->id,
                    'name'       => $item->product?->name,
                    'price'      => $item->product?->pricing,
                    'quantity'   => $item->quantity,
                    'discount'   => $item->product?->discount ?? 0,
                    'images'     => $first,
                    'weight'     => $item->product?->weight ?? 0,
                ];
            });
    }

    if (!empty($idsVenue)) {
        $cartVenues = CartItem::with('venue')
            ->where('user_id', Auth::id())
            ->whereIn('id', $idsVenue)
            ->get()
            ->map(function ($item) use ($selectedImages) {
                $key = 'venue:' . $item->id;
                $filenameFromCart = $selectedImages[$key] ?? '';

                // fallback baca dari model (image / images[0])
                $raw = $item->venue?->image;
                if (!$raw && !empty($item->venue?->images)) {
                    $img = $item->venue->images;
                    if (is_array($img)) { $raw = $img[0] ?? null; }
                    elseif (is_string($img)) {
                        $maybe = json_decode($img, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) { $raw = $maybe[0] ?? null; }
                    }
                }
                $basename = '';
                if (!empty($filenameFromCart)) {
                    $basename = basename($filenameFromCart);
                } elseif (!empty($raw)) {
                    if (filter_var($raw, FILTER_VALIDATE_URL)) {
                        $basename = basename(parse_url($raw, PHP_URL_PATH));
                    } else {
                        $basename = basename(str_replace('\\','/',$raw));
                    }
                }

                return [
                    'cart_id'  => $item->id,
                    'id'       => $item->venue?->id,
                    'name'     => $item->venue?->name,
                    'date'     => $item->date,
                    'start'    => $item->start,
                    'end'      => $item->end,
                    'table'    => $item->table_number,
                    'price'    => $item->price,
                    'image'    => $basename, // filename saja → FE build URL
                ];
            });
    }

    if (!empty($idsSparring)) {
        $cartSparrings = CartItem::with(['sparringSchedule.athlete'])
            ->where('user_id', Auth::id())
            ->whereIn('id', $idsSparring)
            ->get()
            ->map(function ($item) {
                return [
                    'cart_id'       => $item->id,
                    'schedule_id'   => $item->sparringSchedule?->id,
                    'athlete_name'  => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'          => $item->date,
                    'start'         => $item->start,
                    'end'           => $item->end,
                    'price'         => $item->price,
                ];
            });
    }

    // --- Summary ---
    $carts   = $cartProducts;
    $venues  = $cartVenues;
    $sparrings = $cartSparrings;

    $totalProducts = $carts->sum(fn($x) => ($x['price'] - ($x['price'] * ($x['discount'] ?? 0))) * ($x['quantity'] ?? 1));
    $totalVenues   = $venues->sum('price');
    $totalSpar     = $sparrings->sum('price');

    $total = (int) ($totalProducts + $totalVenues + $totalSpar);
    $tax = (int) round($total * 0.0);  // sesuaikan aturan pajak
    $shipping = 0;
    $grandTotal = $total + $tax + $shipping;
    $venueDiscount = 0;

    $totalWeight = $carts->sum('weight');
@endphp

@section('content')
{{-- Latar anti bounce --}}
<div id="antiBounceBg"></div>

<div id="checkoutPage" class="flex min-h-screen bg-[#1E1E1F] text-white">
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
                        $firstImageRaw = is_array($images) ? ($images[0] ?? null) : $cart['images'];
                        $firstImage = null;
                        if ($firstImageRaw) {
                            $firstImage = filter_var($firstImageRaw, FILTER_VALIDATE_URL)
                                ? $firstImageRaw
                                : ('https://xanderbilliard.site/images/products/' . basename(str_replace('\\','/',$firstImageRaw)));
                        }
                        $idx = ($loop->index % 5) + 1;
                        $defaultImg = asset("images/products/{$idx}.png");
                    @endphp
                    <div class="flex items-center space-x-4 mb-4">
                        <img src="{{ $firstImage ?? $defaultImg }}" alt="{{ $cart['name'] }}" class="w-16 h-16 object-cover rounded"
                             onerror="this.onerror=null;this.src='{{ asset('images/placeholder/product.png') }}'">
                        <div class="flex-1">
                            <h3 class="font-semibold">{{ $cart['name'] }}</h3>
                            @if(isset($cart['discount']) && $cart['discount'] > 0)
                                <p class="text-gray-400 line-through">Rp. {{ number_format($cart['price'], 0, ',', '.') }}</p>
                                <p class="text-green-400">Rp. {{ number_format($cart['price'] - ($cart['price'] * $cart['discount']), 0, ',', '.') }}</p>
                            @else
                                <p class="text-gray-400">Rp. {{ number_format($cart['price'], 0, ',', '.') }}</p>
                            @endif
                            <p class="text-gray-400">Quantity: {{ $cart['quantity'] }}</p>
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
                        @php
                            $raw = $sparring['athlete_image'] ?? null;
                            $filename = $raw
                                ? (filter_var($raw, FILTER_VALIDATE_URL)
                                    ? basename(parse_url($raw, PHP_URL_PATH))
                                    : basename(str_replace('\\','/',$raw)))
                                : null;
                            $img = $filename
                                ? ('https://xanderbilliard.site/images/athlete/' . $filename)
                                : asset('images/placeholder/athlete.png');
                        @endphp
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="{{ $img }}" alt="{{ $sparring['athlete_name'] }}" class="w-16 h-16 object-cover rounded"
                                 onerror="this.onerror=null;this.src='{{ asset('images/placeholder/athlete.png') }}'">
                            <div class="flex-1">
                                <h3 class="font-semibold">{{ $sparring['athlete_name'] }}</h3>
                                <p class="text-gray-400">
                                    {{ \Carbon\Carbon::parse($sparring['date'])->format('d M Y') }}
                                    - {{ \Carbon\Carbon::parse($sparring['start'])->format('H:i') }}
                                    - {{ \Carbon\Carbon::parse($sparring['end'])->format('H:i') }}
                                </p>
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
                        @php
                            $cdnBase = 'https://xanderbilliard.site/images/venue/';
                            $filename = trim((string)($venue['image'] ?? ''));
                            $venueImg = $filename !== '' ? ($cdnBase . basename($filename)) : asset('images/placeholder/venue.png');
                            $fallbackVenue = asset('images/placeholder/venue.png');
                        @endphp
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="{{ $venueImg }}" alt="{{ $venue['name'] }}"
                                 class="w-16 h-16 object-cover rounded"
                                 onerror="this.onerror=null;this.src='{{ $fallbackVenue }}'">
                            <div class="flex-1">
                                <h3 class="font-semibold">{{ $venue['name'] }}</h3>

                                @if(isset($venue['table']))
                                    <p class="text-gray-400">Table {{ $venue['table'] }}</p>
                                @endif

                                @if(isset($venue['date']) && isset($venue['start']) && isset($venue['end']))
                                    <p class="text-gray-400">
                                        {{ \Carbon\Carbon::parse($venue['date'])->format('d M Y') }}
                                        - {{ \Carbon\Carbon::parse($venue['start'])->format('H:i') }}
                                        - {{ \Carbon\Carbon::parse($venue['end'])->format('H:i') }}
                                    </p>
                                @endif

                                @if(isset($venue['price']))
                                    <p class="text-gray-400">Rp. {{ number_format($venue['price'], 0, ',', '.') }}</p>
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
                <form id="checkout-form" enctype="multipart/form-data" method="POST" action="{{ route('checkout.store') }}">
                    @csrf

                    <input type="hidden" name="tax" id="tax" value="{{ $tax }}">

                    <!-- Hidden input untuk produk -->
                    @foreach($carts as $index => $cart)
                        <input type="hidden" name="products[{{ $index }}][id]" value="{{ $cart['product_id'] }}">
                        <input type="hidden" name="products[{{ $index }}][quantity]" value="{{ $cart['quantity'] }}">
                    @endforeach

                    <!-- Hidden input untuk sparring -->
                    @foreach($sparrings as $index => $sparring)
                        <input type="hidden" name="sparrings[{{ $index }}][schedule_id]" value="{{ $sparring['schedule_id'] }}">
                        <input type="hidden" name="sparrings[{{ $index }}][price]" value="{{ $sparring['price'] }}">
                    @endforeach

                    <!-- Hidden input untuk venue (SERTAKAN IMAGE) -->
                    @foreach($venues as $index => $v)
                        <input type="hidden" name="venues[{{ $index }}][id]" value="{{ $v['id'] }}">
                        <input type="hidden" name="venues[{{ $index }}][price]" value="{{ $v['price'] }}">
                        <input type="hidden" name="venues[{{ $index }}][date]" value="{{ $v['date'] }}">
                        <input type="hidden" name="venues[{{ $index }}][table]" value="{{ $v['table'] ?? '' }}">
                        <input type="hidden" name="venues[{{ $index }}][start]" value="{{ $v['start'] }}">
                        <input type="hidden" name="venues[{{ $index }}][end]" value="{{ $v['end'] }}">
                        <input type="hidden" name="venues[{{ $index }}][image]" value="{{ $v['image'] ?? '' }}">
                    @endforeach

                    <!-- Billing Details -->
                    <div class="grid grid-cols-1 md-grid-cols-2 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="firstname" class="block font-semibold mb-1">First Name</label>
                            <input type="text" name="firstname" id="firstname"
                                   value="{{ old('firstname', $user->firstname ?? '') }}"
                                   class="w-full border rounded p-2 bg-neutral-800 text-white" required>
                        </div>
                        <div>
                            <label for="lastname" class="block font-semibold mb-1">Last Name</label>
                            <input type="text" name="lastname" id="lastname"
                                   value="{{ old('lastname', $user->lastname ?? '') }}"
                                   class="w-full border rounded p-2 bg-neutral-800 text-white" required>
                        </div>
                        <div>
                            <label for="email" class="block font-semibold mb-1">Email</label>
                            <input type="email" name="email" id="email"
                                   value="{{ old('email', $user->email ?? '') }}"
                                   class="w-full border rounded p-2 bg-neutral-800 text-white" required>
                        </div>
                        <div>
                            <label for="phone" class="block font-semibold mb-1">Phone</label>
                            <input type="tel" name="phone" id="phone"
                                   value="{{ old('phone', $user->phone ?? '') }}"
                                   class="w-full border rounded p-2 bg-neutral-800 text-white" required>
                        </div>
                    </div>

                    <!-- Shipping Information (khusus jika ada produk fisik) -->
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
                            <div class="mt-4">
                                <label for="address" class="block font-semibold mb-1">Address</label>
                                <textarea name="address" id="address" rows="3"
                                          class="w-full border rounded p-2 resize-none bg-neutral-800 text-white"
                                          required>{{ old('address', $user->address ?? '') }}</textarea>
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
                                <div>
                                    <label for="payment_method" class="block font-semibold mb-1">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="w-full border rounded p-2 bg-neutral-800 text-white" required>
                                        <option value="transfer_manual">Bank Transfer (Manual)</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden input untuk cost -->
                            <input type="hidden" name="weight" id="weight" value="{{ (int) $totalWeight }}">
                            <input type="hidden" name="shipping" id="shipping" value="0">
                        </div>
                    @else
                        <!-- Payment Method untuk non-product checkout -->
                        <div class="mb-6">
                            <label class="block font-semibold mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full border rounded p-2 bg-neutral-800 text-white" required>
                                <option value="transfer_manual">Bank Transfer (Manual)</option>
                            </select>
                        </div>
                    @endif

                    <!-- Place Order Button -->
                    <div>
                        <button type="submit" id="pay-button"
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
                    @if(count($carts) > 0)
                        <p>Shipping: <span class="shipping-value">Rp {{ number_format($shipping, 0, ',', '.') }}</span></p>
                        <p>Tax: Rp {{ number_format($tax, 0, ',', '.') }}</p>
                    @endif
                    @if(isset($venues) && count($venues) > 0 && ($venueDiscount ?? 0) > 0)
                        <p>Discount: Rp {{ number_format($venueDiscount, 0, ',', '.') }}</p>
                    @endif
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
        const payButton = document.getElementById('pay-button');
        const weightInput = document.getElementById('weight');
        const courierSelect = document.getElementById('courier');
        const hasProduct = {{ count($carts) > 0 ? 'true' : 'false' }};

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
                            origin: 1391, // sesuaikan origin ID gudangmu
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
                        const currentSubtotal = {{ $total }};
                        const currentTax = {{ $tax }};
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

                    if (!province || !city || !district || !subdistrict || !courier || !shipping || shipping === '0') {
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
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });

                    const data = await res.json();

                    if (res.ok && data.status === 'success') {
                        const redirectUrl = '{{ route("checkout.payment") }}?order_number=' + data.order_number;
                        window.location.href = redirectUrl;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Order Error',
                            text: data.error || 'Order gagal diproses.',
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
