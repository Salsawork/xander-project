<div class="flex flex-col w-full">
    <p class="text-xs text-gray-400 font-medium">Monthly Earnings</p>
    <div class="flex justify-between items-center">
        <div>
            <p class="text-sm text-gray-400">Rp.</p>
            <p class="text-3xl font-bold">{{ number_format($monthlyEarnings) }}</p>
            <p class="text-xs text-gray-400">Rp. {{ number_format($lastMonthEarnings) }} last month</p>
        </div>
        <div class="p-1 rounded {{ $percentageChange >= 0 ? 'bg-green-500' : 'bg-red-500' }} text-white text-xs">
            {{ $percentageChange >= 0 ? '+' : '' }}{{ number_format($percentageChange, 1) }}% {{ $percentageChange >= 0 ? '↑' : '↓' }}
        </div>
    </div>
</div>
