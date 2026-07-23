<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Reviews\StoreStaffReviewRequest;
use App\Http\Requests\Customer\Reviews\UpdateStaffReviewRequest;
use App\Models\Appointment;
use App\Models\StaffReview;
use Illuminate\Http\JsonResponse;

class StaffReviewController extends Controller
{
    /**
     * Create a review for a completed appointment.
     */
    public function store(
        StoreStaffReviewRequest $request,
        Appointment $appointment
    ): JsonResponse {
        // Customer can only review their own appointment
        if ($appointment->customer_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Appointment must be completed
        if ($appointment->status !== 'completed') {
            return response()->json([
                'message' => 'You can only review completed appointments.',
            ], 422);
        }

        // Prevent duplicate reviews
        if ($appointment->review()->exists()) {
            return response()->json([
                'message' => 'You have already reviewed this appointment.',
            ], 422);
        }

        $review = StaffReview::create([
            'appointment_id' => $appointment->id,
            'staff_id' => $appointment->staff_id,
            'customer_id' => auth()->id(),
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'data' => $review,
        ], 201);
    }

    /**
     * Update an existing review.
     */
    public function update(
        UpdateStaffReviewRequest $request,
        StaffReview $review
    ): JsonResponse {
        if ($review->customer_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $review->update([
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return response()->json([
            'message' => 'Review updated successfully.',
            'data' => $review->fresh(),
        ]);
    }

    /**
     * Delete a review.
     */
    public function destroy(
        StaffReview $review
    ): JsonResponse {
        if ($review->customer_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully.',
        ]);
    }
}
