<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;

class WorkingHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffUsers = User::where('role', UserRole::STAFF)->get();

        foreach ($staffUsers as $staff) {
            foreach (range(0, 6) as $day) {
                WorkingHour::updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                        'is_available' => $day !== 0,
                    ]
                );
            }
        }
    }
}
