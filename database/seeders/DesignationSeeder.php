<?php

namespace Database\Seeders;

use App\Models\Designation;
use Illuminate\Database\Seeder;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $designations = [
            [
                'name' => 'Senior Hair Stylist',
                'description' => 'Provides advanced hair cutting, coloring, and styling services.',
            ],
            [
                'name' => 'Hair Stylist',
                'description' => 'Provides professional hair styling and treatments.',
            ],
            [
                'name' => 'Barber',
                'description' => 'Provides men\'s grooming, haircuts, beard trimming, and shaving.',
            ],
            [
                'name' => 'Color Specialist',
                'description' => 'Specializes in hair coloring and highlighting.',
            ],
            [
                'name' => 'Nail Technician',
                'description' => 'Provides manicure, pedicure, and nail care services.',
            ],
            [
                'name' => 'Massage Therapist',
                'description' => 'Provides therapeutic and relaxation massage services.',
            ],
            [
                'name' => 'Esthetician',
                'description' => 'Provides skincare, facial, and beauty treatments.',
            ],
            [
                'name' => 'Spa Therapist',
                'description' => 'Provides body treatments and wellness therapies.',
            ],
            [
                'name' => 'Receptionist',
                'description' => 'Manages appointments and customer reception.',
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
