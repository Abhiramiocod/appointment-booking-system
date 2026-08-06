<?php

namespace App\Actions\Admin\Staff;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateStaffAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {

            // Create user
            $staff = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => UserRole::STAFF,
                'image' => $data['image'] ?? null,
            ]);

            // Create profile
            $staff->staffProfile()->create([
                'phone' => $data['phone'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'employment_status' => $data['employment_status'] ?? 'active',
            ]);

            // Assign services
            if (! empty($data['service_ids'])) {
                $staff->services()->sync($data['service_ids']);
            }

            return $staff;
        });
    }
}
