<div>
    <div class="flex justify-between items-center mb-4">
        <span class="font-bold text-lg">Price & Schedule</span>
        <a href="{{ route('price-schedule.create') }}"
            class="bg-[#232323] border border-blue-500 text-blue-400 rounded px-3 py-1 text-sm hover:bg-blue-500 hover:text-white transition">+
            Create New</a>
    </div>

    <div class="space-y-3">
        @foreach ($priceSchedules as $schedule)
            {{-- {{ $schedule->is_active ? 'bg-blue-500' : 'bg-[#3A3A3A]' }}     --}}
            <div class="rounded-lg overflow-hidden bg-blue-500">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-lg">{{ $schedule->name }}</h3>
                            <p class="text-sm">
                                {{ $schedule->start_time->format('H:i') }}-{{ $schedule->end_time->format('H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            {{-- <div class="relative inline-block w-10 align-middle select-none">
                                <input type="checkbox" {{ $schedule->is_active ? 'checked' : '' }}
                                    class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" />
                                <label
                                    class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div> --}}
                            <form action="{{ route('price-schedule.destroy', $schedule->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this schedule?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 text-red-500 hover:text-red-700 transition"
                                    title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="text-right text-xs mt-2">
                        {{ $schedule->days }}
                    </div>
                </div>
                <div class="bg-black bg-opacity-20 p-3 flex justify-between items-center">
                    <span>
                        @if (is_array($schedule->tables_applicable) && count($schedule->tables_applicable))
                            {{ implode(', ', $schedule->tables_applicable) }}
                        @else
                            All Table
                        @endif
                    </span>
                    <span class="font-bold">{{ number_format($schedule->price, 0, ',', '.') }} <span
                            class="text-sm font-normal">/ session</span></span>
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

    .toggle-checkbox:checked+.toggle-label {
        background-color: #68D391;
    }
</style>
