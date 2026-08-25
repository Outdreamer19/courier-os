<?php

namespace App\Http\Controllers\Central;

use App\Actions\Tenancy\CreateTenant;
use App\Concerns\PasswordValidationRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantSignupController extends Controller
{
    use PasswordValidationRules;

    public function show(): Response
    {
        return Inertia::render('central/Signup', [
            'currencies' => ['USD', 'JMD'],
            'pricing' => [
                'monthly' => 79,
                'setup' => 349,
                'currency' => 'USD',
            ],
            'centralDomain' => config('courieros.central_domain'),
        ]);
    }

    public function store(Request $request, CreateTenant $createTenant): RedirectResponse
    {
        $reserved = (array) config('courieros.reserved_subdomains', []);

        // Normalise the subdomain to a slug before validation so uniqueness
        // and reserved checks run against the stored value.
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
            'owner_email' => ['required', 'string', 'email', 'max:255'],
            'password' => $this->passwordRules(),
        ], [
            'subdomain.not_in' => 'That subdomain is reserved. Please choose another.',
            'subdomain.unique' => 'That subdomain is already taken.',
        ]);

        $tenant = $createTenant->handle($validated);

        return redirect()->route('central.billing.checkout', ['tenant' => $tenant->id]);
    }
}
