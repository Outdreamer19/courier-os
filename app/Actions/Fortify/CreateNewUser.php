<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\CustomerReferenceGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private readonly CustomerReferenceGenerator $references) {}

    /**
     * Validate and create a newly registered user.
     *
     * Every public registration is a customer, gets a customer profile, and
     * receives a freshly generated DJM-XXXXXX reference inside one transaction.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
            ]);

            CustomerProfile::create([
                'user_id' => $user->id,
                'customer_reference' => $this->references->next(),
            ]);

            return $user->fresh('customerProfile');
        });
    }
}
