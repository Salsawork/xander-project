@extends('app')
@section('title', 'Index Dashboard - Xander Billiard')

@section('content')
    @if (Auth::user()->roles == 'admin')
        @include('dash.admin.overview')
    @elseif (Auth::user()->roles == 'user')
        @include('dash.user.profile')
    @elseif (Auth::user()->roles == 'venue')
        @include('dash.venue.dashboard')
    @elseif (Auth::user()->roles == 'athlete')
        @include('dash.athlete.dashboard')
    @endif
@endsection
