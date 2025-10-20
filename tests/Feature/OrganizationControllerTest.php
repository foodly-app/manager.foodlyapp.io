<?php

use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organizationService = $this->mock(OrganizationService::class);
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('index returns organizations list', function () {
    $mockResponse = [
        'data' => [
            ['id' => 1, 'name' => 'Organization 1'],
            ['id' => 2, 'name' => 'Organization 2'],
        ]
    ];

    $this->organizationService
        ->shouldReceive('list')
        ->once()
        ->andReturn($mockResponse);

    $response = $this->getJson('/api/organizations');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockResponse
        ]);
});

test('show returns organization details', function () {
    $organizationId = 1;
    $mockResponse = [
        'id' => 1,
        'name' => 'Test Organization'
    ];

    $this->organizationService
        ->shouldReceive('get')
        ->once()
        ->with($organizationId)
        ->andReturn($mockResponse);

    $response = $this->getJson("/api/organizations/{$organizationId}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockResponse
        ]);
});

test('dashboard returns organization dashboard data', function () {
    $organizationId = 1;
    $mockResponse = [
        'stats' => ['total_restaurants' => 5],
        'recent_reservations' => []
    ];

    $this->organizationService
        ->shouldReceive('getDashboard')
        ->once()
        ->with($organizationId)
        ->andReturn($mockResponse);

    $response = $this->getJson("/api/organizations/{$organizationId}/dashboard");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockResponse
        ]);
});

