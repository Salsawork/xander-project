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
        <h1> {{ $championship->buildName() }}</h1>
    @endif

    {{-- Update Tree Button - Fixed untuk Mobile, Sticky untuk Desktop --}}
    <div class="mb-6">
        {{-- Mobile: Fixed Button di Bottom --}}
        <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-neutral-900 border-t border-gray-700 p-4 z-50 shadow-2xl">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="w-1 h-5 bg-[#1e90ff] rounded flex-shrink-0"></div>
                    <h3 class="text-sm font-semibold text-white truncate">Update Bracket</h3>
                </div>
                <button type="submit" form="tree-form"
                    class="px-5 py-2.5 bg-[#1e90ff] text-white rounded-lg hover:bg-blue-600 transition font-medium text-xs flex items-center justify-center gap-2 shadow-lg flex-shrink-0"
                    id="update-mobile">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </div>

        {{-- Desktop: Sticky di Top --}}
        <div
            class="hidden sm:block sticky top-36 bg-[#2c2c2c] backdrop-blur z-40 py-3 -mx-6 px-6 border-b border-gray-700">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-5 bg-[#1e90ff] rounded"></div>
                    <h3 class="text-base font-semibold text-white">Update Tournament Bracket</h3>
                </div>
                <button type="submit" form="tree-form"
                    class="px-6 py-2 bg-[#1e90ff] text-white rounded-lg hover:bg-blue-600 transition font-medium text-sm flex items-center justify-center gap-2 shadow-lg"
                    id="update-desktop">
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
        {{ $treeGen->printRoundTitles() }}

        <div id="brackets-wrapper" class="pb-20 sm:pb-0"
            style="padding-bottom: {{ ($championship->groupsByRound(1)->count() / 2) * 205 + 100 }}px">
            <!-- 205 px x 2 groups of 2 + 100px extra untuk mobile button -->
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
    </form>

    <div class="clearfix"></div>
@endif

<style>
    /* Pastikan button mobile tidak ikut scroll horizontal */
    @media (max-width: 640px) {

        /* Fixed button tidak terpengaruh overflow-x dari parent */
        .fixed.bottom-0 {
            position: fixed !important;
            left: 0 !important;
            right: 0 !important;
            width: 100vw !important;
            margin: 0 !important;
        }

        /* Tambah padding bottom agar konten tidak tertutup button */
        body {
            padding-bottom: env(safe-area-inset-bottom, 0);
        }

        /* Smooth animation saat muncul */
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
