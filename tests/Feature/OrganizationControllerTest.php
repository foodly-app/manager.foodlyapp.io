<?php

namespace Tests\Feature;

use App\Services\OrganizationService;
use Tests\TestCase;

class OrganizationControllerTest extends TestCase
{
    private OrganizationService $organizationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organizationService = $this->mock(OrganizationService::class);
    }

    public function test_index_returns_organizations_list(): void
    {
        $mockResponse = [
            'data' => [
                ['id' => 1, 'name' => 'Organization 1'],
                ['id' => 2, 'name' => 'Organization 2'],
            ],
            'meta' => ['total' => 2]
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
    }

    public function test_index_handles_query_parameters(): void
    {
        $query = ['page' => 1, 'per_page' => 10];
        $mockResponse = ['data' => []];

        $this->organizationService
            ->shouldReceive('list')
            ->once()
            ->andReturn($mockResponse);

        $response = $this->getJson('/api/organizations?' . http_build_query($query));

        $response->assertStatus(200);
    }

    public function test_index_handles_exception(): void
    {
        $this->organizationService
            ->shouldReceive('list')
            ->once()
            ->andThrow(new \Exception('Service error'));

        $response = $this->getJson('/api/organizations');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Service error'
            ]);
    }

    public function test_show_returns_organization_details(): void
    {
        $organizationId = 1;
        $mockResponse = [
            'id' => 1,
            'name' => 'Test Organization',
            'email' => 'org@example.com'
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
    }

    public function test_show_returns_404_when_not_found(): void
    {
        $organizationId = 999;

        $this->organizationService
            ->shouldReceive('get')
            ->once()
            ->with($organizationId)
            ->andThrow(new \Exception('Not Found: Organization not found'));

        $response = $this->getJson("/api/organizations/{$organizationId}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false
            ]);
    }

    public function test_update_organization_successful(): void
    {
        $organizationId = 1;
        $updateData = [
            'name' => 'Updated Organization',
            'email' => 'updated@example.com'
        ];

        $mockResponse = [
            'id' => 1,
            'name' => 'Updated Organization',
            'email' => 'updated@example.com'
        ];

        $this->organizationService
            ->shouldReceive('update')
            ->once()
            ->with($organizationId, $updateData)
            ->andReturn($mockResponse);

        $response = $this->putJson("/api/organizations/{$organizationId}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_update_validates_email_field(): void
    {
        $organizationId = 1;

        $response = $this->putJson("/api/organizations/{$organizationId}", [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_handles_exception(): void
    {
        $organizationId = 1;
        $updateData = ['name' => 'Updated'];

        $this->organizationService
            ->shouldReceive('update')
            ->once()
            ->with($organizationId, $updateData)
            ->andThrow(new \Exception('Update failed'));

        $response = $this->putJson("/api/organizations/{$organizationId}", $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Update failed'
            ]);
    }

    public function test_statistics_returns_organization_stats(): void
    {
        $organizationId = 1;
        $mockResponse = [
            'total_restaurants' => 5,
            'total_reservations' => 120,
            'active_reservations' => 30
        ];

        $this->organizationService
            ->shouldReceive('getStatistics')
            ->once()
            ->with($organizationId)
            ->andReturn($mockResponse);

        $response = $this->getJson("/api/organizations/{$organizationId}/statistics");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $mockResponse
            ]);
    }

    public function test_statistics_handles_exception(): void
    {
        $organizationId = 1;

        $this->organizationService
            ->shouldReceive('getStatistics')
            ->once()
            ->with($organizationId)
            ->andThrow(new \Exception('Statistics error'));

        $response = $this->getJson("/api/organizations/{$organizationId}/statistics");

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Statistics error'
            ]);
    }
}
