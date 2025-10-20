<?php

namespace App\Services;

use Exception;

class RestaurantService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get list of restaurants
     *
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function list(array $query = []): array
    {
        return $this->client->get('/partner/restaurants', $query);
    }

    /**
     * Get restaurant by ID
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get(int $id): array
    {
        return $this->client->get("/partner/restaurants/{$id}");
    }

    /**
     * Update restaurant details
     *
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function update(int $id, array $data): array
    {
        return $this->client->put("/partner/restaurants/{$id}", $data);
    }

    /**
     * Get restaurant's reservations
     *
     * @param int $id
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getReservations(int $id, array $query = []): array
    {
        return $this->client->get("/partner/restaurants/{$id}/reservations", $query);
    }

    /**
     * Get restaurant's tables
     *
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getTables(int $id): array
    {
        return $this->client->get("/partner/restaurants/{$id}/tables");
    }

    /**
     * Get restaurant's statistics
     *
     * @param int $id
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getStatistics(int $id, array $query = []): array
    {
        return $this->client->get("/partner/restaurants/{$id}/statistics", $query);
    }
}