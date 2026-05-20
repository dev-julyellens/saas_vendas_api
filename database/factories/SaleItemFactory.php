<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Company\Models\Company;
use App\Modules\Product\Models\Product;
use App\Modules\Sale\Models\Sale;
use App\Modules\Sale\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 10);
        $unit = fake()->randomFloat(2, 10, 500);

        return [
            'company_id' => Company::factory(),
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'consignment_item_id' => null,
            'quantity' => $qty,
            'unit_price' => $unit,
            'total' => $qty * $unit,
        ];
    }
}
