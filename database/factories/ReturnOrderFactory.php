<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Core\Enums\ReturnStatus;
use App\Modules\Company\Models\Company;
use App\Modules\Reseller\Models\Reseller;
use App\Modules\ReturnOrder\Models\ReturnOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnOrder>
 */
class ReturnOrderFactory extends Factory
{
    protected $model = ReturnOrder::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'reseller_id' => Reseller::factory(),
            'sale_id' => null,
            'consignment_id' => null,
            'code' => strtoupper(fake()->unique()->bothify('DEV-####')),
            'status' => ReturnStatus::Pending,
            'reason' => fake()->optional()->sentence(),
            'returned_at' => null,
        ];
    }
}
