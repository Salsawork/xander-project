@extends('app')
@section('title', 'Athlete Dashboard')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar.athlete')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                
                <div class="p-8 mt-12 mx-20">
                    <h1 class="text-3xl font-bold mb-8">Dashboard</h1>
                    
                    {{-- Row 1: Statistik Utama (3 Kartu) --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                        {{-- Kartu Ratings --}}
                        <div class="bg-[#292929] rounded-lg p-6 shadow-md flex">
                            @include('dash.athlete.components.dashboard.ratings')
                        </div>
                        
                        {{-- Kartu Monthly Earnings --}}
                        <div class="bg-[#292929] col-span-2 rounded-lg p-6 shadow-md h-full">
                            @include('dash.athlete.components.dashboard.monthly-earnings')
                        </div>
                        
                        {{-- Kartu Session Created --}}
                        <div class="bg-[#292929] col-span-2 rounded-lg p-6 shadow-md">
                            @include('dash.athlete.components.dashboard.session-created')
                        </div>
                    </div>
                    
                    {{-- Row 2: Kalender dan Notifikasi --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                        {{-- Komponen Notification --}}
                        <div class="bg-[#292929] col-span-3 rounded-lg p-6 shadow-md">
                            @include('dash.athlete.components.dashboard.notification')
                        </div>
                
                        {{-- Komponen Calendar --}}
                        <div class="bg-[#292929] col-span-2 rounded-lg p-6 shadow-md">
                            @include('dash.athlete.components.dashboard.calendar')
                        </div>
                    </div>
                    
                    {{-- Row 3: Recent Session dan Review --}}
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        {{-- Komponen Recent Session --}}
                        <div class="bg-[#292929] col-span-3 rounded-lg p-4 shadow-md">
                            {{-- Component: Recent Session (The Break Room, Cue Corner, etc) --}}
                            @include('dash.athlete.components.dashboard.recent_session')
                        </div>
                        
                        {{-- Komponen Review --}}
                        <div class="bg-[#292929] col-span-2 rounded-lg p-4 shadow-md">
                            {{-- Component: Review (Lee Han Yu, Jessica Huang, etc) --}}
                            @include('dash.athlete.components.dashboard.review')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
