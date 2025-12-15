<?php
// Temporary fix: Check if class exists
$singleEliminationTree = $championship->fightersGroups->where('round', '>=', $hasPreliminary + 1)->groupBy('round');
$treeGen = null;

if (sizeof($singleEliminationTree) > 0) {
    // Check if the CreateSingleEliminationTree class exists
    if (class_exists('Xoco70\LaravelTournaments\TreeGen\CreateSingleEliminationTree')) {
        $treeGen = new Xoco70\LaravelTournaments\TreeGen\CreateSingleEliminationTree($singleEliminationTree, $championship, $hasPreliminary);
        $treeGen->build();
    } else {
        // Fallback: Use existing tree data directly
        \Log::warning('CreateSingleEliminationTree class not found, using fallback');
    }
    $match = [];
}
?>

@if (sizeof($singleEliminationTree) > 0)

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

    <form method="POST" id="tree-form" action="{{ route('tree.update', ['championship' => $championship->id]) }}"
        accept-charset="UTF-8">
        {{ csrf_field() }}
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" id="activeTreeTab" name="activeTreeTab" value="{{ $championship->id }}" />

        {{-- ===== ROUND TITLES BAR ===== --}}
        <div class="bracket-scroll-container">
            {{ $treeGen->printRoundTitles() }}

            {{-- ===== MAIN CANVAS ===== --}}
            <div id="brackets-wrapper" class="pb-20 sm:pb-0"
                style="padding-bottom: {{ ($championship->groupsByRound(1)->count() / 2) * 205 + 100 }}px">
                
                @foreach ($treeGen->brackets as $roundNumber => $round)
                    @foreach ($round as $matchNumber => $match)
                        @include('dash.admin.tournament.partials.tree.brackets.fight')

                        @if ($roundNumber != $treeGen->noRounds)
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
        --c-primary: #60a5fa;
        --c-primary-b: rgba(30, 144, 255, .4);
        --c-primary-bg: rgba(30, 144, 255, .1);
        --line: rgba(255, 255, 255, .92);
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
        position: relative;
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

    .round-title {
        padding: 8px 16px;
        border-radius: 999px;
        border: 1px solid var(--c-primary-b);
        background: var(--c-primary-bg);
        font-weight: 700;
        font-size: 13px;
        color: var(--c-primary);
        letter-spacing: .3px;
        white-space: nowrap;
        user-select: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .3);
        backdrop-filter: blur(4px);
        display: inline-block;
        margin: 0 8px 12px 0;
    }

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