<?php

declare(strict_types=1);

namespace App\Actions\GoogleCalendar;

use App\Models\User;

class DisconnectGoogleCalendarAction
{
    public function execute(User $user): bool
    {
        $account = $user->googleCalendarAccount;
        if ($account) {
            return (bool) $account->delete();
        }

        return false;
    }
}
