@extends('app')
@section('title', $event->name . ' - Tournament Bracket - Xander Billiard')

@section('content')
    {{-- ====== Anti white flash / rubber-band (iOS & Android) ====== --}}
    <div id="antiBounceBg" aria-hidden="true"></div>

    <style>
        :root { color-scheme: dark; }

        /* Pastikan SEMUA root gelap */
        :root, html, body { background:#0a0a0a; }
        html, body { height:100%; }

        /* Matikan overscroll glow/bounce tembus body */
        html, body {
            overscroll-behavior-y: none;
            overscroll-behavior-x: none;
            touch-action: pan-y;
            -webkit-text-size-adjust: 100%;
        }

        /* Kanvas gelap fixed di belakang semua konten:
           diperpanjang ke atas & bawah supaya saat bounce tetap gelap */
        #antiBounceBg{
            position: fixed;
            left:0; right:0;
            top:-120svh; bottom:-120svh;
            background:#0a0a0a;
            z-index:-1;
            pointer-events:none;
        }

        /* Kalau layout punya wrapper lain, pastikan gelap juga */
        #app, main { background:#0a0a0a; }
    </style>

    {{-- Stabilkan unit tinggi viewport di mobile (toolbar naik/turun) --}}
    <script>
        (function(){
            function setSVH(){
                const svh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--svh', svh + 'px');
            }
            setSVH();
            window.addEventListener('resize', setSVH);
        })();
    </script>

<div class="min-h-screen bg-neutral-900 text-white">
    <!-- Header dengan background -->
    <div class="mb-8 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
        <p class="text-sm text-gray-400 mt-1">
            <a href="{{ route('events.index') }}" class="hover:text-white">Home</a> /
            <a href="{{ route('events.show', $event) }}" class="hover:text-white">Event</a> /
            <span>Tournament Bracket</span>
        </p>
        <div class="flex items-center justify-between">
            <h2 class="text-4xl font-bold uppercase text-white">{{ $event->name }} - Tournament Bracket</h2>
        </div>
    </div>

    <!-- Bracket container -->
    <div class="overflow-x-auto px-2 py-8">
        <a href="{{ route('events.show', $event) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors ml-10">
            <i class="fas fa-arrow-left mr-2"></i> Back to Event
        </a>

        <div class="min-w-[1400px] mt-8 flex items-center justify-between">
            <!-- Round 1 (16 players) -->
            <div class="flex flex-col justify-around h-[1500px] mx-4">
                <div class="text-center font-bold mb-6">Round 1</div>
                @php
                    $round1Players = $brackets->where('round', 1)->sortBy('position');
                @endphp
                @foreach($round1Players as $bracket)
                    <div class="mb-12">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px]">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-400' }}">{{ $bracket->player_name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Lines between Round 1 and Round 2 -->
            <div class="flex flex-col justify-around h-[1500px] mx-1">
                @for($i = 0; $i < 8; $i++)
                    <div class="flex items-center h-[100px]">
                        <div class="w-8 border-t border-r border-gray-600 h-[90px] rounded-tr-lg"></div>
                    </div>
                    <div class="flex items-center h-[100px]">
                        <div class="w-8 border-b border-r border-gray-600 h-[90px] rounded-br-lg"></div>
                    </div>
                @endfor
            </div>

            <!-- Round 2 (8 players) -->
            <div class="flex flex-col justify-around h-[1500px] mx-4">
                <div class="text-center font-bold mb-6">Round 2</div>
                @php
                    $round2Players = $brackets->where('round', 2)->sortBy('position');
                @endphp
                @foreach($round2Players as $bracket)
                    <div class="mb-36">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px]">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-400' }}">{{ $bracket->player_name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Lines between Round 2 and Semifinal -->
            <div class="flex flex-col justify-around h-[1500px] mx-1">
                @for($i = 0; $i < 4; $i++)
                    <div class="flex items-center h-[100px]">
                        <div class="w-8 border-t border-r border-gray-600 h-[180px] rounded-tr-lg"></div>
                    </div>
                    <div class="flex items-center h-[360px]">
                        <div class="w-8 border-b border-gray-600 border-r h-[180px] rounded-br-lg"></div>
                    </div>
                @endfor
            </div>

            <!-- Semifinal (4 players) -->
            <div class="flex flex-col justify-around h-[1500px] mx-4">
                <div class="text-center font-bold mb-6">Semifinal</div>
                @php
                    $semifinalPlayers = $brackets->where('round', 3)->sortBy('position');
                @endphp
                @foreach($semifinalPlayers as $bracket)
                    <div class="mb-64">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px]">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-400' }}">{{ $bracket->player_name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Lines between Semifinal and Final -->
            <div class="flex flex-col justify-around h-[1500px] mx-1">
                @for($i = 0; $i < 2; $i++)
                    <div class="flex items-center h-[720px]">
                        <div class="w-8 border-t border-r border-gray-600 h-[360px] rounded-tr-lg"></div>
                    </div>
                    <div class="flex items-center h-[720px]">
                        <div class="w-8 border-b border-r border-gray-600 h-[360px] rounded-br-lg"></div>
                    </div>
                @endfor
            </div>

            <!-- Final (2 players) -->
            <div class="flex flex-col justify-around h-[500px] mx-4">
                <div class="text-center font-bold mt-30">Final</div>
                @php
                    $finalPlayers = $brackets->where('round', 4)->sortBy('position');
                @endphp
                @foreach($finalPlayers as $bracket)
                    <div class="mb-0">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px]">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-400' }}">{{ $bracket->player_name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Champion -->
            <div class="flex flex-col justify-center h-[1500px] mx-1 mt-55">
                <div class="w-8 border-t border-r border-gray-600 h-[100px] rounded-tr-lg"></div>
                <div class="w-8 border-b border-r border-gray-600 h-[100px] rounded-tr-lg"></div>
            </div>

            <div class="flex flex-col justify-center h-[1500px] mx-4 mt-40">
                <div class="text-center font-bold mb-6">Champion</div>
                @php
                    $champion = $brackets->where('round', 4)->where('is_winner', true)->first();
                @endphp
                @if($champion)
                    <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px]">
                        <p class="text-sm font-bold text-yellow-400">{{ $champion->player_name }}</p>
                    </div>
                @else
                    <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px]">
                        <p class="text-sm text-gray-400">TBD</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
