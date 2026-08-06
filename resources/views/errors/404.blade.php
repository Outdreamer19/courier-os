@extends('errors.layout')

@section('code', 'Error 404')
@section('title', 'We couldn’t find that page')

@section('message')
    The link may be out of date, or the address may have a typo in it. Nothing
    is broken on your side.
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">Go to the home page</a>
    <a class="btn btn-ghost" href="{{ url('/contact') }}">Contact support</a>
@endsection
