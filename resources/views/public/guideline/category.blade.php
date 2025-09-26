@extends('app')
@section('title', 'Master the Game')

@section('content')
    <div class="bg-neutral-900 text-white min-h-screen">
        <!-- Hero Header -->
        <div class="bg-cover bg-center py-20 px-6" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <div class="max-w-7xl mx-auto">
                <p class="text-sm text-gray-400">Community / News</p>
                <h1 class="text-3xl md:text-5xl font-bold uppercase mt-2">MASTER THE GAME</h1>
            </div>
        </div>

        <!-- Articles Grid -->
        <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($guidelines as $guideline)
                <a href="{{ route('guideline.show', ['slug' => $guideline->slug]) }}"
                    class="block bg-gray-800 rounded-lg overflow-hidden shadow-lg flex flex-col h-full hover:bg-gray-700 transition duration-300">
                    <div class="relative h-44 bg-gray-700 flex items-center justify-center">
                        @if (!empty($guideline->featured_image))
                            @php
                                $imagePath = $guideline->featured_image;
                                // Cek apakah path dimulai dengan 'guidelines/' (dari storage)
                                if (Str::startsWith($imagePath, 'guidelines/')) {
                                    $imagePath = Storage::url($imagePath);
                                }
                                // Cek apakah gambar ada di public path
                                elseif (
                                    !file_exists(public_path($imagePath)) &&
                                    file_exists(public_path('images/guidelines/' . basename($imagePath)))
                                ) {
                                    $imagePath = asset('images/guidelines/' . basename($imagePath));
                                }
                                // Jika tidak, gunakan asset biasa
                                else {
                                    $imagePath = asset($imagePath);
                                }
                            @endphp
                            <img src="{{ $imagePath }}" alt="{{ $guideline->title }}" class="object-cover w-full h-full">
                        @else
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M3 5h18M3 19h18M3 5v14m18-14v14M8 12h8M8 16h8" />
                            </svg>
                        @endif

                        @if (!empty($guideline->is_new))
                            <span
                                class="absolute top-2 left-2 bg-blue-600 text-xs font-bold px-2 py-0.5 rounded-full">New</span>
                        @endif

                        @php
                            $colorMap = [
                                'Beginner' => 'green-500',
                                'Intermediate' => 'yellow-500',
                                'Master' => 'red-500',
                                'General' => 'gray-500',
                            ];
                        @endphp

                        <span
                            class="absolute top-2 right-2 bg-{{ $colorMap[$guideline->category] ?? 'gray-500' }} text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $guideline->category }}
                        </span>
                    </div>
                    <div class="p-4 bg-black bg-opacity-30 flex-grow flex flex-col justify-end">
                        <h3 class="text-sm font-semibold mb-1">{{ $guideline->title }}</h3>
                        <div class="text-xs text-gray-400 flex items-center justify-between mt-auto">
                            <span>{{ $guideline->published_at->format('F j, Y') }}</span>
                            <div class="flex gap-2">
                                <i class="far fa-bookmark"></i>
                                <i class="fas fa-share-alt"></i>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection
