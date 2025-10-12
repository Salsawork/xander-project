@extends('app')
@section('title', $event->name . ' - Tournament Bracket - Xander Billiard')

@section('content')
    {{-- ====== Anti white flash / rubber-band (iOS & Android) ====== --}}
    <div id="antiBounceBg" aria-hidden="true"></div>

    <style>
        :root { color-scheme: dark; }
        :root, html, body { background:#0a0a0a; }
        html, body { height:100%; }
        html, body {
            overscroll-behavior-y: none;
            overscroll-behavior-x: none;
            touch-action: pan-y;
            -webkit-text-size-adjust: 100%;
        }
        #antiBounceBg{
            position: fixed;
            left:0; right:0;
            top:-120svh; bottom:-120svh;
            background:#0a0a0a;
            z-index:-1;
            pointer-events:none;
        }
        #app, main { background:#0a0a0a; }
    </style>

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

@php
    // Helper function: Cek apakah player adalah winner dan advance ke round berikutnya
    function getAdvancedPlayer($brackets, $currentRound, $position) {
        $nextRound = $currentRound + 1;
        $nextPosition = (int) ceil($position / 2);
        
        // Cari di round berikutnya apakah sudah ada data
        $nextBracket = $brackets->where('round', $nextRound)
                               ->where('position', $nextPosition)
                               ->first();
        
        if ($nextBracket && $nextBracket->player_name !== 'TBD') {
            return $nextBracket->player_name;
        }
        
        // Jika tidak ada, cari winner dari current round
        $currentBracket = $brackets->where('round', $currentRound)
                                   ->where('position', $position)
                                   ->where('is_winner', true)
                                   ->first();
        
        return $currentBracket ? $currentBracket->player_name : 'TBD';
    }
    
    // Helper function: Build bracket data with auto-advance
    function buildBracketData($brackets, $round) {
        $roundBrackets = $brackets->where('round', $round)->sortBy('position')->values();
        $result = [];
        
        foreach ($roundBrackets as $bracket) {
            // Jika TBD, coba ambil winner dari round sebelumnya
            if ($bracket->player_name === 'TBD' && $round > 1) {
                $prevRound = $round - 1;
                $position = $bracket->position;
                
                // Hitung dari posisi mana di round sebelumnya
                $prevPosition1 = ($position - 1) * 2 + 1;
                $prevPosition2 = ($position - 1) * 2 + 2;
                
                // Cari winner dari 2 posisi di round sebelumnya
                $winner1 = $brackets->where('round', $prevRound)
                                   ->where('position', $prevPosition1)
                                   ->where('is_winner', true)
                                   ->first();
                
                $winner2 = $brackets->where('round', $prevRound)
                                   ->where('position', $prevPosition2)
                                   ->where('is_winner', true)
                                   ->first();
                
                // Tentukan player yang advance
                if ($winner1) {
                    $bracket->player_name = $winner1->player_name;
                    $bracket->is_winner = false; // Belum menang di round ini
                } elseif ($winner2) {
                    $bracket->player_name = $winner2->player_name;
                    $bracket->is_winner = false;
                }
            }
            
            $result[] = $bracket;
        }
        
        return collect($result);
    }
