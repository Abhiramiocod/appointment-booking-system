<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SendPasswordResetLinkAction
{
    public function execute(array $data): array
    {
        try {
            Password::sendResetLink([
                'email' => $data['email'],
            ]);

            // Always return the same response to prevent email enumeration
            return [
                'message' => 'Password reset link has been sent if the account exists.',
            ];
        } catch (\Throwable $e) {
            Log::error('Send reset link email failed', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);

            throw new HttpException(
                500,
                'Failed to send password reset link. Please try again.'
            );
        }
    }
}
