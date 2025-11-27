<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class DashboardService
{
    protected string $baseUrl;

    protected ?string $token = null;

    public function __construct(
        private readonly AuthService $authService
    ) {
        $this->baseUrl = config('app.partner_api_url', env('PARTNER_API_URL', 'https://api.foodlyapp.ge/api'));
    }

    /**
     * Get dashboard data
     *
     * @param  string  $token
     * @return array
     *
     * @throws Exception
     */
    public function getDashboard(string $token): array
    {
        try {
            $this->token = $token;

            $response = Http::withToken($this->token)->get("{$this->baseUrl}/partner/dashboard");

            if (! $response->successful()) {
                throw new Exception("Get dashboard failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Dashboard error: '.$e->getMessage());
        }
    }

    /**
     * Get dashboard KPIs
     *
     * @param  string  $token
     * @return array
     *
     * @throws Exception
     */
    public function getKPIs(string $token): array
    {
        try {
            $data = $this->getDashboard($token);

            if (isset($data['data']['dashboard']['kpis'])) {
                return $data['data']['dashboard']['kpis'];
            }

            return [];
        } catch (Exception $e) {
            throw new Exception('KPIs error: '.$e->getMessage());
        }
    }

    /**
     * Get restaurant info from dashboard
     *
     * @param  string  $token
     * @return array
     *
     * @throws Exception
     */
    public function getRestaurantInfo(string $token): array
    {
        try {
            $data = $this->getDashboard($token);

            if (isset($data['data']['restaurant'])) {
                return $data['data']['restaurant'];
            }

            return [];
        } catch (Exception $e) {
            throw new Exception('Restaurant info error: '.$e->getMessage());
        }
    }
}
