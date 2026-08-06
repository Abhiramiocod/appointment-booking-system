<?php

namespace App\actions\Customer\StaffReview;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\StaffReview;
use App\Models\User;
use Exception;

class CreateStaffReviewAction
{
    public function execute(Appointment $appointment, User $customer, array $data): StaffReview
    {
        if ($appointment->customer_id !== $customer->id) {
            throw new Exception('Unauthorized.', 403);
        }

        if ($appointment->status !== AppointmentStatus::COMPLETED && $appointment->status !== 'completed') {
            throw new Exception('You can only review completed appointments.', 422);
        }

        if ($appointment->review()->exists()) {
            throw new Exception('You have already reviewed this appointment.', 422);
        }

        return StaffReview::create([
            'appointment_id' => $appointment->id,
            'staff_id' => $appointment->staff_id,
            'customer_id' => $customer->id,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
        ]);
    }
}
