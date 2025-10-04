@extends('app')
@section('title', 'Admin Dashboard - Vouchers')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')

                <div class="p-8 mt-12">

                    <div class="items-center mb-6">
                        <h1 class="text-2xl font-bold">Voucher List</h1>
                    </div>

                    <div class="mb-4 flex justify-between items-center gap-4 flex-col sm:flex-row">
                        <input
                            class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                            onchange="window.location.href = '{{ route('promo.index') }}?search=' + this.value"
                            value="{{ request('search') }}" placeholder="Search" type="search" />
                        <a href="{{ route('promo.create') }}"
                            class="flex items-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition">
                            <i class="fa fa-plus"></i>
                            Add Voucher
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-3 bg-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <div class="flex items-center gap-4 mb-6 border-b border-neutral-700 pb-2 text-sm">
                            <a href="{{ route('promo.index', ['status' => 'all']) }}"
                                class="pb-2 {{ $status == 'all' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                All <span class="ml-1 text-xs text-gray-500">({{ $counts['all'] }})</span>
                            </a>
                            <a href="{{ route('promo.index', ['status' => 'ongoing']) }}"
                                class="pb-2 {{ $status == 'ongoing' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                Ongoing <span class="ml-1 text-xs text-gray-500">({{ $counts['ongoing'] }})</span>
                            </a>
                            <a href="{{ route('promo.index', ['status' => 'upcoming']) }}"
                                class="pb-2 {{ $status == 'upcoming' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                Upcoming <span class="ml-1 text-xs text-gray-500">({{ $counts['upcoming'] }})</span>
                            </a>
                            <a href="{{ route('promo.index', ['status' => 'ended']) }}"
                                class="pb-2 {{ $status == 'ended' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                Ended <span class="ml-1 text-xs text-gray-500">({{ $counts['ended'] }})</span>
                            </a>
                        </div>
                        <table class="min-w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Voucher Name | Code</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Voucher Type</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Voucher Period</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Discount</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Quota</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Claimed</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Status</th> {{-- 👈 Tambah kolom --}}
                                    <th class="px-4 py-3 text-center text-sm font-medium">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-700">
                                @forelse($vouchers as $voucher)
                                    <tr>
                                        {{-- Voucher Name | Code --}}
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col">
                                                <span class="font-semibold">{{ $voucher->name }}</span>
                                                <span class="text-xs text-gray-400">{{ $voucher->code }}</span>
                                            </div>
                                        </td>

                                        {{-- Type --}}
                                        <td class="px-4 py-3 capitalize">
                                            {{ str_replace('_', ' ', $voucher->type) }}
                                        </td>

                                        {{-- Period --}}
                                        <td class="px-4 py-3">
                                            {{ $voucher->start_date->format('d/m/Y H:i') }} -
                                            {{ $voucher->end_date->format('d/m/Y H:i') }}
                                        </td>

                                        {{-- Discount --}}
                                        <td class="px-4 py-3">
                                            @if ($voucher->type === 'percentage')
                                                {{ $voucher->discount_percentage }}%
                                            @elseif($voucher->type === 'fixed_amount')
                                                Rp{{ number_format($voucher->discount_amount, 0, ',', '.') }}
                                            @else
                                                Free Time
                                            @endif
                                        </td>

                                        {{-- Quota --}}
                                        <td class="px-4 py-3">{{ $voucher->quota }}</td>

                                        {{-- Claimed --}}
                                        <td class="px-4 py-3">{{ $voucher->claimed ?? 0 }}</td>

                                        {{-- Status --}}
                                        <td class="px-4 py-3">
                                            @php
                                                switch ($voucher->status) {
                                                    case 'inactive':
                                                        $color = 'text-gray-400';
                                                        $label = 'Inactive';
                                                        break;
                                                    case 'upcoming':
                                                        $color = 'text-blue-400';
                                                        $label = 'Upcoming';
                                                        break;
                                                    case 'ended':
                                                        $color = 'text-red-400';
                                                        $label = 'Ended';
                                                        break;
                                                    case 'ongoing':
                                                        $color = 'text-green-400';
                                                        $label = 'Ongoing';
                                                        break;
                                                    default:
                                                        $color = 'text-gray-400';
                                                        $label = ucfirst($voucher->status);
                                                }
                                            @endphp
                                            <span class="font-semibold {{ $color }}">{{ $label }}</span>
                                        </td>


                                        {{-- Action --}}
                                        <td class="px-4 py-3 flex gap-3 text-gray-400 justify-center">
                                            <a href="{{ route('promo.edit', $voucher->id) }}" class="hover:text-gray-200">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form action="{{ route('promo.destroy', $voucher->id) }}" method="POST"
                                                onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hover:text-gray-200">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-4 py-4 text-center text-neutral-400">
                                            No vouchers found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $vouchers->links() }}
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
