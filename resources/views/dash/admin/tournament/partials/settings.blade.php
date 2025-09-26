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
            @for ($i = 1; $i < 60; $i++)
                <option value="{{ $i }}" @if ($numFighters == $i) selected @endif>
                    {{ $i }}</option>
            @endfor
        </select>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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
        <label for="treeType" class="block text-sm font-medium text-gray-700">Tree Type</label>
        <select
            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
            id="treeType" name="treeType" v-model="tree" v-on:change="treeType()">
            <option value="0" @if ($setting->treeType == 0) selected @endif>
                {{ trans('laravel-tournaments::core.playoff') }}</option>
            <option value="1" @if ($setting->treeType == 1) selected @endif>
                {{ trans('laravel-tournaments::core.single_elimination') }}</option>
        </select>
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
