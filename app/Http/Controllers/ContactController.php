<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Central\ContactController as CentralContactController;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Support\Tenancy\TenantManager;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(TenantManager $tenants): Response
    {
        // On courieros.co itself this is the platform's own contact page,
        // not a tenant's.
        if (! $tenants->hasTenant()) {
            return app(CentralContactController::class)->show();
        }

        return Inertia::render('public/Contact');
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create([
            ...$request->validated(),
            'status' => ContactMessage::STATUS_NEW,
        ]);

        return redirect()
            ->route('contact')
            ->with('success', 'Thanks — your message has been received. We will be in touch shortly.');
    }
}
