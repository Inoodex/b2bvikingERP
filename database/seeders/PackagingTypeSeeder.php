<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PackagingType;
use Illuminate\Database\Seeder;

class PackagingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Small Box / Inner Pack',
                'code' => 'BOX',
                'level_order' => 1,
                'tare_weight' => 0.150,
                'max_weight' => 5.000,
                'length_cm' => 25.00,
                'width_cm' => 18.00,
                'height_cm' => 12.00,
                'is_active' => true,
            ],
            [
                'name' => 'Medium Carton',
                'code' => 'MCTN',
                'level_order' => 2,
                'tare_weight' => 0.350,
                'max_weight' => 15.000,
                'length_cm' => 40.00,
                'width_cm' => 30.00,
                'height_cm' => 25.00,
                'is_active' => true,
            ],
            [
                'name' => 'Master Case / Big Carton',
                'code' => 'CTN',
                'level_order' => 3,
                'tare_weight' => 0.650,
                'max_weight' => 30.000,
                'length_cm' => 60.00,
                'width_cm' => 40.00,
                'height_cm' => 40.00,
                'is_active' => true,
            ],
            [
                'name' => 'Euro Pallet',
                'code' => 'PLT',
                'level_order' => 4,
                'tare_weight' => 22.000,
                'max_weight' => 800.000,
                'length_cm' => 120.00,
                'width_cm' => 80.00,
                'height_cm' => 14.40,
                'is_active' => true,
            ],
            [
                'name' => 'Shipping Container (20ft/40ft)',
                'code' => 'CONT',
                'level_order' => 5,
                'tare_weight' => 2200.000,
                'max_weight' => 28000.000,
                'length_cm' => 590.00,
                'width_cm' => 235.00,
                'height_cm' => 239.00,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            PackagingType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
