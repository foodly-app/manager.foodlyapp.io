<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableController;
use App\Services\TokenService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Test Connection Route
Route::get('/test-connection', function (TokenService $tokenService) {
    try {
        $token = $tokenService->getToken();

        return response()->json([
            'success' => true,
            'message' => 'Connection successful',
            'token' => $token,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('test.connection');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->name('auth.')->group(function () {
    // Login
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Get Current User
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth')->name('me');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    // Initial Dashboard
    Route::get('/initial-dashboard', [AuthController::class, 'initialDashboard'])->middleware('auth')->name('initial-dashboard');

    // Profile
    Route::get('/profile', [AuthController::class, 'getProfile'])->middleware('auth')->name('profile.get');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->middleware('auth')->name('profile.update');

    // Avatar
    Route::post('/avatar', [AuthController::class, 'uploadAvatar'])->middleware('auth')->name('avatar.upload');
    Route::delete('/avatar', [AuthController::class, 'deleteAvatar'])->middleware('auth')->name('avatar.delete');

    // Password
    Route::put('/password', [AuthController::class, 'changePassword'])->middleware('auth')->name('password.change');
});

/*
|--------------------------------------------------------------------------
| Organization Routes
|--------------------------------------------------------------------------
*/

Route::prefix('organizations')->name('organizations.')->middleware('auth')->group(function () {
    // CRUD
    Route::get('/', [OrganizationController::class, 'index'])->name('index');
    Route::get('/{id}', [OrganizationController::class, 'show'])->name('show');
    Route::put('/{id}', [OrganizationController::class, 'update'])->name('update');
    Route::get('/{id}/statistics', [OrganizationController::class, 'statistics'])->name('statistics');

    // Dashboard
    Route::get('/{id}/dashboard', [OrganizationController::class, 'dashboard'])->name('dashboard');
    Route::get('/{id}/dashboard/stats', [OrganizationController::class, 'dashboardStats'])->name('dashboard.stats');
    Route::get('/{id}/dashboard/overview', [OrganizationController::class, 'dashboardOverview'])->name('dashboard.overview');

    // Team Management
    Route::get('/{id}/team', [OrganizationController::class, 'team'])->name('team.index');
    Route::get('/{id}/team/{memberId}', [OrganizationController::class, 'teamMember'])->name('team.show');
    Route::post('/{id}/team', [OrganizationController::class, 'addTeamMember'])->name('team.add');
    Route::put('/{id}/team/{memberId}/role', [OrganizationController::class, 'updateTeamMemberRole'])->name('team.update-role');
    Route::delete('/{id}/team/{memberId}', [OrganizationController::class, 'removeTeamMember'])->name('team.remove');

    // Invitations
    Route::get('/{id}/invitations', [OrganizationController::class, 'invitations'])->name('invitations.index');
    Route::post('/{id}/invitations', [OrganizationController::class, 'sendInvitation'])->name('invitations.send');

    // Analytics
    Route::prefix('{id}/analytics')->name('analytics.')->group(function () {
        Route::get('/reservations', [OrganizationController::class, 'analyticsReservations'])->name('reservations');
        Route::get('/revenue', [OrganizationController::class, 'analyticsRevenue'])->name('revenue');
        Route::get('/popular-tables', [OrganizationController::class, 'analyticsPopularTables'])->name('popular-tables');
        Route::get('/peak-hours', [OrganizationController::class, 'analyticsPeakHours'])->name('peak-hours');
        Route::get('/customer-insights', [OrganizationController::class, 'analyticsCustomerInsights'])->name('customer-insights');
    });
});

/*
|--------------------------------------------------------------------------
| Restaurant Routes
|--------------------------------------------------------------------------
*/

Route::prefix('organizations/{organizationId}/restaurants')->name('restaurants.')->middleware('auth')->group(function () {
    // CRUD
    Route::get('/', [RestaurantController::class, 'index'])->name('index');
    Route::post('/', [RestaurantController::class, 'store'])->name('store');
    Route::get('/{id}', [RestaurantController::class, 'show'])->name('show');
    Route::put('/{id}', [RestaurantController::class, 'update'])->name('update');

    // Images
    Route::post('/{id}/images', [RestaurantController::class, 'uploadImages'])->name('images.upload');
    Route::delete('/{id}/images/{imageId}', [RestaurantController::class, 'deleteImage'])->name('images.delete');

    // Status
    Route::put('/{id}/status', [RestaurantController::class, 'updateStatus'])->name('status.update');

    // Settings
    Route::get('/{id}/settings', [RestaurantController::class, 'settings'])->name('settings.get');
    Route::put('/{id}/settings', [RestaurantController::class, 'updateSettings'])->name('settings.update');

    // Related Resources
    Route::get('/{id}/reservations', [RestaurantController::class, 'reservations'])->name('reservations');
    Route::get('/{id}/tables', [RestaurantController::class, 'tables'])->name('tables');
    Route::get('/{id}/places', [RestaurantController::class, 'places'])->name('places');

    // Statistics & Dashboard
    Route::get('/{id}/statistics', [RestaurantController::class, 'statistics'])->name('statistics');
    Route::get('/{id}/dashboard', [RestaurantController::class, 'dashboard'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Place Routes
|--------------------------------------------------------------------------
*/

Route::prefix('organizations/{organizationId}/restaurants/{restaurantId}/places')->name('places.')->middleware('auth')->group(function () {
    Route::get('/', [PlaceController::class, 'index'])->name('index');
    Route::post('/', [PlaceController::class, 'store'])->name('store');
    Route::get('/{id}', [PlaceController::class, 'show'])->name('show');
    Route::put('/{id}', [PlaceController::class, 'update'])->name('update');
    Route::delete('/{id}', [PlaceController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/tables', [PlaceController::class, 'tables'])->name('tables');
});

/*
|--------------------------------------------------------------------------
| Table Routes
|--------------------------------------------------------------------------
*/

Route::prefix('organizations/{organizationId}/restaurants/{restaurantId}/tables')->name('tables.')->middleware('auth')->group(function () {
    Route::get('/', [TableController::class, 'index'])->name('index');
    Route::post('/', [TableController::class, 'store'])->name('store');
    Route::get('/{id}', [TableController::class, 'show'])->name('show');
    Route::put('/{id}', [TableController::class, 'update'])->name('update');
    Route::delete('/{id}', [TableController::class, 'destroy'])->name('destroy');

    // Status
    Route::put('/{id}/status', [TableController::class, 'updateStatus'])->name('status.update');

    // Bulk Operations
    Route::post('/bulk-update', [TableController::class, 'bulkUpdate'])->name('bulk-update');

    // Availability
    Route::get('/{id}/availability', [TableController::class, 'availability'])->name('availability');
});

/*
|--------------------------------------------------------------------------
| Reservation Routes
|--------------------------------------------------------------------------
*/

Route::prefix('reservations')->name('reservations.')->middleware('auth')->group(function () {
    // Global Reservations
    Route::get('/', [ReservationController::class, 'index'])->name('index');
    Route::get('/calendar', [ReservationController::class, 'calendar'])->name('calendar');
    Route::get('/search', [ReservationController::class, 'search'])->name('search');
    Route::get('/statistics', [ReservationController::class, 'statistics'])->name('statistics');
});

Route::prefix('organizations/{organizationId}/restaurants/{restaurantId}/reservations')->name('reservations.restaurant.')->middleware('auth')->group(function () {
    // CRUD
    Route::post('/', [ReservationController::class, 'store'])->name('store');
    Route::get('/{id}', [ReservationController::class, 'show'])->name('show');
    Route::put('/{id}', [ReservationController::class, 'update'])->name('update');
    Route::delete('/{id}', [ReservationController::class, 'destroy'])->name('destroy');

    // Status Updates
    Route::put('/{id}/status', [ReservationController::class, 'updateStatus'])->name('status.update');
    Route::put('/{id}/confirm', [ReservationController::class, 'confirm'])->name('confirm');
    Route::put('/{id}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
    Route::put('/{id}/paid', [ReservationController::class, 'markAsPaid'])->name('paid');
    Route::put('/{id}/complete', [ReservationController::class, 'complete'])->name('complete');
    Route::put('/{id}/no-show', [ReservationController::class, 'noShow'])->name('no-show');

    // Table Assignment
    Route::put('/{id}/assign-table', [ReservationController::class, 'assignTable'])->name('assign-table');

    // Notes
    Route::post('/{id}/notes', [ReservationController::class, 'addNote'])->name('notes.add');
});

/*
|--------------------------------------------------------------------------
| Booking Routes
|--------------------------------------------------------------------------
*/

Route::prefix('organizations/{organizationId}/restaurants/{restaurantId}/booking')->name('booking.')->middleware('auth')->group(function () {
    // Settings
    Route::get('/settings', [BookingController::class, 'getSettings'])->name('settings.get');
    Route::put('/settings', [BookingController::class, 'updateSettings'])->name('settings.update');

    // Time Slots
    Route::get('/time-slots', [BookingController::class, 'getTimeSlots'])->name('time-slots.get');
    Route::put('/time-slots', [BookingController::class, 'updateTimeSlots'])->name('time-slots.update');

    // Availability
    Route::get('/availability', [BookingController::class, 'checkAvailability'])->name('availability.check');

    // Blocked Dates
    Route::get('/blocked-dates', [BookingController::class, 'getBlockedDates'])->name('blocked-dates.get');
    Route::post('/blocked-dates', [BookingController::class, 'blockDates'])->name('blocked-dates.block');
    Route::delete('/blocked-dates/{id}', [BookingController::class, 'unblockDates'])->name('blocked-dates.unblock');
});

/*
|--------------------------------------------------------------------------
| Menu Routes
|--------------------------------------------------------------------------
*/

Route::prefix('organizations/{organizationId}/restaurants/{restaurantId}/menu')->name('menu.')->middleware('auth')->group(function () {
    // Menu Overview
    Route::get('/', [MenuController::class, 'index'])->name('index');

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [MenuController::class, 'categories'])->name('index');
        Route::post('/', [MenuController::class, 'storeCategory'])->name('store');
        Route::put('/{categoryId}', [MenuController::class, 'updateCategory'])->name('update');
        Route::delete('/{categoryId}', [MenuController::class, 'destroyCategory'])->name('destroy');
    });

    // Items
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [MenuController::class, 'items'])->name('index');
        Route::post('/', [MenuController::class, 'storeItem'])->name('store');
        Route::get('/{itemId}', [MenuController::class, 'showItem'])->name('show');
        Route::put('/{itemId}', [MenuController::class, 'updateItem'])->name('update');
        Route::delete('/{itemId}', [MenuController::class, 'destroyItem'])->name('destroy');

        // Item Images
        Route::post('/{itemId}/image', [MenuController::class, 'uploadItemImage'])->name('image.upload');
        Route::delete('/{itemId}/image', [MenuController::class, 'deleteItemImage'])->name('image.delete');
    });
});
