<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpClient
{
    private string $baseUrl;
    
    /**
     * @param TokenService $tokenService
     */
    public function __construct(
        private readonly TokenService $tokenService
    ) {
        $this->baseUrl = config('services.partner.url');
    }

    /**
     * Make a GET request to the API
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     * @throws Exception
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('get', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request to the API
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('post', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request to the API
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('put', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request to the API
     *
     * @param string $endpoint
     * @return array
     * @throws Exception
     */
    public function delete(string $endpoint): array
    {
        return $this->request('delete', $endpoint);
    }

    /**
     * Make an HTTP request to the API
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array
     * @throws Exception
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->buildRequest()
                ->$method($this->buildUrl($endpoint), $options['json'] ?? $options['query'] ?? []);

            return $this->handleResponse($response);
        } catch (Exception $e) {
            Log::error("API request failed: {$method} {$endpoint}", [
                'error' => $e->getMessage(),
                'options' => $options
            ]);
            throw $e;
        }
    }

    /**
     * Build the HTTP client request
     *
     * @return PendingRequest
     */
    private function buildRequest(): PendingRequest
    {
        return Http::withToken($this->tokenService->getToken())
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->timeout(config('services.partner.timeout', 30));
    }

    /**
     * Build the full URL for the API request
     *
     * @param string $endpoint
     * @return string
     */
    private function buildUrl(string $endpoint): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Handle the API response
     *
     * @param Response $response
     * @return array
     * @throws Exception
     */
    private function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        $this->handleError($response);
        return []; // ეს ხაზი არასდროს არ შესრულდება, რადგან handleError ყოველთვის exception-ს ისვრის
    }

    /**
     * Handle API error responses
     *
     * @param Response $response
     * @throws Exception
     */
    private function handleError(Response $response): void
    {
        $statusCode = $response->status();
        $body = $response->json();

        $message = $body['message'] ?? "HTTP Error: {$statusCode}";

        Log::error('API Error Response', [
            'status' => $statusCode,
            'body' => $body
        ]);

        throw match ($statusCode) {
            401 => new Exception('Unauthorized: ' . $message),
            403 => new Exception('Forbidden: ' . $message),
            404 => new Exception('Not Found: ' . $message),
            422 => new Exception('Validation Error: ' . $message),
            default => new Exception("API Error ({$statusCode}): " . $message),
        };
    }
}