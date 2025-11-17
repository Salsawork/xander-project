@extends('app')
@section('title', $event->name . ' - Tournament Bracket - Xander Billiard')

@section('content')
    <div id="antiBounceBg" aria-hidden="true"></div>

    <style>
        :root {
            --match-height: 88px;
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

        /* ====================================
       BRACKET CONTAINER - FLEXBOX LAYOUT
       ==================================== */
        .bracket-container {
            display: flex;
            flex-direction: column;
            gap: 0;
            padding: 40px 20px;
            min-width: max-content;
            position: relative;
        }

        /* Upper Bracket Wrapper */
        .upper-bracket-wrapper {
            display: flex;
            gap: 80px;
            align-items: center;
        }

        /* Lower Bracket Wrapper */
        .lower-bracket-wrapper {
            display: flex;
            gap: 80px;
            align-items: center;
            margin-top: 100px;
            padding-top: 40px;
            position: relative;
        }

        /* Finals Wrapper Container - Scrollable */
        .finals-wrapper-container {
            width: 100%;
            max-width: 100%;
            position: relative;
            overflow-x: visible;
            overflow-y: visible;
            margin-top: -60px;
        }

        /* Finals Wrapper - Force content to extend beyond viewport */
        .finals-wrapper {
            display: flex;
            gap: 80px;
            align-items: center;
            min-width: max-content;
        }

        /* Spacer to force Grand Final & Champion off-screen */
        .finals-spacer {
            min-width: 100vw;
            width: 100vw;
            flex-shrink: 0;
        }

        /* TAMBAHAN: Extra column sebelum Grand Final */
        .finals-extra-column {
            min-width: 300px;
            width: 300px;
            flex-shrink: 0;
        }

        /* Champion Wrapper Container - Scrollable */
        .champion-wrapper-container {
            width: 100%;
            max-width: 100%;
            overflow-x: visible;
            overflow-y: visible;
        }

        .champion-wrapper-inner {
            display: flex;
            min-width: max-content;
        }

        /* Spacer for single elimination champion */
        .champion-spacer {
            min-width: 100vw;
            width: 100vw;
            flex-shrink: 0;
        }

        /* ====================================
       BRACKET ROUND
       ==================================== */
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
            background: #0a0a0a;
            border-radius: 8px;
        }

        /* ====================================
       MATCH BOX
       ==================================== */
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
            transition: all 0.2s ease;
        }

        .bracket-match.has-winner {
            border-color: #22c55e;
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.2);
        }

        /* Player inside match */
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

        .bracket-player>div {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex: 1;
        }

        /* Match spacing wrapper */
        .matches-wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* ====================================
       SINGLE ELIMINATION SPACING
       ==================================== */
        .bracket-round.single-round-1 .matches-wrapper {
            gap: var(--base-gap);
        }

        .bracket-round.single-round-2 .matches-wrapper {
            gap: calc(var(--match-height) + var(--base-gap) * 2);
        }

        .bracket-round.single-round-3 .matches-wrapper {
            gap: calc(var(--match-height) * 3 + var(--base-gap) * 4);
        }

        .bracket-round.single-round-4 .matches-wrapper {
            gap: calc(var(--match-height) * 7 + var(--base-gap) * 8);
        }

        .bracket-round.single-round-5 .matches-wrapper {
            gap: calc(var(--match-height) * 15 + var(--base-gap) * 16);
        }

        /* ====================================
       DOUBLE ELIMINATION - UPPER BRACKET SPACING
       ==================================== */
        .bracket-round.upper-round-1 .matches-wrapper {
            gap: var(--base-gap);
        }

        .bracket-round.upper-round-2 .matches-wrapper {
            gap: calc(var(--match-height) + var(--base-gap) * 2);
        }

        .bracket-round.upper-round-3 .matches-wrapper {
            gap: calc(var(--match-height) * 3 + var(--base-gap) * 4);
        }

        .bracket-round.upper-round-4 .matches-wrapper {
            gap: calc(var(--match-height) * 7 + var(--base-gap) * 8);
        }

        .bracket-round.upper-round-5 .matches-wrapper {
            gap: calc(var(--match-height) * 15 + var(--base-gap) * 16);
        }

        /* ====================================
       DOUBLE ELIMINATION - LOWER BRACKET SPACING
       ==================================== */
        .bracket-round.lower-round-1 .matches-wrapper,
        .bracket-round.lower-round-2 .matches-wrapper {
            gap: var(--base-gap);
        }

        .bracket-round.lower-round-3 .matches-wrapper,
        .bracket-round.lower-round-4 .matches-wrapper {
            gap: calc(var(--match-height) + var(--base-gap) * 2);
        }

        .bracket-round.lower-round-5 .matches-wrapper,
        .bracket-round.lower-round-6 .matches-wrapper {
            gap: calc(var(--match-height) * 3 + var(--base-gap) * 4);
        }

        /* ====================================
       GRAND FINAL STYLING
       ==================================== */
        .grand-final-round {
            min-width: 300px;
        }

        .grand-final-match {
            border: 2px solid rgba(234, 179, 8, 0.6);
            background: linear-gradient(135deg, rgba(234, 179, 8, 0.15), rgba(234, 179, 8, 0.05));
            box-shadow: 0 4px 20px rgba(234, 179, 8, 0.3);
        }

        .grand-final-match .bracket-player {
            background: rgba(30, 30, 30, 0.8);
        }

        /* ====================================
       SVG CONNECTORS
       ==================================== */
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

        .connector-path.upper-bracket {
            stroke: #60a5fa;
        }

        .connector-path.lower-bracket {
            stroke: #fca5a5;
        }

        .connector-path.grand-final {
            stroke: #fde68a;
            stroke-width: 3;
        }

        /* ====================================
       TOURNAMENT TYPE BADGE
       ==================================== */
        .tournament-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .tournament-type-badge.single-elimination {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .tournament-type-badge.double-elimination {
            background: rgba(234, 179, 8, 0.15);
            border: 1px solid rgba(234, 179, 8, 0.3);
            color: #fde68a;
        }

        /* ====================================
       CUSTOM SCROLLBAR
       ==================================== */
        .finals-wrapper-container::-webkit-scrollbar,
        .champion-wrapper-container::-webkit-scrollbar,
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .finals-wrapper-container::-webkit-scrollbar-track,
        .champion-wrapper-container::-webkit-scrollbar-track,
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #1a1a1a;
            border-radius: 4px;
        }

        .finals-wrapper-container::-webkit-scrollbar-thumb,
        .champion-wrapper-container::-webkit-scrollbar-thumb,
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #404855;
            border-radius: 4px;
        }

        .finals-wrapper-container::-webkit-scrollbar-thumb:hover,
        .champion-wrapper-container::-webkit-scrollbar-thumb:hover,
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #4b5563;
        }

        /* ====================================
       RESPONSIVE - ENHANCED
       ==================================== */
        @media (max-width: 640px) {
            :root {
                --match-height: 70px;
                --base-gap: 15px;
            }

            .bracket-container {
                padding: 20px 10px;
            }

            .upper-bracket-wrapper,
            .lower-bracket-wrapper,
            .finals-wrapper {
                gap: 50px;
            }

            .finals-spacer {
                min-width: 70vw;
            }

            .finals-extra-column {
                min-width: 150px;
                width: 150px;
            }

            .champion-spacer {
                min-width: 70vw;
            }

            .lower-bracket-wrapper {
                margin-top: 60px;
                padding-top: 20px;
            }

            .bracket-round {
                min-width: 160px;
            }

            .round-title {
                font-size: 0.85rem;
                margin-bottom: 1rem;
                padding: 0.4rem 0;
            }

            .bracket-match {
                font-size: 0.75rem;
                border-width: 1px;
            }

            .bracket-player {
                padding: 8px 10px;
                min-height: 32px;
            }

            .bracket-player p {
                font-size: 0.75rem;
            }

            .bracket-player .flex-shrink-0 i {
                font-size: 0.7rem;
            }

            .grand-final-round {
                min-width: 240px;
            }

            .grand-final-match {
                border-width: 2px;
            }

            .connector-path {
                stroke-width: 1.5px;
            }

            .connector-path.grand-final {
                stroke-width: 2px;
            }

            /* Mobile specific adjustments */
            .bracket-player .text-xs {
                font-size: 0.65rem;
            }

            /* Tighter spacing for mobile */
            .bracket-round.single-round-1 .matches-wrapper,
            .bracket-round.upper-round-1 .matches-wrapper,
            .bracket-round.lower-round-1 .matches-wrapper,
            .bracket-round.lower-round-2 .matches-wrapper {
                gap: var(--base-gap);
            }

            .bracket-round.single-round-2 .matches-wrapper,
            .bracket-round.upper-round-2 .matches-wrapper {
                gap: calc(var(--match-height) + var(--base-gap) * 1.5);
            }

            .bracket-round.single-round-3 .matches-wrapper,
            .bracket-round.upper-round-3 .matches-wrapper,
            .bracket-round.lower-round-3 .matches-wrapper,
            .bracket-round.lower-round-4 .matches-wrapper {
                gap: calc(var(--match-height) * 2.5 + var(--base-gap) * 3);
            }

            .bracket-round.single-round-4 .matches-wrapper,
            .bracket-round.upper-round-4 .matches-wrapper {
                gap: calc(var(--match-height) * 5 + var(--base-gap) * 6);
            }

            .bracket-round.single-round-5 .matches-wrapper,
            .bracket-round.upper-round-5 .matches-wrapper,
            .bracket-round.lower-round-5 .matches-wrapper,
            .bracket-round.lower-round-6 .matches-wrapper {
                gap: calc(var(--match-height) * 10 + var(--base-gap) * 12);
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            :root {
                --match-height: 80px;
                --base-gap: 18px;
            }

            .bracket-container {
                padding: 30px 15px;
            }

            .upper-bracket-wrapper,
            .lower-bracket-wrapper,
            .finals-wrapper {
                gap: 65px;
            }

            .finals-spacer {
                min-width: 85vw;
            }

            .finals-extra-column {
                min-width: 220px;
                width: 220px;
            }

            .champion-spacer {
                min-width: 85vw;
            }

            .lower-bracket-wrapper {
                margin-top: 80px;
                padding-top: 30px;
            }

            .bracket-round {
                min-width: 185px;
            }

            .round-title {
                font-size: 0.95rem;
                margin-bottom: 1.5rem;
            }

            .bracket-match {
                font-size: 0.85rem;
            }

            .bracket-player {
                padding: 10px 12px;
                min-height: 36px;
            }

            .bracket-player p {
                font-size: 0.85rem;
            }

            .grand-final-round {
                min-width: 270px;
            }

            .connector-path {
                stroke-width: 1.75px;
            }

            .connector-path.grand-final {
                stroke-width: 2.5px;
            }

            /* Tablet specific spacing */
            .bracket-round.single-round-1 .matches-wrapper,
            .bracket-round.upper-round-1 .matches-wrapper,
            .bracket-round.lower-round-1 .matches-wrapper,
            .bracket-round.lower-round-2 .matches-wrapper {
                gap: var(--base-gap);
            }

            .bracket-round.single-round-2 .matches-wrapper,
            .bracket-round.upper-round-2 .matches-wrapper {
                gap: calc(var(--match-height) + var(--base-gap) * 1.8);
            }

            .bracket-round.single-round-3 .matches-wrapper,
            .bracket-round.upper-round-3 .matches-wrapper,
            .bracket-round.lower-round-3 .matches-wrapper,
            .bracket-round.lower-round-4 .matches-wrapper {
                gap: calc(var(--match-height) * 2.8 + var(--base-gap) * 3.5);
            }

            .bracket-round.single-round-4 .matches-wrapper,
            .bracket-round.upper-round-4 .matches-wrapper {
                gap: calc(var(--match-height) * 6 + var(--base-gap) * 7);
            }

            .bracket-round.single-round-5 .matches-wrapper,
            .bracket-round.upper-round-5 .matches-wrapper,
            .bracket-round.lower-round-5 .matches-wrapper,
            .bracket-round.lower-round-6 .matches-wrapper {
                gap: calc(var(--match-height) * 12 + var(--base-gap) * 14);
            }
        }

        @media (min-width: 1025px) {
            .bracket-container {
                padding: 40px 20px;
            }

            /* Desktop optimal spacing */
            .upper-bracket-wrapper,
            .lower-bracket-wrapper,
            .finals-wrapper {
                gap: 80px;
            }

            .finals-extra-column {
                min-width: 300px;
                width: 300px;
            }

            .grand-final-round {
                min-width: 300px;
            }
        }

        /* Landscape orientation adjustments */
        @media (max-width: 1024px) and (orientation: landscape) {
            .bracket-container {
                padding: 20px 15px;
            }

            .lower-bracket-wrapper {
                margin-top: 60px;
                padding-top: 25px;
            }

            .bracket-round {
                min-width: 170px;
            }

            .bracket-match {
                font-size: 0.8rem;
            }

            .bracket-player {
                padding: 9px 11px;
                min-height: 34px;
            }
        }

        /* Extra small devices (phones in portrait) */
        @media (max-width: 375px) {
            :root {
                --match-height: 65px;
                --base-gap: 12px;
            }

            .bracket-container {
                padding: 15px 8px;
            }

            .bracket-round {
                min-width: 145px;
            }

            .round-title {
                font-size: 0.8rem;
                margin-bottom: 0.8rem;
            }

            .bracket-player {
                padding: 7px 9px;
                min-height: 30px;
            }

            .bracket-player p {
                font-size: 0.7rem;
            }

            .upper-bracket-wrapper,
            .lower-bracket-wrapper,
            .finals-wrapper {
                gap: 40px;
            }

            .finals-extra-column {
                min-width: 120px;
                width: 120px;
            }

            .grand-final-round {
                min-width: 220px;
            }
        }

        /* Large desktop screens */
        @media (min-width: 1440px) {
            .bracket-container {
                padding: 50px 30px;
            }

            .bracket-round {
                min-width: 240px;
            }

            .round-title {
                font-size: 1.2rem;
                margin-bottom: 2.5rem;
            }

            .bracket-match {
                font-size: 1rem;
            }

            .bracket-player {
                padding: 14px 16px;
                min-height: 44px;
            }

            .bracket-player p {
                font-size: 1rem;
            }

            .upper-bracket-wrapper,
            .lower-bracket-wrapper,
            .finals-wrapper {
                gap: 100px;
            }

            .finals-extra-column {
                min-width: 350px;
                width: 350px;
            }

            .grand-final-round {
                min-width: 350px;
            }

            .connector-path {
                stroke-width: 2.5px;
            }

            .connector-path.grand-final {
                stroke-width: 3.5px;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .bracket-player {
                min-height: 40px; /* Larger touch target */
            }

            .bracket-match {
                margin-bottom: 2px; /* Extra spacing for touch */
            }

            /* Disable hover effects on touch devices */
            .bracket-player:hover {
                background: #0f0f0f;
            }

            .bracket-match {
                transition: none;
            }
        }

        /* High DPI screens */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .connector-path {
                stroke-width: 2.5px;
            }

            .connector-path.grand-final {
                stroke-width: 3.5px;
            }

            .bracket-match {
                border-width: 1.5px;
            }
        }

        /* Print styles */
        @media print {
            .bracket-container {
                padding: 20px;
                overflow: visible !important;
            }

            .finals-wrapper-container,
            .champion-wrapper-container,
            .overflow-x-auto {
                overflow: visible !important;
            }

            .bracket-connectors {
                display: block !important;
            }

            .connector-path {
                stroke-width: 1px;
            }

            /* Hide scroll hints on print */
            .lg\:hidden.mb-4.text-center {
                display: none !important;
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

        // Draw bracket connectors
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

            const isDoubleElimination = container.classList.contains('double-elimination');

            if (isDoubleElimination) {
                drawDoubleEliminationConnectors(svg, container);
            } else {
                drawSingleEliminationConnectors(svg, container);
            }

            const viewBoxWidth = container.scrollWidth;
            const viewBoxHeight = container.scrollHeight;
            svg.setAttribute('viewBox', `0 0 ${viewBoxWidth} ${viewBoxHeight}`);
            svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        }

        // Single Elimination Connectors
        function drawSingleEliminationConnectors(svg, container) {
            const rounds = container.querySelectorAll('.bracket-round.single-round');

            Array.from(rounds).forEach((currentRound, roundIndex) => {
                if (roundIndex === rounds.length - 1) return;

                const nextRound = rounds[roundIndex + 1];
                if (!nextRound) return;

                const currentMatches = currentRound.querySelectorAll('.bracket-match');
                const nextMatches = nextRound.querySelectorAll('.bracket-match');

                drawBracketConnectors(svg, currentMatches, nextMatches, container, 'connector-path');
            });
        }

        // Double Elimination Connectors
        function drawDoubleEliminationConnectors(svg, container) {
            // Draw Upper Bracket connectors
            const upperWrapper = container.querySelector('.upper-bracket-wrapper');
            if (upperWrapper) {
                const upperRounds = Array.from(upperWrapper.querySelectorAll('.bracket-round'));

                upperRounds.forEach((currentRound, roundIndex) => {
                    if (roundIndex === upperRounds.length - 1) return;

                    const nextRound = upperRounds[roundIndex + 1];
                    if (!nextRound) return;

                    const currentMatches = currentRound.querySelectorAll('.bracket-match');
                    const nextMatches = nextRound.querySelectorAll('.bracket-match');

                    drawBracketConnectors(svg, currentMatches, nextMatches, container, 'upper-bracket');
                });
            }

            // Draw Lower Bracket connectors
            const lowerWrapper = container.querySelector('.lower-bracket-wrapper');
            if (lowerWrapper) {
                const lowerRounds = Array.from(lowerWrapper.querySelectorAll('.bracket-round'));

                lowerRounds.forEach((currentRound, roundIndex) => {
                    if (roundIndex === lowerRounds.length - 1) return;

                    const nextRound = lowerRounds[roundIndex + 1];
                    if (!nextRound) return;

                    const currentMatches = currentRound.querySelectorAll('.bracket-match');
                    const nextMatches = nextRound.querySelectorAll('.bracket-match');

                    drawBracketConnectors(svg, currentMatches, nextMatches, container, 'lower-bracket');
                });
            }

            // Draw Grand Final connectors - DENGAN EXTRA COLUMN
            const finalsWrapper = container.querySelector('.finals-wrapper');
            if (finalsWrapper && upperWrapper && lowerWrapper) {
                const upperFinalRound = upperWrapper.querySelector('.bracket-round:last-child');
                const lowerFinalRound = lowerWrapper.querySelector('.bracket-round:last-child');
                const grandFinalRound = finalsWrapper.querySelector('.grand-final-round');

                if (upperFinalRound && lowerFinalRound && grandFinalRound) {
                    const upperFinalMatch = upperFinalRound.querySelector('.bracket-match');
                    const lowerFinalMatch = lowerFinalRound.querySelector('.bracket-match');
                    const grandFinalMatch = grandFinalRound.querySelector('.bracket-match');

                    if (upperFinalMatch && lowerFinalMatch && grandFinalMatch) {
                        drawGrandFinalConnectors(svg, upperFinalMatch, lowerFinalMatch, grandFinalMatch, container);
                    }
                }
            }
        }

        // Helper: Draw connectors between brackets
        function drawBracketConnectors(svg, currentMatches, nextMatches, container, className) {
            for (let i = 0; i < currentMatches.length; i += 2) {
                const match1 = currentMatches[i];
                const match2 = currentMatches[i + 1];
                if (!match1) continue;

                const containerRect = container.getBoundingClientRect();
                const match1Rect = match1.getBoundingClientRect();
                const match1CenterY = match1Rect.top - containerRect.top + (match1Rect.height / 2);
                const match1RightX = match1Rect.right - containerRect.left;

                let match2CenterY = match1CenterY;
                if (match2) {
                    const match2Rect = match2.getBoundingClientRect();
                    match2CenterY = match2Rect.top - containerRect.top + (match2Rect.height / 2);
                }

                const midY = (match1CenterY + match2CenterY) / 2;
                const nextMatchIndex = Math.floor(i / 2);
                const nextMatch = nextMatches[nextMatchIndex];

                if (nextMatch) {
                    const nextRect = nextMatch.getBoundingClientRect();
                    const nextCenterY = nextRect.top - containerRect.top + (nextRect.height / 2);
                    const nextLeftX = nextRect.left - containerRect.left;

                    const horizontalExtend = 30;
                    const midX = match1RightX + horizontalExtend + ((nextLeftX - match1RightX - horizontalExtend) / 2);

                    // Path for match 1
                    const path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    const pathData1 =
                        `M ${match1RightX} ${match1CenterY} L ${match1RightX + horizontalExtend} ${match1CenterY} L ${midX} ${match1CenterY} L ${midX} ${midY}`;
                    path1.setAttribute('d', pathData1);
                    path1.setAttribute('class', `connector-path ${className}`);
                    svg.appendChild(path1);

                    // Path for match 2
                    if (match2) {
                        const path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        const pathData2 =
                            `M ${match1RightX} ${match2CenterY} L ${match1RightX + horizontalExtend} ${match2CenterY} L ${midX} ${match2CenterY} L ${midX} ${midY}`;
                        path2.setAttribute('d', pathData2);
                        path2.setAttribute('class', `connector-path ${className}`);
                        svg.appendChild(path2);
                    }

                    // Path to next match
                    const pathToNext = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                    const pathDataNext = `M ${midX} ${midY} L ${nextLeftX} ${midY} L ${nextLeftX} ${nextCenterY}`;
                    pathToNext.setAttribute('d', pathDataNext);
                    pathToNext.setAttribute('class', `connector-path ${className}`);
                    svg.appendChild(pathToNext);
                }
            }
        }

        // Helper: Draw Grand Final connectors - STRAIGHT & SIMPLE
        function drawGrandFinalConnectors(svg, upperMatch, lowerMatch, grandMatch, container) {
            const containerRect = container.getBoundingClientRect();

            const upperRect = upperMatch.getBoundingClientRect();
            const upperCenterY = upperRect.top - containerRect.top + (upperRect.height / 2);
            const upperRightX = upperRect.right - containerRect.left;

            const lowerRect = lowerMatch.getBoundingClientRect();
            const lowerCenterY = lowerRect.top - containerRect.top + (lowerRect.height / 2);
            const lowerRightX = lowerRect.right - containerRect.left;

            const grandRect = grandMatch.getBoundingClientRect();
            const grandCenterY = grandRect.top - containerRect.top + (grandRect.height / 2);
            const grandLeftX = grandRect.left - containerRect.left;

            // Titik pertemuan di tengah (aligned dengan Grand Final Y position)
            const meetingX = Math.max(upperRightX, lowerRightX) + 40;

            // UPPER PATH - Langsung horizontal ke kiri Grand Final
            const upperPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            const upperPathData = `M ${upperRightX} ${upperCenterY} 
                                  L ${meetingX} ${upperCenterY} 
                                  L ${meetingX} ${grandCenterY} 
                                  L ${grandLeftX} ${grandCenterY}`;
            upperPath.setAttribute('d', upperPathData);
            upperPath.setAttribute('class', 'connector-path grand-final');
            svg.appendChild(upperPath);

            // LOWER PATH - Langsung horizontal ke titik pertemuan
            const lowerPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            const lowerPathData = `M ${lowerRightX} ${lowerCenterY} 
                                  L ${meetingX} ${lowerCenterY} 
                                  L ${meetingX} ${grandCenterY}`;
            lowerPath.setAttribute('d', lowerPathData);
            lowerPath.setAttribute('class', 'connector-path grand-final');
            svg.appendChild(lowerPath);
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

        $isDoubleElimination = $event->finals_format === 'Double Elimination';

        if ($isDoubleElimination && $round1Count > 0) {
            $numFighters = $round1Count;
            $numRounds = intval(log($numFighters, 2));
            $upperBracketEnd = $numRounds + 1;
            $lowerBracketStart = $upperBracketEnd + 1;
            $grandFinalRound = $maxRound;
        }
    @endphp

    <div class="min-h-screen bg-neutral-900 text-white pb-20">
        <!-- Header -->
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
        <div class="px-2 sm:px-4 py-4 sm:py-8 overflow-x-auto overflow-y-visible">
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
                <!-- Tournament Type Badge -->
                <div class="ml-2 sm:ml-6 mb-4 sm:mb-6">
                    <span
                        class="tournament-type-badge {{ $isDoubleElimination ? 'double-elimination' : 'single-elimination' }}">
                        @if ($isDoubleElimination)
                            <i class="fas fa-trophy"></i>
                            <span>Double Elimination</span>
                        @else
                            <i class="fas fa-bolt"></i>
                            <span>Single Elimination</span>
                        @endif
                    </span>
                </div>

                <!-- Scroll Hint -->
                <div class="lg:hidden mb-4 text-center ml-2 sm:ml-6">
                    <span
                        class="text-xs sm:text-sm text-gray-400 bg-neutral-800 px-3 py-1.5 rounded-full inline-flex items-center">
                        <i class="fas fa-arrows-h mr-2"></i>
                        <span>Scroll horizontal untuk melihat bracket lengkap</span>
                    </span>
                </div>

                <!-- Main Bracket -->
                <div class="overflow-x-auto overflow-y-visible pb-8">
                    @if ($isDoubleElimination)
                        {{-- DOUBLE ELIMINATION LAYOUT --}}
                        <div class="bracket-container double-elimination">

                            {{-- UPPER BRACKET WRAPPER --}}
                            <div class="upper-bracket-wrapper">
                                @for ($round = 1; $round <= $upperBracketEnd; $round++)
                                    @php
                                        $roundBrackets = $bracketsByRound
                                            ->get($round, collect())
                                            ->sortBy('position')
                                            ->values();
                                        if ($roundBrackets->isEmpty()) {
                                            continue;
                                        }

                                        $matches = $roundBrackets->chunk(2);

                                        if ($round == 1) {
                                            $roundLabel = 'Round 1';
                                            $colorClass = 'text-blue-400';
                                        } elseif ($round == $upperBracketEnd) {
                                            $roundLabel = 'Upper Final';
                                            $colorClass = 'text-purple-400';
                                        } elseif ($round == $upperBracketEnd - 1) {
                                            $roundLabel = 'Upper Semi';
                                            $colorClass = 'text-blue-400';
                                        } else {
                                            $roundLabel = 'Upper R' . $round;
                                            $colorClass = 'text-blue-400';
                                        }
                                    @endphp

                                    <div class="bracket-round upper-round upper-round-{{ $round }}">
                                        <div class="round-title {{ $colorClass }}">
                                            {{ $roundLabel }}
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
                            </div>

                            {{-- FINALS WRAPPER - DENGAN EXTRA COLUMN --}}
                            <div class="finals-wrapper-container overflow-x-auto overflow-y-visible ml-4 sm:ml-6">
                                <div class="finals-wrapper">
                                    {{-- Spacer awal --}}
                                    <div class="finals-spacer"></div>

                                    {{-- EXTRA COLUMN: Kolom kosong 300px sebelum Grand Final --}}
                                    <div class="finals-extra-column"></div>

                                    {{-- GRAND FINAL --}}
                                    <div class="bracket-round grand-final-round">
                                        <div class="round-title text-yellow-400">
                                            <i class="fas fa-trophy mr-2"></i>Grand Final
                                        </div>

                                        <div class="matches-wrapper">
                                            @php
                                                $grandFinalBrackets = $bracketsByRound
                                                    ->get($grandFinalRound, collect())
                                                    ->sortBy('position')
                                                    ->values();
                                                $champion = $grandFinalBrackets->where('is_winner', true)->first();
                                            @endphp

                                            @if ($grandFinalBrackets->count() >= 2)
                                                <div
                                                    class="bracket-match grand-final-match {{ $champion ? 'has-winner' : '' }}">
                                                    @foreach ($grandFinalBrackets->take(2) as $bracket)
                                                        <div
                                                            class="bracket-player {{ $bracket->is_winner ? 'winner' : '' }} {{ $bracket->player_name === 'TBD' ? 'opacity-50' : '' }}">
                                                            <div class="flex items-center justify-between w-full">
                                                                <div class="flex-1">
                                                                    <div class="text-xs text-gray-400 mb-1">
                                                                        {{ $loop->first ? 'Upper Winner' : 'Lower Winner' }}
                                                                    </div>
                                                                    <p
                                                                        class="text-sm font-medium {{ $bracket->is_winner ? 'text-white font-bold' : 'text-gray-300' }} {{ $bracket->player_name === 'TBD' ? 'text-gray-600 italic' : '' }} truncate pr-2">
                                                                        {{ $bracket->player_name }}
                                                                    </p>
                                                                </div>

                                                                @if ($bracket->is_winner && $bracket->player_name !== 'TBD')
                                                                    <div class="ml-2 flex-shrink-0">
                                                                        <i
                                                                            class="fas fa-trophy text-yellow-400 text-sm"></i>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div
                                                    class="bg-neutral-800 rounded-xl px-4 sm:px-6 py-6 sm:py-8 text-center">
                                                    <i
                                                        class="fas fa-hourglass-half text-2xl sm:text-3xl text-gray-500 mb-3"></i>
                                                    <p class="text-sm sm:text-base text-gray-400 font-medium">To Be
                                                        Determined</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- CHAMPION SECTION --}}
                                    <div class="bracket-round" style="min-width: 320px;">
                                        <div class="round-title text-yellow-400">
                                            <i class="fas fa-crown mr-2"></i>Champion
                                        </div>

                                        <div class="matches-wrapper">
                                            @if ($champion && $champion->player_name !== 'TBD')
                                                <div class="relative">
                                                    <div
                                                        class="absolute inset-0 bg-gradient-radial from-yellow-500/20 to-transparent blur-xl">
                                                    </div>
                                                    <div
                                                        class="relative bg-gradient-to-br from-yellow-600 via-yellow-500 to-yellow-600 rounded-xl px-4 sm:px-6 py-6 sm:py-8 shadow-2xl transform hover:scale-105 transition-transform duration-300">
                                                        <div class="text-center">
                                                            <i
                                                                class="fas fa-crown text-3xl sm:text-4xl text-yellow-200 mb-3 sm:mb-4"></i>
                                                            <p class="text-lg sm:text-xl font-bold text-white mb-2">
                                                                {{ $champion->player_name }}</p>
                                                            <p class="text-xs sm:text-sm text-yellow-100">Tournament Winner
                                                            </p>
                                                        </div>
                                                        <div
                                                            class="absolute -top-3 -left-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50">
                                                        </div>
                                                        <div
                                                            class="absolute -bottom-3 -right-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50">
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div
                                                    class="bg-neutral-800 rounded-xl px-4 sm:px-6 py-6 sm:py-8 text-center">
                                                    <i
                                                        class="fas fa-hourglass-half text-2xl sm:text-3xl text-gray-500 mb-3"></i>
                                                    <p class="text-sm sm:text-base text-gray-400 font-medium">To Be
                                                        Determined</p>
                                                    <p class="text-xs text-gray-600 mt-2">Winner will be announced after
                                                        final match</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- LOWER BRACKET WRAPPER --}}
                            <div class="lower-bracket-wrapper">
                                @for ($round = $lowerBracketStart; $round < $grandFinalRound; $round++)
                                    @php
                                        $roundBrackets = $bracketsByRound
                                            ->get($round, collect())
                                            ->sortBy('position')
                                            ->values();
                                        if ($roundBrackets->isEmpty()) {
                                            continue;
                                        }

                                        $matches = $roundBrackets->chunk(2);
                                        $matchCount = $matches->count();

                                        $lbIndex = $round - $lowerBracketStart;

                                        if ($matchCount >= 4) {
                                            $spacingClass = 'lower-round-' . min($lbIndex + 1, 2);
                                        } elseif ($matchCount == 2) {
                                            $spacingClass = 'lower-round-' . min($lbIndex + 1, 4);
                                        } else {
                                            $spacingClass = 'lower-round-' . min($lbIndex + 1, 6);
                                        }

                                        if ($round == $grandFinalRound - 1) {
                                            $roundLabel = 'Lower Final';
                                            $colorClass = 'text-orange-400';
                                        } elseif ($round == $grandFinalRound - 2) {
                                            $roundLabel = 'Lower Semi';
                                            $colorClass = 'text-red-400';
                                        } else {
                                            $roundLabel = 'LB R' . ($lbIndex + 1);
                                            $colorClass = 'text-red-400';
                                        }
                                    @endphp

                                    <div class="bracket-round lower-round {{ $spacingClass }}">
                                        <div class="round-title {{ $colorClass }}">
                                            {{ $roundLabel }}
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
                            </div>
                        </div>
                    @else
                        {{-- SINGLE ELIMINATION LAYOUT --}}
                        <div class="bracket-container single-elimination">
                            <div class="upper-bracket-wrapper">
                                @for ($round = 1; $round <= $maxRound; $round++)
                                    @php
                                        $roundBrackets = $bracketsByRound
                                            ->get($round, collect())
                                            ->sortBy('position')
                                            ->values();
                                        $roundCount = $roundBrackets->count();
                                        if ($roundCount == 0) {
                                            continue;
                                        }

                                        $matches = $roundBrackets->chunk(2);

                                        if ($round == $maxRound) {
                                            $roundLabel = 'Final';
                                            $colorClass = 'text-orange-400';
                                        } elseif ($round == $maxRound - 1) {
                                            $roundLabel = 'Semifinal';
                                            $colorClass = 'text-purple-400';
                                        } elseif ($round == $maxRound - 2 && $maxRound > 3) {
                                            $roundLabel = 'Quarterfinal';
                                            $colorClass = 'text-blue-400';
                                        } else {
                                            $roundLabel = 'Round ' . $round;
                                            $colorClass = 'text-blue-400';
                                        }
                                    @endphp

                                    <div class="bracket-round single-round single-round-{{ $round }}">
                                        <div class="round-title {{ $colorClass }}">
                                            {{ $roundLabel }}
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

                                {{-- CHAMPION SECTION (Single Elimination) - SCROLLABLE --}}
                                <div class="champion-wrapper-container overflow-x-auto overflow-y-visible pb-4">
                                    <div class="champion-wrapper-inner">
                                        <div class="champion-spacer"></div>

                                        <div class="bracket-round" style="min-width: 320px;">
                                            <div class="round-title text-yellow-400">
                                                <i class="fas fa-trophy mr-2"></i>Champion
                                            </div>

                                            <div class="matches-wrapper">
                                                @php
                                                    $champion = $brackets
                                                        ->where('round', $maxRound)
                                                        ->where('is_winner', true)
                                                        ->first();
                                                @endphp

                                                @if ($champion && $champion->player_name !== 'TBD')
                                                    <div class="relative">
                                                        <div
                                                            class="absolute inset-0 bg-gradient-radial from-yellow-500/20 to-transparent blur-xl">
                                                        </div>
                                                        <div
                                                            class="relative bg-gradient-to-br from-yellow-600 via-yellow-500 to-yellow-600 rounded-xl px-4 sm:px-6 py-6 sm:py-8 shadow-2xl transform hover:scale-105 transition-transform duration-300">
                                                            <div class="text-center">
                                                                <i
                                                                    class="fas fa-crown text-3xl sm:text-4xl text-yellow-200 mb-3 sm:mb-4"></i>
                                                                <p class="text-lg sm:text-xl font-bold text-white mb-2">
                                                                    {{ $champion->player_name }}</p>
                                                                <p class="text-xs sm:text-sm text-yellow-100">Tournament
                                                                    Winner</p>
                                                            </div>
                                                            <div
                                                                class="absolute -top-3 -left-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50">
                                                            </div>
                                                            <div
                                                                class="absolute -bottom-3 -right-3 w-6 h-6 bg-yellow-300 rounded-full opacity-50">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div
                                                        class="bg-neutral-800 rounded-xl px-4 sm:px-6 py-6 sm:py-8 text-center">
                                                        <i
                                                            class="fas fa-hourglass-half text-2xl sm:text-3xl text-gray-500 mb-3"></i>
                                                        <p class="text-sm sm:text-base text-gray-400 font-medium">To Be
                                                            Determined</p>
                                                        <p class="text-xs text-gray-600 mt-2">Winner will be announced
                                                            after final match</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Statistics Section -->
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

                                    if ($isDoubleElimination) {
                                        if ($round == 1) {
                                            $label = 'Round 1';
                                        } elseif ($round == $maxRound) {
                                            $label = 'Grand Final';
                                        } elseif ($round <= $upperBracketEnd) {
                                            if ($round == $upperBracketEnd) {
                                                $label = 'Upper Final';
                                            } else {
                                                $label = 'Upper R' . $round;
                                            }
                                        } else {
                                            $lbIndex = $round - $lowerBracketStart + 1;
                                            if ($round == $maxRound - 1) {
                                                $label = 'Lower Final';
                                            } else {
                                                $label = 'LB R' . $lbIndex;
                                            }
                                        }
                                    } else {
                                        if ($round == $maxRound) {
                                            $label = 'Final';
                                        } elseif ($round == $maxRound - 1) {
                                            $label = 'Semifinal';
                                        } elseif ($round == $maxRound - 2 && $maxRound > 3) {
                                            $label = 'Quarterfinal';
                                        } else {
                                            $label = 'Round ' . $round;
                                        }
                                    }
                                @endphp
                                <div class="flex items-center justify-between py-2 border-b border-neutral-700">
                                    <span class="text-xs sm:text-sm font-medium">{{ $label }}</span>
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