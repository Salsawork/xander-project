<div>
    <!-- Filter Tabs -->
    <div class="overflow-x-auto -mx-4 sm:mx-0 border-b border-gray-700 mb-4">
        <div class="flex whitespace-nowrap min-w-[600px] px-4 sm:px-0">
            <a href="{{ route('venue.promo') }}" class="shrink-0 px-4 py-2 pb-4 {{ request()->get('filter') == null ? 'border-b-2 border-blue-500 text-white' : 'text-gray-400' }}">
                All <span class="ml-1 px-2 py-1 bg-gray-700 rounded-full text-xs">{{ $allCount }}</span>
            </a>
            <a href="{{ route('venue.promo') }}?filter=ongoing" class="shrink-0 px-4 py-2 pb-4 {{ request()->get('filter') == 'ongoing' ? 'border-b-2 border-blue-500 text-white' : 'text-gray-400' }}">
                Ongoing <span class="ml-1 px-2 py-1 bg-gray-700 rounded-full text-xs">{{ $ongoingCount }}</span>
            </a>
            <a href="{{ route('venue.promo') }}?filter=upcoming" class="shrink-0 px-4 py-2 pb-4 {{ request()->get('filter') == 'upcoming' ? 'border-b-2 border-blue-500 text-white' : 'text-gray-400' }}">
                Upcoming <span class="ml-1 px-2 py-1 bg-gray-700 rounded-full text-xs">{{ $upcomingCount }}</span>
            </a>
            <a href="{{ route('venue.promo') }}?filter=ended" class="shrink-0 px-4 py-2 pb-4 {{ request()->get('filter') == 'ended' ? 'border-b-2 border-blue-500 text-white' : 'text-gray-400' }}">
                Ended <span class="ml-1 px-2 py-1 bg-gray-700 rounded-full text-xs">{{ $endedCount }}</span>
            </a>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-4 sm:space-y-0">
        <h2 class="text-xl font-semibold">Voucher List</h2>
        <div class="w-full sm:w-auto">
            <div class="relative">
                <input type="text" placeholder="Search" class="bg-[#1e1e1e] border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <!-- Voucher Table -->
    <div class="overflow-x-auto -mx-4 sm:mx-0">
        <div class="min-w-[800px] px-4 sm:px-0">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="py-3 px-4">Voucher Name | Code</th>
                        <th class="py-3 px-4">Voucher Type</th>
                        <th class="py-3 px-4">Voucher Period</th>
                        <th class="py-3 px-4">Discount</th>
                        <th class="py-3 px-4">Quota</th>
                        <th class="py-3 px-4">Claimed</th>
                        <th class="py-3 px-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vouchers as $voucher)
                        <tr class="border-b border-gray-700">
                            <td class="py-3 px-4">{{ $voucher->name }}</td>
                            <td class="py-3 px-4">{{ $voucher->type }}</td>
                            <td class="py-3 px-4">
                                <div class="whitespace-nowrap">
                                    {{ $voucher->start_date->format('d/m/Y H:i') }} -
                                    {{ $voucher->end_date->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if ($voucher->discount_percentage)
                                    {{ $voucher->discount_percentage }}%
                                @else
                                    Rp {{ number_format($voucher->discount_amount, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="py-3 px-4">{{ $voucher->quota }}</td>
                            <td class="py-3 px-4">{{ $voucher->claimed }}</td>
                            <td class="py-3 px-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('venue.promo.delete', $voucher->id) }}" class="text-red-500 hover:text-red-400" onclick="return confirm('Are you sure you want to delete this voucher?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 px-4 text-center text-gray-400">No vouchers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
