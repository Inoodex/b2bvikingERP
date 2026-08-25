<?php

namespace Database\Factories;

use App\Models\StockBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockBatch>
 */
class StockBatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'batch_no' => 'BATCH-' . strtoupper($this->faker->bothify('?????-#####')),
            'outlet_id' => 1,
            'product_id' => 1,
            'variant_id' => null,
            'qty_received' => 100,
            'qty_remaining' => 100,
            'unit_cost' => 50.00,
            'received_date' => now(),
        ];
    }
}
