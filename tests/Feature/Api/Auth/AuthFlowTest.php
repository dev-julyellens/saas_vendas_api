<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Auth;

use App\Core\Enums\AccessLogEvent;
use App\Core\Enums\RoleSlug;
use App\Models\User;
use App\Modules\Auth\Models\AccessLog;
use App\Modules\Company\Models\Company;
use App\Modules\Rbac\Models\Permission;
use App\Modules\Rbac\Models\Role;
use Database\Seeders\MasterAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createTenantUser(): User
    {
        $company = Company::query()->create([
            'name' => 'Test Co',
            'document' => fake()->unique()->numerify('##############'),
        ]);

        $permission = Permission::query()->withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Gerenciar produtos',
            'slug' => 'products.manage',
            'module' => 'product',
        ]);

        $role = Role::query()->withoutGlobalScopes()->create([
            'company_id' => $company->id,
            'name' => 'Empresa',
            'slug' => RoleSlug::Empresa->value,
            'is_system' => true,
        ]);

        $role->permissions()->attach($permission->id, ['company_id' => $company->id]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'tenant@test.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($role->id, ['company_id' => $company->id]);

        return $user;
    }

    public function test_login_returns_token_and_creates_access_log(): void
    {
        $this->createTenantUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'tenant@test.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user', 'expires_in']]);

        $this->assertDatabaseHas('access_logs', [
            'email' => 'tenant@test.com',
            'event' => AccessLogEvent::LoginSuccess->value,
        ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->createTenantUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'tenant@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();

        $this->assertDatabaseHas('access_logs', [
            'event' => AccessLogEvent::LoginFailed->value,
        ]);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = $this->createTenantUser();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $token = $login->json('data.token');

        $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ])
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_logout_invalidates_session(): void
    {
        $user = $this->createTenantUser();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $token = $login->json('data.token');

        $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk();

        $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertUnauthorized();
    }

    public function test_master_admin_can_access_me_without_company(): void
    {
        $this->seed(MasterAdminSeeder::class);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'master@saas.local',
            'password' => 'Master@123',
        ]);

        $token = $login->json('data.token');

        $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ])
            ->assertOk()
            ->assertJsonPath('data.is_master', true);
    }

    public function test_user_with_permission_passes_middleware(): void
    {
        $user = $this->createTenantUser();

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $token = $login->json('data.token');

        $this->getJson('/api/v1/products', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertOk();
    }
}
