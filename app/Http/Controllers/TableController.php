<?php

namespace App\Http\Controllers;

use App\Services\TableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function __construct(
        private readonly TableService $tableService
    ) {}

    /**
     * Get list of tables
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function index(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $tables = $this->tableService->list($organizationId, $restaurantId, $request->query());

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
     * Get table by ID
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $table = $this->tableService->get($organizationId, $restaurantId, $id);

            return response()->json([
                'success' => true,
                'data' => $table
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create table
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
                'capacity' => ['required', 'integer', 'min:1'],
                'place_id' => ['sometimes', 'integer'],
                'status' => ['sometimes', 'string', 'in:available,reserved,occupied'],
            ]);

            $table = $this->tableService->create($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $table
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update table
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
                'capacity' => ['sometimes', 'integer', 'min:1'],
                'place_id' => ['sometimes', 'integer'],
                'status' => ['sometimes', 'string', 'in:available,reserved,occupied'],
            ]);

            $table = $this->tableService->update($organizationId, $restaurantId, $id, $data);

            return response()->json([
                'success' => true,
                'data' => $table
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update table status
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'status' => ['required', 'string', 'in:available,reserved,occupied'],
            ]);

            $table = $this->tableService->updateStatus($organizationId, $restaurantId, $id, $data);

            return response()->json([
                'success' => true,
                'data' => $table
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete table
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $this->tableService->delete($organizationId, $restaurantId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Table deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update tables
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function bulkUpdate(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $data = $request->validate([
                'tables' => ['required', 'array'],
                'tables.*.id' => ['required', 'integer'],
                'tables.*.name' => ['sometimes', 'string'],
                'tables.*.capacity' => ['sometimes', 'integer'],
                'tables.*.status' => ['sometimes', 'string'],
            ]);

            $tables = $this->tableService->bulkUpdate($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $tables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get table availability
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function availability(Request $request, int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $availability = $this->tableService->getAvailability($organizationId, $restaurantId, $id, $request->query());

            return response()->json([
                'success' => true,
                'data' => $availability
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
