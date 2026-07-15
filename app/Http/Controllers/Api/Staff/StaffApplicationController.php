<?php

namespace App\Http\Controllers\Api\Staff;

use App\Enums\StaffApplicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staffs\Application\StoreStaffApplicationRequest;
use App\Http\Resources\StaffApplicationResource;
use App\Models\StaffApplication;
use App\Models\User;

class StaffApplicationController extends Controller
{
    /**
     * Submit a staff application.
     */
    public function store(StoreStaffApplicationRequest $request)
    {
        // Don't allow existing staff to apply again
        if (
            User::where('email', $request->email)
                ->where('role', UserRole::STAFF)
                ->exists()
        ) {
            return response()->json([
                'message' => 'A staff account already exists with this email.',
            ], 422);
        }

        // Prevent duplicate pending applications
        if (
            StaffApplication::where('email', $request->email)
                ->where('status', StaffApplicationStatus::PENDING)
                ->exists()
        ) {
            return response()->json([
                'message' => 'A pending application already exists for this email.',
            ], 422);
        }

        $application = StaffApplication::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'designation_id' => $request->designation_id,
            'status' => StaffApplicationStatus::PENDING,
            'cover_letter' => $request->cover_letter,
            'experience_years' => $request->experience_years,
        ]);

        return response()->json([
            'message' => 'Your staff application has been submitted successfully.',
            'data' => new StaffApplicationResource($application),
        ], 201);
    }
}
