<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Company\Models\Company;
use App\Modules\Product\Models\Product;
use App\Modules\ReturnOrder\Models\ReturnItem;
use App\Modules\ReturnOrder\Models\ReturnOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnItem>
 */
class ReturnItemFactory extends Factory
{
    protected $model = ReturnItem::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'return_id' => ReturnOrder::factory(),
            'product_id' => Product::factory(),
            'sale_item_id' => null,
            'quantity' => fake()->numberBetween(1, 10),
            'unit_price' => fake()->optional()->randomFloat(2, 10, 500),
        ];
    }
}
