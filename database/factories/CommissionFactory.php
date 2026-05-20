<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Core\Enums\CommissionStatus;
use App\Modules\Commission\Models\Commission;
use App\Modules\Company\Models\Company;
use App\Modules\Representative\Models\Representative;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commission>
 */
class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        $base = fake()->randomFloat(2, 100, 5000);
        $rate = fake()->randomFloat(4, 0.01, 0.15);

        return [
            'company_id' => Company::factory(),
            'sale_id' => Sale::factory(),
            'representative_id' => Representative::factory(),
            'base_amount' => $base,
            'rate' => $rate,
            'amount' => round($base * $rate, 2),
            'status' => CommissionStatus::Pending,
            'paid_at' => null,
        ];
    }
}
