<div>
    <div class="flex justify-between items-center mb-4">
        <span class="font-bold text-lg">Price & Schedule</span>
        <button class="bg-[#232323] border border-blue-500 text-blue-400 rounded px-3 py-1 text-sm hover:bg-blue-500 hover:text-white transition">+ Create New</button>
    </div>
    
    <div class="space-y-3">
        @foreach($priceSchedules as $schedule)
            <div class="{{ $schedule->is_active ? 'bg-blue-500' : 'bg-[#3A3A3A]' }} rounded-lg overflow-hidden">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-lg">{{ $schedule->name }}</h3>
                            <p class="text-sm">{{ $schedule->start_time->format('H:i') }}-{{ $schedule->end_time->format('H:i') }}</p>
                        </div>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input type="checkbox" {{ $schedule->is_active ? 'checked' : '' }} class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                            <label class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                    </div>
                    <div class="text-right text-xs mt-2">
                        {{ $schedule->days }}
                    </div>
                </div>
                <div class="bg-black bg-opacity-20 p-3 flex justify-between items-center">
                    <span>
                        @if(is_array($schedule->tables_applicable) && count($schedule->tables_applicable))
                            {{ implode(', ', $schedule->tables_applicable) }}
                        @else
                            All Table
                        @endif
                    </span>
                    <span class="font-bold">{{ number_format($schedule->price, 0, ',', '.') }} <span class="text-sm font-normal">/ session</span></span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: white;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #68D391;
    }
</style>