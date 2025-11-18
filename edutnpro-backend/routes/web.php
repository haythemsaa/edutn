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
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\CanteenController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AnalyticsController;
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

    // Gamification
    Route::resource('badges', BadgeController::class);
    Route::get('/leaderboard', [BadgeController::class, 'leaderboard'])->name('leaderboard');

    // Library
    Route::resource('library', LibraryController::class);
    Route::post('/library/{book}/loan', [LibraryController::class, 'loan'])->name('library.loan');
    Route::post('/library/loans/{loan}/return', [LibraryController::class, 'return'])->name('library.return');

    // Transport
    Route::resource('buses', BusController::class);
    Route::get('/transport/tracking', [BusController::class, 'tracking'])->name('transport.tracking');

    // Canteen
    Route::resource('canteen', CanteenController::class);
    Route::post('/canteen/reserve', [CanteenController::class, 'reserve'])->name('canteen.reserve');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Analytics & Reports
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/predictions', [AnalyticsController::class, 'predictions'])->name('analytics.predictions');

    // Schools Management (Admin only)
    Route::middleware('role:super_admin|admin')->group(function () {
        Route::resource('schools', SchoolController::class);
    });
});
