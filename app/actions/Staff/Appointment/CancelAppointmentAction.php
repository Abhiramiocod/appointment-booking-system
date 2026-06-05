<?php

namespace App\Actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;

class CancelAppointmentAction
{
    public function execute(Appointment $appointment): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::CANCELLED,
        ]);

        return $appointment;
    }
}
