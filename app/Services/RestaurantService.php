<?php

namespace App\Services;

use Exception;

class RestaurantService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get list of all user's restaurants
     *
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function list(array $query = []): array
    {
        return $this->client->get('/api/partner/restaurants', $query);
    }

    /**
     * Get restaurant by ID
     *
     * @param int $organizationId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get(int $organizationId, int $id): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$id}");
    }

    /**
     * Create restaurant
     *
     * @param int $organizationId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function create(int $organizationId, array $data): array
    {
        return $this->client->post("/api/partner/organizations/{$organizationId}/restaurants", $data);
    }

    /**
     * Update restaurant details
     *
     * @param int $organizationId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function update(int $organizationId, int $id, array $data): array
    {
        return $this->client->put("/api/partner/organizations/{$organizationId}/restaurants/{$id}", $data);
    }

    /**
     * Upload restaurant images
     *
     * @param int $organizationId
     * @param int $id
     * @param array $images
     * @return array
     * @throws Exception
     */
    public function uploadImages(int $organizationId, int $id, array $images): array
    {
        return $this->client->post("/api/partner/organizations/{$organizationId}/restaurants/{$id}/images", $images);
    }

    /**
     * Delete restaurant image
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $imageId
     * @return array
     * @throws Exception
     */
    public function deleteImage(int $organizationId, int $restaurantId, int $imageId): array
    {
        return $this->client->delete("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/images/{$imageId}");
    }

    /**
     * Update restaurant status
     *
     * @param int $organizationId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateStatus(int $organizationId, int $id, array $data): array
    {
        return $this->client->put("/api/partner/organizations/{$organizationId}/restaurants/{$id}/status", $data);
    }

    /**
     * Get restaurant settings
     *
     * @param int $organizationId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getSettings(int $organizationId, int $id): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$id}/settings");
    }

    /**
     * Update restaurant settings
     *
     * @param int $organizationId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateSettings(int $organizationId, int $id, array $data): array
    {
        return $this->client->put("/api/partner/organizations/{$organizationId}/restaurants/{$id}/settings", $data);
    }

    /**
     * Get restaurant's reservations
     *
     * @param int $organizationId
     * @param int $id
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getReservations(int $organizationId, int $id, array $query = []): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$id}/reservations", $query);
    }

    /**
     * Get restaurant's tables
     *
     * @param int $organizationId
     * @param int $id
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getTables(int $organizationId, int $id, array $query = []): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$id}/tables", $query);
    }

    /**
     * Get restaurant's places
     *
     * @param int $organizationId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getPlaces(int $organizationId, int $id): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$id}/places");
    }

    /**
     * Get restaurant's statistics
     *
     * @param int $organizationId
     * @param int $id
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getStatistics(int $organizationId, int $id, array $query = []): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$id}/statistics", $query);
    }

    /**
     * Get restaurant dashboard
     *
     * @param int $organizationId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getDashboard(int $organizationId, int $id): array
    {
        return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$id}/dashboard");
    }
}