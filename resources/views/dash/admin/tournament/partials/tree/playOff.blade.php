<?php
use Xoco70\LaravelTournaments\TreeGen\CreateDoubleEliminationTree;

$doubleEliminationTree = $championship->fightersGroups->where('round', '>=', $hasPreliminary + 1)->groupBy('round');
$treeGen = null;

if (sizeof($doubleEliminationTree) > 0) {
    try {
        $treeGen = new CreateDoubleEliminationTree($doubleEliminationTree, $championship, $hasPreliminary);
        $treeGen->build();
    } catch (\Exception $e) {
        \Log::error('Error building double elimination tree', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        $treeGen = null;
    }
}
?>

@if (sizeof($doubleEliminationTree) > 0 && $treeGen)

    @if (Request::is('championships/' . $championship->id . '/pdf'))
        <h1>{{ $championship->buildName() }}</h1>
    @endif

    {{-- ===== ACTION BAR ===== --}}
    <div class="mb-6">
        {{-- Mobile --}}
        <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-neutral-900 border-t border-gray-700 p-4 z-50 shadow-2xl">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="w-1 h-5 bg-[#1e90ff] rounded flex-shrink-0"></div>
                    <h3 class="text-sm font-semibold text-white truncate">Update Bracket</h3>
                </div>
                <button type="submit" form="tree-form"
                    class="px-5 py-2.5 bg-[#1e90ff] text-white rounded-lg hover:bg-blue-600 transition font-medium text-xs flex items-center justify-center gap-2 shadow-lg flex-shrink-0">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:block sticky top-36 bg-[#2c2c2c]/95 backdrop-blur z-40 py-3 -mx-6 px-6 border-b border-gray-700">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-[#1e90ff] rounded"></div>
                    <h3 class="text-base font-semibold text-white">Update Tournament Bracket</h3>
                </div>
                <button type="submit" form="tree-form"
                    class="px-6 py-2 bg-[#1e90ff] text-white rounded-lg hover:bg-blue-600 transition font-medium text-sm flex items-center justify-center gap-2 shadow-lg">
                    <i class="fas fa-save"></i> Update Tree
                </button>
            </div>
        </div>
    </div>

    <form method="POST" id="tree-form" action="{{ route('tree.update', ['championship' => $championship->id]) }}" accept-charset="UTF-8">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" id="activeTreeTab" name="activeTreeTab" value="{{ $championship->id }}" />

        {{-- ===== CALCULATE STRUCTURE ===== --}}
        @php
            $boxW = 280;
            $boxH = 70;
            $colGap = 100;
            $vSpace = 45;

            // Calculate structure
            $round1Count = count($treeGen->round1 ?? []);
            $numFighters = $round1Count * 2;
            $numRounds = intval(log($numFighters, 2));
            $upperBracketEnd = $numRounds + 1;
            $lowerBracketStart = $upperBracketEnd + 1;

            // Get all rounds
            $upperKeys = array_keys($treeGen->upperBrackets ?? []);
            $lowerKeys = array_keys($treeGen->lowerBrackets ?? []);
            sort($upperKeys, SORT_NUMERIC);
            sort($lowerKeys, SORT_NUMERIC);

            // Calculate positions
            $upperCols = count($upperKeys);
            $lowerCols = count($lowerKeys);
            $totalCols = $upperCols + $lowerCols + 1;

            // Canvas dimensions
            $canvasW = ($totalCols + 1) * ($boxW + $colGap);
            $canvasH = $round1Count * ($boxH + $vSpace) * 2 + 300;

            // Lower bracket offset
            $lowerOffset = $round1Count * ($boxH + $vSpace) + 150;

            // Round titles
            $roundTitles = [];
            $roundPositions = [];

            // Round 1 title
            $roundTitles[] = 'Round 1';
            $roundPositions[] = $boxW / 2;

            // Upper bracket titles
            foreach ($upperKeys as $idx => $rk) {
                if ($rk == 1) continue;
                
                $col = $idx;
                $roundsFromEnd = $upperCols - $col;
                
                if ($roundsFromEnd == 1) {
                    $title = 'Upper Final';
                } elseif ($roundsFromEnd == 2) {
                    $title = 'Upper Semi';
                } elseif ($roundsFromEnd == 3) {
                    $title = 'Upper Quarter';
                } else {
                    $title = 'Upper R' . $rk;
                }

                $roundTitles[] = $title;
                $roundPositions[] = $col * ($boxW + $colGap) + $boxW / 2;
            }

            // Lower bracket titles
            foreach ($lowerKeys as $idx => $rk) {
                $col = $upperCols + $idx;
                
                if ($idx == count($lowerKeys) - 1) {
                    $title = 'Lower Final';
                } elseif ($idx == count($lowerKeys) - 2) {
                    $title = 'Lower Semi';
                } else {
                    $title = 'LB R' . ($idx + 1);
                }

                $roundTitles[] = $title;
                $roundPositions[] = $col * ($boxW + $colGap) + $boxW / 2;
            }

            // Grand Final title
            $roundTitles[] = '<i class="fas fa-trophy"></i> Grand Final';
            $roundPositions[] = ($upperCols + $lowerCols) * ($boxW + $colGap) + $boxW / 2;
        @endphp

        {{-- ===== ROUND TITLES BAR ===== --}}
        <div class="bracket-scroll-container">
            <div id="round-titles-wrapper" style="position:relative; height:50px; width: {{ $canvasW }}px; min-width:100%; margin-bottom:20px;">
                @foreach ($roundTitles as $idx => $title)
                    @php
                        $class = 'upper-round';
                        if ($idx >= $upperCols && $idx < $upperCols + $lowerCols) {
                            $class = 'lower-round';
                        }
                        if ($idx == count($roundTitles) - 1) {
                            $class = 'grand-final-round';
                        }
                    @endphp
                    <div class="round-title {{ $class }}" style="left: {{ $roundPositions[$idx] }}px; transform: translateX(-50%); top:10px; position:absolute;">
                        {!! $title !!}
                    </div>
                @endforeach
            </div>

            {{-- ===== MAIN CANVAS ===== --}}
            <div id="brackets-wrapper" style="position:relative; width: {{ $canvasW }}px; height: {{ $canvasH }}px; min-width:100%;">

                {{-- ========== UPPER BRACKET ========== --}}
                @foreach ($treeGen->upperBrackets as $roundNumber => $round)
                    @php
                        $colIndex = array_search($roundNumber, $upperKeys);
                        $vMultiplier = pow(2, $colIndex);
                        $startY = ($vMultiplier - 1) * ($boxH / 2 + $vSpace / 2);
                        $isUpperFinal = ($roundNumber == $upperBracketEnd);
                    @endphp

                    @foreach ($round as $matchNumber => $match)
                        @php
                            $x = $colIndex * ($boxW + $colGap);
                            $y = $startY + ($matchNumber - 1) * $vMultiplier * ($boxH + $vSpace);
                            $centerY = $y + $boxH / 2;
                            $rightX = $x + $boxW;

                            // JANGAN hitung connector untuk Upper Final SAMA SEKALI
                            $shouldDrawConnector = !$isUpperFinal && ($colIndex < count($upperKeys) - 1);
                            
                            if ($shouldDrawConnector) {
                                $halfGap = $colGap / 2;
                                $vLineX = $rightX + $halfGap;
                                $nextMatchNum = ceil($matchNumber / 2);
                                $nextVMultiplier = pow(2, $colIndex + 1);
                                $nextStartY = ($nextVMultiplier - 1) * ($boxH / 2 + $vSpace / 2);
                                $nextY = $nextStartY + ($nextMatchNum - 1) * $nextVMultiplier * ($boxH + $vSpace);
                                $nextCenterY = $nextY + $boxH / 2;
                                $isOdd = ($matchNumber % 2 == 1);
                                
                                if ($isOdd) {
                                    $vConnectorTop = $centerY;
                                    $vConnectorHeight = $nextCenterY - $centerY;
                                } else {
                                    $vConnectorTop = $nextCenterY;
                                    $vConnectorHeight = $centerY - $nextCenterY;
                                }
                            }
                        @endphp

                        {{-- Match Box --}}
                        <div class="match-wrapper upper-match" style="top: {{ $y }}px; left: {{ $x }}px; width: {{ $boxW }}px;">
                            @php
                                $isAWinner = (optional($match['playerA'])->id == ($match['winner_id'] ?? null) && !empty($match['winner_id'])) ? 'X' : null;
                                $isBWinner = (optional($match['playerB'])->id == ($match['winner_id'] ?? null) && !empty($match['winner_id'])) ? 'X' : null;
                            @endphp

                            <div {{ $isAWinner ? 'id=success' : '' }}>
                                <input type="text" class="score" name="score[]" value="{{ $isAWinner }}">
                                @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                    'selected' => $match['playerA'],
                                    'roundNumber' => $roundNumber,
                                    'isSuccess' => $isAWinner,
                                    'treeGen' => $treeGen,
                                ])
                            </div>
                            <div class="match-divider"></div>
                            <div {{ $isBWinner ? 'id=success' : '' }}>
                                <input type="text" class="score" name="score[]" value="{{ $isBWinner }}">
                                @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                    'selected' => $match['playerB'],
                                    'roundNumber' => $roundNumber,
                                    'isSuccess' => $isBWinner,
                                    'treeGen' => $treeGen,
                                ])
                            </div>
                        </div>

                        {{-- Connectors - HANYA gambar jika shouldDrawConnector = true --}}
                        @if (isset($shouldDrawConnector) && $shouldDrawConnector)
                            <div class="horizontal-connector upper-connector" style="left: {{ $rightX }}px; top: {{ $centerY }}px; width: {{ $halfGap }}px;"></div>
                            <div class="vertical-connector upper-connector" style="left: {{ $vLineX }}px; top: {{ $vConnectorTop }}px; height: {{ $vConnectorHeight }}px;"></div>
                            <div class="horizontal-connector upper-connector" style="left: {{ $vLineX }}px; top: {{ $nextCenterY }}px; width: {{ $halfGap }}px;"></div>
                        @endif
                    @endforeach
                @endforeach

                {{-- ========== LOWER BRACKET ========== --}}
                @foreach ($treeGen->lowerBrackets as $roundNumber => $round)
                    @php
                        $lbIndex = array_search($roundNumber, $lowerKeys);
                        $colIndex = $upperCols + $lbIndex;
                        $matchCount = count($round);
                        $isLowerFinal = ($lbIndex == count($lowerKeys) - 1);

                        // Dynamic spacing
                        if ($matchCount >= 4) {
                            $lbVMultiplier = 1;
                            $lbStartY = $lowerOffset;
                        } elseif ($matchCount == 2) {
                            $lbVMultiplier = 2;
                            $lbStartY = $lowerOffset + ($boxH + $vSpace);
                        } else {
                            $lbVMultiplier = 4;
                            $lbStartY = $lowerOffset + ($boxH + $vSpace) * 1.5;
                        }
                    @endphp

                    @foreach ($round as $matchNumber => $match)
                        @php
                            $x = $colIndex * ($boxW + $colGap);
                            $y = $lbStartY + ($matchNumber - 1) * $lbVMultiplier * ($boxH + $vSpace);
                            $centerY = $y + $boxH / 2;
                            $rightX = $x + $boxW;

                            // Connector calculations
                            if (!$isLowerFinal) {
                                $halfGap = $colGap / 2;
                                $vLineX = $rightX + $halfGap;
                                $nextMatchNum = ceil($matchNumber / 2);
                                $nextRoundKey = $lowerKeys[$lbIndex + 1] ?? null;

                                if ($nextRoundKey) {
                                    $nextMatchCount = count($treeGen->lowerBrackets[$nextRoundKey] ?? []);
                                    
                                    if ($nextMatchCount >= 4) {
                                        $nextLbVMultiplier = 1;
                                        $nextLbStartY = $lowerOffset;
                                    } elseif ($nextMatchCount == 2) {
                                        $nextLbVMultiplier = 2;
                                        $nextLbStartY = $lowerOffset + ($boxH + $vSpace);
                                    } else {
                                        $nextLbVMultiplier = 4;
                                        $nextLbStartY = $lowerOffset + ($boxH + $vSpace) * 1.5;
                                    }

                                    $nextY = $nextLbStartY + ($nextMatchNum - 1) * $nextLbVMultiplier * ($boxH + $vSpace);
                                    $nextCenterY = $nextY + $boxH / 2;
                                } else {
                                    $nextCenterY = $centerY;
                                }

                                $isOdd = ($matchNumber % 2 == 1);
                                
                                if ($isOdd) {
                                    $vConnectorTop = $centerY;
                                    $vConnectorHeight = abs($nextCenterY - $centerY);
                                } else {
                                    $vConnectorTop = min($centerY, $nextCenterY);
                                    $vConnectorHeight = abs($centerY - $nextCenterY);
                                }
                            }
                        @endphp

                        {{-- Match Box --}}
                        <div class="match-wrapper lower-match" style="top: {{ $y }}px; left: {{ $x }}px; width: {{ $boxW }}px;">
                            @php
                                $isAWinner = (optional($match['playerA'])->id == ($match['winner_id'] ?? null) && !empty($match['winner_id'])) ? 'X' : null;
                                $isBWinner = (optional($match['playerB'])->id == ($match['winner_id'] ?? null) && !empty($match['winner_id'])) ? 'X' : null;
                            @endphp

                            <div {{ $isAWinner ? 'id=success' : '' }}>
                                <input type="text" class="score" name="score[]" value="{{ $isAWinner }}">
                                @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                    'selected' => $match['playerA'],
                                    'roundNumber' => $roundNumber,
                                    'isSuccess' => $isAWinner,
                                    'treeGen' => $treeGen,
                                ])
                            </div>
                            <div class="match-divider"></div>
                            <div {{ $isBWinner ? 'id=success' : '' }}>
                                <input type="text" class="score" name="score[]" value="{{ $isBWinner }}">
                                @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                    'selected' => $match['playerB'],
                                    'roundNumber' => $roundNumber,
                                    'isSuccess' => $isBWinner,
                                    'treeGen' => $treeGen,
                                ])
                            </div>
                        </div>

                        {{-- Connectors - SKIP untuk Lower Final --}}
                        @if (!$isLowerFinal)
                            <div class="horizontal-connector lower-connector" style="left: {{ $rightX }}px; top: {{ $centerY }}px; width: {{ $halfGap }}px;"></div>
                            @if ($vConnectorHeight > 0)
                                <div class="vertical-connector lower-connector" style="left: {{ $vLineX }}px; top: {{ $vConnectorTop }}px; height: {{ $vConnectorHeight }}px;"></div>
                            @endif
                            <div class="horizontal-connector lower-connector" style="left: {{ $vLineX }}px; top: {{ $nextCenterY }}px; width: {{ $halfGap }}px;"></div>
                        @endif
                    @endforeach
                @endforeach

                {{-- ========== GRAND FINAL ========== --}}
                @if (!empty($treeGen->grandFinal))
                    @php
                        // Get positions
                        $lastUpperRound = $treeGen->upperBrackets[$upperBracketEnd] ?? [];
                        $lastUpperCol = count($upperKeys) - 1;
                        $upperVMultiplier = pow(2, $lastUpperCol);
                        $upperStartY = ($upperVMultiplier - 1) * ($boxH / 2 + $vSpace / 2);
                        $upperCenterY = $upperStartY + $boxH / 2;

                        $lastLowerRound = end($treeGen->lowerBrackets);
                        $lowerMatchCount = count($lastLowerRound);
                        
                        if ($lowerMatchCount >= 4) {
                            $lbVMultiplier = 1;
                            $lbStartY = $lowerOffset;
                        } elseif ($lowerMatchCount == 2) {
                            $lbVMultiplier = 2;
                            $lbStartY = $lowerOffset + ($boxH + $vSpace);
                        } else {
                            $lbVMultiplier = 4;
                            $lbStartY = $lowerOffset + ($boxH + $vSpace) * 1.5;
                        }
                        
                        $lowerCenterY = $lbStartY + $boxH / 2;

                        // Grand Final position
                        $gfX = ($upperCols + $lowerCols) * ($boxW + $colGap);
                        $gfY = ($upperCenterY + $lowerCenterY) / 2 - $boxH / 2;
                    @endphp

                    {{-- Grand Final Match Box --}}
                    <div class="match-wrapper grand-final-match" style="top: {{ $gfY }}px; left: {{ $gfX }}px; width: {{ $boxW }}px;">
                        @php
                            $gf = $treeGen->grandFinal;
                            $isAW = (optional($gf['playerA'])->id == ($gf['winner_id'] ?? null) && !empty($gf['winner_id'])) ? 'X' : null;
                            $isBW = (optional($gf['playerB'])->id == ($gf['winner_id'] ?? null) && !empty($gf['winner_id'])) ? 'X' : null;
                        @endphp

                        <div class="grand-final-header">
                            <i class="fas fa-trophy"></i>
                            <span>GRAND FINAL</span>
                            <i class="fas fa-trophy"></i>
                        </div>

                        <div {{ $isAW ? 'id=success' : '' }} class="grand-final-player">
                            <div class="player-label upper-label">
                                <i class="fas fa-arrow-up"></i> Upper Winner
                            </div>
                            <div class="player-input-row">
                                <input type="text" class="score" name="score[]" value="{{ $isAW }}">
                                @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                    'selected' => $gf['playerA'],
                                    'roundNumber' => $gf['roundNumber'],
                                    'isSuccess' => $isAW,
                                    'treeGen' => $treeGen,
                                ])
                            </div>
                        </div>

                        <div class="match-divider grand-final-divider">
                            <span class="vs-label">VS</span>
                        </div>

                        <div {{ $isBW ? 'id=success' : '' }} class="grand-final-player">
                            <div class="player-label lower-label">
                                <i class="fas fa-arrow-down"></i> Lower Winner
                            </div>
                            <div class="player-input-row">
                                <input type="text" class="score" name="score[]" value="{{ $isBW }}">
                                @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                    'selected' => $gf['playerB'],
                                    'roundNumber' => $gf['roundNumber'],
                                    'isSuccess' => $isBW,
                                    'treeGen' => $treeGen,
                                ])
                            </div>
                        </div>

                        @if ($isAW || $isBW)
                            <div class="champion-indicator">
                                <i class="fas fa-crown"></i>
                                <span>CHAMPION</span>
                                <i class="fas fa-crown"></i>
                            </div>
                        @endif
                    </div>

                    {{-- ===== CONNECTOR FROM UPPER FINAL TO GRAND FINAL ===== --}}
                    @php
                        $upperX = $lastUpperCol * ($boxW + $colGap);
                        $upperRightX = $upperX + $boxW;
                        $gfMidX = $gfX - $colGap / 2;
                        $gfCenterY = $gfY + $boxH / 2;
                    @endphp

                    {{-- Horizontal from Upper Final to midpoint --}}
                    <div class="horizontal-connector gf-connector"
                        style="left: {{ $upperRightX }}px; 
                               top: {{ $upperCenterY }}px; 
                               width: {{ $gfMidX - $upperRightX }}px;">
                    </div>

                    {{-- Vertical at midpoint --}}
                    <div class="vertical-connector gf-connector"
                        style="left: {{ $gfMidX }}px; 
                               top: {{ min($upperCenterY, $gfCenterY) }}px; 
                               height: {{ abs($gfCenterY - $upperCenterY) }}px;">
                    </div>

                    {{-- Horizontal to Grand Final --}}
                    <div class="horizontal-connector gf-connector"
                        style="left: {{ $gfMidX }}px; 
                               top: {{ $gfCenterY }}px; 
                               width: {{ $gfX - $gfMidX }}px;">
                    </div>

                    {{-- ===== CONNECTOR FROM LOWER FINAL TO GRAND FINAL ===== --}}
                    @php
                        $lowerX = ($upperCols + $lowerCols - 1) * ($boxW + $colGap);
                        $lowerRightX = $lowerX + $boxW;
                    @endphp

                    {{-- Horizontal from Lower Final to midpoint --}}
                    <div class="horizontal-connector gf-connector"
                        style="left: {{ $lowerRightX }}px; 
                               top: {{ $lowerCenterY }}px; 
                               width: {{ $gfMidX - $lowerRightX }}px;">
                    </div>

                    {{-- Vertical at midpoint --}}
                    <div class="vertical-connector gf-connector"
                        style="left: {{ $gfMidX }}px; 
                               top: {{ min($lowerCenterY, $gfCenterY) }}px; 
                               height: {{ abs($gfCenterY - $lowerCenterY) }}px;">
                    </div>
                @endif

            </div>
        </div>
    </form>

