@extends('app')
@section('title', 'Admin Dashboard - Edit Tournament')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            {{-- Sidebar --}}
            @include('partials.sidebar')

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')

                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    {{-- Header dengan Back Button --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('tournament.index') }}" class="text-gray-400 hover:text-white transition">
                                <i class="fas fa-arrow-left text-lg"></i>
                            </a>
                            <div>
                                <h1 class="text-2xl sm:text-3xl font-extrabold">Edit Tournament</h1>
                                <p class="text-xs sm:text-sm text-gray-400 mt-1">Kelola dan setup tournament Anda</p>
                            </div>
                        </div>
                    </div>

                    {{-- Alert Messages --}}
                    @if ($errors->any())
                        <div
                            class="mb-6 bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg flex items-start gap-3">
                            <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
                            <div class="flex-1">
                                <p class="font-medium">Validation Error</p>
                                <ul class="text-sm mt-2 space-y-1 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div
                            class="mb-6 bg-green-500/10 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg flex items-start gap-3">
                            <i class="fas fa-check-circle mt-1 flex-shrink-0"></i>
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif

                    {{-- Link to Tournament Library CSS --}}
                    <link rel="stylesheet" href="{{ asset('vendor/laravel-tournaments/css/custom.css') }}">
                    <link rel="stylesheet" href="{{ asset('vendor/laravel-tournaments/css/brackets.css') }}">
                    <script src="https://unpkg.com/vue@2.4.2/dist/vue.js"></script>

                    {{-- Setup Variables --}}
                    <?php
                    $isTeam = 0;
                    $championship = $tournament->championships[$isTeam];
                    $setting = $championship->getSettings();
                    $treeType = $setting->treeType;
                    $hasPreliminary = $setting->hasPreliminary;
                    $fightingAreas = $setting->fightingAreas;
                    $fights = $championship->fights;
                    $numFighters = 5;
                    ?>

                    {{-- Main Form Grid --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

                        {{-- Left Column - Form Inputs --}}
                        <div class="lg:col-span-2 space-y-6">

                            <form
                                action="{{ route('tournament.update', [
                                    'tournament' => $tournament,
                                    'championship' => $championship,
                                ]) }}"
                                method="POST" class="space-y-6" enctype="multipart/form-data">
                                @method('PUT')
                                @csrf

                                {{-- TOURNAMENT INFO CARD - MOBILE --}}
                                <div class="sm:hidden bg-[#2c2c2c] border border-gray-700 rounded-lg p-4 space-y-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-1 h-5 bg-[#1e90ff] rounded flex-shrink-0"></div>
                                        <h2 class="text-base font-semibold text-white">Informasi Tournament</h2>
                                    </div>

                                    {{-- Tournament Name --}}
                                    <div class="space-y-2">
                                        <label for="name" class="block text-xs font-medium text-gray-300">
                                            Judul Tournament <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" id="name" name="name"
                                            placeholder="Masukkan nama tournament"
                                            value="{{ old('name', $tournament->name) }}" required
                                            class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-xs text-gray-300 placeholder-gray-500 transition focus:outline-none focus:ring-1 focus:ring-[#1e90ff] focus:border-[#1e90ff]">
                                        <p class="text-xs text-gray-500">Nama yang akan ditampilkan di seluruh
                                            sistem</p>
                                    </div>
                                </div>

                                {{-- TOURNAMENT INFO CARD - TABLET & DESKTOP --}}
                                <div class="hidden sm:block bg-[#2c2c2c] border border-gray-700 rounded-lg p-6">
                                    <div class="flex items-center gap-2 mb-6">
                                        <div class="w-1 h-6 bg-[#1e90ff] rounded flex-shrink-0"></div>
                                        <h2 class="text-lg font-semibold text-white">Informasi Tournament</h2>
                                    </div>

                                    {{-- Tournament Name --}}
                                    <div class="space-y-1 md:space-y-0 md:grid md:grid-cols-4 md:items-center md:gap-4">
                                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2 md:mb-0">
                                            Judul Tournament <span class="text-red-500">*</span>
                                        </label>
                                        <div class="md:col-span-3">
                                            <input type="text" id="name" name="name"
                                                placeholder="Masukkan nama tournament"
                                                value="{{ old('name', $tournament->name) }}" required
                                                class="w-full rounded-md border border-gray-600 bg-transparent px-4 py-2 text-gray-300 placeholder-gray-500 transition focus:outline-none focus:ring-1 focus:ring-[#1e90ff] focus:border-[#1e90ff]">
                                            <p class="text-xs text-gray-500 mt-1">Nama yang akan ditampilkan di seluruh
                                                sistem</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- TOURNAMENT SETTINGS CARD - Include dari partials --}}
                                <div class="bg-[#2c2c2c] border border-gray-700 rounded-lg p-6">
                                    <div class="flex items-center gap-2 mb-6">
                                        <div class="w-1 h-6 bg-[#1e90ff] rounded"></div>
                                        <h2 class="text-lg font-semibold">Pengaturan Tournament</h2>
                                    </div>
                                    @include('dash.admin.tournament.partials.settings')
                                </div>

                                {{-- ACTION BUTTONS - MOBILE --}}
                                <div class="sm:hidden flex flex-col gap-2">
                                    <button type="submit"
                                        class="w-full px-4 py-2.5 bg-[#1e90ff] text-white rounded-lg hover:bg-blue-600 transition font-medium text-xs flex items-center justify-center gap-2">
                                        <i class="fas fa-magic"></i> Generate Tree
                                    </button>
                                    <a href="{{ route('tournament.index') }}"
                                        class="w-full px-4 py-2.5 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition font-medium text-xs text-center">
                                        Batal
                                    </a>
                                </div>

                                {{-- ACTION BUTTONS - TABLET & DESKTOP --}}
                                <div class="hidden sm:flex flex-row gap-3 justify-end">
                                    <a href="{{ route('tournament.index') }}"
                                        class="px-6 py-2 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-700 transition font-medium text-sm text-center">
                                        Batal
                                    </a>
                                    <button type="submit"
                                        class="px-6 py-2 bg-[#1e90ff] text-white rounded-lg hover:bg-blue-600 transition font-medium text-sm flex items-center justify-center gap-2">
                                        <i class="fas fa-magic"></i> Generate Tree
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Right Column - Info Sidebar --}}
                        <div class="lg:col-span-1">
                            {{-- STATUS CARD --}}
                            <div class="bg-[#2c2c2c] border border-gray-700 rounded-lg p-5 space-y-4 mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-medium">Status Active</span>
                                </div>
                                <div class="pt-4 border-t border-gray-700 space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Tournament:</span>
                                        <span class="font-medium truncate ml-2">{{ $tournament->name }}</span>
                                    </div>
                                    @if (isset($tournament->event_id))
                                        <div class="flex justify-between">
                                            <span class="text-gray-400">Event:</span>
                                            <span
                                                class="font-medium truncate ml-2">{{ $tournament->event->name ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Fighters:</span>
                                        <span class="font-medium">{{ $championship->users->count() }} Peserta</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Areas:</span>
                                        <span class="font-medium">{{ $fightingAreas }} Area</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Type:</span>
                                        <span class="font-medium text-xs">{{ $hasPreliminary ? 'Preliminary' : '' }}
                                            {{ $treeType == 0 ? 'Playoff' : 'SE' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- QUICK INFO CARD --}}
                            <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-5 text-sm space-y-2">
                                <p class="text-blue-300 font-medium flex items-center gap-2">
                                    <i class="fas fa-lightbulb"></i> Tips
                                </p>
                                <p class="text-gray-300 text-xs leading-relaxed">
                                    Pastikan semua pengaturan sudah benar sebelum generate tree, karena perubahan di
                                    kemudian hari akan mereset bracket.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- TAB NAVIGATION FOR TREE & FIGHT LIST --}}
                    @if ($championship->fightersGroups->count() > 0)
                        <div class="space-y-6">
                            <div
                                class="flex gap-6 border-b border-gray-700 overflow-x-auto sticky top-28 bg-neutral-900/95 backdrop-blur py-2">
                                <button type="button" onclick="switchTab(event, 'tree')" id="treeTab"
                                    class="tab-button active px-4 py-3 font-medium text-sm whitespace-nowrap transition border-b-2 border-[#1e90ff] text-white">
                                    <i class="fas fa-code-branch mr-2"></i> Tournament Tree
                                </button>
                                <button type="button" onclick="switchTab(event, 'fights')" id="fightsTab"
                                    class="tab-button px-4 py-3 font-medium text-sm whitespace-nowrap text-gray-400 transition border-b-2 border-transparent hover:border-gray-700">
                                    <i class="fas fa-fist-raised mr-2"></i> Fight List
                                </button>
                            </div>

                            {{-- TREE TAB CONTENT --}}
                            <div id="treeContent" class="space-y-6">
                                <div class="bg-[#2c2c2c] border border-gray-700 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-6">Tournament Bracket</h3>

                                    {{-- Responsive Bracket Container --}}
                                    <div class="overflow-x-auto -mx-6 px-6" style="max-height: 600px; overflow-y: auto;">
                                        <div class="inline-block min-w-full">
                                            {{-- Include Tree Components --}}
                                            @if ($championship->hasPreliminary())
                                                @include('dash.admin.tournament.partials.tree.preliminary')
                                            @else
                                                @if ($championship->isSingleEliminationType())
                                                    @include(
                                                        'dash.admin.tournament.partials.tree.singleElimination',
                                                        ['hasPreliminary' => 0]
                                                    )
                                                @elseif ($championship->isPlayOffType())
                                                    @include('dash.admin.tournament.partials.tree.playOff')
                                                @else
                                                    <div class="text-center py-12 text-gray-400">
                                                        <i class="fas fa-exclamation-circle text-3xl mb-3"></i>
                                                        <p>Tipe tournament tidak dikenali</p>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-6 text-xs text-gray-500 text-center">
                                        <p><i class="fas fa-arrow-right mr-2"></i>Scroll untuk melihat lebih banyak</p>
                                    </div>
                                </div>
                            </div>

                            {{-- FIGHT LIST TAB CONTENT --}}
                            <div id="fightsContent" class="hidden space-y-6">
                                <div class="bg-[#2c2c2c] border border-gray-700 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold mb-6">Fight Schedule</h3>

                                    {{-- Include Fights Component --}}
                                    <div class="space-y-6">
                                        @include('dash.admin.tournament.partials.fights')
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-[#2c2c2c] border border-gray-700 rounded-lg p-12 text-center">
                            <div class="mb-4">
                                <i class="fas fa-layer-group text-4xl text-gray-600"></i>
                            </div>
                            <p class="text-gray-400 text-lg">Generate tree terlebih dahulu</p>
                            <p class="text-gray-500 text-sm mt-2">Klik tombol "Generate Tree" untuk membuat bracket
                                tournament</p>
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <style>
        /* Tab active state */
        .tab-button.active {
            border-bottom-color: #1e90ff;
            color: white;
        }

        .tab-button:not(.active) {
            border-bottom-color: transparent;
        }

        .tab-button:not(.active):hover {
            border-bottom-color: #4b5563;
        }

        /* Smooth tab switching */
        #treeContent,
        #fightsContent {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Bracket responsive styles */
        .table-bordered {
            width: 100%;
            border-collapse: collapse;
            background: #2c2c2c;
            border: 1px solid #444;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #444;
            padding: 0.75rem;
            text-align: center;
            color: #ccc;
            font-size: 0.875rem;
        }

        .table-bordered th {
            background: #1c1c1c;
            font-weight: 600;
            color: #999;
        }

        .table-bordered tr:hover {
            background: #333;
            transition: background 0.2s ease;
        }

        /* Mobile responsif */
        @media (max-width: 768px) {
            .table-bordered {
                font-size: 0.75rem;
            }

            .table-bordered th,
            .table-bordered td {
                padding: 0.5rem;
            }

            .p-10 {
                padding: 0.5rem !important;
            }
        }
    </style>

    <script>
        function switchTab(event, tabName) {
            event.preventDefault();

            const treeTab = document.getElementById('treeTab');
            const fightsTab = document.getElementById('fightsTab');
            const treeContent = document.getElementById('treeContent');
            const fightsContent = document.getElementById('fightsContent');

            if (tabName === 'tree') {
                // Tree tab active
                treeTab.classList.add('active', 'text-white', 'border-[#1e90ff]');
                treeTab.classList.remove('text-gray-400', 'border-transparent');

                fightsTab.classList.remove('active', 'text-white', 'border-[#1e90ff]');
                fightsTab.classList.add('text-gray-400', 'border-transparent');

                treeContent.classList.remove('hidden');
                fightsContent.classList.add('hidden');
            } else {
                // Fights tab active
                fightsTab.classList.add('active', 'text-white', 'border-[#1e90ff]');
                fightsTab.classList.remove('text-gray-400', 'border-transparent');

                treeTab.classList.remove('active', 'text-white', 'border-[#1e90ff]');
                treeTab.classList.add('text-gray-400', 'border-transparent');

                fightsContent.classList.remove('hidden');
                treeContent.classList.add('hidden');
            }
        }
    </script>
@endsection