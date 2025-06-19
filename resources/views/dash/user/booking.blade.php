@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <section class="flex-1 overflow-auto p-6 space-y-6 mt-12 mx-8">
                    <h1 class="text-2xl font-bold mb-4">
                        Booking
                    </h1>
                    <nav class="flex space-x-6 text-gray-400 text-sm font-semibold mb-6 border-b border-gray-700">
                        <button class="text-white border-b-2 border-[#0ea5e9] pb-1 flex items-center space-x-1">
                            <span>
                                All
                            </span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">
                                10
                            </span>
                        </button>
                        <button class="flex items-center space-x-1" disabled="">
                            <span>
                                Pending
                            </span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">
                                5
                            </span>
                        </button>
                        <button class="flex items-center space-x-1" disabled="">
                            <span>
                                Confirmed
                            </span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">
                                5
                            </span>
                        </button>
                        <button class="flex items-center space-x-1" disabled="">
                            <span>
                                Completed
                            </span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">
                                0
                            </span>
                        </button>
                        <button class="flex items-center space-x-1" disabled="">
                            <span>
                                Cancelled
                            </span>
                            <span class="bg-gray-700 rounded text-xs font-normal px-2 py-[2px]">
                                0
                            </span>
                        </button>
                    </nav>
                    <div class="space-y-6">
                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>
                                    22 February 2025
                                </span>
                                <span
                                    class="bg-green-500 text-white text-xs font-medium rounded-full px-4 py-1 select-none">
                                    Completed
                                </span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Modern building exterior with glass walls and blue sky"
                                    class="w-14 h-14 rounded-md object-cover flex-shrink-0" height="56"
                                    src="https://storage.googleapis.com/a1aa/image/ce5ef24b-6337-455a-001c-66d0af5e8b0c.jpg"
                                    width="56" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">
                                        The Cue Lounge
                                    </p>
                                    <p class="text-gray-300 text-xs">
                                        27 June 2025, 12.00-13.00
                                    </p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">
                                    50.000
                                </div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">
                                    Total :
                                </span>
                                <span class="text-white text-lg">
                                    50.000
                                </span>
                            </footer>
                        </section>
                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>
                                    22 February 2025
                                </span>
                                <span
                                    class="bg-[#3b82f6] text-white text-xs font-medium rounded-full px-4 py-1 select-none">
                                    Confirmed
                                </span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Placeholder image icon in gray square"
                                    class="w-14 h-14 rounded-md object-cover flex-shrink-0 bg-gray-600" height="56"
                                    src="https://storage.googleapis.com/a1aa/image/a356a268-a5f4-435f-182e-4afa4c919e13.jpg"
                                    width="56" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">
                                        Golden Eight Billiards
                                    </p>
                                    <p class="text-gray-300 text-xs">
                                        27 June 2025, 12.00-13.00
                                    </p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">
                                    120.000
                                </div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">
                                    Total :
                                </span>
                                <span class="text-white text-lg">
                                    120.000
                                </span>
                            </footer>
                        </section>
                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>
                                    22 February 2025
                                </span>
                                <span
                                    class="bg-[#fbbf24] text-white text-xs font-medium rounded-full px-4 py-1 select-none">
                                    Pending
                                </span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Placeholder image icon in gray square"
                                    class="w-14 h-14 rounded-md object-cover flex-shrink-0 bg-gray-600" height="56"
                                    src="https://storage.googleapis.com/a1aa/image/a356a268-a5f4-435f-182e-4afa4c919e13.jpg"
                                    width="56" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">
                                        Elite Cue Arena
                                    </p>
                                    <p class="text-gray-300 text-xs">
                                        27 June 2025, 12.00-13.00
                                    </p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">
                                    90.000
                                </div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">
                                    Total :
                                </span>
                                <span class="text-white text-lg">
                                    90.000
                                </span>
                            </footer>
                        </section>
                        <section class="bg-[#222222] rounded-lg p-4 space-y-4">
                            <header class="flex justify-between border-b border-gray-400 pb-2 text-sm">
                                <span>
                                    22 February 2025
                                </span>
                                <span class="bg-red-500 text-white text-xs font-medium rounded-full px-4 py-1 select-none">
                                    Cancelled
                                </span>
                            </header>
                            <div class="flex items-center space-x-4 border-b border-gray-400 pb-3">
                                <img alt="Placeholder image icon in gray square"
                                    class="w-14 h-14 rounded-md object-cover flex-shrink-0 bg-gray-600" height="56"
                                    src="https://storage.googleapis.com/a1aa/image/a356a268-a5f4-435f-182e-4afa4c919e13.jpg"
                                    width="56" />
                                <div class="flex-1">
                                    <p class="font-bold text-white text-sm leading-tight">
                                        Elite Cue Arena
                                    </p>
                                    <p class="text-gray-300 text-xs">
                                        27 June 2025, 12.00-13.00
                                    </p>
                                </div>
                                <div class="text-gray-300 text-sm w-16 text-right">
                                    90.000
                                </div>
                            </div>
                            <footer class="flex justify-end text-sm font-semibold text-white">
                                <span class="mr-2">
                                    Total :
                                </span>
                                <span class="text-white text-lg">
                                    90.000
                                </span>
                            </footer>
                        </section>
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
