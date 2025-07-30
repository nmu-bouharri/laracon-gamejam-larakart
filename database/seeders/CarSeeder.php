<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Car;

class CarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cars = [
            // Taylor's Legendary Lambo (LOCKED until you beat him)
            [
                'name' => 'Taylor\'s Legendary Lambo',
                'slug' => 'taylors-legendary-lambo',
                'brand' => 'Lamborghini',
                'model' => 'Huracán EVO Special',
                'description' => 'Taylor\'s personal ride. Beat him in single player to unlock this beast!',
                'image_url' => '/storage/taylor-car.png',
                'speed_rating' => 100,
                'acceleration_rating' => 95,
                'handling_rating' => 90,
                'is_lambo' => true,
                'is_premium' => true,
                'unlock_level' => 999 // Special unlock level for Taylor's car
            ],
            
            // Available Cars for Other Developers
            [
                'name' => 'Nuno\'s Testing Machine',
                'slug' => 'nunos-testing-machine',
                'brand' => 'Porsche',
                'model' => '911 GT3 RS',
                'description' => 'Precise and reliable. Perfect for testing every corner of the track.',
                'image_url' => '/storage/nuno-car.png',
                'speed_rating' => 85,
                'acceleration_rating' => 80,
                'handling_rating' => 95,
                'is_lambo' => false,
                'is_premium' => false,
                'unlock_level' => 1
            ],
            [
                'name' => 'TJ\'s AI Speedster',
                'slug' => 'tjs-ai-speedster',
                'brand' => 'McLaren',
                'model' => '720S',
                'description' => 'AI-optimized performance. Machine learning meets racing.',
                'image_url' => '/storage/tj-car.png',
                'speed_rating' => 90,
                'acceleration_rating' => 92,
                'handling_rating' => 88,
                'is_lambo' => false,
                'is_premium' => false,
                'unlock_level' => 1
            ],
            [
                'name' => 'Aaron\'s Database Cruiser',
                'slug' => 'aarons-database-cruiser',
                'brand' => 'BMW',
                'model' => 'M8 Competition',
                'description' => 'Optimized for smooth performance. Queries the track like a pro.',
                'image_url' => '/storage/aaron-car.png',
                'speed_rating' => 82,
                'acceleration_rating' => 85,
                'handling_rating' => 87,
                'is_lambo' => false,
                'is_premium' => false,
                'unlock_level' => 1
            ]
        ];

        foreach ($cars as $car) {
            Car::create($car);
        }
    }
}
