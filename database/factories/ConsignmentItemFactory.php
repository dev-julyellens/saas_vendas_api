<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Company\Models\Company;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Consignment\Models\ConsignmentItem;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsignmentItem>
 */
class ConsignmentItemFactory extends Factory
{
    protected $model = ConsignmentItem::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'consignment_id' => Consignment::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 50),
            'unit_price' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
