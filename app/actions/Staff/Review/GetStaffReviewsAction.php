<?php

namespace App\actions\Staff\Review;

use App\Models\StaffReview;
use App\Models\User;

class GetStaffReviewsAction
{
    public function execute(User $staff): array
    {
        $reviewsQuery = StaffReview::query()
            ->where('staff_id', $staff->id)
            ->with([
                'customer:id,name',
                'appointment:id,appointment_date,service_id',
                'appointment.service:id,name',
            ])
            ->latest();

        $stats = [
            'average_rating' => round((float) ($reviewsQuery->avg('rating') ?? 0.0), 2),
            'total_reviews' => (int) $reviewsQuery->count(),
        ];

        $reviews = $reviewsQuery->paginate(15);

        return [
            'reviews' => $reviews,
            'stats' => $stats,
        ];
    }
}
