<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Students Management
    Route::resource('students', StudentController::class);

    // Teachers Management
    Route::resource('teachers', TeacherController::class);

    // Grades Management
    Route::resource('grades', GradeController::class);

    // Parents Management
    Route::resource('parents', ParentController::class);

    // Attendance Management
    Route::resource('attendances', AttendanceController::class);

    // Finance Management
    Route::resource('invoices', InvoiceController::class);
    Route::resource('payments', PaymentController::class);

    // Communication
    Route::resource('announcements', AnnouncementController::class);
    Route::resource('messages', MessageController::class);

    // Schools Management (Admin only)
    Route::middleware('role:super_admin|admin')->group(function () {
        Route::resource('schools', SchoolController::class);
    });
});
