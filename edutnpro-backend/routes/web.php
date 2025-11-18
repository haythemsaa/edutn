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
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\ClassDiaryController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\EmployeeContractController;
use App\Http\Controllers\EmployeeLeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OnlineClassController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DocumentController;
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

    // Subjects Management
    Route::resource('subjects', SubjectController::class);

    // Classrooms Management
    Route::resource('classrooms', ClassroomController::class);

    // Class Sections Management
    Route::resource('class-sections', ClassSectionController::class);

    // Class Diary (Cahier de texte)
    Route::resource('class-diary', ClassDiaryController::class);

    // Timetables Management
    Route::resource('timetables', TimetableController::class);

    // Report Cards
    Route::resource('report-cards', ReportCardController::class);
    Route::get('/report-cards/generate/{student}/{term}', [ReportCardController::class, 'generate'])->name('report-cards.generate');

    // HR Management
    Route::prefix('hr')->name('hr.')->group(function () {
        // Employee Contracts
        Route::resource('contracts', EmployeeContractController::class);

        // Employee Leaves
        Route::resource('leaves', EmployeeLeaveController::class);
        Route::post('leaves/{leave}/approve', [EmployeeLeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('leaves/{leave}/reject', [EmployeeLeaveController::class, 'reject'])->name('leaves.reject');

        // Payroll
        Route::resource('payrolls', PayrollController::class);
        Route::get('payrolls/generate/{month}/{year}', [PayrollController::class, 'generate'])->name('payrolls.generate');
    });

    // Appointments
    Route::resource('appointments', AppointmentController::class);
    Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // Conversations & Messaging
    Route::resource('conversations', ConversationController::class);
    Route::post('conversations/{conversation}/messages', [ConversationController::class, 'sendMessage'])->name('conversations.send');
    Route::get('conversations/{conversation}/messages', [ConversationController::class, 'messages'])->name('conversations.messages');

    // Assignments & Homework
    Route::resource('assignments', AssignmentController::class);
    Route::get('assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])->name('assignments.submissions');
    Route::post('assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');

    // Discipline Management
    Route::resource('discipline', DisciplineController::class);
    Route::post('discipline/{incident}/sanction', [DisciplineController::class, 'addSanction'])->name('discipline.sanction');

    // Events Management
    Route::resource('events', EventController::class);
    Route::post('events/{event}/register', [EventController::class, 'register'])->name('events.register');

    // Online Classes
    Route::resource('online-classes', OnlineClassController::class);
    Route::post('online-classes/{class}/start', [OnlineClassController::class, 'start'])->name('online-classes.start');
    Route::post('online-classes/{class}/end', [OnlineClassController::class, 'end'])->name('online-classes.end');

    // Certificates
    Route::resource('certificates', CertificateController::class);
    Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::post('certificates/request', [CertificateController::class, 'request'])->name('certificates.request');

    // Documents
    Route::resource('documents', DocumentController::class);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // Schools Management (Admin only)
    Route::middleware('role:super_admin|admin')->group(function () {
        Route::resource('schools', SchoolController::class);
    });
});
