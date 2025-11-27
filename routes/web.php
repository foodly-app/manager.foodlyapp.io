<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Login Page
Route::get('/login', function () {
    return view('login');
})->name('login');

// Login API
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
