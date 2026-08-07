<?php

declare(strict_types=1);

namespace App\Actions\GoogleCalendar;

use App\Models\Appointment;
use App\Models\GoogleCalendarAccount;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Exception;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

class UpdateCalendarEventAction
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

            $event = $service->events->get('primary', $appointment->google_calendar_event_id);
            $event->setSummary($title);
            $event->setDescription($description);
            $event->setLocation($location);

            $start = new EventDateTime;
            $start->setDateTime($startIso);
            $start->setTimeZone($timezone);
            $event->setStart($start);

            $end = new EventDateTime;
            $end->setDateTime($endIso);
            $end->setTimeZone($timezone);
            $event->setEnd($end);

            $service->events->update('primary', $appointment->google_calendar_event_id, $event);

            return true;
        } catch (Exception $e) {
            Log::error("Failed to update Google Calendar event ID {$appointment->google_calendar_event_id} for appointment {$appointment->id}: {$e->getMessage()}", [
                'appointment_id' => $appointment->id,
                'event_id' => $appointment->google_calendar_event_id,
                'exception' => $e,
            ]);

            return false;
        }
    }
}
