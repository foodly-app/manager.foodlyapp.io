<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * Get dashboard data
     */
    public function index(): JsonResponse
    {
        try {
            $token = $this->getTokenFromRequest();

            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $result = $this->dashboardService->getDashboard($token);

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Dashboard failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get KPIs only
     */
    public function getKPIs(): JsonResponse
    {
        try {
            $token = $this->getTokenFromRequest();

            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $kpis = $this->dashboardService->getKPIs($token);

            return response()->json([
                'success' => true,
                'data' => $kpis,
            ]);
        } catch (Exception $e) {
            Log::error('KPIs failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get restaurant info
     */
    public function getRestaurantInfo(): JsonResponse
    {
        try {
            $token = $this->getTokenFromRequest();

            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            $restaurant = $this->dashboardService->getRestaurantInfo($token);

            return response()->json([
                'success' => true,
                'data' => $restaurant,
            ]);
        } catch (Exception $e) {
            Log::error('Restaurant info failed:', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get token from Authorization header or session
     */
    private function getTokenFromRequest(): ?string
    {
        // Try header first
        $header = request()->header('Authorization');
        if ($header && strpos($header, 'Bearer ') === 0) {
            return substr($header, 7);
        }

        // Try session
        return session('partner_token');
    }
}
