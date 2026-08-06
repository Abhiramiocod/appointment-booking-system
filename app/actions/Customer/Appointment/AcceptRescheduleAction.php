<?php

namespace App\actions\Customer\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;

class AcceptRescheduleAction
{
    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update([
            'appointment_date' => $appointment->proposed_date,
            'start_time' => $appointment->proposed_time,
            'status' => AppointmentStatus::CONFIRMED,
            'proposed_date' => null,
            'proposed_time' => null,
            'proposed_note' => null,
        ]);

        NotificationService::notify(
            user: $appointment->staff,
            title: 'Reschedule Accepted',
            message: "{$appointment->customer->name} has accepted the reschedule proposal for {$appointment->service->name} on ".$appointment->appointment_date->toDateString()." at {$appointment->start_time}.",
            type: 'appointment',
            actionUrl: '/staff/appointments'
        );

        return $appointment;
    }
}
