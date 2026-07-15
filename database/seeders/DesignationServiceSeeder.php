<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DesignationServiceSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [

            'Senior Hair Stylist' => [
                'Haircut',
                'Hair Coloring',
                'Hair Wash',
            ],

            'Hair Stylist' => [
                'Haircut',
                'Hair Wash',
            ],

            'Barber' => [
                'Haircut',
                'Beard Trim',
                'Shaving',
            ],

            'Color Specialist' => [
                'Hair Coloring',
            ],

            'Nail Technician' => [
                'Manicure',
                'Pedicure',
            ],

            'Massage Therapist' => [
                'Swedish Massage',
                'Deep Tissue Massage',
            ],

            'Esthetician' => [
                'Facial',
            ],

            'Spa Therapist' => [
                'Facial',
                'Swedish Massage',
            ],

            'Receptionist' => [],
        ];

        foreach ($mapping as $designationName => $serviceNames) {

            $designation = Designation::where('name', $designationName)->first();

            if (! $designation) {
                continue;
            }

            $serviceIds = Service::whereIn('name', $serviceNames)
                ->pluck('id')
                ->toArray();

            $designation->services()->sync($serviceIds);
        }
    }
}
