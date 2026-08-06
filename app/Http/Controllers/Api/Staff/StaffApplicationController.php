<?php

namespace App\Http\Controllers\Api\Staff;

use App\actions\Staff\Application\SubmitStaffApplicationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staffs\Application\StoreStaffApplicationRequest;
use App\Http\Resources\StaffApplicationResource;
use Exception;
use Illuminate\Http\JsonResponse;

class StaffApplicationController extends Controller
{
    /**
     * Submit a staff application.
     */
    public function store(
        StoreStaffApplicationRequest $request,
        SubmitStaffApplicationAction $action
    ): JsonResponse {
        try {
            $application = $action->execute($request->validated());

            return response()->json([
                'message' => 'Your staff application has been submitted successfully.',
                'data' => new StaffApplicationResource($application),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}
