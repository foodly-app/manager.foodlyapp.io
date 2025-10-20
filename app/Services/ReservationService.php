<?php

namespace App\Services;

use Exception;

class ReservationService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get list of reservations for a restaurant
     *
     * @param int $restaurantId
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function list(int $restaurantId, array $query = []): array
    {
        return $this->client->get("/partner/restaurants/{$restaurantId}/reservations", $query);
    }

    /**
     * Get reservation by ID
     *
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get(int $restaurantId, int $id): array
    {
        return $this->client->get("/partner/restaurants/{$restaurantId}/reservations/{$id}");
    }

    /**
     * Update reservation status
     *
     * @param int $restaurantId
     * @param int $id
     * @param string $status
     * @return array
     * @throws Exception
     */
    public function updateStatus(int $restaurantId, int $id, string $status): array
    {
        return $this->client->put("/partner/restaurants/{$restaurantId}/reservations/{$id}/status", [
            'status' => $status
        ]);
    }

    /**
     * Add notes to reservation
     *
     * @param int $restaurantId
     * @param int $id
     * @param string $notes
     * @return array
     * @throws Exception
     */
    public function addNotes(int $restaurantId, int $id, string $notes): array
    {
        return $this->client->post("/partner/restaurants/{$restaurantId}/reservations/{$id}/notes", [
            'notes' => $notes
        ]);
    }

    /**
     * Get reservation statistics for a restaurant
     *
     * @param int $restaurantId
     * @param array $query Filter parameters (e.g. date range)
     * @return array
     * @throws Exception
     */
    public function getStatistics(int $restaurantId, array $query = []): array
    {
        return $this->client->get("/partner/restaurants/{$restaurantId}/reservations/statistics", $query);
    }

    /**
     * Get today's reservations
     *
     * @param int $restaurantId
     * @return array
     * @throws Exception
     */
    public function getToday(int $restaurantId): array
    {
        return $this->client->get("/partner/restaurants/{$restaurantId}/reservations/today");
    }

    /**
     * Get upcoming reservations
     *
     * @param int $restaurantId
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getUpcoming(int $restaurantId, array $query = []): array
    {
        return $this->client->get("/partner/restaurants/{$restaurantId}/reservations/upcoming", $query);
    }

    /**
     * Cancel a reservation
     *
     * @param int $restaurantId
     * @param int $id
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function cancel(int $restaurantId, int $id, string $reason): array
    {
        return $this->client->post("/partner/restaurants/{$restaurantId}/reservations/{$id}/cancel", [
            'reason' => $reason
        ]);
    }
}