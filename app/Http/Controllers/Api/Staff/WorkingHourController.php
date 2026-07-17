<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staffs\UpdateWorkingHoursRequest;
use App\Http\Resources\WorkingHourResource;
use App\Models\WorkingHour;
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

    public function update(UpdateWorkingHoursRequest $request): JsonResponse
    {
        $validated = $request->validated();

        foreach ($validated['working_hours'] as $hour) {

            WorkingHour::updateOrCreate(
                [
                    'staff_id' => $request->user()->id,
                    'day_of_week' => $hour['day_of_week'],
                ],
                [
                    'start_time' => $hour['is_available']
                        ? $hour['start_time']
                        : null,

                    'end_time' => $hour['is_available']
                        ? $hour['end_time']
                        : null,

                    'is_available' => $hour['is_available'],
                    'breaks' => $hour['is_available']
                        ? ($hour['breaks'] ?? [])
                        : [],
                ]
            );
        }

        return response()->json([
            'message' => 'Working hours updated successfully',
        ]);
    }
}
