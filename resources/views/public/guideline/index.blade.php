@extends('app')
@section('title', 'Guideline - Xander Billiard')

@section('content')
    <div class="bg-neutral-900 text-white min-h-screen">
        <!-- Hero Section - Latest Guideline -->
        @php
            $latestGuideline = $guidelines->first();
        @endphp

        @if ($latestGuideline)
            <div class="relative">
                @php
                    $heroImagePath = !empty($latestGuideline->featured_image)
                        ? $latestGuideline->featured_image
                        : '/images/hero/guideline-main.jpg';
                    // Cek apakah path dimulai dengan 'guidelines/' (dari storage)
                    if (Str::startsWith($heroImagePath, 'guidelines/')) {
                        $heroImagePath = Storage::url($heroImagePath);
                    }
                    // Cek apakah gambar ada di public path
                    elseif (
                        !file_exists(public_path($heroImagePath)) &&
                        file_exists(public_path('images/guidelines/' . basename($heroImagePath)))
                    ) {
                        $heroImagePath = asset('images/guidelines/' . basename($heroImagePath));
                    }
                    // Jika tidak, gunakan asset biasa
                    else {
                        $heroImagePath = asset($heroImagePath);
                    }
                @endphp
                <img src="{{ asset($heroImagePath) }}" class="w-full h-[500px] object-cover"
                    alt="{{ $latestGuideline->title }}">
                <div class="absolute inset-0 bg-black/60 flex flex-col justify-center px-6 lg:px-24">
                    <p class="text-sm text-gray-300">{{ $latestGuideline->published_at->format('d F Y') }}</p>
                    <h1 class="text-3xl md:text-5xl font-bold mt-2 mb-4 max-w-4xl">{{ $latestGuideline->title }}</h1>
                    <p class="max-w-2xl text-sm text-gray-200">{{ $latestGuideline->description }}</p>
                    <a href="{{ route('guideline.show', ['slug' => $latestGuideline->slug]) }}"
                        class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition w-max">
                        Read More
                    </a>
                </div>
            </div>
        @else
            <div class="relative">
                <img src="/images/hero/guideline-main.jpg" class="w-full h-[500px] object-cover" alt="Guideline Hero">
                <div class="absolute inset-0 bg-black/60 flex flex-col justify-center px-6 lg:px-24">
                    <p class="text-sm text-gray-300">Xander Billiard</p>
                    <h1 class="text-3xl md:text-5xl font-bold mt-2 mb-4 max-w-4xl">Master the Game of Billiards</h1>
                    <p class="max-w-2xl text-sm text-gray-200">Explore our comprehensive guides and tutorials to improve
                        your billiards skills.</p>
                </div>
            </div>
        @endif

        <!-- Sections -->
        @php
            $categories = [
                'BEGINNER' => 'green-600',
                'INTERMEDIATE' => 'yellow-500',
                'MASTER' => 'red-500',
                'GENERAL' => 'gray-500',
            ];

            $displayNames = [
                'BEGINNER' => 'Beginner',
                'INTERMEDIATE' => 'Intermediate',
                'MASTER' => 'Master',
                'GENERAL' => 'General',
            ];
        @endphp

        @foreach ($categories as $category => $badgeColor)
            @php
                $categoryGuidelines = $guidelines->where('category', $category)->take(4);
            @endphp

            @if ($categoryGuidelines->count() > 0)
                <section class="px-6 lg:px-24 py-12">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold uppercase">{{ $displayNames[$category] }}</h2>
                        <a href="{{ route('guideline.category', ['category' => Str::slug($displayNames[$category], '-')]) }}"
                            class="text-sm text-gray-400 hover:text-white">view all</a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($categoryGuidelines as $guideline)
                            <a href="{{ route('guideline.show', ['slug' => $guideline->slug]) }}"
                                class="block bg-gray-800 rounded-lg overflow-hidden shadow-lg flex flex-col h-full hover:bg-gray-700 transition duration-300">
                                <div class="relative h-40 bg-gray-700 flex items-center justify-center">
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
                                        <img src="{{ $imagePath }}" alt="{{ $guideline->title }}"
                                            class="object-cover w-full h-full">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path d="M21 15V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10" />
                                            <rect width="20" height="16" x="2" y="4" rx="2" ry="2" />
                                            <circle cx="8" cy="10" r="1" />
                                            <path d="m21 15-5-5L5 21" />
                                        </svg>
                                    @endif

                                    @if ($guideline->is_new)
                                        <span
                                            class="absolute top-2 left-2 bg-blue-600 text-xs px-2 py-0.5 rounded-full font-bold">New</span>
                                    @endif
                                    <span
                                        class="absolute top-2 right-2 bg-{{ $badgeColor }} text-xs px-2 py-0.5 rounded-full font-bold">{{ $displayNames[$category] }}</span>
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
                </section>
            @endif
        @endforeach
    </div>
@endsection
