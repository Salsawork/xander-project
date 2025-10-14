@extends('app')
@section('title', 'Admin Dashboard - Create Voucher')

@push('styles')
<style>
    /* ====== Anti overscroll / white bounce ====== */
    :root{ color-scheme: dark; --page-bg:#0a0a0a; }
    html, body{
        height:100%;
        min-height:100%;
        background:var(--page-bg);
        overscroll-behavior-y: none;   /* cegah rubber-band ke body */
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust:100%;
    }
    /* Kanvas gelap tetap di belakang konten */
    #antiBounceBg{
        position: fixed;
        left:0; right:0;
        top:-120svh; bottom:-120svh;   /* svh stabil di mobile */
        background:var(--page-bg);
        z-index:-1;
        pointer-events:none;
    }
    /* Pastikan area scroll utama tidak meneruskan overscroll ke body */
    .scroll-safe{
        background-color:#171717;      /* senada dengan bg-neutral-900 */
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endpush

@section('content')
    <div id="antiBounceBg" aria-hidden="true"></div>

    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
                @include('partials.topbar')

                <div class="p-8 mt-12">
                    <h1 class="text-2xl font-bold mb-6">Create New Voucher</h1>

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

                    <form action="{{ route('promo.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            {{-- Name --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Voucher Name</label>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                            </div>

                            {{-- Code --}}
                            <div>
                                <label for="code" class="block text-sm font-medium mb-2">Voucher Code</label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white"
                                    required>

                                <p class="text-xs text-gray-400 mt-1">Note: Voucher code harus unik, tidak boleh sama dengan
                                    kode voucher lain.</p>

                                @error('code')
                                    <span class="text-red-400 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Voucher Type</label>
                                <select name="type"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                                    <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>
                                        Percentage
                                    </option>
                                    <option value="fixed_amount" {{ old('type') == 'fixed_amount' ? 'selected' : '' }}>Fixed
                                        Amount</option>
                                    <option value="free_time" {{ old('type') == 'free_time' ? 'selected' : '' }}>Free Time
                                    </option>
                                </select>
                            </div>

                            {{-- Discount Percentage --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Discount Percentage</label>
                                <input type="number" step="0.01" name="discount_percentage"
                                    value="{{ old('discount_percentage') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white focus:outline-none">
                            </div>

                            {{-- Discount Amount --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Discount Amount</label>
                                <input type="number" step="0.01" name="discount_amount"
                                    value="{{ old('discount_amount') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white focus:outline-none">
                            </div>

                            {{-- Minimum Purchase --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Minimum Purchase</label>
                                <input type="number" step="0.01" name="minimum_purchase"
                                    value="{{ old('minimum_purchase') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white focus:outline-none">
                            </div>

                            {{-- Quota --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Quota</label>
                                <input type="number" name="quota" value="{{ old('quota') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white focus:outline-none">
                            </div>

                            {{-- Start Date --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Start Date</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white focus:outline-none">
                            </div>

                            {{-- End Date --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">End Date</label>
                                <input type="date" name="end_date" value="{{ old('end_date') }}"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white focus:outline-none">
                            </div>

                            {{-- Venue --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Venue</label>
                                <select name="venue_id" required
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                                    @foreach ($venues as $venue)
                                        <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Active Status --}}
                            <div>
                                <label class="block text-sm font-medium mb-2">Status</label>
                                <select name="is_active"
                                    class="w-full px-3 py-2 rounded bg-neutral-800 border border-neutral-700 text-white">
                                    <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>

                        </div>

                        {{-- Submit Buttons --}}
                        <div class="flex flex-col sm:flex-row gap-4 justify-end mt-6">
                            <a href="{{ route('promo.index') }}"
                                class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-center">Cancel</a>
                            <button type="submit"
                                class="px-6 py-2 bg-[#0a8cff] rounded-md hover:bg-[#0077e6] transition">Save</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection
