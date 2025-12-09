<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Partner Panel API Routes
|--------------------------------------------------------------------------
*/

Route::get('/test-login', [AuthController::class, 'testLogin'])->name('test-login');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::get('/partner/dashboard', [DashboardController::class, 'index'])->name('partner.dashboard');
Route::get('/partner/dashboard/kpis', [DashboardController::class, 'getKPIs'])->name('partner.dashboard.kpis');
Route::get('/partner/dashboard/restaurant', [DashboardController::class, 'getRestaurantInfo'])->name('partner.dashboard.restaurant');