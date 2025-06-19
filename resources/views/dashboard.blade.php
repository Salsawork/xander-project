@extends('app')
@section('title', 'Index Dashboard - Xander Billiard')

@section('content')
    @if (Auth::user()->roles == 'admin')
        @include('dash.admin.overview')
    @elseif (Auth::user()->roles == 'user')
        @include('dash.user.profile')
    @endif
@endsection
