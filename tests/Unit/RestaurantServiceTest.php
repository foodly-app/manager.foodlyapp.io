<?php

namespace Tests\Unit;

use App\Services\HttpClient;
use App\Services\RestaurantService;
use Mockery;
use Tests\TestCase;

class RestaurantServiceTest extends TestCase
{
    private RestaurantService $restaurantService;
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->restaurantService = new RestaurantService($this->httpClient);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_restaurants(): void
    {
        $query = ['page' => 1, 'status' => 'active'];
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'name' => 'Restaurant 1'],
                ['id' => 2, 'name' => 'Restaurant 2'],
            ]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with('/partner/restaurants', $query)
            ->andReturn($expectedResponse);

        $result = $this->restaurantService->list($query);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_restaurant_by_id(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $expectedResponse = [
            'id' => 1,
            'name' => 'Test Restaurant',
            'address' => '123 Main St'
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}")
            ->andReturn($expectedResponse);

        $result = $this->restaurantService->get($organizationId, $restaurantId);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals($restaurantId, $result['id']);
    }

    public function test_update_restaurant(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $updateData = [
            'name' => 'Updated Restaurant',
            'status' => 'active'
        ];
        $expectedResponse = [
            'id' => 1,
            'name' => 'Updated Restaurant',
            'status' => 'active'
        ];

        $this->httpClient
            ->shouldReceive('put')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}", $updateData)
            ->andReturn($expectedResponse);

        $result = $this->restaurantService->update($organizationId, $restaurantId, $updateData);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_restaurant_reservations(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $query = ['date' => '2025-10-20'];
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'customer_name' => 'John Doe'],
                ['id' => 2, 'customer_name' => 'Jane Smith'],
            ]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations", $query)
            ->andReturn($expectedResponse);

        $result = $this->restaurantService->getReservations($organizationId, $restaurantId, $query);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_restaurant_tables(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'number' => 'T1', 'capacity' => 4],
                ['id' => 2, 'number' => 'T2', 'capacity' => 2],
            ]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables", [])
            ->andReturn($expectedResponse);

        $result = $this->restaurantService->getTables($organizationId, $restaurantId);

        $this->assertEquals($expectedResponse, $result);
    }

    public function test_get_restaurant_statistics(): void
    {
        $organizationId = 1;
        $restaurantId = 1;
        $query = ['start_date' => '2025-10-01', 'end_date' => '2025-10-31'];
        $expectedResponse = [
            'total_reservations' => 150,
            'confirmed_reservations' => 100,
            'cancelled_reservations' => 20
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/statistics", $query)
            ->andReturn($expectedResponse);

        $result = $this->restaurantService->getStatistics($organizationId, $restaurantId, $query);

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('total_reservations', $result);
    }

    public function test_throws_exception_when_restaurant_not_found(): void
    {
        $organizationId = 1;
        $restaurantId = 999;

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}")
            ->andThrow(new \Exception('Not Found: Restaurant not found'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not Found');

        $this->restaurantService->get($organizationId, $restaurantId);
    }
}
