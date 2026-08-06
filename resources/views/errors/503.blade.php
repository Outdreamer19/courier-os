@extends('errors.layout')

@section('code', 'Maintenance')
@section('title', 'We’ll be back shortly')

@section('message')
    We’re deploying an update. This usually takes under a minute — refresh the
    page and you should be straight back in.
@endsection

@section('actions')
    <a class="btn btn-primary" href="{{ url()->current() }}">Try again</a>
@endsection
