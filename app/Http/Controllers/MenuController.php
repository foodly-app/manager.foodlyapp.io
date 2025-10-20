<?php

namespace App\Http\Controllers;

use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menuService
    ) {}

    /**
     * Get menu for restaurant
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function index(int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $menu = $this->menuService->get($organizationId, $restaurantId);

            return response()->json([
                'success' => true,
                'data' => $menu
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menu categories
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function categories(int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $categories = $this->menuService->getCategories($organizationId, $restaurantId);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create menu category
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function storeCategory(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'order' => ['sometimes', 'integer'],
            ]);

            $category = $this->menuService->createCategory($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update menu category
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $categoryId
     * @return JsonResponse
     */
    public function updateCategory(Request $request, int $organizationId, int $restaurantId, int $categoryId): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'order' => ['sometimes', 'integer'],
            ]);

            $category = $this->menuService->updateCategory($organizationId, $restaurantId, $categoryId, $data);

            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete menu category
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $categoryId
     * @return JsonResponse
     */
    public function destroyCategory(int $organizationId, int $restaurantId, int $categoryId): JsonResponse
    {
        try {
            $this->menuService->deleteCategory($organizationId, $restaurantId, $categoryId);

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menu items
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function items(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $items = $this->menuService->getItems($organizationId, $restaurantId, $request->query());

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menu item by ID
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return JsonResponse
     */
    public function showItem(int $organizationId, int $restaurantId, int $itemId): JsonResponse
    {
        try {
            $item = $this->menuService->getItem($organizationId, $restaurantId, $itemId);

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create menu item
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function storeItem(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'price' => ['required', 'numeric', 'min:0'],
                'category_id' => ['required', 'integer'],
                'available' => ['sometimes', 'boolean'],
            ]);

            $item = $this->menuService->createItem($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $item
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update menu item
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return JsonResponse
     */
    public function updateItem(Request $request, int $organizationId, int $restaurantId, int $itemId): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'price' => ['sometimes', 'numeric', 'min:0'],
                'category_id' => ['sometimes', 'integer'],
                'available' => ['sometimes', 'boolean'],
            ]);

            $item = $this->menuService->updateItem($organizationId, $restaurantId, $itemId, $data);

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete menu item
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return JsonResponse
     */
    public function destroyItem(int $organizationId, int $restaurantId, int $itemId): JsonResponse
    {
        try {
            $this->menuService->deleteItem($organizationId, $restaurantId, $itemId);

            return response()->json([
                'success' => true,
                'message' => 'Menu item deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload menu item image
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return JsonResponse
     */
    public function uploadItemImage(Request $request, int $organizationId, int $restaurantId, int $itemId): JsonResponse
    {
        try {
            $request->validate([
                'image' => ['required', 'image', 'max:2048'],
            ]);

            $item = $this->menuService->uploadItemImage($organizationId, $restaurantId, $itemId, $request->all());

            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete menu item image
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $itemId
     * @return JsonResponse
     */
    public function deleteItemImage(int $organizationId, int $restaurantId, int $itemId): JsonResponse
    {
        try {
            $this->menuService->deleteItemImage($organizationId, $restaurantId, $itemId);

            return response()->json([
                'success' => true,
                'message' => 'Menu item image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
