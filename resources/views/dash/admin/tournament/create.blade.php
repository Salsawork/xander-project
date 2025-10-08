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

                        <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-6">
                            <div>
                                <label for="name" class="block text-xs sm:text-sm font-medium text-gray-300 mb-1">
                                    Judul Tournament <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan judul tournament">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
                            <a href="{{ route('tournament.index') }}"
                                class="w-full sm:w-auto px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded text-center text-sm transition order-2 sm:order-1">
                                Batal
                            </a>
                            <button type="submit" 
                                class="w-full sm:w-auto px-6 py-2 bg-[#1e90ff] hover:bg-blue-600 text-white rounded text-sm transition order-1 sm:order-2">
                                Simpan Tournament
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
@endsection