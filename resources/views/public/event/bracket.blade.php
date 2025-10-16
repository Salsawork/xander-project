@extends('app')
@section('title', $event->name . ' - Tournament Bracket - Xander Billiard')

@section('content')
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

        /* Bracket Layout */
        .bracket-container {
            display: flex;
            gap: 80px;
            padding: 40px 20px;
            min-width: max-content;
        }

        .bracket-round {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            min-width: 220px;
        }

        /* MATCH BOX - Container for 2 players */
        .bracket-match {
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid #404855;
            border-radius: 4px;
            background: #1a1a1a;
            overflow: hidden;
        }

        .bracket-match.has-winner {
            border-color: #22c55e;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.15);
        }

        /* Individual player inside match box */
        .bracket-player {
            position: relative;
            padding: 10px 12px;
            border-bottom: 1px solid #282828;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 36px;
            background: #0f0f0f;
        }

        .bracket-player:last-child {
            border-bottom: none;
        }

        .bracket-player.winner {
            background: rgba(34, 197, 94, 0.08);
            border-left: 2px solid #22c55e;
            padding-left: 10px;
        }

        .bracket-player.winner p {
            color: #22c55e !important;
        }

        /* Player card wrapper */
        .bracket-player > div {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
        }

        /* Horizontal line from match box (30px to the right) */
        .bracket-player::after {
            display: none;
        }

        /* Hide original CSS-based connectors */
        .bracket-match::before,
        .bracket-match::after,
        .bracket-round::before,
        .bracket-round::after {
            display: none !important;
        }

        /* Specific spacing for different bracket sizes */
        /* Round 1: 4 matches - spacing kecil */
        .bracket-round.round-1 {
            justify-content: space-between;
            padding: 20px 0;
        }

        .bracket-round.round-1 .bracket-match {
            margin: 15px 0;
        }

        /* Round 2: 2 matches - spacing besar */
        .bracket-round.round-2 {
            justify-content: space-between;
            padding: 60px 0;
        }

        .bracket-round.round-2 .bracket-match {
            margin: 40px 0;
        }

        /* Round 3: 1 match - spacing sangat besar */
        .bracket-round.round-3 {
            justify-content: space-between;
            padding: 150px 0;
        }

        .bracket-round.round-3 .bracket-match {
            margin: 0;
        }

        /* Round 4 dan seterusnya */
        .bracket-round.round-4 {
            justify-content: space-between;
            padding: 300px 0;
        }

        .bracket-round.round-4 .bracket-match {
            margin: 0;
        }

        /* For final round - center it */
        .bracket-round.final-round {
            justify-content: center;
            padding: 0;
        }

        .bracket-round.final-round .bracket-match {
            margin: 0;
        }

        /* Champion badge styling for final round winner */
        .final-champion-badge {
            position: absolute;
            right: -50px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 20;
        }

        /* SVG Connector Container */
        .bracket-connectors {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .connector-path {
            stroke: #4b5563;
            stroke-width: 1;
            fill: none;
            stroke-linecap: round;
        }
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

        // Draw bracket connectors
        function drawConnectors() {
            const container = document.querySelector('.bracket-container');
            if (!container) return;

            // Remove existing SVG
            const existingSvg = container.querySelector('.bracket-connectors');
            if (existingSvg) existingSvg.remove();

            // Create new SVG
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.classList.add('bracket-connectors');
            
            svg.setAttribute('width', '100%');
            svg.setAttribute('height', '100%');
            svg.style.position = 'absolute';
            svg.style.top = '0';
            svg.style.left = '0';

            container.style.position = 'relative';
            container.appendChild(svg);

            // Get all rounds
            const rounds = container.querySelectorAll('.bracket-round');
            
            rounds.forEach((currentRound, roundIndex) => {
                if (roundIndex === rounds.length - 1) return; // Skip final round

                const nextRound = rounds[roundIndex + 1];
                const currentMatches = currentRound.querySelectorAll('.bracket-match');
                const nextMatches = nextRound.querySelectorAll('.bracket-match');

                currentMatches.forEach((currentMatch, matchIndex) => {
                    // Get position relative to container
                    const currentRect = currentMatch.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    
                    const currentY = currentRect.top - containerRect.top + (currentRect.height / 2);
                    const currentX = currentRect.right - containerRect.left;

                    const nextMatchIndex = Math.floor(matchIndex / 2);
                    const nextMatch = nextMatches[nextMatchIndex];

                    if (nextMatch) {
                        const nextRect = nextMatch.getBoundingClientRect();
                        const nextY = nextRect.top - containerRect.top + (nextRect.height / 2);
                        const nextX = nextRect.left - containerRect.left;

                        // Calculate middle point for vertical line
                        const midX = (currentX + nextX) / 2;

                        // Draw path: 
                        // 1. Horizontal dari current match ke tengah
                        // 2. Vertical dari top current ke top next
                        // 3. Horizontal dari tengah ke next match
                        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        const pathData = `M ${currentX} ${currentY} L ${midX} ${currentY} L ${midX} ${nextY} L ${nextX} ${nextY}`;
                        
                        path.setAttribute('d', pathData);
                        path.setAttribute('class', 'connector-path');
                        
                        svg.appendChild(path);

                        console.log(`Match ${matchIndex} -> ${nextMatchIndex}: From (${currentX}, ${currentY}) to (${nextX}, ${nextY})`);
                    }
                });
            });

            // Update SVG viewBox untuk proper scaling
            const viewBoxWidth = container.offsetWidth;
            const viewBoxHeight = container.offsetHeight;
            svg.setAttribute('viewBox', `0 0 ${viewBoxWidth} ${viewBoxHeight}`);
            svg.setAttribute('preserveAspectRatio', 'none');
        }

        // Draw connectors on load and resize
        window.addEventListener('load', function() {
            setTimeout(drawConnectors, 100);
        });
        window.addEventListener('resize', drawConnectors);
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(drawConnectors, 100);
        });
    </script>

