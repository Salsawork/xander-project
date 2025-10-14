@extends('app')
@section('title', $event->name . ' - Tournament Bracket - Xander Billiard')

@section('content')
    <div id="antiBounceBg" aria-hidden="true"></div>

    <style>
        :root {
            color-scheme: dark;
        }

        :root,
        html,
        body {
            background: #0a0a0a;
        }

        html,
        body {
            height: 100%;
        }

        html,
        body {
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
            background: #0a0a0a;
            z-index: -1;
            pointer-events: none;
        }

        #app,
        main {
            background: #0a0a0a;
        }

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
            min-width: 250px;
        }

        .bracket-match {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin: 20px 0;
        }

        .bracket-player {
            position: relative;
        }

        /* Horizontal line from each player */
        .bracket-player::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            width: 40px;
            height: 2px;
            background: #4b5563;
        }

        /* Vertical line connecting the two players in a match */
        .bracket-match::before {
            content: '';
            position: absolute;
            left: calc(100% + 40px);
            top: 19px;
            height: calc(100% - 10px);
            width: 2px;
            background: #4b5563;
        }

        /* Horizontal line from vertical connector to next round */
        .bracket-match::after {
            content: '';
            position: absolute;
            left: calc(100% + 40px);
            top: 50%;
            width: 40px;
            height: 2px;
            background: #4b5563;
        }

        /* Hide lines for the last round (before champion) */
        .bracket-round:nth-last-child(2) .bracket-player::after,
        .bracket-round:nth-last-child(2) .bracket-match::before,
        .bracket-round:nth-last-child(2) .bracket-match::after {
            display: none;
        }

        /* Spacing adjustments per round */
        .bracket-round.round-2 .bracket-match {
            margin: 80px 0;
        }

        .bracket-round.round-3 .bracket-match {
            margin: 200px 0;
        }

        .bracket-round.round-4 .bracket-match {
            margin: 400px 0;
        }

        /* Special styling for final round to center it */
        .bracket-round.final-round {
            justify-content: center;
        }

        .bracket-round.final-round .bracket-match {
            margin: 0 !important;
        }

        /* Third place layout */
        .third-place-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-left: 60px;
            min-width: 250px;
        }

        .third-place-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.25rem;
            color: #f59e0b;
            margin-bottom: 1rem;
        }

        .third-place-match {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .third-place-player {
            background: #1f2937;
            border: 2px solid #92400e;
            border-radius: 0.5rem;
            padding: 10px 16px;
            color: #fef3c7;
            position: relative;
        }

        .third-place-player.winner {
            border-color: #fbbf24;
            background: linear-gradient(to right, #b45309, #f59e0b);
            color: #fff;
        }
    </style>

    <script>
        (function() {
            function setSVH() {
                const svh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--svh', svh + 'px');
            }
            setSVH();
            window.addEventListener('resize', setSVH);
        })();
    </script>

    @php
        $bracketsByRound = $brackets->groupBy('round');
        $maxRound = $brackets->max('round');
        $round1Count = $bracketsByRound->get(1, collect())->count();

        function getRoundLabel($round, $maxRound)
        {
            if ($round == 'third_place') {
                return '3rd Position';
            }
            if ($round == $maxRound) {
                return 'Final';
            }
            if ($round == $maxRound - 1) {
                return 'Semifinal';
            }
            if ($round == $maxRound - 2 && $maxRound > 3) {
                return 'Quarterfinal';
            }
            return 'Round ' . $round;
        }

        // Get losers from semifinal for 3rd place
        $semifinalRound = $maxRound - 1;
        $semifinalLosers = collect();
        $thirdPlaceWinner = null;

        if ($bracketsByRound->has($semifinalRound)) {
            $semifinalLosers = $bracketsByRound
                ->get($semifinalRound)
                ->where('is_winner', false)
                ->where('player_name', '!=', 'TBD');

            $thirdPlaceWinner = $event->third_place_winner ?? null;
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
            <a href="{{ route('events.show', $event) }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors ml-6 inline-block mb-6">
                <i class="fas fa-arrow-left mr-2"></i> Back to Event
            </a>

            @if ($round1Count == 0)
                <div class="mt-8 ml-6 bg-yellow-500 text-yellow-900 px-4 py-3 rounded-lg inline-block">
                    <strong class="font-bold">Notice:</strong>
                    <span>No bracket data available. Please generate the tournament tree first.</span>
                </div>
            @else
                <!-- Main Bracket -->
                <div class="overflow-x-auto">
                    <div class="bracket-container">
                        @for ($round = 1; $round <= $maxRound; $round++)
                            @php
                                $roundBrackets = $bracketsByRound->get($round, collect())->sortBy('position')->values();
                                $roundCount = $roundBrackets->count();
                                if ($roundCount == 0) {
                                    continue;
                                }

                                $matches = $roundBrackets->chunk(2);
                            @endphp

                            <!-- Round Column -->
                            <div class="bracket-round round-{{ $round }}">
                                <div
                                    class="text-center font-bold text-lg mb-12 {{ $round == 1 ? 'text-blue-400' : ($round == $maxRound ? 'text-orange-400' : ($round == $maxRound - 1 ? 'text-purple-400' : 'text-blue-400')) }}">
                                    {{ getRoundLabel($round, $maxRound) }}
                                </div>

                                @foreach ($matches as $matchIndex => $match)
                                    <div class="bracket-match">
                                        @foreach ($match as $bracket)
                                            <div class="bracket-player">
                                                <div
                                                    class="bg-neutral-800 rounded-lg px-4 py-3 transition-all duration-200 border-2 relative
                                                {{ $bracket->is_winner ? 'border-green-500 shadow-lg shadow-green-500/20' : 'border-neutral-700' }}
                                                {{ $bracket->player_name === 'TBD' ? 'opacity-50' : '' }}">

                                                    @if ($bracket->is_winner && $bracket->player_name !== 'TBD')
                                                        <div
                                                            class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full z-10">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                    @endif

                                                    <div class="flex items-center justify-between">
                                                        <div class="flex-1">
                                                            <p
                                                                class="text-sm font-medium {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-300' }} {{ $bracket->player_name === 'TBD' ? 'text-gray-600 italic' : '' }}">
                                                                {{ $bracket->player_name }}
                                                            </p>
                                                        </div>

                                                        @if ($bracket->is_winner && $bracket->player_name !== 'TBD')
                                                            <div class="ml-2">
                                                                <i class="fas fa-chevron-right text-green-500"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endfor

                        <!-- Champion Section -->
                        <div class="flex flex-col justify-center" style="min-width: 300px;">
                            <div class="text-center font-bold text-xl mb-8 text-yellow-400">
                                <i class="fas fa-trophy mr-2"></i>Champion
                            </div>

                            @php
                                $champion = $brackets->where('round', $maxRound)->where('is_winner', true)->first();
                            @endphp

                            @if ($champion)
                                <div class="relative">
                                    <div
                                        class="absolute inset-0 bg-gradient-radial from-yellow-500/20 to-transparent blur-xl">
                                    </div>

                                    <div
                                        class="relative bg-gradient-to-br from-yellow-600 via-yellow-500 to-yellow-600 rounded-xl px-8 py-10 shadow-2xl transform hover:scale-105 transition-transform duration-300">
                                        <div class="text-center">
                                            <i class="fas fa-crown text-5xl text-yellow-200 mb-4"></i>
                                            <p class="text-2xl font-bold text-white mb-2">{{ $champion->player_name }}</p>
                                            <p class="text-sm text-yellow-100 uppercase tracking-wider">Tournament Winner
                                            </p>
                                        </div>

                                        <div
                                            class="absolute -top-3 -left-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50 animate-ping">
                                        </div>
                                        <div
                                            class="absolute -bottom-3 -right-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50 animate-ping">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div
                                    class="bg-neutral-800 rounded-lg px-6 py-8 text-center border-2 border-dashed border-neutral-700">
                                    <i class="fas fa-hourglass-half text-4xl text-gray-500 mb-3"></i>
                                    <p class="text-gray-400 font-medium">To Be Determined</p>
                                    <p class="text-xs text-gray-600 mt-2">Winner will be announced after final match</p>
                                </div>
                            @endif
                        </div>
                        
                    </div>
                </div>


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
                    <div
                        class="w-10 h-10 bg-neutral-700 rounded mr-3 flex items-center justify-center border-2 border-neutral-600">
                        <i class="fas fa-user text-gray-500 text-sm"></i>
                    </div>
                    <span class="text-sm">Active Player</span>
                </div>
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-neutral-700 rounded mr-3 flex items-center justify-center border-2 border-green-500">
                        <i class="fas fa-check text-green-500 text-sm"></i>
                    </div>
                    <span class="text-sm">Winner</span>
                </div>
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-yellow-600 to-yellow-500 rounded mr-3 flex items-center justify-center">
                        <i class="fas fa-trophy text-yellow-200 text-sm"></i>
                    </div>
                    <span class="text-sm">Champion</span>
                </div>
                <div class="flex items-center">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-amber-700 to-amber-600 rounded mr-3 flex items-center justify-center">
                        <i class="fas fa-medal text-amber-200 text-sm"></i>
                    </div>
                    <span class="text-sm">3rd Place</span>
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
                @foreach ($bracketsByRound as $round => $roundBrackets)
                    @php
                        $winnersInRound = $roundBrackets->where('is_winner', true)->count();
                        $matchesInRound = $roundBrackets->count() / 2;
                    @endphp
                    <div class="flex items-center justify-between py-2 border-b border-neutral-700">
                        <span class="text-sm font-medium">{{ getRoundLabel($round, $maxRound) }}</span>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="text-gray-400">{{ $matchesInRound }}
                                {{ $matchesInRound > 1 ? 'Matches' : 'Match' }}</span>
                            <span
                                class="px-2 py-1 rounded {{ $winnersInRound == $matchesInRound ? 'bg-green-600' : 'bg-gray-700' }}">
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
