<?php

use App\Models\User;
use App\Services\RestaurantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->restaurantService = $this->mock(RestaurantService::class);
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

test('index returns restaurants for organization', function () {
    $orgId = 1;
    $mockRestaurants = [
        ['id' => 1, 'name' => 'Restaurant 1'],
        ['id' => 2, 'name' => 'Restaurant 2']
    ];

    $this->restaurantService
        ->shouldReceive('list')
        ->once()
        ->with([])
        ->andReturn($mockRestaurants);

    $response = $this->getJson("/api/organizations/{$orgId}/restaurants");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockRestaurants
        ]);
});

test('store creates new restaurant', function () {
    $orgId = 1;
    $data = [
        'name' => 'New Restaurant',
        'address' => '123 Main St',
        'phone' => '555-1234',
    ];

    $mockRestaurant = array_merge(['id' => 1], $data);

    $this->restaurantService
        ->shouldReceive('create')
        ->once()
        ->with($orgId, $data)
        ->andReturn($mockRestaurant);

    $response = $this->postJson("/api/organizations/{$orgId}/restaurants", $data);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'data' => $mockRestaurant
        ]);
});

test('show returns single restaurant', function () {
    $orgId = 1;
    $restaurantId = 1;
    $mockRestaurant = [
        'id' => $restaurantId,
        'name' => 'Restaurant 1',
        'address' => '123 Main St'
    ];

    $this->restaurantService
        ->shouldReceive('get')
        ->once()
        ->with($orgId, $restaurantId)
        ->andReturn($mockRestaurant);

    $response = $this->getJson("/api/organizations/{$orgId}/restaurants/{$restaurantId}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => $mockRestaurant
        ]);
});
