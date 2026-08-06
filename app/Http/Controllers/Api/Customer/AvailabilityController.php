<?php

namespace App\Http\Controllers\Api\Customer;

use App\actions\Customer\Availability\GetAvailableSlotsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AvailableSlotsRequest;
use App\Models\User;

class AvailabilityController extends Controller
{
    public function availableSlots(AvailableSlotsRequest $request, User $staff, GetAvailableSlotsAction $action)
    {
        return response()->json([
            'data' => $action->execute(
                $staff,
                $request->validated()
            ),
        ]);
    }
}
