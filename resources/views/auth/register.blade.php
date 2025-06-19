@extends('app')
@section('title', 'Register Page - Xander Billiard')

@section('content')
    <div class="flex min-h-screen">
        <div class="hidden w-1/2 bg-cover bg-center lg:flex"
            style="background-image: url('{{ asset('/images/bg/login-bg.png') }}')">
            <div class="flex h-full w-full items-center justify-center ">
                <img src="{{ asset('/images/big-logo.png') }}" alt="Logo" class="h-64 w-64" />
            </div>
        </div>
        <div class="flex w-full items-center justify-center bg-neutral-900 lg:w-1/2">
            <div class="w-full max-w-sm rounded-lg bg-neutral-800 p-8 shadow-md">
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
