@extends('app')
@section('title', 'Create Promo')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold my-8 px-8">
                    Promo Detail
                </h1>
                <form method="POST" action="{{ route('venue.promo.store') }}"
                    class="flex flex-col lg:flex-row lg:space-x-8 px-8">
                    @csrf
                    <section aria-labelledby="general-info-title"
                        class="bg-[#262626] rounded-lg p-8  space-y-8 w-full">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">
                            General Information
                        </h2>
                        <div class="space-y-6">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="product-name">
                                Name
                            </label>
                            <input name="name"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                required id="product-name" type="text" placeholder="e.g., Regular Weekday" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="product-code">
                                Code
                            </label>
                            <input name="code"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                required id="product-code" type="text" placeholder="e.g., REGULAR-WEEKDAY" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="product-type">
                                Type
                            </label>
                            <select name="type"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                required id="product-type">
                                <option value="" disabled selected>Select type</option>
                                <option value="percentage">Percentage</option>
                                <option value="amount">Amount</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="discount-percentage">
                                Discount Percentage
                            </label>
                            <input name="discount_percentage"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="discount-percentage" type="number" placeholder="e.g., 10" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="discount-amount">
                                Discount Amount
                            </label>
                            <input name="discount_amount"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="discount-amount" type="number" placeholder="e.g., 50000" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="minimum-purchase">
                                Minimum Purchase
                            </label>
                            <input name="minimum_purchase"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="minimum-purchase" type="number" placeholder="e.g., 100000" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="quota">
                                Quota
                            </label>
                            <input name="quota"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="quota" type="number" placeholder="e.g., 100" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="claimed">
                                Start Date
                            </label>
                            <input name="start_date"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="start-date" type="date" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="end-date">
                                End Date
                            </label>
                            <input name="end_date"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                id="end-date" type="date" />
                        </div>
                        <div>
                            <button
                                class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition"
                                type="reset">
                                Discard
                            </button>
                            <button class="px-6 py-2 bg-[#0a8cff] rounded-md hover:bg-[#0077e6] transition"
                                type="submit">
                                Save
                            </button>
                        </div>
                    </section>
                </form>

            </main>
        </div>
    </div>
@endsection
