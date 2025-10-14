@extends('app')
@section('title', 'Admin Dashboard - Opinion List')

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
                    <div class="flex items-center justify-between gap-3 mb-6">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-extrabold">Data Opinion</h1>
                            <p class="text-gray-400 text-sm mt-1">Kumpulan masukan dari pengguna.</p>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Search (opsional) --}}
                            <form method="GET" action="{{ route('dash.admin.opinion') }}" class="hidden sm:block">
                                <input name="search" value="{{ request('search') }}"
                                    class="w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                                    placeholder="Cari email/subject/description..." type="search"/>
                            </form>

                            {{-- Export Excel: ikut ?search jika ada --}}
                            <a href="{{ route('dash.admin.opinion.export', request()->only('search')) }}"
                               class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 3a1 1 0 011 1v9.586l2.293-2.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L11 13.586V4a1 1 0 011-1z"/>
                                    <path d="M5 18a1 1 0 011-1h12a1 1 0 110 2H6a1 1 0 01-1-1z"/>
                                </svg>
                                Export Excel
                            </a>
                        </div>
                    </div>

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

                    <!-- Desktop & Tablet Table View -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
                            <thead class="bg-[#2c2c2c] text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Subject</th>
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                @forelse ($opinions as $opinion)
                                    <tr>
                                        <td class="px-4 py-3">{{ $opinion->email }}</td>
                                        <td class="px-4 py-3">{{ $opinion->subject }}</td>
                                        <td class="px-4 py-3">{{ $opinion->description }}</td>
                                        <td class="px-4 py-3 text-gray-400">{{ $opinion->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                            Belum ada opinion
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card View -->
                    <div class="sm:hidden space-y-4">
                        @forelse ($opinions as $opinion)
                            <div class="bg-[#2c2c2c] rounded-lg p-4 border border-gray-700">
                                <div class="mb-1">
                                    <h3 class="font-semibold text-base">{{ $opinion->email }}</h3>
                                </div>
                                <p class="text-sm text-gray-300"><span class="text-gray-400">Subject:</span> {{ $opinion->subject }}</p>
                                <p class="text-sm text-gray-300"><span class="text-gray-400">Description:</span> {{ $opinion->description }}</p>
                                <p class="text-xs text-gray-500 mt-2">{{ $opinion->created_at->format('d M Y') }}</p>
                            </div>
                        @empty
                            <div class="bg-[#2c2c2c] rounded-lg p-6 border border-gray-700 text-center">
                                <p class="text-gray-400">Belum ada opinion.</p>
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
