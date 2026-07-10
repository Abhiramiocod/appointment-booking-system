<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Manicure', 'slug' => Service::generateSlug('Manicure'), 'description' => 'Professional nail care', 'duration' => 30, 'price' => 25.00],
            ['name' => 'Pedicure', 'slug' => Service::generateSlug('Pedicure'), 'description' => 'Foot and nail treatment', 'duration' => 45, 'price' => 35.00],
            ['name' => 'Haircut', 'slug' => Service::generateSlug('Haircut'), 'description' => 'Men\u2019s haircut', 'duration' => 60, 'price' => 45.00],
            ['name' => 'Coloring', 'slug' => Service::generateSlug('Coloring'), 'description' => 'Hair coloring service', 'duration' => 120, 'price' => 100.00],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
