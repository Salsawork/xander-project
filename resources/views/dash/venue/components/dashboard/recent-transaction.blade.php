<div class="flex justify-between items-center mb-2">
    <span class="text-white text-lg font-semibold">Recent Transaction</span>
    <span class="text-white text-2xl cursor-pointer">...</span>
</div>
<hr class="border-gray-600 mb-4">

@foreach ($recentTransactions as $transaction)
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            @if ($transaction->status === 'booked' || $transaction->status === 'confirmed')
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-900 mr-3">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            @elseif($transaction->status === 'cancelled')
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-red-900 mr-3">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            @elseif($transaction->status === 'pending')
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-yellow-900 mr-3">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                </span>
            @elseif($transaction->status === 'completed')
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-900 mr-3">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
            @else
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-gray-700 mr-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2.5"
                        viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                </span>
            @endif
            <div>
                <div class="text-white font-bold">
                    Table #{{ $transaction->table->table_number ?? '-' }}
                </div>
                <div class="text-gray-400 text-sm">
                    {{ \Carbon\Carbon::parse($transaction->booking_date)->format('d/m/Y') }}
                    |
                    {{ \Carbon\Carbon::parse($transaction->start_time)->format('H.i') }}-{{ \Carbon\Carbon::parse($transaction->end_time)->format('H.i') }}
                </div>
            </div>
        </div>
        <div class="text-right">
            <div class="font-bold text-white">
                {{ $transaction->status === 'Booked' ? 'Booked' : ($transaction->status === 'Cancelled' ? 'Cancelled' : $transaction->status) }}
            </div>
            <div class="text-gray-400 text-sm">
                by {{ $transaction->user->name ?? '-' }}
            </div>
        </div>
    </div>
@endforeach
