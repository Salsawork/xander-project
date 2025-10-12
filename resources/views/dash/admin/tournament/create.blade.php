@extends('app')
@section('title', 'Admin Dashboard - Tambah Tournament')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')

            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                
                <div class="mt-20 sm:mt-28 px-4 sm:px-8">
                    <div class="flex items-center mb-6">
                        <a href="{{ route('tournament.index') }}" class="text-gray-400 hover:text-white mr-3 sm:mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl sm:text-3xl font-extrabold">
                            Tambah Tournament Baru
                        </h1>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-500 text-white px-4 py-3 rounded text-sm">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('tournament.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Tournament Name -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-6">
                            <div>
                                <label for="name" class="block text-xs sm:text-sm font-medium text-gray-300 mb-1">
                                    Judul Tournament <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan judul tournament">
                            </div>
                        </div>

                        <!-- Tournament Settings -->
                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-6">
                            <h2 class="text-lg font-semibold mb-4">Tournament Settings</h2>
                            
                            <!-- Row 1: Preliminary, Group Size, Fighter Qty -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="hasPreliminary" class="block text-sm font-medium text-gray-300 mb-1">
                                        Preliminary
                                    </label>
                                    <select name="hasPreliminary" id="hasPreliminary"
                                        class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="0" {{ old('hasPreliminary', 0) == 0 ? 'selected' : '' }}>NO</option>
                                        <option value="1" {{ old('hasPreliminary') == 1 ? 'selected' : '' }}>YES</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="preliminaryGroupSize" class="block text-sm font-medium text-gray-300 mb-1">
                                        Preliminary Group Size
                                    </label>
                                    <select name="preliminaryGroupSize" id="preliminaryGroupSize"
                                        class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="3" {{ old('preliminaryGroupSize', 3) == 3 ? 'selected' : '' }}>3</option>
                                        <option value="4" {{ old('preliminaryGroupSize') == 4 ? 'selected' : '' }}>4</option>
                                        <option value="5" {{ old('preliminaryGroupSize') == 5 ? 'selected' : '' }}>5</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="numFighters" class="block text-sm font-medium text-gray-300 mb-1">
                                        Fighter Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <select name="numFighters" id="numFighters" required
                                        class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        @for ($i = 2; $i <= 60; $i++)
                                            <option value="{{ $i }}" {{ old('numFighters', 8) == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Team, Tree Type, Fighting Areas -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="isTeam" class="block text-sm font-medium text-gray-300 mb-1">
                                        Team?
                                    </label>
                                    <select name="isTeam" id="isTeam"
                                        class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="0" {{ old('isTeam', 0) == 0 ? 'selected' : '' }}>NO</option>
                                        <option value="1" {{ old('isTeam') == 1 ? 'selected' : '' }}>YES</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="treeType" class="block text-sm font-medium text-gray-300 mb-1">
                                        Tree Type
                                    </label>
                                    <select name="treeType" id="treeType"
                                        class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="0" {{ old('treeType', 1) == 0 ? 'selected' : '' }}>Playoff</option>
                                        <option value="1" {{ old('treeType', 1) == 1 ? 'selected' : '' }}>Single Elimination</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="fightingAreas" class="block text-sm font-medium text-gray-300 mb-1">
                                        Fighting Areas
                                    </label>
                                    <select name="fightingAreas" id="fightingAreas"
                                        class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="1" {{ old('fightingAreas', 1) == 1 ? 'selected' : '' }}>1</option>
                                        <option value="2" {{ old('fightingAreas') == 2 ? 'selected' : '' }}>2</option>
                                        <option value="4" {{ old('fightingAreas') == 4 ? 'selected' : '' }}>4</option>
                                        <option value="8" {{ old('fightingAreas') == 8 ? 'selected' : '' }}>8</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 3: Event Selection -->
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="event_id" class="block text-sm font-medium text-gray-300 mb-1">
                                        Pilih Event <span class="text-red-500">*</span>
                                    </label>
                                    <select name="event_id" id="event_id" required
                                        class="w-full rounded-md border border-gray-600 bg-[#1a1a1a] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">-- Pilih Event --</option>
                                        @foreach($events as $event)
                                            <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                                {{ $event->name }} - {{ \Carbon\Carbon::parse($event->start_date)->format('d M Y') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-400">Tournament ini akan terhubung dengan event yang dipilih</p>
                                </div>
                            </div>

                            <!-- Info Box -->
                            <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4 mt-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                                    <div class="text-sm text-gray-300">
                                        <p class="font-medium text-blue-300 mb-1">Tournament akan dibuat dengan bracket otomatis</p>
                                        <p>Setelah tournament dibuat, Anda dapat mengedit dan mengatur peserta di halaman edit.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
                            <a href="{{ route('tournament.index') }}"
                                class="w-full sm:w-auto px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-center text-sm transition order-2 sm:order-1">
                                Batal
                            </a>
                            <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2 bg-[#1e90ff] hover:bg-blue-600 text-white rounded text-sm transition order-1 sm:order-2">
                                Buat Tournament & Generate Bracket
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection