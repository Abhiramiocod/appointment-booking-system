<?php

namespace App\Http\Controllers\Api\Customer;

use App\actions\Customer\StaffReview\CreateStaffReviewAction;
use App\actions\Customer\StaffReview\DeleteStaffReviewAction;
use App\actions\Customer\StaffReview\UpdateStaffReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\Reviews\StoreStaffReviewRequest;
use App\Http\Requests\Customer\Reviews\UpdateStaffReviewRequest;
use App\Models\Appointment;
use App\Models\StaffReview;
use Exception;
use Illuminate\Http\JsonResponse;

class StaffReviewController extends Controller
{
    /**
     * Create a review for a completed appointment.
     */
    public function store(
        StoreStaffReviewRequest $request,
        Appointment $appointment,
        CreateStaffReviewAction $action
    ): JsonResponse {
        try {
            $review = $action->execute(
                $appointment,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Review submitted successfully.',
                'data' => $review,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    /**
     * Update an existing review.
     */
    public function update(
        UpdateStaffReviewRequest $request,
        StaffReview $review,
        UpdateStaffReviewAction $action
    ): JsonResponse {
        try {
            $updatedReview = $action->execute(
                $review,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Review updated successfully.',
                'data' => $updatedReview,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    /**
     * Delete a review.
     */
    public function destroy(
        StaffReview $review,
        DeleteStaffReviewAction $action
    ): JsonResponse {
        try {
            $action->execute($review, request()->user());

            return response()->json([
                'message' => 'Review deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}
