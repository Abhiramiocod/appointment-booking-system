<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $services = [

            [
                'name' => 'Haircut',
                'slug' => Service::generateSlug('Haircut'),
                'description' => 'Professional haircut and styling.',
                'duration' => 45,
                'price' => 30,
            ],

            [
                'name' => 'Hair Coloring',
                'slug' => Service::generateSlug('Hair Coloring'),
                'description' => 'Complete hair coloring service.',
                'duration' => 120,
                'price' => 120,
            ],

            [
                'name' => 'Hair Wash',
                'slug' => Service::generateSlug('Hair Wash'),
                'description' => 'Professional hair wash and conditioning.',
                'duration' => 20,
                'price' => 15,
            ],

            [
                'name' => 'Beard Trim',
                'slug' => Service::generateSlug('Beard Trim'),
                'description' => 'Professional beard trimming.',
                'duration' => 20,
                'price' => 20,
            ],

            [
                'name' => 'Shaving',
                'slug' => Service::generateSlug('Shaving'),
                'description' => 'Traditional clean shave.',
                'duration' => 25,
                'price' => 18,
            ],

            [
                'name' => 'Facial',
                'slug' => Service::generateSlug('Facial'),
                'description' => 'Refreshing facial treatment.',
                'duration' => 60,
                'price' => 70,
            ],

            [
                'name' => 'Manicure',
                'slug' => Service::generateSlug('Manicure'),
                'description' => 'Professional manicure.',
                'duration' => 45,
                'price' => 35,
            ],

            [
                'name' => 'Pedicure',
                'slug' => Service::generateSlug('Pedicure'),
                'description' => 'Professional pedicure.',
                'duration' => 60,
                'price' => 45,
            ],

            [
                'name' => 'Swedish Massage',
                'slug' => Service::generateSlug('Swedish Massage'),
                'description' => 'Relaxing full-body massage.',
                'duration' => 60,
                'price' => 90,
            ],

            [
                'name' => 'Deep Tissue Massage',
                'slug' => Service::generateSlug('Deep Tissue Massage'),
                'description' => 'Deep muscle therapy massage.',
                'duration' => 90,
                'price' => 130,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }
    }
}
