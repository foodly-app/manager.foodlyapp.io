<?php

namespace App\Services;

use Exception;

class TableService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get list of tables for a restaurant
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function list(int $organizationId, int $restaurantId, array $query = []): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables", $query);
    }

    /**
     * Get table by ID
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables/{$id}");
    }

    /**
     * Create table
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function create(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->post("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables", $data);
    }

    /**
     * Update table details
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function update(int $organizationId, int $restaurantId, int $id, array $data): array
    {
        return $this->client->put("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables/{$id}", $data);
    }

    /**
     * Update table status
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateStatus(int $organizationId, int $restaurantId, int $id, array $data): array
    {
        return $this->client->put("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables/{$id}/status", $data);
    }

    /**
     * Delete table
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function delete(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->delete("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables/{$id}");
    }

    /**
     * Bulk update tables
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function bulkUpdate(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->post("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables/bulk-update", $data);
    }

    /**
     * Get table availability
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getAvailability(int $organizationId, int $restaurantId, int $id, array $query = []): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/tables/{$id}/availability", $query);
    }
}
