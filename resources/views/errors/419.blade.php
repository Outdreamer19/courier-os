@extends('errors.layout')

@section('code', 'Error 419')
@section('title', 'Your session expired')

@section('message')
    You were away long enough that we signed you out for safety. Sign in again
    and you’ll pick up where you left off — nothing was lost.
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ url('/login') }}">Sign in again</a>
@endsection
