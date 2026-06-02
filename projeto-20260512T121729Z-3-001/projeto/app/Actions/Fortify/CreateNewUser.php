<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Models\Customer; // Importar o Model correto
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Importar a Facade DB
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'gender' => ['nullable', 'in:M,F'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'user_type' => 'C',
                'gender' => $input['gender'] ?? 'M',
            ]);

            Customer::create([
                'id' => $user->id,
            ]);

            return $user;
        });
    }
}
