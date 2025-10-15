    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Xander Billiard')</title>

        {{-- Favicon / Logo --}}
        <link rel="icon" type="image/png" href="{{ asset('images/logo/logo-x.png') }}?v={{ @filemtime(public_path('images/logo/logo-x.png')) }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo/logo-x.png') }}?v={{ @filemtime(public_path('images/logo/logo-x.png')) }}">
        <meta name="theme-color" content="#111111">

        @vite(['resources/js/app.js', 'resources/css/app.css'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @stack('styles')
    </head>

    <body>
        <div class="w-full">
            @if (!Request::is('login') && !Request::is('register') && !Request::is('dashboard/*') && !Request::is('venue/*') && !Request::is('order/*') && !Request::is('athlete/*'))
                @include('partials.navbar')
            @endif

            @yield('content')

            @if (!Request::is('login') && !Request::is('register') && !Request::is('dashboard/*') && !Request::is('venue/*') && !Request::is('order/*') && !Request::is('athlete/*'))
                @include('partials.footer')
            @endif
        </div>

        @stack('scripts')
    </body>
    </html>
