<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Core\Enums\StockMovementType;
use App\Modules\Company\Models\Company;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'reseller_id' => null,
            'movement_type' => StockMovementType::Entrada,
            'quantity' => fake()->numberBetween(1, 100),
            'unit_cost' => fake()->randomFloat(2, 5, 500),
            'reference_type' => null,
            'reference_id' => null,
            'notes' => fake()->optional()->sentence(),
            'occurred_at' => now(),
            'created_by' => null,
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn() => ['movement_type' => StockMovementType::Entrada]);
    }

    public function consigned(): static
    {
        return $this->state(fn() => ['movement_type' => StockMovementType::Consignado]);
    }
}
