<?php

namespace App\actions\Auth;

use App\Models\User;

class ResendEmailVerificationAction
{
    public function execute(User $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return [
                'success' => false,
                'status' => 400,
                'message' => 'Email is already verified.',
            ];
        }

        $user->sendEmailVerificationNotification();

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Verification link sent to your email.',
        ];
    }
}
