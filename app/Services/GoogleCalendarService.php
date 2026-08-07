<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GoogleCalendarAccount;
use Google\Client;
use Google\Service\Calendar;

class GoogleCalendarService
{
    /**
     * Create a Google Client instance initialized for OAuth / Calendar API.
     */
    public function createClient(): Client
    {
        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.calendar_redirect_uri', url('/calendar/google/callback')));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope('https://www.googleapis.com/auth/calendar.events');

        return $client;
    }

    /**
     * Get authorized client for a specific account, automatically refreshing token if expired.
     */
    public function getAuthorizedClient(GoogleCalendarAccount $account): Client
    {
        $client = $this->createClient();

        $tokenArray = [
            'access_token' => $account->access_token,
            'expires_in' => $account->expires_at ? max(0, $account->expires_at->timestamp - time()) : 0,
            'created' => time(),
        ];

        if ($account->refresh_token) {
            $tokenArray['refresh_token'] = $account->refresh_token;
        }

        $client->setAccessToken($tokenArray);

        if ($client->isAccessTokenExpired()) {
            if ($account->refresh_token) {
                $newToken = $client->fetchAccessTokenWithRefreshToken($account->refresh_token);
                if (isset($newToken['access_token'])) {
                    $expiresAt = isset($newToken['expires_in'])
                        ? now()->addSeconds((int) $newToken['expires_in'])
                        : now()->addHour();

                    $account->update([
                        'access_token' => $newToken['access_token'],
                        'expires_at' => $expiresAt,
                    ]);

                    $client->setAccessToken($newToken);
                }
            }
        }

        return $client;
    }

    /**
     * Get Google Service Calendar instance.
     */
    public function getCalendarService(GoogleCalendarAccount $account): Calendar
    {
        $client = $this->getAuthorizedClient($account);

        return new Calendar($client);
    }
}
