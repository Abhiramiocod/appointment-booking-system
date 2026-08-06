<?php

namespace App\actions\Customer\StaffReview;

use App\Models\StaffReview;
use App\Models\User;
use Exception;

class DeleteStaffReviewAction
{
    public function execute(StaffReview $review, User $customer): bool
    {
        if ($review->customer_id !== $customer->id) {
            throw new Exception('Unauthorized.', 403);
        }

        return (bool) $review->delete();
    }
}
