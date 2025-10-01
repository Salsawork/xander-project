@extends('app')
@section('title', 'Admin Dashboard - Vouchers')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')

            <div class="p-8 mt-12">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Voucher List</h1>
                    <a href="{{ route('promo.create') }}"
                       class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-white text-sm font-medium">
                        + Add Voucher
                    </a>
                </div>

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full border border-neutral-700 rounded-lg overflow-hidden">
                        <thead class="bg-neutral-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium">Name</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Code</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Type</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Discount</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Quota</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Claimed</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Period</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Status</th>
                                <th class="px-4 py-2 text-center text-sm font-medium">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-700">
                            @forelse($vouchers as $voucher)
                                <tr>
                                    <td class="px-4 py-2">{{ $voucher->name }}</td>
                                    <td class="px-4 py-2">{{ $voucher->code }}</td>
                                    <td class="px-4 py-2 capitalize">{{ str_replace('_',' ', $voucher->type) }}</td>
                                    <td class="px-4 py-2">
                                        @if($voucher->type === 'percentage')
                                            {{ $voucher->discount_percentage }}%
                                        @elseif($voucher->type === 'fixed_amount')
                                            Rp{{ number_format($voucher->discount_amount, 0, ',', '.') }}
                                        @else
                                            Free Time
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $voucher->quota }}</td>
                                    <td class="px-4 py-2">{{ $voucher->claimed }}</td>
                                    <td class="px-4 py-2">
                                        {{ $voucher->start_date->format('d M Y') }} - 
                                        {{ $voucher->end_date->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        @if($voucher->is_active)
                                            <span class="px-2 py-1 bg-green-600 text-xs rounded">Active</span>
                                        @else
                                            <span class="px-2 py-1 bg-red-600 text-xs rounded">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <div class="flex justify-center gap-2">
                                            <a href="{{ route('promo.edit', $voucher->id) }}"
                                               class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm">Edit</a>
                                            <form action="{{ route('promo.destroy', $voucher->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1 bg-red-600 hover:bg-red-700 rounded text-white text-sm">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-4 text-center text-neutral-400">
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
