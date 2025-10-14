@extends('app')
@section('title', 'Admin Dashboard - Subscriber List')

@push('styles')
<style>
    :root{ color-scheme: dark; --page-bg:#0a0a0a; }
    html, body{
        height:100%;
        min-height:100%;
        background:var(--page-bg);
        overscroll-behavior-y: none;
        overscroll-behavior-x: none;
        touch-action: pan-y;
        -webkit-text-size-adjust:100%;
    }
    #antiBounceBg{
        position: fixed;
        left:0; right:0;
        top:-120svh; bottom:-120svh;
        background:var(--page-bg);
        z-index:-1;
        pointer-events:none;
    }
    .scroll-safe{
        background-color:#171717;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
    .btn{
        display:inline-flex; align-items:center; gap:.5rem;
        padding:.6rem .9rem; border-radius:.55rem; font-weight:600; font-size:.9rem;
        transition:.15s ease;
    }
    .btn-primary{ background:#16a34a; color:#fff; }
    .btn-primary:hover{ background:#15803d; }
</style>
@endpush

@section('content')
    <div id="antiBounceBg" aria-hidden="true"></div>

    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
                @include('partials.topbar')
                
                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">
                        Data Subscriber
                    </h1>

                    @if (session('success'))
                        <div class="mb-4 bg-green-500 text-white px-4 py-2 rounded text-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-500 text-white px-4 py-2 rounded text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <input name="search" value="{{ request('search') }}"
                                class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                placeholder="Cari subscriber..." type="search"
                                onchange="window.location.href='{{ route('dash.admin.subscriber') }}?search=' + encodeURIComponent(this.value)" />
                        </div>

                        <!-- Tombol Export Excel (ikut query ?search=...) -->
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('dash.admin.subscriber.export', request()->only('search')) }}"
                                class="flex items-center justify-center gap-1 border border-green-500 text-green-500 rounded px-3 py-2 text-xs sm:text-sm hover:bg-green-500 hover:text-white transition whitespace-nowrap">
                                <i class="fas fa-file-excel"></i>
                                Export Excel
                            </a>
                        </div>
                    </div>

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse ($subscribers as $subscriber)
                                    <tr>
                                        <td class="px-4 py-3">{{ $subscriber->email }}</td>
                                        <td class="px-4 py-3 text-gray-400">{{ $subscriber->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-400">
                                            Belum ada subscriber
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse ($subscribers as $subscriber)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <div class="mb-3 pb-3 border-b border-gray-700">
                                    <h3 class="font-semibold text-base mb-2">{{ $subscriber->email }}</h3>
                                    <p class="text-xs text-gray-400">{{ $subscriber->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Belum ada subscriber.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
