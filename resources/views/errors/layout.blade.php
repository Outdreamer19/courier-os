{{--
    Shared shell for every error page.

    Deliberately dependency-free: no Vite assets, no Inertia, no database
    lookups. An error page that itself needs the asset manifest or a working
    tenant binding will fail exactly when it is most needed (bad deploy,
    suspended tenant, database down), leaving the visitor with a blank white
    screen instead of an explanation.
--}}
@php
    /**
     * An error page on a tenant's own domain must not say "CourierOS" — that
     * is the one place a white-label customer's users are most likely to look
     * closely. TenantConfig reads the already-resolved tenant object, so this
     * adds no query; rescue() covers the case where the failure happened
     * before the tenant was ever bound.
     */
    $brandName = rescue(
        fn () => app(\App\Support\Tenancy\TenantConfig::class)->name(),
        config('app.name', 'CourierOS'),
        report: false,
    );
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') — {{ $brandName }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: #210d02;
            background:
                radial-gradient(900px 460px at 50% -10%, rgba(252, 213, 25, .28), transparent 62%),
                radial-gradient(700px 420px at 90% 10%, rgba(198, 223, 250, .45), transparent 62%),
                #fffbf5;
        }
        .card {
            width: 100%;
            max-width: 30rem;
            background: rgba(255, 255, 255, .82);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(33, 13, 2, .08);
            border-radius: 1rem;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 30px 70px -35px rgba(33, 13, 2, .4);
        }
        .code {
            display: inline-block;
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #aa8322;
            background: rgba(252, 213, 25, .22);
            border-radius: 999px;
            padding: .3rem .7rem;
        }
        h1 { margin: 1.1rem 0 0; font-size: 1.5rem; line-height: 1.25; letter-spacing: -.02em; }
        p  { margin: .75rem 0 0; font-size: .9375rem; line-height: 1.6; color: rgba(79, 77, 73, .82); }
        .actions { margin-top: 1.75rem; display: flex; gap: .6rem; justify-content: center; flex-wrap: wrap; }
        a.btn {
            display: inline-block;
            text-decoration: none;
            font-size: .875rem;
            font-weight: 600;
            padding: .7rem 1.3rem;
            border-radius: .55rem;
            transition: opacity .15s ease;
        }
        a.btn:hover { opacity: .88; }
        .btn-primary { background: #210d02; color: #fff; }
        .btn-ghost { background: #fff; color: #210d02; border: 1px solid rgba(33, 13, 2, .12); }
        .foot { margin-top: 1.75rem; font-size: .75rem; color: rgba(79, 77, 73, .6); }
        @media (prefers-color-scheme: dark) {
            body { background: #14100c; color: #f6f1ea; }
            .card { background: rgba(255, 255, 255, .05); border-color: rgba(255, 255, 255, .1); }
            p, .foot { color: rgba(246, 241, 234, .68); }
            .btn-primary { background: #fcd519; color: #210d02; }
            .btn-ghost { background: transparent; color: #f6f1ea; border-color: rgba(255, 255, 255, .18); }
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="code">@yield('code')</span>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>

        <div class="actions">
            @hasSection('actions')
                @yield('actions')
            @else
                <a class="btn btn-primary" href="{{ url('/') }}">Back to safety</a>
            @endif
        </div>

        <p class="foot">{{ $brandName }}</p>
    </main>
</body>
</html>
