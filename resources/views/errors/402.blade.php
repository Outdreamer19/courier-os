@extends('errors.layout')

@section('code', 'Error 402')
@section('title', 'This courier site is paused')

{{--
    Shown by EnsureTenantSubscribed when a courier has no active CourierOS
    subscription and its trial has ended. Customers get a plain explanation;
    the courier's owner gets a way to restart billing.
--}}
@php
    $pausedTenant = app(\App\Support\Tenancy\TenantManager::class)->current();
    $viewer = auth()->user();
    $canRestart = $pausedTenant && $viewer && $viewer->isOwner();
    $central = (app()->environment('local') ? 'http' : 'https').'://'.config('courieros.central_domain');
@endphp

@section('message')
    @if ($canRestart)
        Your CourierOS subscription is not active, so your site and portals are paused. Restart billing to switch everything back on. Your customers, packages and settings are all still here.
    @else
        {{ $pausedTenant?->name ?? 'This courier' }} has paused their online portal for now. Please contact them directly about your packages.
    @endif
@endsection

@section('actions')
    @if ($canRestart)
        <a class="btn btn-primary" href="{{ $central }}/billing/checkout/{{ $pausedTenant->id }}">Restart subscription</a>
    @endif
    <a class="btn btn-ghost" href="{{ url('/') }}">Go to the home page</a>
@endsection
