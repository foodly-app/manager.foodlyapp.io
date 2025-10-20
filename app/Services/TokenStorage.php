<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class TokenStorage
{
    private const TOKEN_FILE = 'partner_token.json';
    private string $storagePath;

    public function __construct()
    {
        $this->storagePath = storage_path('app/tokens/'.self::TOKEN_FILE);
        $this->ensureDirectoryExists();
    }

    public function save(string $token, ?int $expiresAt = null): void
    {
        $data = [
            'token' => $token,
            'created_at' => Carbon::now()->timestamp,
            'expires_at' => $expiresAt ?? Carbon::now()->addDays(7)->timestamp
        ];

        File::put($this->storagePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function get(): ?array
    {
        if (!File::exists($this->storagePath)) {
            return null;
        }

        return json_decode(File::get($this->storagePath), true);
    }

    public function clear(): void
    {
        if (File::exists($this->storagePath)) {
            File::delete($this->storagePath);
        }
    }

    private function ensureDirectoryExists(): void
    {
        $directory = dirname($this->storagePath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}
