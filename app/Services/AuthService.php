<?php

namespace App\Services;

use Exception;

use Illuminate\Support\Facades\Http;

class AuthService
{
    public function __construct(
        private readonly HttpClient $client
    ) {}

    /**
     * Authenticate user and get token
     *
     * @param array $credentials
     * @return array
     * @throws Exception
     */
    public function login(array $credentials): array
    {
        $baseUrl = config('services.partner.url');
        $url = rtrim($baseUrl, '/') . '/api/partner/login';

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($url, $credentials);

        if ($response->successful()) {
            return $response->json();
        }

        $body = $response->json();
        $message = $body['message'] ?? 'Login failed';

        throw new Exception($message);
    }

    /**
     * Get authenticated user information
     *
     * @return array
     * @throws Exception
     */
    public function me(): array
    {
        return $this->client->get('/api/partner/me');
    }

    /**
     * Get initial dashboard data after login
     *
     * @return array
     * @throws Exception
     */
    public function initialDashboard(): array
    {
        return $this->client->get('/api/partner/dashboard');
    }

    /**
     * Logout user and invalidate token
     *
     * @return array
     * @throws Exception
     */
    public function logout(): array
    {
        return $this->client->post('/api/partner/logout');
    }

    /**
     * Get user profile
     *
     * @return array
     * @throws Exception
     */
    public function getProfile(): array
    {
        return $this->client->get('/api/partner/profile');
    }

    /**
     * Update user profile
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function updateProfile(array $data): array
    {
        return $this->client->put('/api/partner/profile', $data);
    }

    /**
     * Upload user avatar
     *
     * @param mixed $avatar
     * @return array
     * @throws Exception
     */
    public function uploadAvatar($avatar): array
    {
        return $this->client->post('/api/partner/avatar', [
            'avatar' => $avatar
        ]);
    }

    /**
     * Delete user avatar
     *
     * @return array
     * @throws Exception
     */
    public function deleteAvatar(): array
    {
        return $this->client->delete('/api/partner/avatar');
    }

    /**
     * Change user password
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function changePassword(array $data): array
    {
        return $this->client->put('/api/partner/password', $data);
    }
}
