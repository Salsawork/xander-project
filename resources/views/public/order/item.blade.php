{{-- resources/views/dash/user/order-detail.blade.php --}}
@extends('app')
@section('title', 'Order Detail - Xander Billiard')

@section('content')
<div class="min-h-screen bg-neutral-900 text-gray-100 font-sans py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">

        {{-- HEADER --}}
        <div class="flex items-center mb-6">
            <i class="fas fa-chevron-left text-xl mr-4 cursor-pointer hover:text-blue-400 transition"
               onclick="window.history.back();"></i>
            <h1 class="text-xl sm:text-2xl font-semibold flex flex-wrap items-center gap-2">
                <span>Order Detail</span>
                <span class="text-blue-500 font-bold">{{ $order->order_number }}</span>
                <span class="text-gray-500">|</span>
                <span class="text-sm text-gray-400">
                    {{ $order->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y \a\t H:i') }}
                </span>
            </h1>
        </div>

        @php
            use Illuminate\Support\Str;

            /*
             |--------------------------------------------------------------------------
             | IMAGE SOURCE (PRODUCTS):
             |   /home/xanderbilliard.site/public_html/images/products
             |--------------------------------------------------------------------------
             | Public URL:
             |   https://xanderbilliard.site/images/products/{filename}
             | Blade helper:
             |   asset('images/products/{filename}')
             |--------------------------------------------------------------------------
             */

            $prodCdnBase   = rtrim(asset('images/products'), '/') . '/';
            $prodFallbacks = ['1.png','2.png','3.png','4.png','5.png'];

            if (!function_exists('xb_product_image_url')) {
                /**
                 * Normalisasi path gambar menjadi:
                 *   {base}/{basename}
                 * Jika tidak ada nama file valid → pakai fallback (jika ada).
                 */
                function xb_product_image_url($raw, string $base, array $fallbacks = [], ?int $idx = null): string {
                    $basename = null;

                    if ($raw) {
                        if (filter_var($raw, FILTER_VALIDATE_URL)) {
                            $path = parse_url($raw, PHP_URL_PATH) ?? $raw;
                            $basename = basename(str_replace('\\','/',$path));
                        } else {
                            $basename = basename(str_replace('\\','/',$raw));
                        }
                    }

                    if ($basename && $basename !== '.' && $basename !== '..' && $basename !== '/') {
                        return rtrim($base, '/') . '/' . $basename;
                    }

                    if (!empty($fallbacks)) {
                        $i = is_int($idx) ? ($idx % count($fallbacks)) : 0;
                        return rtrim($base, '/') . '/' . $fallbacks[$i];
                    }

                    return rtrim($base, '/') . '/';
                }
            }

            // ===== Hitung subtotal, diskon, tax, shipping =====
            $subtotal      = 0;
            $discountTotal = 0;
            $tax           = 0;
            $shipping      = 0;

            foreach ($order->products as $p) {
                $qty     = $p->pivot->quantity ?? 1;
                $price   = $p->pivot->price ?? 0;
                $disc    = $p->pivot->discount ?? 0;
                $itemSub = ($price * $qty) - $disc;

                $subtotal      += $itemSub;
                $discountTotal += $disc;
            }

            // Ambil tax & shipping dari pivot pertama jika ada
            $firstPivot = $order->products->first()->pivot ?? null;
            if ($firstPivot) {
                $tax      = $firstPivot->tax      ?? 0;
                $shipping = $firstPivot->shipping ?? 0;
            }

            // Grand total
            $grandTotal = $subtotal + $tax + $shipping;

            // Customer
            $user = $order->user ?? auth()->user();

            // Courier info dari pivot (TANPA FOTO COURIER)
            $courierName = strtoupper($firstPivot->courier ?? '');
            $trackingNo  = $firstPivot->tracking_number ?? null;
        @endphp

        {{-- GRID: PRODUCTS + CUSTOMER INFO --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            {{-- PRODUCTS ORDERED --}}
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Products Ordered</h2>

                <div class="space-y-5">
                    @foreach($order->products as $index => $product)
                        @php
                            $qty   = $product->pivot->quantity ?? 1;
                            $price = $product->pivot->price ?? 0;
                            $disc  = $product->pivot->discount ?? 0;

                            $itemSubtotal = ($price * $qty) - $disc;

                            // Deteksi product "Courier" → tidak tampilkan foto produk
                            $nameRaw   = (string) ($product->name ?? '');
                            $isCourier = Str::contains(Str::lower($nameRaw), 'courier');

                            $rawImg = null;

                            if (!$isCourier) {
                                // 1) pivot->images
                                if (!empty($product->pivot->images)) {
                                    if (is_array($product->pivot->images)) {
                                        $rawImg = $product->pivot->images[0] ?? null;
                                    } else {
                                        $parsed = json_decode($product->pivot->images, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                                            $rawImg = $parsed[0] ?? null;
                                        } elseif (is_string($product->pivot->images)) {
                                            $rawImg = $product->pivot->images;
                                        }
                                    }
                                }

                                // 2) product->images
                                if (!$rawImg && !empty($product->images)) {
                                    if (is_array($product->images)) {
                                        $rawImg = $product->images[0] ?? null;
                                    } else {
                                        $parsed = json_decode($product->images, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                                            $rawImg = $parsed[0] ?? null;
                                        } elseif (is_string($product->images)) {
                                            $rawImg = $product->images;
                                        }
                                    }
                                }

                                // 3) product->image
                                if (!$rawImg && !empty($product->image)) {
                                    $rawImg = $product->image;
                                }
                            }

                            // URL final SELALU dari /images/products
                            $imgUrl = !$isCourier
                                ? xb_product_image_url($rawImg, $prodCdnBase, $prodFallbacks, $index)
                                : null;
                        @endphp

                        <div class="flex items-center justify-between border-b border-neutral-800 pb-3">
                            <div class="flex items-center gap-4">
                                {{-- IMG hanya untuk non-courier, dari /images/products --}}
                                @if(!$isCourier && $imgUrl)
                                    <img
                                        src="{{ $imgUrl }}"
                                        alt="{{ $product->name ?? 'Product' }}"
                                        class="w-16 h-16 rounded-lg object-cover shadow-sm bg-neutral-800"
                                        onerror="this.onerror=null;this.src='{{ $prodCdnBase . ($prodFallbacks[0] ?? '') }}';">
                                @else
                                    {{-- Courier / tanpa gambar: kotak placeholder teks --}}
                                    <div class="w-16 h-16 rounded-lg bg-neutral-800 flex items-center justify-center text-[9px] text-gray-400 uppercase tracking-wide">
                                        {{ $isCourier ? 'Courier' : 'No Image' }}
                                    </div>
                                @endif

                                <div>
                                    <p class="font-medium text-white">
                                        {{ $product->name ?? 'Product Name' }}
                                    </p>
                                    <p class="text-gray-400 text-sm">
                                        {{ $product->category->name ?? ($isCourier ? 'Shipping' : 'Category') }}
                                    </p>
                                    <p class="text-gray-400 text-sm">
                                        Qty: {{ $qty }}
                                        @if($disc > 0)
                                            <span class="text-red-400 ml-2">
                                                Disc: Rp {{ number_format($disc, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <p class="font-medium text-right text-white">
                                Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- DELIVERY DETAILS (TANPA FOTO COURIER) --}}
                <h2 class="text-lg font-semibold mt-6 mb-3">Delivery Details</h2>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-white">
                            {{ $courierName !== '' ? $courierName : 'Courier' }}
                        </p>
                        <p class="text-gray-400 text-sm">
                            Standard Shipping (3–5 Business Days)
                        </p>
                        @if($trackingNo)
                            <p class="text-gray-300 text-sm">
                                Tracking:
                                <span class="font-mono text-blue-400">{{ $trackingNo }}</span>
                            </p>
                        @endif
                    </div>
                    <p class="font-medium text-white whitespace-nowrap">
                        Rp {{ number_format($shipping, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- CUSTOMER INFO --}}
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Customer Information</h2>

                <div class="text-sm space-y-1">
                    <p class="font-semibold text-base">
                        {{ $user->name ?? 'Customer' }}
                    </p>
                    <a href="mailto:{{ $user->email ?? '' }}"
                       class="text-gray-300 hover:text-white block">
                        {{ $user->email ?? '-' }}
                    </a>
                    <p class="text-gray-300">
                        {{ $user->phone ?? '-' }}
                    </p>
                </div>

                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-1">Address:</p>
                    <p class="text-gray-300">
                        {{ $firstPivot->address ?? '-' }}
                    </p>
                </div>

                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-1">Payment Details:</p>
                    <p class="text-gray-300 capitalize">
                        {{ str_replace('_', ' ', $order->payment_method ?? '-') }}
                    </p>
                    <p class="text-gray-300">
                        Status:
                        <span class="{{ ($order->payment_status ?? '') === 'paid' ? 'text-green-400' : 'text-yellow-400' }} font-semibold">
                            {{ ucfirst($order->payment_status ?? 'pending') }}
                        </span>
                    </p>
                    <p class="text-gray-400 text-xs mt-1">
                        Transaction ID:
                        <span class="text-gray-300">
                            {{ $order->transaction_id ?? '-' }}
                        </span>
                    </p>
                </div>

                @if(($order->payment_status ?? '') === 'paid')
                    <a href="{{ route('invoice.product', $order->id) }}"
                       class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 rounded-md font-medium transition inline-block text-center">
                        Download Invoice
                    </a>
                @endif
            </div>
        </div>

        {{-- PAYMENT SUMMARY --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-3">Payment Summary</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Subtotal</span>
                        <span class="text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tax</span>
                        <span class="text-white">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Discount</span>
                        <span class="text-white">Rp {{ number_format($discountTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Shipping</span>
                        <span class="text-white">Rp {{ number_format($shipping, 0, ',', '.') }}</span>
                    </div>
                </div>
                <hr class="border-neutral-800 my-3">
                <div class="flex justify-between text-base font-semibold">
                    <span>Grand Total</span>
                    <span class="text-white">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <p class="text-sm text-gray-300 mb-4">
                    Need help with your order? Contact our support team for updates or refund assistance:
                </p>
                <div class="space-y-2 text-sm text-gray-300">
                    <p><span class="text-gray-400">Phone:</span> +1 234 567 890</p>
                    <p>
                        <span class="text-gray-400">Email:</span>
                        <a href="mailto:support@xanderbilliard.com"
                           class="text-blue-500 hover:underline">
                            support@xanderbilliard.com
                        </a>
                    </p>
                    <p>
                        <span class="text-gray-400">Address:</span>
                        4568 Greenway Street, Los Angeles, CA
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
