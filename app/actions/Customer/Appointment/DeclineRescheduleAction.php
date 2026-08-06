<?php

namespace App\actions\Customer\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;

class DeclineRescheduleAction
{
    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::REJECTED,
            'proposed_date' => null,
            'proposed_time' => null,
            'proposed_note' => null,
        ]);

        NotificationService::notify(
            user: $appointment->staff,
            title: 'Reschedule Declined',
            message: "{$appointment->customer->name} has declined the reschedule proposal for {$appointment->service->name}.",
            type: 'appointment',
            actionUrl: '/staff/appointments'
        );

        return $appointment;
    }
}
