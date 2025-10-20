<?php

namespace Tests\Unit;

use App\Services\TokenStorage;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TokenStorageTest extends TestCase
{
    private TokenStorage $tokenStorage;
    private string $testTokenPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenStorage = new TokenStorage();
        $this->testTokenPath = storage_path('app/tokens/partner_token.json');
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->testTokenPath)) {
            File::delete($this->testTokenPath);
        }
        parent::tearDown();
    }

    public function test_can_save_token(): void
    {
        $token = 'test-token-123';
        $expiresAt = now()->addDays(7)->timestamp;

        $this->tokenStorage->save($token, $expiresAt);

        $this->assertFileExists($this->testTokenPath);
        
        $content = json_decode(File::get($this->testTokenPath), true);
        $this->assertEquals($token, $content['token']);
        $this->assertEquals($expiresAt, $content['expires_at']);
        $this->assertArrayHasKey('created_at', $content);
    }

    public function test_can_save_token_with_default_expiration(): void
    {
        $token = 'test-token-456';

        $this->tokenStorage->save($token);

        $this->assertFileExists($this->testTokenPath);
        
        $content = json_decode(File::get($this->testTokenPath), true);
        $this->assertEquals($token, $content['token']);
        $this->assertArrayHasKey('expires_at', $content);
    }

    public function test_can_get_token(): void
    {
        $token = 'test-token-789';
        $expiresAt = now()->addDays(7)->timestamp;

        $this->tokenStorage->save($token, $expiresAt);
        $result = $this->tokenStorage->get();

        $this->assertIsArray($result);
        $this->assertEquals($token, $result['token']);
        $this->assertEquals($expiresAt, $result['expires_at']);
    }

    public function test_get_returns_null_when_no_token_exists(): void
    {
        $result = $this->tokenStorage->get();

        $this->assertNull($result);
    }

    public function test_can_clear_token(): void
    {
        $token = 'test-token-clear';
        $this->tokenStorage->save($token);

        $this->assertFileExists($this->testTokenPath);

        $this->tokenStorage->clear();

        $this->assertFileDoesNotExist($this->testTokenPath);
    }

    public function test_clear_does_not_fail_when_no_token_exists(): void
    {
        $this->tokenStorage->clear();

        $this->assertFileDoesNotExist($this->testTokenPath);
    }

    public function test_directory_is_created_if_not_exists(): void
    {
        $directory = dirname($this->testTokenPath);
        
        if (File::exists($directory)) {
            File::deleteDirectory($directory);
        }

        new TokenStorage();

        $this->assertDirectoryExists($directory);
    }
}
