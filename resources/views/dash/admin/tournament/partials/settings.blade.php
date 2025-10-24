{{-- NOTE: Tree Type options updated to reflect Double Elimination --}}
{{-- MODIFIED: Playoff option now labeled as "Double Elimination" --}}

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="col-span-1">
        <label for="hasPreliminary" class="block text-sm font-medium text-gray-700">Preliminary</label>
        <input name="hasPreliminary" type="hidden" value="0">
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="hasPreliminary" name="hasPreliminary" :disabled="isPrelimDisabled" v-model="hasPrelim"
            v-on:change="prelim()">
            <option value="0" {{ $hasPreliminary == 0 ? 'selected' : '' }}>NO</option>
            <option value="1" {{ $hasPreliminary == 1 ? 'selected' : '' }}>YES</option>
        </select>
    </div>

    <div class="col-span-1">
        <label for="preliminaryGroupSize"
            class="block text-sm font-medium text-gray-700">{{ trans('laravel-tournaments::core.preliminaryGroupSize') }}</label>
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="preliminaryGroupSize" name="preliminaryGroupSize" :disabled="isGroupSizeDisabled">
            <option value="3" @if ($setting->preliminaryGroupSize == 3) selected @endif>3</option>
            <option value="4" @if ($setting->preliminaryGroupSize == 4) selected @endif>4</option>
            <option value="5" @if ($setting->preliminaryGroupSize == 5) selected @endif>5</option>
        </select>
    </div>

    <div class="col-span-1">
        <label for="numFighters" class="block text-sm font-medium text-gray-700">Fighter Qty</label>
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="numFighters" name="numFighters">
            @for ($i = 2; $i <= 256; $i++)
                <option value="{{ $i }}" 
                    @if ($numFighters == $i) selected @endif
                    @if ($i > 128) class="text-yellow-400" @endif>
                    {{ $i }}
                    @if (in_array($i, [4, 8, 16, 32, 64, 128, 256]))
                        - Full Bracket
                    @endif
                </option>
            @endfor
        </select>
        <p class="mt-1 text-xs text-gray-400">
            <i class="fas fa-info-circle"></i> 
            Full brackets (4,8,16,32,64,128,256) will have most balanced matches
        </p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
    <div class="col-span-1">
        <label for="isTeam" class="block text-sm font-medium text-gray-700">Team?</label>
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="isTeam" name="isTeam">
            <option value="0" {{ $isTeam == 0 ? 'selected' : '' }}>NO</option>
            <option value="1" {{ $isTeam == 1 ? 'selected' : '' }}>YES</option>
        </select>
    </div>

    <div class="col-span-1">
        <label for="treeType" class="block text-sm font-medium text-gray-700">
            Tournament Format
            <span class="text-xs text-gray-500 ml-1">(Type)</span>
        </label>
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="treeType" name="treeType" v-model="tree" v-on:change="treeType()">
            {{-- MODIFIED: Changed "Playoff" to "Double Elimination" --}}
            <option value="0" @if ($setting->treeType == 0) selected @endif>
                Double Elimination (Second Chance)
            </option>
            <option value="1" @if ($setting->treeType == 1) selected @endif>
                Single Elimination (One & Done)
            </option>
        </select>
        <p class="mt-1 text-xs text-gray-400">
            <span class="font-semibold">Double Elimination:</span> Losers get a second chance in Lower Bracket<br>
            <span class="font-semibold">Single Elimination:</span> Lose once and you're out
        </p>
    </div>

    <div class="col-span-1">
        <label for="fightingAreas"
            class="block text-sm font-medium text-gray-700">{{ trans_choice('laravel-tournaments::core.fightingArea', 2) }}</label>
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="fightingAreas" name="fightingAreas" :disabled="isAreaDisabled">
            <option value="1" @if ($setting->fightingAreas == 1) selected @endif>1</option>
            <option value="2" @if ($setting->fightingAreas == 2) selected @endif>2</option>
            <option value="4" @if ($setting->fightingAreas == 4) selected @endif>4</option>
            <option value="8" @if ($setting->fightingAreas == 8) selected @endif>8</option>
        </select>
    </div>
</div>

<!-- Event Selection Row -->
<div class="grid grid-cols-1 gap-6 mt-6">
    <div class="col-span-1">
        <label for="event_id" class="block text-sm font-medium text-gray-700">
            Pilih Event <span class="text-red-500">*</span>
        </label>
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="event_id" name="event_id" required>
            <option value="">-- Pilih Event --</option>
            @foreach($events as $event)
                <option value="{{ $event->id }}" 
                    @if(isset($tournament) && $tournament->event_id == $event->id) selected 
                    @elseif(old('event_id') == $event->id) selected 
                    @endif>
                    {{ $event->name }} - {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}
                    @if(isset($tournament) && $tournament->event_id == $event->id)
                        (Current)
                    @endif
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-400">
            Tournament ini akan terhubung dengan event yang dipilih
        </p>
    </div>
</div>

{{-- ADDED: Info box explaining tournament formats --}}
<div class="mt-6 bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
    <div class="flex items-start gap-3">
        <i class="fas fa-info-circle text-blue-400 mt-0.5 flex-shrink-0"></i>
        <div class="text-sm text-gray-300 space-y-2">
            <p class="font-semibold text-blue-300">Tournament Format Guide:</p>
            <div class="space-y-1 pl-4">
                <p><span class="font-medium text-yellow-300">🏆 Double Elimination:</span> Players who lose get a second chance by dropping to the Lower Bracket. Only after losing twice are they eliminated. Final match: Upper Bracket winner vs Lower Bracket winner.</p>
                <p><span class="font-medium text-red-300">⚔️ Single Elimination:</span> Traditional knockout format. Lose once and you're out. Faster but less forgiving.</p>
            </div>
        </div>
    </div>
</div>