<?php

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->authService = $this->mock(AuthService::class);
});

test('login successful', function () {
    $credentials = [
        'email' => 'test@example.com',
        'password' => 'password123'
    ];

    $mockResponse = [
        'token' => 'auth-token-123',
        'user' => ['id' => 1, 'email' => 'test@example.com']
    ];

    $this->authService
        ->shouldReceive('login')
        ->once()
        ->with($credentials)
        ->andReturn($mockResponse);

    $response = $this->postJson('/api/auth/login', $credentials);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockResponse
        ]);
});

test('login validates required fields', function () {
    // Note: AuthController catches ValidationException and returns 401 instead of 422
    $response = $this->postJson('/api/auth/login', []);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false
        ]);
});

test('get profile returns user data', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $mockProfile = [
        'id' => 1,
        'email' => 'test@example.com',
        'name' => 'Test User'
    ];

    $this->authService
        ->shouldReceive('getProfile')
        ->once()
        ->andReturn($mockProfile);

    $response = $this->getJson('/api/auth/profile');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockProfile
        ]);
});

