<?php

declare(strict_types=1);

namespace App\Actions\GoogleCalendar;

use App\Services\GoogleCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

class ConnectGoogleCalendarAction
{

    public function execute(Request $request): RedirectResponse
    {
        if (! auth()->check() && $request->has('bearer_token')) {
            $token = $request->input('bearer_token');
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                auth()->setUser($accessToken->tokenable);
            }
        }

        $redirectUrl = config('services.google.calendar_redirect_uri', url('/calendar/google/callback'));

        /** @var GoogleProvider $driver */
        $driver = Socialite::driver('google');

        $state = encrypt(auth()->id());

        $scopes = array_filter(explode(',', (string) config('services.google.calendar_scopes')));

        return $driver
            ->stateless()
            ->with(['state' => $state, 'access_type' => 'offline', 'prompt' => 'consent'])
            ->redirectUrl($redirectUrl)
            ->scopes($scopes)
            ->redirect();
    }
}
