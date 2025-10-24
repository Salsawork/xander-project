<?php
use Xoco70\LaravelTournaments\TreeGen\CreateSingleEliminationTree;

$doubleEliminationTree = $championship->fightersGroups->where('round', '>=', $hasPreliminary + 1)->groupBy('round');
if (sizeof($doubleEliminationTree) > 0) {
    $treeGen = new CreateSingleEliminationTree($doubleEliminationTree, $championship, $hasPreliminary);
    $treeGen->build();
    
    // Get bracket structure information
    $structure = $treeGen->getBracketStructure();
    $isDoubleElimination = ($structure['type'] == 'double_elimination');
    
    if ($isDoubleElimination) {
        $round1 = $structure['round_1'];
        $upperBracketStart = $structure['upper_bracket_start'];
        $upperBracketEnd = $structure['upper_bracket_end'];
        $lowerBracketStart = $structure['lower_bracket_start'];
        $lowerBracketEnd = $structure['lower_bracket_end'];
        $grandFinalRound = $structure['grand_final'];
    }
}
?>

@if (sizeof($doubleEliminationTree) > 0)

    @if (Request::is('championships/' . $championship->id . '/pdf'))
        <h1> {{ $championship->buildName() }}</h1>
    @endif

    {{-- Update Tree Button --}}
    <div class="mb-6">
        {{-- Mobile: Fixed Button di Bottom --}}
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

        {{-- Desktop: Sticky di Top --}}
        <div class="hidden sm:block sticky top-36 bg-[#2c2c2c] backdrop-blur z-40 py-3 -mx-6 px-6 border-b border-gray-700">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-[#1e90ff] rounded"></div>
                    <h3 class="text-base font-semibold text-white">
                        @if($isDoubleElimination)
                            Update Double Elimination Bracket
                        @else
                            Update Single Elimination Bracket
                        @endif
                    </h3>
                </div>
                <button type="submit" form="tree-form"
                    class="px-6 py-2 bg-[#1e90ff] text-white rounded-lg hover:bg-blue-600 transition font-medium text-sm flex items-center justify-center gap-2 shadow-lg">
                    <i class="fas fa-save"></i> Update Tree
                </button>
            </div>
        </div>
    </div>

    <form method="POST" id="tree-form" action="{{ route('tree.update', ['championship' => $championship->id]) }}"
        accept-charset="UTF-8">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" id="activeTreeTab" name="activeTreeTab" value="{{ $championship->id }}" />

        @if($isDoubleElimination)
            {{-- ================================================ --}}
            {{-- DOUBLE ELIMINATION: 3 SECTIONS --}}
            {{-- ================================================ --}}

            {{-- SECTION 1: ROUND 1 (Initial Matches) --}}
            <div class="mb-8">
                <div class="bg-green-500/10 border-l-4 border-green-500 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-flag-checkered text-green-400 mr-2"></i>
                        Round 1 - Opening Matches
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        <i class="fas fa-info-circle"></i> All participants start here
                    </p>
                </div>

                {{-- Round 1 Title --}}
                <div id="round-titles-wrapper-r1" class="mb-4 relative" style="height: 40px;">
                    <div class="round-title" style="left: 0px; background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); color: #86efac;">
                        Round 1
                    </div>
                </div>
            </div>

            {{-- Round 1 Brackets --}}
            <div id="brackets-wrapper-r1" class="pb-10 relative"
                style="padding-bottom: {{ ($championship->groupsByRound(1)->count() / 2) * 205 + 100 }}px">
                @foreach ($treeGen->brackets as $roundNumber => $round)
                    @if($roundNumber == $round1)
                        @foreach ($round as $matchNumber => $match)
                            @include('dash.admin.tournament.partials.tree.brackets.fight')

                            {{-- Connectors to Upper Bracket --}}
                            <div class="vertical-connector"
                                style="top: {{ $match['vConnectorTop'] }}px; left: {{ $match['vConnectorLeft'] }}px; height: {{ $match['vConnectorHeight'] }}px; background-color: rgba(34, 197, 94, 0.4);">
                            </div>
                            <div class="horizontal-connector"
                                style="top: {{ $match['hConnectorTop'] }}px; left: {{ $match['hConnectorLeft'] }}px; background-color: rgba(34, 197, 94, 0.4);">
                            </div>
                            <div class="horizontal-connector"
                                style="top: {{ $match['hConnector2Top'] }}px; left: {{ $match['hConnector2Left'] }}px; background-color: rgba(34, 197, 94, 0.4);">
                            </div>
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- SECTION 2: UPPER BRACKET --}}
            <div class="mt-12 mb-8">
                <div class="bg-blue-500/10 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-trophy text-yellow-400 mr-2"></i>
                        Upper Bracket (Winners Path)
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        <i class="fas fa-info-circle"></i> Winners continue here. Losers drop to Lower Bracket.
                    </p>
                </div>
                
                {{-- Upper Bracket Round Titles --}}
                <div id="round-titles-wrapper-upper" class="mb-4 relative" style="height: 40px;">
                    @php $upperRoundIndex = 0; @endphp
                    @foreach ($doubleEliminationTree as $roundNumber => $groups)
                        @if($roundNumber >= $upperBracketStart && $roundNumber <= $upperBracketEnd)
                            <?php 
                                $left = $upperRoundIndex * ($treeGen->matchWrapperWidth + $treeGen->roundSpacing - 1); 
                                $isUpperFinal = ($roundNumber == $upperBracketEnd);
                            ?>
                            <div class="round-title" style="left: {{ $left }}px; background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.3); color: #93c5fd;">
                                @if($isUpperFinal)
                                    Upper Final
                                @elseif($roundNumber == $upperBracketEnd - 1)
                                    Upper Semi
                                @else
                                    Upper R{{ $upperRoundIndex + 1 }}
                                @endif
                            </div>
                            @php $upperRoundIndex++; @endphp
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Upper Bracket Matches --}}
            <div id="brackets-wrapper-upper" class="pb-10 relative"
                style="padding-bottom: {{ ($championship->groupsByRound(1)->count() / 4) * 205 + 100 }}px">
                @php $upperRoundPosition = 0; @endphp
                @foreach ($treeGen->brackets as $roundNumber => $round)
                    @if($roundNumber >= $upperBracketStart && $roundNumber <= $upperBracketEnd)
                        @foreach ($round as $matchNumber => $match)
                            @php
                                // Adjust position for visual layout
                                $adjustedMatch = $match;
                                $adjustedMatch['matchWrapperLeft'] = $upperRoundPosition * ($treeGen->matchWrapperWidth + $treeGen->roundSpacing - 1);
                                
                                // Recalculate vertical positions
                                $spaceFactor = pow(2, $upperRoundPosition);
                                $adjustedMatch['matchWrapperTop'] = (((2 * $matchNumber) - 1) * $spaceFactor - 1) * (($treeGen->matchSpacing / 2) + $treeGen->playerWrapperHeight);
                                
                                // Recalculate connectors
                                $adjustedMatch['vConnectorLeft'] = floor($adjustedMatch['matchWrapperLeft'] + $treeGen->matchWrapperWidth + ($treeGen->roundSpacing / 2) - ($treeGen->borderWidth / 2));
                                $adjustedMatch['vConnectorHeight'] = ($spaceFactor * 0.5 * $treeGen->matchSpacing) + ($spaceFactor * 1 * $treeGen->playerWrapperHeight) + $treeGen->borderWidth;
                                $adjustedMatch['vConnectorTop'] = $adjustedMatch['hConnectorTop'] = $adjustedMatch['matchWrapperTop'] + $treeGen->playerWrapperHeight;
                                $adjustedMatch['hConnectorLeft'] = ($adjustedMatch['vConnectorLeft'] - ($treeGen->roundSpacing / 2)) + 2;
                                $adjustedMatch['hConnector2Left'] = $adjustedMatch['matchWrapperLeft'] + $treeGen->matchWrapperWidth + ($treeGen->roundSpacing / 2);
                                
                                if (!($matchNumber % 2)) {
                                    $adjustedMatch['hConnector2Top'] = $adjustedMatch['vConnectorTop'] -= ($adjustedMatch['vConnectorHeight'] - $treeGen->borderWidth);
                                } else {
                                    $adjustedMatch['hConnector2Top'] = $adjustedMatch['vConnectorTop'] + ($adjustedMatch['vConnectorHeight'] - $treeGen->borderWidth);
                                }
                                
                                $tempMatch = $match;
                                $match = $adjustedMatch;
                            @endphp
                            
                            @include('dash.admin.tournament.partials.tree.brackets.fight')
                            
                            @php $match = $tempMatch; @endphp

                            {{-- Connectors (blue for upper bracket) --}}
                            @if ($roundNumber != $upperBracketEnd)
                                <div class="vertical-connector"
                                    style="top: {{ $adjustedMatch['vConnectorTop'] }}px; left: {{ $adjustedMatch['vConnectorLeft'] }}px; height: {{ $adjustedMatch['vConnectorHeight'] }}px; background-color: rgba(59, 130, 246, 0.4);">
                                </div>
                                <div class="horizontal-connector"
                                    style="top: {{ $adjustedMatch['hConnectorTop'] }}px; left: {{ $adjustedMatch['hConnectorLeft'] }}px; background-color: rgba(59, 130, 246, 0.4);">
                                </div>
                                <div class="horizontal-connector"
                                    style="top: {{ $adjustedMatch['hConnector2Top'] }}px; left: {{ $adjustedMatch['hConnector2Left'] }}px; background-color: rgba(59, 130, 246, 0.4);">
                                </div>
                            @endif
                        @endforeach
                        @php $upperRoundPosition++; @endphp
                    @endif
                @endforeach
            </div>

            {{-- SECTION 3: LOWER BRACKET --}}
            <div class="mt-12 mb-8">
                <div class="bg-red-500/10 border-l-4 border-red-500 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-redo text-orange-400 mr-2"></i>
                        Lower Bracket (Second Chance)
                    </h3>
                    <p class="text-xs text-gray-400 mt-2">
                        <i class="fas fa-info-circle"></i> Losers from Upper Bracket drop here. Lose twice = eliminated.
                    </p>
                </div>
                
                {{-- Lower Bracket Round Titles --}}
                <div id="round-titles-wrapper-lower" class="mb-4 relative" style="height: 40px;">
                    @php $lowerRoundIndex = 0; @endphp
                    @foreach ($doubleEliminationTree as $roundNumber => $groups)
                        @if($roundNumber >= $lowerBracketStart && $roundNumber <= $lowerBracketEnd)
                            <?php 
                                $left = $lowerRoundIndex * ($treeGen->matchWrapperWidth + $treeGen->roundSpacing - 1); 
                                $isLowerFinal = ($roundNumber == $lowerBracketEnd);
                            ?>
                            <div class="round-title" style="left: {{ $left }}px; background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3); color: #fca5a5;">
                                @if($isLowerFinal)
                                    Lower Final
                                @else
                                    LB R{{ $lowerRoundIndex + 1 }}
                                @endif
                            </div>
                            @php $lowerRoundIndex++; @endphp
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Lower Bracket Matches --}}
            <div id="brackets-wrapper-lower" class="pb-10 relative"
                style="padding-bottom: {{ ($championship->groupsByRound(1)->count() / 4) * 205 + 100 }}px">
                @php $lowerRoundPosition = 0; @endphp
                
                @foreach ($treeGen->brackets as $roundNumber => $round)
                    @if($roundNumber >= $lowerBracketStart && $roundNumber <= $lowerBracketEnd)
                        @foreach ($round as $matchNumber => $match)
                            @php
                                // Adjust position for lower bracket
                                $adjustedMatch = $match;
                                $adjustedMatch['matchWrapperLeft'] = $lowerRoundPosition * ($treeGen->matchWrapperWidth + $treeGen->roundSpacing - 1);
                                
                                // Recalculate vertical positions
                                $spaceFactor = pow(2, $lowerRoundPosition);
                                $adjustedMatch['matchWrapperTop'] = (((2 * $matchNumber) - 1) * $spaceFactor - 1) * (($treeGen->matchSpacing / 2) + $treeGen->playerWrapperHeight);
                                
                                // Recalculate connectors
                                $adjustedMatch['vConnectorLeft'] = floor($adjustedMatch['matchWrapperLeft'] + $treeGen->matchWrapperWidth + ($treeGen->roundSpacing / 2) - ($treeGen->borderWidth / 2));
                                $adjustedMatch['vConnectorHeight'] = ($spaceFactor * 0.5 * $treeGen->matchSpacing) + ($spaceFactor * 1 * $treeGen->playerWrapperHeight) + $treeGen->borderWidth;
                                $adjustedMatch['vConnectorTop'] = $adjustedMatch['hConnectorTop'] = $adjustedMatch['matchWrapperTop'] + $treeGen->playerWrapperHeight;
                                $adjustedMatch['hConnectorLeft'] = ($adjustedMatch['vConnectorLeft'] - ($treeGen->roundSpacing / 2)) + 2;
                                $adjustedMatch['hConnector2Left'] = $adjustedMatch['matchWrapperLeft'] + $treeGen->matchWrapperWidth + ($treeGen->roundSpacing / 2);
                                
                                if (!($matchNumber % 2)) {
                                    $adjustedMatch['hConnector2Top'] = $adjustedMatch['vConnectorTop'] -= ($adjustedMatch['vConnectorHeight'] - $treeGen->borderWidth);
                                } else {
                                    $adjustedMatch['hConnector2Top'] = $adjustedMatch['vConnectorTop'] + ($adjustedMatch['vConnectorHeight'] - $treeGen->borderWidth);
                                }
                                
                                $tempMatch = $match;
                                $match = $adjustedMatch;
                            @endphp
                            
                            @include('dash.admin.tournament.partials.tree.brackets.fight')
                            
                            @php $match = $tempMatch; @endphp

                            {{-- Connectors (red for lower bracket) --}}
                            @if ($roundNumber != $lowerBracketEnd)
                                <div class="vertical-connector"
                                    style="top: {{ $adjustedMatch['vConnectorTop'] }}px; left: {{ $adjustedMatch['vConnectorLeft'] }}px; height: {{ $adjustedMatch['vConnectorHeight'] }}px; background-color: rgba(239, 68, 68, 0.4);">
                                </div>
                                <div class="horizontal-connector"
                                    style="top: {{ $adjustedMatch['hConnectorTop'] }}px; left: {{ $adjustedMatch['hConnectorLeft'] }}px; background-color: rgba(239, 68, 68, 0.4);">
                                </div>
                                <div class="horizontal-connector"
                                    style="top: {{ $adjustedMatch['hConnector2Top'] }}px; left: {{ $adjustedMatch['hConnector2Left'] }}px; background-color: rgba(239, 68, 68, 0.4);">
                                </div>
                            @endif
                        @endforeach
                        @php $lowerRoundPosition++; @endphp
                    @endif
                @endforeach
            </div>

            {{-- SECTION 4: GRAND FINAL --}}
            <div class="mt-12 mb-8">
                <div class="bg-gradient-to-r from-purple-500/10 to-pink-500/10 border-l-4 border-purple-500 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-semibold text-white text-center">
                        <i class="fas fa-crown text-yellow-400 mr-2 animate-pulse"></i>
                        GRAND FINAL
                        <i class="fas fa-crown text-yellow-400 ml-2 animate-pulse"></i>
                    </h3>
                    <p class="text-xs text-gray-400 text-center mt-2">Upper Bracket Winner vs Lower Bracket Winner</p>
                </div>
                
                <div id="round-titles-wrapper-final" class="mb-4 relative" style="height: 40px;">
                    <div class="round-title" style="left: 0px; background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(236, 72, 153, 0.2)); border-color: rgba(168, 85, 247, 0.4); color: #e9d5ff;">
                        <i class="fas fa-trophy"></i> Championship Match
                    </div>
                </div>
            </div>

            {{-- Grand Final Match --}}
            <div id="brackets-wrapper-final" class="pb-20 sm:pb-10 relative">
                @if(isset($treeGen->brackets[$grandFinalRound][1]))
                    @php
                        $match = $treeGen->brackets[$grandFinalRound][1];
                        $match['matchWrapperLeft'] = 0;
                        $match['matchWrapperTop'] = 50;
                    @endphp
                    @include('dash.admin.tournament.partials.tree.brackets.fight')
                @endif
            </div>

        @else
            {{-- ================================================ --}}
            {{-- SINGLE ELIMINATION: STANDARD LAYOUT --}}
            {{-- ================================================ --}}
            <div class="mb-8">
                <div class="bg-blue-500/10 border-l-4 border-blue-500 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-semibold text-white">
                        <i class="fas fa-trophy text-yellow-400 mr-2"></i>
                        Single Elimination Bracket
                    </h3>
                </div>
                {{ $treeGen->printRoundTitles() }}
            </div>

            <div id="brackets-wrapper" class="pb-10 relative"
                style="padding-bottom: {{ ($championship->groupsByRound(1)->count() / 2) * 205 + 100 }}px">
                @foreach ($treeGen->brackets as $roundNumber => $round)
                    @foreach ($round as $matchNumber => $match)
                        @include('dash.admin.tournament.partials.tree.brackets.fight')

                        @if ($roundNumber != count($treeGen->brackets))
                            <div class="vertical-connector"
                                style="top: {{ $match['vConnectorTop'] }}px; left: {{ $match['vConnectorLeft'] }}px; height: {{ $match['vConnectorHeight'] }}px;">
                            </div>
                            <div class="horizontal-connector"
                                style="top: {{ $match['hConnectorTop'] }}px; left: {{ $match['hConnectorLeft'] }}px;">
                            </div>
                            <div class="horizontal-connector"
                                style="top: {{ $match['hConnector2Top'] }}px; left: {{ $match['hConnector2Left'] }}px;">
                            </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        @endif

    </form>

    <div class="clearfix"></div>
@endif

<style>
    /* Pastikan button mobile tidak ikut scroll horizontal */
    @media (max-width: 640px) {
        .fixed.bottom-0 {
            position: fixed !important;
            left: 0 !important;
            right: 0 !important;
            width: 100vw !important;
            margin: 0 !important;
        }

        body {
            padding-bottom: env(safe-area-inset-bottom, 0);
        }

        .fixed.bottom-0 {
            animation: slideUp 0.3s ease-out;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Enhanced connector styles for different brackets */
    .vertical-connector, .horizontal-connector {
        transition: opacity 0.2s ease;
    }

    .match-wrapper:hover ~ .vertical-connector,
    .match-wrapper:hover ~ .horizontal-connector {
        opacity: 0.8;
    }
</style>