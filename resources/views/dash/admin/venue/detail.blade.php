@extends('app')
@section('title', 'Detail Pesanan Venue - ' . $venue->name)

@push('styles')
<style>
    :root { color-scheme: dark; }
</style>
@endpush

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')

            <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                    Pesanan Venue: {{ $venue->name }}
                </h1>

                <a href="{{ route('venue.index') }}" class="text-blue-400 hover:underline text-sm mb-4 inline-block">
                    ← Kembali ke daftar venue
                </a>

                @if (session('success'))
                    <div class="mb-4 bg-green-600 text-white px-4 py-2 rounded text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-600 text-white px-4 py-2 rounded text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($venue->orders->count() > 0)
                    <div class="overflow-x-auto bg-[#1e1e1e] rounded-lg border border-gray-700">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Order #</th>
                                    <th class="px-4 py-3">User</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3">Metode Pembayaran</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @foreach ($venue->orders as $order)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3">
                                            <p>{{ $order->user->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $order->user->email }}</p>
                                        </td>
                                        <td class="px-4 py-3">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3">{{ $order->payment_method ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-1 rounded text-xs
                                                @if($order->payment_status == 'paid') bg-green-600
                                                @elseif($order->payment_status == 'processing') bg-yellow-600
                                                @else bg-gray-600 @endif">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($order->payment_status == 'processing')
                                                <form action="{{ route('venue.verify', $order->id) }}" method="POST" onsubmit="return confirm('Verifikasi pembayaran ini?');">
                                                    @csrf
                                                    <button type="submit" class="bg-green-600 hover:bg-green-500 text-white text-xs px-3 py-1 rounded">
                                                        Verifikasi
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-500 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-400 mt-6">Belum ada pesanan untuk venue ini.</p>
                @endif
            </div>
        </main>
    </div>
</div>
@endsection
