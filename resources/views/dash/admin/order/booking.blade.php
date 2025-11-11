{{-- resources/views/admin/order/booking.blade.php --}}
@extends('app')
@section('title', 'Admin Dashboard - Order')

@push('styles')
<style>
    [x-cloak]{display:none!important}
    .scrollbar-hide::-webkit-scrollbar{display:none}
    .scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}

    :root{color-scheme:dark;--page-bg:#0a0a0a}
    html,body{
        height:100%;min-height:100%;
        background:var(--page-bg);
        overscroll-behavior-y:none;overscroll-behavior-x:none;
        touch-action:pan-y;-webkit-text-size-adjust:100%
    }
    #antiBounceBg{
        position:fixed;left:0;right:0;top:-120svh;bottom:-120svh;
        background:var(--page-bg);z-index:-1;pointer-events:none
    }
    .scroll-safe{
        background-color:#171717;
        overscroll-behavior:contain;
        -webkit-overflow-scrolling:touch
    }
</style>
@endpush

@php
    /**
     * FILE BUKTI TRANSFER
     *
     * Lokasi FISIK:
     *   /home/xanderbilliard.site/public_html/images/payment_proof/{filename}
     *
     * URL PUBLIK:
     *   https://xanderbilliard.site/images/payment_proof/{filename}
     *
     * Jadi:
     * - Gunakan public_path('images/payment_proof') untuk scan file
     * - Saat tampilkan, SELALU arahkan ke CDN base di bawah.
     */

    $__proofDir     = public_path('images/payment_proof');
    $__proofCdnBase = 'https://xanderbilliard.site/images/payment_proof/';

    if (!function_exists('__proof_cdn_url')) {
        /**
         * Generate URL bukti transfer dari CDN /images/payment_proof.
         *
         * Urutan:
         * 1) Scan folder fisik pakai order_number / id → ambil yang terbaru.
         * 2) Jika tidak ketemu, ambil basename dari field file/payment_proof/proof_file,
         *    tapi TETAP arahkan ke $__proofCdnBase.
         */
        function __proof_cdn_url($order, string $dir, string $cdnBase): ?string {
            $allowedExt = ['jpg','jpeg','png','webp','pdf'];

            $keys = [];
            if (!empty($order->order_number)) {
                $keys[] = (string) $order->order_number;
            }
            $keys[] = (string) $order->id;

            $matches = [];

            if (is_dir($dir)) {
                foreach ($keys as $k) {
                    $pattern = rtrim($dir, '/')
                        . '/*' . $k . '*.{'
                        . implode(',', $allowedExt)
                        . '}';
                    $found = glob($pattern, GLOB_BRACE) ?: [];
                    if (!empty($found)) {
                        $matches = array_merge($matches, $found);
                    }
                }
            }

            if (!empty($matches)) {
                usort($matches, fn($a, $b) => filemtime($b) <=> filemtime($a));
                return rtrim($cdnBase, '/') . '/' . basename($matches[0]);
            }

            // Fallback: pakai nama file dari DB, tapi tetap pakai CDN base
            $fieldCandidates = [
                $order->file          ?? null,
                $order->payment_proof ?? null,
                $order->proof_file    ?? null,
            ];

            foreach ($fieldCandidates as $f) {
                if (!empty($f)) {
                    $name = basename(str_replace('\\', '/', $f));
                    if ($name !== '' && $name !== '.' && $name !== '..') {
                        return rtrim($cdnBase, '/') . '/' . $name;
                    }
                }
            }

            return null;
        }
    }

    // Siapkan data summary & list
    $pendingCount    = $pendingCount    ?? 0;
    $processingCount = $processingCount ?? 0;
    $paidCount       = $paidCount       ?? 0;
    $failedCount     = $failedCount     ?? 0;
    $refundedCount   = $refundedCount   ?? 0;
