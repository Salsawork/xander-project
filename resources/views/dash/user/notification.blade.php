@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 my-8">
                @include('partials.topbar')
                <h1 class="text-2xl md:text-3xl font-extrabold mb-8 mx-16 mt-16">Notification Settings</h1>
                <section class="bg-[#2a2a2a] rounded-lg p-8 mx-16 space-y-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-bold text-lg md:text-xl">Order Updates</h2>
                            <p class="text-xs md:text-sm text-gray-300 mt-1 max-w-lg">
                                Get notified when your order status changes
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-[#0d6efd] peer-checked:bg-[#0d6efd] transition-colors">
                            </div>
                            <div
                                class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5">
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-bold text-lg md:text-xl">Booking Updates</h2>
                            <p class="text-xs md:text-sm text-gray-300 mt-1 max-w-lg">
                                Receive updates on your billiard table reservations and sparring sessions
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-[#0d6efd] peer-checked:bg-[#0d6efd] transition-colors">
                            </div>
                            <div
                                class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5">
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-bold text-lg md:text-xl">Promotions & Vouchers</h2>
                            <p class="text-xs md:text-sm text-gray-300 mt-1 max-w-lg">
                                Stay informed about new discounts, special offers, and limited-time vouchers.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-[#0d6efd] peer-checked:bg-[#0d6efd] transition-colors">
                            </div>
                            <div
                                class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5">
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-bold text-lg md:text-xl">Community & Events</h2>
                            <p class="text-xs md:text-sm text-gray-300 mt-1 max-w-lg">
                                Receive updates about upcoming tournaments, events, or training sessions.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-[#0d6efd] peer-checked:bg-[#0d6efd] transition-colors">
                            </div>
                            <div
                                class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5">
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-bold text-lg md:text-xl">Account & Security Alerts</h2>
                            <p class="text-xs md:text-sm text-gray-300 mt-1 max-w-lg">
                                Get alerts about login attempts, password changes, or security updates.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-[#0d6efd] peer-checked:bg-[#0d6efd] transition-colors">
                            </div>
                            <div
                                class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5">
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="font-bold text-lg md:text-xl">Newsletter & Announcements</h2>
                            <p class="text-xs md:text-sm text-gray-300 mt-1 max-w-lg">
                                Stay informed with the latest news and updates from Xander Billiard.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" />
                            <div
                                class="w-11 h-6 bg-gray-300 rounded-full peer peer-focus:ring-4 peer-focus:ring-[#0d6efd] peer-checked:bg-[#0d6efd] transition-colors">
                            </div>
                            <div
                                class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform peer-checked:translate-x-5">
                            </div>
                        </label>
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
