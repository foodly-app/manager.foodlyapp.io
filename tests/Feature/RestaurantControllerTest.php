<?php

namespace Tests\Feature;

use App\Services\RestaurantService;
use Tests\TestCase;

class RestaurantControllerTest extends TestCase
{
    private RestaurantService $restaurantService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->restaurantService = $this->mock(RestaurantService::class);
    }

    public function test_index_returns_restaurants_list(): void
    {
        $mockResponse = [
            'data' => [
                ['id' => 1, 'name' => 'Restaurant 1', 'status' => 'active'],
                ['id' => 2, 'name' => 'Restaurant 2', 'status' => 'active'],
            ]
        ];

        $this->restaurantService
            ->shouldReceive('list')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->getJson('/api/restaurants');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_index_handles_exception(): void
    {
        $this->restaurantService
            ->shouldReceive('list')
            ->once()
            ->andThrow(new \Exception('Service error'));

        $response = $this->getJson('/api/restaurants');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false
            ]);
    }

    public function test_show_returns_restaurant_details(): void
    {
        $restaurantId = 1;
        $mockResponse = [
            'id' => 1,
            'name' => 'Test Restaurant',
            'address' => '123 Main St',
            'status' => 'active'
        ];

        $this->restaurantService
            ->shouldReceive('get')
            ->once()
            ->with($restaurantId)
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $restaurantId = 999;

        $this->restaurantService
            ->shouldReceive('get')
            ->once()
            ->with($restaurantId)
            ->andThrow(new \Exception('Not Found: Restaurant not found'));

        $response = $this->getJson("/api/restaurants/{$restaurantId}");

        $response->assertStatus(404);
    }

    public function test_update_restaurant_successful(): void
    {
        $restaurantId = 1;
        $updateData = [
            'name' => 'Updated Restaurant',
            'status' => 'active'
        ];

        $mockResponse = [
            'id' => 1,
            'name' => 'Updated Restaurant',
            'status' => 'active'
        ];

        $this->restaurantService
            ->shouldReceive('update')
            ->once()
            ->with($restaurantId, $updateData)
            ->andReturn($mockResponse);

        $response = $this->putJson("/api/restaurants/{$restaurantId}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_update_validates_status_field(): void
    {
        $restaurantId = 1;

        $response = $this->putJson("/api/restaurants/{$restaurantId}", [
            'status' => 'invalid-status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_handles_exception(): void
    {
        $restaurantId = 1;
        $updateData = ['name' => 'Updated'];

        $this->restaurantService
            ->shouldReceive('update')
            ->once()
            ->with($restaurantId, $updateData)
            ->andThrow(new \Exception('Update failed'));

        $response = $this->putJson("/api/restaurants/{$restaurantId}", $updateData);

        $response->assertStatus(422);
    }

    public function test_tables_returns_restaurant_tables(): void
    {
        $restaurantId = 1;
        $mockResponse = [
            'data' => [
                ['id' => 1, 'number' => 'T1', 'capacity' => 4],
                ['id' => 2, 'number' => 'T2', 'capacity' => 2],
            ]
        ];

        $this->restaurantService
            ->shouldReceive('getTables')
            ->once()
            ->with($restaurantId)
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}/tables");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_tables_handles_exception(): void
    {
        $restaurantId = 1;

        $this->restaurantService
            ->shouldReceive('getTables')
            ->once()
            ->with($restaurantId)
            ->andThrow(new \Exception('Tables error'));

        $response = $this->getJson("/api/restaurants/{$restaurantId}/tables");

        $response->assertStatus(500);
    }

    public function test_statistics_returns_restaurant_stats(): void
    {
        $restaurantId = 1;
        $mockResponse = [
            'total_reservations' => 150,
            'confirmed_reservations' => 100,
            'cancelled_reservations' => 20
        ];

        $this->restaurantService
            ->shouldReceive('getStatistics')
            ->once()
            ->with($restaurantId, [])
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}/statistics");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_statistics_handles_query_parameters(): void
    {
        $restaurantId = 1;
        $query = ['start_date' => '2025-10-01', 'end_date' => '2025-10-31'];
        $mockResponse = ['total_reservations' => 50];

        $this->restaurantService
            ->shouldReceive('getStatistics')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/restaurants/{$restaurantId}/statistics?" . http_build_query($query));

        $response->assertStatus(200);
    }

    public function test_statistics_handles_exception(): void
    {
        $restaurantId = 1;

        $this->restaurantService
            ->shouldReceive('getStatistics')
            ->once()
            ->andThrow(new \Exception('Statistics error'));

        $response = $this->getJson("/api/restaurants/{$restaurantId}/statistics");

        $response->assertStatus(500);
    }
}
