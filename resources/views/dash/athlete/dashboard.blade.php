@extends('app')
@section('title', 'Athlete Dashboard')

@push('styles')
<style>
    :root {
        color-scheme: dark;
        --page-bg: #0a0a0a;
    }

    html,
    body {
        height: 100%;
        min-height: 100%;
        background: var(--page-bg);
        overscroll-behavior-y: none;
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust: 100%;
    }

    #antiBounceBg {
        position: fixed;
        left: 0;
        right: 0;
        top: -120svh;
        bottom: -120svh;
        background: var(--page-bg);
        z-index: -1;
        pointer-events: none;
    }

    #app,
    main {
        background: var(--page-bg);
    }

    .main-scroll {
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        background: #0a0a0a;
    }

    .panel {
        background: #292929;
    }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar.athlete')
        <main class="main-scroll flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')
            <h1 class="text-2xl font-bold p-8 mt-12">Dashboard</h1>

            <div class="px-8">

                {{-- Row 1: Statistik Utama (3 Kartu) --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 sm:mb-8">
                    {{-- Kartu Ratings --}}
                    <div class="panel rounded-lg p-4 sm:p-6 shadow-md flex">
                        @include('dash.athlete.components.dashboard.ratings')
                    </div>

                    {{-- Kartu Monthly Earnings --}}
                    <div class="panel col-span-2 rounded-lg p-4 sm:p-6 shadow-md h-full">
                        @include('dash.athlete.components.dashboard.monthly-earnings')
                    </div>

                    {{-- Kartu Session Created --}}
                    <div class="panel col-span-2 rounded-lg p-4 sm:p-6 shadow-md">
                        @include('dash.athlete.components.dashboard.session-created')
                    </div>
                </div>

                {{-- Row 2: Kalender dan Notifikasi --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6 sm:mb-8">
                    {{-- Komponen Notification --}}
                    <div class="panel col-span-3 rounded-lg p-4 sm:p-6 shadow-md">
                        @include('dash.athlete.components.dashboard.notification')
                    </div>

                    {{-- Komponen Calendar --}}
                    <div class="panel col-span-2 rounded-lg p-4 sm:p-6 shadow-md">
                        @include('dash.athlete.components.dashboard.calendar')
                    </div>
                </div>

                {{-- Row 3: Recent Session dan Review --}}
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    {{-- Komponen Recent Session --}}
                    <div class="panel col-span-3 rounded-lg p-4 shadow-md">
                        {{-- Component: Recent Session (The Break Room, Cue Corner, etc) --}}
                        @include('dash.athlete.components.dashboard.recent_session')
                    </div>

                    {{-- Komponen Review --}}
                    <div class="panel col-span-2 rounded-lg p-4 shadow-md">
                        {{-- Component: Review (Lee Han Yu, Jessica Huang, etc) --}}
                        @include('dash.athlete.components.dashboard.review')
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
