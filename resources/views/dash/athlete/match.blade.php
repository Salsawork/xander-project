@extends('app')
@section('title', 'Match History')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar.athlete')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')
            <div class="p-8 mt-12 mx-20">
                <h1 class="text-3xl font-bold mb-8">Match History</h1>

                {{-- Filter Form --}}
                <form method="GET" action="{{ route('athlete.match') }}" class="flex flex-row justify-between items-center mb-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search" 
                        class="bg-neutral-800 rounded px-4 py-2 w-64 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>

                    <div class="flex gap-2">
                        <select name="status" class="bg-neutral-800 rounded px-3 py-2 text-white focus:outline-none">
                            <option value="">Status</option>
                            <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                            <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                            <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelled</option>
                        </select>

                        <input type="text" id="dateRange" name="date_range" value="{{ request('date_range') }}"
                            placeholder="Date Range" 
                            class="bg-neutral-800 rounded px-3 py-2 text-white focus:outline-none"/>

                        <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white">Filter</button>
                    </div>
                </form>

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
                                    <td class="py-3 px-6">{{ date('H.i', strtotime($match->time_start)) }} - {{ date('H.i', strtotime($match->time_end)) }}</td>
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
                                        <a href="{{ route('athlete.match.show', $match->id) }}">
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
