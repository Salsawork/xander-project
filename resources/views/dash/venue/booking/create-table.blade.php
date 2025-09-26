@extends('app')
@section('title', 'Create Table')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold my-8 px-8">
                    Product Detail
                </h1>
                <form method="POST" action="{{ route('venue.booking.store-table') }}"
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
                                    Table Number
                                </label>
                                <input name="table_number"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    required
                                    id="table-number" type="text" placeholder="Table Number" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="category">
                                    Status
                                </label>
                                <select name="status"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    required
                                    id="status">
                                    <option disabled selected>Please choose status</option>
                                    <option value="available">Available</option>
                                    <option value="booked">Booked</option>
                                </select>
                            </div>
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
