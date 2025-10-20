@extends('app')
@section('title', 'Match History')

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
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    /* ====== Anti overscroll / white bounce ====== */
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
        /* cegah rubber-band ke body */
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust: 100%;
    }

    /* Kanvas gelap tetap di belakang konten */
    #antiBounceBg {
        position: fixed;
        left: 0;
        right: 0;
        top: -120svh;
        bottom: -120svh;
        /* svh stabil di mobile */
        background: var(--page-bg);
        z-index: -1;
        pointer-events: none;
    }

    /* Pastikan area scroll utama tidak meneruskan overscroll ke body */
    .scroll-safe {
        background-color: #171717;
        /* senada bg-neutral-900 */
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar.athlete')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')
            <h1 class="text-2xl font-bold p-4 sm:p-8 mt-12">Match History</h1>
            <div class="mx-8">
                {{-- Add Session & Filter Row --}}
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 mb-6">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('athlete.match') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1">
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search" 
                               class="bg-[#2c2c2c] text-gray-400 text-xs sm:text-sm rounded border border-gray-700 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500"
                               oninput="clearTimeout(this.searchTimeout); this.searchTimeout = setTimeout(() => this.form.submit(), 300);"/>
                        <div class="flex gap-2">
                            <select id="statusFilter"
                                onchange="window.location.href = '{{ route('athlete.match') }}?search=' + (document.querySelector('input[type=search]')?.value || '') + '&status=' + this.value + '&date_range=' + (document.getElementById('dateRange')?.value || '');"
                                class="bg-[#2c2c2c] text-gray-400 text-xs sm:text-sm rounded border border-gray-700 px-3 py-2 cursor-pointer">
                                <option value="">-- Status --</option>
                                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                                <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                            </select>
                            <input type="text" id="dateRange" name="date_range" value="{{ request('date_range') }}" placeholder="Date Range" class="bg-[#2c2c2c] text-gray-400 text-xs sm:text-sm rounded border border-gray-700 px-3 py-2 focus:outline-none"/>
                        </div>
                    </form>

                    {{-- Add Session Button --}}
                    <a href="{{ route('athlete.match.create') }}" class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                        <i class="fas fa-plus"></i>
                        Add Session
                    </a>
                </div>
                    
                {{-- Table --}}
                <div class="bg-[#232323] rounded-lg shadow-md overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-neutral-700">
                                <th class="py-4 px-6">Location</th>
                                <th class="py-4 px-6">Date</th>
                                <th class="py-4 px-6">Time</th>
                                <th class="py-4 px-6">Payment</th>
                                <th class="py-4 px-6">Total</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matches as $match)
                                <tr class="border-b border-neutral-800 hover:bg-neutral-800/50 transition">
                                    <td class="py-3 px-6">{{ $match->venue->name ?? '-' }}</td>
                                    <td class="py-3 px-6">{{ \Carbon\Carbon::parse($match->date)->format('d/m/Y') }}</td>
                                    <td class="py-3 px-6">{{ date('H:i', strtotime($match->time_start)) }} - {{ date('H:i', strtotime($match->time_end)) }}</td>
                                    <td class="py-3 px-6">{{ $match->payment_method ?? '-' }}</td>
                                    <td class="py-3 px-6">Rp. {{ number_format($match->total_amount, 0, ',', '.') }}</td>
                                    <td class="py-3 px-6">
                                        @php
                                            $statusColor = [
                                                'pending' => 'bg-yellow-400 text-yellow-900',
                                                'completed' => 'bg-green-400 text-green-900',
                                                'cancelled' => 'bg-red-400 text-red-900',
                                            ][$match->status] ?? 'bg-gray-400 text-gray-900';
                                        @endphp
                                        <span class="px-3 py-1 rounded-full font-semibold text-xs {{ $statusColor }}">
                                            {{ ucfirst($match->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-6">
                                        <a href="{{ route('athlete.match.show', $match->id) }}" class="text-blue-500 hover:text-blue-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" 
                                                viewBox="0 0 24 24" stroke-width="1.5" 
                                                stroke="currentColor" 
                                                class="w-5 h-5 opacity-60 hover:opacity-100 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round" 
                                                    d="M2.25 12s3.75-7.5 9.75-7.5 
                                                    9.75 7.5 9.75 7.5-3.75 7.5-9.75 
                                                    7.5S2.25 12 2.25 12z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" 
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 
                                                    016 0z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-neutral-400">
                                        Belum ada match history.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
    {{-- Flatpickr untuk date range --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>
    <script>
        flatpickr("#dateRange", {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: true,
        });
    </script>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection
