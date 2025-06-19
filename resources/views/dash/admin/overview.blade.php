@extends('app')
@section('title', 'Admin Dashboard - Overview')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-2xl font-bold p-8 mt-12">Shop Overview</h1>
                <section class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6 px-8">
                    <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Page Visit</h2>
                        <div class="text-3xl font-extrabold leading-none mb-1">129.475</div>
                        <div class="text-xs text-gray-500 mb-2">000 last month</div>
                        <span
                            class="inline-flex items-center text-xs font-semibold bg-[#0a8aff] rounded px-2 py-0.5 text-white">
                            +1.5% <i class="fas fa-arrow-up ml-1"></i>
                        </span>
                    </div>
                    <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Weekly Earnings</h2>
                        <div class="text-3xl font-extrabold leading-none mb-1">Rp. 10.234.000</div>
                        <div class="text-xs text-gray-500 mb-2">Rp. 100.000 last month</div>
                        <span
                            class="inline-flex items-center text-xs font-semibold bg-[#0a8aff] rounded px-2 py-0.5 text-white">
                            +1.5% <i class="fas fa-arrow-up ml-1"></i>
                        </span>
                    </div>
                    <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <h2 class="font-semibold text-sm mb-1">Session Purchased</h2>
                        <div class="text-3xl font-extrabold leading-none mb-1">123.000</div>
                        <div class="text-xs text-gray-500 mb-2">000.000 last year</div>
                        <span
                            class="inline-flex items-center text-xs font-semibold bg-[#ef4444] rounded px-2 py-0.5 text-white">
                            -1.5% <i class="fas fa-arrow-down ml-1"></i>
                        </span>
                    </div>
                </section>
                <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-8">
                    <div class="lg:col-span-2 bg-[#292929] rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Sales Report</h3>
                            <button aria-label="More options" class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <div class="overflow-x-auto">
                            <svg role="img" aria-label="Bar chart showing sales report from January to June"
                                class="w-full h-48" viewBox="0 0 500 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="40" y1="10" x2="40" y2="180" stroke="#4b5563"
                                    stroke-width="1" />
                                <line x1="40" y1="180" x2="460" y2="180" stroke="#4b5563"
                                    stroke-width="1" />
                                <text x="40" y="195" fill="#9ca3af" font-weight="700" font-family="sans-serif"
                                    font-size="14" text-anchor="middle">JAN</text>
                                <text x="110" y="195" fill="#9ca3af" font-weight="700" font-family="sans-serif"
                                    font-size="14" text-anchor="middle">FEB</text>
                                <text x="180" y="195" fill="#9ca3af" font-weight="700" font-family="sans-serif"
                                    font-size="14" text-anchor="middle">MAR</text>
                                <text x="250" y="195" fill="#9ca3af" font-weight="700" font-family="sans-serif"
                                    font-size="14" text-anchor="middle">APR</text>
                                <text x="320" y="195" fill="#9ca3af" font-weight="700" font-family="sans-serif"
                                    font-size="14" text-anchor="middle">MAY</text>
                                <text x="390" y="195" fill="#9ca3af" font-weight="700" font-family="sans-serif"
                                    font-size="14" text-anchor="middle">JUN</text>
                                <rect x="20" y="155" width="40" height="25" fill="#3b82f6" />
                                <rect x="90" y="120" width="40" height="60" fill="#3b82f6" />
                                <rect x="160" y="160" width="40" height="20" fill="#3b82f6" />
                                <rect x="230" y="155" width="40" height="25" fill="#3b82f6" />
                                <rect x="300" y="150" width="40" height="30" fill="#3b82f6" />
                                <rect x="370" y="120" width="40" height="60" fill="#3b82f6" />
                                <rect x="70" y="180" width="40" height="0" fill="#34d399" />
                                <rect x="140" y="160" width="40" height="20" fill="#34d399" />
                                <rect x="210" y="160" width="40" height="20" fill="#34d399" />
                                <rect x="280" y="120" width="40" height="60" fill="#34d399" />
                                <rect x="350" y="140" width="40" height="40" fill="#34d399" />
                                <rect x="420" y="160" width="40" height="20" fill="#34d399" />
                            </svg>
                        </div>
                    </div>
                    <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Inventory Overview</h3>
                            <button aria-label="More options" class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <ul class="space-y-3 text-sm">
                            <li class="flex items-start space-x-3">
                                <span class="text-[#fbbf24] mt-0.5"><i class="fas fa-exclamation-circle"></i></span>
                                <div>
                                    <div class="font-semibold">CueCare Oil Finish Set</div>
                                    <div class="text-gray-300">is low in stock</div>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <span class="text-[#ef4444] mt-0.5"><i class="fas fa-times-circle"></i></span>
                                <div>
                                    <div class="font-semibold">CueCraft Orion LX</div>
                                    <div class="text-gray-300">is out of stock</div>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <span class="text-[#34d399] mt-0.5"><i class="fas fa-plus-circle"></i></span>
                                <div>
                                    <div class="font-semibold">NeonStrike Evolve</div>
                                    <div class="text-gray-300">is low in stock</div>
                                </div>
                            </li>
                            <li class="flex items-start space-x-3">
                                <span class="text-[#fbbf24] mt-0.5"><i class="fas fa-exclamation-circle"></i></span>
                                <div>
                                    <div class="font-semibold">CueMark Fusion Chalk</div>
                                    <div class="text-gray-300">is low in stock</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </section>
                <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6 px-8">
                    <div class="lg:col-span-2 bg-[#292929] rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Recent Transaction</h3>
                            <button aria-label="More options" class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <ul class="space-y-4 text-sm">
                            <li class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <span class="text-[#34d399]"><i class="fas fa-check-circle"></i></span>
                                    <div>
                                        <div class="font-semibold">Table #1</div>
                                        <div class="text-gray-400 text-xs">01/02/2025 <span class="mx-1">|</span>
                                            17.38
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">Completed</div>
                                    <div class="text-gray-400 text-xs">0JKTWNOC1835</div>
                                </div>
                            </li>
                            <li class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <span class="text-[#ef4444]"><i class="fas fa-times-circle"></i></span>
                                    <div>
                                        <div class="font-semibold">Table #3</div>
                                        <div class="text-gray-400 text-xs">01/02/2025 <span class="mx-1">|</span>
                                            13.17
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">Cancelled</div>
                                    <div class="text-gray-400 text-xs">9TSNAI7143KF</div>
                                </div>
                            </li>
                            <li class="flex justify-between items-center">
                                <div class="flex items-center space-x-3">
                                    <span class="text-[#34d399]"><i class="fas fa-check-circle"></i></span>
                                    <div>
                                        <div class="font-semibold">Table #4</div>
                                        <div class="text-gray-400 text-xs">01/02/2025 <span class="mx-1">|</span>
                                            15.10
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold">Completed</div>
                                    <div class="text-gray-400 text-xs">1QWMSUAK98F6</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="bg-[#292929] rounded-lg p-5 shadow-sm">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="font-semibold">Notification</h3>
                            <button aria-label="More options" class="text-gray-400 hover:text-white focus:outline-none">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <hr class="border-gray-600 mb-3" />
                        <ul class="space-y-4 text-sm">
                            <li>
                                <div class="font-semibold">New Venue Registration</div>
                                <div class="text-gray-400">Pocket &amp; Play – Pending Review</div>
                            </li>
                            <li>
                                <div class="font-semibold">New Athlete Registration</div>
                                <div class="text-gray-400">Ahmad Hendra – Awaiting Approval</div>
                            </li>
                            <li>
                                <div class="font-semibold">New Venue Registration</div>
                                <div class="text-gray-400">Chalk House Jakarta – Pending Review</div>
                            </li>
                        </ul>
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
