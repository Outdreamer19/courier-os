@extends('errors.layout')

@section('code', 'Error 429')
@section('title', 'Too many attempts')

@section('message')
    We’ve paused requests from your connection for a moment to keep the service
    stable. Wait about a minute, then try again.
@endsection
