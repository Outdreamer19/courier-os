<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateAuthorisedPickupPersonRequest;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AuthorisedPickupPersonController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function update(UpdateAuthorisedPickupPersonRequest $request): RedirectResponse
    {
        $profile = $request->user()->customerProfile;

        abort_unless($profile, 404);

        if ($request->boolean('remove')) {
            $profile->authorisedPickupPerson?->delete();

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Authorised pickup person removed.']);

            return back();
        }

        $data = $request->validated();

        $pickupPerson = $profile->authorisedPickupPerson()->updateOrCreate(
            ['customer_profile_id' => $profile->id],
            [
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'relationship_note' => $data['relationship_note'] ?? null,
                'id_number' => $data['id_number'] ?? null,
            ],
        );

        $this->activityLogger->log(
            ActivityLog::ACTION_CUSTOMER_PROFILE_UPDATED,
            "{$request->user()->name} updated their authorised pickup person.",
            $request->user(),
            $pickupPerson,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Authorised pickup person saved.']);

        return back();
    }
}
