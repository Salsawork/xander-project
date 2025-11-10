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
    .scroll-safe{background-color:#171717;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
</style>
@endpush

@php
    // Path server tempat file bukti transfer disimpan
    $__proofDir     = base_path('../demo-xanders/images/payment_proof');
    // Base URL CDN untuk menampilkan file (HARUS ini, bukan storage)
    $__proofCdnBase = 'https://xanderbilliard.site/images/payment_proof/';

    if (!function_exists('__proof_cdn_url')) {
        /**
         * Kembalikan URL CDN untuk bukti transfer.
         * Urutan:
         * 1) Scan folder ../demo-xanders/images/payment_proof pakai order_number / id
         * 2) Jika tidak ada, pakai basename dari field $order->file / $order->payment_proof,
         *    tapi tetap arahkan ke CDN images/payment_proof (bukan storage)
         */
        function __proof_cdn_url($order, string $dir, string $cdnBase): ?string {
            $allowedExt = ['jpg','jpeg','png','webp','pdf'];
            $keys = [];
            if (!empty($order->order_number)) $keys[] = (string)$order->order_number;
            $keys[] = (string)$order->id;

            $matches = [];
            if (is_dir($dir)) {
                foreach ($keys as $k) {
                    $glob = glob($dir . '/*' . $k . '*.{'.implode(',', $allowedExt).'}', GLOB_BRACE) ?: [];
                    if (!empty($glob)) $matches = array_merge($matches, $glob);
                }
            }
            if (!empty($matches)) {
                usort($matches, fn($a,$b) => filemtime($b) <=> filemtime($a));
                return $cdnBase . basename($matches[0]);
            }

            // Fallback: ambil nama file dari field database, tetapi tetap arahkan ke CDN images/payment_proof
            $fieldCandidates = [
                $order->file        ?? null,
                $order->payment_proof ?? null,
                $order->proof_file  ?? null,
            ];
            foreach ($fieldCandidates as $f) {
                if (!empty($f)) {
                    $name = basename(str_replace('\\','/',$f));
                    if ($name !== '' && $name !== '.' && $name !== '..') {
                        return $cdnBase . $name; // PAKSA ke images/payment_proof
                    }
                }
            }
            return null;
        }
    }
@endphp

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')

            <h1 class="text-2xl font-bold p-4 sm:p-8 mt-12">Order Booking Management</h1>

            <!-- Summary -->
            <section class="mx-4 sm:mx-8 mb-8" x-data="{
                scrollContainer:null,canScrollLeft:false,canScrollRight:true,
                init(){this.$nextTick(()=>{this.scrollContainer=this.$refs.summaryScroll;this.updateScrollButtons();});},
                updateScrollButtons(){if(this.scrollContainer){this.canScrollLeft=this.scrollContainer.scrollLeft>0;this.canScrollRight=this.scrollContainer.scrollLeft<(this.scrollContainer.scrollWidth-this.scrollContainer.clientWidth-10);}},
                scrollLeft(){this.scrollContainer.scrollBy({left:-200,behavior:'smooth'});setTimeout(()=>this.updateScrollButtons(),300);},
                scrollRight(){this.scrollContainer.scrollBy({left:200,behavior:'smooth'});setTimeout(()=>this.updateScrollButtons(),300);}
            }">
                <div class="relative">
                    <button @click="scrollLeft" x-show="canScrollLeft" class="sm:hidden absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-neutral-800 hover:bg-neutral-700 text-white p-2 rounded-full shadow-lg" aria-label="Scroll left">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <div x-ref="summaryScroll" @scroll="updateScrollButtons" class="bg-[#292929] rounded-lg p-4 sm:p-6 flex gap-4 sm:justify-between overflow-x-auto sm:overflow-x-visible scrollbar-hide snap-x snap-mandatory">
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

                    <button @click="scrollRight" x-show="canScrollRight" class="sm:hidden absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-neutral-800 hover:bg-neutral-700 text-white p-2 rounded-full shadow-lg" aria-label="Scroll right">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- Filter -->
            <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between mx-4 sm:mx-8 mb-4 gap-4">
                <input type="text" placeholder="Insert Name"
                    class="bg-transparent border border-gray-600 rounded-md px-3 py-1.5 text-gray-400 placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-[#0d82ff] focus:border-[#0d82ff] w-full sm:max-w-xs"
                    value="{{ request('search') }}" id="search"
                    onchange="window.location.href='{{ route('order.index.booking') }}?search='+document.getElementById('search').value+'&status='+document.getElementById('status').value+'&orderBy='+document.getElementById('orderBy').value" />

                <div class="flex flex-col sm:flex-row gap-2 text-xs text-gray-600 select-none">
                    <div class="relative">
                        <select class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full sm:w-40 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="status"
                                onchange="window.location.href='{{ route('order.index.booking') }}?search='+document.getElementById('search').value+'&status='+document.getElementById('status').value+'&orderBy='+document.getElementById('orderBy').value">
                            <option value="" disabled selected>Filter by Status</option>
                            @foreach (['pending','processing','paid','failed','refunded'] as $opt)
                                <option value="{{ $opt }}" {{ request('status')==$opt?'selected':'' }}>{{ ucfirst($opt) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full sm:w-40 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="orderBy"
                                onchange="window.location.href='{{ route('order.index.booking') }}?search='+document.getElementById('search').value+'&status='+document.getElementById('status').value+'&orderBy='+this.value">
                            <option value="" {{ request('orderBy')?'':'selected' }}>Sort by Date</option>
                            <option value="asc"  {{ request('orderBy')=='asc'?'selected':'' }}>Oldest to Newest</option>
                            <option value="desc" {{ request('orderBy')=='desc'?'selected':'' }}>Newest to Oldest</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- Desktop Table -->
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
                                // URL bukti transfer SELALU diarahkan ke images/payment_proof
                                $proofUrl = __proof_cdn_url($order, $__proofDir, $__proofCdnBase);
                                $statusClassPay = [
                                    'pending'=>'bg-blue-600 text-white',
                                    'processing'=>'bg-yellow-400 text-gray-900',
                                    'paid'=>'bg-green-600 text-white',
                                    'failed'=>'bg-red-600 text-white',
                                    'refunded'=>'bg-gray-600 text-white',
                                ];
                                $statusClassBook = [
                                    'pending'=>'bg-blue-600 text-white',
                                    'confirmed'=>'bg-yellow-400 text-gray-900',
                                    'cancelled'=>'bg-red-600 text-white',
                                    'completed'=>'bg-green-600 text-white',
                                    'booking'=>'bg-gray-600 text-white',
                                    'booked'=>'bg-gray-600 text-white',
                                ];
                                $bookingStatus = optional($order->bookings->first())->status;
                            @endphp
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-3">{{ $order->id }}</td>
                                <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 truncate max-w-[160px]" title="{{ $order->user->name ?? 'Guest' }}">
                                    {{ $order->user->name ?? 'Guest' }}
                                </td>
                                <td class="px-4 py-3">{{ $order->payment_method === 'transfer_manual' ? 'Transfer manual' : $order->payment_method }}</td>
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
                                <!-- FILE column → SELALU ke images/payment_proof -->
                                <td class="px-4 py-3">
                                    @if ($proofUrl)
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
                                    <a href="{{ route('order.detail.index', $order->id) }}" aria-label="View order {{ $order->id }}" class="hover:text-gray-300">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <div class="relative" x-data="{ openPayment:false }">
                                        <button @click="openPayment=!openPayment" aria-label="Update payment status order {{ $order->id }}" class="hover:text-blue-400">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </button>
                                        <div x-show="openPayment" x-cloak @click.away="openPayment=false" class="absolute right-0 mt-2 w-48 bg-[#333333] rounded-md shadow-lg z-50" style="transform:translateX(-30%);min-width:12rem;">
                                            <div class="py-1">
                                                <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'pending']) }}"    class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Pending</a>
                                                <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'processing']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Processing</a>
                                                <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'paid']) }}"       class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Paid</a>
                                                <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'failed']) }}"     class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Failed</a>
                                                <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'refunded']) }}"  class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Refunded</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative" x-data="{ openBooking:false }">
                                        <button @click="openBooking=!openBooking" aria-label="Update booking status order {{ $order->id }}" class="hover:text-blue-400">
                                            <i class="fas fa-calendar-check"></i>
                                        </button>
                                        <div x-show="openBooking" x-cloak @click.away="openBooking=false" class="absolute right-0 mt-2 w-48 bg-[#333333] rounded-md shadow-lg z-50" style="transform:translateX(-30%);min-width:12rem;">
                                            <div class="py-1">
                                                <a href="{{ route('admin.orders.update-booking-status', ['order'=>$order->id,'status'=>'pending']) }}"   class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Pending</a>
                                                <a href="{{ route('admin.orders.update-booking-status', ['order'=>$order->id,'status'=>'confirmed']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Confirmed</a>
                                                <a href="{{ route('admin.orders.update-booking-status', ['order'=>$order->id,'status'=>'cancelled']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Cancelled</a>
                                                <a href="{{ route('admin.orders.update-booking-status', ['order'=>$order->id,'status'=>'completed']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Completed</a>
                                                <a href="{{ route('admin.orders.update-booking-status', ['order'=>$order->id,'status'=>'booked']) }}"    class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Booked</a>
                                            </div>
                                        </div>
                                    </div>

                                    <button aria-label="Delete order {{ $order->id }}" class="hover:text-red-500 delete-order" data-id="{{ $order->id }}" data-name="{{ $order->user->name ?? 'Guest' }}">
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

            <!-- Mobile / Tablet Cards -->
            <section class="lg:hidden mx-4 space-y-4">
                @forelse($orders as $order)
                    @php
                        $proofUrlM = __proof_cdn_url($order, $__proofDir, $__proofCdnBase);
                        $paymentStatusClass = [
                            'pending'=>'bg-blue-600 text-white',
                            'processing'=>'bg-yellow-600 text-white',
                            'paid'=>'bg-green-600 text-white',
                            'failed'=>'bg-red-600 text-white',
                            'refunded'=>'bg-purple-600 text-white',
                        ];
                    @endphp
                    <div class="bg-[#292929] rounded-lg p-4" x-data="{ open:false }">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="text-sm text-gray-400">Order ID</div>
                                <div class="font-semibold">{{ $order->id }}</div>
                            </div>
                            <span class="{{ $paymentStatusClass[$order->payment_status] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full text-xs">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-400">Date:</span><span>{{ $order->created_at->format('d/m/Y') }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Name:</span><span class="truncate ml-2">{{ $order->user->name ?? 'Guest' }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Payment:</span><span>{{ $order->payment_method }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Total:</span><span class="font-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-400">Admin Fee:</span><span class="font-semibold">Rp {{ number_format($order->bookings->sum('admin_fee'), 0, ',', '.') }}</span></div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">File:</span>
                                @if ($proofUrlM)
                                    <a href="{{ $proofUrlM }}" target="_blank" class="text-blue-400 hover:underline text-xs">
                                        <i class="fas fa-file-alt"></i> View Proof
                                    </a>
                                @else
                                    <span class="text-gray-500 text-xs">No File</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-3 mt-4 pt-3 border-t border-gray-700 justify-end">
                            <a href="{{ route('order.detail.index', $order->id) }}" class="text-gray-400 hover:text-gray-300">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <div class="relative" x-data="{ openPayment:false }">
                                <button @click="openPayment=!openPayment" class="text-gray-400 hover:text-blue-400">
                                    <i class="fas fa-money-bill-wave"></i> Payment
                                </button>
                                <div x-show="openPayment" x-cloak @click.away="openPayment=false" class="absolute right-0 bottom-full mb-2 w-48 bg-[#333333] rounded-md shadow-lg z-50">
                                    <div class="py-1">
                                        <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'pending']) }}"    class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Pending</a>
                                        <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'processing']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Processing</a>
                                        <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'paid']) }}"       class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Paid</a>
                                        <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'failed']) }}"     class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Failed</a>
                                        <a href="{{ route('admin.orders.update-payment-status', ['order'=>$order->id,'status'=>'refunded']) }}"  class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444]">Refunded</a>
                                    </div>
                                </div>
                            </div>
                            <button class="text-gray-400 hover:text-red-500 delete-order" data-id="{{ $order->id }}" data-name="{{ $order->user->name ?? 'Guest' }}">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-[#292929] rounded-lg p-6 text-center text-gray-400">Belum ada order</div>
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
    if (typeof Alpine !== 'undefined' && !Alpine.initialized) { Alpine.start(); }

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

    document.querySelectorAll('.delete-order').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            const orderId = btn.getAttribute('data-id');
            const orderName = btn.getAttribute('data-name');
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
                    }).then(r=>r.json()).then(data=>{
                        if(data.success){
                            Swal.fire({
                                title: 'Success!',
                                text: 'Order has been successfully deleted.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6',
                                background: '#1E1E1F',
                                color: '#FFFFFF'
                            }).then(()=>window.location.reload());
                        }else{
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
                    }).catch(err=>{
                        console.error(err);
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
