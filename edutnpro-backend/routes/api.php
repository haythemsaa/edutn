<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\MessageController;
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
});
