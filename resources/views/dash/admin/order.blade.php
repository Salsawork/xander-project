@extends('app')
@section('title', 'Admin Dashboard - Order')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
        
        /* Hide scrollbar for Chrome, Safari and Opera */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        /* Hide scrollbar for IE, Edge and Firefox */
        .scrollbar-hide {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
@endpush

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-2xl font-bold p-4 sm:p-8 mt-12">Order Management</h1>
                
                <!-- Summary Section with Scroll Arrows for Mobile -->
                <section class="mx-4 sm:mx-8 mb-8" x-data="{ 
                    scrollContainer: null,
                    canScrollLeft: false,
                    canScrollRight: true,
                    init() {
                        this.$nextTick(() => {
                            this.scrollContainer = this.$refs.summaryScroll;
                            this.updateScrollButtons();
                        });
                    },
                    updateScrollButtons() {
                        if (this.scrollContainer) {
                            this.canScrollLeft = this.scrollContainer.scrollLeft > 0;
                            this.canScrollRight = this.scrollContainer.scrollLeft < (this.scrollContainer.scrollWidth - this.scrollContainer.clientWidth - 10);
                        }
                    },
                    scrollLeft() {
                        this.scrollContainer.scrollBy({ left: -200, behavior: 'smooth' });
                        setTimeout(() => this.updateScrollButtons(), 300);
                    },
                    scrollRight() {
                        this.scrollContainer.scrollBy({ left: 200, behavior: 'smooth' });
                        setTimeout(() => this.updateScrollButtons(), 300);
                    }
                }">
                    <div class="relative">
                        <!-- Left Arrow Button (Mobile Only) -->
                        <button 
                            @click="scrollLeft"
                            x-show="canScrollLeft"
                            class="sm:hidden absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-neutral-800 hover:bg-neutral-700 text-white p-2 rounded-full shadow-lg"
                            aria-label="Scroll left">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        <!-- Summary Cards -->
                        <div 
                            x-ref="summaryScroll"
                            @scroll="updateScrollButtons"
                            class="bg-[#292929] rounded-lg p-4 sm:p-6 flex gap-4 sm:justify-between overflow-x-auto sm:overflow-x-visible scrollbar-hide snap-x snap-mandatory"
                            aria-label="Order status summary">
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
                                <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $shippedCount }}</div>
                                <div class="font-semibold mt-1 text-sm sm:text-base">Shipped</div>
                            </div>
                            <div class="border-l border-gray-600 flex-shrink-0"></div>
                            <div class="flex-shrink-0 snap-center min-w-[100px] sm:flex-1 text-center">
                                <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $deliveredCount }}</div>
                                <div class="font-semibold mt-1 text-sm sm:text-base">Delivered</div>
                            </div>
                            <div class="border-l border-gray-600 flex-shrink-0"></div>
                            <div class="flex-shrink-0 snap-center min-w-[100px] sm:flex-1 text-center">
                                <div class="text-2xl sm:text-3xl font-extrabold leading-none">{{ $cancelledCount }}</div>
                                <div class="font-semibold mt-1 text-sm sm:text-base">Cancelled</div>
                            </div>
                        </div>

                        <!-- Right Arrow Button (Mobile Only) -->
                        <button 
                            @click="scrollRight"
                            x-show="canScrollRight"
                            class="sm:hidden absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-neutral-800 hover:bg-neutral-700 text-white p-2 rounded-full shadow-lg"
                            aria-label="Scroll right">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </section>

                <!-- Filter Section -->
                <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between mx-4 sm:mx-8 mb-4 gap-4">
                    <input type="text" placeholder="Insert Name"
                        class="bg-transparent border border-gray-600 rounded-md px-3 py-1.5 text-gray-400 placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-[#0d82ff] focus:border-[#0d82ff] w-full sm:max-w-xs"
                        value="{{ request('search') }}" id="search"
                        onchange="window.location.href='{{ route('order.index') }}?search=' + document.getElementById('search').value + '&status=' + document.getElementById('status').value + '&orderBy=' + document.getElementById('orderBy').value" />
                    <div class="flex flex-col sm:flex-row gap-2 text-xs text-gray-600 select-none">
                        <div class="relative">
                            <select
                                class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full sm:w-40 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="status"
                                onchange="window.location.href='{{ route('order.index') }}?search=' + document.getElementById('search').value + '&status=' + document.getElementById('status').value + '&orderBy=' + document.getElementById('orderBy').value">
                                <option value="" disabled selected>Filter by Status</option>
                                @foreach (['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned'] as $option)
                                    <option value="{{ $option }}"
                                        {{ request('status') == $option ? 'selected' : '' }}>{{ ucfirst($option) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="relative">
                            <select
                                class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full sm:w-40 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="orderBy"
                                onchange="window.location.href='{{ route('order.index') }}?search=' + document.getElementById('search').value + '&status=' + document.getElementById('status').value + '&orderBy=' + this.value">
                                <option value="" {{ request('orderBy') ? '' : 'selected' }}>Sort by Date</option>
                                <option value="asc" {{ request('orderBy') == 'asc' ? 'selected' : '' }}>Oldest to Newest</option>
                                <option value="desc" {{ request('orderBy') == 'desc' ? 'selected' : '' }}>Newest to Oldest</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Desktop Table View -->
                <section class="hidden lg:block overflow-x-auto mx-8">
                    <table class="w-full text-left text-gray-300 border-collapse">
                        <thead class="bg-[#292929] text-sm font-normal">
                            <tr>
                                <th class="px-4 py-3">Order ID</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Payment</th>
                                <th class="px-4 py-3">File</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Status Pengiriman</th>
                                <th class="px-4 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm font-normal">
                            @forelse($orders as $order)
                                <tr class="border-b border-gray-700">
                                    <td class="px-4 py-3">{{ $order->id }}</td>
                                    <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 truncate max-w-[160px]"
                                        title="{{ $order->user->name ?? 'Guest' }}">
                                        {{ $order->user->name ?? 'Guest' }}
                                    </td>
                                    <td class="px-4 py-3">{{ $order->payment_method }}</td>
                                    <td>
                                        @if ($order->file)
                                            <a href="{{ asset('storage/' . $order->file) }}" target="_blank"
                                                class="text-blue-400 hover:underline">
                                                <i class="fas fa-file-alt"></i> View File
                                            </a>
                                        @else
                                            <span class="text-gray-500">No File</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusClass = [
                                                'pending' => 'bg-[#3b82f6] text-white',
                                                'processing' => 'bg-[#fbbf24] text-[#78350f]',
                                                'packed' => 'bg-[#3b82f6] text-white',
                                                'shipped' => 'bg-[#3b82f6] text-white',
                                                'delivered' => 'bg-[#22c55e] text-white',
                                                'cancelled' => 'bg-[#f87171] text-[#7f1d1d]',
                                                'returned' => 'bg-[#f87171] text-[#7f1d1d]',
                                            ];
                                        @endphp
                                        <span
                                            class="{{ $statusClass[$order->delivery_status] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full inline-block min-w-[80px] text-center">
                                            {{ ucfirst($order->delivery_status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 flex gap-4 text-gray-500">
                                        <a href="{{ route('order.detail.index', $order->id) }}"
                                            aria-label="View order {{ $order->id }}" class="hover:text-gray-300">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open"
                                                aria-label="Update status order {{ $order->id }}"
                                                class="hover:text-blue-400">
                                                <i class="fas fa-shipping-fast"></i>
                                            </button>
                                            <div x-show="open" x-cloak @click.away="open = false"
                                                class="absolute right-0 mt-2 w-48 bg-[#333333] rounded-md shadow-lg z-50"
                                                style="transform: translateX(-30%); min-width: 12rem;">
                                                <div class="py-1">
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'pending']) }}"
                                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                        data-status="Pending">Pending</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'processing']) }}"
                                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                        data-status="Processing">Processing</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'packed']) }}"
                                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                        data-status="Packed">Packed</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'shipped']) }}"
                                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                        data-status="Shipped">Shipped</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'delivered']) }}"
                                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                        data-status="Delivered">Delivered</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'cancelled']) }}"
                                                        class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                        data-status="Cancelled">Cancelled</a>
                                                </div>
                                            </div>
                                        </div>
                                        <button aria-label="Delete order {{ $order->id }}"
                                            class="hover:text-red-500 delete-order" data-id="{{ $order->id }}"
                                            data-name="{{ $order->user->name ?? 'Guest' }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="border-b border-gray-700">
                                    <td colspan="8" class="px-4 py-3 text-center">Belum ada order</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </section>

                <!-- Tablet & Mobile Card View -->
                <section class="lg:hidden mx-4 space-y-4">
                    @forelse($orders as $order)
                        <div class="bg-[#292929] rounded-lg p-4" x-data="{ open: false }">
                            <!-- Card Header -->
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <div class="text-sm text-gray-400">Order ID</div>
                                    <div class="font-semibold">{{ $order->id }}</div>
                                </div>
                                @php
                                    $statusClass = [
                                        'pending' => 'bg-[#3b82f6] text-white',
                                        'processing' => 'bg-[#fbbf24] text-[#78350f]',
                                        'packed' => 'bg-[#3b82f6] text-white',
                                        'shipped' => 'bg-[#3b82f6] text-white',
                                        'delivered' => 'bg-[#22c55e] text-white',
                                        'cancelled' => 'bg-[#f87171] text-[#7f1d1d]',
                                        'returned' => 'bg-[#f87171] text-[#7f1d1d]',
                                    ];
                                @endphp
                                <span class="{{ $statusClass[$order->delivery_status] ?? 'bg-gray-500 text-white' }} px-3 py-1 rounded-full text-xs">
                                    {{ ucfirst($order->delivery_status) }}
                                </span>
                            </div>

                            <!-- Card Content -->
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Date:</span>
                                    <span>{{ $order->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Name:</span>
                                    <span class="truncate ml-2">{{ $order->user->name ?? 'Guest' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Payment:</span>
                                    <span>{{ $order->payment_method }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Total:</span>
                                    <span class="font-semibold">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400">File:</span>
                                    @if ($order->file)
                                        <a href="{{ asset('storage/' . $order->file) }}" target="_blank"
                                            class="text-blue-400 hover:underline text-xs">
                                            <i class="fas fa-file-alt"></i> View File
                                        </a>
                                    @else
                                        <span class="text-gray-500 text-xs">No File</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div class="flex gap-3 mt-4 pt-3 border-t border-gray-700 justify-end">
                                <a href="{{ route('order.detail.index', $order->id) }}"
                                    aria-label="View order {{ $order->id }}" 
                                    class="text-gray-400 hover:text-gray-300">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <div class="relative">
                                    <button @click="open = !open"
                                        aria-label="Update status order {{ $order->id }}"
                                        class="text-gray-400 hover:text-blue-400">
                                        <i class="fas fa-shipping-fast"></i> Status
                                    </button>
                                    <div x-show="open" x-cloak @click.away="open = false"
                                        class="absolute right-0 bottom-full mb-2 w-48 bg-[#333333] rounded-md shadow-lg z-50">
                                        <div class="py-1">
                                            <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'pending']) }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                data-status="Pending">Pending</a>
                                            <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'processing']) }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                data-status="Processing">Processing</a>
                                            <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'packed']) }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                data-status="Packed">Packed</a>
                                            <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'shipped']) }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                data-status="Shipped">Shipped</a>
                                            <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'delivered']) }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                data-status="Delivered">Delivered</a>
                                            <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'cancelled']) }}"
                                                class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link"
                                                data-status="Cancelled">Cancelled</a>
                                        </div>
                                    </div>
                                </div>
                                <button aria-label="Delete order {{ $order->id }}"
                                    class="text-gray-400 hover:text-red-500 delete-order" 
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
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi Alpine.js jika belum
            if (typeof Alpine !== 'undefined' && !Alpine.initialized) {
                Alpine.start();
            }

            // Tampilkan SweetAlert jika ada session success
            @if (session('success'))
                Swal.fire({
                    title: 'Success!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    background: '#1E1E1F',
                    color: '#FFFFFF'
                });
            @endif

            // Tambahkan event listener untuk link update status
            const statusLinks = document.querySelectorAll('.status-link');
            statusLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const statusValue = e.target.getAttribute('data-status');

                    Swal.fire({
                        title: 'Konfirmasi',
                        text: `Are you sure you want to change the status to ${statusValue}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Change!',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        background: '#1E1E1F',
                        color: '#FFFFFF'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = e.target.href;
                        }
                    });
                });
            });

            // Tambahkan event listener untuk tombol hapus order
            const deleteOrderButtons = document.querySelectorAll('.delete-order');
            deleteOrderButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-id');
                    const orderName = this.getAttribute('data-name');

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
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Kirim request hapus order ke server
                            const deleteUrl = '{{ route('admin.orders.delete', ':id') }}'
                                .replace(':id', orderId);

                            fetch(deleteUrl, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: 'Order has been successfully deleted.',
                                            icon: 'success',
                                            confirmButtonText: 'OK',
                                            confirmButtonColor: '#3085d6',
                                            background: '#1E1E1F',
                                            color: '#FFFFFF'
                                        });
                                        // Muat ulang halaman
                                        window.location.reload();
                                    } else {
                                        Swal.fire({
                                            title: 'Error!',
                                            text: data.message ||
                                                'Failed to delete order.',
                                            icon: 'error',
                                            confirmButtonText: 'OK',
                                            confirmButtonColor: '#3085d6',
                                            background: '#1E1E1F',
                                            color: '#FFFFFF'
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
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