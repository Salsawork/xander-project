@extends('app')

@section('title', 'Sparring Detail')

@section('content')
<div class="bg-gray-950 text-white min-h-screen overflow-hidden">
    <!-- Breadcrumb + Title -->
    <div class="relative bg-gray-900">
        <div class="relative max-w-7xl mx-auto px-6 py-8">
            <div class="flex items-center space-x-2 text-sm">
                <a href="/" class="text-gray-400 hover:text-white">Home</a>
                <span class="text-gray-600">/</span>
                <a href="{{ route('sparring.index') }}" class="text-gray-400 hover:text-white">Sparring</a>
                <span class="text-gray-600">/</span>
                <span class="text-gray-400">{{ $athlete->name }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-3 gap-10">
        
        <!-- Left Column: Photo -->
        <div>
            @if ($athlete->athleteDetail && $athlete->athleteDetail->image)
                <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}" 
                     alt="{{ $athlete->name }}"
                     class="rounded-lg shadow-md w-full object-cover"
                     onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
            @else
                <img src="{{ asset('images/placeholder.jpg') }}" 
                     alt="{{ $athlete->name }}"
                     class="rounded-lg shadow-md w-full object-cover">
            @endif
        </div>

        <!-- Middle Column: Bio -->
        <div class="space-y-4">
            <h2 class="text-3xl font-bold">{{ $athlete->name }}</h2>
            <p class="text-gray-400">{{ $athlete->athleteDetail->handicap ?? 'Handicap N/A' }}</p>

            <div class="text-sm space-y-2 mt-4">
                <p><span class="font-semibold">Year of Experience:</span>
                    {{ $athlete->athleteDetail->experience_years ?? 'N/A' }} Years</p>
                <p><span class="font-semibold">Specialty:</span> {{ $athlete->athleteDetail->specialty ?? 'N/A' }}</p>
                <p><span class="font-semibold">Location:</span> {{ $athlete->athleteDetail->location ?? 'N/A' }}</p>
            </div>

            <p class="text-sm text-gray-300 mt-4">
                {{ $athlete->athleteDetail->bio ?? 'No bio available.' }}
            </p>

            <!-- Share Icons -->
            <div class="flex space-x-3 mt-4">
                <a href="#" class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600">
                    <i class="fab fa-facebook-f text-white"></i>
                </a>
                <a href="#" class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600">
                    <i class="fab fa-twitter text-white"></i>
                </a>
                <a href="#" class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600">
                    <i class="fab fa-instagram text-white"></i>
                </a>
            </div>
        </div>

        <!-- Right Column: Booking -->
        <div class="space-y-6">
            {{-- Booking Box --}}
            <div class="bg-neutral-800 p-5 rounded-lg shadow-md">
                <p class="text-sm text-gray-400 mb-1">Start from</p>
                <h2 class="text-xl font-bold text-white mb-4">
                    Rp. {{ number_format($athlete->athleteDetail->price_per_session, 0, ',', '.') }} / session
                </h2>

                <form action="{{ route('cart.add') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="id" value="{{ $athlete->id }}">

                    {{-- Date --}}
                    <div>
                        <label class="text-sm text-gray-400">Date</label>
                        <input type="date" name="date"
                            class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500">
                    </div>

                    {{-- Schedule --}}
                    <div>
                        <label class="text-sm text-gray-400">Schedule</label>
                        <div class="grid grid-cols-3 gap-2 mt-1">
                            @foreach (['09.00-10.00', '10.00-11.00', '11.00-12.00', '13.00-14.00', '14.00-15.00', '15.00-16.00', '17.00-18.00', '18.00-19.00', '20.00-21.00'] as $slot)
                                <label
                                    class="border border-gray-600 rounded text-center py-2 text-sm cursor-pointer hover:bg-blue-600 hover:border-blue-600">
                                    <input type="radio" name="schedule" value="{{ $slot }}" class="hidden"> {{ $slot }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Promo --}}
                    <div>
                        <label class="text-sm text-gray-400">Promo code (Optional)</label>
                        <input type="text" name="promo"
                            class="mt-1 w-full px-3 py-2 rounded bg-neutral-700 text-white focus:ring focus:ring-blue-500"
                            placeholder="Ex. PROMO70%DAY">
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded font-semibold">
                        Add to cart
                    </button>
                </form>
            </div>
        </div>
    </div> <!-- tutup grid -->

    <!-- Customer Reviews -->
    <div class="max-w-7xl mx-auto px-4 mt-16">
        <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Rating Summary -->
            <div class="bg-gray-900 p-5 rounded-lg shadow-md">
                <div class="flex items-center text-yellow-400 text-2xl font-bold mb-4">
                    <span class="text-3xl">{{ $averageRating }}</span>
                    <span class="text-sm text-gray-400 ml-2">out of 5</span>
                </div>

                <div class="space-y-2">
                    @for ($i = 5; $i >= 1; $i--)
                        <div class="flex items-center">
                            <div class="text-yellow-400">
                                {!! str_repeat('★', $i) !!}{!! str_repeat('☆', 5 - $i) !!}
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5 ml-2">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: {{ $percents[$i] }}%"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">{{ $counts[$i] }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Reviews List -->
            <div class="md:col-span-3 space-y-4">
                @foreach ($reviews as $review)
                    <div class="bg-gray-900 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                        <div class="flex items-center space-x-4 mb-3">
                            <div
                                class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center text-xl font-bold">
                                {{ substr($review->user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold">{{ $review->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $review->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="text-yellow-400 text-sm mb-2">
                            {!! str_repeat('★', $review->rating) !!}{!! str_repeat('☆', 5 - $review->rating) !!}
                        </div>
                        <p class="text-gray-300">{{ $review->comment }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Add Review Form -->
        @auth
            @if(!$alreadyReviewed)
                <div class="mt-10 bg-gray-900 p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold mb-4">Leave a Review</h3>
                    <form action="{{ route('sparring.review.store', $athlete->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm mb-1">Rating</label>
                            <select name="rating" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700">
                                <option value="">Select rating</option>
                                <option value="5">★★★★★ - Excellent</option>
                                <option value="4">★★★★☆ - Good</option>
                                <option value="3">★★★☆☆ - Average</option>
                                <option value="2">★★☆☆☆ - Poor</option>
                                <option value="1">★☆☆☆☆ - Terrible</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Your Review</label>
                            <textarea name="comment" rows="3" required class="w-full p-2 rounded bg-gray-800 text-white border border-gray-700"></textarea>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded font-semibold">
                            Submit Review
                        </button>
                    </form>
                </div>
            @endif
        @endauth
    </div>
</div>
@endsection
