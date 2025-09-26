@extends('app')
@section('title', 'Register Page - Xander Billiard')

@section('content')
    <div class="flex min-h-screen">
        <div class="hidden w-1/2 bg-cover bg-center lg:flex"
            style="background-image: url('{{ asset('/images/bg/login-bg.png') }}')">
            <div class="flex h-full w-full items-center justify-center ">
                <a href="{{ route('index') }}">
                    <img src="{{ asset('/images/logo/logo-xander.png') }}" alt="Logo" class="h-50 w-67" />
                </a>
            </div>
        </div>
        <div class="flex w-full items-center justify-center bg-neutral-900 lg:w-1/2">
            <div class="w-full max-w-sm rounded-lg bg-neutral-800 p-8 shadow-md">

                <a href="{{ route('index') }}"
                   class="mb-6 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white border-2 border-black text-black shadow transition
                          hover:bg-gray-100 active:scale-[.98] focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2 focus:ring-offset-neutral-800"
                   aria-label="Kembali ke Home">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <h2 class="mb-4 text-center text-xl font-semibold text-white">Sign Up</h2>
                <p class="mb-4 text-center text-sm text-gray-400">
                    Already have account?
                    <a href="{{ route('login') }}" class="text-blue-400 hover:underline">Login</a>
                </p>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <input type="text" placeholder="Full Name" name="name"
                        class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('name')
                        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                    @enderror
                    <input type="text" placeholder="Phone number or Email or Username" name="username"
                        class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('username')
                        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                    @enderror
                    <input type="password" placeholder="Password" name="password"
                        class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('password')
                        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                    @enderror
                    <input type="password" placeholder="Confirm Password" name="password_confirmation"
                        class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('password_confirmation')
                        <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                    @enderror
                    <button type="submit"
                        class="mb-4 w-full rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Sign Up
                    </button>
                    <div class="mb-4 border-t border-gray-600"></div>
                    <button type="button"
                        class="w-full rounded-md border border-blue-500 px-4 py-2 text-blue-500 hover:bg-blue-500 hover:text-white transition-colors">Google</button>
                </form>
            </div>
        </div>
    </div>
@endsection

