@auth
    @if (auth()->user()->roles == 'admin')
        @include('partials.sidebar.admin')
    @elseif(auth()->user()->roles == 'user')
        @include('partials.sidebar.user')
    @elseif(auth()->user()->roles == 'venue')
        @include('partials.sidebar.venue')
    @elseif(auth()->user()->roles == 'athlete')
        @include('partials.sidebar.athlete')
    @endif
@endauth
