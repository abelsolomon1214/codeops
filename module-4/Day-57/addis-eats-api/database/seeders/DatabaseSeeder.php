<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(5)->create();

        $restaurants = [
            [
                'name'          => 'Yod Abyssinia',
                'slug'          => 'yod-abyssinia',
                'area'          => 'Bole',
                'phone'         => '+251911223344',
                'opening_hours' => '11:00 - 23:00',
            ],
            [
                'name'          => 'Kategna',
                'slug'          => 'kategna',
                'area'          => 'Bole',
                'phone'         => '+251911556677',
                'opening_hours' => '07:30 - 22:30',
            ],
            [
                'name'          => 'Sishu',
                'slug'          => 'sishu',
                'area'          => 'Kazanchis',
                'phone'         => '+251911889900',
                'opening_hours' => '11:30 - 21:30',
            ],
            [
                'name'          => '2000 Habesha',
                'slug'          => '2000-habesha',
                'area'          => 'Bole',
                'phone'         => '+251912345678',
                'opening_hours' => '12:00 - 23:30',
            ],
            [
                'name'          => 'Totot Traditional Food',
                'slug'          => 'totot-traditional-food',
                'area'          => 'Megenagna',
                'phone'         => '+251913456789',
                'opening_hours' => '08:00 - 22:00',
            ],
        ];

        foreach ($restaurants as $restaurant) {
            Restaurant::create($restaurant);
        }

        Dish::factory()->count(40)->create();

        $this->call(OrderSeeder::class);
    }
}
