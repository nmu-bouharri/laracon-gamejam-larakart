<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhpDeveloper;

class PhpDeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Taylor Otwell - The ultimate boss
        PhpDeveloper::create([
            'name' => 'Taylor Otwell',
            'slug' => 'taylor-otwell',
            'bio' => 'Creator of Laravel. The legend himself.',
            'avatar_url' => '/storage/taylor.png',
            'special_abilities' => json_encode([
                'speed' => 100,
                'handling' => 100,
                'acceleration' => 100
            ]),
            'popularity_rating' => 100,
            'is_featured' => true,
            'is_locked' => true,
            'unlock_order' => 4, // Final boss
        ]);

        // Create developers in progression order
        PhpDeveloper::create([
            'name' => 'TJ Miller',
            'slug' => 'tj-miller',
            'bio' => 'AI programmer extraordinaire at Geocodio. Built Prism PHP and loves fast cars.',
            'avatar_url' => '/storage/tj.png',
            'special_abilities' => json_encode([
                'speed' => 65,
                'handling' => 70,
                'acceleration' => 60
            ]),
            'popularity_rating' => 75,
            'is_featured' => false,
            'is_locked' => true, // LOCKED as character - only his stage is available
            'unlock_order' => 1, // First opponent stage
        ]);

        PhpDeveloper::create([
            'name' => 'Nuno Maduro',
            'slug' => 'nuno-maduro',
            'bio' => 'Laravel core team member and Pest PHP creator. Testing his way to victory.',
            'avatar_url' => '/storage/nuno.png',
            'special_abilities' => json_encode([
                'speed' => 80,
                'handling' => 85,
                'acceleration' => 75
            ]),
            'popularity_rating' => 85,
            'is_featured' => false,
            'is_locked' => true,
            'unlock_order' => 2, // Second opponent
        ]);

        PhpDeveloper::create([
            'name' => 'Aaron Francis',
            'slug' => 'aaron-francis',
            'bio' => 'Database wizard and Laravel educator. Knows every MySQL optimization trick.',
            'avatar_url' => '/storage/aaron.png',
            'special_abilities' => json_encode([
                'speed' => 85,
                'handling' => 90,
                'acceleration' => 80
            ]),
            'popularity_rating' => 88,
            'is_featured' => false,
            'is_locked' => false, // UNLOCKED - Only available character
            'unlock_order' => 0, // Available from start
        ]);
    }
}
