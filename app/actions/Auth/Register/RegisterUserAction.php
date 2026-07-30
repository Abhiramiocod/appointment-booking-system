<?php

namespace App\actions\Auth\Register;

use App\Enums\UserRole;
use App\Models\User;

class RegisterUserAction
{
    public function execute(array $data): array
    {
        $data['role'] = UserRole::CUSTOMER;

        $user = User::create($data);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful. Please check your email to verify your account.',
        ];
    }
}