@php
    $bracketsByRound = $brackets->groupBy('round');
    $maxRound = $brackets->max('round');
    $round1Count = $bracketsByRound->get(1, collect())->count();
    
    function getRoundLabel($round, $maxRound) {
        if ($round == $maxRound) return 'Final';
        if ($round == $maxRound - 1) return 'Semifinal';
        if ($round == $maxRound - 2 && $maxRound > 3) return 'Quarterfinal';
        return 'Round ' . $round;
    }
@endphp

<div class="min-h-screen bg-neutral-900 text-white pb-20">
    <!-- Header -->
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
    <div class="px-4 py-8">
        <a href="{{ route('events.show', $event) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors ml-6 inline-block mb-6">
            <i class="fas fa-arrow-left mr-2"></i> Back to Event
        </a>

        @if($round1Count == 0)
            <div class="mt-8 ml-6 bg-yellow-500 text-yellow-900 px-4 py-3 rounded-lg inline-block">
                <strong class="font-bold">Notice:</strong>
                <span>No bracket data available. Please generate the tournament tree first.</span>
            </div>
        @else
            <!-- Main Bracket -->
            <div class="overflow-x-auto">
                <div class="bracket-container">
                    @for($round = 1; $round <= $maxRound; $round++)
                        @php
                            $roundBrackets = $bracketsByRound->get($round, collect())->sortBy('position')->values();
                            $roundCount = $roundBrackets->count();
                            if ($roundCount == 0) continue;
                            
                            $matches = $roundBrackets->chunk(2);
                        @endphp

                        <!-- Round Column -->
                        <div class="bracket-round round-{{ $round }} {{ $round == $maxRound ? 'final-round' : '' }}">
                            <div class="text-center font-bold text-lg mb-8 {{ $round == 1 ? 'text-blue-400' : ($round == $maxRound ? 'text-orange-400' : ($round == $maxRound - 1 ? 'text-purple-400' : 'text-blue-400')) }}">
                                {{ getRoundLabel($round, $maxRound) }}
                            </div>
                            
                            @foreach($matches as $matchIndex => $match)
                                <div class="bracket-match {{ count($match->where('is_winner', true)) > 0 ? 'has-winner' : '' }}">
                                    @foreach($match as $bracket)
                                        <div class="bracket-player {{ $bracket->is_winner ? 'winner' : '' }} {{ $bracket->player_name === 'TBD' ? 'opacity-50' : '' }}">
                                            <div class="flex items-center justify-between w-full">
                                                <p class="text-sm font-medium {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-300' }} {{ $bracket->player_name === 'TBD' ? 'text-gray-600 italic' : '' }}">
                                                    {{ $bracket->player_name }}
                                                </p>
                                                
                                                @if($bracket->is_winner && $bracket->player_name !== 'TBD')
                                                    <div class="ml-2">
                                                        <i class="fas fa-check text-green-500 text-xs"></i>
                                                    </div>
                                                @endif
                                            </div>

                                            @if($round == $maxRound && $bracket->is_winner && $bracket->player_name !== 'TBD')
                                                <div class="final-champion-badge">
                                                    <div class="relative">
                                                        <div class="absolute inset-0 bg-gradient-radial from-yellow-500/30 to-transparent blur-lg"></div>
                                                        <div class="relative bg-gradient-to-br from-yellow-600 via-yellow-500 to-yellow-600 rounded-lg px-3 py-2 shadow-xl whitespace-nowrap">
                                                            <div class="flex items-center gap-2">
                                                                <i class="fas fa-crown text-lg text-yellow-200"></i>
                                                                <div>
                                                                    <p class="text-xs text-yellow-100 uppercase tracking-wider">Champion</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="mt-12 ml-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Legend -->
                <div class="bg-neutral-800 rounded-xl p-6">
                    <h3 class="font-bold text-lg mb-4 text-blue-400">
                        <i class="fas fa-info-circle mr-2"></i>Legend
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-neutral-700 rounded mr-3 flex items-center justify-center border-2 border-neutral-600">
                                <i class="fas fa-user text-gray-500 text-sm"></i>
                            </div>
                            <span class="text-sm">Active Player</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-neutral-700 rounded mr-3 flex items-center justify-center border-2 border-green-500">
                                <i class="fas fa-check text-green-500 text-sm"></i>
                            </div>
                            <span class="text-sm">Winner</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-600 to-yellow-500 rounded mr-3 flex items-center justify-center">
                                <i class="fas fa-trophy text-yellow-200 text-sm"></i>
                            </div>
                            <span class="text-sm">Champion</span>
                        </div>
                    </div>
                </div>

                <!-- Tournament Progress -->
                @php
                    $totalPlayers = $round1Count;
                    $totalWinners = $brackets->where('is_winner', true)->count();
                    $totalMatches = $brackets->count() / 2;
                    $progress = $totalMatches > 0 ? round(($totalWinners / $totalMatches) * 100) : 0;
                @endphp

                <div class="bg-neutral-800 rounded-xl p-6">
                    <h3 class="font-bold text-lg mb-4 text-blue-400">
                        <i class="fas fa-chart-bar mr-2"></i>Progress
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Total Players</span>
                            <span class="text-2xl font-bold text-blue-400">{{ $totalPlayers }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Completed</span>
                            <span class="text-2xl font-bold text-green-400">{{ $totalWinners }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">Remaining</span>
                            <span class="text-2xl font-bold text-yellow-400">{{ $totalMatches - $totalWinners }}</span>
                        </div>
                        <div class="pt-2">
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span>Progress</span>
                                <span class="font-bold text-purple-400">{{ $progress }}%</span>
                            </div>
                            <div class="bg-neutral-700 rounded-full h-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-blue-500 via-green-500 to-purple-500 h-full transition-all duration-500" 
                                     style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Round Details -->
                <div class="bg-neutral-800 rounded-xl p-6">
                    <h3 class="font-bold text-lg mb-4 text-blue-400">
                        <i class="fas fa-layer-group mr-2"></i>Rounds
                    </h3>
                    <div class="space-y-2">
                        @foreach($bracketsByRound as $round => $roundBrackets)
                            @php
                                $winnersInRound = $roundBrackets->where('is_winner', true)->count();
                                $matchesInRound = $roundBrackets->count() / 2;
                            @endphp
                            <div class="flex items-center justify-between py-2 border-b border-neutral-700">
                                <span class="text-sm font-medium">{{ getRoundLabel($round, $maxRound) }}</span>
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="text-gray-400">{{ $matchesInRound }} {{ $matchesInRound > 1 ? 'Matches' : 'Match' }}</span>
                                    <span class="px-2 py-1 rounded {{ $winnersInRound == $matchesInRound ? 'bg-green-600' : 'bg-gray-700' }}">
                                        {{ $winnersInRound }}/{{ $matchesInRound }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection