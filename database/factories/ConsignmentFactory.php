<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Core\Enums\ConsignmentStatus;
use App\Modules\Company\Models\Company;
use App\Modules\Consignment\Models\Consignment;
use App\Modules\Representative\Models\Representative;
use App\Modules\Reseller\Models\Reseller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consignment>
 */
class ConsignmentFactory extends Factory
{
    protected $model = Consignment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'reseller_id' => Reseller::factory(),
            'representative_id' => Representative::factory(),
            'code' => strtoupper(fake()->unique()->bothify('CONS-####')),
            'status' => ConsignmentStatus::Aberto,
            'consigned_at' => now()->toDateString(),
            'expected_return_at' => now()->addDays(30)->toDateString(),
            'closed_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
