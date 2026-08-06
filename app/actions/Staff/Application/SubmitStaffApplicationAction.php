<?php

namespace App\actions\Staff\Application;

use App\Enums\StaffApplicationStatus;
use App\Enums\UserRole;
use App\Models\StaffApplication;
use App\Models\User;
use Exception;

class SubmitStaffApplicationAction
{
    public function execute(array $data): StaffApplication
    {
        // Don't allow existing staff to apply again
        if (
            User::where('email', $data['email'])
                ->where('role', UserRole::STAFF)
                ->exists()
        ) {
            throw new Exception('A staff account already exists with this email.', 422);
        }

        // Prevent duplicate pending applications
        if (
            StaffApplication::where('email', $data['email'])
                ->where('status', StaffApplicationStatus::PENDING)
                ->exists()
        ) {
            throw new Exception('A pending application already exists for this email.', 422);
        }

        return StaffApplication::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'designation_id' => $data['designation_id'],
            'status' => StaffApplicationStatus::PENDING,
            'cover_letter' => $data['cover_letter'] ?? null,
            'experience_years' => $data['experience_years'] ?? null,
        ]);
    }
}
