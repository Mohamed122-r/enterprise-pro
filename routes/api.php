<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\ChatController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Protected Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        // Dashboard
        Route::get('dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('dashboard/activities', [DashboardController::class, 'recentActivities']);

        // Users
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);

        // Contacts
        Route::apiResource('contacts', ContactController::class);

        // Attendance
        Route::apiResource('attendances', AttendanceController::class);
        Route::post('attendances/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendances/check-out', [AttendanceController::class, 'checkOut']);

        // Invoices
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/send', [InvoiceController::class, 'sendInvoice']);
        Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'recordPayment']);

        // Chat
        Route::get('chat/conversations', [ChatController::class, 'conversations']);
        Route::post('chat/conversations', [ChatController::class, 'createConversation']);
        Route::get('chat/conversations/{conversation}/messages', [ChatController::class, 'messages']);
        Route::post('chat/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
    });
});