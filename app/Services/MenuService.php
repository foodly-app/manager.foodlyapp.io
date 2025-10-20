<?php

namespace App\Services;

use Exception;

class MenuService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Get menu for restaurant
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return array
     * @throws Exception
     */
    public function get(int $organizationId, int $restaurantId): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu");
    }

    /**
     * Get menu categories
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return array
     * @throws Exception
     */
    public function getCategories(int $organizationId, int $restaurantId): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/categories");
    }

    /**
     * Create menu category
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createCategory(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->post("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/categories", $data);
    }

    /**
     * Update menu category
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $categoryId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateCategory(int $organizationId, int $restaurantId, int $categoryId, array $data): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/categories/{$categoryId}", $data);
    }

    /**
     * Delete menu category
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $categoryId
     * @return array
     * @throws Exception
     */
    public function deleteCategory(int $organizationId, int $restaurantId, int $categoryId): array
    {
        return $this->client->delete("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/categories/{$categoryId}");
    }

    /**
     * Get menu items
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function getItems(int $organizationId, int $restaurantId, array $query = []): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/items", $query);
    }

    /**
     * Get menu item by ID
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return array
     * @throws Exception
     */
    public function getItem(int $organizationId, int $restaurantId, int $itemId): array
    {
        return $this->client->get("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/items/{$itemId}");
    }

    /**
     * Create menu item
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function createItem(int $organizationId, int $restaurantId, array $data): array
    {
        return $this->client->post("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/items", $data);
    }

    /**
     * Update menu item
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateItem(int $organizationId, int $restaurantId, int $itemId, array $data): array
    {
        return $this->client->put("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/items/{$itemId}", $data);
    }

    /**
     * Delete menu item
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return array
     * @throws Exception
     */
    public function deleteItem(int $organizationId, int $restaurantId, int $itemId): array
    {
        return $this->client->delete("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/items/{$itemId}");
    }

    /**
     * Upload menu item image
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function uploadItemImage(int $organizationId, int $restaurantId, int $itemId, array $data): array
    {
        return $this->client->post("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/items/{$itemId}/image", $data);
    }

    /**
     * Delete menu item image
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return array
     * @throws Exception
     */
    public function deleteItemImage(int $organizationId, int $restaurantId, int $itemId): array
    {
        return $this->client->delete("/partner/organizations/{$organizationId}/restaurants/{$restaurantId}/menu/items/{$itemId}/image");
    }
}
