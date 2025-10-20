@extends('app')
@section('title', 'Sparring Schedule')

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .scrollbar-hide {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    /* ====== Anti overscroll / white bounce ====== */
    :root {
        color-scheme: dark;
        --page-bg: #0a0a0a;
    }

    html,
    body {
        height: 100%;
        min-height: 100%;
        background: var(--page-bg);
        overscroll-behavior-y: none;
        /* cegah rubber-band ke body */
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust: 100%;
    }

    /* Kanvas gelap tetap di belakang konten */
    #antiBounceBg {
        position: fixed;
        left: 0;
        right: 0;
        top: -120svh;
        bottom: -120svh;
        /* svh stabil di mobile */
        background: var(--page-bg);
        z-index: -1;
        pointer-events: none;
    }

    /* Pastikan area scroll utama tidak meneruskan overscroll ke body */
    .scroll-safe {
        background-color: #171717;
        /* senada bg-neutral-900 */
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar.athlete')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
            @include('partials.topbar')
            <h1 class="text-2xl font-bold p-4 sm:p-8 mt-12">Sparring Schedule</h1>
            <div class="mx-8">
                <div class="flex justify-end mb-6">
                    <div class="flex flex-col sm:flex-row flex-wrap gap-2 items-stretch sm:items-center">
                        <select id="statusFilter"
                            onchange="window.location.href = '{{ route('athlete.sparring') }}?search=' + document.querySelector('input[type=search]').value + '&status=' + this.value + '&date_range=' + document.getElementById('dateRange').value;"
                            class="bg-[#2c2c2c] text-gray-400 text-xs sm:text-sm rounded border border-gray-700 px-2 py-2 cursor-pointer">
                            <option value="">-- Status --</option>
                            <option value="1" {{ request('status')=='1'?'selected':'' }}>Booked</option>
                            <option value="0" {{ request('status')=='0'?'selected':'' }}>Available</option>
                        </select>

                        <a href="{{ route('athlete.sparring.create') }}"
                            class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                            <i class="fas fa-plus"></i>
                            Add Sparring Schedule
                        </a>
                    </div>
                </div>

                {{-- Table --}}
                <div class="bg-[#232323] rounded-lg shadow-md overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left border-b border-neutral-700">
                                <th class="py-4 px-6">No</th>
                                <th class="py-4 px-6">Date</th>
                                <th class="py-4 px-6">Start Time</th>
                                <th class="py-4 px-6">End Time</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedule as $s)
                            <tr class="border-b border-neutral-800 hover:bg-neutral-800/50 transition">
                                <td class="py-3 px-6">{{ $loop->iteration }}</td>
                                <td class="py-3 px-6">{{ \Carbon\Carbon::parse($s->date)->format('d/m/Y') }}</td>
                                <td class="py-3 px-6">{{ date('H:i', strtotime($s->start_time)) }}</td>
                                <td class="py-3 px-6">{{ date('H:i', strtotime($s->end_time)) }}</td>
                                <td class="py-3 px-6">
                                    <span class="px-3 py-1 rounded-full font-semibold text-xs {{ $s->is_booked ? 'bg-green-400 text-green-900' : 'bg-yellow-400 text-yellow-900' }}">
                                        {{ $s->is_booked ? 'Booked' : 'Available' }}
                                    </span>
                                </td>
                                <td class="py-3 px-6">
                                    <a class="text-blue-500 hover:text-blue-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor"
                                            class="w-5 h-5 opacity-60 hover:opacity-100 cursor-pointer">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 12s3.75-7.5 9.75-7.5 
                                                    9.75 7.5 9.75 7.5-3.75 7.5-9.75 
                                                    7.5S2.25 12 2.25 12z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 
                                                    016 0z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-neutral-400">
                                    Belum ada jadwal sparring.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
{{-- Flatpickr untuk date range --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>
<script>
    flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        allowInput: true,
    });
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection