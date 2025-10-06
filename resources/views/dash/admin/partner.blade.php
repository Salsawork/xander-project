@extends('app')
@section('title', 'Admin Dashboard - Venue/Partner')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-2xl font-bold p-8 mt-12">Venue/Partner Management</h1>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mx-8">
                    <section class="bg-gray-800 rounded-lg p-6 space-y-4 col-span-2">
                        <div class="flex justify-between items-center">
                            <h2 class="font-bold text-white text-lg">Venue</h2>
                            <input type="search" placeholder="Search"
                                class="bg-gray-900 border border-gray-600 rounded-md px-3 py-1 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600" />
                        </div>
                        <hr class="border-gray-700" />
                        <ul class="space-y-3 text-sm">
                            <li class="grid grid-cols-3 items-center">
                                <span class="font-bold">Rack &amp; Roll Billiards</span>
                                <span class="text-center">Jakarta Pusat</span>
                                <button aria-label="More options" class="text-gray-400 hover:text-white justify-self-end">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="grid grid-cols-3 items-center">
                                <span class="font-bold">Green Felt Lounge</span>
                                <span class="text-center">Jakarta Barat</span>
                                <button aria-label="More options" class="text-gray-400 hover:text-white justify-self-end">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="grid grid-cols-3 items-center">
                                <span class="font-bold">The 8-Ball Club</span>
                                <span class="text-center">Jakarta Utara</span>
                                <button aria-label="More options" class="text-gray-400 hover:text-white justify-self-end">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="grid grid-cols-3 items-center">
                                <span class="font-bold">Starlite Billiard Hall</span>
                                <span class="text-center">Jakarta Selatan</span>
                                <button aria-label="More options" class="text-gray-400 hover:text-white justify-self-end">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="grid grid-cols-3 items-center">
                                <span class="font-bold">Chalk House Jakarta</span>
                                <span class="text-center">Jakarta Timur</span>
                                <button aria-label="More options" class="text-gray-400 hover:text-white justify-self-end">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="grid grid-cols-3 items-center">
                                <span class="font-bold">Pocket &amp; Play</span>
                                <span class="text-center">Jakarta Pusat</span>
                                <button aria-label="More options" class="text-gray-400 hover:text-white justify-self-end">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                        </ul>
                    </section>
                    <aside class="bg-gray-800 rounded-lg p-6 space-y-4 max-w-md row-span-2">
                        <h2 class="font-bold text-white text-lg">Review</h2>
                        <hr class="border-gray-700" />
                        <ul class="text-xs space-y-4">
                            <li>
                                <p class="font-bold text-white text-sm">New Venue Registration</p>
                                <p class="text-gray-300">Pocket &amp; Play – Pending Review</p>
                            </li>
                            <li>
                                <p class="font-bold text-white text-sm">New Athlete Registration</p>
                                <p class="text-gray-300">Ahmad Hendra – Awaiting Approval</p>
                            </li>
                            <li>
                                <p class="font-bold text-white text-sm">New Venue Registration</p>
                                <p class="text-gray-300">Chalk House Jakarta – Pending Review</p>
                            </li>
                            <li>
                                <p class="font-bold text-white text-sm">New Athlete Registration</p>
                                <p class="text-gray-300">Dicky Herman – Awaiting Approval</p>
                            </li>
                            <li>
                                <p class="font-bold text-white text-sm">New Venue Registration</p>
                                <p class="text-gray-300">Side Pocket Studio – Pending Review</p>
                            </li>
                        </ul>
                    </aside>
                    <section class="bg-gray-800 rounded-lg p-6 space-y-4 col-span-2">
                        <div class="flex justify-between items-center">
                            <h2 class="font-bold text-white text-lg">Athlete</h2>
                            <input type="search" placeholder="Search"
                                class="bg-gray-900 border border-gray-600 rounded-md px-3 py-1 text-xs placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-600" />
                        </div>
                        <hr class="border-gray-700" />
                        <ul class="space-y-3 text-sm">
                            <li class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold">Vincentsius Adam Natahanael</p>
                                    <p class="text-gray-400 text-xs">Online</p>
                                </div>
                                <button aria-label="More options" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold">Bisma Kristian</p>
                                    <p class="text-gray-400 text-xs">5 min ago</p>
                                </div>
                                <button aria-label="More options" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold">Divananta Pradaya</p>
                                    <p class="text-gray-400 text-xs">1 hour ago</p>
                                </div>
                                <button aria-label="More options" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                            <li class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold">Hendra Zexus Tirta Simanjuntak</p>
                                    <p class="text-gray-400 text-xs">3 days ago</p>
                                </div>
                                <button aria-label="More options" class="text-gray-400 hover:text-white">
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                            </li>
                        </ul>
                    </section>
                </div>
            </main>
        </div>
    </div>
@endsection
