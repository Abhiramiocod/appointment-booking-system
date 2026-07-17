<?php

namespace App\actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;
use Exception;

class CompleteAppointmentAction
{
    public function execute(Appointment $appointment): Appointment
    {
        if ($appointment->status === AppointmentStatus::CANCELLED) {
            throw new Exception('Cannot complete a cancelled appointment.');
        }

        if ($appointment->status !== AppointmentStatus::CONFIRMED) {
            throw new Exception('Only confirmed appointments can be completed.');
        }

        $appointment->update([
            'status' => AppointmentStatus::COMPLETED,
        ]);

        NotificationService::notify(
            user: $appointment->customer,
            title: 'Appointment Completed',
            message: "Your appointment for {$appointment->service->name} has been completed. Thank you!",
            type: 'appointment',
            actionUrl: '/customer/schedule'
        );

        return $appointment;
    }
}
