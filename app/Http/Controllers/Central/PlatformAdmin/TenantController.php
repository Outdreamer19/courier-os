<?php

namespace App\Http\Controllers\Central\PlatformAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Platform\PlatformStatsService;
use App\Support\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request, PlatformStatsService $stats): Response
    {
        // Usage/health figures for every tenant, computed in a handful of
        // grouped queries rather than one query per row.
        $health = collect($stats->tenantHealth(PHP_INT_MAX))->keyBy('id');

        $tenants = Tenant::query()
            ->withCount('subscriptions')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Tenant $tenant) use ($health) {
                $row = $health->get($tenant->id, []);

                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'custom_domain' => $tenant->custom_domain,
                    'url' => $row['url'] ?? null,
                    'status' => $tenant->status,
                    'currency' => $tenant->currency,
                    'subscription_status' => $row['subscription_status'] ?? 'none',
                    'customers' => $row['customers'] ?? 0,
                    'packages' => $row['packages'] ?? 0,
                    'packages_30d' => $row['packages_30d'] ?? 0,
                    'last_activity_human' => $row['last_activity_human'] ?? null,
                    'health' => $row['health'] ?? 'onboarding',
                    'created_at' => $tenant->created_at->toDateString(),
                ];
            });

        return Inertia::render('central/admin/Tenants', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Create a tenant directly, active from the moment it's created, with an
     * owner account ready to sign in — no Stripe checkout involved.
     *
     * This is the "someone messages you asking to try CourierOS" path: the
     * public /signup form is the only other way to create a tenant, and it
     * requires a working Stripe checkout. Until that's configured (or for
     * pilots you want to comp), this is how a tenant gets created at all.
     * The owner's password is generated and shown once in the confirmation
     * message — it is hashed on save and cannot be retrieved again, so it
     * must be copied out to the owner immediately. `php artisan tenant:user`
     * remains the way to reset it later.
     */
    public function store(Request $request, TenantManager $tenants): RedirectResponse
    {
        $reserved = (array) config('courieros.reserved_subdomains', []);

        $request->merge([
            'subdomain' => Str::slug((string) $request->input('subdomain')),
        ]);

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required', 'string', 'min:2', 'max:63',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn($reserved),
                Rule::unique('tenants', 'subdomain'),
            ],
            'currency' => ['required', Rule::in(['USD', 'JMD'])],
            'customer_reference_prefix' => ['required', 'string', 'min:2', 'max:8', 'alpha'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ], [
            'subdomain.not_in' => 'That subdomain is reserved. Please choose another.',
            'subdomain.unique' => 'That subdomain is already taken.',
            'owner_email.unique' => 'That email is already in use on another account.',
        ]);

        // Long, random, never shown again after this request — the same
        // shape as the password `tenant:user` generates on the CLI.
        $password = Str::password(20);

        $tenant = DB::transaction(function () use ($validated, $tenants, $password): Tenant {
            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'subdomain' => $validated['subdomain'],
                'status' => Tenant::STATUS_ACTIVE,
                'currency' => $validated['currency'],
                'customer_reference_prefix' => strtoupper($validated['customer_reference_prefix']),
                'package_reference_prefix' => 'PKG',
                'trial_ends_at' => ! empty($validated['trial_days'])
                    ? now()->addDays((int) $validated['trial_days'])
                    : null,
            ]);

            $previous = $tenants->current();
            $tenants->set($tenant);

            try {
                // email_verified_at isn't in the model's mass-assignable
                // list (by design — a public signup must go through email
                // verification), so it needs forceFill here. A tenant Shane
                // creates by hand should be usable immediately: the owner
                // can't click a verification link for an email that hasn't
                // received one yet, and mail deliverability isn't proven in
                // this environment (see docs/launch-readiness.md, P0 #4).
                (new User)->forceFill([
                    'name' => $validated['owner_name'],
                    'email' => $validated['owner_email'],
                    'password' => $password,
                    'role' => User::ROLE_OWNER,
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                ])->save();
            } finally {
                $previous ? $tenants->set($previous) : $tenants->forget();
            }

            return $tenant->fresh();
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => sprintf(
                '%s is live at %s. Owner login — email: %s · temporary password: %s. Copy this now, it will not be shown again.',
                $tenant->name,
                $tenant->url('/login'),
                $validated['owner_email'],
                $password,
            ),
        ]);

        return back();
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['sometimes', Rule::in(['suspend', 'activate'])],
            'trial_days' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:365'],
        ]);

        if (! array_key_exists('action', $validated) && ! array_key_exists('trial_days', $validated)) {
            abort(422, 'Nothing to update.');
        }

        $messages = [];

        if (array_key_exists('action', $validated)) {
            $tenant->forceFill([
                'status' => $validated['action'] === 'suspend'
                    ? Tenant::STATUS_SUSPENDED
                    : Tenant::STATUS_ACTIVE,
            ])->save();

            $messages[] = $validated['action'] === 'suspend'
                ? "{$tenant->name} has been suspended."
                : "{$tenant->name} has been reactivated.";
        }

        if (array_key_exists('trial_days', $validated)) {
            $days = $validated['trial_days'];

            $tenant->forceFill([
                'trial_ends_at' => $days ? now()->addDays((int) $days) : null,
            ])->save();

            $messages[] = $days
                ? "{$tenant->name}'s trial now ends {$tenant->trial_ends_at->toFormattedDateString()}."
                : "{$tenant->name}'s trial date was cleared.";
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => implode(' ', $messages)]);

        return back();
    }
}
