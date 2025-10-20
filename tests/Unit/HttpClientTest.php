<?php

namespace Tests\Unit;

use App\Services\HttpClient;
use App\Services\TokenService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpClientTest extends TestCase
{
    private HttpClient $httpClient;
    private TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenService = $this->mock(TokenService::class);
        $this->httpClient = new HttpClient($this->tokenService);
        
        config(['services.partner.url' => 'https://api.test.com']);
        config(['services.partner.timeout' => 30]);
    }

    public function test_get_request_successful(): void
    {
        $expectedData = ['id' => 1, 'name' => 'Test'];
        $token = 'test-token-123';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response($expectedData, 200)
        ]);

        $result = $this->httpClient->get('/test-endpoint', ['key' => 'value']);

        $this->assertEquals($expectedData, $result);
        Http::assertSent(function ($request) use ($token) {
            return str_contains($request->url(), 'test-endpoint') &&
                   $request->hasHeader('Authorization', 'Bearer ' . $token) &&
                   $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_post_request_successful(): void
    {
        $requestData = ['name' => 'Test'];
        $responseData = ['id' => 1, 'name' => 'Test'];
        $token = 'test-token-456';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response($responseData, 200)
        ]);

        $result = $this->httpClient->post('/test-endpoint', $requestData);

        $this->assertEquals($responseData, $result);
    }

    public function test_put_request_successful(): void
    {
        $requestData = ['name' => 'Updated'];
        $responseData = ['id' => 1, 'name' => 'Updated'];
        $token = 'test-token-789';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response($responseData, 200)
        ]);

        $result = $this->httpClient->put('/test-endpoint/1', $requestData);

        $this->assertEquals($responseData, $result);
    }

    public function test_delete_request_successful(): void
    {
        $responseData = ['message' => 'Deleted'];
        $token = 'test-token-abc';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response($responseData, 200)
        ]);

        $result = $this->httpClient->delete('/test-endpoint/1');

        $this->assertEquals($responseData, $result);
    }

    public function test_handles_401_unauthorized_error(): void
    {
        $token = 'invalid-token';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response([
                'message' => 'Unauthorized access'
            ], 401)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');

        $this->httpClient->get('/test-endpoint');
    }

    public function test_handles_404_not_found_error(): void
    {
        $token = 'test-token';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response([
                'message' => 'Resource not found'
            ], 404)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not Found');

        $this->httpClient->get('/test-endpoint');
    }

    public function test_handles_422_validation_error(): void
    {
        $token = 'test-token';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response([
                'message' => 'Validation failed'
            ], 422)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Validation Error');

        $this->httpClient->post('/test-endpoint', []);
    }

    public function test_handles_500_server_error(): void
    {
        $token = 'test-token';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response([
                'message' => 'Internal server error'
            ], 500)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('API Error (500)');

        $this->httpClient->get('/test-endpoint');
    }

    public function test_builds_correct_url_with_leading_slash(): void
    {
        $token = 'test-token';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response(['success' => true], 200)
        ]);

        $this->httpClient->get('/path/to/endpoint');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'path/to/endpoint');
        });
    }

    public function test_builds_correct_url_without_leading_slash(): void
    {
        $token = 'test-token';

        $this->tokenService
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);

        Http::fake([
            '*' => Http::response(['success' => true], 200)
        ]);

        $this->httpClient->get('path/to/endpoint');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'path/to/endpoint');
        });
    }
}
