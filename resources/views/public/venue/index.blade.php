@extends('app')
@section('title', 'Venues - Xander Billiard')

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">Home / Venue</p>
            <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR FAVORITE VENUE HERE</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-24 py-18">
            <!-- <div class="w-full space-y-6 rounded-xl bg-neutral-900 p-4 text-white text-sm">
                <div>
                    <input type="text" placeholder="Search"
                        class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Date</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="flex items-center gap-2 justify-center">
                        <button class="text-gray-400 hover:text-white">&#60;</button>
                        <span>February</span>
                        <button class="text-gray-400 hover:text-white">&#62;</button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center mt-2 text-xs text-gray-400">
                        @for($i=1; $i<=28; $i++)
                            <span class="py-1">{{ $i }}</span>
                        @endfor
                    </div>
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Location</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full border border-gray-500 text-gray-400 cursor-pointer">Depok</span>
                        <span class="px-3 py-1 rounded-full border border-gray-500 text-gray-400 cursor-pointer">Bekasi</span>
                        <span class="px-3 py-1 rounded-full border border-gray-500 text-gray-400 cursor-pointer">Tangerang</span>
                        <span class="px-3 py-1 rounded-full border border-gray-500 text-gray-400 cursor-pointer">Bogor</span>
                    </div>
                </div>
                <div class="border-t border-gray-500 pt-4">
                    <div class="flex items-center justify-between mb-2 font-semibold">
                        <span>Price Range</span>
                        <span class="text-xl leading-none text-gray-300">–</span>
                    </div>
                    <div class="w-full flex items-center gap-2">
                        <input type="range" min="0" max="1000000" value="0" class="w-full">
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                        Filter
                    </button>
                    <button
                        class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">
                        Reset
                    </button>
                </div>
            </div> -->
            <section class="lg:col-span-4 flex flex-col gap-8">
                @forelse ($venues as $venue)
                <div class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg flex flex-row items-center p-6 relative">
                    <div class="w-64 h-36 bg-neutral-700 rounded-lg mr-8 flex-shrink-0 flex items-center justify-center">
                        <span class="text-gray-500 text-2xl">Image</span>
                    </div>
                    <div class="flex-1 flex flex-col justify-between h-full">
                        <div>
                            <h3 class="text-2xl font-bold mb-1">{{ $venue->name }}</h3>
                            <div class="text-gray-400 text-sm mb-2">{{ $venue->address ?? 'Jakarta' }}</div>
                        </div>
                        <div class="mt-4">
                            <span class="text-gray-400 text-sm">start from</span>
                            <span class="text-xl font-bold text-white ml-2">Rp. 50.000.-</span>
                            <span class="text-gray-400 text-sm">/ session</span>
                        </div>
                    </div>
                    <div class="absolute top-6 right-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-gray-400 hover:text-blue-500 cursor-pointer">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75a4.5 4.5 0 00-6.364 0l-.636.637-.636-.637a4.5 4.5 0 00-6.364 6.364l.637.636L12 20.25l7.364-7.364.636-.636a4.5 4.5 0 000-6.364z" />
                        </svg>
                    </div>
                </div>
                @empty
                <div class="lg:col-span-2 text-center py-12">
                    <p class="text-gray-400">No venues found.</p>
                </div>
                @endforelse
            </section>
        </div>
    </div>
@endsection
