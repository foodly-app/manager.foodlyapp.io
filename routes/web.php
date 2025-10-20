<?php

use App\Services\TokenService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Test Connection Route
Route::get('/test-connection', function(TokenService $tokenService) {
    try {
        $token = $tokenService->getToken();
        return response()->json([
            'success' => true,
            'message' => 'Connection successful',
            'token' => $token
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
});