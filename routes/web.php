<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HomeController;

// Health Check Routes - يجب أن تكون أول routes
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Enterprise Pro API is running',
        'timestamp' => now()->toDateTimeString()
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// Application SPA Routes
Route::get('/welcome', [HomeController::class, 'welcome'])->name('welcome');

// Authentication Routes
Route::get('/login', [HomeController::class, 'index'])->name('login');
Route::get('/register', [HomeController::class, 'index'])->name('register');
Route::get('/forgot-password', [HomeController::class, 'index'])->name('password.request');

// Application Routes - جميع المسارات تشير إلى SPA
Route::get('/{any}', [HomeController::class, 'index'])->where('any', '.*');
