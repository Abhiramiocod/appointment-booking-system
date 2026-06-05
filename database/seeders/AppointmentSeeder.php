<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('role', UserRole::CUSTOMER)->first();

        $staff = User::where('role', UserRole::STAFF)->first();

        $service = Service::first();

        if (! $customer || ! $staff || ! $service) {
            return;
        }

        Appointment::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'staff_id' => $staff->id,
                'service_id' => $service->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '10:00:00',
            ],
            [
                'end_time' => '10:30:00',
                'status' => AppointmentStatus::CONFIRMED,
                'notes' => 'Seeded appointment',
            ]
        );

        Appointment::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'staff_id' => $staff->id,
                'service_id' => $service->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '11:00:00',
            ],
            [
                'end_time' => '11:30:00',
                'status' => AppointmentStatus::PENDING,
                'notes' => 'Another seeded appointment',
            ]
        );
    }
}
