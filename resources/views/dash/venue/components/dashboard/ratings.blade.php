<div class="flex flex-col w-full">
    <span class="text-white text-lg font-semibold mb-2 ">Ratings</span>
    <div class="flex justify-center items-center mb-1">
        
    <p class="text-3xl font-bold">{{ number_format($averageRating, 1) }}/5 ⭐</p>
    </div>
    <span class="text-gray-400 text-sm">({{ number_format($totalRatings) }} Rating)</span>
</div>