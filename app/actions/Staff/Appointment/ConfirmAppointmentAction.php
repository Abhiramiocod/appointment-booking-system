<?php

namespace App\actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
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

        return $appointment;
    }
}
