<?php
use Xoco70\LaravelTournaments\TreeGen\CreateDoubleEliminationTree;

$doubleEliminationTree = $championship->fightersGroups
    ->where('round', '>=', $hasPreliminary + 1)
    ->groupBy('round');

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

    {{-- ===== ACTION BAR (tetap) ===== --}}
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

        {{-- ===== POSISI & PENYELARASAN KOLOM ===== --}}
        @php
            $boxW = (int)($treeGen->matchWrapperWidth ?? 320);
            $boxH = (int)($treeGen->matchWrapperHeight ?? 80);

            // ---------- Global shift (padding) ----------
            $minLeft = PHP_INT_MAX; $minTop = PHP_INT_MAX;
            $scanMin = function($rounds) use (&$minLeft,&$minTop){
                foreach ($rounds as $r) foreach ($r as $m){
                    $minLeft = min($minLeft, (int)($m['matchWrapperLeft'] ?? 0));
                    $minTop  = min($minTop,  (int)($m['matchWrapperTop']  ?? 0));
                    foreach (['vConnectorLeft','hConnectorLeft','hConnector2Left'] as $k) if (isset($m[$k])) $minLeft = min($minLeft,(int)$m[$k]);
                    foreach (['vConnectorTop','hConnectorTop','hConnector2Top']   as $k) if (isset($m[$k])) $minTop  = min($minTop,(int)$m[$k]);
                }
            };
            $scanMin($treeGen->upperBrackets ?? []);
            $scanMin($treeGen->lowerBrackets ?? []);
            if (!empty($treeGen->grandFinal)){
                $minLeft = min($minLeft, (int)($treeGen->grandFinal['matchWrapperLeft'] ?? 0));
                $minTop  = min($minTop,  (int)($treeGen->grandFinal['matchWrapperTop']  ?? 0));
            }
            if ($minLeft === PHP_INT_MAX) $minLeft = 0;
            if ($minTop  === PHP_INT_MAX) $minTop  = 0;

            $padLeft = 24; $padTop = 16;
            $globalXShift = $padLeft - $minLeft;
            $globalYShift = $padTop  - $minTop;

            // ---------- Kolom upper ----------
            $upperKeys = array_keys($treeGen->upperBrackets ?? []);
            sort($upperKeys, SORT_NUMERIC);
            $upperColLeftsRaw = [];
            foreach ($upperKeys as $rk){
                $minL = PHP_INT_MAX;
                foreach ($treeGen->upperBrackets[$rk] as $m) $minL = min($minL, (int)($m['matchWrapperLeft'] ?? 0));
                if ($minL === PHP_INT_MAX) continue;
                $upperColLeftsRaw[] = $minL;
            }
            $upperCols = count($upperColLeftsRaw);

            // ---------- Jarak kolom (colGap) dari upper ----------
            $colGap = $boxW + 80; // fallback
            if ($upperCols >= 2){
                $gaps = [];
                for ($i=1;$i<$upperCols;$i++) $gaps[] = $upperColLeftsRaw[$i]-$upperColLeftsRaw[$i-1];
                sort($gaps);
                $colGap = (int)round($gaps[intdiv(count($gaps),2)]);
                if ($colGap < $boxW + 40) $colGap = $boxW + 40;
            }

            // ---------- Target LB R1 = setelah kolom upper terakhir ----------
            $desiredLowerStartRaw = (count($upperColLeftsRaw) ? $upperColLeftsRaw[0] : 0) + $upperCols * $colGap;

            // ---------- Posisi aktual LB R1 (raw) ----------
            $lowerKeys = array_keys($treeGen->lowerBrackets ?? []);
            sort($lowerKeys, SORT_NUMERIC);
            $firstLowerKey = $lowerKeys[0] ?? null;
            $lowerR1LeftRaw = null;
            if (!is_null($firstLowerKey)){
                $minLL = PHP_INT_MAX;
                foreach ($treeGen->lowerBrackets[$firstLowerKey] as $m) $minLL = min($minLL, (int)($m['matchWrapperLeft'] ?? 0));
                if ($minLL !== PHP_INT_MAX) $lowerR1LeftRaw = $minLL;
            }

            // ---------- Shift lower agar LB R1 tepat di kolom target ----------
            $lowerShiftToLB1Raw = 0;
            if (!is_null($lowerR1LeftRaw)){
                $lowerShiftToLB1Raw = $desiredLowerStartRaw - $lowerR1LeftRaw;
            }

            // ---------- Penanda round lower terakhir ----------
            $lowerBracketEnd = $treeGen->lowerBracketEnd ?? (count($lowerKeys) ? max($lowerKeys) : null);

            // ---------- Pusat judul (bar atas) ----------
            $titleCenters = [];
            $titleLabels  = [];

            $niceUpper = ['Round 1','Upper Quarter','Upper Semi','Upper Final'];
            foreach ($upperColLeftsRaw as $i => $lRaw){
                $cx = (int)round($lRaw + $globalXShift + $boxW/2);
                $titleCenters[] = $cx;
                $titleLabels[]  = $niceUpper[$i] ?? ('Upper R'.($i+1));
            }

            foreach ($lowerKeys as $idx => $rk){
                $colLeftRaw = $desiredLowerStartRaw + $idx * $colGap;
                $cx = (int)round($colLeftRaw + $globalXShift + $boxW/2);
                $titleCenters[] = $cx;
                if ($idx === count($lowerKeys)-2)       $titleLabels[] = 'Lower Semi';
                elseif ($idx === count($lowerKeys)-1)   $titleLabels[] = 'Lower Final';
                else                                    $titleLabels[] = 'LB R'.($idx+1);
            }

            // ---------- Grand Final kolom & center ----------
            $gfLeftRaw  = $desiredLowerStartRaw + count($lowerKeys) * $colGap;

            // ---------- Ukuran kanvas ----------
            $maxRight = 0; $maxBottom = 0;

            foreach ($treeGen->upperBrackets ?? [] as $r){
                foreach ($r as $m){
                    $left = (int)($m['matchWrapperLeft'] ?? 0) + $globalXShift;
                    $top  = (int)($m['matchWrapperTop']  ?? 0) + $globalYShift;
                    $maxRight  = max($maxRight,  $left + $boxW);
                    $maxBottom = max($maxBottom, $top + $boxH);
                }
            }
            foreach ($treeGen->lowerBrackets ?? [] as $r){
                foreach ($r as $m){
                    $left = (int)($m['matchWrapperLeft'] ?? 0) + $globalXShift + $lowerShiftToLB1Raw;
                    $top  = (int)($m['matchWrapperTop']  ?? 0) + $globalYShift;
                    $maxRight  = max($maxRight,  $left + $boxW);
                    $maxBottom = max($maxBottom, $top + $boxH);
                }
            }
            if (!empty($treeGen->grandFinal)){
                $gfTopRaw  = (int)($treeGen->grandFinal['matchWrapperTop']  ?? 0);
                $gfLeftNew = $gfLeftRaw + $globalXShift;
                $gfTopNew  = $gfTopRaw + $globalYShift;
                $maxRight  = max($maxRight,  $gfLeftNew + $boxW);
                $maxBottom = max($maxBottom, $gfTopNew + $boxH);
            }

            $canvasW = $maxRight + 60;
            $canvasH = $maxBottom + 60;

            // ===== Helper: lebar horizontal connector agar pasti nyambung =====
            $halfBridgeFallback = max(16, (int)floor(($colGap - $boxW) / 2));
            $getHWidth = function(array $m, string $key) use ($halfBridgeFallback) {
                return (int)($m[$key] ?? $halfBridgeFallback);
            };
        @endphp

        {{-- ===== BAR JUDUL ROUND (Sejajar) ===== --}}
        <div class="bracket-scroll-container">
            <div id="round-titles-wrapper" style="position:relative; height:42px; width: {{ $canvasW }}px; min-width:100%; margin-bottom:10px;">
                @foreach ($titleCenters as $i => $cx)
                    @php
                        $class = 'upper-round';
                        if ($i >= count($upperKeys) && $i < (count($upperKeys) + count($lowerKeys))) $class = 'lower-round';
                        if ($i === count($titleCenters)-1) $class = 'grand-final-round';
                    @endphp
                    <div class="round-title {{ $class }}" style="left: {{ $cx }}px; transform: translateX(-50%); top:0; position:absolute;">
                        {{ $titleLabels[$i] ?? ('Round '.($i+1)) }}
                    </div>
                @endforeach
            </div>

            {{-- ===== KANVAS BRACKET ===== --}}
            <div id="brackets-wrapper" style="position:relative; width: {{ $canvasW }}px; height: {{ $canvasH }}px; min-width:100%;">
                {{-- -------- UPPER -------- --}}
                @foreach ($treeGen->upperBrackets as $roundNumber => $round)
                    @foreach ($round as $matchNumber => $match)
                        @php
                            $match['matchWrapperTop']  = (int)($match['matchWrapperTop']  ?? 0) + $globalYShift;
                            $match['matchWrapperLeft'] = (int)($match['matchWrapperLeft'] ?? 0) + $globalXShift;

                            foreach (['vConnectorTop','hConnectorTop','hConnector2Top'] as $k)
                                if (isset($match[$k])) $match[$k] = (int)$match[$k] + $globalYShift;
                            foreach (['vConnectorLeft','hConnectorLeft','hConnector2Left'] as $k)
                                if (isset($match[$k])) $match[$k] = (int)$match[$k] + $globalXShift;

                            $roundNumber = $match['roundNumber'] ?? $roundNumber;

                            $hW  = $getHWidth($match, 'hConnectorWidth');
                            $hW2 = $getHWidth($match, 'hConnector2Width');
                        @endphp

                        @include('dash.admin.tournament.partials.tree.brackets.fight', ['match' => $match])

                        @if ($roundNumber != $treeGen->upperBracketEnd)
                            <div class="vertical-connector"
                                 style="top: {{ (int)$match['vConnectorTop'] }}px; left: {{ (int)$match['vConnectorLeft'] }}px; height: {{ (int)$match['vConnectorHeight'] }}px;"></div>
                            <div class="horizontal-connector"
                                 style="top: {{ (int)$match['hConnectorTop'] }}px; left: {{ (int)$match['hConnectorLeft'] }}px; width: {{ $hW }}px;"></div>
                            <div class="horizontal-connector"
                                 style="top: {{ (int)$match['hConnector2Top'] }}px; left: {{ (int)$match['hConnector2Left'] }}px; width: {{ $hW2 }}px;"></div>
                        @endif
                    @endforeach
                @endforeach

                {{-- -------- LOWER (shifting ke kolom LB R1) -------- --}}
                @if (!empty($treeGen->lowerBrackets))
                    @foreach ($treeGen->lowerBrackets as $roundNumber => $round)
                        @foreach ($round as $matchNumber => $match)
                            @php
                                $roundNumber = $match['roundNumber'] ?? $roundNumber;

                                $match['matchWrapperTop']  = (int)($match['matchWrapperTop']  ?? 0) + $globalYShift;
                                $match['matchWrapperLeft'] = (int)($match['matchWrapperLeft'] ?? 0) + $globalXShift + $lowerShiftToLB1Raw;

                                foreach (['vConnectorTop','hConnectorTop','hConnector2Top'] as $k)
                                    if (isset($match[$k])) $match[$k] = (int)$match[$k] + $globalYShift;
                                foreach (['vConnectorLeft','hConnectorLeft','hConnector2Left'] as $k)
                                    if (isset($match[$k])) $match[$k] = (int)$match[$k] + $globalXShift + $lowerShiftToLB1Raw;

                                $isAWinner = (optional($match['playerA'])->id == ($match['winner_id'] ?? null) && !empty($match['winner_id'])) ? 'X' : null;
                                $isBWinner = (optional($match['playerB'])->id == ($match['winner_id'] ?? null) && !empty($match['winner_id'])) ? 'X' : null;

                                $hW  = $getHWidth($match, 'hConnectorWidth');
                                $hW2 = $getHWidth($match, 'hConnector2Width');
                            @endphp

                            <div class="match-wrapper lower-bracket-match"
                                 style="top: {{ (int)$match['matchWrapperTop'] }}px; left: {{ (int)$match['matchWrapperLeft'] }}px; width: {{ $boxW }}px;">
                                <div {{ $isAWinner ? "id=success" : '' }}>
                                    <input type="text" class="score" name="score[]" value="{{ $isAWinner }}">
                                    @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                        'selected'    => $match['playerA'],
                                        'roundNumber' => $roundNumber,
                                        'isSuccess'   => $isAWinner,
                                        'treeGen'     => $treeGen
                                    ])
                                </div>
                                <div class="match-divider"></div>
                                <div {{ $isBWinner ? "id=success" : '' }}>
                                    <input type="text" class="score" name="score[]" value="{{ $isBWinner }}">
                                    @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                        'selected'    => $match['playerB'],
                                        'roundNumber' => $roundNumber,
                                        'isSuccess'   => $isBWinner,
                                        'treeGen'     => $treeGen
                                    ])
                                </div>
                            </div>

                            {{-- ===== CONNECTORS LOWER: gambar jika bukan lower final ===== --}}
                            @if (!is_null($lowerBracketEnd) && $roundNumber != $lowerBracketEnd)
                                <div class="vertical-connector lower-connector"
                                     style="top: {{ (int)$match['vConnectorTop'] }}px; left: {{ (int)$match['vConnectorLeft'] }}px; height: {{ (int)$match['vConnectorHeight'] }}px;"></div>
                                <div class="horizontal-connector lower-connector"
                                     style="top: {{ (int)$match['hConnectorTop'] }}px; left: {{ (int)$match['hConnectorLeft'] }}px; width: {{ $hW }}px;"></div>
                                <div class="horizontal-connector lower-connector"
                                     style="top: {{ (int)$match['hConnector2Top'] }}px; left: {{ (int)$match['hConnector2Left'] }}px; width: {{ $hW2 }}px;"></div>
                            @endif

                        @endforeach
                    @endforeach
                @endif

                {{-- -------- GRAND FINAL (override ke kolom GF) -------- --}}
                @if (!empty($treeGen->grandFinal))
                    @php
                        $gf = $treeGen->grandFinal;
                        $gfTop  = (int)($gf['matchWrapperTop']  ?? 0) + $globalYShift;
                        $gfLeft = (int)$gfLeftRaw + $globalXShift;
                        $gfMidY = $gfTop + ($boxH/2);

                        $isAW = (optional($gf['playerA'])->id == ($gf['winner_id'] ?? null) && !empty($gf['winner_id'])) ? 'X' : null;
                        $isBW = (optional($gf['playerB'])->id == ($gf['winner_id'] ?? null) && !empty($gf['winner_id'])) ? 'X' : null;
                    @endphp

                    <div class="match-wrapper grand-final-match"
                         style="top: {{ $gfTop }}px; left: {{ $gfLeft }}px; width: {{ $boxW }}px;">
                        <div {{ $isAW ? "id=success" : '' }}>
                            <input type="text" class="score" name="score[]" value="{{ $isAW }}">
                            @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                'selected'    => $gf['playerA'],
                                'roundNumber' => $gf['roundNumber'],
                                'isSuccess'   => $isAW,
                                'treeGen'     => $treeGen
                            ])
                        </div>
                        <div class="match-divider"></div>
                        <div {{ $isBW ? "id=success" : '' }}>
                            <input type="text" class="score" name="score[]" value="{{ $isBW }}">
                            @include('dash.admin.tournament.partials.tree.brackets.playerList', [
                                'selected'    => $gf['playerB'],
                                'roundNumber' => $gf['roundNumber'],
                                'isSuccess'   => $isBW,
                                'treeGen'     => $treeGen
                            ])
                        </div>
                    </div>

                    {{-- ===== CONNECTOR: Upper Final -> Grand Final ===== --}}
                    @php
                        $upperFinal = null;
                        if (!empty($treeGen->upperBrackets)) {
                            $lastUK = max(array_keys($treeGen->upperBrackets));
                            $upperFinal = $treeGen->upperBrackets[$lastUK];
                            $upperFinal = is_array($upperFinal) ? reset($upperFinal) : null;
                        }
                    @endphp
                    @if ($upperFinal)
                        @php
                            $uX1   = (int)($upperFinal['matchWrapperLeft'] ?? 0) + $globalXShift + $boxW;
                            $uY1   = (int)($upperFinal['matchWrapperTop']  ?? 0) + $globalYShift + ($boxH/2);
                            $uMidX = $uX1 + max(16, intdiv(($gfLeft - $uX1), 2));
                        @endphp
                        <div class="horizontal-connector gf-connector" style="left: {{ $uX1 }}px; top: {{ $uY1 }}px; width: {{ $uMidX - $uX1 }}px;"></div>
                        @if ($uY1 != $gfMidY)
                            <div class="vertical-connector gf-connector" style="left: {{ $uMidX }}px; top: {{ min($uY1, $gfMidY) }}px; height: {{ abs($gfMidY - $uY1) }}px;"></div>
                        @endif
                        <div class="horizontal-connector gf-connector" style="left: {{ $uMidX }}px; top: {{ $gfMidY }}px; width: {{ $gfLeft - $uMidX }}px;"></div>
                    @endif

                    {{-- ===== CONNECTOR: Lower Final -> Grand Final ===== --}}
                    @php
                        $lowerFinal = null;
                        if (!empty($treeGen->lowerBrackets)) {
                            $lastLK = max(array_keys($treeGen->lowerBrackets));
                            $lowerFinal = $treeGen->lowerBrackets[$lastLK];
                            $lowerFinal = is_array($lowerFinal) ? reset($lowerFinal) : null;
                        }
                    @endphp
                    @if ($lowerFinal)
                        @php
                            $lX1   = (int)($lowerFinal['matchWrapperLeft'] ?? 0) + $globalXShift + $lowerShiftToLB1Raw + $boxW;
                            $lY1   = (int)($lowerFinal['matchWrapperTop']  ?? 0) + $globalYShift + ($boxH/2);
                            $lMidX = $lX1 + max(16, intdiv(($gfLeft - $lX1), 2));
                        @endphp
                        <div class="horizontal-connector gf-connector" style="left: {{ $lX1 }}px; top: {{ $lY1 }}px; width: {{ $lMidX - $lX1 }}px;"></div>
                        @if ($lY1 != $gfMidY)
                            <div class="vertical-connector gf-connector" style="left: {{ $lMidX }}px; top: {{ min($lY1, $gfMidY) }}px; height: {{ abs($gfMidY - $lY1) }}px;"></div>
                        @endif
                        <div class="horizontal-connector gf-connector" style="left: {{ $lMidX }}px; top: {{ $gfMidY }}px; width: {{ $gfLeft - $lMidX }}px;"></div>
                    @endif

                @endif {{-- end if grandFinal --}}
            </div>
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

