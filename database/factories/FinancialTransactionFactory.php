<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Core\Enums\FinancialTransactionStatus;
use App\Core\Enums\FinancialTransactionType;
use App\Modules\Company\Models\Company;
use App\Modules\Financial\Models\FinancialTransaction;
use App\Modules\Sale\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    protected $model = FinancialTransaction::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'reference_type' => Sale::class,
            'reference_id' => Sale::factory(),
            'type' => FinancialTransactionType::Receivable,
            'category' => 'venda',
            'amount' => fake()->randomFloat(2, 50, 10000),
            'due_date' => now()->addDays(30)->toDateString(),
            'paid_at' => null,
            'status' => FinancialTransactionStatus::Pending,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
