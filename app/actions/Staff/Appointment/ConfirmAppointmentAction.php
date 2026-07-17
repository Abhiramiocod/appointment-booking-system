<?php

namespace App\actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;
use Exception;

class ConfirmAppointmentAction
{
    public function execute(Appointment $appointment): Appointment
    {
        if ($appointment->status === AppointmentStatus::CANCELLED) {
            throw new Exception('Cannot confirm a cancelled appointment.');
        }

        if ($appointment->status === AppointmentStatus::COMPLETED) {
            throw new Exception('Cannot confirm a completed appointment.');
        }

        $appointment->update([
            'status' => AppointmentStatus::CONFIRMED,
        ]);

        NotificationService::notify(
            user: $appointment->customer,
            title: 'Appointment Confirmed',
            message: "Your appointment for {$appointment->service->name} on ".$appointment->appointment_date->toDateString()." at {$appointment->start_time} has been confirmed.",
            type: 'appointment',
            actionUrl: '/customer/schedule'
        );

        return $appointment;
    }
}
