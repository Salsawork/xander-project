<div class="flex flex-col w-full">
    <p class="text-xs text-gray-400 font-medium">Session Created</p>
    <div class="flex justify-between items-center">
        <div>
            <p class="text-3xl font-bold">{{ number_format($sessionCreated) }}</p>
            <p class="text-xs text-gray-400">{{ number_format($lastYearSessionCreated) }} last year</p>
        </div>
        <div class="p-1 rounded {{ $sessionPercentageChange >= 0 ? 'bg-green-500' : 'bg-red-500' }} text-white text-xs">
            {{ $sessionPercentageChange >= 0 ? '+' : '' }}{{ number_format($sessionPercentageChange, 1) }}% {{ $sessionPercentageChange >= 0 ? '↑' : '↓' }}
        </div>
    </div>
</div>
