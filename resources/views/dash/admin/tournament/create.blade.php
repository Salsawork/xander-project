@extends('app')
@section('title', 'Admin Dashboard - Tambah tournament')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            {{-- Sidebar --}}
            @include('partials.sidebar')

            {{-- Main Content --}}
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <div class="flex items-center mb-6">
                    <a href="{{ route('tournament.index') }}" class="text-gray-400 hover:text-white mr-4">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h1 class="text-3xl font-extrabold">
                        Tambah Tournament Baru
                    </h1>
                </div>

                @if ($errors->any())
                    <div class="mx-8 mb-4 bg-red-500 text-white px-4 py-2 rounded">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="px-8">
                    <form action="{{ route('tournament.store') }}" method="POST" class="space-y-6" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                            <!-- Kolom Kiri -->
                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Judul tournament <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                        class="w-full rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <a href="{{ route('tournament.index') }}"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded">
                                Batal
                            </a>
                            <button type="submit" class="px-4 py-2 bg-[#1e90ff] hover:bg-blue-600 text-white rounded">
                                Simpan Tournament
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection
