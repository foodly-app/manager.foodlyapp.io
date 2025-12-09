<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root Route - Redirect to Login
Route::get('/', function () {
    return redirect('/login');
});

// Login Page
Route::get('/login', function () {
    return view('login');
})->name('login');

<<<<<<< HEAD
// Reservations API Route (must be before catch-all)
// Reservations API Route (must be before catch-all)
Route::get('/reservations', function () {
    try {
        // Get reservations directly using the partner/reservations endpoint
        // This avoids the need for organization/restaurant IDs
        $reservationService = app(\App\Services\ReservationService::class);
        $result = $reservationService->list();

        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('partner.auth')->name('reservations.index');

// Tables API Route (must be before catch-all)
Route::get('/tables', function () {
    try {
        // Get initial dashboard to get organization/restaurant IDs
        $authService = app(\App\Services\AuthService::class);
        $dashboardData = $authService->initialDashboard();

        $organizationId = $dashboardData['data']['organization']['id'] ?? null;
        $restaurantId = $dashboardData['data']['restaurant']['id'] ?? null;

        if (!$organizationId || !$restaurantId) {
            return response()->json([
                'success' => false,
                'message' => 'Organization or Restaurant not found'
            ], 404);
        }

        // Get tables
        $tableService = app(\App\Services\TableService::class);
        $result = $tableService->list($organizationId, $restaurantId, []);

        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('partner.auth')->name('tables.index');

// Places API Route (must be before catch-all)
Route::get('/places', function () {
    try {
        // Get initial dashboard to get organization/restaurant IDs
        $authService = app(\App\Services\AuthService::class);
        $dashboardData = $authService->initialDashboard();

        $organizationId = $dashboardData['data']['organization']['id'] ?? null;
        $restaurantId = $dashboardData['data']['restaurant']['id'] ?? null;

        if (!$organizationId || !$restaurantId) {
            return response()->json([
                'success' => false,
                'message' => 'Organization or Restaurant not found'
            ], 404);
        }

        // Get places
        $placeService = app(\App\Services\PlaceService::class);
        $result = $placeService->list($organizationId, $restaurantId, []);

        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('partner.auth')->name('places.index');

// Calendar API Route (Global)
Route::get('/calendar/events', [ReservationController::class, 'calendar'])->middleware('partner.auth')->name('calendar.events');

// Reservation Routes (must be before catch-all)
Route::prefix('organizations/{organizationId}/restaurants/{restaurantId}/reservations')->middleware('partner.auth')->group(function () {


    // Individual reservation
    Route::get('/{id}', [ReservationController::class, 'show'])->name('reservations.show');

    // Status Actions
    Route::post('/{id}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/{id}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::post('/{id}/paid', [ReservationController::class, 'markAsPaid'])->name('reservations.paid');
    Route::post('/{id}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');
    Route::post('/{id}/no-show', [ReservationController::class, 'noShow'])->name('reservations.no-show');
});

// SPA Routes - All routes that use Vue Router should return the dashboard view
Route::get('/{any}', function () {
    return view('dashboard');
})->where('any', '^(?!api|auth|test-connection|organizations|reservations|tables|places).*$')
    ->middleware('partner.auth')
    ->name('spa');
=======
// Dashboard Page
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
>>>>>>> 6eac07bd639cc909318602c3773296acf4fccf92

// Login API Routes
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