{{-- ===== STYLE (dirapikan) ===== --}}
<style>
    :root{
        --c-bg: #111214;
        --c-panel: rgba(30,30,30,.92);
        --c-border: rgba(255,255,255,.1);
        --c-text: #e5e7eb;

        --c-upper: #60a5fa;        /* upper label text */
        --c-upper-b: rgba(30,144,255,.35);
        --c-upper-bg: rgba(30,144,255,.09);

        --c-lower: #fca5a5;        /* lower label text */
        --c-lower-b: rgba(239,68,68,.35);
        --c-lower-bg: rgba(239,68,68,.09);

        --c-gf: #fde68a;           /* grand final label text */
        --c-gf-b: rgba(234,179,8,.40);
        --c-gf-bg: rgba(234,179,8,.10);

        --line: rgba(255,255,255,.88);
        --line-lower: rgba(239,68,68,.92);
        --line-thick: 2px;
    }

    .bracket-scroll-container{
        width:100%;
        overflow-x:auto; overflow-y:visible;
        padding:10px 0; background:transparent;
        scroll-behavior: smooth;
        /* Scrollbar rapi */
    }
    .bracket-scroll-container::-webkit-scrollbar{ height:10px; }
    .bracket-scroll-container::-webkit-scrollbar-track{ background:transparent; }
    .bracket-scroll-container::-webkit-scrollbar-thumb{
        background:linear-gradient(90deg, rgba(255,255,255,.12), rgba(255,255,255,.18));
        border-radius:999px;
    }

    /* Grid halus agar tata letak terlihat rapi */
    #brackets-wrapper{
        background:
            repeating-linear-gradient(
                to right,
                rgba(255,255,255,.03), rgba(255,255,255,.03) 1px,
                transparent 1px, transparent 60px
            ),
            linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.02));
        border-radius: 12px;
    }

    .match-wrapper{
        position:absolute;
        background:var(--c-panel);
        border:2px solid var(--c-border);
        border-radius:10px;
        padding:4px;
        transition:all .2s ease;
        z-index:2;
        backdrop-filter: blur(2px);
    }
    .match-wrapper:hover{
        border-color:rgba(30,144,255,.5);
        box-shadow:0 4px 12px rgba(30,144,255,.2);
        transform:translateY(-2px);
    }
    .lower-bracket-match{ border-color:rgba(239,68,68,.25); }
    .lower-bracket-match:hover{ border-color:rgba(239,68,68,.5); box-shadow:0 4px 12px rgba(239,68,68,.2); }
    .grand-final-match{ border-color:rgba(234,179,8,.35); background:rgba(30,30,30,.95); }
    .match-divider{ height:2px; background:rgba(255,255,255,.08); margin:2px 0; border-radius:2px; }

    /* Garis connector rapi & crisp */
    .vertical-connector,.horizontal-connector{
        position:absolute; z-index:1; pointer-events:none;
        background:var(--line);
        border-radius:2px;
        shape-rendering:geometricPrecision;
        image-rendering: -webkit-optimize-contrast;
        will-change: auto;
    }
    .vertical-connector{ width:var(--line-thick); }
    .horizontal-connector{ height:var(--line-thick); }
    .vertical-connector.lower-connector,.horizontal-connector.lower-connector{
        background:var(--line-lower);
        box-shadow:0 0 0 1px rgba(239,68,68,.15);
    }

    /* Grand Final connectors highlight (emas) */
    .horizontal-connector.gf-connector,
    .vertical-connector.gf-connector{
        background: linear-gradient(90deg, rgba(234,179,8,.9), rgba(234,179,8,.6));
        box-shadow:0 0 0 1px rgba(234,179,8,.2);
    }

    .match-wrapper .score{
        width:28px;height:28px;flex:0 0 28px;text-align:center;border-radius:8px;
        border:1px solid #2f2f2f;background:#0f0f0f;color:#f3f4f6;font-weight:800;line-height:26px;padding:0;
    }
    .match-wrapper > div#success .score{
        border-color:rgba(34,197,94,.6); background:rgba(34,197,94,.12); color:#86efac;
    }

    /* Round titles (chip) */
    .round-title{
        padding: 6px 14px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,.12);
        background: rgba(30,30,30,.85);
        font-weight: 700; font-size: 12px; color: var(--c-text);
        letter-spacing:.2px;
        white-space: nowrap; user-select: none;
        box-shadow: 0 2px 6px rgba(0,0,0,.25);
        backdrop-filter: blur(4px);
    }
    .round-title.upper-round{ border-color: var(--c-upper-b); background: var(--c-upper-bg); color: var(--c-upper); }
    .round-title.lower-round{ border-color: var(--c-lower-b); background: var(--c-lower-bg); color: var(--c-lower); }
    .round-title.grand-final-round{ border-color: var(--c-gf-b); background: var(--c-gf-bg); color: var(--c-gf); }

    @media (max-width:640px){
        .round-title{ font-size:11px; padding:5px 10px }
        .fixed.bottom-0{ position:fixed !important; left:0 !important; right:0 !important; width:100vw !important; margin:0 !important; }
        body{ padding-bottom: env(safe-area-inset-bottom, 0); }
        .fixed.bottom-0{ animation: slideUp .3s ease-out; }
        @keyframes slideUp { from { transform:translateY(100%); opacity:0 } to { transform:translateY(0); opacity:1 } }
    }
</style>
