<?php

namespace App\Actions\Auth;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class LoginUserAction
{
    public function execute(array $credentials): array
    {
        if (! Auth::attempt($credentials)) {
            return [
                'success' => false,
                'status' => 401,
                'message' => 'Invalid credentials',
            ];
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            $token = $user->createToken('unverified_api_token')->plainTextToken;

            $user->sendEmailVerificationNotification();

            return [
                'success' => false,
                'status' => 403,
                'message' => 'Please verify your email address. A verification link has been sent to your email.',
                'token' => $token,
                'user' => new UserResource($user),
            ];
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'success' => true,
            'status' => 200,
            'token' => $token,
            'user' => new UserResource($user),
        ];
    }
}
