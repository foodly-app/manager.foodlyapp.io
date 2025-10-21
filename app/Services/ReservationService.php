<?php

namespace App\Services;

use Exception;

class ReservationService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get list of all reservations
     *
     * @param int|null $organizationId
     * @param int|null $restaurantId
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function list(?int $organizationId = null, ?int $restaurantId = null, array $query = []): array
    {
        if ($organizationId && $restaurantId) {
            // Call Partner API endpoint
            return $this->client->get("/api/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations", $query);
        }
        
        // Fallback to general reservations endpoint
        return $this->client->get('/api/partner/reservations', $query);
    }

    /**
     * Get calendar view of reservations
     *
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function calendar(array $query = []): array
    {
        return $this->client->get('/partner/reservations/calendar', $query);
    }

    /**
     * Get reservation by ID
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function get(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}");
    }

    /**
     * Create reservation
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function create(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->post("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations", $data);
    }

    /**
     * Update reservation details
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
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}", $data);
    }

    /**
     * Update reservation status
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
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/status", $data);
    }

    /**
     * Confirm reservation
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function confirm(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/confirm");
    }

    /**
     * Cancel reservation
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function cancel(int $organizationId, int $restaurantId, int $id, array $data = []): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/cancel", $data);
    }

    /**
     * Mark reservation as paid
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function markAsPaid(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/paid");
    }

    /**
     * Complete reservation
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function complete(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/complete");
    }

    /**
     * Mark reservation as no-show
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function noShow(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/no-show");
    }

    /**
     * Assign table to reservation
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function assignTable(int $organizationId, int $restaurantId, int $id, array $data): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/assign-table", $data);
    }

    /**
     * Get reservation statistics
     *
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function statistics(array $query = []): array
    {
        return $this->client->get('/partner/reservations/statistics', $query);
    }

    /**
     * Search reservations
     *
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function search(array $query = []): array
    {
        return $this->client->get('/partner/reservations/search', $query);
    }

    /**
     * Add note to reservation
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function addNote(int $organizationId, int $restaurantId, int $id, array $data): array
    {
        return $this->client->post("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}/notes", $data);
    }

    /**
     * Delete reservation
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function delete(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->delete("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/reservations/{$id}");
    }
}