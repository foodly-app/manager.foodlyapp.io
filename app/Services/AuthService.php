<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Http;

class AuthService
{
    protected string $baseUrl;

    protected ?string $token = null;

    protected ?array $user = null;

    protected ?array $context = null;

    public function __construct()
    {
        $this->baseUrl = config('app.partner_api_url', env('PARTNER_API_URL', 'https://api.foodlyapp.ge/api'));
    }

    /**
     * Login with email and password
     *
     * @param  array  $credentials  ['email' => '', 'password' => '']
     *
     * @throws Exception
     */
    public function login(array $credentials): array
    {
<<<<<<< HEAD
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
=======
        try {
            $response = Http::post("{$this->baseUrl}/partner/login", [
                'email' => $credentials['email'] ?? null,
                'password' => $credentials['password'] ?? null,
            ]);

            if (! $response->successful()) {
                throw new Exception("Login failed: {$response->json('message', 'Unknown error')}");
            }

            $data = $response->json();

            if (isset($data['data']['token'])) {
                $this->token = $data['data']['token'];
                $this->user = $data['data']['user'] ?? null;
                $this->context = $data['data']['context'] ?? null;
            }

            return $data['data'] ?? [];
        } catch (Exception $e) {
            throw new Exception('Authentication error: '.$e->getMessage());
        }
>>>>>>> 6eac07bd639cc909318602c3773296acf4fccf92
    }

    /**
     * Get bootstrap data (initial state after login)
     *
     * @throws Exception
     */
    public function bootstrap(): array
    {
        try {
            $response = Http::withToken($this->token)->get("{$this->baseUrl}/partner/bootstrap");

            if (! $response->successful()) {
                throw new Exception("Bootstrap failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json('data', []);
        } catch (Exception $e) {
            throw new Exception('Bootstrap error: '.$e->getMessage());
        }
    }

    /**
     * Switch context (organization or restaurant)
     *
     * @throws Exception
     */
    public function switchContext(?int $organizationId = null, ?int $restaurantId = null): array
    {
<<<<<<< HEAD
        return $this->client->get('/api/partner/dashboard');
=======
        try {
            $payload = [];
            if ($organizationId !== null) {
                $payload['organization_id'] = $organizationId;
            }
            if ($restaurantId !== null) {
                $payload['restaurant_id'] = $restaurantId;
            }

            $response = Http::withToken($this->token)->patch("{$this->baseUrl}/partner/context", $payload);

            if (! $response->successful()) {
                throw new Exception("Context switch failed: {$response->json('message', 'Unknown error')}");
            }

            $data = $response->json('data', []);
            $this->context = $data['context'] ?? $this->context;

            return $data;
        } catch (Exception $e) {
            throw new Exception('Context switch error: '.$e->getMessage());
        }
>>>>>>> 6eac07bd639cc909318602c3773296acf4fccf92
    }

    /**
     * Logout
     *
     * @throws Exception
     */
    public function logout(): array
    {
        try {
            $response = Http::withToken($this->token)->post("{$this->baseUrl}/partner/logout");

            if (! $response->successful()) {
                throw new Exception("Logout failed: {$response->json('message', 'Unknown error')}");
            }

            $this->token = null;
            $this->user = null;
            $this->context = null;

            return $response->json('data', []);
        } catch (Exception $e) {
            throw new Exception('Logout error: '.$e->getMessage());
        }
    }

    /**
     * Get current token
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Get current user
     */
    public function getUser(): ?array
    {
        return $this->user;
    }

    /**
     * Get current context
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * Get authenticated user info
     *
     * @throws Exception
     */
    public function me(): array
    {
        try {
            $response = Http::withToken($this->token)->get("{$this->baseUrl}/partner/me");

            if (! $response->successful()) {
                throw new Exception("Me failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Me error: '.$e->getMessage());
        }
    }

    /**
     * Get initial dashboard data
     *
     * @throws Exception
     */
    public function initialDashboard(): array
    {
        try {
            $response = Http::withToken($this->token)->get("{$this->baseUrl}/partner/initial-dashboard");

            if (! $response->successful()) {
                throw new Exception("Initial dashboard failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Initial dashboard error: '.$e->getMessage());
        }
    }

    /**
     * Get user profile
     *
     * @throws Exception
     */
    public function getProfile(): array
    {
        try {
            $response = Http::withToken($this->token)->get("{$this->baseUrl}/partner/profile");

            if (! $response->successful()) {
                throw new Exception("Get profile failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Get profile error: '.$e->getMessage());
        }
    }

    /**
     * Update user profile
     *
     * @throws Exception
     */
    public function updateProfile(array $data): array
    {
        try {
            $response = Http::withToken($this->token)->patch("{$this->baseUrl}/partner/profile", $data);

            if (! $response->successful()) {
                throw new Exception("Update profile failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Update profile error: '.$e->getMessage());
        }
    }

    /**
     * Upload user avatar
     *
     * @param  mixed  $file
     *
     * @throws Exception
     */
    public function uploadAvatar($file): array
    {
        try {
            $response = Http::withToken($this->token)
                ->attach('avatar', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
                ->post("{$this->baseUrl}/partner/profile/avatar");

            if (! $response->successful()) {
                throw new Exception("Upload avatar failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Upload avatar error: '.$e->getMessage());
        }
    }

    /**
     * Change user password
     *
     * @throws Exception
     */
    public function changePassword(array $data): array
    {
        try {
            $response = Http::withToken($this->token)->post("{$this->baseUrl}/partner/change-password", $data);

            if (! $response->successful()) {
                throw new Exception("Change password failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Change password error: '.$e->getMessage());
        }
    }
<<<<<<< HEAD
=======

    /**
     * Set token manually
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Make authenticated HTTP request
     */
    public function request(string $method, string $endpoint, array $data = []): Response
    {
        $url = "{$this->baseUrl}/{endpoint}";

        return Http::withToken($this->token)->request($method, $url, $data);
    }

    /**
     * Make GET request
     */
    public function get(string $endpoint): Response
    {
        return $this->request('GET', $endpoint);
    }

    /**
     * Make POST request
     */
    public function post(string $endpoint, array $data = []): Response
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Make PATCH request
     */
    public function patch(string $endpoint, array $data = []): Response
    {
        return $this->request('PATCH', $endpoint, $data);
    }

    /**
     * Delete user avatar
     *
     * @throws Exception
     */
    public function deleteAvatar(): array
    {
        try {
            $response = Http::withToken($this->token)->delete("{$this->baseUrl}/partner/profile/avatar");

            if (! $response->successful()) {
                throw new Exception("Delete avatar failed: {$response->json('message', 'Unknown error')}");
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception('Delete avatar error: '.$e->getMessage());
        }
    }

    /**
     * Make DELETE request
     */
    public function delete(string $endpoint, array $data = []): Response
    {
        return $this->request('DELETE', $endpoint, $data);
    }
>>>>>>> 6eac07bd639cc909318602c3773296acf4fccf92
}
