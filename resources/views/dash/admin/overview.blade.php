@extends('app')
@section('title', 'Admin Dashboard - Overview')

@section('content')
    <style>
        :root {
            color-scheme: dark;
            --page-bg: #0a0a0a;
            --panel-bg: #1a1a1a;
            --panel-hover: #222;
            --accent-primary: #6366f1;
            --accent-secondary: #8b5cf6;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;

            /* Topbar selalu di atas */
            --topbar-z: 70;
        }

        html, body {
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

        #app, main { background: var(--page-bg); }

        .main-scroll {
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            background: #0a0a0a;
        }

        /* === Pastikan TOP BAR selalu di atas card yang ter-transform === */
        /* Topbar partial kamu pakai <header class="... fixed top-0 ..."> */
        header.fixed,
        header[class*="fixed"] {
            z-index: var(--topbar-z) !important;
        }

        /* Semua card/chart ada di bawah topbar (z-index lebih rendah) */
        .panel,
        .transaction-item,
        .chart-container,
        #revenueChart {
            position: relative;
            z-index: 0;
        }

        .panel {
            background: linear-gradient(145deg, #1a1a1a 0%, #161616 100%);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .panel::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0; /* garis aksen juga di bawah topbar */
        }

        .panel:hover {
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
        }

        .panel:hover::before { opacity: 1; }

        .stat-card { position: relative; overflow: hidden; }
        .stat-icon {
            position: absolute;
            right: 20px; top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.1;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .stat-card:hover .stat-icon { opacity: 0.2; transform: translateY(-50%) scale(1.1); }

        .stat-value {
            background: linear-gradient(135deg, #fff 0%, #a0a0a0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chart-container {
            position: relative;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
        }

        #revenueChart { min-height: 360px; }

        .transaction-item {
            padding: 16px; border-radius: 10px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .transaction-item:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .status-pending   { background: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-processing{ background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }
        .status-paid      { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .status-failed    { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
        .status-refunded  { background: rgba(156, 163, 175, 0.1); color: #9ca3af; border: 1px solid rgba(156, 163, 175, 0.2); }

        .page-title{
            background: linear-gradient(135deg, #fff 0%, #a0a0a0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .section-title{
            font-size: 1.125rem; font-weight: 700; color: #e5e7eb;
            display: flex; align-items: center; gap: 10px;
        }
        .section-title::before{
            content: ''; width: 4px; height: 20px;
            background: linear-gradient(180deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 2px;
        }

        .divider{
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            margin: 16px 0;
        }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px);} to { opacity: 1; transform: translateY(0);} }
        .animate-fade-in { animation: fadeInUp .6s ease-out forwards; }
        .animate-delay-1 { animation-delay: .1s; opacity: 0; }
        .animate-delay-2 { animation-delay: .2s; opacity: 0; }
        .animate-delay-3 { animation-delay: .3s; opacity: 0; }
    </style>

    <div id="antiBounceBg" aria-hidden="true"></div>

    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')

            <main class="main-scroll flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')

                <h1 class="page-title p-8 mt-12">Shop Overview</h1>

                {{-- === Statistik Utama === --}}
                <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 px-8">
                    <div class="panel stat-card rounded-xl p-6 shadow-lg animate-fade-in animate-delay-1">
                        <div class="stat-icon"><i class="fas fa-eye"></i></div>
                        <h2 class="font-semibold text-xs uppercase tracking-wide text-gray-400 mb-2">Page Visits</h2>
                        <div class="stat-value text-4xl font-extrabold leading-none mb-2">{{ number_format($visits) }}</div>
                        <div class="flex items-center gap-2 text-xs text-green-400">
                            <i class="fas fa-arrow-up"></i><span>Live tracking</span>
                        </div>
                    </div>

                    <div class="panel stat-card rounded-xl p-6 shadow-lg animate-fade-in animate-delay-2">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <h2 class="font-semibold text-xs uppercase tracking-wide text-gray-400 mb-2">Monthly Earnings</h2>
                        <div class="stat-value text-4xl font-extrabold leading-none mb-2">
                            Rp {{ number_format($monthlyEarnings, 0, ',', '.') }}
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <i class="far fa-calendar"></i><span>Last 30 days</span>
                        </div>
                    </div>
                </section>

                {{-- === Sales Chart === --}}
                <section class="grid grid-cols-1 lg:grid-cols-1 gap-6 px-8 animate-fade-in animate-delay-3">
                    <div class="panel lg:col-span-2 rounded-xl p-6 shadow-lg">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="section-title">Sales Report</h3>
                            <div class="flex gap-2"><!-- actions optional --></div>
                        </div>
                        <div class="divider"></div>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </section>

                {{-- === Recent Transactions === --}}
                <section class="grid grid-cols-1 lg:grid-cols-1 gap-6 mt-6 px-8 pb-8">
                    <div class="panel lg:col-span-2 rounded-xl p-6 shadow-lg">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="section-title">Recent Transactions</h3>
                            <button aria-label="More options"
                                class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all flex items-center justify-center">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <div class="divider"></div>
                        <div class="space-y-3">
                            @forelse ($recentOrders as $order)
                                <div class="transaction-item">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                                #{{ substr($order->id, 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="font-semibold text-white">Order #{{ $order->id }}</div>
                                                <div class="text-gray-400 text-xs flex items-center gap-2 mt-1">
                                                    <i class="far fa-calendar-alt"></i>
                                                    {{ $order->created_at->format('d/m/Y') }}
                                                    <span class="text-gray-600">•</span>
                                                    <i class="far fa-clock"></i>
                                                    {{ $order->created_at->format('H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-bold text-lg text-white mb-1">
                                                Rp {{ number_format($order->total, 0, ',', '.') }}
                                            </div>
                                            @if ($order->payment_status == 'pending')
                                                <span class="status-badge status-pending"><i class="fas fa-clock"></i> Pending</span>
                                            @elseif ($order->payment_status == 'processing')
                                                <span class="status-badge status-processing"><i class="fas fa-spinner fa-pulse"></i> Processing</span>
                                            @elseif ($order->payment_status == 'paid')
                                                <span class="status-badge status-paid"><i class="fas fa-check-circle"></i> Paid</span>
                                            @elseif ($order->payment_status == 'failed')
                                                <span class="status-badge status-failed"><i class="fas fa-times-circle"></i> Failed</span>
                                            @elseif ($order->payment_status == 'refunded')
                                                <span class="status-badge status-refunded"><i class="fas fa-undo"></i> Refunded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3 opacity-30"></i>
                                    <p>No recent transactions</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const months = @json($labels);
        const revenues = @json($dataPoints);

        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart').getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.8)');
            gradient.addColorStop(1, 'rgba(139, 92, 246, 0.3)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: revenues,
                        backgroundColor: gradient,
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#e5e7eb',
                                font: { size: 13, weight: '600' },
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#e5e7eb',
                            borderColor: 'rgba(99, 102, 241, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: (context) => 'Rp ' + context.parsed.y.toLocaleString('id-ID')
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#9ca3af',
                                font: { size: 12 },
                                callback: (value) => 'Rp ' + (value / 1000000).toFixed(1) + 'M'
                            },
                            grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
                            border: { display: false }
                        },
                        x: {
                            ticks: { color: '#9ca3af', font: { size: 12 } },
                            grid: { display: false },
                            border: { display: false }
                        }
                    },
                    interaction: { intersect: false, mode: 'index' }
                }
            });
        });
    </script>
@endsection
