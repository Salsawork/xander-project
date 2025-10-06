@extends('app')
@section('title', 'Venue Dashboard')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-6 md:my-8">
            @include('partials.topbar')
            <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">Dashboard Venue</h1>
            <section class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
                @if($venue)
                {{-- Row 1: Statistik Utama (3 Kartu) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-4 sm:mb-6 md:mb-8">
                    {{-- Kartu Ratings --}}
                    <div class="bg-[#292929] rounded-lg p-4 sm:p-5 md:p-6 shadow-md flex">
                        @include('dash.venue.components.dashboard.ratings')
                    </div>

                    {{-- Kartu Monthly Earnings --}}
                    <div class="bg-[#292929] sm:col-span-2 rounded-lg p-4 sm:p-5 md:p-6 shadow-md h-full">
                        @include('dash.venue.components.dashboard.monthly-earnings', [
                        'monthlyEarnings' => $monthlyEarnings,
                        'lastMonthEarnings' => $lastMonthEarnings,
                        'percentageChange' => $percentageChange
                        ])
                    </div>

                    {{-- Kartu Session Purchased --}}
                    <div class="bg-[#292929] sm:col-span-2 rounded-lg p-4 sm:p-5 md:p-6 shadow-md">
                        @include('dash.venue.components.dashboard.session-purchased')
                    </div>
                </div>

                {{-- Row 2: Grafik dan Notifikasi --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4 mb-4 sm:mb-6 md:mb-8">
                    {{-- Komponen Sales Report --}}
                    <div class="bg-[#292929] sm:col-span-2 md:col-span-3 rounded-lg p-4 sm:p-5 md:p-6 shadow-md">
                        @include('dash.venue.components.dashboard.sales-report')
                    </div>

                    {{-- Komponen Notification --}}
                    <div class="bg-[#292929] sm:col-span-2 rounded-lg p-4 sm:p-5 md:p-6 shadow-md">
                        @include('dash.venue.components.dashboard.notification')
                    </div>
                </div>

                {{-- Row 3: Transaksi Terbaru dan Review --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 sm:gap-4">
                    {{-- Komponen Recent Transaction --}}
                    <div class="bg-[#292929] sm:col-span-2 md:col-span-3 rounded-lg p-3 sm:p-4 shadow-md">
                        @include('dash.venue.components.dashboard.recent-transaction')
                    </div>

                    {{-- Komponen Review --}}
                    <div class="bg-[#292929] sm:col-span-2 rounded-lg p-3 sm:p-4 shadow-md">
                        @include('dash.venue.components.dashboard.review')
                    </div>
                </div>
                @else
                <div class="bg-red-500 bg-opacity-20 border border-red-500 rounded-lg p-4 sm:p-5 md:p-6 shadow-md">
                    <h2 class="text-lg sm:text-xl font-bold mb-2">Data Venue Tidak Ditemukan</h2>
                    <p class="text-sm sm:text-base">Kamu belum memiliki data venue. Silakan hubungi admin untuk menambahkan data venue kamu.</p>
                </div>
                @endif
            </section>
        </main>
    </div>
</div>
@endsection