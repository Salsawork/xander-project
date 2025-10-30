@extends('app')
@section('title', 'Admin Dashboard - Detail Order')

@push('styles')
<style>
    /* ====== Anti overscroll / white bounce ====== */
    :root { color-scheme: dark; --page-bg:#0a0a0a; }
    html, body{
        height:100%; min-height:100%;
        background:var(--page-bg);
        overscroll-behavior-y:none; overscroll-behavior-x:none;
        touch-action:pan-y; -webkit-text-size-adjust:100%;
    }
    #antiBounceBg{
        position:fixed; left:0; right:0; top:-120svh; bottom:-120svh;
        background:var(--page-bg); z-index:-1; pointer-events:none;
    }
    .scroll-safe{ background-color:#171717; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
</style>
@endpush

@php
    /**
     * ====== CDN CONFIG & HELPERS ======
     * Semua gambar HARUS dari ../demo-xanders/images/{products|venue|athlete}/
     * Gunakan XB_CDN di .env untuk override host (default: https://demo-xanders.ptbmn.id)
     */

    if (!function_exists('xb_cdn_bases')) {
        function xb_cdn_bases(): array {
            $host = rtrim(config('app.xb_cdn', env('XB_CDN', 'https://demo-xanders.ptbmn.id')), '/');
            return [
                'products' => $host . '/images/products/',
                'venue'    => $host . '/images/venue/',
                'athlete'  => $host . '/images/athlete/',
            ];
        }
    }

    if (!function_exists('xb_fallbacks')) {
        function xb_fallbacks(): array {
            return [
                'products' => ['1.png','2.png','3.png','4.png','5.png'],
                'venue'    => ['venue-1.png','venue-2.png','venue-3.png'],
                'athlete'  => ['athlete-1.png','athlete-2.png','athlete-3.png','athlete-4.png'],
            ];
        }
    }

    if (!function_exists('xb_basename')) {
        function xb_basename($maybePath): ?string {
            if (!$maybePath) return null;
            $s = is_string($maybePath) ? $maybePath : (string)$maybePath;
            // Jika URL penuh → ambil path-nya dulu
            $path = filter_var($s, FILTER_VALIDATE_URL) ? (parse_url($s, PHP_URL_PATH) ?? $s) : $s;
            $path = str_replace('\\','/', $path);
            $name = basename($path);
            return ($name === '' || $name === '.' || $name === '..') ? null : $name;
        }
    }

    if (!function_exists('xb_cdn_url')) {
        /**
         * Buat URL CDN final dari type dan kandidat path.
         * - $type: 'products'|'venue'|'athlete'
         * - $maybePath: string|url|storage path|json/array (ambil first image di luar)
         * - $fallbackIdx: index fallback pool jika kosong/gagal
         */
        function xb_cdn_url(string $type, $maybePath, int $fallbackIdx = 0): ?string {
            $bases = xb_cdn_bases();
            $fbs   = xb_fallbacks();
            $base  = $bases[$type] ?? null;
            if (!$base) return null;

            $name = xb_basename($maybePath);
            if ($name) return $base . $name;

            $pool = $fbs[$type] ?? [];
            $fallback = $pool ? $pool[$fallbackIdx % max(1, count($pool))] : null;
            return $fallback ? ($base . $fallback) : null;
        }
    }

    if (!function_exists('first_image_from_mixed')) {
        /**
         * Ambil image pertama dari field yang bisa jadi:
         * - array gambar
         * - json string berisi array
         * - string single path/url
         */
        function first_image_from_mixed($value) {
            if (!$value) return null;
            if (is_array($value)) return $value[0] ?? null;
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded[0] ?? null;
                }
                return $value; // single string path/url
            }
            return null;
        }
    }

    // Precompute untuk dipakai di onerror fallback
    $CDN_BASE  = xb_cdn_bases();
    $FALLBACKS = xb_fallbacks();
