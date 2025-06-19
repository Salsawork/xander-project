<div class="flex justify-between items-center mb-2">
    <span class="text-white text-lg font-semibold">Recent Reviews</span>
    <span class="text-white text-2xl cursor-pointer">...</span>
</div>
<hr class="border-gray-600 mb-4">

@if($recentReviews->isEmpty())
    <div class="flex flex-col items-center justify-center h-48 text-gray-400">
        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h.01M12 4v.01M4.93 4.93l.01.01M19.07 4.93l-.01.01M4 12h.01M20 12h.01M4.93 19.07l.01-.01M19.07 19.07l-.01-.01" />
        </svg>
        <span class="text-center">Belum ada review untuk venue ini</span>
    </div>
@else
    @foreach($recentReviews as $review)
        <div class="mb-4">
            <div class="flex items-center mb-1">
                <span class="font-bold text-white mr-2">{{ $review->user->name ?? 'Unknown' }}</span>
                <span class="text-yellow-400">
                    @for($i = 0; $i < $review->rating; $i++)
                        ★
                    @endfor
                    @for($i = $review->rating; $i < 5; $i++)
                        <span class="text-gray-600">★</span>
                    @endfor
                </span>
            </div>
            <div class="text-gray-300 text-sm mb-1">
                "{{ $review->comment }}"
            </div>
            <div class="text-gray-500 text-xs">
                {{ $review->created_at->format('d M Y H:i') }}
            </div>
        </div>
    @endforeach
@endif