@endphp

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
                <div class="text-center font-bold mb-6 text-blue-400">Round 1</div>
                @php
                    $round1Players = buildBracketData($brackets, 1);
                @endphp
                @foreach($round1Players as $bracket)
                    <div class="mb-12">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px] transition-all
                            {{ $bracket->is_winner ? 'ring-2 ring-green-500 shadow-lg' : '' }}">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-400' }}">
                                {{ $bracket->player_name }}
                            </p>
                            @if($bracket->is_winner)
                                <div class="text-xs text-green-400 mt-1">
                                    <i class="fas fa-check-circle mr-1"></i>Winner
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Lines between Round 1 and Round 2 -->
            <div class="flex flex-col justify-around h-[1500px] mx-1">
                @for($i = 0; $i < 8; $i++ )
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
                <div class="text-center font-bold mb-6 text-blue-400">Round 2</div>
                @php
                    $round2Players = buildBracketData($brackets, 2);
                @endphp
                @foreach($round2Players as $bracket)
                    <div class="mb-36">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px] transition-all
                            {{ $bracket->is_winner ? 'ring-2 ring-green-500 shadow-lg' : '' }}
                            {{ $bracket->player_name === 'TBD' ? 'opacity-50' : '' }}">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : ($bracket->player_name === 'TBD' ? 'text-gray-600 italic' : 'text-gray-300') }}">
                                {{ $bracket->player_name }}
                            </p>
                            @if($bracket->is_winner)
                                <div class="text-xs text-green-400 mt-1">
                                    <i class="fas fa-check-circle mr-1"></i>Winner
                                </div>
                            @endif
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
                <div class="text-center font-bold mb-6 text-purple-400">Semifinal</div>
                @php
                    $semifinalPlayers = buildBracketData($brackets, 3);
                @endphp
                @foreach($semifinalPlayers as $bracket)
                    <div class="mb-64">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px] transition-all
                            {{ $bracket->is_winner ? 'ring-2 ring-green-500 shadow-lg' : '' }}
                            {{ $bracket->player_name === 'TBD' ? 'opacity-50' : '' }}">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : ($bracket->player_name === 'TBD' ? 'text-gray-600 italic' : 'text-gray-300') }}">
                                {{ $bracket->player_name }}
                            </p>
                            @if($bracket->is_winner)
                                <div class="text-xs text-green-400 mt-1">
                                    <i class="fas fa-check-circle mr-1"></i>Winner
                                </div>
                            @endif
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
                <div class="text-center font-bold mt-30 text-orange-400">Final</div>
                @php
                    $finalPlayers = buildBracketData($brackets, 4);
                @endphp
                @foreach($finalPlayers as $bracket)
                    <div class="mb-0">
                        <div class="bg-neutral-800 rounded px-4 py-2 min-w-[200px] transition-all
                            {{ $bracket->is_winner ? 'ring-2 ring-green-500 shadow-lg' : '' }}
                            {{ $bracket->player_name === 'TBD' ? 'opacity-50' : '' }}">
                            <p class="text-sm {{ $bracket->is_winner ? 'text-white font-bold' : ($bracket->player_name === 'TBD' ? 'text-gray-600 italic' : 'text-gray-300') }}">
                                {{ $bracket->player_name }}
                            </p>
                            @if($bracket->is_winner)
                                <div class="text-xs text-green-400 mt-1">
                                    <i class="fas fa-trophy mr-1"></i>Champion
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Champion -->
            <div class="flex flex-col justify-center h-[1500px] mx-1 mt-55">
                <div class="w-8 border-t border-r border-gray-600 h-[100px] rounded-tr-lg"></div>
                <div class="w-8 border-b border-r border-gray-600 h-[100px] rounded-br-lg"></div>
            </div>

            <div class="flex flex-col justify-center h-[1500px] mx-4 mt-40">
                <div class="text-center font-bold mb-6 text-yellow-400">
                    <i class="fas fa-trophy mr-2"></i>Champion
                </div>
                @php
                    $champion = $brackets->where('round', 4)->where('is_winner', true)->first();
                @endphp
                @if($champion)
                    <div class="relative">
                        <div class="absolute inset-0 bg-yellow-500/20 blur-xl rounded-lg"></div>
                        <div class="relative bg-gradient-to-br from-yellow-600 via-yellow-500 to-yellow-600 rounded-lg px-6 py-4 min-w-[200px] shadow-2xl">
                            <div class="text-center">
                                <i class="fas fa-crown text-2xl text-yellow-200 mb-2"></i>
                                <p class="text-lg font-bold text-white">{{ $champion->player_name }}</p>
                                <p class="text-xs text-yellow-100 mt-1">Tournament Winner</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-neutral-800 rounded-lg px-4 py-2 min-w-[200px]">
                        <p class="text-sm text-gray-400 text-center italic">TBD</p>
                        <p class="text-xs text-gray-600 text-center mt-1">Awaiting final match</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-12 ml-10 p-4 bg-neutral-800 rounded-lg inline-block">
            <h3 class="font-bold mb-3 text-blue-400">
                <i class="fas fa-info-circle mr-2"></i>Legend
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-neutral-700 rounded mr-2"></div>
                    <span class="text-sm">Active Player</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 ring-2 ring-green-500 bg-neutral-700 rounded mr-2"></div>
                    <span class="text-sm">Winner</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gradient-to-br from-yellow-600 to-yellow-500 rounded mr-2"></div>
                    <span class="text-sm">Champion</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-neutral-700 opacity-50 rounded mr-2"></div>
                    <span class="text-sm">TBD (Waiting)</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection