<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateCustomerProfileRequest;
use App\Models\ActivityLog;
use App\Models\AuthorisedPickupPerson;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerProfileController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = $user?->customerProfile;
        $profile?->load('authorisedPickupPeople');

        return Inertia::render('customer/Profile', [
            'profile' => [
                'phone' => $profile?->phone,
                'whatsapp_number' => $profile?->whatsapp_number,
                'jamaica_address' => $profile?->jamaica_address,
                'parish' => $profile?->parish,
                'trn' => $profile?->trn,
                'date_of_birth' => $profile?->date_of_birth?->toDateString(),
                'customer_reference' => $profile?->customer_reference,
            ],
            'authorisedPickupPeople' => $profile?->authorisedPickupPeople
                ->map(fn (AuthorisedPickupPerson $person) => $person->toSummaryArray())
                ->values()
                ->all(),
            'maxAuthorisedPickupPeople' => AuthorisedPickupPerson::MAX_PER_CUSTOMER,
        ]);
    }

    public function update(UpdateCustomerProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $user->customerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'phone',
                'whatsapp_number',
                'jamaica_address',
                'parish',
                'trn',
                'date_of_birth',
            ]),
        );

        $this->activityLogger->log(
            ActivityLog::ACTION_CUSTOMER_PROFILE_UPDATED,
            "{$user->name} updated their profile.",
            $user,
            $user->customerProfile,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('portal.profile.edit');
    }
}
