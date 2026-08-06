<?php

namespace App\Http\Controllers\Api\Staff;

use App\actions\Staff\WorkingHour\UpdateWorkingHoursAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staffs\UpdateWorkingHoursRequest;
use App\Http\Resources\WorkingHourResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WorkingHourController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return WorkingHourResource::collection(
            $request->user()->workingHours()->orderBy('day_of_week')->get()
        );
    }

    public function update(
        UpdateWorkingHoursRequest $request,
        UpdateWorkingHoursAction $action
    ): JsonResponse {
        $action->execute(
            $request->user(),
            $request->validated()['working_hours']
        );

        return response()->json([
            'message' => 'Working hours updated successfully',
        ]);
    }
}
