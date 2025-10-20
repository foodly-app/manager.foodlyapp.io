<?php

namespace Tests\Unit;

use App\Services\TokenService;
use App\Services\TokenStorage;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    private TokenService $tokenService;
    private $tokenStorage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenStorage = Mockery::mock(TokenStorage::class);
        $this->tokenService = new TokenService($this->tokenStorage);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_existing_valid_token(): void
    {
        $validToken = 'valid-token-123';
        $futureTimestamp = now()->addDays(3)->timestamp;

        $this->tokenStorage
            ->shouldReceive('get')
            ->once()
            ->andReturn([
                'token' => $validToken,
                'expires_at' => $futureTimestamp
            ]);

        $token = $this->tokenService->getToken();

        $this->assertEquals($validToken, $token);
    }

    public function test_generates_new_token_when_no_token_exists(): void
    {
        $newToken = 'new-token-456';

        $this->tokenStorage
            ->shouldReceive('get')
            ->once()
            ->andReturn(null);

        $this->tokenStorage
            ->shouldReceive('save')
            ->once()
            ->with($newToken, \Mockery::any());

        Http::fake([
            '*/api/partner/login' => Http::response([
                'token' => $newToken
            ], 200)
        ]);

        $token = $this->tokenService->getToken();

        $this->assertEquals($newToken, $token);
    }

    public function test_generates_new_token_when_expired(): void
    {
        $expiredToken = 'expired-token-789';
        $newToken = 'new-token-abc';
        $pastTimestamp = now()->subDays(1)->timestamp;

        $this->tokenStorage
            ->shouldReceive('get')
            ->once()
            ->andReturn([
                'token' => $expiredToken,
                'expires_at' => $pastTimestamp
            ]);

        $this->tokenStorage
            ->shouldReceive('save')
            ->once()
            ->with($newToken, \Mockery::any());

        Http::fake([
            '*/api/partner/login' => Http::response([
                'token' => $newToken
            ], 200)
        ]);

        $token = $this->tokenService->getToken();

        $this->assertEquals($newToken, $token);
    }

    public function test_throws_exception_on_failed_token_generation(): void
    {
        $this->tokenStorage
            ->shouldReceive('get')
            ->once()
            ->andReturn(null);

        Http::fake([
            '*/api/partner/login' => Http::response([
                'error' => 'Invalid credentials'
            ], 401)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to generate token');

        $this->tokenService->getToken();
    }

    public function test_clear_token_calls_storage_clear(): void
    {
        $this->tokenStorage
            ->shouldReceive('clear')
            ->once();

        $this->tokenService->clearToken();
    }

    public function test_generates_token_with_correct_credentials(): void
    {
        $newToken = 'test-token-xyz';
        
        config(['services.partner.email' => 'test@example.com']);
        config(['services.partner.password' => 'password123']);
        config(['services.partner.url' => 'https://api.test.com']);

        $this->tokenStorage
            ->shouldReceive('get')
            ->once()
            ->andReturn(null);

        $this->tokenStorage
            ->shouldReceive('save')
            ->once();

        Http::fake([
            'https://api.test.com/api/partner/login' => Http::response([
                'token' => $newToken
            ], 200)
        ]);

        $this->tokenService->getToken();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.test.com/api/partner/login' &&
                   $request['email'] === 'test@example.com' &&
                   $request['password'] === 'password123';
        });
    }
}
