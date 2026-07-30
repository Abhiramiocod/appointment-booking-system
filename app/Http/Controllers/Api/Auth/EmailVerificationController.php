<?php

namespace App\Http\Controllers\Api\Auth;

use App\actions\Auth\ResendEmailVerificationAction;
use App\actions\Auth\VerifyEmailAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified from signed URL.
     */
    public function verify(string $id, string $hash, VerifyEmailAction $action): RedirectResponse
    {
        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        try {
            $result = $action->execute($id, $hash);

            $path = $result['success'] ? 'verify-success' : 'verify-email';
            $key = $result['success'] ? 'status' : 'error';

            return redirect()->away(
                "{$frontendUrl}/{$path}?{$key}=".urlencode($result['message'])
            );
        } catch (Throwable $e) {
            Log::error('Email verification failed', [
                'user_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->away(
                "{$frontendUrl}/verify-email?error=".
                    urlencode('Verification failed or expired.')
            );
        }
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request, ResendEmailVerificationAction $action): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $result = $action->execute($user);

            return response()->json([
                'message' => $result['message'],
            ], $result['status']);
        } catch (Throwable $e) {
            Log::error('Resend email verification failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send verification email. Please try again.',
            ], 500);
        }
    }
}
