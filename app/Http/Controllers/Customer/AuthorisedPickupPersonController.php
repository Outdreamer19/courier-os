<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAuthorisedPickupPersonRequest;
use App\Models\ActivityLog;
use App\Models\AuthorisedPickupPerson;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthorisedPickupPersonController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function store(StoreAuthorisedPickupPersonRequest $request): RedirectResponse
    {
        $profile = $request->user()->customerProfile;

        abort_unless($profile, 404);

        $pickupPerson = $profile->authorisedPickupPeople()->create(
            $request->validated(),
        );

        $this->activityLogger->log(
            ActivityLog::ACTION_CUSTOMER_PROFILE_UPDATED,
            "{$request->user()->name} added authorised pickup person {$pickupPerson->full_name}.",
            $request->user(),
            $pickupPerson,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Authorised pickup person added.']);

        return back();
    }

    public function destroy(
        Request $request,
        AuthorisedPickupPerson $authorisedPickupPerson,
    ): RedirectResponse {
        $profile = $request->user()->customerProfile;

        abort_unless($profile, 404);
        abort_unless(
            $authorisedPickupPerson->customer_profile_id === $profile->id,
            404,
        );

        $name = $authorisedPickupPerson->full_name;
        $authorisedPickupPerson->delete();

        $this->activityLogger->log(
            ActivityLog::ACTION_CUSTOMER_PROFILE_UPDATED,
            "{$request->user()->name} removed authorised pickup person {$name}.",
            $request->user(),
            $profile,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Authorised pickup person removed.']);

        return back();
    }
}
