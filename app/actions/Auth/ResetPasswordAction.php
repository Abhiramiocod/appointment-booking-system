<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResetPasswordAction
{
    public function execute(array $data): array
    {
        try {
            $status = Password::reset(
                [
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'password_confirmation' => $data['password_confirmation'],
                    'token' => $data['token'],
                ],
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            return [
                'message' => __($status),
                'status' => $status === Password::PASSWORD_RESET ? 200 : 400,
            ];
        } catch (\Throwable $e) {
            Log::error('Password reset failed', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);

            throw new HttpException(
                500,
                'Failed to reset password. Please try again.'
            );
        }
    }
}
