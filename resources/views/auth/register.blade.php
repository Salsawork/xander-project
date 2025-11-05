@extends('app')
@section('title', 'Register Page - Xander Billiard')

@push('styles')
<style>
  /* Tidak mengubah layout: hanya atur perilaku scroll & warna latar dokumen */
  html, body {
    background: #0a0a0a;           /* hilangkan flash putih di tepi/overscroll */
    overscroll-behavior-y: none;   /* cegah bounce (Chrome/Edge/Firefox/Android) */
    overscroll-behavior-x: none;
    overflow-x: hidden;
  }
  /* Penting: jangan beri background ke img agar PNG transparan tetap transparan */
  img { display: block; }
</style>
@endpush

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

                    {{-- Full Name --}}
                    <input
                        type="text"
                        name="name"
                        placeholder="Full Name"
                        value="{{ old('name') }}"
                        class="mb-2 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('name')
                        <p class="text-red-500 text-sm mb-3">{{ $message }}</p>
                    @enderror

                    {{-- Phone (opsional) --}}
                    <input
                        type="tel"
                        name="phone"
                        placeholder="Phone Number (optional)"
                        value="{{ old('phone') }}"
                        class="mb-2 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('phone')
                        <p class="text-red-500 text-sm mb-3">{{ $message }}</p>
                    @enderror

                    {{-- Email --}}
                    <input
                        type="email"
                        name="email"
                        placeholder="Email"
                        value="{{ old('email') }}"
                        class="mb-2 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('email')
                        <p class="text-red-500 text-sm mb-3">{{ $message }}</p>
                    @enderror

                    {{-- Password --}}
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        class="mb-2 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('password')
                        <p class="text-red-500 text-sm mb-3">{{ $message }}</p>
                    @enderror

                    {{-- Confirm Password --}}
                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Confirm Password"
                        class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('password_confirmation')
                        <p class="text-red-500 text-sm mb-3">{{ $message }}</p>
                    @enderror

                    <button type="submit"
                        class="w-full rounded-md bg-blue-500 px-4 py-2 text-white hover:bg-blue-600">
                        Sign Up
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
  // ==== iOS edge-bounce guard: tetap bisa scroll, tanpa putih-putih di tepi ====
  (function(){
    const isIOS = /iP(ad|hone|od)/.test(navigator.userAgent);
    if (!isIOS) return;

    let startY = 0;
    window.addEventListener('touchstart', (e) => {
      if (e.touches && e.touches.length) startY = e.touches[0].clientY;
    }, { passive: true });

    window.addEventListener('touchmove', (e) => {
      if (!e.touches || !e.touches.length) return;
      const scroller = document.scrollingElement || document.documentElement;
      const atTop    = scroller.scrollTop <= 0;
      const atBottom = (scroller.scrollTop + window.innerHeight) >= (scroller.scrollHeight - 1);
      const dy = e.touches[0].clientY - startY;

      // Di tepi atas geser ke bawah, atau di tepi bawah geser ke atas -> tahan
      if ((atTop && dy > 0) || (atBottom && dy < 0)) e.preventDefault();
    }, { passive: false });
  })();
</script>
@endpush
