<?php

namespace App\actions\Customer\StaffReview;

use App\Models\StaffReview;
use App\Models\User;
use Exception;

class UpdateStaffReviewAction
{
    public function execute(StaffReview $review, User $customer, array $data): StaffReview
    {
        if ($review->customer_id !== $customer->id) {
            throw new Exception('Unauthorized.', 403);
        }

        $review->update([
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
        ]);

        return $review->fresh();
    }
}
