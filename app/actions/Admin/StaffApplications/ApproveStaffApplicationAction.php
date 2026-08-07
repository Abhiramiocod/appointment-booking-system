<?php

namespace App\Actions\Admin\StaffApplications;

use App\Enums\EmploymentStatus;
use App\Enums\StaffApplicationStatus;
use App\Enums\UserRole;
use App\Models\StaffApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApproveStaffApplicationAction
{
    public function execute(StaffApplication $staffApplication): array
    {
        if ($staffApplication->status !== StaffApplicationStatus::PENDING) {
            throw new HttpException(
                422,
                'This application has already been processed.'
            );
        }

        return DB::transaction(function () use ($staffApplication) {

            $temporaryPassword = Str::password(10);

            $staff = User::create([
                'name' => $staffApplication->name,
                'email' => $staffApplication->email,
                'password' => Hash::make($temporaryPassword),
                'role' => UserRole::STAFF,
            ]);

            $staff->staffProfile()->create([
                'designation_id' => $staffApplication->designation_id,
                'phone' => $staffApplication->phone,
                'bio' => $staffApplication->cover_letter,
                'experience_years' => $staffApplication->experience_years,
                'employment_status' => EmploymentStatus::ACTIVE,
            ]);

            $serviceIds = $staffApplication->designation
                ->services()
                ->pluck('services.id');

            $staff->services()->sync($serviceIds);

            $staffApplication->update([
                'status' => StaffApplicationStatus::APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            Log::info('New staff account approved.', [
                'email' => $staff->email,
                'temporary_password' => $temporaryPassword,
            ]);

            return [
                'message' => 'Application approved successfully.',
                'temporary_password' => $temporaryPassword,
                'staff' => $staff->load([
                    'staffProfile.designation',
                    'services',
                ]),
            ];
        });
    }
}
