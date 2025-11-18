<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\BusController;
use App\Http\Controllers\Api\CanteenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AnalyticsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Students
    Route::apiResource('students', StudentController::class);

    // Grades
    Route::apiResource('grades', GradeController::class);

    // Attendances
    Route::apiResource('attendances', AttendanceController::class);

    // Finance
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('payments', PaymentController::class);

    // Communication
    Route::apiResource('announcements', AnnouncementController::class);
    Route::apiResource('messages', MessageController::class);

    // Gamification
    Route::apiResource('badges', BadgeController::class);
    Route::get('/my-badges', [BadgeController::class, 'myBadges']);
    Route::get('/leaderboard', [BadgeController::class, 'leaderboard']);

    // Library
    Route::apiResource('library', LibraryController::class);
    Route::get('/my-loans', [LibraryController::class, 'myLoans']);
    Route::post('/library/{book}/loan', [LibraryController::class, 'loan']);
    Route::post('/library/loans/{loan}/return', [LibraryController::class, 'return']);

    // Transport
    Route::apiResource('buses', BusController::class);
    Route::get('/my-bus', [BusController::class, 'myBus']);
    Route::get('/bus-tracking/{bus}', [BusController::class, 'tracking']);

    // Canteen
    Route::apiResource('canteen', CanteenController::class);
    Route::get('/my-reservations', [CanteenController::class, 'myReservations']);
    Route::post('/canteen/reserve', [CanteenController::class, 'reserve']);
    Route::post('/canteen/cancel/{reservation}', [CanteenController::class, 'cancel']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Analytics
    Route::get('/analytics/dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('/analytics/predictions', [AnalyticsController::class, 'predictions']);
    Route::get('/analytics/performance', [AnalyticsController::class, 'performance']);
});
