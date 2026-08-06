<?php

namespace App\Http\Controllers\Api\Staff;

use App\actions\Staff\Review\GetStaffReviewsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request, GetStaffReviewsAction $action): JsonResponse
    {
        return response()->json(
            $action->execute($request->user())
        );
    }
}
