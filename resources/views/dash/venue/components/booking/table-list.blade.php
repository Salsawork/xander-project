<div>
    <div class="flex justify-between items-center mb-4 gap-3">
        <span class="font-bold text-lg">Table List</span>
        <a href="{{ route('venue.booking.create-table') }}"
            class="bg-[#232323] border border-blue-500 text-blue-400 rounded px-3 py-1 text-sm hover:bg-blue-500 hover:text-white transition">
            + Add New Table</a>
    </div>
    <hr class="border-gray-600 mb-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        @if (session('success'))
        <div class="col-span-1 sm:col-span-2 bg-green-500 text-white p-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif
        @foreach($tables as $table)
        <div class="flex items-center justify-between p-2 sm:p-3">
            <div class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-full {{ $table->status === 'available' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                <span class="font-medium text-sm sm:text-base">Table #{{ $table->table_number }}</span>
            </div>
            <form action="{{ route('venue.booking.delete-table', $table->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this table?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700 text-sm transition">
                    Delete
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>