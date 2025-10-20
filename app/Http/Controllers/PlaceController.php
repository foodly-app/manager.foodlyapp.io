<?php

namespace App\Http\Controllers;

use App\Services\PlaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function __construct(
        private readonly PlaceService $placeService
    ) {}

    /**
     * Get list of places
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function index(int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $places = $this->placeService->list($organizationId, $restaurantId);

            return response()->json([
                'success' => true,
                'data' => $places
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get place by ID
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $place = $this->placeService->get($organizationId, $restaurantId, $id);

            return response()->json([
                'success' => true,
                'data' => $place
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create place
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function store(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'capacity' => ['sometimes', 'integer', 'min:1'],
            ]);

            $place = $this->placeService->create($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $place
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update place
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['sometimes', 'string'],
                'capacity' => ['sometimes', 'integer', 'min:1'],
            ]);

            $place = $this->placeService->update($organizationId, $restaurantId, $id, $data);

            return response()->json([
                'success' => true,
                'data' => $place
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete place
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $this->placeService->delete($organizationId, $restaurantId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Place deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tables for a place
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function tables(int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $tables = $this->placeService->getTables($organizationId, $restaurantId, $id);

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
}
