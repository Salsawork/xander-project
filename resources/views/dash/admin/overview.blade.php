@extends('app')
@section('title', 'Admin Dashboard - Overview')

@section('content')
    <style>
        :root {
            color-scheme: dark;
            --page-bg: #0a0a0a;
        }

        html,
        body {
            height: 100%;
            min-height: 100%;
            background: var(--page-bg);
            overscroll-behavior-y: none;
            overscroll-behavior-x: none;
            touch-action: pan-y;
            -webkit-text-size-adjust: 100%;
        }

        #antiBounceBg {
            position: fixed;
            left: 0;
            right: 0;
            top: -120svh;
            bottom: -120svh;
            background: var(--page-bg);
            z-index: -1;
            pointer-events: none;
        }

        #app,
        main {
            background: var(--page-bg);
        }

        .main-scroll {
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            background: #0a0a0a;
        }

        .panel {
            background: #292929;
        }
    </style>

    <div id="antiBounceBg" aria-hidden="true"></div>

    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')

            <main class="main-scroll flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')

                <h1 class="text-2xl font-bold p-8 mt-12">Shop Overview</h1>

                {{-- === Statistik Utama === --}}
                <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 px-8">
                    <div class="panel rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Page Visit</h2>
                        <div class="text-3xl font-extrabold leading-none mb-1">{{ $visits }}</div>
                    </div>

                    <div class="panel rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Earnings (Last 30 Days)</h2>
                        <div class="text-3xl font-extrabold leading-none mb-1">
                            Rp. {{ number_format($monthlyEarnings, 0, ',', '.') }}
                        </div>
                    </div>
                </section>

                {{-- === Sales Chart === --}}
                <section class="grid grid-cols-1 lg:grid-cols-1 gap-6 px-8">
                    <div class="panel lg:col-span-2 rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Sales Report</h3>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <div class="overflow-x-auto">
                            <canvas id="revenueChart"></canvas>
                        </div>

                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                            const months = @json($labels);
                            const revenues = @json($dataPoints);

                            document.addEventListener('DOMContentLoaded', function() {
                                const ctx = document.getElementById('revenueChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: months,
                                        datasets: [{
                                            label: 'Pendapatan Bulanan',
                                            data: revenues,
                                            backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { labels: { color: '#e5e7eb' } }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: { color: '#e5e7eb' },
                                                grid: { color: 'rgba(255,255,255,.06)' }
                                            },
                                            x: {
                                                ticks: { color: '#e5e7eb' },
                                                grid: { color: 'rgba(255,255,255,.06)' }
                                            }
                                        }
                                    }
                                });
                            });
                        </script>
                        <style>
                            #revenueChart {
                                min-height: 320px;
                            }
                        </style>
                    </div>
                </section>

                {{-- === Recent Transactions === --}}
                <section class="grid grid-cols-1 lg:grid-cols-1 gap-6 mt-6 px-8">
                    <div class="panel lg:col-span-2 rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Recent Transaction</h3>
                            <button aria-label="More options"
                                class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <ul class="space-y-4 text-sm">
                            @foreach ($recentOrders as $order)
                                <li class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        @if ($order->payment_status == 'pending')
                                            <span class="text-yellow-400"><i class="fas fa-exclamation-circle"></i></span>
                                        @elseif ($order->payment_status == 'processing')
                                            <span class="text-blue-400"><i class="fas fa-spinner fa-spin"></i></span>
                                        @elseif ($order->payment_status == 'paid')
                                            <span class="text-green-400"><i class="fas fa-check-circle"></i></span>
                                        @elseif ($order->payment_status == 'failed')
                                            <span class="text-red-400"><i class="fas fa-times-circle"></i></span>
                                        @elseif ($order->payment_status == 'refunded')
                                            <span class="text-gray-400"><i class="fas fa-undo"></i></span>
                                        @endif
                                        <div>
                                            <div class="font-semibold">Order #{{ $order->id }}</div>
                                            <div class="text-gray-400 text-xs">
                                                {{ $order->created_at->format('d/m/Y') }}
                                                <span class="mx-1">|</span>
                                                {{ $order->created_at->format('H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold">Rp.
                                            {{ number_format($order->total, 0, ',', '.') }}
                                        </div>
                                        <div class="text-gray-400 text-xs">{{ $order->payment_status }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
