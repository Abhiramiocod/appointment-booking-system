<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            [
                'name' => 'Dentist',
                'description' => 'Performs dental examinations and treatments.',
            ],
            [
                'name' => 'Dental Assistant',
                'description' => 'Assists dentists during procedures.',
            ],
            [
                'name' => 'Receptionist',
                'description' => 'Handles appointments and front desk operations.',
            ],
            [
                'name' => 'Barber',
                'description' => 'Provides haircut and grooming services.',
            ],
            [
                'name' => 'Hair Stylist',
                'description' => 'Provides hair styling and treatment services.',
            ],
            [
                'name' => 'Massage Therapist',
                'description' => 'Provides massage therapy services.',
            ],
            [
                'name' => 'Physiotherapist',
                'description' => 'Provides physical rehabilitation treatments.',
            ],
            [
                'name' => 'Dermatologist',
                'description' => 'Specialist in skin care and treatment.',
            ],
            [
                'name' => 'Esthetician',
                'description' => 'Provides skincare and beauty treatments.',
            ],
            [
                'name' => 'Nurse',
                'description' => 'Provides patient care and medical assistance.',
            ],
        ];

        foreach ($designations as $designation) {
            Designation::updateOrCreate(
                ['name' => $designation['name']],
                $designation
            );
        }
    }
}
