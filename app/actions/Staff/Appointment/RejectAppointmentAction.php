<?php

namespace App\actions\Staff\Appointment;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\NotificationService;

class RejectAppointmentAction
{
    public function execute(Appointment $appointment, ?string $rejectionReason = null): Appointment
    {
        $appointment->update([
            'status' => AppointmentStatus::REJECTED,
            'rejection_reason' => $rejectionReason,
        ]);

        NotificationService::notify(
            user: $appointment->customer,
            title: 'Appointment Declined',
            message: "Your appointment request for {$appointment->service->name} has been declined. Reason: ".($rejectionReason ?? 'None provided.'),
            type: 'appointment',
            actionUrl: '/customer/schedule'
        );

        return $appointment;
    }
}
