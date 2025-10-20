<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TokenService
{
    /**
     * @param TokenStorage $storage
     */
    public function __construct(
        private readonly TokenStorage $storage
    ) {}

    /**
     * Get a valid token, either from storage or by generating a new one
     *
     * @return string
     * @throws Exception
     */
    public function getToken(): string
    {
        $tokenData = $this->storage->get();

        if ($this->isValidToken($tokenData)) {
            return $tokenData['token'];
        }

        return $this->generateNewToken();
    }

    /**
     * Generate a new token by authenticating with the partner API
     *
     * @return string
     * @throws Exception
     */
    private function generateNewToken(): string
    {
        try {
            $response = Http::post(config('partner.api.url') . '/login', [
                'email' => config('partner.api.email'),
                'password' => config('partner.api.password')
            ]);

            if ($response->successful()) {
                $token = $response->json('token');
                $this->storage->save($token);
                return $token;
            }

            throw new Exception('Failed to generate token: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Token generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if the stored token data is valid
     *
     * @param array|null $tokenData
     * @return bool
     */
    private function isValidToken(?array $tokenData): bool
    {
        if (!$tokenData) {
            return false;
        }

        return Carbon::createFromTimestamp($tokenData['expires_at'])->isFuture();
    }

    /**
     * Clear the stored token
     *
     * @return void
     */
    public function clearToken(): void
    {
        $this->storage->clear();
    }
}
