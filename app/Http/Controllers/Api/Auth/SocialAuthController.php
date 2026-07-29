<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
    public function googleCallback(): RedirectResponse|JsonResponse
    {
        try {
            /** @var AbstractProvider $driver */
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
                        'username' => $this->generateUniqueUsername($googleUser->getName() ?? $googleUser->getEmail()),
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
    public function microsoftCallback(): RedirectResponse|JsonResponse
    {
        $frontendUrl = config('app.frontend_url');

        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('microsoft');
            $microsoftUser = $driver->stateless()->user();

            $microsoftRawUser = $microsoftUser->user ?? [];
            $rawMail = $microsoftRawUser['mail'] ?? null;
            $userPrincipalName = $microsoftRawUser['userPrincipalName'] ?? ($microsoftUser->getEmail() ?? '');

            $email = null;

            // 1. Prefer official 'mail' attribute if valid email
            if (! empty($rawMail) && filter_var($rawMail, FILTER_VALIDATE_EMAIL) && ! str_contains($rawMail, '#EXT#')) {
                $email = $rawMail;
            }

            // 2. Parse guest/external #EXT# format from userPrincipalName or email
            if (! $email) {
                $target = ! empty($userPrincipalName) ? $userPrincipalName : $microsoftUser->getEmail();
                if (! empty($target) && str_contains($target, '#EXT#')) {
                    $prefix = explode('#EXT#', $target)[0];
                    $lastUnderscorePos = strrpos($prefix, '_');
                    if ($lastUnderscorePos !== false) {
                        $email = substr_replace($prefix, '@', $lastUnderscorePos, 1);
                    }
                }
            }

            // 3. Fallback to Socialite email
            if (! $email) {
                $email = $microsoftUser->getEmail();
            }

            if (empty($email)) {
                Log::warning('Microsoft OAuth callback missing email', [
                    'microsoft_id' => $microsoftUser->getId(),
                ]);

                $errorRedirectUrl = rtrim($frontendUrl, '/').'/login?error='.urlencode('Email address not provided by Microsoft.');

                return redirect()->away($errorRedirectUrl);
            }

            $token = DB::transaction(function () use ($microsoftUser, $email) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    $user->update([
                        'provider' => AuthProvider::MICROSOFT,
                        'provider_id' => $microsoftUser->getId(),
                        'image' => $microsoftUser->getAvatar() ?? $user->image,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $microsoftUser->getName() ?? $microsoftUser->getNickname() ?? 'Microsoft User',
                        'username' => $this->generateUniqueUsername($microsoftUser->getName() ?? $email),
                        'email' => $email,
                        'provider' => AuthProvider::MICROSOFT,
                        'provider_id' => $microsoftUser->getId(),
                        'image' => $microsoftUser->getAvatar(),
                        'role' => UserRole::CUSTOMER,
                        'password' => null,
                        'email_verified_at' => now(),
                    ]);
                }

                return $user->createToken('api_token')->plainTextToken;
            });

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

    /**
     * Generate a clean, unique username from user name or email.
     */
    private function generateUniqueUsername(string $nameOrEmail): string
    {
        $base = Str::slug(explode('@', $nameOrEmail)[0], '');
        if (empty($base)) {
            $base = 'user';
        }

        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }
}
