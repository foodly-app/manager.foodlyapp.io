<?php

namespace App\Services;

use Exception;

class OrganizationService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get list of organizations
     *
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function list(array $query = []): array
    {
        return $this->client->get('/partner/organizations', $query);
    }

    /**
     * Get organization by ID
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get(int $id): array
    {
        return $this->client->get("/partner/organizations/{$id}");
    }

    /**
     * Get organization's statistics
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getStatistics(int $id): array
    {
        return $this->client->get("/partner/organizations/{$id}/statistics");
    }

    /**
     * Update organization details
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function update(int $id, array $data): array
    {
        return $this->client->put("/partner/organizations/{$id}", $data);
    }

    /**
     * Get organization's restaurants
     *
     * @param int $id
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getRestaurants(int $id, array $query = []): array
    {
        return $this->client->get("/partner/organizations/{$id}/restaurants", $query);
    }
}