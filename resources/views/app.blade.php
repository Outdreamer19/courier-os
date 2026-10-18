<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        @php($currentTenant = app(\App\Support\Tenancy\TenantManager::class)->current())
        @if ($currentTenant)
            {{-- Each courier's own icon: its uploaded logo, else the CourierOS mark.
                 Today Shipping's icon predates logo uploads, so it keeps its file. --}}
            @php($tenantIcon = $currentTenant->logo_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($currentTenant->logo_path)
                : ($currentTenant->subdomain === 'today' ? '/branding/today-shipping-icon.png' : '/branding/courieros-icon.svg'))
            <link rel="icon" href="{{ $tenantIcon }}">
            <link rel="apple-touch-icon" href="{{ $currentTenant->logo_path || $currentTenant->subdomain === 'today' ? $tenantIcon : '/branding/courieros-icon-180.png' }}">
        @else
            @include('partials.central-head')
        @endif

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ $meta['title'] ?? ($currentTenant?->name ?? config('courieros.name', 'CourierOS')) }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
