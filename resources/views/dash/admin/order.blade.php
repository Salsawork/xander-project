@extends('app')
@section('title', 'Admin Dashboard - Order')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-2xl font-bold p-8 mt-12">Order Management</h1>
                <section
                    class="bg-[#292929] rounded-lg p-6 mx-8 mb-8 flex justify-between text-center text-white max-w-full overflow-x-auto"
                    aria-label="Order status summary">
                    <div class="flex-1 min-w-[80px]">
                        <div class="text-3xl font-extrabold leading-none">{{ $pendingCount }}</div>
                        <div class="font-semibold mt-1">Pending</div>
                    </div>
                    <div class="border-l border-gray-600 mx-6"></div>
                    <div class="flex-1 min-w-[80px]">
                        <div class="text-3xl font-extrabold leading-none">{{ $processingCount }}</div>
                        <div class="font-semibold mt-1">Processing</div>
                    </div>
                    <div class="border-l border-gray-600 mx-6"></div>
                    <div class="flex-1 min-w-[80px]">
                        <div class="text-3xl font-extrabold leading-none">{{ $shippedCount }}</div>
                        <div class="font-semibold mt-1">Shipped</div>
                    </div>
                    <div class="border-l border-gray-600 mx-6"></div>
                    <div class="flex-1 min-w-[80px]">
                        <div class="text-3xl font-extrabold leading-none">{{ $deliveredCount }}</div>
                        <div class="font-semibold mt-1">Delivered</div>
                    </div>
                    <div class="border-l border-gray-600 mx-6"></div>
                    <div class="flex-1 min-w-[80px]">
                        <div class="text-3xl font-extrabold leading-none">{{ $cancelledCount }}</div>
                        <div class="font-semibold mt-1">Cancelled</div>
                    </div>
                    <button aria-label="More options" class="text-gray-400 hover:text-white ml-4 self-center">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                </section>
                <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between mx-8 mb-4 gap-4">
                    <input type="text" placeholder="Phone number or Email"
                        class="bg-transparent border border-gray-600 rounded-md px-3 py-1.5 text-gray-400 placeholder-gray-600 focus:outline-none focus:ring-1 focus:ring-[#0d82ff] focus:border-[#0d82ff] max-w-xs w-full sm:w-auto" />
                    <div class="flex gap-2 text-xs text-gray-600 select-none">
                        <div class="flex items-center gap-1 cursor-default">
                            <span>Status</span>
                            <i class="fas fa-chevron-down text-gray-600"></i>
                        </div>
                        <div class="flex items-center gap-1 cursor-default">
                            <span>Date Range</span>
                        </div>
                    </div>
                </section>
                <section class="overflow-x-auto mx-8">
                    <table class="w-full text-left text-gray-300 border-collapse">
                        <thead class="bg-[#292929] text-sm font-normal">
                            <tr>
                                <th class="px-4 py-3">Order ID</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Payment</th>
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
                                    <td class="px-4 py-3 truncate max-w-[160px]" title="{{ $order->user->name ?? 'Guest' }}">
                                        {{ $order->user->name ?? 'Guest' }}
                                    </td>
                                    <td class="px-4 py-3">{{ $order->payment_method }}</td>
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
                                        <a href="{{ route('order.detail.index', $order->id) }}" aria-label="View order {{ $order->id }}" class="hover:text-gray-300">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" aria-label="Update status order {{ $order->id }}" class="hover:text-blue-400">
                                                <i class="fas fa-shipping-fast"></i>
                                            </button>
                                            <div x-show="open" x-cloak @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-[#333333] rounded-md shadow-lg z-50" style="transform: translateX(-30%); min-width: 12rem;">
                                                <div class="py-1">
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'pending']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link" data-status="Pending">Pending</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'processing']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link" data-status="Processing">Processing</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'packed']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link" data-status="Packed">Packed</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'shipped']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link" data-status="Shipped">Shipped</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'delivered']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link" data-status="Delivered">Delivered</a>
                                                    <a href="{{ route('admin.orders.update-status', ['order' => $order->id, 'status' => 'cancelled']) }}" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#444444] status-link" data-status="Cancelled">Cancelled</a>
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
                                    <td colspan="7" class="px-4 py-3 text-center">Belum ada order</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
        @if(session('success'))
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
                        const deleteUrl = '{{ route("admin.orders.delete", ":id") }}'.replace(':id', orderId);
                        
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
                                    text: data.message || 'Failed to delete order.',
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
