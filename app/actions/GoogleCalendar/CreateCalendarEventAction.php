<?php

declare(strict_types=1);

namespace App\Actions\GoogleCalendar;

use App\Models\Appointment;
use App\Models\GoogleCalendarAccount;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Exception;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

class CreateCalendarEventAction
{
    public function __construct(
        private readonly GoogleCalendarService $calendarService
    ) {}

    public function execute(GoogleCalendarAccount $account, Appointment $appointment): ?string
    {
        try {
            $service = $this->calendarService->getCalendarService($account);

            $appointment->loadMissing(['service', 'customer', 'staff']);

            $serviceName = $appointment->service->name ?? 'Service';
            $customerName = $appointment->customer->name ?? 'Customer';
            $staffName = $appointment->staff->name ?? 'Staff';
            $notes = $appointment->notes ?? 'None';

            $title = "Appointment - {$serviceName}";
            $description = "Customer:\n{$customerName}\n\nStaff:\n{$staffName}\n\nNotes:\n{$notes}";
            $location = 'Business Name';
            $timezone = 'Asia/Kolkata';

            $dateStr = $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d') : now()->format('Y-m-d');
            $startDateTimeStr = "{$dateStr} {$appointment->start_time}";
            $endDateTimeStr = "{$dateStr} {$appointment->end_time}";

            $startIso = Carbon::parse($startDateTimeStr, $timezone)->toIso8601String();
            $endIso = Carbon::parse($endDateTimeStr, $timezone)->toIso8601String();

            $event = new Event([
                'summary' => $title,
                'description' => $description,
                'location' => $location,
                'start' => new EventDateTime([
                    'dateTime' => $startIso,
                    'timeZone' => $timezone,
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $endIso,
                    'timeZone' => $timezone,
                ]),
            ]);

            $createdEvent = $service->events->insert('primary', $event);

            if ($createdEvent && $createdEvent->getId()) {
                $eventId = $createdEvent->getId();
                $appointment->updateQuietly(['google_calendar_event_id' => $eventId]);

                return $eventId;
            }
        } catch (Exception $e) {
            Log::error("Failed to create Google Calendar event for appointment ID {$appointment->id}: {$e->getMessage()}", [
                'appointment_id' => $appointment->id,
                'exception' => $e,
            ]);
        }

        return null;
    }
}
