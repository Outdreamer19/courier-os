<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\UpdateCustomerProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = $user?->customerProfile;

        return Inertia::render('customer/Profile', [
            'profile' => [
                'phone' => $profile?->phone,
                'whatsapp_number' => $profile?->whatsapp_number,
                'jamaica_address' => $profile?->jamaica_address,
                'parish' => $profile?->parish,
                'customer_reference' => $profile?->customer_reference,
            ],
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
            $request->only(['phone', 'whatsapp_number', 'jamaica_address', 'parish']),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('portal.profile.edit');
    }
}
