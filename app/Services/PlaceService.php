<?php

namespace App\Services;

use Exception;

class PlaceService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get list of places for a restaurant
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return array
     * @throws Exception
     */
    public function list(int $organizationId, int $restaurantId): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/places");
    }

    /**
     * Get place by ID
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/places/{$id}");
    }

    /**
     * Create place
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function create(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->post("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/places", $data);
    }

    /**
     * Update place details
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
        return $this->client->put("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/places/{$id}", $data);
    }

    /**
     * Delete place
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function delete(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->delete("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/places/{$id}");
    }

    /**
     * Get tables for a place
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getTables(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/places/{$id}/tables");
    }
}
