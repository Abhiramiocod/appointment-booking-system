<?php

declare(strict_types=1);

namespace App\Actions\GoogleCalendar;

use App\Models\GoogleCalendarAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class HandleGoogleCalendarCallbackAction
{
    public function execute(User $user): GoogleCalendarAccount
    {
        $redirectUrl = config('services.google.calendar_redirect_uri', url('/calendar/google/callback'));

        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google');

        $googleUser = $driver
            ->redirectUrl($redirectUrl)
            ->stateless()
            ->user();

        $expiresAt = isset($googleUser->expiresIn) && $googleUser->expiresIn
            ? now()->addSeconds((int) $googleUser->expiresIn)
            : now()->addHour();

        $refreshToken = $googleUser->refreshToken;

        if (! $refreshToken && isset($googleUser->user['refresh_token'])) {
            $refreshToken = $googleUser->user['refresh_token'];
        }

        $existingAccount = $user->googleCalendarAccount;

        if (! $refreshToken && $existingAccount) {
            $refreshToken = $existingAccount->refresh_token;
        }

        return GoogleCalendarAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'google_email' => $googleUser->getEmail(),
                'access_token' => $googleUser->token,
                'refresh_token' => $refreshToken,
                'expires_at' => $expiresAt,
            ]
        );
    }
}
