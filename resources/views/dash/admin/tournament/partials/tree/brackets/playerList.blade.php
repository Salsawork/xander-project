<?php
$prefix = "singleElimination";
if ($championship->hasPreliminary() && $roundNumber == 1) {
    $prefix = "preliminary";
}
$className = $prefix . "_select";
$selectName = $prefix . "_fighters[]";

// CRITICAL: Get active fighters for current round (excludes eliminated fighters)
// Check if $treeGen is available, otherwise fallback to all fighters
if (isset($treeGen) && method_exists($treeGen, 'getActiveFightersForRound')) {
    $availableFighters = $treeGen->getActiveFightersForRound($roundNumber);
} else {
    $availableFighters = $championship->fighters;
}
?>
<!-- r = round, m = match, f = fighter -->
@if (isset($show_tree))
    {{  $fighter->fullName }}
@else
    <select name="{{ $selectName }}" class="{{$className}} rounded-md border border-gray-600 text-gray-600 bg-transparent px-3 py-2 text-gray-300 " {{ $isSuccess ? "id=success" : '' }}>
        <option {{ $selected == '' ? ' selected' : '' }} ></option>
        
        {{-- NEW: Only show fighters who are still active (not eliminated in previous rounds) --}}
        @foreach ($availableFighters as $fighter)
            @if ($fighter != null)
                <option {{ $selected != null && $selected->id == $fighter->id ? ' selected' : '' }}  value="{{$fighter->id ?? null }}">
                    {{  $fighter->fullName }}
                </option>
            @endif
        @endforeach
    </select>
@endif