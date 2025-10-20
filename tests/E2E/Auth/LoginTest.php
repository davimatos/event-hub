<?php

namespace Tests\E2E\Auth;

use App\Modules\User\Infra\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials()
    {
        $user = UserModel::factory()->create([
            'email' => 'test123@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'test123@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_with_invalid_credentials()
    {
        $user = UserModel::factory()->create([
            'email' => 'test123@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);

        $this->assertEmpty($response->json('token'));
    }

    public function test_login_with_missing_email()
    {
        $response = $this->postJson('/api/v1/auth/token', [
            'password' => 'password',
        ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_login_with_missing_password()
    {
        $response = $this->postJson('/api/v1/auth/token', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);
    }
}
