<?php

namespace Database\Seeders;

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
                'image' => 'images/users/image.jpg',
            ]
        );

        $staff = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => bcrypt('password'),
                'role' => UserRole::STAFF,
                'image' => 'images/users/staff.jpg',
            ]
        );

        $staff->staffProfile()->updateOrCreate(
            [],
            [
                'phone' => '8138927654',
                'bio' => 'Experienced professional',
                'experience_years' => 5,
            ]
        );
    }
}
