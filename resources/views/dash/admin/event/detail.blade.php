@extends('app')
@section('title', 'Detail Pemesanan Event')

@push('styles')
<style>
    :root {
        color-scheme: dark;
        --page-bg: #0a0a0a;
        --card-bg: #1c1c1c;
        --border-color: #2c2c2c;
    }

    body {
        background: var(--page-bg);
        color: white;
        font-family: 'Inter', sans-serif;
    }

    .card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid #2c2c2c;
    }

    th {
        color: #ccc;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }

    td {
        color: #e5e5e5;
        font-size: 0.9rem;
    }

    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pending { background-color: #555; }
    .status-paid { background-color: #2563eb; }
    .status-verified { background-color: #16a34a; }
    .status-rejected { background-color: #dc2626; }
</style>
@endpush

@section('content')
<div class="min-h-screen flex flex-col bg-neutral-900 text-white">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')

        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')

            <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                <h1 class="text-2xl sm:text-3xl font-bold mb-6">
                    Detail Pemesanan Event — {{ $event->name }}
                </h1>

                {{-- Jika tidak ada pesanan --}}
                @if($orders->isEmpty())
                    <div class="card text-center text-gray-400 py-10">
                        <p class="text-lg font-medium mb-2">Belum ada pemesanan tiket untuk event ini.</p>
                        <p class="text-sm text-gray-500">Silakan kembali ke daftar event untuk melihat data lainnya.</p>
                        <a href="{{ route('admin.event.index') }}"
                           class="inline-block mt-6 text-sm text-blue-400 hover:text-blue-300 transition">
                           ← Kembali ke daftar event
                        </a>
                    </div>
                @else
                <div class="card overflow-x-auto">
                    <h2 class="text-lg font-semibold mb-4 text-gray-300">Daftar Pemesanan</h2>

                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Order Number</th>
                                <th>Nama User</th>
                                <th>Total Payment</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Bukti Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $i => $order)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->user->name ?? '-' }}</td>
                                <td>Rp {{ number_format($order->total_payment, 0, ',', '.') }}</td>
                                <td>
                                    <span class="status-badge
                                        @if($order->status === 'pending') status-pending
                                        @elseif($order->status === 'paid') status-paid
                                        @elseif($order->status === 'verified') status-verified
                                        @elseif($order->status === 'rejected') status-rejected
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                <td>
                                    @if($order->bukti_payment)
                                        <a href="{{ asset('images/payments/events/' . $order->bukti_payment) }}" 
                                        target="_blank" class="text-blue-400 hover:underline">
                                        Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-500">Belum ada</span>
                                    @endif                                
                                </td>
                                <td class="flex gap-2">
                                    @if($order->status === 'paid')
                                        <form id="verifyForm-{{ $order->id }}" action="{{ route('admin.event.verify', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="button" class="verifyBtn bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-xs font-semibold">
                                                Verifikasi
                                            </button>
                                        </form>
                                        <form id="rejectForm-{{ $order->id }}" action="{{ route('admin.event.reject', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="button" class="rejectBtn bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-xs font-semibold">
                                                Tolak
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-500 text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-6">
                        <a href="{{ route('admin.event.index') }}"
                           class="inline-block text-sm text-gray-400 hover:text-white transition">
                           ← Kembali ke daftar event
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </main>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        confirmButtonColor: '#1e90ff'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}",
        confirmButtonColor: '#d33'
    });
</script>
@endif

{{-- Konfirmasi untuk setiap tombol --}}
<script>
document.querySelectorAll('.verifyBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = btn.closest('form');
        Swal.fire({
            title: 'Verifikasi Pembayaran?',
            text: "Pastikan data sudah benar sebelum verifikasi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, verifikasi!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});

document.querySelectorAll('.rejectBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        const form = btn.closest('form');
        Swal.fire({
            title: 'Tolak Pembayaran?',
            text: "Tindakan ini tidak bisa dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, tolak!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endsection
