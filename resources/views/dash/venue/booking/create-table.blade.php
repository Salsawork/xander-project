@extends('app')
@section('title', 'Create Table')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-8 md:my-8">
            @include('partials.topbar')
            <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">Create Table</h1>
            <form method="POST" action="{{ route('venue.booking.store-table') }}"
                class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
                @csrf
                <section aria-labelledby="general-info-title"
                    class="bg-[#262626] rounded-lg p-4 sm:p-6 md:p-8 space-y-6 sm:space-y-8 w-full">
                    <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">
                        General Information
                    </h2>
                    <div class="space-y-4 sm:space-y-6">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5" for="table-number">
                                Table Number
                            </label>
                            <input name="table_number"
                                class="w-full rounded-lg border border-gray-600 bg-[#262626] px-4 py-2.5 text-base text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                                id="table-number"
                                type="text"
                                placeholder="Enter table number" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1.5" for="status">
                                Status
                            </label>
                            <select name="status"
                                class="w-full rounded-lg border border-gray-600 bg-[#262626] px-4 py-2.5 text-base text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                                id="status">
                                <option disabled selected>Please choose status</option>
                                <option value="available">Available</option>
                                <option value="booked">Booked</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 pt-4">
                        <button
                            class="w-full sm:w-auto order-2 sm:order-1 px-6 py-2.5 border-2 border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-colors"
                            type="reset">
                            Discard
                        </button>
                        <button
                            class="w-full sm:w-auto order-1 sm:order-2 px-6 py-2.5 bg-[#0a8cff] rounded-lg hover:bg-[#0077e6] transition-colors"
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