@endphp

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')

        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')

            <h1 class="text-2xl font-bold p-4 sm:p-8 mt-12">Order Booking Management</h1>

            {{-- SUMMARY STATUS --}}
            <section class="mx-4 sm:mx-8 mb-8"
                     x-data="{
                        scrollContainer:null,
                        canScrollLeft:false,
                        canScrollRight:true,
                        init(){
                            this.$nextTick(()=>{
                                this.scrollContainer=this.$refs.summaryScroll;
                                this.updateScrollButtons();
                            });
                        },
                        updateScrollButtons(){
                            if(!this.scrollContainer) return;
                            this.canScrollLeft = this.scrollContainer.scrollLeft > 0;
                            this.canScrollRight = this.scrollContainer.scrollLeft <
                                (this.scrollContainer.scrollWidth - this.scrollContainer.clientWidth - 10);
                        },
                        scrollLeft(){
                            this.scrollContainer.scrollBy({left:-200,behavior:'smooth'});
                            setTimeout(()=>this.updateScrollButtons(),300);
                        },
                        scrollRight(){
                            this.scrollContainer.scrollBy({left:200,behavior:'smooth'});
                            setTimeout(()=>this.updateScrollButtons(),300);
                        }
                     }">
                <div class="relative">
                    <button @click="scrollLeft"
                            x-show="canScrollLeft"
                            class="sm:hidden absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-neutral-800 hover:bg-neutral-700 text-white p-2 rounded-full shadow-lg"
                            aria-label="Scroll left">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <div x-ref="summaryScroll"
                         @scroll="updateScrollButtons"
                         class="bg-[#292929] rounded-lg p-4 sm:p-6 flex gap-4 sm:justify-between overflow-x-auto sm:overflow-x-visible scrollbar-hide snap-x snap-mandatory">
                        <div class="flex-shrink-0 snap-center min-w-[100px] sm:flex-1 text-center">
                            <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $pendingCount }}</div>
                            <div class="font-semibold mt-1 text-sm sm:text-base">Pending</div>
                        </div>
                        <div class="border-l border-gray-600 flex-shrink-0"></div>

                        <div class="flex-shrink-0 snap-center min-w-[100px] sm:flex-1 text-center">
                            <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $processingCount }}</div>
                            <div class="font-semibold mt-1 text-sm sm:text-base">Processing</div>
                        </div>
                        <div class="border-l border-gray-600 flex-shrink-0"></div>

                        <div class="flex-shrink-0 snap-center min-w-[100px] sm:flex-1 text-center">
                            <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $paidCount }}</div>
                            <div class="font-semibold mt-1 text-sm sm:text-base">Paid</div>
                        </div>
                        <div class="border-l border-gray-600 flex-shrink-0"></div>

                        <div class="flex-shrink-0 snap-center min-w-[100px] sm:flex-1 text-center">
                            <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $failedCount }}</div>
                            <div class="font-semibold mt-1 text-sm sm:text-base">Failed</div>
                        </div>
                        <div class="border-l border-gray-600 flex-shrink-0"></div>

                        <div class="flex-shrink-0 snap-center min-w-[100px] sm:flex-1 text-center">
                            <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $refundedCount }}</div>
                            <div class="font-semibold mt-1 text-sm sm:text-base">Refunded</div>
                        </div>
                    </div>

                    <button @click="scrollRight"
                            x-show="canScrollRight"
                            class="sm:hidden absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-neutral-800 hover:bg-neutral-700 text-white p-2 rounded-full shadow-lg"
                            aria-label="Scroll right">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </section>

            {{-- FILTER --}}
            <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between mx-4 sm:mx-8 mb-4 gap-4">
                <input
                    type="text"
                    placeholder="Insert Name"
                    id="search"
                    value="{{ request('search') }}"
                    class="bg-transparent border border-gray-600 rounded-md px-3 py-1.5 text-gray-400 placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-[#0d82ff] focus:border-[#0d82ff] w-full sm:max-w-xs"
                    onchange="
                        window.location.href='{{ route('order.index.booking') }}'
                        + '?search='  + encodeURIComponent(this.value)
                        + '&status='  + encodeURIComponent(document.getElementById('status').value || '')
                        + '&orderBy=' + encodeURIComponent(document.getElementById('orderBy').value || '')
                    "
                />

                <div class="flex flex-col sm:flex-row gap-2 text-xs text-gray-600 select-none">
                    <div class="relative">
                        <select
                            id="status"
                            class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full sm:w-40 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            onchange="
                                window.location.href='{{ route('order.index.booking') }}'
                                + '?search='  + encodeURIComponent(document.getElementById('search').value || '')
                                + '&status='  + encodeURIComponent(this.value || '')
                                + '&orderBy=' + encodeURIComponent(document.getElementById('orderBy').value || '')
                            "
                        >
                            <option value="" {{ request('status') ? '' : 'selected' }}>Filter by Status</option>
                            @foreach (['pending','processing','paid','failed','refunded'] as $opt)
                                <option value="{{ $opt }}" {{ request('status') === $opt ? 'selected' : '' }}>
                                    {{ ucfirst($opt) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative">
                        <select
                            id="orderBy"
                            class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full sm:w-40 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            onchange="
                                window.location.href='{{ route('order.index.booking') }}'
                                + '?search='  + encodeURIComponent(document.getElementById('search').value || '')
                                + '&status='  + encodeURIComponent(document.getElementById('status').value || '')
                                + '&orderBy=' + encodeURIComponent(this.value || '')
                            "
                        >
                            <option value="" {{ request('orderBy') ? '' : 'selected' }}>Sort by Date</option>
                            <option value="asc"  {{ request('orderBy') === 'asc'  ? 'selected' : '' }}>Oldest to Newest</option>
                            <option value="desc" {{ request('orderBy') === 'desc' ? 'selected' : '' }}>Newest to Oldest</option>
                        </select>
                    </div>
                </div>
            </section>

            {{-- DESKTOP TABLE --}}
            <section class="hidden lg:block overflow-x-auto mx-8">
                <table class="w-full text-left text-gray-300 border-collapse">
                    <thead class="bg-[#292929] text-sm font-normal">
                    <tr>
                        <th class="px-4 py-3">Order ID</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Payment</th>
                        <th class="px-4 py-3">Status Payment</th>
                        <th class="px-4 py-3">Status Booking</th>
                        <th class="px-4 py-3">File</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Admin Fee</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                    </thead>
                    <tbody class="text-sm font-normal">
                    @forelse($orders as $order)
                        @php
                            $proofUrl = __proof_cdn_url($order, $__proofDir, $__proofCdnBase);

                            $statusClassPay = [
                                'pending'    => 'bg-blue-600 text-white',
                                'processing' => 'bg-yellow-400 text-gray-900',
                                'paid'       => 'bg-green-600 text-white',
                                'failed'     => 'bg-red-600 text-white',
                                'refunded'   => 'bg-gray-600 text-white',
                            ];

                            $statusClassBook = [
                                'pending'   => 'bg-blue-600 text-white',
                                'confirmed' => 'bg-yellow-400 text-gray-900',
                                'cancelled' => 'bg-red-600 text-white',
                                'completed' => 'bg-green-600 text-white',
                                'booking'   => 'bg-gray-600 text-white',
                                'booked'    => 'bg-gray-600 text-white',
                            ];

                            $bookingStatus = optional($order->bookings->first())->status;
                        @endphp
                        <tr class="border-b border-gray-700">
                            <td class="px-4 py-3">{{ $order->id }}</td>
                            <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 truncate max-w-[160px]" title="{{ $order->user->name ?? 'Guest' }}">
                                {{ $order->user->name ?? 'Guest' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $order->payment_method === 'transfer_manual' ? 'Transfer manual' : $order->payment_method }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="{{ $statusClassPay[$order->payment_status] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full inline-block min-w-[80px] text-center">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="{{ $statusClassBook[$bookingStatus] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full inline-block min-w-[80px] text-center">
                                    {{ ucfirst($bookingStatus ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($proofUrl)
                                    <a href="{{ $proofUrl }}" target="_blank" class="text-blue-400 hover:underline">
                                        <i class="fas fa-file-alt"></i> View Proof
                                    </a>
                                @else
                                    <span class="text-gray-500">No File</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">Rp {{ number_format($order->bookings->sum('admin_fee'), 0, ',', '.') }}</td>
                            <td class="px-4 py-3 flex gap-4 text-gray-500">
                                <a href="{{ route('order.detail.index', $order->id) }}"
                                   aria-label="View order {{ $order->id }}"
                                   class="hover:text-gray-300">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- Update Payment Status --}}
                                <div class="relative" x-data="{ openPayment:false }">
                                    <button @click="openPayment=!openPayment"
                                            aria-label="Update payment status order {{ $order->id }}"
                                            class="hover:text-blue-400">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </button>
                                    <div x-show="openPayment" x-cloak @click.away="openPayment=false"
                                         class="absolute right-0 mt-2 w-48 bg-[#333333] rounded-md shadow-lg z-50"
                                         style="transform:translateX(-30%);min-width:12rem;">
                                        <div class="py-1">
                                            @foreach (['pending','processing','paid','failed','refunded'] as $st)
                                                <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>$st]) }}"
                                                   class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">
                                                    {{ ucfirst($st) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Update Booking Status --}}
                                <div class="relative" x-data="{ openBooking:false }">
                                    <button @click="openBooking=!openBooking"
                                            aria-label="Update booking status order {{ $order->id }}"
                                            class="hover:text-blue-400">
                                        <i class="fas fa-calendar-check"></i>
                                    </button>
                                    <div x-show="openBooking" x-cloak @click.away="openBooking=false"
                                         class="absolute right-0 mt-2 w-48 bg-[#333333] rounded-md shadow-lg z-50"
                                         style="transform:translateX(-30%);min-width:12rem;">
                                        <div class="py-1">
                                            @foreach (['pending','confirmed','cancelled','completed','booked'] as $bst)
                                                <a href="{{ route('admin.orders.update-booking-status', ['order'=>$order->id,'status'=>$bst]) }}"
                                                   class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">
                                                    {{ ucfirst($bst) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Delete --}}
                                <button
                                    aria-label="Delete order {{ $order->id }}"
                                    class="hover:text-red-500 delete-order"
                                    data-id="{{ $order->id }}"
                                    data-name="{{ $order->user->name ?? 'Guest' }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr class="border-b border-gray-700">
                            <td colspan="10" class="px-4 py-3 text-center">Belum ada order</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </section>

            {{-- MOBILE / TABLET CARDS --}}
            <section class="lg:hidden mx-4 space-y-4 mt-4">
                @forelse($orders as $order)
                    @php
                        $proofUrlM = __proof_cdn_url($order, $__proofDir, $__proofCdnBase);
                        $paymentStatusClass = [
                            'pending'    => 'bg-blue-600 text-white',
                            'processing' => 'bg-yellow-600 text-white',
                            'paid'       => 'bg-green-600 text-white',
                            'failed'     => 'bg-red-600 text-white',
                            'refunded'   => 'bg-purple-600 text-white',
                        ];
                    @endphp
                    <div class="bg-[#292929] rounded-lg p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="text-xs text-gray-400">Order ID</div>
                                <div class="font-semibold text-sm">#{{ $order->id }}</div>
                            </div>
                            <span class="{{ $paymentStatusClass[$order->payment_status] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full text-[10px]">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>

                        <div class="space-y-1 text-xs text-gray-300">
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-400">Date:</span>
                                <span>{{ $order->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-400">Name:</span>
                                <span class="truncate max-w-[60%] text-right">{{ $order->user->name ?? 'Guest' }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-400">Payment:</span>
                                <span>{{ $order->payment_method }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-400">Total:</span>
                                <span class="font-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between gap-2">
                                <span class="text-gray-400">Admin Fee:</span>
                                <span class="font-semibold">Rp {{ number_format($order->bookings->sum('admin_fee'), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between gap-2 items-center">
                                <span class="text-gray-400">File:</span>
                                @if($proofUrlM)
                                    <a href="{{ $proofUrlM }}" target="_blank" class="text-blue-400 hover:underline text-[10px]">
                                        <i class="fas fa-file-alt"></i> View Proof
                                    </a>
                                @else
                                    <span class="text-gray-500 text-[10px]">No File</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 mt-3 pt-2 border-t border-gray-700 justify-end text-[10px]">
                            <a href="{{ route('order.detail.index', $order->id) }}" class="text-gray-400 hover:text-gray-200">
                                <i class="fas fa-eye"></i> View
                            </a>

                            {{-- Payment status (mobile) --}}
                            <div class="relative" x-data="{ openPayment:false }">
                                <button @click="openPayment=!openPayment" class="text-gray-400 hover:text-blue-400">
                                    <i class="fas fa-money-bill-wave"></i> Payment
                                </button>
                                <div x-show="openPayment" x-cloak @click.away="openPayment=false"
                                     class="absolute right-0 bottom-full mb-2 w-40 bg-[#333333] rounded-md shadow-lg z-50">
                                    <div class="py-1">
                                        @foreach (['pending','processing','paid','failed','refunded'] as $st)
                                            <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>$st]) }}"
                                               class="block w-full text-left px-3 py-1.5 text-[10px] hover:bg-[#444444]">
                                                {{ ucfirst($st) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Delete --}}
                            <button class="text-gray-400 hover:text-red-500 delete-order"
                                    data-id="{{ $order->id }}"
                                    data-name="{{ $order->user->name ?? 'Guest' }}">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-[#292929] rounded-lg p-6 text-center text-gray-400">
                        Belum ada order
                    </div>
                @endforelse
            </section>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Alpine !== 'undefined' && !Alpine.initialized) {
        Alpine.start();
    }

    @if(session('success'))
    Swal.fire({
        title: 'Success!',
        text: @json(session('success')),
        icon: 'success',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6',
        background: '#1E1E1F',
        color: '#FFFFFF'
    });
    @endif

    document.querySelectorAll('.delete-order').forEach(function(btn){
        btn.addEventListener('click', function(){
            const orderId   = btn.getAttribute('data-id');
            const orderName = btn.getAttribute('data-name') || 'this order';

            Swal.fire({
                title: 'Konfirmasi',
                text: `Are you sure you want to delete order ${orderName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                background: '#1E1E1F',
                color: '#FFFFFF'
            }).then((res)=>{
                if(res.isConfirmed){
                    const deleteUrl = '{{ route('order.delete', ':id') }}'.replace(':id', orderId);
                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if(data.success){
                            Swal.fire({
                                title: 'Success!',
                                text: 'Order has been successfully deleted.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6',
                                background: '#1E1E1F',
                                color: '#FFFFFF'
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Failed to delete order.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6',
                                background: '#1E1E1F',
                                color: '#FFFFFF'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to delete order.',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF'
                        });
                    });
                }
            });
        });
    });
});
</script>
@endpush
