<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Core\Enums\SaleStatus;
use App\Modules\Company\Models\Company;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);

        return [
            'company_id' => Company::factory(),
            'reseller_id' => Reseller::factory(),
            'customer_id' => null,
            'representative_id' => null,
            'consignment_id' => null,
            'code' => strtoupper(fake()->unique()->bothify('VND-####')),
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'status' => SaleStatus::Pending,
            'sold_at' => null,
        ];
    }
}
