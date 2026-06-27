<?php

namespace App\actions\Customer\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
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

        return $appointment;
    }
}
