@extends('app')
@section('title', 'Admin Dashboard - Vouchers')

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

                <div class="p-4 sm:p-8 mt-12">

                    <div class="items-center mb-6">
                        <h1 class="text-2xl font-bold">Voucher List</h1>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center justify-between mb-4">
                        <input
                            class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                            onchange="window.location.href = '{{ route('promo.index') }}?search=' + this.value"
                            value="{{ request('search') }}" placeholder="Search" type="search" />
                        <a href="{{ route('promo.create') }}"
                            class="flex items-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap justify-center">
                            <i class="fa fa-plus"></i>
                            <span class="sm:inline">Add Voucher</span>
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-3 bg-green-700 rounded text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Tab Navigation -->
                    <div class="overflow-x-auto mb-6">
                        <div class="flex items-center gap-4 border-b border-neutral-700 pb-2 text-sm min-w-max">
                            <a href="{{ route('promo.index', ['status' => 'all']) }}"
                                class="pb-2 whitespace-nowrap {{ $status == 'all' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                All <span class="ml-1 text-xs text-gray-500">({{ $counts['all'] }})</span>
                            </a>
                            <a href="{{ route('promo.index', ['status' => 'ongoing']) }}"
                                class="pb-2 whitespace-nowrap {{ $status == 'ongoing' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                Ongoing <span class="ml-1 text-xs text-gray-500">({{ $counts['ongoing'] }})</span>
                            </a>
                            <a href="{{ route('promo.index', ['status' => 'upcoming']) }}"
                                class="pb-2 whitespace-nowrap {{ $status == 'upcoming' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                Upcoming <span class="ml-1 text-xs text-gray-500">({{ $counts['upcoming'] }})</span>
                            </a>
                            <a href="{{ route('promo.index', ['status' => 'ended']) }}"
                                class="pb-2 whitespace-nowrap {{ $status == 'ended' ? 'border-b-2 border-[#1e90ff] text-white' : 'text-gray-400' }}">
                                Ended <span class="ml-1 text-xs text-gray-500">({{ $counts['ended'] }})</span>
                            </a>
                        </div>
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="min-w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Voucher Name | Code</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Voucher Type</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Voucher Period</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Discount</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Quota</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Claimed</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                                    <th class="px-4 py-3 text-center text-sm font-medium">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-700">
                                @forelse($vouchers as $voucher)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col">
                                                <span class="font-semibold">{{ $voucher->name }}</span>
                                                <span class="text-xs text-gray-400">{{ $voucher->code }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 capitalize">
                                            {{ str_replace('_', ' ', $voucher->type) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $voucher->start_date->format('d/m/Y H:i') }} -
                                            {{ $voucher->end_date->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($voucher->type === 'percentage')
                                                {{ $voucher->discount_percentage }}%
                                            @elseif($voucher->type === 'fixed_amount')
                                                Rp{{ number_format($voucher->discount_amount, 0, ',', '.') }}
                                            @else
                                                Free Time
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">{{ $voucher->quota }}</td>
                                        <td class="px-4 py-3">{{ $voucher->claimed ?? 0 }}</td>
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
                                        <td class="px-4 py-3 flex gap-3 text-gray-400 justify-center">
                                            <a href="{{ route('promo.edit', $voucher->id) }}" class="hover:text-gray-200">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form id="delete-form-{{ $voucher->id }}"
                                                action="{{ route('promo.destroy', $voucher->id) }}" method="POST"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="deletePromo({{ $voucher->id }})"
                                                    class="hover:text-gray-200">
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

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse($vouchers as $voucher)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <!-- Header: Name & Status -->
                                <div class="flex justify-between items-start mb-3 pb-3 border-b border-gray-700">
                                    <div class="flex-1 min-w-0 pr-2">
                                        <h3 class="font-semibold text-base truncate">{{ $voucher->name }}</h3>
                                        <p class="text-xs text-gray-400 mt-1">Code: {{ $voucher->code }}</p>
                                    </div>
                                    <div>
                                        @php
                                            switch ($voucher->status) {
                                                case 'inactive':
                                                    $bgColor = 'bg-gray-600';
                                                    $textColor = 'text-gray-200';
                                                    $label = 'Inactive';
                                                    break;
                                                case 'upcoming':
                                                    $bgColor = 'bg-blue-600';
                                                    $textColor = 'text-white';
                                                    $label = 'Upcoming';
                                                    break;
                                                case 'ended':
                                                    $bgColor = 'bg-red-600';
                                                    $textColor = 'text-white';
                                                    $label = 'Ended';
                                                    break;
                                                case 'ongoing':
                                                    $bgColor = 'bg-green-600';
                                                    $textColor = 'text-white';
                                                    $label = 'Ongoing';
                                                    break;
                                                default:
                                                    $bgColor = 'bg-gray-600';
                                                    $textColor = 'text-gray-200';
                                                    $label = ucfirst($voucher->status);
                                            }
                                        @endphp
                                        <span
                                            class="px-2 py-1 rounded text-xs font-medium {{ $bgColor }} {{ $textColor }} whitespace-nowrap">
                                            {{ $label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Voucher Details -->
                                <div class="space-y-2 text-sm mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Type:</span>
                                        <span
                                            class="font-medium capitalize">{{ str_replace('_', ' ', $voucher->type) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Discount:</span>
                                        <span class="font-medium">
                                            @if ($voucher->type === 'percentage')
                                                {{ $voucher->discount_percentage }}%
                                            @elseif($voucher->type === 'fixed_amount')
                                                Rp{{ number_format($voucher->discount_amount, 0, ',', '.') }}
                                            @else
                                                Free Time
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Quota:</span>
                                        <span class="font-medium">{{ $voucher->quota }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Claimed:</span>
                                        <span class="font-medium">{{ $voucher->claimed ?? 0 }}</span>
                                    </div>
                                    <div class="flex flex-col pt-2 border-t border-gray-700">
                                        <span class="text-gray-400 text-xs mb-1">Period:</span>
                                        <span class="text-xs">{{ $voucher->start_date->format('d/m/Y H:i') }}</span>
                                        <span class="text-xs text-gray-500">to</span>
                                        <span class="text-xs">{{ $voucher->end_date->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('promo.edit', $voucher->id) }}"
                                        class="w-1/2 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition h-10">
                                        <i class="fas fa-pen text-xs"></i>
                                        <span>Edit</span>
                                    </a>

                                    <!-- Tombol Delete -->
                                    <form id="delete-form-{{ $voucher->id }}"
                                        action="{{ route('promo.destroy', $voucher->id) }}" method="POST" class="w-1/2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="deletePromo({{ $voucher->id }})"
                                            class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition h-10">
                                            <i class="fas fa-trash text-xs"></i>
                                            <span>Delete</span>
                                        </button>
                                    </form>
                                </div>


                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-neutral-400">No vouchers found.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $vouchers->links() }}
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deletePromo(id) {
        Swal.fire({
            title: 'Hapus promo ini?',
            text: "Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            background: '#222',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById(`delete-form-${id}`);
                if (form) {
                    form.submit();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Form penghapusan tidak ditemukan.',
                        background: '#222',
                        color: '#fff'
                    });
                }
            }
        });
    }

    @if (session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 3000,
        background: '#222',
        color: '#fff'
    });
    @endif

    @if (session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        background: '#222',
        color: '#fff'
    });
    @endif
</script>
@endpush

