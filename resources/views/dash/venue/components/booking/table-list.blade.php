<div>
    <div class="flex justify-between items-center mb-4">
        <span class="font-bold text-lg">Table List</span>
        <button class="bg-[#232323] border border-blue-500 text-blue-400 rounded px-3 py-1 text-sm hover:bg-blue-500 hover:text-white transition">+ Add New Table</button>
    </div>
    <hr class="border-gray-600 mb-4">
    <div class="grid grid-cols-2 gap-y-4">
        @foreach($tables as $table)
            <div class="flex items-center space-x-2">
                <span class="h-3 w-3 rounded-full {{ $table->status === 'available' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                <span>Table #{{ $table->table_number }}</span>
            </div>
        @endforeach
    </div>
</div>