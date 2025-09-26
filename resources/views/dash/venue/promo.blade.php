@extends('app')
@section('title', 'Promo Management')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')

                <div class="p-8 mt-12 mx-20">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-3xl font-bold mb-8">Promo Management</h1>
                        <a href="{{ route('venue.promo.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Create Voucher</a>
                    </div>

                    <div class="bg-[#292929] rounded-lg p-6 shadow-md">
                        @include('dash.venue.components.promo.voucher-list')
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
