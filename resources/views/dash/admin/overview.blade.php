@extends('app')
@section('title', 'Admin Dashboard - Overview')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-2xl font-bold p-8 mt-12">Shop Overview</h1>
                <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 px-8">
                    <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Page Visit</h2>
                        @php
                            // Get visits last month
                            $visits = \App\Models\Visit::sum('visit');
                        @endphp
                        <div class="text-3xl font-extrabold leading-none mb-1">{{ $visits }}</div>
                        {{-- <div class="text-xs text-gray-500 mb-2">{{ $visitsThisMonth }} last month</div> --}}
                    </div>
                    <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Weekly Earnings</h2>
                        @php
                            // Get earnings last week
                            $earnings = \App\Models\Order::where('created_at', '>=', now()->subWeek())
                                ->where('payment_status', 'paid')
                                ->sum('total');
                        @endphp
                        <div class="text-3xl font-extrabold leading-none mb-1">Rp. {{ number_format($earnings, 0, ',', '.') }}</div>
                        {{-- <div class="text-xs text-gray-500 mb-2">Rp. 100.000 last month</div>
                        <span
                            class="inline-flex items-center text-xs font-semibold bg-[#0a8aff] rounded px-2 py-0.5 text-white">
                            +1.5% <i class="fas fa-arrow-up ml-1"></i>
                        </span> --}}
                    </div>
                    {{-- <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Session Purchased</h2>
                        <div class="text-3xl font-extrabold leading-none mb-1">123.000</div>
                        <div class="text-xs text-gray-500 mb-2">000.000 last year</div>
                        <span
                            class="inline-flex items-center text-xs font-semibold bg-[#ef4444] rounded px-2 py-0.5 text-white">
                            -1.5% <i class="fas fa-arrow-down ml-1"></i>
                        </span>
                    </div> --}}
                </section>
                <section class="grid grid-cols-1 lg:grid-cols-1 gap-6 px-8">
                    <div class="lg:col-span-2 bg-[#292929] rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Sales Report</h3>
                            <button aria-label="More options" class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        @php
                            $yearlyRevenue = \App\Models\Order::select(
                                    \Illuminate\Support\Facades\DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                                    \Illuminate\Support\Facades\DB::raw("SUM(total) as monthly_revenue")
                                )
                                ->where('payment_status', 'paid')
                                ->whereYear('created_at', now()->year) // Hanya mengambil data untuk tahun berjalan
                                ->groupBy('month')
                                ->orderBy('month')
                                ->get();

                            // Mengubah data menjadi format yang mudah diolah JavaScript
                            $labels = $yearlyRevenue->pluck('month')->toJson();
                            $dataPoints = $yearlyRevenue->pluck('monthly_revenue')->toJson();
                        @endphp
                        <div class="overflow-x-auto">
                            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                            <canvas id="revenueChart"></canvas>
                            <script>
                                 // Data yang diteruskan dari Laravel, sudah dalam format JSON
                                const months = {!! $labels !!};
                                const revenues = {!! $dataPoints !!};

                                document.addEventListener('DOMContentLoaded', function () {
                                    const ctx = document.getElementById('revenueChart').getContext('2d');
                                    new Chart(ctx, {
                                        type: 'bar', // Anda bisa mengubah ke 'line', 'pie', dll.
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
                                            scales: {
                                                y: {
                                                    beginAtZero: true,
                                                    title: {
                                                        display: true,
                                                        text: 'Pendapatan'
                                                    }
                                                },
                                                x: {
                                                    title: {
                                                        display: true,
                                                    }
                                                }
                                            }
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </section>
                <section class="grid grid-cols-1 lg:grid-cols-1 gap-6 mt-6 px-8">
                    <div class="lg:col-span-2 bg-[#292929] rounded-lg p-5 shadow-sm">
                        {{-- Get recent oreder --}}
                        @php
                            $recentOrders = \App\Models\Order::orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();
                        @endphp
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Recent Transaction</h3>
                            <button aria-label="More options" class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <ul class="space-y-4 text-sm">
                            @foreach ($recentOrders as $order)
                                <li class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        @if($order->payment_status == 'pending')
                                            <span class="text-yellow-400"><i class="fas fa-exclamation-circle"></i></span>
                                        @elseif($order->payment_status == 'processing')
                                            <span class="text-blue-400"><i class="fas fa-spinner fa-spin"></i></span>
                                        @elseif($order->payment_status == 'paid')
                                            <span class="text-green-400"><i class="fas fa-check-circle"></i></span>
                                        @elseif($order->payment_status == 'failed')
                                            <span class="text-red-400"><i class="fas fa-times-circle"></i></span>
                                        @elseif($order->payment_status == 'refunded')
                                            <span class="text-gray-400"><i class="fas fa-undo"></i></span>
                                        @endif
                                        <div>
                                            <div class="font-semibold">Order #{{ $order->id }}</div>
                                            <div class="text-gray-400 text-xs">{{ $order->created_at->format('d/m/Y') }}
                                                <span class="mx-1">|</span> {{ $order->created_at->format('H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold">Rp. {{ number_format($order->total, 0, ',', '.') }}</div>
                                        <div class="text-gray-400 text-xs">{{ $order->payment_status }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    {{-- <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Notification</h3>
                            <button aria-label="More options" class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <ul class="space-y-4 text-sm">
                            <li>
                                <div class="font-semibold">New Venue Registration</div>
                                <div class="text-gray-400">Pocket &amp; Play – Pending Review</div>
                            </li>
                            <li>
                                <div class="font-semibold">New Athlete Registration</div>
                                <div class="text-gray-400">Ahmad Hendra – Awaiting Approval</div>
                            </li>
                            <li>
                                <div class="font-semibold">New Venue Registration</div>
                                <div class="text-gray-400">Chalk House Jakarta – Pending Review</div>
                            </li>
                        </ul>
                    </div> --}}
                </section>
            </main>
        </div>
    </div>
@endsection
