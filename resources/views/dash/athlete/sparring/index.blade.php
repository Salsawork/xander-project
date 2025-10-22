@extends('app')
@section('title', 'Sparring Schedule')

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }

    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }

    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

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
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust: 100%;
    }

    #antiBounceBg {
        position: fixed;
        left: 0;
        right: 0;
        top: -120svh;
        bottom: -120svh;
        background: var(--page-bg);
        z-index: -1;
        pointer-events: none;
    }

    .scroll-safe {
        background-color: #171717;
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
                            onchange="window.location.href = '{{ route('athlete.sparring') }}?search=' + (document.querySelector('input[type=search]')?.value || '') + '&status=' + this.value + '&date_range=' + (document.getElementById('dateRange')?.value || '');"
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
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('athlete.sparring.edit', $s->id) }}"
                                            class="text-blue-500 hover:text-blue-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor"
                                                class="w-5 h-5 opacity-60 hover:opacity-100 cursor-pointer">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('athlete.sparring.destroy', $s->id) }}" method="POST"
                                            onsubmit="return deleteSchedule(event, this)" class="inline">

                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5"
                                                    stroke="currentColor"
                                                    class="w-5 h-5 opacity-60 hover:opacity-100 cursor-pointer">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
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

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Flatpickr --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>

<script>
    function deleteSchedule(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    flatpickr("#dateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        allowInput: true,
    });
</script>
@endpush