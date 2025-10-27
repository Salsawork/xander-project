@extends('app')
@section('title', $event->name . ' - Tournament Bracket - Xander Billiard')

@section('content')
    <div id="antiBounceBg" aria-hidden="true"></div>

    <style>
        :root {
            --match-height: 88px;
            /* Height of a match box (2 players) */
            --base-gap: 20px;
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

        /* Bracket Layout - Base */
        .bracket-container {
            display: flex;
            gap: 80px;
            padding: 40px 20px;
            min-width: max-content;
            align-items: center;
        }

        .bracket-round {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 220px;
        }

        /* Round Title */
        .round-title {
            text-align: center;
            font-weight: bold;
            font-size: 1.125rem;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 10;
            padding: 0.5rem 0;
        }

        /* MATCH BOX - Container for 2 players */
        .bracket-match {
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid #404855;
            border-radius: 6px;
            background: #1a1a1a;
            overflow: hidden;
            margin: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .bracket-match.has-winner {
            border-color: #22c55e;
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.2);
        }

        /* Individual player inside match box */
        .bracket-player {
            position: relative;
            padding: 12px 14px;
            border-bottom: 1px solid #282828;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 40px;
            background: #0f0f0f;
            transition: all 0.2s ease;
        }

        .bracket-player:last-child {
            border-bottom: none;
        }

        .bracket-player:hover {
            background: #151515;
        }

        .bracket-player.winner {
            background: rgba(34, 197, 94, 0.1);
            border-left: 3px solid #22c55e;
            padding-left: 11px;
        }

        .bracket-player.winner p {
            color: #22c55e !important;
            font-weight: 600;
        }

        /* Player card wrapper */
        .bracket-player>div {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
        }

        /* Hide original CSS-based connectors */
        .bracket-player::after,
        .bracket-match::before,
        .bracket-match::after,
        .bracket-round::before,
        .bracket-round::after {
            display: none !important;
        }

        /* Match spacing based on round - FIXED ALGORITHM */
        .matches-wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* Dynamic spacing based on number of matches */
        .bracket-round.round-1 .matches-wrapper {
            gap: var(--base-gap);
        }

        .bracket-round.round-2 .matches-wrapper {
            gap: calc(var(--match-height) + var(--base-gap) * 2);
        }

        .bracket-round.round-3 .matches-wrapper {
            gap: calc(var(--match-height) * 3 + var(--base-gap) * 4);
        }

        .bracket-round.round-4 .matches-wrapper {
            gap: calc(var(--match-height) * 7 + var(--base-gap) * 8);
        }

        .bracket-round.round-5 .matches-wrapper {
            gap: calc(var(--match-height) * 15 + var(--base-gap) * 16);
        }

        /* SVG Connector Container */
        .bracket-connectors {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .connector-path {
            stroke: #4b5563;
            stroke-width: 2;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .connector-path.winner {
            stroke: #22c55e;
            stroke-width: 2.5;
        }

        /* ============================================
               RESPONSIVE STYLES
               ============================================ */

        /* Mobile Styles (< 640px) */
        @media (max-width: 640px) {
            .bracket-container {
                gap: 60px;
                padding: 20px 10px;
            }

            .bracket-round {
                min-width: 170px;
            }

            .bracket-match {
                font-size: 0.8rem;
            }

            .bracket-player {
                padding: 10px 12px;
                min-height: 36px;
            }

            .bracket-player p {
                font-size: 0.8rem;
            }

            .round-title {
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
            }

            /* Match spacing for mobile */
            .bracket-round.round-1 .matches-wrapper {
                gap: 30px;
            }

            .bracket-round.round-2 .matches-wrapper {
                gap: 110px;
            }

            .bracket-round.round-3 .matches-wrapper {
                gap: 270px;
            }

            .bracket-round.round-4 .matches-wrapper {
                gap: 590px;
            }

            .connector-path {
                stroke-width: 1.5px;
            }
        }

        /* Tablet Styles (641px - 1024px) */
        @media (min-width: 641px) and (max-width: 1024px) {
            .bracket-container {
                gap: 70px;
                padding: 30px 15px;
            }

            .bracket-round {
                min-width: 195px;
            }

            .bracket-match {
                font-size: 0.9rem;
            }

            .bracket-player {
                padding: 11px 13px;
                min-height: 38px;
            }

            .round-title {
                font-size: 1rem;
            }

            /* Match spacing for tablet */
            .bracket-round.round-1 .matches-wrapper {
                gap: 35px;
            }

            .bracket-round.round-2 .matches-wrapper {
                gap: 125px;
            }

            .bracket-round.round-3 .matches-wrapper {
                gap: 305px;
            }

            .bracket-round.round-4 .matches-wrapper {
                gap: 665px;
            }
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

        // Draw bracket connectors - IMPROVED ALGORITHM
        function drawConnectors() {
            const container = document.querySelector('.bracket-container');
            if (!container) return;

            const existingSvg = container.querySelector('.bracket-connectors');
            if (existingSvg) existingSvg.remove();

            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.classList.add('bracket-connectors');

            svg.setAttribute('width', '100%');
            svg.setAttribute('height', '100%');
            svg.style.position = 'absolute';
            svg.style.top = '0';
            svg.style.left = '0';

            container.style.position = 'relative';
            container.appendChild(svg);

            const rounds = container.querySelectorAll('.bracket-round');

            rounds.forEach((currentRound, roundIndex) => {
                // Skip if this is the last round OR if it's the champion column
                if (roundIndex === rounds.length - 1 || roundIndex === rounds.length - 2) return;

                const nextRound = rounds[roundIndex + 1];
                const currentMatches = currentRound.querySelectorAll('.bracket-match');
                const nextMatches = nextRound.querySelectorAll('.bracket-match');

                // Group matches in pairs
                for (let i = 0; i < currentMatches.length; i += 2) {
                    const match1 = currentMatches[i];
                    const match2 = currentMatches[i + 1];

                    if (!match1) continue;

                    const containerRect = container.getBoundingClientRect();

                    // Get center points of matches
                    const match1Rect = match1.getBoundingClientRect();
                    const match1CenterY = match1Rect.top - containerRect.top + (match1Rect.height / 2);
                    const match1RightX = match1Rect.right - containerRect.left;

                    let match2CenterY = match1CenterY;
                    if (match2) {
                        const match2Rect = match2.getBoundingClientRect();
                        match2CenterY = match2Rect.top - containerRect.top + (match2Rect.height / 2);
                    }

                    // Calculate middle point between two matches
                    const midY = (match1CenterY + match2CenterY) / 2;

                    // Get next match (the one these two feed into)
                    const nextMatchIndex = Math.floor(i / 2);
                    const nextMatch = nextMatches[nextMatchIndex];

                    if (nextMatch) {
                        const nextRect = nextMatch.getBoundingClientRect();
                        const nextCenterY = nextRect.top - containerRect.top + (nextRect.height / 2);
                        const nextLeftX = nextRect.left - containerRect.left;

                        // Calculate connection points
                        const horizontalExtend = 30;
                        const midX = match1RightX + horizontalExtend + ((nextLeftX - match1RightX -
                            horizontalExtend) / 2);

                        // Draw path for match 1
                        const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        const pathData1 = `
                            M ${match1RightX} ${match1CenterY} 
                            L ${match1RightX + horizontalExtend} ${match1CenterY}
                            L ${midX} ${match1CenterY}
                            L ${midX} ${midY}
                        `;
                        path1.setAttribute('d', pathData1);
                        path1.setAttribute('class', 'connector-path');
                        svg.appendChild(path1);

                        // Draw path for match 2 (if exists)
                        if (match2) {
                            const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                            const pathData2 = `
                                M ${match1RightX} ${match2CenterY}
                                L ${match1RightX + horizontalExtend} ${match2CenterY}
                                L ${midX} ${match2CenterY}
                                L ${midX} ${midY}
                            `;
                            path2.setAttribute('d', pathData2);
                            path2.setAttribute('class', 'connector-path');
                            svg.appendChild(path2);
                        }

                        // Draw connecting line to next match
                        const pathToNext = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        const pathDataNext = `
                            M ${midX} ${midY}
                            L ${nextLeftX} ${midY}
                            L ${nextLeftX} ${nextCenterY}
                        `;
                        pathToNext.setAttribute('d', pathDataNext);
                        pathToNext.setAttribute('class', 'connector-path');
                        svg.appendChild(pathToNext);
                    }
                }
            });

            const viewBoxWidth = container.scrollWidth;
            const viewBoxHeight = container.scrollHeight;
            svg.setAttribute('viewBox', `0 0 ${viewBoxWidth} ${viewBoxHeight}`);
            svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        }

        // Redraw on various events
        function scheduleRedraw() {
            setTimeout(drawConnectors, 100);
            setTimeout(drawConnectors, 300);
            setTimeout(drawConnectors, 500);
        }

        window.addEventListener('load', scheduleRedraw);
        window.addEventListener('resize', drawConnectors);
        document.addEventListener('DOMContentLoaded', scheduleRedraw);
    </script>

    @php
        $bracketsByRound = $brackets->groupBy('round');
        $maxRound = $brackets->max('round');
        $round1Count = $bracketsByRound->get(1, collect())->count();

        function getRoundLabel($round, $maxRound)
        {
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
    @endphp

    <div class="min-h-screen bg-neutral-900 text-white pb-20">
        <!-- Header - Responsive -->
        <div class="mb-6 sm:mb-8 bg-cover bg-center p-8 sm:p-16 lg:p-24"
            style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-xs sm:text-sm text-gray-400 mt-1 mb-2 sm:mb-0">
                <a href="{{ route('events.index') }}" class="hover:text-white">Home</a> /
                <a href="{{ route('events.show', $event) }}" class="hover:text-white">Event</a> /
                <span>Tournament Bracket</span>
            </p>
            <div class="flex items-center justify-between">
                <h2 class="text-xl sm:text-2xl lg:text-4xl font-bold uppercase text-white">
                    <span class="block sm:hidden">{{ $event->name }}</span>
                    <span class="hidden sm:block">{{ $event->name }} - Tournament Bracket</span>
                </h2>
            </div>
        </div>

        <!-- Bracket container -->
        <div class="px-2 sm:px-4 py-4 sm:py-8">
            <a href="{{ route('events.show', $event) }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-2 rounded-lg text-sm sm:text-base font-medium transition-colors ml-2 sm:ml-6 inline-block mb-4 sm:mb-6">
                <i class="fas fa-arrow-left mr-1 sm:mr-2"></i>
                <span class="hidden sm:inline">Back to Event</span>
                <span class="sm:hidden">Back</span>
            </a>

            @if ($round1Count == 0)
                <div
                    class="mt-6 sm:mt-8 ml-2 sm:ml-6 bg-yellow-500 text-yellow-900 px-3 sm:px-4 py-2 sm:py-3 rounded-lg inline-block text-sm">
                    <strong class="font-bold">Notice:</strong>
                    <span class="text-xs sm:text-sm">No bracket data available. Please generate the tournament tree
                        first.</span>
                </div>
            @else
                <!-- Scroll Hint for Mobile/Tablet -->
                <div class="lg:hidden mb-4 text-center ml-2 sm:ml-6">
                    <span
                        class="text-xs sm:text-sm text-gray-400 bg-neutral-800 px-3 py-1.5 rounded-full inline-flex items-center">
                        <i class="fas fa-arrows-h mr-2"></i>
                        <span>Scroll horizontal untuk melihat bracket lengkap</span>
                    </span>
                </div>

                <!-- Main Bracket -->
                <div class="overflow-x-auto overflow-y-visible pb-8">
                    <div class="bracket-container">
                        {{-- BRACKET ROUNDS --}}
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
                                    class="round-title {{ $round == 1 ? 'text-blue-400' : ($round == $maxRound ? 'text-orange-400' : ($round == $maxRound - 1 ? 'text-purple-400' : 'text-blue-400')) }}">
                                    {{ getRoundLabel($round, $maxRound) }}
                                </div>

                                <div class="matches-wrapper">
                                    @foreach ($matches as $matchIndex => $match)
                                        <div
                                            class="bracket-match {{ count($match->where('is_winner', true)) > 0 ? 'has-winner' : '' }}">
                                            @foreach ($match as $bracket)
                                                <div
                                                    class="bracket-player {{ $bracket->is_winner ? 'winner' : '' }} {{ $bracket->player_name === 'TBD' ? 'opacity-50' : '' }}">
                                                    <div class="flex items-center justify-between w-full">
                                                        <p
                                                            class="text-sm font-medium {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-300' }} {{ $bracket->player_name === 'TBD' ? 'text-gray-600 italic' : '' }} truncate pr-2">
                                                            {{ $bracket->player_name }}
                                                        </p>

                                                        @if ($bracket->is_winner && $bracket->player_name !== 'TBD')
                                                            <div class="ml-2 flex-shrink-0">
                                                                <i class="fas fa-check text-green-500 text-sm"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endfor

                        {{-- CHAMPION SECTION - SEPARATE COLUMN --}}
                        <div class="bracket-round" style="min-width: 280px;">
                            <div class="round-title text-yellow-400">
                                <i class="fas fa-trophy mr-2"></i>Champion
                            </div>

                            <div class="matches-wrapper">
                                @php
                                    $champion = $brackets->where('round', $maxRound)->where('is_winner', true)->first();
                                @endphp

                                @if ($champion && $champion->player_name !== 'TBD')
                                    <div class="relative">
                                        {{-- Glow effect --}}
                                        <div
                                            class="absolute inset-0 bg-gradient-radial from-yellow-500/20 to-transparent blur-xl">
                                        </div>

                                        {{-- Champion card --}}
                                        <div
                                            class="relative bg-gradient-to-br from-yellow-600 via-yellow-500 to-yellow-600 rounded-xl px-4 sm:px-6 py-6 sm:py-8 shadow-2xl transform hover:scale-105 transition-transform duration-300">
                                            <div class="text-center">
                                                <i
                                                    class="fas fa-crown text-3xl sm:text-4xl text-yellow-200 mb-3 sm:mb-4"></i>
                                                <p class="text-lg sm:text-xl font-bold text-white mb-2">
                                                    {{ $champion->player_name }}</p>
                                                <p class="text-xs sm:text-sm text-yellow-100">Tournament Winner</p>
                                            </div>

                                            {{-- Decorative elements --}}
                                            <div
                                                class="absolute -top-3 -left-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50">
                                            </div>
                                            <div
                                                class="absolute -bottom-3 -right-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-neutral-800 rounded-xl px-4 sm:px-6 py-6 sm:py-8 text-center">
                                        <i class="fas fa-hourglass-half text-2xl sm:text-3xl text-gray-500 mb-3"></i>
                                        <p class="text-sm sm:text-base text-gray-400 font-medium">To Be Determined</p>
                                        <p class="text-xs text-gray-600 mt-2">Winner will be announced after final match
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Section - Responsive Grid -->
                <div class="mt-8 sm:mt-12 mx-2 sm:mx-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <!-- Legend -->
                    <div class="bg-neutral-800 rounded-xl p-4 sm:p-6">
                        <h3 class="font-bold text-base sm:text-lg mb-3 sm:mb-4 text-blue-400">
                            <i class="fas fa-info-circle mr-2"></i>Status
                        </h3>
                        <div class="space-y-2 sm:space-y-3">
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-neutral-700 rounded mr-2 sm:mr-3 flex items-center justify-center border-2 border-neutral-600 flex-shrink-0">
                                    <i class="fas fa-user text-gray-500 text-xs sm:text-sm"></i>
                                </div>
                                <span class="text-xs sm:text-sm">Active Player</span>
                            </div>
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-neutral-700 rounded mr-2 sm:mr-3 flex items-center justify-center border-2 border-green-500 flex-shrink-0">
                                    <i class="fas fa-check text-green-500 text-xs sm:text-sm"></i>
                                </div>
                                <span class="text-xs sm:text-sm">Winner</span>
                            </div>
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-yellow-600 to-yellow-500 rounded mr-2 sm:mr-3 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-trophy text-yellow-200 text-xs sm:text-sm"></i>
                                </div>
                                <span class="text-xs sm:text-sm">Champion</span>
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

                    <div class="bg-neutral-800 rounded-xl p-4 sm:p-6">
                        <h3 class="font-bold text-base sm:text-lg mb-3 sm:mb-4 text-blue-400">
                            <i class="fas fa-chart-bar mr-2"></i>Progress
                        </h3>
                        <div class="space-y-3 sm:space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xs sm:text-sm text-gray-400">Total Players</span>
                                <span class="text-xl sm:text-2xl font-bold text-blue-400">{{ $totalPlayers }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs sm:text-sm text-gray-400">Completed</span>
                                <span class="text-xl sm:text-2xl font-bold text-green-400">{{ $totalWinners }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs sm:text-sm text-gray-400">Remaining</span>
                                <span
                                    class="text-xl sm:text-2xl font-bold text-yellow-400">{{ $totalMatches - $totalWinners }}</span>
                            </div>
                            <div class="pt-2">
                                <div class="flex justify-between text-xs text-gray-400 mb-1">
                                    <span>Progress</span>
                                    <span class="font-bold text-purple-400">{{ $progress }}%</span>
                                </div>
                                <div class="bg-neutral-700 rounded-full h-2 sm:h-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 via-green-500 to-purple-500 h-full transition-all duration-500"
                                        style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Round Details -->
                    <div class="bg-neutral-800 rounded-xl p-4 sm:p-6 md:col-span-2 lg:col-span-1">
                        <h3 class="font-bold text-base sm:text-lg mb-3 sm:mb-4 text-blue-400">
                            <i class="fas fa-layer-group mr-2"></i>Rounds
                        </h3>
                        <div class="space-y-2">
                            @foreach ($bracketsByRound as $round => $roundBrackets)
                                @php
                                    $winnersInRound = $roundBrackets->where('is_winner', true)->count();
                                    $matchesInRound = $roundBrackets->count() / 2;
                                @endphp
                                <div class="flex items-center justify-between py-2 border-b border-neutral-700">
                                    <span
                                        class="text-xs sm:text-sm font-medium">{{ getRoundLabel($round, $maxRound) }}</span>
                                    <div class="flex items-center gap-2 sm:gap-3 text-xs">
                                        <span class="text-gray-400 hidden sm:inline">{{ $matchesInRound }}
                                            {{ $matchesInRound > 1 ? 'Matches' : 'Match' }}</span>
                                        <span
                                            class="px-1.5 sm:px-2 py-0.5 sm:py-1 rounded text-xs {{ $winnersInRound == $matchesInRound ? 'bg-green-600' : 'bg-gray-700' }}">
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
