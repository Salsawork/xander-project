@extends('app')
@section('title', 'Verify Email - Xander Billiard')

@section('content')
<div class="flex min-h-screen items-center justify-center bg-neutral-900">
    <div class="w-full max-w-sm rounded-lg bg-neutral-800 p-8 shadow-md text-center">

        <h2 class="mb-4 text-xl font-semibold text-white">Insert Verification Code</h2>
        <p class="mb-4 text-sm text-gray-400">
            A verification code has been sent to {{ $email }}
        </p>

        <form method="POST" action="{{ route('verification.verify') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <input type="text" name="otp_code" placeholder="Enter 6 digit code"
                   class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white text-center text-lg tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500"
                   required maxlength="6">

            @error('otp_code')
                <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="mb-4 w-full rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                Verify
            </button>

            <p class="text-sm text-gray-400">
                Didn't receive code?
                <a href="{{ route('verification.form', ['email' => $email]) }}" class="text-blue-400 hover:underline">Resend</a>
            </p>
        </form>
    </div>
</div>
@endsection