@else
    <div class="bg-yellow-500/10 border-l-4 border-yellow-500 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-white mb-2">
            <i class="fas fa-info-circle mr-2"></i>
            No Tournament Data Available
        </h3>
        <p class="text-sm text-gray-400 mb-4">
            Please create fighters and generate the tournament tree first.
        </p>
    </div>
@endif

{{-- ===== STYLES ===== --}}
<style>
    :root {
        --c-panel: rgba(30, 30, 30, .95);
        --c-border: rgba(255, 255, 255, .12);
        --c-text: #e5e7eb;
        --c-upper: #60a5fa;
        --c-upper-b: rgba(30, 144, 255, .4);
        --c-upper-bg: rgba(30, 144, 255, .1);
        --c-lower: #fca5a5;
        --c-lower-b: rgba(239, 68, 68, .4);
        --c-lower-bg: rgba(239, 68, 68, .1);
        --c-gf: #fde68a;
        --c-gf-b: rgba(234, 179, 8, .45);
        --c-gf-bg: rgba(234, 179, 8, .12);
        --line: rgba(255, 255, 255, .92);
        --line-lower: rgba(239, 68, 68, .95);
        --line-thick: 2px;
    }

    .bracket-scroll-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        padding: 10px 0;
        background: transparent;
        scroll-behavior: smooth;
    }

    .bracket-scroll-container::-webkit-scrollbar {
        height: 10px;
    }

    .bracket-scroll-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .bracket-scroll-container::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, rgba(255, 255, 255, .12), rgba(255, 255, 255, .18));
        border-radius: 999px;
    }

    #brackets-wrapper {
        background: repeating-linear-gradient(to right, rgba(255, 255, 255, .03), rgba(255, 255, 255, .03) 1px, transparent 1px, transparent 60px), linear-gradient(180deg, rgba(255, 255, 255, .02), rgba(255, 255, 255, .02));
        border-radius: 12px;
    }

    .match-wrapper {
        position: absolute;
        background: var(--c-panel);
        border: 2px solid var(--c-border);
        border-radius: 10px;
        padding: 6px;
        transition: all .2s ease;
        z-index: 2;
        backdrop-filter: blur(2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
    }

    .match-wrapper:hover {
        border-color: rgba(30, 144, 255, .6);
        box-shadow: 0 4px 16px rgba(30, 144, 255, .25);
        transform: translateY(-2px);
    }

    .match-wrapper.lower-match {
        border-color: var(--c-lower-b);
    }

    .match-wrapper.lower-match:hover {
        border-color: rgba(239, 68, 68, .7);
        box-shadow: 0 4px 16px rgba(239, 68, 68, .3);
    }

    .match-wrapper.grand-final-match {
        border-color: var(--c-gf-b);
        background: rgba(30, 30, 30, .98);
        box-shadow: 0 4px 20px rgba(234, 179, 8, .2);
        padding: 8px;
    }

    .match-wrapper.grand-final-match:hover {
        border-color: rgba(234, 179, 8, .8);
        box-shadow: 0 6px 24px rgba(234, 179, 8, .35);
    }

    .grand-final-header {
        background: linear-gradient(135deg, rgba(234, 179, 8, .2), rgba(234, 179, 8, .1));
        border: 1px solid rgba(234, 179, 8, .4);
        border-radius: 8px;
        padding: 8px;
        margin-bottom: 10px;
        text-align: center;
        font-weight: 800;
        font-size: 13px;
        color: #fde68a;
        letter-spacing: 1px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .grand-final-header i {
        color: #fbbf24;
        animation: pulse-trophy 2s ease-in-out infinite;
    }

    @keyframes pulse-trophy {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .grand-final-player {
        margin-bottom: 8px;
    }

    .player-label {
        font-size: 10px;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
        letter-spacing: 0.5px;
    }

    .player-label.upper-label {
        background: rgba(30, 144, 255, .15);
        color: #60a5fa;
        border: 1px solid rgba(30, 144, 255, .3);
    }

    .player-label.lower-label {
        background: rgba(239, 68, 68, .15);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, .3);
    }

    .player-input-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .grand-final-divider {
        position: relative;
        height: 3px;
        background: linear-gradient(90deg, rgba(234, 179, 8, .2), rgba(234, 179, 8, .6), rgba(234, 179, 8, .2));
        margin: 12px 0;
        border-radius: 2px;
    }

    .vs-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(30, 30, 30, .98);
        padding: 4px 12px;
        border: 2px solid rgba(234, 179, 8, .6);
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        color: #fde68a;
        letter-spacing: 1px;
    }

    .champion-indicator {
        margin-top: 10px;
        background: linear-gradient(135deg, rgba(234, 179, 8, .25), rgba(234, 179, 8, .15));
        border: 2px solid rgba(234, 179, 8, .6);
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        font-weight: 800;
        font-size: 12px;
        color: #fbbf24;
        letter-spacing: 1.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        animation: champion-glow 1.5s ease-in-out infinite;
    }

    .champion-indicator i {
        color: #fbbf24;
        font-size: 14px;
    }

    @keyframes champion-glow {
        0%, 100% {
            box-shadow: 0 0 10px rgba(234, 179, 8, .3);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 20px rgba(234, 179, 8, .5);
            transform: scale(1.02);
        }
    }

    .match-wrapper > div {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px;
    }

    .match-divider {
        height: 2px;
        background: rgba(255, 255, 255, .1);
        margin: 3px 0;
        border-radius: 2px;
    }

    .match-wrapper .score {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        text-align: center;
        border-radius: 8px;
        border: 1px solid #2f2f2f;
        background: #0f0f0f;
        color: #f3f4f6;
        font-weight: 800;
        line-height: 30px;
        padding: 0;
        font-size: 14px;
    }

    .match-wrapper > div#success .score {
        border-color: rgba(34, 197, 94, .7);
        background: rgba(34, 197, 94, .15);
        color: #86efac;
    }

    .match-wrapper select {
        flex: 1;
        background: rgba(15, 15, 15, .8);
        border: 1px solid rgba(255, 255, 255, .08);
        color: #e5e7eb;
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 13px;
        outline: none;
        transition: all .2s ease;
    }

    .match-wrapper select:focus {
        border-color: rgba(30, 144, 255, .5);
        background: rgba(15, 15, 15, .95);
    }

    .match-wrapper > div#success select {
        border-color: rgba(34, 197, 94, .4);
        background: rgba(34, 197, 94, .08);
    }

    .vertical-connector,
    .horizontal-connector {
        position: absolute;
        z-index: 1;
        pointer-events: none;
        background: var(--line);
        border-radius: 1px;
    }

    .vertical-connector {
        width: var(--line-thick);
    }

    .horizontal-connector {
        height: var(--line-thick);
    }

    .vertical-connector.lower-connector,
    .horizontal-connector.lower-connector {
        background: var(--line-lower);
        box-shadow: 0 0 4px rgba(239, 68, 68, .2);
    }

    .vertical-connector.gf-connector,
    .horizontal-connector.gf-connector {
        background: var(--line-gf);
        box-shadow: 0 0 6px rgba(234, 179, 8, .3);
    }

    .horizontal-connector.gf-connector {
        height: 3px;
    }

    .vertical-connector.gf-connector {
        width: 3px;
    }

    .round-title {
        padding: 8px 16px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, .12);
        background: rgba(30, 30, 30, .9);
        font-weight: 700;
        font-size: 13px;
        color: var(--c-text);
        letter-spacing: .3px;
        white-space: nowrap;
        user-select: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
        backdrop-filter: blur(4px);
    }

    .round-title.upper-round {
        border-color: var(--c-upper-b);
        background: var(--c-upper-bg);
        color: var(--c-upper);
    }

    .round-title.lower-round {
        border-color: var(--c-lower-b);
        background: var(--c-lower-bg);
        color: var(--c-lower);
    }

    .round-title.grand-final-round {
        border-color: var(--c-gf-b);
        background: var(--c-gf-bg);
        color: var(--c-gf);
        padding: 8px 20px;
    }

    @media (max-width: 640px) {
        .round-title {
            font-size: 11px;
            padding: 6px 12px;
        }
        .match-wrapper {
            padding: 4px;
        }
        .match-wrapper .score {
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
            font-size: 12px;
        }
        .match-wrapper select {
            font-size: 12px;
            padding: 4px 8px;
        }
    }
