<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth consent screen.
     */
    public function googleRedirect(): JsonResponse|RedirectResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
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
    public function googleCallback(): RedirectResponse|JsonResponse
    {
        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->user();

            $token = DB::transaction(function () use ($googleUser) {
                $user = User::where('email', $googleUser->getEmail())->first();

                if ($user) {
                    $user->update([
                        'provider' => AuthProvider::GOOGLE,
                        'provider_id' => $googleUser->getId(),
                        'image' => $googleUser->getAvatar() ?? $user->image,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                        'email' => $googleUser->getEmail(),
                        'provider' => AuthProvider::GOOGLE,
                        'provider_id' => $googleUser->getId(),
                        'image' => $googleUser->getAvatar(),
                        'role' => UserRole::CUSTOMER,
                        'password' => null,
                        'email_verified_at' => now(),
                    ]);
                }

                return $user->createToken('api_token')->plainTextToken;
            });

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $redirectUrl = rtrim($frontendUrl, '/') . '/login/callback?token=' . urlencode($token);

            return redirect()->away($redirectUrl);
        } catch (Exception $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $errorRedirectUrl = rtrim($frontendUrl, '/') . '/login?error=' . urlencode('Google authentication failed. Please try again.');

            return redirect()->away($errorRedirectUrl);
        }
    }
}
