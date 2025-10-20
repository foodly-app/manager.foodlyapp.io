<?php

namespace App\Http\Controllers;

use App\Services\RestaurantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(
        private readonly RestaurantService $restaurantService
    ) {}

    /**
     * Get list of restaurants
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $restaurants = $this->restaurantService->list($request->query());

            return response()->json([
                'success' => true,
                'data' => $restaurants
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurant by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $restaurant = $this->restaurantService->get($id);

            return response()->json([
                'success' => true,
                'data' => $restaurant
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update restaurant
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['sometimes', 'string'],
                'phone' => ['sometimes', 'string'],
                'address' => ['sometimes', 'string'],
                'status' => ['sometimes', 'string', 'in:active,inactive'],
            ]);

            $restaurant = $this->restaurantService->update($id, $data);

            return response()->json([
                'success' => true,
                'data' => $restaurant
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get restaurant tables
     *
     * @param int $id
     * @return JsonResponse
     */
    public function tables(int $id): JsonResponse
    {
        try {
            $tables = $this->restaurantService->getTables($id);

            return response()->json([
                'success' => true,
                'data' => $tables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurant statistics
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function statistics(Request $request, int $id): JsonResponse
    {
        try {
            $statistics = $this->restaurantService->getStatistics($id, $request->query());

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}