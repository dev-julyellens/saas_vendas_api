<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Modules\Company\Models\Company;
use App\Modules\Rbac\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $module = fake()->randomElement(['product', 'sale', 'consignment', 'financial']);

        return [
            'company_id' => Company::factory(),
            'name' => fake()->sentence(3),
            'slug' => $module . '.' . Str::slug(fake()->unique()->word()),
            'module' => $module,
            'description' => fake()->sentence(),
        ];
    }
}
