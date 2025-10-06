@extends('app')
@section('title', 'Venue Booking')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')
            <div class="p-8 mt-12 mx-20">
                <h1 class="text-3xl font-bold mb-8">Booking Management</h1>
                <div class="grid grid-cols-5 gap-4">
                    {{-- Table List - Menggunakan auto-height --}}
                    <div class="col-span-3 bg-[#292929] rounded-lg p-6 shadow-md h-130">
                        @include('dash.venue.components.booking.table-list', ['tables' => $tables])
                    </div>

                    <div class="col-span-2 flex flex-col gap-4">
                        {{-- Available & Booked Count --}}
                        <div class="bg-[#292929] rounded-lg p-6 shadow-md flex flex-col items-center justify-center">
                            @include('dash.venue.components.booking.avail-booked', ['availableCount' => $availableCount, 'bookedCount' => $bookedCount])
                        </div>

                        {{-- Price & Schedule --}}
                        <div class="bg-[#292929] rounded-lg p-6 shadow-md flex-grow">
                            @include('dash.venue.components.booking.price-schedule', ['priceSchedules' => $priceSchedules])
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection