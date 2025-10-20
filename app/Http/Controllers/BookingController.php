<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService
    ) {}

    /**
     * Get booking settings
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function getSettings(int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $settings = $this->bookingService->getSettings($organizationId, $restaurantId);

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking settings
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function updateSettings(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $data = $request->validate([
                'enabled' => ['sometimes', 'boolean'],
                'advance_days' => ['sometimes', 'integer', 'min:1'],
                'max_party_size' => ['sometimes', 'integer', 'min:1'],
                'slot_duration' => ['sometimes', 'integer', 'min:15'],
            ]);

            $settings = $this->bookingService->updateSettings($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get booking time slots
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function getTimeSlots(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $timeSlots = $this->bookingService->getTimeSlots($organizationId, $restaurantId, $request->query());

            return response()->json([
                'success' => true,
                'data' => $timeSlots
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking time slots
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function updateTimeSlots(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $data = $request->validate([
                'slots' => ['required', 'array'],
                'slots.*.day' => ['required', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
                'slots.*.start_time' => ['required', 'string'],
                'slots.*.end_time' => ['required', 'string'],
            ]);

            $timeSlots = $this->bookingService->updateTimeSlots($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $timeSlots
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Check booking availability
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function checkAvailability(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $query = $request->validate([
                'date' => ['required', 'date'],
                'time' => ['required', 'string'],
                'guests' => ['required', 'integer', 'min:1'],
            ]);

            $availability = $this->bookingService->checkAvailability($organizationId, $restaurantId, $query);

            return response()->json([
                'success' => true,
                'data' => $availability
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get blocked dates/times
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function getBlockedDates(int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $blockedDates = $this->bookingService->getBlockedDates($organizationId, $restaurantId);

            return response()->json([
                'success' => true,
                'data' => $blockedDates
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Block dates/times for booking
     *
     * @param Request $request
     * @param int $organizationId
     * @param int $restaurantId
     * @return JsonResponse
     */
    public function blockDates(Request $request, int $organizationId, int $restaurantId): JsonResponse
    {
        try {
            $data = $request->validate([
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date'],
                'reason' => ['sometimes', 'string'],
            ]);

            $blocked = $this->bookingService->blockDates($organizationId, $restaurantId, $data);

            return response()->json([
                'success' => true,
                'data' => $blocked
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Unblock dates/times
     *
     * @param int $organizationId
     * @param int $restaurantId
     * @param int $id
     * @return JsonResponse
     */
    public function unblockDates(int $organizationId, int $restaurantId, int $id): JsonResponse
    {
        try {
            $this->bookingService->unblockDates($organizationId, $restaurantId, $id);

            return response()->json([
                'success' => true,
                'message' => 'Dates unblocked successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