@endphp

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')

            <h1 class="text-2xl font-bold p-4 sm:p-8 mt-12">
                Detail Order {{ $order->order_type === 'product' ? 'Produk' :  ($order->order_type === 'sparring' ? 'Sparring' : 'Booking') }}
            </h1>

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
                                'pending'   => 'bg-yellow-500 text-white',
                                'paid'      => 'bg-green-500 text-white',
                                'shipped'   => 'bg-blue-500 text-white',
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
                    <div>
                        <h3 class="text-base sm:text-lg font-semibold mb-2">Customer Information</h3>
                        <div class="bg-[#1e1e1e] rounded-lg p-4 space-y-2">
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Name:</span> {{ $order->user->name ?? 'Guest' }}</p>
                            <p class="text-sm sm:text-base break-all"><span class="text-gray-400">Email:</span> {{ $order->user->email ?? 'N/A' }}</p>
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Phone:</span> {{ $order->user->phone ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-base sm:text-lg font-semibold mb-2">Payment Information</h3>
                        <div class="bg-[#1e1e1e] rounded-lg p-4 space-y-2">
                            <p class="text-sm sm:text-base"><span class="text-gray-400">Payment Method:</span> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                            <p class="text-sm sm:text-base">
                                <span class="text-gray-400">Payment Status:</span>
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
                            {{-- Products --}}
                            @forelse($order->products as $product)
                                @php
                                    // Utamakan $product->images, fallback ke pivot->images
                                    $firstProductImg = first_image_from_mixed($product->images)
                                        ?? first_image_from_mixed($product->pivot->images);
                                    $imgUrl = xb_cdn_url('products', $firstProductImg, $loop->index);
                                @endphp
                                <tr class="border-b border-gray-700">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-md flex-shrink-0"
                                                 onerror="this.onerror=null;this.src='{{ $CDN_BASE['products'] . $FALLBACKS['products'][0] }}'">
                                            <div>
                                                <p class="font-medium">{{ $product->name }}</p>
                                                <p class="text-xs text-gray-400">ID: {{ $product->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">Product</td>
                                    <td class="px-4 py-3">
                                        <div>Rp. {{ number_format($product->pivot->price, 0, ',', '.') }}</div>
                                        @if(!empty($product->pivot->discount))
                                            <div class="text-xs text-green-400">Disc: -Rp. {{ number_format($product->pivot->discount, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $product->pivot->quantity }}</td>
                                    <td class="px-4 py-3">Rp. {{ number_format($product->pivot->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                            @endforelse

                            {{-- Bookings (Venue/Table) --}}
                            @forelse($order->bookings as $booking)
                                @php
                                    $venueImage = first_image_from_mixed($booking->venue->images ?? null)
                                        ?? ($booking->venue->image ?? $booking->venue->foto ?? $booking->venue->banner ?? null);
                                    $tableImage = $booking->table->image ?? $booking->table->foto ?? null;
                                    $picked     = $venueImage ?: $tableImage;

                                    $venueImgUrl = xb_cdn_url('venue', $picked, $loop->index);
                                @endphp
                                <tr class="border-b border-gray-700">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $venueImgUrl }}" alt="{{ $booking->venue->name ?? 'Venue' }}" class="w-12 h-12 object-cover rounded-md flex-shrink-0"
                                                 onerror="this.onerror=null;this.src='{{ $CDN_BASE['venue'] . $FALLBACKS['venue'][0] }}'">
                                            <div>
                                                <p class="font-medium">{{ $booking->venue->name ?? 'Venue' }}</p>
                                                <p class="text-xs text-gray-400">
                                                    {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
                                                    {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                                </p>
                                                <p class="text-xs text-gray-400">Table: {{ $booking->table->table_number ?? '-' }}</p>
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

                            {{-- Sparring (Athlete) --}}
                            @forelse($order->orderSparrings as $sparring)
                                @php
                                    $athPhoto = first_image_from_mixed($sparring->athlete->images ?? null)
                                        ?? ($sparring->athlete->photo ?? $sparring->athlete->image ?? $sparring->athlete->avatar ?? null);
                                    $athUrl = xb_cdn_url('athlete', $athPhoto, $loop->index);
                                @endphp
                                <tr class="border-b border-gray-700">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $athUrl }}" alt="{{ $sparring->athlete->name ?? 'Athlete' }}" class="w-12 h-12 object-cover rounded-md flex-shrink-0"
                                                 onerror="this.onerror=null;this.src='{{ $CDN_BASE['athlete'] . $FALLBACKS['athlete'][0] }}'">
                                            <div>
                                                <p class="font-medium">{{ $sparring->athlete->name ?? 'Sparring' }}</p>
                                                <p class="text-xs text-gray-400">
                                                    {{ \Carbon\Carbon::parse($sparring->schedule->date)->format('d/m/Y') }}
                                                    {{ \Carbon\Carbon::parse($sparring->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sparring->schedule->end_time)->format('H:i') }}
                                                </p>
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

                        @php
                            // === Summary (Subtotal, Shipping, Tax, Total) ===
                            $subtotal = 0;
                            $shipping = 0;
                            $tax      = 0;

                            if ($order->products->isNotEmpty()) {
                                $subtotal += $order->products->sum('pivot.subtotal');
                                $shipping += (int) ($order->products->first()->pivot->shipping ?? 0);
                                $tax      += (int) ($order->products->first()->pivot->tax ?? 0);
                            }
                            if ($order->bookings->isNotEmpty()) {
                                $subtotal += $order->bookings->sum('price');
                            }
                            if ($order->orderSparrings->isNotEmpty()) {
                                $subtotal += $order->orderSparrings->sum('price');
                            }
                        @endphp

                        <tfoot class="bg-[#1e1e1e] font-medium">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right">Subtotal:</td>
                                <td class="px-4 py-3">Rp. {{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($order->products->isNotEmpty())
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right">Shipping:</td>
                                    <td class="px-4 py-3">Rp. {{ number_format($shipping, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right">Tax:</td>
                                    <td class="px-4 py-3">Rp. {{ number_format($tax, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right font-bold">Total:</td>
                                <td class="px-4 py-3 font-bold">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="sm:hidden space-y-4">
                    {{-- Products Mobile --}}
                    @forelse($order->products as $product)
                        @php
                            $firstProductImg = first_image_from_mixed($product->images)
                                ?? first_image_from_mixed($product->pivot->images);
                            $imgUrl = xb_cdn_url('products', $firstProductImg, $loop->index);
                        @endphp
                        <div class="bg-[#1e1e1e] rounded-lg p-4">
                            <div class="flex gap-3 mb-3">
                                <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-16 h-16 object-cover rounded-md flex-shrink-0"
                                     onerror="this.onerror=null;this.src='{{ $CDN_BASE['products'] . $FALLBACKS['products'][0] }}'">
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
                    @endforelse

                    {{-- Bookings Mobile --}}
                    @forelse($order->bookings as $booking)
                        @php
                            $venueImage = first_image_from_mixed($booking->venue->images ?? null)
                                ?? ($booking->venue->image ?? $booking->venue->foto ?? $booking->venue->banner ?? null);
                            $tableImage = $booking->table->image ?? $booking->table->foto ?? null;
                            $picked     = $venueImage ?: $tableImage;
                            $venueImgUrl = xb_cdn_url('venue', $picked, $loop->index);
                        @endphp
                        <div class="bg-[#1e1e1e] rounded-lg p-4">
                            <div class="flex gap-3 mb-3">
                                <img src="{{ $venueImgUrl }}" alt="{{ $booking->venue->name ?? 'Venue' }}" class="w-16 h-16 object-cover rounded-md flex-shrink-0"
                                     onerror="this.onerror=null;this.src='{{ $CDN_BASE['venue'] . $FALLBACKS['venue'][0] }}'">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm">{{ $booking->venue->name ?? 'Venue' }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}
                                        {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                    </p>
                                    <p class="text-xs text-gray-400">Table: {{ $booking->table->table_number ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Price:</span>
                                    <span>Rp. {{ number_format($booking->price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium">
                                    <span class="text-gray-400">Subtotal:</span>
                                    <span>Rp. {{ number_format($booking->price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                    @endforelse

                    {{-- Sparring Mobile --}}
                    @forelse($order->orderSparrings as $sparring)
                        @php
                            $athPhoto = first_image_from_mixed($sparring->athlete->images ?? null)
                                ?? ($sparring->athlete->photo ?? $sparring->athlete->image ?? $sparring->athlete->avatar ?? null);
                            $athUrl = xb_cdn_url('athlete', $athPhoto, $loop->index);
                        @endphp
                        <div class="bg-[#1e1e1e] rounded-lg p-4">
                            <div class="flex gap-3 mb-3">
                                <img src="{{ $athUrl }}" alt="{{ $sparring->athlete->name ?? 'Athlete' }}" class="w-16 h-16 object-cover rounded-md flex-shrink-0"
                                     onerror="this.onerror=null;this.src='{{ $CDN_BASE['athlete'] . $FALLBACKS['athlete'][0] }}'">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm">{{ $sparring->athlete->name ?? 'Sparring' }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($sparring->schedule->date)->format('d/m/Y') }}
                                        {{ \Carbon\Carbon::parse($sparring->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sparring->schedule->end_time)->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Price:</span>
                                    <span>Rp. {{ number_format($sparring->price, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between font-medium">
                                    <span class="text-gray-400">Subtotal:</span>
                                    <span>Rp. {{ number_format($sparring->price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                    @endforelse

                    @if(optional($order->products)->isEmpty() && optional($order->bookings)->isEmpty() && optional($order->orderSparrings)->isEmpty())
                        <div class="bg-[#1e1e1e] rounded-lg p-4 text-center text-gray-400">
                            No items found
                        </div>
                    @endif

                    <!-- Mobile Order Summary -->
                    <div class="bg-[#1e1e1e] rounded-lg p-4 space-y-2">
                        @php
                            $mobileSubtotal = max(0, (int)$order->total - 30000);
                        @endphp
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Subtotal:</span>
                            <span>Rp. {{ number_format($mobileSubtotal, 0, ',', '.') }}</span>
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
                <p class="text-sm sm:text-base">
                    No order selected. Please select an order from the
                    <a href="{{ route('order.index') }}" class="text-blue-400 hover:underline">order list</a>.
                </p>
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
