<div class="flex flex-col w-full">
    <p class="text-xs text-gray-400 font-medium">Ratings</p>
    <div class="flex items-baseline mt-1">
        <span class="text-3xl font-bold">{{ number_format($averageRating, 1) }}</span>
        <span class="text-xl font-bold">/5</span>
        <span class="text-amber-400 ml-2">★</span>
    </div>
    <div class="text-xs text-gray-400 mt-1">({{ number_format($totalRating) }} rating)</div>
</div>
