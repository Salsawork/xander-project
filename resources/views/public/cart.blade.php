<div class="fixed top-0 right-0 bg-gray-900 min-h-screen max-w-1/3 z-50 hidden" id="cart">
    <header class="flex items-center space-x-4 p-6">
        <button aria-label="Back" class="text-gray-500 hover:text-gray-400 focus:outline-none" onclick="showCart()">
            <i class="fas fa-arrow-left text-xl"></i>
        </button>
        <h2 class="text-white text-xl font-bold">Your Cart</h2>
    </header>

    <div class="p-6 pt-0">
        <div class="mb-6">
            <h3 class="text-white font-bold text-lg mb-2">Items</h3>
            <ul class="space-y-4">
                {{-- Product --}}
                @forelse ($cartProducts as $cart)
                    <li class="flex items-center space-x-4">
                        <input aria-label="{{ 'Select ' . $cart['name'] }}" onchange="handleCheckboxChange(this)"
                            class="w-5 h-5 border border-gray-600 rounded-sm bg-transparent checked:bg-blue-500 checked:border-blue-500"
                            type="checkbox" value="{{ $cart['id'] }}" />
                        <img alt="{{ $cart['name'] }}" class="w-20 h-20 rounded-md object-cover flex-shrink-0" height="80"
                            src="{{ $cart['image'] ?? 'https://placehold.co/400x400?text=No+Image' }}" width="80" />
                        <div class="flex-1">
                            <p class="font-bold text-white text-base leading-tight">
                                {{ $cart['name'] }}
                            </p>
                            <input type="hidden" id="price-{{ $cart['id'] }}" value="{{ $cart['price'] }}">
                            <p class="text-white text-sm mt-1">
                                Rp. {{ number_format($cart['price'], 0, ',', '.') }}
                            </p>
                        </div>
                        <form action="{{ route('cart.del.product') }}" method="POST" class="delete-form">
                            @csrf
                            <input type="hidden" name="id" value="{{ $cart['id'] }}">
                            <button type="submit" aria-label="Delete {{ $cart['name'] }}"
                                class="text-gray-400 hover:text-red-500 focus:outline-none flex-shrink-0">
                                <i class="fas fa-trash-alt text-lg">
                                </i>
                            </button>
                        </form>
                    </li>
                @empty
                    @if(empty($cartSparrings) && empty($cartVenues))
                        <li class="text-center text-gray-500 py-4 min-w-xs">
                            Your cart is empty
                        </li>
                    @endif
                @endforelse

                {{-- Venue --}}
                @forelse ($cartVenues ?? [] as $index => $venue)
                    <li class="flex items-center space-x-4">
                        <input type="checkbox" name="selected_items[]" value="venue-{{ $venue['id'] }}"
                            onchange="handleCheckboxChange(this)"
                            class="w-5 h-5 border border-gray-600 rounded-sm bg-transparent checked:bg-blue-500 checked:border-blue-500" />

                        <img class="w-20 h-20 rounded-md object-cover flex-shrink-0" height="80"
                            src="https://placehold.co/400x400?text=No+Image"
                            width="80" />
                        <div class="flex-1">
                            <p class="font-bold text-white text-base leading-tight">
                                {{ $venue['name'] }}
                            </p>
                            <p class="text-white text-xs mt-1">
                                {{ $venue['date'] }} {{ $venue['start'] }} - {{ $venue['end'] }}
                            </p>
                            <input type="hidden" id="price-venue-{{ $venue['id'] }}" value="{{ $venue['price'] }}">
                            <p class="text-white text-sm mt-1">
                                Rp. {{ number_format($venue['price'], 0, ',', '.') }}
                            </p>
                        </div>

                        <form action="{{ route('cart.del.venue') }}" method="POST" class="delete-form">
                            @csrf
                            <input type="hidden" name="id" value="{{ $venue['id'] }}">
                            <button type="submit" aria-label="Delete {{ $venue['name'] }}"
                                class="text-gray-400 hover:text-red-500 focus:outline-none flex-shrink-0">
                                <i class="fas fa-trash-alt text-lg">
                                </i>
                            </button>
                        </form>
                    </li>
                @empty
                @endforelse

                {{-- Sparring --}}
                @forelse ($cartSparrings ?? [] as $index => $sparring)
                    <li class="flex items-center space-x-4">
                        <input aria-label="{{ 'Select ' . $sparring['name'] }}" onchange="handleCheckboxChange(this)"
                            class="w-5 h-5 border border-gray-600 rounded-sm bg-transparent checked:bg-blue-500 checked:border-blue-500"
                            type="checkbox" value="sparring-{{ $sparring['schedule_id'] }}" />
                        <img alt="{{ $sparring['name'] }}" class="w-20 h-20 rounded-md object-cover flex-shrink-0"
                            height="80"
                            src="{{ $sparring['image'] ? asset('images/athlete/' . $sparring['image']) : 'https://placehold.co/400x400?text=No+Image' }}"
                            width="80" />
                        <div class="flex-1">
                            <p class="font-bold text-white text-base leading-tight">
                                {{ $sparring['name'] }} (Sparring)
                            </p>
                            <p class="text-white text-xs mt-1">
                                {{ $sparring['schedule'] }}
                            </p>
                            <input type="hidden" id="price-sparring-{{ $sparring['schedule_id'] }}"
                                value="{{ $sparring['price'] }}">
                            <p class="text-white text-sm mt-1">
                                Rp. {{ number_format($sparring['price'], 0, ',', '.') }}
                            </p>
                        </div>
                        <form action="{{ route('cart.del.sparring') }}" method="POST" class="delete-form">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="schedule_id" value="{{ $sparring['schedule_id'] }}">
                            <button type="submit" aria-label="Delete {{ $sparring['name'] }}"
                                class="text-gray-400 hover:text-red-500 focus:outline-none flex-shrink-0">
                                <i class="fas fa-trash-alt text-lg">
                                </i>
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
                <div class="flex justify-between text-white mb-2">
                    <span>Total</span>
                    <span>
                        @php
                            $total = 0;
                            foreach ($cartProducts as $item) {
                                $total += (int) $item['price'];
                            }
                            foreach ($cartVenues as $item) {
                                $total += (int) $item['price'];
                            }
                            foreach ($cartSparrings ?? [] as $item) {
                                $total += (int) $item['price'];
                            }
                        @endphp
                        Rp. {{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>
                <button type="submit"
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 rounded-md">
                    Checkout
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('checkoutForm').addEventListener('submit', function (e) {
        // Cek apakah minimal ada 1 checkbox yang dipilih
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        let isAnyChecked = false;

        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                isAnyChecked = true;
            }
        });

        // Jika tidak ada yang dipilih, tampilkan SweetAlert dan hentikan submit
        if (!isAnyChecked) {
            e.preventDefault();
            Swal.fire({
                title: 'Perhatian!',
                text: 'Wajib Pilih Minimal 1 Barang',
                icon: 'warning',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6',
                background: '#1E1E1F',
                color: '#FFFFFF',
                iconColor: '#FFC107'
            });
        }
    });
</script>