</style>

{{-- ===== JAVASCRIPT FOR AUTO-FILL GRAND FINAL ===== --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Grand Final Auto-Fill Script Loaded');

    const allMatches = document.querySelectorAll('.match-wrapper');
    const totalMatches = allMatches.length;

    console.log('Total matches:', totalMatches);

    let upperFinalIndex = -1;
    let lowerFinalIndex = -1;
    let grandFinalIndex = -1;

    let upperMatches = [];
    let lowerMatches = [];

    allMatches.forEach((match, idx) => {
        if (match.classList.contains('grand-final-match')) {
            grandFinalIndex = idx;
        } else if (match.classList.contains('upper-match') || (!match.classList.contains('lower-match') && !match.classList.contains('grand-final-match'))) {
            upperMatches.push(idx);
        } else if (match.classList.contains('lower-match')) {
            lowerMatches.push(idx);
        }
    });

    upperFinalIndex = upperMatches[upperMatches.length - 1];
    lowerFinalIndex = lowerMatches[lowerMatches.length - 1];

    console.log('Match Indices:', {
        upperFinal: upperFinalIndex,
        lowerFinal: lowerFinalIndex,
        grandFinal: grandFinalIndex
    });

    if (grandFinalIndex === -1) {
        console.error('❌ Grand Final not found!');
        return;
    }

    const allSelects = document.querySelectorAll('select[name="singleElimination_fighters[]"], select[name="playOff_fighters[]"]');
    const allScores = document.querySelectorAll('input[name="score[]"]');

    const grandFinalSelectIndex1 = grandFinalIndex * 2;
    const grandFinalSelectIndex2 = grandFinalIndex * 2 + 1;

    const grandFinalSelect1 = allSelects[grandFinalSelectIndex1];
    const grandFinalSelect2 = allSelects[grandFinalSelectIndex2];

    if (!grandFinalSelect1 || !grandFinalSelect2) {
        console.error('❌ Grand Final selects not found!');
        return;
    }

    function getMatchWinner(matchIndex) {
        const selectIndex1 = matchIndex * 2;
        const selectIndex2 = matchIndex * 2 + 1;

        const select1 = allSelects[selectIndex1];
        const select2 = allSelects[selectIndex2];
        const score1 = allScores[selectIndex1];
        const score2 = allScores[selectIndex2];

        if (!select1 || !select2 || !score1 || !score2) return null;

        const player1 = select1.value;
        const player2 = select2.value;
        const isWinner1 = score1.value && score1.value.trim() !== '';
        const isWinner2 = score2.value && score2.value.trim() !== '';

        if (isWinner1 && player1) {
            return {
                winner: player1,
                winnerName: select1.options[select1.selectedIndex].text
            };
        } else if (isWinner2 && player2) {
            return {
                winner: player2,
                winnerName: select2.options[select2.selectedIndex].text
            };
        }

        return null;
    }

    function updateGrandFinal() {
        console.log('🔄 Updating Grand Final...');

        let upperWinner = null;
        if (upperFinalIndex >= 0) {
            upperWinner = getMatchWinner(upperFinalIndex);
            if (upperWinner) {
                console.log('✓ Upper Final Winner:', upperWinner.winnerName);
            }
        }

        let lowerWinner = null;
        if (lowerFinalIndex >= 0) {
            lowerWinner = getMatchWinner(lowerFinalIndex);
            if (lowerWinner) {
                console.log('✓ Lower Final Winner:', lowerWinner.winnerName);
            }
        }

        if (upperWinner && upperWinner.winner) {
            if (grandFinalSelect1.value !== upperWinner.winner) {
                grandFinalSelect1.value = upperWinner.winner;
                grandFinalSelect1.dispatchEvent(new Event('change'));
                console.log('✅ Grand Final c1 updated:', upperWinner.winnerName);

                grandFinalSelect1.style.backgroundColor = 'rgba(30,144,255,0.1)';
                grandFinalSelect1.style.borderColor = 'rgba(30,144,255,0.5)';
            }
        }

        if (lowerWinner && lowerWinner.winner) {
            if (grandFinalSelect2.value !== lowerWinner.winner) {
                grandFinalSelect2.value = lowerWinner.winner;
                grandFinalSelect2.dispatchEvent(new Event('change'));
                console.log('✅ Grand Final c2 updated:', lowerWinner.winnerName);

                grandFinalSelect2.style.backgroundColor = 'rgba(239,68,68,0.1)';
                grandFinalSelect2.style.borderColor = 'rgba(239,68,68,0.5)';
            }
        }

        if (upperWinner && lowerWinner) {
            showGrandFinalNotification(upperWinner.winnerName, lowerWinner.winnerName);
        }
    }

    function showGrandFinalNotification(upperName, lowerName) {
        const existingNotif = document.querySelector('.gf-notification');
        if (existingNotif) existingNotif.remove();

        const notification = document.createElement('div');
        notification.className = 'gf-notification';
        notification.innerHTML = `
            <div class="gf-notif-content">
                <i class="fas fa-trophy"></i>
                <div>
                    <strong>🏆 GRAND FINAL MATCHUP SET!</strong><br>
                    <span style="color: #60a5fa;">${upperName}</span> 
                    <span style="color: #fbbf24;">VS</span> 
                    <span style="color: #fca5a5;">${lowerName}</span>
                </div>
                <i class="fas fa-trophy"></i>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    allScores.forEach((score) => {
        score.addEventListener('input', function() {
            clearTimeout(window.gfUpdateTimeout);
            window.gfUpdateTimeout = setTimeout(() => updateGrandFinal(), 300);
        });
    });

    allSelects.forEach((select) => {
        select.addEventListener('change', function() {
            clearTimeout(window.gfUpdateTimeout);
            window.gfUpdateTimeout = setTimeout(() => updateGrandFinal(), 300);
        });
    });

    updateGrandFinal();
    console.log('✅ Grand Final Auto-Fill Active');
});
</script>

<style>
    .gf-notification {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 9999;
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.95), rgba(234, 179, 8, 0.85));
        border: 2px solid rgba(234, 179, 8, 1);
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 8px 32px rgba(234, 179, 8, 0.4);
        animation: slideInRight 0.5s ease-out;
        max-width: 400px;
        transition: opacity 0.3s ease;
    }

    .gf-notif-content {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #1a1a1a;
        font-weight: 600;
        font-size: 13px;
    }

    .gf-notif-content i {
        font-size: 20px;
        color: #fbbf24;
        animation: pulse-trophy 2s ease-in-out infinite;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @media (max-width: 640px) {
        .gf-notification {
            top: auto;
            bottom: 80px;
            right: 10px;
            left: 10px;
            max-width: none;
        }
    }