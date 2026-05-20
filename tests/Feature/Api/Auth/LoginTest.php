<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\Modules\Company\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $company = Company::query()->create([
            'name' => 'Test Co',
            'document' => '11111111000111',
        ]);

        User::query()->create([
            'company_id' => $company->id,
            'name' => 'Tester',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'user'],
            ]);
    }
}
