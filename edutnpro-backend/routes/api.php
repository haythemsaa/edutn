<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ParentController;
use App\Http\Controllers\Api\StudentApiController;
use App\Http\Controllers\Api\TeacherApiController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/password', [AuthController::class, 'changePassword']);

    // ==========================================
    // PARENT APP ROUTES
    // ==========================================
    Route::prefix('parent')->name('api.parent.')->group(function () {
        // Children
        Route::get('/children', [ParentController::class, 'getChildren']);
        Route::get('/children/{student}', [ParentController::class, 'getChildDetails']);

        // Grades
        Route::get('/students/{student}/grades', [ParentController::class, 'getGrades']);
        Route::get('/students/{student}/report-cards', [ParentController::class, 'getReportCards']);
        Route::get('/students/{student}/report-cards/{term}', [ParentController::class, 'getReportCard']);

        // Attendance
        Route::get('/students/{student}/attendance', [ParentController::class, 'getAttendance']);
        Route::post('/students/{student}/attendance/justify', [ParentController::class, 'justifyAbsence']);

        // Timetable
        Route::get('/students/{student}/timetable', [ParentController::class, 'getTimetable']);

        // Assignments
        Route::get('/students/{student}/assignments', [ParentController::class, 'getAssignments']);
        Route::get('/assignments/{assignment}', [ParentController::class, 'getAssignment']);

        // Discipline
        Route::get('/students/{student}/discipline', [ParentController::class, 'getDiscipline']);

        // Transport
        Route::get('/students/{student}/transport', [ParentController::class, 'getTransport']);
        Route::get('/buses/{bus}/track', [ParentController::class, 'trackBus']);

        // Canteen
        Route::get('/canteen/menus', [ParentController::class, 'getMenus']);
        Route::get('/students/{student}/canteen/reservations', [ParentController::class, 'getMealReservations']);

        // Documents
        Route::get('/students/{student}/documents', [ParentController::class, 'getDocuments']);
        Route::get('/documents/{document}/download', [ParentController::class, 'downloadDocument']);

        // Certificates
        Route::get('/students/{student}/certificates', [ParentController::class, 'getCertificates']);
        Route::post('/students/{student}/certificates/request', [ParentController::class, 'requestCertificate']);

        // Invoices & Payments
        Route::get('/invoices', [ParentController::class, 'getInvoices']);
        Route::post('/invoices/{invoice}/pay', [ParentController::class, 'payInvoice']);
        Route::get('/payments', [ParentController::class, 'getPayments']);

        // Appointments
        Route::get('/appointments', [ParentController::class, 'getAppointments']);
        Route::post('/appointments', [ParentController::class, 'requestAppointment']);
        Route::put('/appointments/{appointment}', [ParentController::class, 'updateAppointment']);

        // Events
        Route::get('/events', [ParentController::class, 'getEvents']);
        Route::post('/events/{event}/register', [ParentController::class, 'registerEvent']);

        // Conversations & Messages
        Route::get('/conversations', [ParentController::class, 'getConversations']);
        Route::post('/conversations', [ParentController::class, 'createConversation']);
        Route::get('/conversations/{conversation}/messages', [ParentController::class, 'getMessages']);
        Route::post('/conversations/{conversation}/messages', [ParentController::class, 'sendMessage']);

        // Notifications
        Route::get('/notifications', [ParentController::class, 'getNotifications']);
        Route::post('/notifications/{notification}/read', [ParentController::class, 'markAsRead']);
    });

    // ==========================================
    // STUDENT APP ROUTES
    // ==========================================
    Route::prefix('student')->name('api.student.')->group(function () {
        // Profile
        Route::get('/profile', [StudentApiController::class, 'getProfile']);
        Route::put('/profile', [StudentApiController::class, 'updateProfile']);

        // Grades
        Route::get('/grades', [StudentApiController::class, 'getGrades']);
        Route::get('/report-cards', [StudentApiController::class, 'getReportCards']);
        Route::get('/report-cards/{term}', [StudentApiController::class, 'getReportCard']);

        // Timetable
        Route::get('/timetable', [StudentApiController::class, 'getTimetable']);

        // Class Diary
        Route::get('/class-diary', [StudentApiController::class, 'getClassDiary']);
        Route::get('/class-diary/{diary}', [StudentApiController::class, 'getDiaryEntry']);

        // Assignments
        Route::get('/assignments', [StudentApiController::class, 'getAssignments']);
        Route::get('/assignments/{assignment}', [StudentApiController::class, 'getAssignment']);
        Route::post('/assignments/{assignment}/submit', [StudentApiController::class, 'submitAssignment']);

        // Attendance
        Route::get('/attendance', [StudentApiController::class, 'getAttendance']);

        // Gamification
        Route::get('/badges', [StudentApiController::class, 'getMyBadges']);
        Route::get('/points', [StudentApiController::class, 'getMyPoints']);
        Route::get('/leaderboard', [StudentApiController::class, 'getLeaderboard']);
        Route::get('/achievements', [StudentApiController::class, 'getAchievements']);

        // Library
        Route::get('/library/loans', [StudentApiController::class, 'getMyLoans']);
        Route::get('/library/books', [StudentApiController::class, 'searchBooks']);
        Route::post('/library/books/{book}/scan', [StudentApiController::class, 'scanBook']);

        // Transport
        Route::get('/transport', [StudentApiController::class, 'getTransport']);
        Route::get('/bus/track', [StudentApiController::class, 'trackMyBus']);

        // Canteen
        Route::get('/canteen/menus', [StudentApiController::class, 'getMenus']);
        Route::get('/canteen/reservations', [StudentApiController::class, 'getMyReservations']);

        // Events
        Route::get('/events', [StudentApiController::class, 'getEvents']);

        // Messages
        Route::get('/conversations', [StudentApiController::class, 'getConversations']);
        Route::get('/conversations/{conversation}/messages', [StudentApiController::class, 'getMessages']);
        Route::post('/conversations/{conversation}/messages', [StudentApiController::class, 'sendMessage']);

        // Documents
        Route::get('/documents', [StudentApiController::class, 'getDocuments']);

        // Notifications
        Route::get('/notifications', [StudentApiController::class, 'getNotifications']);
    });

    // ==========================================
    // TEACHER APP ROUTES
    // ==========================================
    Route::prefix('teacher')->name('api.teacher.')->group(function () {
        // Profile
        Route::get('/profile', [TeacherApiController::class, 'getProfile']);

        // Classes
        Route::get('/classes', [TeacherApiController::class, 'getMyClasses']);
        Route::get('/classes/{classSection}', [TeacherApiController::class, 'getClassDetails']);
        Route::get('/classes/{classSection}/students', [TeacherApiController::class, 'getClassStudents']);

        // Timetable
        Route::get('/timetable', [TeacherApiController::class, 'getMyTimetable']);

        // Grades
        Route::get('/classes/{classSection}/grades', [TeacherApiController::class, 'getClassGrades']);
        Route::post('/grades', [TeacherApiController::class, 'addGrade']);
        Route::put('/grades/{grade}', [TeacherApiController::class, 'updateGrade']);
        Route::delete('/grades/{grade}', [TeacherApiController::class, 'deleteGrade']);

        // Attendance
        Route::get('/classes/{classSection}/attendance', [TeacherApiController::class, 'getAttendance']);
        Route::post('/attendance', [TeacherApiController::class, 'takeAttendance']);
        Route::post('/attendance/qr-scan', [TeacherApiController::class, 'scanQRAttendance']);

        // Class Diary
        Route::get('/class-diary', [TeacherApiController::class, 'getMyDiary']);
        Route::post('/class-diary', [TeacherApiController::class, 'createDiaryEntry']);
        Route::put('/class-diary/{diary}', [TeacherApiController::class, 'updateDiaryEntry']);

        // Assignments
        Route::get('/assignments', [TeacherApiController::class, 'getMyAssignments']);
        Route::post('/assignments', [TeacherApiController::class, 'createAssignment']);
        Route::put('/assignments/{assignment}', [TeacherApiController::class, 'updateAssignment']);
        Route::delete('/assignments/{assignment}', [TeacherApiController::class, 'deleteAssignment']);
        Route::get('/assignments/{assignment}/submissions', [TeacherApiController::class, 'getSubmissions']);
        Route::post('/assignments/{assignment}/submissions/{submission}/grade', [TeacherApiController::class, 'gradeSubmission']);

        // Online Classes
        Route::get('/online-classes', [TeacherApiController::class, 'getOnlineClasses']);
        Route::post('/online-classes', [TeacherApiController::class, 'createOnlineClass']);
        Route::post('/online-classes/{class}/start', [TeacherApiController::class, 'startClass']);
        Route::post('/online-classes/{class}/end', [TeacherApiController::class, 'endClass']);

        // Discipline
        Route::get('/discipline', [TeacherApiController::class, 'getDisciplineIncidents']);
        Route::post('/discipline', [TeacherApiController::class, 'reportIncident']);
        Route::post('/discipline/{incident}/sanction', [TeacherApiController::class, 'addSanction']);

        // Analytics
        Route::get('/classes/{classSection}/analytics', [TeacherApiController::class, 'getClassAnalytics']);
        Route::get('/students/{student}/analytics', [TeacherApiController::class, 'getStudentAnalytics']);

        // Messages
        Route::get('/conversations', [TeacherApiController::class, 'getConversations']);
        Route::post('/conversations', [TeacherApiController::class, 'createConversation']);
        Route::get('/conversations/{conversation}/messages', [TeacherApiController::class, 'getMessages']);
        Route::post('/conversations/{conversation}/messages', [TeacherApiController::class, 'sendMessage']);

        // Appointments
        Route::get('/appointments', [TeacherApiController::class, 'getAppointments']);
        Route::post('/appointments/{appointment}/confirm', [TeacherApiController::class, 'confirmAppointment']);

        // HR (Personal)
        Route::get('/contracts', [TeacherApiController::class, 'getMyContracts']);
        Route::get('/leaves', [TeacherApiController::class, 'getMyLeaves']);
        Route::post('/leaves', [TeacherApiController::class, 'requestLeave']);
        Route::get('/payrolls', [TeacherApiController::class, 'getMyPayrolls']);

        // Documents
        Route::get('/documents', [TeacherApiController::class, 'getDocuments']);
        Route::post('/documents', [TeacherApiController::class, 'uploadDocument']);
    });

    // ==========================================
    // ADMIN APP ROUTES
    // ==========================================
    Route::prefix('admin')->middleware('role:admin|super_admin')->name('api.admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'getDashboard']);
        Route::get('/stats', [AdminController::class, 'getStats']);

        // Schools
        Route::apiResource('schools', AdminController::class . '@schools');

        // Students
        Route::get('/students', [AdminController::class, 'getStudents']);
        Route::post('/students', [AdminController::class, 'createStudent']);
        Route::put('/students/{student}', [AdminController::class, 'updateStudent']);
        Route::delete('/students/{student}', [AdminController::class, 'deleteStudent']);
        Route::post('/students/import', [AdminController::class, 'importStudents']);
        Route::get('/students/export', [AdminController::class, 'exportStudents']);

        // Teachers
        Route::get('/teachers', [AdminController::class, 'getTeachers']);
        Route::post('/teachers', [AdminController::class, 'createTeacher']);
        Route::put('/teachers/{teacher}', [AdminController::class, 'updateTeacher']);
        Route::delete('/teachers/{teacher}', [AdminController::class, 'deleteTeacher']);

        // HR Management
        Route::get('/hr/contracts', [AdminController::class, 'getContracts']);
        Route::post('/hr/contracts', [AdminController::class, 'createContract']);
        Route::get('/hr/leaves', [AdminController::class, 'getLeaves']);
        Route::post('/hr/leaves/{leave}/approve', [AdminController::class, 'approveLeave']);
        Route::post('/hr/leaves/{leave}/reject', [AdminController::class, 'rejectLeave']);
        Route::get('/hr/payrolls', [AdminController::class, 'getPayrolls']);
        Route::post('/hr/payrolls/generate', [AdminController::class, 'generatePayrolls']);

        // Academic Management
        Route::get('/subjects', [AdminController::class, 'getSubjects']);
        Route::get('/classrooms', [AdminController::class, 'getClassrooms']);
        Route::get('/class-sections', [AdminController::class, 'getClassSections']);
        Route::get('/timetables', [AdminController::class, 'getTimetables']);

        // Finance
        Route::get('/invoices', [AdminController::class, 'getInvoices']);
        Route::get('/payments', [AdminController::class, 'getPayments']);
        Route::get('/finance/reports', [AdminController::class, 'getFinanceReports']);

        // Library
        Route::get('/library/books', [AdminController::class, 'getBooks']);
        Route::get('/library/loans', [AdminController::class, 'getLoans']);
        Route::get('/library/stats', [AdminController::class, 'getLibraryStats']);

        // Transport
        Route::get('/transport/buses', [AdminController::class, 'getBuses']);
        Route::get('/transport/routes', [AdminController::class, 'getRoutes']);
        Route::get('/transport/tracking', [AdminController::class, 'getTracking']);

        // Canteen
        Route::get('/canteen/menus', [AdminController::class, 'getMenus']);
        Route::get('/canteen/reservations', [AdminController::class, 'getReservations']);
        Route::get('/canteen/stats', [AdminController::class, 'getCanteenStats']);

        // Analytics
        Route::get('/analytics', [AdminController::class, 'getAnalytics']);
        Route::get('/analytics/predictions', [AdminController::class, 'getPredictions']);
        Route::get('/analytics/reports', [AdminController::class, 'getReports']);

        // Messaging
        Route::get('/conversations', [AdminController::class, 'getAllConversations']);
        Route::get('/messages/stats', [AdminController::class, 'getMessageStats']);

        // Events
        Route::get('/events', [AdminController::class, 'getEvents']);
        Route::post('/events', [AdminController::class, 'createEvent']);

        // Documents
        Route::get('/documents', [AdminController::class, 'getDocuments']);
        Route::get('/certificates', [AdminController::class, 'getCertificates']);

        // System
        Route::get('/logs', [AdminController::class, 'getLogs']);
        Route::post('/backup', [AdminController::class, 'backup']);
        Route::get('/settings', [AdminController::class, 'getSettings']);
        Route::put('/settings', [AdminController::class, 'updateSettings']);
    });
});
