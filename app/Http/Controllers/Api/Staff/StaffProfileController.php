<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staffs\StoreStaffProfileRequest;
use App\Http\Requests\Staffs\UpdateStaffProfileRequest;
use App\Http\Resources\StaffProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'The current password you entered is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
