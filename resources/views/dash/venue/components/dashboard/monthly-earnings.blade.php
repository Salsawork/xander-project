<div class="w-full h-full flex flex-col justify-between">
    <span class="text-white text-lg font-semibold mb-2">Monthly Earnings</span>
    <div class="flex items-end mb-1">
        <div class="flex flex-row">
            <span class="text-sm text-gray-400 mr-4">Rp.</span>
            <span class="text-2xl md:text-4xl font-bold">{{ number_format($monthlyEarnings, 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="flex items-end justify-between">
        <span class="text-gray-400 text-sm">Rp. {{ number_format($lastMonthEarnings, 0, ',', '.') }} last month</span>
        @if($percentageChange >= 0)
            <span class="bg-green-500 text-white text-xs px-2 py-1 rounded flex items-center">
                +{{ rtrim(rtrim(number_format($percentageChange, 1, '.', ''), '0'), '.') }}% <span class="ml-1">↑</span>
            </span>
        @else
            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded flex items-center">
                {{ rtrim(rtrim(number_format($percentageChange, 1, '.', ''), '0'), '.') }}% <span class="ml-1">↓</span>
            </span>
        @endif
    </div>
</div>