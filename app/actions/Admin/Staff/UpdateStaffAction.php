<?php

namespace App\Actions\Admin\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateStaffAction
{
    public function execute(User $staff, array $data): User
    {
        return DB::transaction(function () use ($staff, $data) {

            // Update user
            $userFields = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'image' => $data['image'] ?? null,
            ], fn ($value) => $value !== null);

            if (! empty($data['password'])) {
                $userFields['password'] = Hash::make($data['password']);
            }

            if (! empty($userFields)) {
                $staff->update($userFields);
            }

            // Update or create profile
            $profileFields = array_filter([
                'phone' => $data['phone'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'employment_status' => $data['employment_status'] ?? null,
            ], fn ($value) => $value !== null);

            if (! empty($profileFields)) {
                $staff->staffProfile()->updateOrCreate(
                    ['user_id' => $staff->id],
                    $profileFields
                );
            }

            // Sync services
            if (array_key_exists('service_ids', $data)) {
                $staff->services()->sync($data['service_ids']);
            }

            return $staff->fresh();
        });
    }
}
