<?php

namespace App\actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;

class CancelAppointmentAction
{
    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
        ]);

        NotificationService::notify(
            user: $appointment->customer,
            title: 'Appointment Cancelled',
            message: "Your appointment for {$appointment->service->name} on ".$appointment->appointment_date->toDateString().' has been cancelled.',
            type: 'appointment',
            actionUrl: '/customer/schedule'
        );

        return $appointment;
    }
}
