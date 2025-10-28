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
            'trace' => $e->getTraceAsString()
        ]);
        $treeGen = null;
    }
}
?>

@if (sizeof($doubleEliminationTree) > 0 && $treeGen)

    @if (Request::is('championships/' . $championship->id . '/pdf'))
        <h1>{{ $championship->buildName() }}</h1>
    @endif

    {{-- Update Tree Button - Same as Single Elimination --}}
    <div class="mb-6">
        {{-- Mobile: Fixed Button at Bottom --}}
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

        {{-- Desktop: Sticky at Top --}}
        <div class="hidden sm:block sticky top-36 bg-[#2c2c2c] backdrop-blur z-40 py-3 -mx-6 px-6 border-b border-gray-700">
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

    <form method="POST" id="tree-form" action="{{ route('tree.update', ['championship' => $championship->id]) }}"
        accept-charset="UTF-8">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" id="activeTreeTab" name="activeTreeTab" value="{{ $championship->id }}" />

        {{-- Round Titles - EXACTLY like Single Elimination --}}
        {{ $treeGen->printRoundTitles() }}

        {{-- Brackets Wrapper - EXACTLY like Single Elimination structure --}}
        <div id="brackets-wrapper" class="pb-20 sm:pb-0"
            style="padding-bottom: {{ ($championship->groupsByRound(1)->count() / 2) * 205 + 100 }}px">
            
            {{-- 
                UPPER BRACKET SECTION (Round 1 + Upper Rounds)
                Uses EXACT same rendering as Single Elimination
            --}}
            @foreach ($treeGen->upperBrackets as $roundNumber => $round)
                @foreach ($round as $matchNumber => $match)
                    @php
                        $roundNumber = $match['roundNumber'] ?? $roundNumber;
                    @endphp
                    
                    {{-- Match Box - Use shared partial --}}
                    @include('dash.admin.tournament.partials.tree.brackets.fight')

                    {{-- Connectors - EXACTLY like Single Elimination --}}
                    @if ($roundNumber != $treeGen->upperBracketEnd)
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

            {{-- 
                LOWER BRACKET SECTION
                Uses SAME structure, just positioned below
            --}}
            @if (!empty($treeGen->lowerBrackets))
                @foreach ($treeGen->lowerBrackets as $roundNumber => $round)
                    @foreach ($round as $matchNumber => $match)
                        @php
                            $roundNumber = $match['roundNumber'] ?? $roundNumber;
                            $isLastLowerRound = ($roundNumber == $treeGen->totalRounds - 1);
                        @endphp
                        
                        {{-- Match Box with slight red tint for lower bracket --}}
                        <div class="match-wrapper lower-bracket-match"
                             style="top: {{ $match['matchWrapperTop'] }}px; left: {{ $match['matchWrapperLeft'] }}px; width: {{ $treeGen->matchWrapperWidth }}px;">
                            @php
                                $isAWinner = (optional($match['playerA'])->id == $match['winner_id'] && $match['winner_id'] != null) ? 'X' : null;
                                $isBWinner = (optional($match['playerB'])->id == $match['winner_id'] && $match['winner_id'] != null) ? 'X' : null;
                            @endphp
                            
                            <div {{ $isAWinner ? "id=success" : '' }}>
                                <input type="text" class="score" name="score[]" value="{{ $isAWinner }}" 
                                    {{ $isAWinner ? "id=success" : '' }} 
                                    class="rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-600 placeholder-gray-500">
                                @include('dash.admin.tournament.partials.tree.brackets.playerList',
                                    ['selected' => $match['playerA'],
                                    'roundNumber' => $roundNumber,
                                    'isSuccess' => $isAWinner,
                                    'treeGen' => $treeGen
                                    ])
                            </div>
                            
                            <div class="match-divider"></div>
                            
                            <div {{ $isBWinner ? "id=success" : '' }}>
                                <input type="text" class="score" name="score[]"
                                    class="rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-600 placeholder-gray-500"
                                    value="{{ $isBWinner }}" {{ $isBWinner ? "bg-success-300" : "" }}>
                                @include('dash.admin.tournament.partials.tree.brackets.playerList',
                                    ['selected' => $match['playerB'],
                                     'roundNumber' => $roundNumber,
                                     'isSuccess' => $isBWinner,
                                     'treeGen' => $treeGen
                                      ])
                            </div>
                        </div>

                        {{-- Connectors for lower bracket --}}
                        @if (!$isLastLowerRound)
                            <div class="vertical-connector lower-connector"
                                style="top: {{ $match['vConnectorTop'] }}px; left: {{ $match['vConnectorLeft'] }}px; height: {{ $match['vConnectorHeight'] }}px;">
                            </div>
                            <div class="horizontal-connector lower-connector"
                                style="top: {{ $match['hConnectorTop'] }}px; left: {{ $match['hConnectorLeft'] }}px;">
                            </div>
                            <div class="horizontal-connector lower-connector"
                                style="top: {{ $match['hConnector2Top'] }}px; left: {{ $match['hConnector2Left'] }}px;">
                            </div>
                        @endif
                    @endforeach
                @endforeach
            @endif

            {{-- 
                GRAND FINAL
                Rendered last, uses same match-wrapper style
            --}}
            @if (!empty($treeGen->grandFinal))
                @php
                    $match = $treeGen->grandFinal;
                    $isAWinner = (optional($match['playerA'])->id == $match['winner_id'] && $match['winner_id'] != null) ? 'X' : null;
                    $isBWinner = (optional($match['playerB'])->id == $match['winner_id'] && $match['winner_id'] != null) ? 'X' : null;
                @endphp
                
                <div class="match-wrapper grand-final-match"
                     style="top: {{ $match['matchWrapperTop'] }}px; left: {{ $match['matchWrapperLeft'] }}px; width: {{ $treeGen->matchWrapperWidth }}px;">
                    <div {{ $isAWinner ? "id=success" : '' }}>
                        <input type="text" class="score" name="score[]" value="{{ $isAWinner }}" 
                            {{ $isAWinner ? "id=success" : '' }} 
                            class="rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-600 placeholder-gray-500">
                        @include('dash.admin.tournament.partials.tree.brackets.playerList',
                            ['selected' => $match['playerA'],
                            'roundNumber' => $match['roundNumber'],
                            'isSuccess' => $isAWinner,
                            'treeGen' => $treeGen
                            ])
                    </div>
                    
                    <div class="match-divider"></div>
                    
                    <div {{ $isBWinner ? "id=success" : '' }}>
                        <input type="text" class="score" name="score[]"
                            class="rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-600 placeholder-gray-500"
                            value="{{ $isBWinner }}" {{ $isBWinner ? "bg-success-300" : "" }}>
                        @include('dash.admin.tournament.partials.tree.brackets.playerList',
                            ['selected' => $match['playerB'],
                             'roundNumber' => $match['roundNumber'],
                             'isSuccess' => $isBWinner,
                             'treeGen' => $treeGen
                              ])
                    </div>
                </div>
            @endif

        </div>
    </form>

    <div class="clearfix"></div>
    
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

