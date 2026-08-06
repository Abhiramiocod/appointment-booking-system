<?php

namespace App\actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;
use Carbon\Carbon;

class ProposeAppointmentTimeAction
{
    public function execute(Appointment $appointment, string $proposedDate, string $proposedTime, ?string $proposedNote = null): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::RESCHEDULE_REQUESTED,
            'proposed_date' => $proposedDate,
            'proposed_time' => $proposedTime,
            'proposed_note' => $proposedNote,
        ]);

        NotificationService::notify(
            user: $appointment->customer,
            title: 'Reschedule Proposed',
            message: "A reschedule has been proposed for your {$appointment->service->name} appointment to ".Carbon::parse($proposedDate)->toDateString()." at {$proposedTime}.",
            type: 'appointment',
            actionUrl: '/customer/schedule'
        );

        return $appointment;
    }
}
