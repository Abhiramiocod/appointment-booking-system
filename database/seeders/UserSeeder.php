<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => UserRole::ADMIN,
                'image' => 'users/image.jpeg',
            ]
        );

        $staffs = [
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '9876543210',
                'experience_years' => 6,
                'status' => EmploymentStatus::ACTIVE,
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '9876543211',
                'experience_years' => 3,
                'status' => EmploymentStatus::ON_LEAVE,
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah@example.com',
                'phone' => '9876543212',
                'experience_years' => 8,
                'status' => EmploymentStatus::ACTIVE,
            ],
        ];

        foreach ($staffs as $member) {
            $user = User::updateOrCreate(
                ['email' => $member['email']],
                [
                    'name' => $member['name'],
                    'password' => bcrypt('password'),
                    'role' => UserRole::STAFF,
                    'image' => 'users/staff.jpeg',
                ]
            );

            $user->staffProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $member['phone'],
                    'bio' => 'Experienced healthcare professional.',
                    'experience_years' => $member['experience_years'],
                    'profile_photo' => '/storage/staff_profiles/staff.jpeg',
                    'employment_status' => $member['status'],
                ]
            );
        }
    }
}
