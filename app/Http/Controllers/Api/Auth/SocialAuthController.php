<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\HandleSocialAuthCallbackAction;
use App\Actions\Auth\ResolveMicrosoftEmailAction;
use App\Enums\AuthProvider;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function googleRedirect(): JsonResponse|RedirectResponse
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');

            return $driver->stateless()->redirect();
        } catch (Exception $e) {
            Log::error('Google OAuth redirect failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to initiate Google authentication.',
            ], 500);
        }
    }

    /**
     * Handle the callback from Google OAuth.
     */
    public function googleCallback(HandleSocialAuthCallbackAction $action): RedirectResponse|JsonResponse
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();

            $token = $action->execute($googleUser, AuthProvider::GOOGLE);

            $frontendUrl = config('app.frontend_url');
            $redirectUrl = rtrim($frontendUrl, '/').'/login/callback?token='.urlencode($token);

            return redirect()->away($redirectUrl);
        } catch (Exception $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $frontendUrl = config('app.frontend_url');
            $errorRedirectUrl = rtrim($frontendUrl, '/').'/login?error='.urlencode('Google authentication failed. Please try again.');

            return redirect()->away($errorRedirectUrl);
        }
    }

    /**
     * Redirect the user to Microsoft's OAuth consent screen.
     */
    public function microsoftRedirect(): JsonResponse|RedirectResponse
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('microsoft');

            return $driver->stateless()->redirect();
        } catch (Exception $e) {
            Log::error('Microsoft OAuth redirect failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to initiate Microsoft authentication.',
            ], 500);
        }
    }

    /**
     * Handle the callback from Microsoft OAuth.
     */
    public function microsoftCallback(HandleSocialAuthCallbackAction $handleAuthAction, ResolveMicrosoftEmailAction $resolveEmailAction): RedirectResponse|JsonResponse
    {
        $frontendUrl = config('app.frontend_url');

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('microsoft');
            $microsoftUser = $driver->stateless()->user();

            $email = $resolveEmailAction->execute($microsoftUser);

            if (empty($email)) {
                Log::warning('Microsoft OAuth callback missing email', [
                    'microsoft_id' => $microsoftUser->getId(),
                ]);

                $errorRedirectUrl = rtrim($frontendUrl, '/').'/login?error='.urlencode('Email address not provided by Microsoft.');

                return redirect()->away($errorRedirectUrl);
            }

            $token = $handleAuthAction->execute($microsoftUser, AuthProvider::MICROSOFT, $email);

            $redirectUrl = rtrim($frontendUrl, '/').'/login/callback?token='.urlencode($token);

            return redirect()->away($redirectUrl);
        } catch (Exception $e) {
            Log::error('Microsoft OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorRedirectUrl = rtrim($frontendUrl, '/').'/login?error='.urlencode('Microsoft authentication failed. Please try again.');

            return redirect()->away($errorRedirectUrl);
        }
    }
}
