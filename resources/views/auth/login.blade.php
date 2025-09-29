@extends('app')
@section('title', 'Login Page - Xander Billiard')

@push('styles')
    <style>
        /* Tidak mengubah layout: hanya atur perilaku scroll & warna latar dokumen */
        html,
        body {
            background: #0a0a0a;
            /* hilangkan flash putih di tepi/overscroll */
            overscroll-behavior-y: none;
            /* cegah bounce browser modern */
            overscroll-behavior-x: none;
            overflow-x: hidden;
        }

        /* Penting: JANGAN beri background ke semua img agar PNG transparan tetap transparan */
        img {
            display: block;
        }
    </style>
@endpush

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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none"
                        stroke="currentColor" stroke-width="2">
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

                    <input type="text" name="login" placeholder="Username or Phone Number"
                        class="mb-4 w-full rounded-md border border-gray-500 bg-transparent px-3 py-2 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        value="{{ old('login') }}" required />
                    @error('login')
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

                    <a href="{{ route('oauth.google.redirect') }}"
                        class="flex items-center justify-center gap-2 w-full rounded-md border border-blue-500 px-4 py-2 text-blue-500 hover:bg-blue-500 hover:text-white transition-colors text-center">
                        {{-- Icon Google kecil (opsional) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="18" height="18"
                            aria-hidden="true">
                            <path fill="#FFC107"
                                d="M43.6 20.5H42v-.1H24v7.2h11.3C33.7 31.4 29.3 34 24 34c-7 0-12.8-5.7-12.8-12.8S17 8.5 24 8.5c3.2 0 6.1 1.2 8.3 3.2l5.1-5.1C33.7 3.6 29.1 1.8 24 1.8 12 1.8 2.3 11.5 2.3 23.5S12 45.2 24 45.2c11.3 0 21-8.2 21-21 0-1.6-.2-3.1-.6-4.7z" />
                            <path fill="#FF3D00"
                                d="M6.3 14.7l5.9 4.3C14 15.2 18.6 12 24 12c3.2 0 6.1 1.2 8.3 3.2l5.1-5.1C33.7 6.1 29.1 4.3 24 4.3 16.1 4.3 9.5 8.7 6.3 14.7z" />
                            <path fill="#4CAF50"
                                d="M24 42.7c5.2 0 9.8-1.7 13.3-4.6l-6.1-5c-2.1 1.5-4.7 2.4-7.2 2.4-5.2 0-9.6-3.3-11.2-7.9l-6.1 4.7c3.2 6.1 9.8 10.4 17.3 10.4z" />
                            <path fill="#1976D2"
                                d="M43.6 20.5H42v-.1H24v7.2h11.3c-1 3.2-3.5 5.8-6.7 7.2l6.1 5c-3.4 2.3-7.6 3.7-12.7 3.7-7.5 0-14.1-4.3-17.3-10.4l6.1-4.7c1.6 4.6 6 7.9 11.2 7.9 5.3 0 9.7-2.6 12.2-6.6l.9-1.4c.9-1.9 1.4-4 1.4-6.3 0-1.6-.2-3.1-.6-4.7z" />
                        </svg>
                        <span>Masuk dengan Google</span>
                    </a>
                    

                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // ==== iOS edge-bounce guard: tetap bisa scroll, tanpa putih-putih di tepi ====
        (function() {
            const isIOS = /iP(ad|hone|od)/.test(navigator.userAgent);
            if (!isIOS) return;

            let startY = 0;
            window.addEventListener('touchstart', (e) => {
                if (e.touches && e.touches.length) startY = e.touches[0].clientY;
            }, {
                passive: true
            });

            window.addEventListener('touchmove', (e) => {
                if (!e.touches || !e.touches.length) return;
                const scroller = document.scrollingElement || document.documentElement;
                const atTop = scroller.scrollTop <= 0;
                const atBottom = (scroller.scrollTop + window.innerHeight) >= (scroller.scrollHeight - 1);
                const dy = e.touches[0].clientY - startY;

                // Di tepi atas geser ke bawah, atau di tepi bawah geser ke atas -> tahan
                if ((atTop && dy > 0) || (atBottom && dy < 0)) e.preventDefault();
            }, {
                passive: false
            });
        })();
    </script>
@endpush
