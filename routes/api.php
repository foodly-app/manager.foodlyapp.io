<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Partner Panel API Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    // Auth Routes
    Route::get('me', [AuthController::class, 'me'])->name('me');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Organization Routes
    Route::prefix('organizations')->group(function () {
        Route::get('/', [OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/{id}', [OrganizationController::class, 'show'])->name('organizations.show');
        Route::put('/{id}', [OrganizationController::class, 'update'])->name('organizations.update');
        Route::get('/{id}/statistics', [OrganizationController::class, 'statistics'])->name('organizations.statistics');
    });

    // Restaurant Routes
    Route::prefix('restaurants')->group(function () {
        Route::get('/', [RestaurantController::class, 'index'])->name('restaurants.index');
        Route::get('/{id}', [RestaurantController::class, 'show'])->name('restaurants.show');
        Route::put('/{id}', [RestaurantController::class, 'update'])->name('restaurants.update');
        Route::get('/{id}/tables', [RestaurantController::class, 'tables'])->name('restaurants.tables');
        Route::get('/{id}/statistics', [RestaurantController::class, 'statistics'])->name('restaurants.statistics');
    
        // Reservation Routes
        Route::prefix('{restaurantId}/reservations')->group(function () {
            Route::get('/', [ReservationController::class, 'index'])->name('reservations.index');
            Route::get('/today', [ReservationController::class, 'today'])->name('reservations.today');
            Route::get('/upcoming', [ReservationController::class, 'upcoming'])->name('reservations.upcoming');
            Route::get('/statistics', [ReservationController::class, 'statistics'])->name('reservations.statistics');
            
            Route::prefix('{id}')->group(function () {
                Route::put('/status', [ReservationController::class, 'updateStatus'])->name('reservations.update-status');
                Route::post('/notes', [ReservationController::class, 'addNotes'])->name('reservations.add-notes');
                Route::post('/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
            });
        });
    });
});