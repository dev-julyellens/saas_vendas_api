<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Company\Models\Company;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 10, 500);

        return [
            'company_id' => Company::factory(),
            'product_category_id' => ProductCategory::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'unit_price' => $cost * fake()->randomFloat(2, 1.2, 2.5),
            'cost_price' => $cost,
            'is_active' => true,
        ];
    }
}