{{-- 
    STYLES - EXACTLY like Single Elimination
    Only add specific styling for lower bracket differentiation
--}}
<style>
    /* Match wrapper - base style */
    .match-wrapper {
        position: absolute;
        background: rgba(30, 30, 30, 0.9);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 4px;
        transition: all 0.2s ease;
    }

    .match-wrapper:hover {
        border-color: rgba(30, 144, 255, 0.5);
        box-shadow: 0 4px 12px rgba(30, 144, 255, 0.2);
        transform: translateY(-2px);
    }

    /* Lower bracket - subtle red tint */
    .lower-bracket-match {
        border-color: rgba(239, 68, 68, 0.2);
    }

    .lower-bracket-match:hover {
        border-color: rgba(239, 68, 68, 0.5);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
    }

    /* Grand final - gold tint */
    .grand-final-match {
        border-color: rgba(234, 179, 8, 0.3);
        background: rgba(30, 30, 30, 0.95);
    }

    .grand-final-match:hover {
        border-color: rgba(234, 179, 8, 0.6);
        box-shadow: 0 4px 16px rgba(234, 179, 8, 0.3);
    }

    /* Match divider */
    .match-divider {
        height: 2px;
        background: rgba(255, 255, 255, 0.1);
        margin: 2px 0;
    }

    /* Vertical connectors */
    .vertical-connector {
        position: absolute;
        width: 3px;
        background-color: rgba(255, 255, 255, 0.2);
        transition: opacity 0.2s ease;
    }

    .vertical-connector.lower-connector {
        background-color: rgba(239, 68, 68, 0.3);
    }

    /* Horizontal connectors */
    .horizontal-connector {
        position: absolute;
        height: 3px;
        width: 20px;
        background-color: rgba(255, 255, 255, 0.2);
        transition: opacity 0.2s ease;
    }

    .horizontal-connector.lower-connector {
        background-color: rgba(239, 68, 68, 0.3);
    }

    /* Round titles */
    #round-titles-wrapper {
        position: relative;
        height: 40px;
        margin-bottom: 16px;
    }

    .round-title {
        position: absolute;
        top: 0;
        padding: 8px 16px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(30, 30, 30, 0.9);
        font-weight: 600;
        font-size: 13px;
        text-align: center;
        min-width: 120px;
        color: #e5e7eb;
        backdrop-filter: blur(10px);
    }

    /* Color coding for round titles */
    .round-title.upper-round {
        border-color: rgba(30, 144, 255, 0.3);
        background: rgba(30, 144, 255, 0.1);
        color: #60a5fa;
    }

    .round-title.lower-round {
        border-color: rgba(239, 68, 68, 0.3);
        background: rgba(239, 68, 68, 0.1);
        color: #f87171;
    }

    .round-title.grand-final-round {
        border-color: rgba(234, 179, 8, 0.3);
        background: rgba(234, 179, 8, 0.1);
        color: #fbbf24;
    }

    /* Mobile styles - EXACTLY like Single Elimination */
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
</style>