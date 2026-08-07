<?php

declare(strict_types=1);

namespace App\Actions\GoogleCalendar;

use App\Models\GoogleCalendarAccount;
use App\Services\GoogleCalendarService;
use Exception;
use Illuminate\Support\Facades\Log;

class RefreshGoogleAccessTokenAction
{
    public function __construct(
        private readonly GoogleCalendarService $calendarService
    ) {}

    public function execute(GoogleCalendarAccount $account): bool
    {
        try {
            $this->calendarService->getAuthorizedClient($account);

            return true;
        } catch (Exception $e) {
            Log::error("Failed to refresh Google access token for user ID {$account->user_id}: {$e->getMessage()}", [
                'account_id' => $account->id,
                'exception' => $e,
            ]);

            return false;
        }
    }
}
