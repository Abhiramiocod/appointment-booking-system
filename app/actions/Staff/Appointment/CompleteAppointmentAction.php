<?php

namespace App\Actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
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

        return $appointment;
    }
}
