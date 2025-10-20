<?php

namespace App\Services;

use Exception;

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
        return $this->client->post('/partner/login', $credentials);
    }

    /**
     * Get authenticated user information
     *
     * @return array
     * @throws Exception
     */
    public function me(): array
    {
        return $this->client->get('/partner/me');
    }

    /**
     * Logout user and invalidate token
     *
     * @return array
     * @throws Exception
     */
    public function logout(): array
    {
        return $this->client->post('/partner/logout');
    }
}