<?php

use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

// Authentication routes
Route::get('/login', [HomeController::class, 'index'])->name('login');
Route::get('/register', [HomeController::class, 'index'])->name('register');
Route::get('/forgot-password', [HomeController::class, 'index'])->name('password.request');

// Application routes - all routes should point to the SPA
Route::get('/{any}', [HomeController::class, 'index'])->where('any', '.*');