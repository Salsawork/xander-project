@extends('app')
@section('title', 'Admin Dashboard - Promo')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <h1 class="text-2xl font-bold p-8 mt-12">Promo Management</h1>
            </main>
        </div>
    </div>
@endsection
