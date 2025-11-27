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

// Dashboard Page
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Login API Routes
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
