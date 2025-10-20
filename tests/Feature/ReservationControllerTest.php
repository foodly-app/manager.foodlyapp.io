<?php

use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->reservationService = $this->mock(ReservationService::class);
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('index returns reservations list', function () {
    $mockResponse = [
        'data' => [
            ['id' => 1, 'customer_name' => 'John Doe'],
            ['id' => 2, 'customer_name' => 'Jane Smith'],
        ]
    ];

    $this->reservationService
        ->shouldReceive('list')
        ->once()
        ->andReturn($mockResponse);

    $response = $this->getJson('/api/reservations');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockResponse
        ]);
});

test('show returns reservation details', function () {
    $organizationId = 1;
    $restaurantId = 1;
    $reservationId = 1;
    
    $mockResponse = [
        'id' => 1,
        'customer_name' => 'John Doe',
        'status' => 'confirmed'
    ];

    $this->reservationService
        ->shouldReceive('get')
        ->once()
        ->with($organizationId, $restaurantId, $reservationId)
        ->andReturn($mockResponse);

    $response = $this->getJson("/api/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$reservationId}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockResponse
        ]);
});

test('store creates new reservation', function () {
    $organizationId = 1;
    $restaurantId = 1;
    $reservationData = [
        'customer_name' => 'John Doe',
        'customer_phone' => '+995555123456',
        'date' => '2025-10-25',
        'time' => '19:00',
        'guests' => 4
    ];

    $mockResponse = array_merge(['id' => 1, 'status' => 'pending'], $reservationData);

    $this->reservationService
        ->shouldReceive('create')
        ->once()
        ->with($organizationId, $restaurantId, $reservationData)
        ->andReturn($mockResponse);

    $response = $this->postJson("/api/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations", $reservationData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => $mockResponse
        ]);
});

