<?php

namespace Tests\Unit;

use App\Services\AuthService;
use App\Services\HttpClient;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = Mockery::mock(HttpClient::class);
        $this->authService = new AuthService($this->httpClient);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_successful(): void
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $expectedResponse = [
            'token' => 'auth-token-123',
            'user' => [
                'id' => 1,
                'email' => 'test@example.com',
                'name' => 'Test User'
            ]
        ];

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->with('/partner/login', $credentials)
            ->andReturn($expectedResponse);

        $result = $this->authService->login($credentials);

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function test_login_throws_exception_on_failure(): void
    {
        $credentials = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword'
        ];

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->with('/partner/login', $credentials)
            ->andThrow(new \Exception('Invalid credentials'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login($credentials);
    }

    public function test_me_returns_user_information(): void
    {
        $expectedUser = [
            'id' => 1,
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => 'partner'
        ];

        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with('/partner/me')
            ->andReturn($expectedUser);

        $result = $this->authService->me();

        $this->assertEquals($expectedUser, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('email', $result);
    }

    public function test_me_throws_exception_when_unauthorized(): void
    {
        $this->httpClient
            ->shouldReceive('get')
            ->once()
            ->with('/partner/me')
            ->andThrow(new \Exception('Unauthorized'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unauthorized');

        $this->authService->me();
    }

    public function test_logout_successful(): void
    {
        $expectedResponse = [
            'message' => 'Successfully logged out'
        ];

        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->with('/partner/logout')
            ->andReturn($expectedResponse);

        $result = $this->authService->logout();

        $this->assertEquals($expectedResponse, $result);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_logout_throws_exception_on_failure(): void
    {
        $this->httpClient
            ->shouldReceive('post')
            ->once()
            ->with('/partner/logout')
            ->andThrow(new \Exception('Logout failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Logout failed');

        $this->authService->logout();
    }
}
