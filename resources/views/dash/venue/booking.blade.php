@extends('app')
@section('title', 'Venue Booking')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-8 md:my-8">
            @include('partials.topbar')
            <h1 class="text-2xl md:text-3xl font-extrabold mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">Booking Management</h1>
            <section class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
                <div class="md:hidden grid grid-cols-1 gap-3 sm:gap-4">
                    {{-- Available & Booked Count --}}
                    <div class="bg-[#292929] rounded-lg p-4 sm:p-5 shadow-md flex flex-col items-center justify-center min-h-[150px]">
                        @include('dash.venue.components.booking.avail-booked', ['availableCount' => $availableCount, 'bookedCount' => $bookedCount])
                    </div>

                    {{-- Table List --}}
                    <div class="bg-[#292929] rounded-lg p-4 sm:p-5 shadow-md h-auto">
                        @include('dash.venue.components.booking.table-list', ['tables' => $tables])
                    </div>

                    {{-- Price & Schedule --}}
                    <div class="bg-[#292929] rounded-lg p-4 sm:p-5 shadow-md">
                        @include('dash.venue.components.booking.price-schedule', ['priceSchedules' => $priceSchedules])
                    </div>
                </div>

                <div class="hidden md:grid md:grid-cols-5 gap-3 sm:gap-4">
                    {{-- Table List - Menggunakan auto-height --}}
                    <div class="col-span-1 sm:col-span-2 md:col-span-3 bg-[#292929] rounded-lg p-4 sm:p-5 md:p-6 shadow-md h-auto sm:h-[500px] md:h-130">
                        @include('dash.venue.components.booking.table-list', ['tables' => $tables])
                    </div>

                    <div class="col-span-1 sm:col-span-2 md:col-span-2 flex flex-col gap-3 sm:gap-4">
                        {{-- Available & Booked Count --}}
                        <div class="bg-[#292929] rounded-lg p-4 sm:p-5 md:p-6 shadow-md flex flex-col items-center justify-center min-h-[150px]">
                            @include('dash.venue.components.booking.avail-booked', ['availableCount' => $availableCount, 'bookedCount' => $bookedCount])
                        </div>

                        {{-- Price & Schedule --}}
                        <div class="bg-[#292929] rounded-lg p-4 sm:p-5 md:p-6 shadow-md flex-grow">
                            @include('dash.venue.components.booking.price-schedule', ['priceSchedules' => $priceSchedules])
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>
@endsection