<?php

namespace App\Http\Controllers\Api\Staff;

use App\actions\Staff\Profile\ChangeStaffPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staffs\ChangeStaffPasswordRequest;
use App\Http\Requests\Staffs\StoreStaffProfileRequest;
use App\Http\Requests\Staffs\UpdateStaffProfileRequest;
use App\Http\Resources\StaffProfileResource;
use Exception;
use Illuminate\Http\JsonResponse;

class StaffProfileController extends Controller
{
    public function show(): StaffProfileResource
    {
        $profile = auth()->user()->staffProfile;

        abort_if(! $profile, 404, 'Profile not found.');

        return new StaffProfileResource($profile);
    }

    public function store(StoreStaffProfileRequest $request): StaffProfileResource|JsonResponse
    {
        if (auth()->user()->staffProfile) {
            return response()->json([
                'message' => 'Profile already exists.',
            ], 422);
        }

        $profile = auth()->user()->staffProfile()->create(
            $request->validated()
        );

        return new StaffProfileResource($profile);
    }

    public function update(UpdateStaffProfileRequest $request): StaffProfileResource
    {
        $profile = auth()->user()->staffProfile;

        abort_if(! $profile, 404, 'Profile not found.');

        $profile->update($request->validated());

        return new StaffProfileResource($profile);
    }

    public function changePassword(
        ChangeStaffPasswordRequest $request,
        ChangeStaffPasswordAction $action
    ): JsonResponse {
        try {
            $action->execute(
                $request->user(),
                $request->current_password,
                $request->new_password
            );

            return response()->json([
                'message' => 'Password changed successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}
