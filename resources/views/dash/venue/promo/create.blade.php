@extends('app')
@section('title', 'Create Promo')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-8 md:my-8">
            @include('partials.topbar')
            <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">Promo Detail</h1>
            @if ($errors->any())
            <div class="mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-4 bg-red-700 rounded p-3 sm:p-4">
                <ul class="list-disc list-inside text-xs sm:text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form method="POST" action="{{ route('venue.promo.store') }}"
                class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
                @csrf
                <section aria-labelledby="general-info-title"
                    class="bg-[#262626] rounded-lg p-4 sm:p-8 space-y-6 sm:space-y-8 w-full">
                    <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">
                        General Information
                    </h2>
                    <!-- Left & Right columns -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Left column -->
                        <div class="space-y-4 sm:space-y-6">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="product-name">
                                    Name
                                </label>
                                <input name="name"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    required id="product-name" type="text" placeholder="e.g., Regular Weekday" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="product-code">
                                    Code
                                </label>
                                <input name="code"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    required id="product-code" type="text" placeholder="e.g., REGULAR-WEEKDAY" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="product-type">
                                    Type
                                </label>
                                <select name="type"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
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
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="discount-percentage" type="number" placeholder="e.g., 10" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="discount-amount">
                                    Discount Amount
                                </label>
                                <input name="discount_amount"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="discount-amount" type="number" placeholder="e.g., 50000" />
                            </div>
                        </div>
                        <!-- Right column -->
                        <div class="space-y-4 sm:space-y-6">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="minimum-purchase">
                                    Minimum Purchase
                                </label>
                                <input name="minimum_purchase"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="minimum-purchase" type="number" placeholder="e.g., 100000" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="quota">
                                    Quota
                                </label>
                                <input name="quota"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="quota" type="number" placeholder="e.g., 100" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="start-date">
                                    Start Date
                                </label>
                                <input name="start_date"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="start-date" type="date" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="end-date">
                                    End Date
                                </label>
                                <input name="end_date"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="end-date" type="date" />
                            </div>
                        </div>
                    </div>
                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 pt-2">
                        <button
                            class="w-full sm:w-auto px-6 py-2.5 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition"
                            type="reset">
                            Discard
                        </button>
                        <button
                            class="w-full sm:w-auto px-6 py-2.5 bg-[#0a8cff] rounded-md hover:bg-[#0077e6] transition"
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