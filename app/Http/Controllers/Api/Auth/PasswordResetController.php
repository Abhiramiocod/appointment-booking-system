<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            // Use Laravel's Password Broker - sends email if email exists
            Password::sendResetLink($request->only('email'));

            // Always return the exact same success response regardless of email existence
            return response()->json([
                'message' => 'Password reset link has been sent if the account exists.',
            ]);
        } catch (\Exception $e) {
            Log::error('Send reset link email failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send password reset link. Please try again.',
            ], 500);
        }
    }

    /**
     * Reset the given user's password.
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => __($status),
                ]);
            }

            return response()->json([
                'message' => __($status),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to reset password. Please try again.',
            ], 500);
        }
    }
}
