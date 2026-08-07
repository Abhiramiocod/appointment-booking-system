<?php

declare(strict_types=1);

namespace App\Actions\GoogleCalendar;

use App\Models\Appointment;
use App\Models\GoogleCalendarAccount;
use App\Services\GoogleCalendarService;
use Exception;
use Illuminate\Support\Facades\Log;

class DeleteCalendarEventAction
{
    public function __construct(
        private readonly GoogleCalendarService $calendarService
    ) {}

    public function execute(GoogleCalendarAccount $account, Appointment $appointment): bool
    {
        if (! $appointment->google_calendar_event_id) {
            return false;
        }

        try {
            $service = $this->calendarService->getCalendarService($account);
            $service->events->delete('primary', $appointment->google_calendar_event_id);

            $appointment->updateQuietly(['google_calendar_event_id' => null]);

            return true;
        } catch (Exception $e) {
            Log::error("Failed to delete Google Calendar event ID {$appointment->google_calendar_event_id} for appointment {$appointment->id}: {$e->getMessage()}", [
                'appointment_id' => $appointment->id,
                'event_id' => $appointment->google_calendar_event_id,
                'exception' => $e,
            ]);

            return false;
        }
    }
}
