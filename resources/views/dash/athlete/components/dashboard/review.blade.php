<div class="flex flex-col h-full">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-xl font-bold">Review</h2>
        <button>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
            </svg>
        </button>
    </div>

    <div class="border-t border-neutral-700 my-2"></div>

    <div class="space-y-4">
        @forelse ($reviews as $review)
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-1">
                    <div class="w-8 h-8 {{ $review->rating >= 4 ? 'bg-green-500' : 'bg-red-500' }} rounded-full flex items-center justify-center">
                        @if($review->rating >= 4)
                            {{-- Icon smile --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM9 10.5a.75.75 0 110-1.5.75.75 0 010 1.5zm6 0a.75.75 0 110-1.5.75.75 0 010 1.5zm-6.75 3a4.5 4.5 0 008.25 0H8.25z" clip-rule="evenodd" />
                            </svg>
                        @else
                            {{-- Icon sad --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                                <path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 100 19.5 9.75 9.75 0 000-19.5zM9 10.5a.75.75 0 110-1.5.75.75 0 010 1.5zm6 0a.75.75 0 110-1.5.75.75 0 010 1.5zM8.25 15.75a4.5 4.5 0 017.5 0h-7.5z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="flex-1">
                    <div class="flex justify-between">
                        <div class="font-medium">
                            {{ $review->user->name ?? 'Anonymous' }} |
                            {{ $review->rating }} <span class="text-yellow-500">★</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 mt-1">{{ $review->comment }}</p>
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm">No reviews yet.</p>
        @endforelse
    </div>
</div>
