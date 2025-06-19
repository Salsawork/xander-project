@extends('app')
@section('title', 'Match Detail')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar.athlete')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8">
            @include('partials.topbar')
            <div class="p-8 mt-12 mx-20 max-w-6xl mx-auto">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('athlete.match') }}" class="text-neutral-400 hover:text-white text-2xl mr-2">&larr;</a>
                        <h1 class="text-3xl font-bold">Session Detail <span class="text-blue-400 ml-2">#XB{{ str_pad($match->id, 8, '0', STR_PAD_LEFT) }}</span></h1>
                    </div>
                    <div class="text-neutral-400 text-sm font-medium">{{ \Carbon\Carbon::parse($match->created_at)->format('d/m/Y \a\t H.i') }}</div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-2 flex flex-col gap-8">
                        <div class="bg-[#232323] rounded-xl shadow-md p-8">
                            <h2 class="text-lg font-semibold mb-4 border-b border-neutral-700 pb-2">Booking Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <div class="text-neutral-400 text-xs">Location:</div>
                                    <div class="font-bold">{{ $match->venue->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-neutral-400 text-xs">Price:</div>
                                    <div class="font-bold">Rp. {{ number_format($match->total_amount, 0, ',', '.') }} <span class="font-normal text-xs">/session</span></div>
                                </div>
                                <div>
                                    <div class="text-neutral-400 text-xs">Table:</div>
                                    <div class="font-bold">Table #{{ $match->venue->id ?? '-' }}</div>
                                </div>
                                <div>
                                    <div class="text-neutral-400 text-xs">Booking Date:</div>
                                    <div class="font-bold">{{ \Carbon\Carbon::parse($match->date)->format('F j, Y') }}</div>
                                </div>
                                <div>
                                    <div class="text-neutral-400 text-xs">Session Time:</div>
                                    <div class="font-bold">{{ date('H.i', strtotime($match->time_start)) }}-{{ date('H.i', strtotime($match->time_end)) }}</div>
                                </div>
                                <div>
                                    <div class="text-neutral-400 text-xs">Promo Code:</div>
                                    <div class="font-bold">JNTX1235</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 mt-4">
                                <div>
                                    <div class="text-neutral-400 text-xs">Booking ID:</div>
                                    <div class="font-bold inline-block">#BK{{ $match->created_at->format('Ymd') . str_pad($match->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </div>
                                <button type="button" onclick="navigator.clipboard.writeText('#BK{{ $match->created_at->format('Ymd') . str_pad($match->id, 4, '0', STR_PAD_LEFT) }}')" class="ml-2 px-3 py-1 rounded bg-neutral-800 text-xs border border-neutral-700 hover:bg-neutral-700">Copy</button>
                            </div>
                        </div>
                        <div class="bg-[#232323] rounded-xl shadow-md p-8">
                            <h2 class="text-lg font-semibold mb-4 border-b border-neutral-700 pb-2">Session Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <div class="text-neutral-400 text-xs">Game Type:</div>
                                    <div class="font-bold">9-Ball Match Rotation</div>
                                </div>
                                <div>
                                    <div class="text-neutral-400 text-xs">Max Participant:</div>
                                    <div class="font-bold">5 Players</div>
                                </div>
                                <div>
                                    <div class="text-neutral-400 text-xs">Entry Fee:</div>
                                    <div class="font-bold">Rp.50.000 <span class="font-normal text-xs">/person</span></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 mt-4">
                                <div>
                                    <div class="text-neutral-400 text-xs">Session Code:</div>
                                    <div class="font-bold inline-block">#SPG{{ $match->created_at->format('Ymd') . str_pad($match->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </div>
                                <button type="button" onclick="navigator.clipboard.writeText('#SPG{{ $match->created_at->format('Ymd') . str_pad($match->id, 4, '0', STR_PAD_LEFT) }}')" class="ml-2 px-3 py-1 rounded bg-neutral-800 text-xs border border-neutral-700 hover:bg-neutral-700">Copy</button>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-8">
                        <div class="bg-[#232323] rounded-xl shadow-md p-8 flex-1">
                            <h2 class="text-lg font-semibold mb-4">Registered Participants <span class="text-neutral-400">(3/5)</span></h2>
                            <ul class="mb-6">
                                <li class="border-b border-neutral-700 py-2">Daniel Cruz</li>
                                <li class="border-b border-neutral-700 py-2">Emily Carter</li>
                                <li class="py-2">Mark Stevens</li>
                            </ul>
                            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded transition">Download Invoice</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection
