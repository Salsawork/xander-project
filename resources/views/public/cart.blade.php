<style>
    :root { --cart-header-h: 64px; } /* default header height */

    /* PANEL */
    #cart{
        position: fixed;
        top: 0;
        right: 0;
        width: min(420px, 100vw);       /* desktop ~420px, mobile full width */
        height: 100vh;                  /* penuh layar */
        background-color:#1e1e1f;
        overflow: hidden;               /* body yang scroll, bukan panel */
        z-index: 50;
        /* cegah body ikut scroll saat panel di-scroll */
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        box-shadow: -8px 0 24px rgba(0,0,0,.35);
    }

    /* KONTEN DI ATAS BACKGROUND */
    #cart .cart-header,
    #cart .cart-body { position: relative; z-index: 1; }

    /* HEADER */
    #cart .cart-header{
        height: var(--cart-header-h);
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 1rem;
        background:#1e1e1f;
        border-bottom: 1px solid rgba(255,255,255,.08);
        position: sticky;   /* sticky agar selalu terlihat ketika isi scroll */
        top: 0;
    }
    #cart .cart-title{ font-size:1.25rem; font-weight:800; line-height:1.25; }

    /* BODY: selalu scroll (desktop & mobile) */
    #cart .cart-body{
        height: calc(100vh - var(--cart-header-h));
        overflow-y: auto;
        padding: 16px 20px;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    /* Section & item */
    #cart .cart-section-title { font-size:1.1rem; margin-bottom:.75rem; font-weight:800; }
    #cart .cart-item { gap:16px; }
    #cart .cart-img { width:80px; height:80px; border-radius:.75rem; object-fit:cover; flex-shrink:0; }
    #cart .cart-name { font-size:1.05rem; line-height:1.2; }
    #cart .cart-meta { font-size:.8rem; }
    #cart .cart-price { font-size:.95rem; }
    #cart .cart-total-row { margin-bottom:.75rem; }
    #cart .checkout-btn { height:46px; border-radius:.6rem; font-weight:700; }

    /* MOBILE: hanya tweak ukuran */
    @media (max-width: 640px){
        #cart{ width:100vw; max-width:100vw; }
        #cart .cart-img { width:64px; height:64px; border-radius:.5rem; }
        #cart .cart-name{
            font-size:.95rem;
            display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
        }
        #cart .cart-meta{ font-size:.75rem; }
        #cart .cart-price{ font-size:.875rem; }
        #cart .cart-title{ font-size:1.0625rem; }
    }
</style>

<div class="hidden" id="cart"> {{-- tetap pakai .hidden utk toggle buka/tutup --}}
    <header class="cart-header">
        <button aria-label="Back" class="text-gray-500 hover:text-gray-400 focus:outline-none" onclick="showCart()">
            <i class="fas fa-arrow-left text-xl"></i>
        </button>
        <h2 class="text-white cart-title">Your Cart</h2>
    </header>

    <div class="cart-body">
        <div class="mb-6">
            <h3 class="text-white cart-section-title">Items</h3>

            <ul class="space-y-4">
                {{-- Product --}}
                @forelse ($cartProducts as $cart)
                    @php
                        $raw = $cart['images'] ?? null;
                        $first = null;
                        if (is_string($raw)) {
                            $maybe = json_decode($raw, true);
                            $first = (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) ? ($maybe[0] ?? null) : $raw;
                        } elseif (is_array($raw)) { $first = $raw[0] ?? null; }
                        if (is_string($first)) { $first = str_replace('\\','/',$first); }
                        $filename = $first ? basename($first) : null;
                        $imageUrl = $filename
                            ? ('https://demo-xanders.ptbmn.id/images/products/' . $filename)
                            : 'https://demo-xanders.ptbmn.id/images/products/default.png';
                    @endphp
                    <li class="flex items-center cart-item">
                        <input type="checkbox" name="selected_items[]" data-type="product"
                               value="product:{{ $cart['cart_id'] }}" onchange="updateCartTotal()"
                               class="mr-2 w-5 h-5 border border-gray-600 rounded-sm" checked />
                        <img alt="{{ $cart['name'] }}" class="cart-img" src="{{ $imageUrl }}" />
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-white cart-name">{{ $cart['name'] }}</p>
                            <input type="hidden" id="price-{{ $cart['cart_id'] }}" value="{{ $cart['price'] }}">
                            <input type="hidden" id="discount-{{ $cart['cart_id'] }}" value="{{ $cart['discount'] ?? 0 }}">
                            <div class="text-sm mt-1 cart-price">
                                @if(isset($cart['discount']) && $cart['discount'] > 0)
                                    <div class="text-gray-400 line-through">Rp {{ number_format($cart['price'], 0, ',', '.') }} / item</div>
                                    <div class="text-green-400">Rp {{ number_format($cart['price'] - ($cart['price'] * $cart['discount']), 0, ',', '.') }} / item</div>
                                @else
                                    <div class="text-white">Rp {{ number_format($cart['price'], 0, ',', '.') }} / item</div>
                                @endif
                            </div>
                            <p class="text-white text-xs mt-1 cart-meta">Quantity: {{ $cart['quantity'] ?? 0 }}</p>
                        </div>
                        <form action="{{ route('cart.delete') }}" method="POST" class="delete-form">
                            @csrf
                            <input type="hidden" name="id" value="{{ $cart['cart_id'] }}">
                            <button type="submit" aria-label="Delete {{ $cart['name'] }}" class="text-gray-400 hover:text-red-500 focus:outline-none flex-shrink-0">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </button>
                        </form>
                    </li>
                @empty
                    @if(empty($cartSparrings) && empty($cartVenues) && empty($cartProducts))
                        <li class="text-center text-gray-500 py-4 min-w-xs cart-meta">Your cart is empty</li>
                    @endif
                @endforelse

                {{-- Venue --}}
                @forelse ($cartVenues ?? [] as $venue)
                    @php
                        $rawVenue = $venue['image'] ?? null;
                        if (!$rawVenue && !empty($venue['venue_id'])) {
                            $__v = \App\Models\Venue::find($venue['venue_id']);
                            if ($__v) {
                                if (!empty($__v->images)) {
                                    if (is_array($__v->images)) { $rawVenue = $__v->images[0] ?? null; }
                                    elseif (is_string($__v->images)) {
                                        $maybe = json_decode($__v->images, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($maybe)) { $rawVenue = $maybe[0] ?? null; }
                                    }
                                }
                                if (!$rawVenue && !empty($__v->image)) { $rawVenue = $__v->image; }
                            }
                        }
                        $filenameVenue = $rawVenue ? basename(str_replace('\\','/',$rawVenue)) : null;

                        $venueImageUrl = asset('images/venue/default.png');
                        if ($filenameVenue) {
                            $feAbs  = base_path('../demo-xanders/images/venue/' . $filenameVenue);
                            $feLink = public_path('fe-venue');
                            if (\Illuminate\Support\Facades\File::exists($feAbs) && is_dir($feLink)) {
                                $venueImageUrl = asset('fe-venue/' . $filenameVenue);
                            } else {
                                $cmsAbs = public_path('images/venue/' . $filenameVenue);
                                if (\Illuminate\Support\Facades\File::exists($cmsAbs)) {
                                    $venueImageUrl = asset('images/venue/' . $filenameVenue);
                                } else {
                                    $storAbs = public_path('storage/uploads/' . $filenameVenue);
                                    if (\Illuminate\Support\Facades\File::exists($storAbs)) {
                                        $venueImageUrl = asset('storage/uploads/' . $filenameVenue);
                                    }
                                }
                            }
                        }
                    @endphp

                    <li class="flex items-center cart-item">
                        <input type="checkbox" name="selected_items[]" data-type="venue"
                               value="venue:{{ $venue['cart_id'] }}" onchange="updateCartTotal()"
                               class="mr-2 w-5 h-5 border border-gray-600 rounded-sm" checked />
                        <img class="cart-img" src="{{ $venueImageUrl }}" alt="{{ $venue['name'] }}" />
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-white cart-name">{{ $venue['name'] }}</p>
                            <p class="text-white text-xs mt-1 cart-meta">
                                {{ \Carbon\Carbon::parse($venue['date'])->format('d M Y') }}
                                {{ \Carbon\Carbon::parse($venue['start'])->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($venue['end'])->format('H:i') }}
                            </p>
                            <input type="hidden" id="price-venue-{{ $venue['cart_id'] }}" value="{{ $venue['price'] }}">
                            <p class="text-white text-sm mt-1 cart-price">Rp. {{ number_format($venue['price'], 0, ',', '.') }}</p>
                            <p class="text-white text-sm mt-1 cart-meta">Table {{ $venue['table'] }}</p>
                        </div>
                        <form action="{{ route('cart.delete') }}" method="POST" class="delete-form">
                            @csrf
                            <input type="hidden" name="id" value="{{ $venue['cart_id'] }}">
                            <button type="submit" aria-label="Delete {{ $venue['name'] }}" class="text-gray-400 hover:text-red-500 focus:outline-none">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </button>
                        </form>
                    </li>
                @empty
                @endforelse

                {{-- Sparring --}}
                @forelse ($cartSparrings ?? [] as $sparring)
                    @php
                        $rawAthlete = $sparring['athlete_image'] ?? null;
                        $filenameAthlete = null;
                        if (!empty($rawAthlete)) {
                            if (filter_var($rawAthlete, FILTER_VALIDATE_URL)) { $filenameAthlete = basename(parse_url($rawAthlete, PHP_URL_PATH)); }
                            else { $filenameAthlete = basename(str_replace('\\', '/', $rawAthlete)); }
                        }
                        $athleteImageUrl = $filenameAthlete
                            ? ('https://demo-xanders.ptbmn.id/images/athlete/' . $filenameAthlete)
                            : 'https://demo-xanders.ptbmn.id/images/athlete/default.png';
                    @endphp

                    <li class="flex items-center cart-item">
                        <input type="checkbox" name="selected_items[]" data-type="sparring"
                               value="sparring:{{ $sparring['cart_id'] }}" onchange="updateCartTotal()"
                               class="mr-2 w-5 h-5 border border-gray-600 rounded-sm" checked />
                        <img alt="{{ $sparring['athlete_name'] }}" class="cart-img" src="{{ $athleteImageUrl }}" />
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-white cart-name">{{ $sparring['athlete_name'] }} (Sparring)</p>
                            <p class="text-white text-xs mt-1 cart-meta">
                                {{ \Carbon\Carbon::parse($sparring['date'])->format('d M Y') }}
                                {{ \Carbon\Carbon::parse($sparring['start'])->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($sparring['end'])->format('H:i') }}
                            </p>
                            <input type="hidden" id="price-sparring-{{ $sparring['cart_id'] }}" value="{{ $sparring['price'] }}">
                            <p class="text-white text-sm mt-1 cart-price">Rp. {{ number_format($sparring['price'], 0, ',', '.') }}</p>
                        </div>
                        <form action="{{ route('cart.delete') }}" method="POST" class="delete-form">
                            @csrf
                            <input type="hidden" name="id" value="{{ $sparring['cart_id'] }}">
                            <button type="submit" aria-label="Delete {{ $sparring['athlete_name'] }}" class="text-gray-400 hover:text-red-500 focus:outline-none">
                                <i class="fas fa-trash-alt text-lg"></i>
                            </button>
                        </form>
                    </li>
                @empty
                @endforelse
            </ul>
        </div>

        <form action="{{ route('checkout.index') }}" method="GET" id="checkoutForm">
            @csrf
            <div class="border-t border-gray-800 pt-4">
                <div class="flex justify-between text-white mb-2 cart-total-row">
                    <span>Total</span>
                    <span id="cart-total-display">Rp. 0</span>
                </div>
                <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-md checkout-btn">
                    Checkout
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Ukur tinggi header dinamis -> supaya calc(100vh - header) akurat
    (function syncCartHeaderHeight(){
        const cart = document.getElementById('cart');
        const header = cart?.querySelector('.cart-header');
        if (!cart || !header) return;
        const h = header.getBoundingClientRect().height || 64;
        cart.style.setProperty('--cart-header-h', h + 'px');
        // update bila resize
        window.addEventListener('resize', () => {
            const nh = header.getBoundingClientRect().height || 64;
            cart.style.setProperty('--cart-header-h', nh + 'px');
        });
    })();

    function updateCartTotal() {
        const scope = document.getElementById('cart');
        const checkboxes = scope.querySelectorAll('input[type="checkbox"]:checked');
        let total = 0;

        checkboxes.forEach(function(checkbox) {
            const type = checkbox.dataset.type || 'product';
            const rawValue = checkbox.value;
            const id = rawValue.split(':')[1] || rawValue;

            let priceInput = null;
            let discountInput = null;
            let qty = 1;

            if (type === 'venue') {
                priceInput = document.getElementById('price-venue-' + id);
            } else if (type === 'sparring') {
                priceInput = document.getElementById('price-sparring-' + id);
            } else {
                priceInput = document.getElementById('price-' + id);
                discountInput = document.getElementById('discount-' + id);
            }

            const qtyText = checkbox.closest('li').querySelector('.cart-meta')?.textContent || '';
            const qtyMatch = qtyText.match(/Quantity:\s*(\d+)/i);
            if (qtyMatch) qty = parseInt(qtyMatch[1], 10);

            if (priceInput) {
                const price = parseFloat(priceInput.value) || 0;
                const discount = discountInput ? parseFloat(discountInput.value) || 0 : 0;
                const finalPrice = price - (price * discount);
                total += finalPrice * qty;
            }
        });

        document.getElementById('cart-total-display').textContent =
            'Rp. ' + (total || 0).toLocaleString('id-ID');
    }

    document.querySelectorAll('#cart input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateCartTotal);
    });
    updateCartTotal();

    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const form = this;
        form.querySelectorAll('input[name="selected_items[]"]').forEach(el => el.remove());
        const checked = document.querySelectorAll('#cart input[type="checkbox"]:checked');

        if (checked.length === 0) {
            e.preventDefault();
            Swal.fire({
                title: 'Perhatian!',
                text: 'Wajib pilih minimal 1 barang untuk checkout.',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                background: '#1E1E1F',
                color: '#FFFFFF',
                iconColor: '#FFC107'
            });
            return;
        }

        const types = new Set();
        checked.forEach(cb => types.add(cb.dataset.type || 'product'));
        if (types.size > 1) {
            e.preventDefault();
            Swal.fire({
                title: 'Failed Checkout!',
                text: 'Checkout hanya bisa 1 jenis item (produk/venue/sparring) dalam sekali transaksi.',
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33',
                background: '#1E1E1F',
                color: '#FFFFFF',
                iconColor: '#FF5252'
            });
            return;
        }

        checked.forEach(cb => {
            const type = cb.dataset.type || 'product';
            const id = cb.value.split(':')[1] || cb.value;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected_items[]';
            hidden.value = `${type}:${id}`;
            form.appendChild(hidden);
        });
    });
</script>
