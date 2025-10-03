@extends('app')
@section('title', 'Admin Dashboard - Edit Voucher')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')

            <div class="p-8 mt-12">
                <h1 class="text-2xl font-bold mb-6">Edit Voucher</h1>

                {{-- Error validation --}}
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-700 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('promo.update', $voucher->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Voucher Name</label>
                            <input type="text" name="name" value="{{ old('name', $voucher->name) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- Code --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Voucher Code</label>
                            <input type="text" name="code" value="{{ old('code', $voucher->code) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Voucher Type</label>
                            <select name="type" class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                                <option value="percentage" {{ old('type', $voucher->type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                <option value="fixed_amount" {{ old('type', $voucher->type) == 'fixed_amount' ? 'selected' : '' }}>Fixed Amount</option>
                                <option value="free_time" {{ old('type', $voucher->type) == 'free_time' ? 'selected' : '' }}>Free Time</option>
                            </select>
                        </div>

                        {{-- Discount Percentage --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Discount Percentage</label>
                            <input type="number" step="0.01" name="discount_percentage" 
                                value="{{ old('discount_percentage', $voucher->discount_percentage) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- Discount Amount --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Discount Amount</label>
                            <input type="number" step="0.01" name="discount_amount" 
                                value="{{ old('discount_amount', $voucher->discount_amount) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- Minimum Purchase --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Minimum Purchase</label>
                            <input type="number" step="0.01" name="minimum_purchase" 
                                value="{{ old('minimum_purchase', $voucher->minimum_purchase) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- Quota --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Quota</label>
                            <input type="number" name="quota" 
                                value="{{ old('quota', $voucher->quota) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Start Date</label>
                            <input type="date" name="start_date" 
                                value="{{ old('start_date', $voucher->start_date->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- End Date --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">End Date</label>
                            <input type="date" name="end_date" 
                                value="{{ old('end_date', $voucher->end_date->format('Y-m-d')) }}"
                                class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                        </div>

                        {{-- Active --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Status</label>
                            <select name="is_active" class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                                <option value="1" {{ old('is_active', $voucher->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $voucher->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        {{-- Venue --}}
                        <div>
                            <label class="block text-sm font-medium mb-2">Venue</label>
                            <select name="venue_id" class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}" {{ old('venue_id', $voucher->venue_id) == $venue->id ? 'selected' : '' }}>
                                        {{ $venue->name }}
                                    </option>
                                @endforeach
                            </select>
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end md:col-span-2">
                        <a href="{{ route('promo.index') }}" class="px-4 py-2 bg-neutral-700 rounded-lg hover:bg-neutral-600 mr-2">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-green-600 rounded-lg hover:bg-green-700">Update</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
