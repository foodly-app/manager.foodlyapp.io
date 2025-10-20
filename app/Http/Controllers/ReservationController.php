<?php

namespace App\Http\Controllers;

use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService
    ) {}

    /**
     * Get list of reservations
     *
     * @param Request $request
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function index(Request $request, int $restaurantId): JsonResponse
    {
        try {
            $reservations = $this->reservationService->list($restaurantId, $request->query());

            return response()->json([
                'success' => true,
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's reservations
     *
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function today(int $restaurantId): JsonResponse
    {
        try {
            $reservations = $this->reservationService->getToday($restaurantId);

            return response()->json([
                'success' => true,
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming reservations
     *
     * @param Request $request
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function upcoming(Request $request, int $restaurantId): JsonResponse
    {
        try {
            $reservations = $this->reservationService->getUpcoming($restaurantId, $request->query());

            return response()->json([
                'success' => true,
                'data' => $reservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update reservation status
     *
     * @param Request $request
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $restaurantId, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'status' => ['required', 'string', 'in:confirmed,cancelled,completed,no_show']
            ]);

            $reservation = $this->reservationService->updateStatus($restaurantId, $id, $data['status']);

            return response()->json([
                'success' => true,
                'data' => $reservation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Add notes to reservation
     *
     * @param Request $request
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function addNotes(Request $request, int $restaurantId, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'notes' => ['required', 'string']
            ]);

            $reservation = $this->reservationService->addNotes($restaurantId, $id, $data['notes']);

            return response()->json([
                'success' => true,
                'data' => $reservation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Cancel reservation
     *
     * @param Request $request
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $restaurantId, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'reason' => ['required', 'string']
            ]);

            $reservation = $this->reservationService->cancel($restaurantId, $id, $data['reason']);

            return response()->json([
                'success' => true,
                'data' => $reservation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get reservation statistics
     *
     * @param Request $request
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function statistics(Request $request, int $restaurantId): JsonResponse
    {
        try {
            $statistics = $this->reservationService->getStatistics($restaurantId, $request->query());

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