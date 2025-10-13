<div class="w-full h-full flex flex-col justify-between">
    <span class="text-white text-lg font-semibold mb-2">Session Purchased</span>
    <div class="flex items-end mb-1">
        <span class="text-2xl md:text-4xl font-bold mr-2">{{ number_format($sessionPurchased) }}</span>
    </div>
    <div class="flex items-end justify-between">
        <span class="text-gray-500 text-sm">{{ number_format($lastYearSessions) }} last year</span>
        <span class="{{ $sessionPercentageChange >= 0 ? 'text-green-400' : 'text-red-400' }}">
            {{ $sessionPercentageChange >= 0 ? '+' : '' }}{{ $sessionPercentageChange }}%
        </span>
    </div>
</div>