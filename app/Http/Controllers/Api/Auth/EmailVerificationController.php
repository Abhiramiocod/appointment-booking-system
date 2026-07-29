<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified from signed URL.
     */
    public function verify(Request $request, string $id, string $hash): RedirectResponse
    {
        $frontendUrl = config('app.frontend_url');

        try {
            $user = User::findOrFail($id);

            if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
                $errorUrl = rtrim($frontendUrl, '/').'/verify-email?error='.urlencode('Invalid verification link.');

                return redirect()->away($errorUrl);
            }

            if ($user->hasVerifiedEmail()) {
                $successUrl = rtrim($frontendUrl, '/').'/verify-success?status='.urlencode('Email already verified.');

                return redirect()->away($successUrl);
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            $successUrl = rtrim($frontendUrl, '/').'/verify-success?status='.urlencode('Email verified successfully.');

            return redirect()->away($successUrl);
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'error' => $e->getMessage(),
            ]);

            $errorUrl = rtrim($frontendUrl, '/').'/verify-email?error='.urlencode('Verification failed or expired.');

            return redirect()->away($errorUrl);
        }
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (! $user) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email is already verified.',
                ], 400);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'message' => 'Verification link sent to your email.',
            ]);
        } catch (\Exception $e) {
            Log::error('Resend email verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to send verification email. Please try again.',
            ], 500);
        }
    }
}
