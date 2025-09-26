@extends('app')
@section('title', 'Transaction History')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')

                <div class="p-8 mt-12 mx-20">
                    <h1 class="text-3xl font-bold mb-8">Transaction History</h1>

                    <div class="bg-[#292929] rounded-lg p-6 shadow-md">
                        <div class="flex justify-between items-center mb-6">
                            <div class="relative w-60">
                                <input type="text" placeholder="Search" class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="search"
                                value="{{ request('search') }}"
                                onchange="window.location.href= '{{route('venue.transaction')}}?status=' + document.getElementById('status').value + '&orderBy=' + document.getElementById('orderBy').value + '&search=' + document.getElementById('search').value">
                            </div>
                            <div class="flex space-x-2">
                                <div class="relative">
                                    {{-- Set select option --}}
                                    <select class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-40 focus:outline-none focus:ring-1 focus:ring-blue-500" id="status" onchange="window.location.href= '{{route('venue.transaction')}}?status=' + document.getElementById('status').value + '&orderBy=' + document.getElementById('orderBy').value + '&search=' + document.getElementById('search').value">
                                        <option value="" disabled selected>Filter by Status</option>
                                        @foreach(['pending','confirmed','cancelled','completed','booked'] as $option)
                                            <option value="{{ $option }}" {{ request('status') == $option ? 'selected' : '' }}>{{ ucfirst($option) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="relative">
                                    <select class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-40 focus:outline-none focus:ring-1 focus:ring-blue-500" id="orderBy" onchange="window.location.href= '{{route('venue.transaction')}}?status=' + document.getElementById('status').value + '&orderBy=' + document.getElementById('orderBy').value + '&search=' + document.getElementById('search').value">
                                        <option value="" disabled selected>Sort by Date</option>
                                        <option value="asc" {{ request('orderBy') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                                        <option value="desc" {{ request('orderBy') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-[#1e1e1e]">
                                        <th class="py-3 px-4 rounded-tl-lg">Table Number</th>
                                        <th class="py-3 px-4">Date</th>
                                        <th class="py-3 px-4">Time</th>
                                        <th class="py-3 px-4">Payment</th>
                                        <th class="py-3 px-4">Total</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4 rounded-tr-lg text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transactions as $transaction)
                                        <tr class="border-b border-gray-700">
                                            <td class="py-3 px-4">Table #{{ $transaction->table->table_number ?? 'N/A' }}</td>
                                            <td class="py-3 px-4">{{ $transaction->booking_date->format('d/m/Y') }}</td>
                                            <td class="py-3 px-4">{{ $transaction->start_time->format('H:i') }}-{{ $transaction->end_time->format('H:i') }}</td>
                                            <td class="py-3 px-4">{{ $transaction->payment_method }}</td>
                                            <td class="py-3 px-4">Rp. {{ number_format($transaction->price - $transaction->discount, 0, ',', '.') }}</td>
                                            <td class="py-3 px-4">
                                                @php
                                                    $statusClass = [
                                                        'pending' => 'bg-yellow-500 text-yellow-900',
                                                        'completed' => 'bg-green-500 text-white',
                                                        'cancelled' => 'bg-red-500 text-white',
                                                    ][$transaction->status] ?? 'bg-gray-500 text-white';
                                                @endphp
                                                <span class="px-3 py-1 rounded-full text-sm {{ $statusClass }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <button class="text-blue-400 hover:text-blue-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-4 px-4 text-center text-gray-400">No transactions found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $transactions->links('pagination::tailwind') }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
