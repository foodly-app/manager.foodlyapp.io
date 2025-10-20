<?php

namespace App\Services;

use Exception;

class BookingService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get booking settings for restaurant
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return array
     * @throws Exception
     */
    public function getSettings(int $organizationId, int $restaurantId): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/settings");
    }

    /**
     * Update booking settings
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateSettings(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/settings", $data);
    }

    /**
     * Get booking time slots
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getTimeSlots(int $organizationId, int $restaurantId, array $query = []): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/time-slots", $query);
    }

    /**
     * Update booking time slots
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateTimeSlots(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/time-slots", $data);
    }

    /**
     * Get booking availability
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function checkAvailability(int $organizationId, int $restaurantId, array $query): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/availability", $query);
    }

    /**
     * Get blocked dates/times
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return array
     * @throws Exception
     */
    public function getBlockedDates(int $organizationId, int $restaurantId): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/blocked-dates");
    }

    /**
     * Block dates/times for booking
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function blockDates(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->post("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/block-dates", $data);
    }

    /**
     * Unblock dates/times
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function unblockDates(int $organizationId, int $restaurantId, int $id): array
    {
        return $this->client->delete("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/booking/blocked-dates/{$id}");
    }
}
