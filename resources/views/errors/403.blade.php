@extends('errors.layout')

@section('code', 'Error 403')
@section('title', 'You don’t have access to this')

{{--
    403 covers three very different situations in this app, and the generic
    "Forbidden" served previously left people stuck with no idea which one
    they were in. The exception message set by ResolveTenant /
    EnsureTenantSubscribed / EnsureUserBelongsToTenant is specific and safe to
    show, so prefer it when present.
--}}
@section('message')
    {{ $exception?->getMessage() ?: 'This page belongs to someone else, or your account isn’t permitted to open it. If you think that’s wrong, ask the account owner to check your access.' }}
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">Go to the home page</a>
    @auth
        <a class="btn btn-ghost" href="{{ url('/dashboard') }}">My dashboard</a>
    @else
        <a class="btn btn-ghost" href="{{ url('/login') }}">Sign in</a>
    @endauth
@endsection
