<?php

namespace App\actions\Customer\Appointment;

use App\Models\Appointment;
use App\Models\User;
use App\Services\NotificationService;

class StoreAppointmentReviewAction
{
    public function execute(Appointment $appointment, User $customer, array $data): Appointment
    {
        $appointment->review()->create([
            'staff_id' => $appointment->staff_id,
            'customer_id' => $customer->id,
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
        ]);

        NotificationService::notify(
            user: $appointment->staff,
            title: 'New Review Received',
            message: "{$appointment->customer->name} left you a review with rating: {$data['rating']}/5 stars.",
            type: 'review',
            actionUrl: '/staff/reviews'
        );

        return $appointment;
    }
}
