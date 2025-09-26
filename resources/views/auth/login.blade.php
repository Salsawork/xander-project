@extends('app')
@section('title', 'Login Page - Xander Billiard')

@section('content')
    <div class="flex min-h-screen">
        <!-- Kiri: gambar -->
        <div class="hidden w-1/2 bg-cover bg-center lg:flex"
             style="background-image: url('{{ asset('/images/bg/login-bg.png') }}')">
            <div class="flex h-full w-full items-center justify-center">
                <a href="{{ route('index') }}">
                    <img src="{{ asset('/images/logo/logo-xander.png') }}" alt="Logo" class="h-50 w-67" />
                </a>
            </div>
        </div>

        <!-- Kanan: card login -->
        <div class="flex w-full items-center justify-center bg-neutral-900 lg:w-1/2">
            <div class="w-full max-w-sm rounded-lg bg-neutral-800 p-8 shadow-md">

                <!-- Tombol panah saja (kembali ke landing page) -->
                <a href="{{ route('index') }}"
                   class="mb-6 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white border-2 border-black text-black shadow transition
                          hover:bg-gray-100 active:scale-[.98] focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 focus:ring-offset-neutral-800"
                   aria-label="Kembali ke Home">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <h2 class="mb-4 text-center text-xl font-semibold text-white">Login</h2>
                <p class="mb-4 text-center text-sm text-gray-400">
                    Don't have account?
                    <a href="{{ route('register') }}" class="text-blue-400 hover:underline">Sign Up</a>
                </p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <input type="text" name="username" placeholder="Phone number or Email"
                           class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="{{ old('username') }}" required />
                    @error('username')
                        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                    @enderror

                    <input type="password" name="password" placeholder="Password"
                           class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required />
                    @error('password')
                        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                            class="mb-4 w-full rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Sign In
                    </button>

                    <div class="mb-4 border-t border-gray-600"></div>

                    <a href="#"
                       class="block w-full rounded-md border border-blue-500 px-4 py-2 text-blue-500 hover:bg-blue-500 hover:text-white transition-colors text-center">
                        Google
                    </a>
                </form>
            </div>
        </div>
    </div>
@endsection

