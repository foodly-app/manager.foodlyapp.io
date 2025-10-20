<?php

namespace Tests\Unit;

use App\Services\HttpClient;
use App\Services\OrganizationService;
use Mockery;
use Tests\TestCase;

class OrganizationServiceTest extends TestCase
{
    private OrganizationService $organizationService;
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->organizationService = new OrganizationService($this->httpClient);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_list_organizations(): void
    {
        $query = ['page' => 1, 'per_page' => 10];
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'name' => 'Organization 1'],
                ['id' => 2, 'name' => 'Organization 2'],
            ],
            'meta' => ['total' => 2]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with('/partner/organizations', $query)
            ->andReturn($expectedResponse);

        $result = $this->organizationService->list($query);

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_get_organization_by_id(): void
    {
        $organizationId = 1;
        $expectedResponse = [
            'id' => 1,
            'name' => 'Test Organization',
            'email' => 'org@example.com'
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}")
            ->andReturn($expectedResponse);

        $result = $this->organizationService->get($organizationId);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals($organizationId, $result['id']);
    }

    public function test_update_organization(): void
    {
        $organizationId = 1;
        $updateData = [
            'name' => 'Updated Organization',
            'email' => 'updated@example.com'
        ];
        $expectedResponse = [
            'id' => 1,
            'name' => 'Updated Organization',
            'email' => 'updated@example.com'
        ];

        $this->httpClient
            ->shouldReceive('put')
            ->once()
            ->with("/partner/organizations/{$organizationId}", $updateData)
            ->andReturn($expectedResponse);

        $result = $this->organizationService->update($organizationId, $updateData);

        $this->assertEquals($expectedResponse, $result);
        $this->assertEquals('Updated Organization', $result['name']);
    }

    public function test_get_organization_statistics(): void
    {
        $organizationId = 1;
        $expectedResponse = [
            'total_restaurants' => 5,
            'total_reservations' => 120,
            'active_reservations' => 30
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/statistics")
            ->andReturn($expectedResponse);

        $result = $this->organizationService->getStatistics($organizationId);

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('total_restaurants', $result);
    }

    public function test_get_organization_restaurants(): void
    {
        $organizationId = 1;
        $query = ['status' => 'active'];
        $expectedResponse = [
            'data' => [
                ['id' => 1, 'name' => 'Restaurant 1'],
                ['id' => 2, 'name' => 'Restaurant 2'],
            ]
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}/restaurants", $query)
            ->andReturn($expectedResponse);

        $result = $this->organizationService->getRestaurants($organizationId, $query);

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_throws_exception_when_organization_not_found(): void
    {
        $organizationId = 999;

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with("/partner/organizations/{$organizationId}")
            ->andThrow(new \Exception('Not Found: Organization not found'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not Found');

        $this->organizationService->get($organizationId);
    }
}
