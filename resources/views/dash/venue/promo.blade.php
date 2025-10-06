@extends('app')
@section('title', 'Promo Management')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-6 md:my-8">
            @include('partials.topbar')
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 md:mb-8 mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16 space-y-4 sm:space-y-0">
                <h1 class="text-2xl md:text-3xl font-extrabold">Promo Management</h1>
                <a href="{{ route('venue.promo.create') }}" class="sm:w-auto bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-center">Create Voucher</a>
            </div>

            <section class="mx-4 sm:mx-8 md:mx-16 space-y-4 sm:space-y-6 md:space-y-8">
                <div class="bg-[#292929] rounded-lg p-4 sm:p-5 md:p-6 shadow-md">
                    @include('dash.venue.components.promo.voucher-list')
                </div>
            </section>
        </main>
    </div>
</div>
@endsection