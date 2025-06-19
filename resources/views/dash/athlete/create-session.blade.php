@extends('app')
@section('title', 'Create Session')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar.athlete')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <div class="p-8 mt-12 mx-20">
                    <h1 class="text-3xl font-bold mb-8">Create Session</h1>
                    <form action="{{ route('athlete.match.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @csrf
                        <div class="bg-[#232323] rounded-lg p-8 shadow-md flex flex-col gap-4">
                            <h2 class="text-lg font-semibold mb-2 border-b border-neutral-700 pb-2">Session Details</h2>
                            <div>
                                <label class="block mb-1 text-sm">Venue</label>
                                <select name="venue_id" class="w-full bg-neutral-800 rounded px-4 py-2 text-white focus:outline-none">
                                    <option value="">Pilih Venue</option>
                                    @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm">Opponent</label>
                                <select name="opponent_id" class="w-full bg-neutral-800 rounded px-4 py-2 text-white focus:outline-none">
                                    <option value="">Pilih Lawan</option>
                                    @foreach($opponents as $opponent)
                                        <option value="{{ $opponent->id }}">{{ $opponent->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm">Payment Method</label>
                                <input type="text" name="payment_method" class="w-full bg-neutral-800 rounded px-4 py-2 text-white focus:outline-none" placeholder="Ex. Cash, Gopay, DANA"/>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm">Total Amount</label>
                                <input type="number" name="total_amount" class="w-full bg-neutral-800 rounded px-4 py-2 text-white focus:outline-none" placeholder="Ex. 50000"/>
                            </div>
                        </div>
                        <div class="bg-[#232323] rounded-lg p-8 shadow-md flex flex-col gap-4">
                            <h2 class="text-lg font-semibold mb-2 border-b border-neutral-700 pb-2">Schedule</h2>
                            <div>
                                <label class="block mb-1 text-sm">Date</label>
                                <input type="date" name="date" class="w-full bg-neutral-800 rounded px-4 py-2 text-white focus:outline-none"/>
                            </div>
                            <div class="flex gap-4">
                                <div class="w-1/2">
                                    <label class="block mb-1 text-sm">Time Start</label>
                                    <input type="time" name="time_start" class="w-full bg-neutral-800 rounded px-4 py-2 text-white focus:outline-none"/>
                                </div>
                                <div class="w-1/2">
                                    <label class="block mb-1 text-sm">Time End</label>
                                    <input type="time" name="time_end" class="w-full bg-neutral-800 rounded px-4 py-2 text-white focus:outline-none"/>
                                </div>
                            </div>
                            <div class="mt-8">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded transition">Create Session</button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection
