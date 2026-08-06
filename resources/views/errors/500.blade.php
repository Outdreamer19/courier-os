@extends('errors.layout')

@section('code', 'Error 500')
@section('title', 'Something went wrong on our end')

@section('message')
    This one is ours, not yours. The error has been logged and we’re on it. Try
    again in a few minutes — your data is safe.
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ url('/') }}">Back to the home page</a>
    <a class="btn btn-ghost" href="{{ url('/contact') }}">Report this</a>
@endsection
