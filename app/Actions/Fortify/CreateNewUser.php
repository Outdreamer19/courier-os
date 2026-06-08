<?php

namespace App\Actions\Fortify;

use App\Concerns\CustomerProfileValidationRules;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\ActivityLog;
use App\Models\AuthorisedPickupPerson;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\CustomerReferenceGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use CustomerProfileValidationRules, PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private readonly CustomerReferenceGenerator $references,
        private readonly ActivityLogger $activityLogger,
    ) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $validator = Validator::make($input, [
            ...$this->profileRules(),
            ...$this->customerProfileRules(required: true),
            'password' => $this->passwordRules(),
        ]);

        $validator->after(function ($validator) use ($input): void {
            if (! empty($input['date_of_birth'])) {
                $dob = \Carbon\Carbon::parse($input['date_of_birth']);

                if ($dob->greaterThan(now()->subYears(18))) {
                    $validator->errors()->add(
                        'date_of_birth',
                        'You must be at least 18 years old to register.',
                    );
                }
            }
        });

        $validator->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
            ]);

            $profile = CustomerProfile::create([
                'user_id' => $user->id,
                'customer_reference' => $this->references->next(),
                'trn' => $input['trn'],
                'phone' => $input['phone'],
                'jamaica_address' => $input['jamaica_address'],
                'date_of_birth' => $input['date_of_birth'],
            ]);

            $this->activityLogger->log(
                ActivityLog::ACTION_CUSTOMER_REGISTERED,
                "{$user->name} registered with reference {$profile->customer_reference}.",
                $user,
                $profile,
                ['email' => $user->email],
            );

            return $user->fresh('customerProfile');
        });
    }
}
