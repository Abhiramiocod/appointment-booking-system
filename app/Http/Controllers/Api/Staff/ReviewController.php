<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $staffId = $request->user()->id;

        $reviewsQuery = StaffReview::query()
            ->where('staff_id', $staffId)
            ->with([
                'customer:id,name',
                'appointment:id,appointment_date,service_id',
                'appointment.service:id,name',
            ])
            ->latest();

        $stats = [
            'average_rating' => round($reviewsQuery->avg('rating') ?? 0.0, 2),
            'total_reviews' => $reviewsQuery->count(),
        ];

        $reviews = $reviewsQuery->paginate(15);

        return response()->json([
            'reviews' => $reviews,
            'stats' => $stats,
        ]);
    }
}
