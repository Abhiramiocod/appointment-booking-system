<?php

namespace App\Actions\Customer\Dashboard;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;

class GetDashboardStatsAction
{
    public function execute(User $customer): array
    {
        return [
            'upcoming_appointments' => Appointment::query()
                ->where('customer_id', $customer->id)
                ->where('appointment_date', '>=', today())
                ->whereNotIn('status', [
                    AppointmentStatus::CANCELLED,
                    AppointmentStatus::COMPLETED,
                ])
                ->count(),

            'completed_appointments' => Appointment::query()
                ->where('customer_id', $customer->id)
                ->where('status', AppointmentStatus::COMPLETED)
                ->count(),

            'cancelled_appointments' => Appointment::query()
                ->where('customer_id', $customer->id)
                ->where('status', AppointmentStatus::CANCELLED)
                ->count(),
        ];
    }
}
