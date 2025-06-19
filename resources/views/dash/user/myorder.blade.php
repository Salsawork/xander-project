@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <section class="flex-1 overflow-auto p-6 space-y-6 mt-12 mx-8">
                    <h1 class="text-3xl font-extrabold">
                        My Order
                    </h1>
                    <nav class="flex space-x-6 border-b border-gray-700 text-sm font-semibold text-gray-500">
                        <button
                            class="relative text-[#0ea5e9] after:absolute after:-bottom-px after:left-0 after:right-0 after:h-[2px] after:bg-[#0ea5e9] after:rounded"
                            type="button">
                            All
                            <span
                                class="ml-2 inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                10
                            </span>
                        </button>
                        <button class="flex items-center space-x-1 cursor-default" disabled="" type="button">
                            <span>
                                Processing
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                5
                            </span>
                        </button>
                        <button class="flex items-center space-x-1 cursor-default" disabled="" type="button">
                            <span>
                                Shipped
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                5
                            </span>
                        </button>
                        <button class="flex items-center space-x-1 cursor-default" disabled="" type="button">
                            <span>
                                Delivered
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                0
                            </span>
                        </button>
                        <button class="flex items-center space-x-1 cursor-default" disabled="" type="button">
                            <span>
                                Cancelled
                            </span>
                            <span
                                class="inline-block rounded border border-gray-600 bg-[#2c2c2c] px-2 text-xs font-normal text-gray-400">
                                0
                            </span>
                        </button>
                    </nav>
                    <div class="space-y-6">
                        <article class="bg-[#222222] rounded-lg p-4 space-y-4 shadow-sm">
                            <header class="flex justify-between items-center border-b border-gray-600 pb-2">
                                <span class="text-gray-300 text-sm">
                                    22 February 2025
                                </span>
                                <span class="bg-[#f59e0b] text-white text-xs font-normal rounded-full px-4 py-1">
                                    Processing
                                </span>
                            </header>
                            <ul class="divide-y divide-gray-600">
                                <li class="flex items-center py-3 space-x-4">
                                    <img alt="Elite Strike Carbon Cue stick on blue background" class="w-16 h-16 rounded"
                                        height="64"
                                        src="https://storage.googleapis.com/a1aa/image/77ff22bc-0c3a-4590-cfdd-40d6890f2d21.jpg"
                                        width="64" />
                                    <div class="flex-1">
                                        <p class="font-bold text-white text-sm leading-tight">
                                            Elite Strike Carbon Cue
                                        </p>
                                        <p class="text-gray-300 text-xs mt-1">
                                            1x
                                        </p>
                                    </div>
                                    <p class="text-gray-300 text-sm whitespace-nowrap">
                                        Rp. 2.500.000
                                    </p>
                                </li>
                                <li class="flex items-center py-3 space-x-4">
                                    <img alt="Predator Vision Pro Billiard Gloves on green background"
                                        class="w-16 h-16 rounded" height="64"
                                        src="https://storage.googleapis.com/a1aa/image/7b5675ad-768a-4279-8507-57934bc17300.jpg"
                                        width="64" />
                                    <div class="flex-1">
                                        <p class="font-bold text-white text-sm leading-tight">
                                            Predator Vision Pro Billiard Gloves
                                        </p>
                                        <p class="text-gray-300 text-xs mt-1">
                                            2x
                                        </p>
                                    </div>
                                    <p class="text-gray-300 text-sm whitespace-nowrap">
                                        Rp. 500.000
                                    </p>
                                </li>
                            </ul>
                            <footer
                                class="flex justify-end border-t border-gray-600 pt-3 text-gray-300 text-sm font-semibold">
                                <span class="mr-2">
                                    Total :
                                </span>
                                <span class="text-white font-extrabold text-base">
                                    Rp 2.805.000
                                </span>
                            </footer>
                        </article>
                        <article class="bg-[#222222] rounded-lg p-4 space-y-4 shadow-sm">
                            <header class="flex justify-between items-center border-b border-gray-600 pb-2">
                                <span class="text-gray-300 text-sm">
                                    22 February 2025
                                </span>
                                <span class="bg-[#3b82f6] text-white text-xs font-normal rounded-full px-4 py-1">
                                    Shipping
                                </span>
                            </header>
                            <ul class="divide-y divide-gray-600">
                                <li class="flex items-center py-3 space-x-4">
                                    <img alt="Driftwood Classic billiard cue on gray background" class="w-16 h-16 rounded"
                                        height="64"
                                        src="https://storage.googleapis.com/a1aa/image/6c4fe793-ffb0-4eb1-0bb7-db7a375db677.jpg"
                                        width="64" />
                                    <div class="flex-1">
                                        <p class="font-bold text-white text-sm leading-tight">
                                            Driftwood Classic
                                        </p>
                                        <p class="text-gray-300 text-xs mt-1">
                                            1x
                                        </p>
                                    </div>
                                    <p class="text-gray-300 text-sm whitespace-nowrap">
                                        Rp. 2.000.000
                                    </p>
                                </li>
                            </ul>
                            <footer
                                class="flex justify-end border-t border-gray-600 pt-3 text-gray-300 text-sm font-semibold">
                                <span class="mr-2">
                                    Total :
                                </span>
                                <span class="text-white font-extrabold text-base">
                                    Rp 2.890.000
                                </span>
                            </footer>
                        </article>
                        <article class="bg-[#222222] rounded-lg p-4 space-y-4 shadow-sm">
                            <header class="flex justify-between items-center border-b border-gray-600 pb-2">
                                <span class="text-gray-300 text-sm">
                                    22 February 2025
                                </span>
                                <span class="bg-[#22c55e] text-white text-xs font-normal rounded-full px-4 py-1">
                                    Delivered
                                </span>
                            </header>
                            <ul class="divide-y divide-gray-600">
                                <li class="flex items-center py-3 space-x-4">
                                    <img alt="GlidePro Cue Glove on gray background" class="w-16 h-16 rounded"
                                        height="64"
                                        src="https://storage.googleapis.com/a1aa/image/37258c64-9a2d-4f69-4cfb-91e389406508.jpg"
                                        width="64" />
                                    <div class="flex-1">
                                        <p class="font-bold text-white text-sm leading-tight">
                                            GlidePro Cue Glove
                                        </p>
                                        <p class="text-gray-300 text-xs mt-1">
                                            1x
                                        </p>
                                    </div>
                                    <p class="text-gray-300 text-sm whitespace-nowrap">
                                        Rp. 3.990.000
                                    </p>
                                </li>
                            </ul>
                            <footer
                                class="flex justify-end border-t border-gray-600 pt-3 text-gray-300 text-sm font-semibold">
                                <span class="mr-2">
                                    Total :
                                </span>
                                <span class="text-white font-extrabold text-base">
                                    Rp 4.190.000
                                </span>
                            </footer>
                        </article>
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
