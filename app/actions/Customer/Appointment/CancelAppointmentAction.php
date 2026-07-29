<?php

namespace App\actions\Customer\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;
use Exception;

class CancelAppointmentAction
{
    public function execute(Appointment $appointment): Appointment
    {
        if ($appointment->status === AppointmentStatus::COMPLETED) {
            throw new Exception('Cannot cancel a completed appointment.');
        }

        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
        ]);

        NotificationService::notify(
            user: $appointment->staff,
            title: 'Appointment Cancelled',
            message: "{$appointment->customer->name} has cancelled their appointment for {$appointment->service->name} on ".$appointment->appointment_date->toDateString().'.',
            type: 'appointment',
            actionUrl: '/staff/appointments'
        );

        return $appointment;
    }
}
