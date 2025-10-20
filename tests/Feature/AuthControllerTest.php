<?php

namespace Tests\Feature;

use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = $this->mock(AuthService::class);
    }

    public function test_login_successful(): void
    {
        $credentials = [
            'email' => 'partner@example.com',
            'password' => 'password123'
        ];

        $mockResponse = [
            'token' => 'test-token-123',
            'user' => [
                'id' => 1,
                'email' => 'partner@example.com',
                'name' => 'Test Partner'
            ]
        ];

        $this->authService
            ->shouldReceive('login')
            ->once()
            ->with($credentials)
            ->andReturn($mockResponse);

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_login_validation_fails_without_email(): void
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validation_fails_with_invalid_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validation_fails_without_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'partner@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $credentials = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword'
        ];

        $this->authService
            ->shouldReceive('login')
            ->once()
            ->with($credentials)
            ->andThrow(new \Exception('Invalid credentials'));

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $mockUser = [
            'id' => 1,
            'email' => 'partner@example.com',
            'name' => 'Test Partner',
            'role' => 'partner'
        ];

        $this->authService
            ->shouldReceive('me')
            ->once()
            ->andReturn($mockUser);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockUser
            ]);
    }

    public function test_me_fails_when_unauthorized(): void
    {
        $this->authService
            ->shouldReceive('me')
            ->once()
            ->andThrow(new \Exception('Unauthorized'));

        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
    }

    public function test_logout_successful(): void
    {
        $mockResponse = [
            'message' => 'Successfully logged out'
        ];

        $this->authService
            ->shouldReceive('logout')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
    }

    public function test_logout_handles_exception(): void
    {
        $this->authService
            ->shouldReceive('logout')
            ->once()
            ->andThrow(new \Exception('Logout failed'));

        $response = $this->postJson('/api/logout');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Logout failed'
            ]);
    }
}
