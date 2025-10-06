@extends('app')
@section('title', 'Create Price Schedule')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-8 md:my-8">
            @include('partials.topbar')
            <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">Create New Price Schedule</h1>

            @if (session('success'))
            <div class="px-8 mb-4">
                <div class="bg-green-500 text-white text-sm font-bold px-4 py-3 rounded-md" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('price-schedule.store') }}" class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
                @csrf
                <section class="bg-[#262626] rounded-lg p-8 space-y-8 w-full">
                    <h2 class="text-lg font-bold border-b border-gray-600 pb-2">
                        Schedule Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="hidden" name="venue_id" value="1">

                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="name">Schedule Name</label>
                            <input name="name" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm" required id="name" type="text" placeholder="e.g., Regular Weekday" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="price">Price</label>
                            <input name="price" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm" required id="price" type="number" placeholder="e.g., 50000" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="start_time">Start Time</label>
                            <input name="start_time" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm" required id="start_time" type="time" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="end_time">End Time</label>
                            <input name="end_time" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm" required id="end_time" type="time" required>
                        </div>
                        <div>
                            <div class="space-y-2">
                                <label class="block text-xs text-gray-400 mb-1">Days</label>
                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="days[]" value="monday" class="rounded bg-[#262626] border-gray-600">
                                        <span>Monday</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="days[]" value="tuesday" class="rounded bg-[#262626] border-gray-600">
                                        <span>Tuesday</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="days[]" value="wednesday" class="rounded bg-[#262626] border-gray-600">
                                        <span>Wednesday</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="days[]" value="thursday" class="rounded bg-[#262626] border-gray-600">
                                        <span>Thursday</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="days[]" value="friday" class="rounded bg-[#262626] border-gray-600">
                                        <span>Friday</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="days[]" value="saturday" class="rounded bg-[#262626] border-gray-600">
                                        <span>Saturday</span>
                                    </label>
                                    <label class="flex items-center space-x-2 text-sm">
                                        <input type="checkbox" name="days[]" value="sunday" class="rounded bg-[#262626] border-gray-600">
                                        <span>Sunday</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="time_category">Time Category</label>
                            <select name="time_category" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm" required id="time_category" required>
                                <option disabled selected>Choose time category</option>
                                <option value="peak-hours">Peak Hours</option>
                                <option value="normal-hours">Normal Hours</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="tables_applicable">Applicable Tables</label>
                            <select name="tables_applicable[]" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm" multiple required id="tables_applicable" required>
                                @foreach ($tables as $table)
                                <option value="{{ $table->table_number }}">{{ $table->table_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="is_active">Status</label>
                            <select name="is_active" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm" required id="is_active" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition" type="reset">
                            Discard
                        </button>
                        <button class="px-6 py-2 bg-[#0a8cff] rounded-md hover:bg-[#0077e6] transition" type="submit">
                            Save Schedule
                        </button>
                    </div>
                </section>
            </form>
        </main>
    </div>
</div>
@endsection