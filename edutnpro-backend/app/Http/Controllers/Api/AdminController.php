<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\ClassSection;
use App\Models\Timetable;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Bus;
use App\Models\BusRoute;
use App\Models\CanteenMenu;
use App\Models\MealReservation;
use App\Models\EmployeeContract;
use App\Models\EmployeeLeave;
use App\Models\Payroll;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Document;
use App\Models\Certificate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    // Dashboard & Statistics
    public function getDashboard()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_students' => Student::where('school_id', $schoolId)->count(),
            'total_teachers' => Teacher::where('school_id', $schoolId)->count(),
            'total_classes' => ClassSection::where('school_id', $schoolId)->count(),
            'active_students' => Student::where('school_id', $schoolId)->where('status', 'active')->count(),
            'attendance_rate' => $this->calculateOverallAttendanceRate($schoolId),
            'average_grade' => Grade::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->avg('grade') ?? 0,
            'pending_invoices' => Invoice::where('school_id', $schoolId)->where('status', 'pending')->count(),
            'monthly_revenue' => Payment::where('school_id', $schoolId)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getStats()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'students' => [
                'total' => Student::where('school_id', $schoolId)->count(),
                'active' => Student::where('school_id', $schoolId)->where('status', 'active')->count(),
                'inactive' => Student::where('school_id', $schoolId)->where('status', 'inactive')->count(),
                'by_gender' => Student::where('school_id', $schoolId)
                    ->select('gender', DB::raw('count(*) as count'))
                    ->groupBy('gender')
                    ->get(),
            ],
            'teachers' => [
                'total' => Teacher::where('school_id', $schoolId)->count(),
                'active' => Teacher::where('school_id', $schoolId)->where('status', 'active')->count(),
            ],
            'attendance' => [
                'rate' => $this->calculateOverallAttendanceRate($schoolId),
                'today' => Attendance::whereHas('student', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->whereDate('date', today())->count(),
            ],
            'finance' => [
                'total_revenue' => Payment::where('school_id', $schoolId)->sum('amount'),
                'monthly_revenue' => Payment::where('school_id', $schoolId)
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
                'pending_amount' => Invoice::where('school_id', $schoolId)
                    ->where('status', '!=', 'paid')
                    ->sum(DB::raw('total_amount - paid_amount')),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Schools Management
    public function schools(Request $request)
    {
        if ($request->isMethod('get')) {
            $schools = School::withCount(['students', 'teachers'])->paginate(20);
            return response()->json(['success' => true, 'data' => $schools]);
        }

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:schools',
                'phone' => 'required|string',
                'address' => 'required|string',
            ]);

            $school = School::create($validated);
            return response()->json(['success' => true, 'data' => $school], 201);
        }
    }

    // Students Management
    public function getStudents(Request $request)
    {
        $query = Student::where('school_id', auth()->user()->school_id)
            ->with(['classSection', 'parents']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('class_section_id')) {
            $query->where('class_section_id', $request->input('class_section_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $students = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    public function createStudent(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'class_section_id' => 'required|exists:class_sections,id',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = 'active';

        $student = Student::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.student_created'),
            'data' => $student,
        ], 201);
    }

    public function updateStudent(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:students,email,' . $student->id,
            'class_section_id' => 'nullable|exists:class_sections,id',
            'status' => 'nullable|in:active,inactive,graduated',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.student_updated'),
            'data' => $student,
        ]);
    }

    public function deleteStudent(Student $student)
    {
        $this->authorize('delete', $student);

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.student_deleted'),
        ]);
    }

    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        // Import logic with Excel package
        // This is a placeholder - implement actual import logic

        return response()->json([
            'success' => true,
            'message' => __('messages.students_imported'),
        ]);
    }

    public function exportStudents()
    {
        $students = Student::where('school_id', auth()->user()->school_id)
            ->with(['classSection'])
            ->get();

        // Export logic - return CSV or Excel file
        // This is a placeholder

        return response()->json([
            'success' => true,
            'message' => __('messages.export_ready'),
        ]);
    }

    // Teachers Management
    public function getTeachers(Request $request)
    {
        $query = Teacher::where('school_id', auth()->user()->school_id)
            ->with(['subjects', 'classSections']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teachers = $query->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $teachers,
        ]);
    }

    public function createTeacher(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers',
            'phone' => 'required|string',
            'specialization' => 'nullable|string',
            'hire_date' => 'required|date',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = 'active';

        $teacher = Teacher::create($validated);

        // Create user account
        User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make('password123'),
            'school_id' => auth()->user()->school_id,
        ])->assignRole('teacher');

        return response()->json([
            'success' => true,
            'message' => __('messages.teacher_created'),
            'data' => $teacher,
        ], 201);
    }

    public function updateTeacher(Request $request, Teacher $teacher)
    {
        $this->authorize('update', $teacher);

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string',
            'specialization' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        $teacher->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.teacher_updated'),
            'data' => $teacher,
        ]);
    }

    public function deleteTeacher(Teacher $teacher)
    {
        $this->authorize('delete', $teacher);

        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.teacher_deleted'),
        ]);
    }

    // HR Management
    public function getContracts()
    {
        $contracts = EmployeeContract::where('school_id', auth()->user()->school_id)
            ->with(['employee'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $contracts,
        ]);
    }

    public function createContract(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'employee_type' => 'required|in:teacher,staff',
            'type' => 'required|in:full_time,part_time,contract,temporary',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'salary' => 'required|numeric|min:0',
            'terms' => 'nullable|string',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = 'active';

        $contract = EmployeeContract::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.contract_created'),
            'data' => $contract,
        ], 201);
    }

    public function getLeaves()
    {
        $leaves = EmployeeLeave::where('school_id', auth()->user()->school_id)
            ->with(['employee'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $leaves,
        ]);
    }

    public function approveLeave(EmployeeLeave $leave)
    {
        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.leave_approved'),
            'data' => $leave,
        ]);
    }

    public function rejectLeave(EmployeeLeave $leave)
    {
        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.leave_rejected'),
            'data' => $leave,
        ]);
    }

    public function getPayrolls()
    {
        $payrolls = Payroll::where('school_id', auth()->user()->school_id)
            ->with(['employee'])
            ->latest('month')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $payrolls,
        ]);
    }

    public function generatePayrolls(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);

        // Generate payrolls for all active employees
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->where('status', 'active')
            ->get();

        foreach ($teachers as $teacher) {
            $contract = EmployeeContract::where('employee_id', $teacher->id)
                ->where('employee_type', 'teacher')
                ->where('status', 'active')
                ->first();

            if ($contract) {
                Payroll::updateOrCreate(
                    [
                        'employee_id' => $teacher->id,
                        'employee_type' => 'teacher',
                        'month' => $validated['month'],
                        'year' => $validated['year'],
                    ],
                    [
                        'school_id' => auth()->user()->school_id,
                        'basic_salary' => $contract->salary,
                        'bonuses' => 0,
                        'deductions' => 0,
                        'net_salary' => $contract->salary,
                        'status' => 'pending',
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.payrolls_generated'),
        ]);
    }

    // Academic Management
    public function getSubjects()
    {
        $subjects = Subject::where('school_id', auth()->user()->school_id)
            ->with('teachers')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    public function getClassrooms()
    {
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)
            ->withCount('timetables')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classrooms,
        ]);
    }

    public function getClassSections()
    {
        $sections = ClassSection::where('school_id', auth()->user()->school_id)
            ->withCount(['students', 'timetables'])
            ->with('classLevel')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }

    public function getTimetables()
    {
        $timetables = Timetable::whereHas('classSection', function ($q) {
                $q->where('school_id', auth()->user()->school_id);
            })
            ->with(['classSection', 'subject', 'teacher', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('class_section_id');

        return response()->json([
            'success' => true,
            'data' => $timetables,
        ]);
    }

    // Finance
    public function getInvoices()
    {
        $invoices = Invoice::where('school_id', auth()->user()->school_id)
            ->with(['student'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    public function getPayments()
    {
        $payments = Payment::where('school_id', auth()->user()->school_id)
            ->with(['student', 'invoice'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    public function getFinanceReports(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $reports = [
            'total_revenue' => Payment::where('school_id', $schoolId)->sum('amount'),
            'monthly_revenue' => Payment::where('school_id', $schoolId)
                ->whereMonth('created_at', $request->input('month', now()->month))
                ->whereYear('created_at', $request->input('year', now()->year))
                ->sum('amount'),
            'pending_invoices' => Invoice::where('school_id', $schoolId)
                ->where('status', 'pending')
                ->sum('total_amount'),
            'overdue_invoices' => Invoice::where('school_id', $schoolId)
                ->where('status', 'overdue')
                ->sum('total_amount'),
            'payment_methods' => Payment::where('school_id', $schoolId)
                ->select('method', DB::raw('sum(amount) as total'))
                ->groupBy('method')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    // Library
    public function getBooks()
    {
        $books = Book::where('school_id', auth()->user()->school_id)
            ->withCount('loans')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $books,
        ]);
    }

    public function getLoans()
    {
        $loans = BookLoan::whereHas('book', function ($q) {
                $q->where('school_id', auth()->user()->school_id);
            })
            ->with(['book', 'student'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $loans,
        ]);
    }

    public function getLibraryStats()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_books' => Book::where('school_id', $schoolId)->count(),
            'available_books' => Book::where('school_id', $schoolId)->where('status', 'available')->count(),
            'borrowed_books' => Book::where('school_id', $schoolId)->where('status', 'borrowed')->count(),
            'total_loans' => BookLoan::whereHas('book', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count(),
            'active_loans' => BookLoan::whereHas('book', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->where('status', 'borrowed')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Transport
    public function getBuses()
    {
        $buses = Bus::where('school_id', auth()->user()->school_id)
            ->withCount('routes')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $buses,
        ]);
    }

    public function getRoutes()
    {
        $routes = BusRoute::whereHas('bus', function ($q) {
                $q->where('school_id', auth()->user()->school_id);
            })
            ->with(['bus', 'students'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $routes,
        ]);
    }

    public function getTracking()
    {
        $buses = Bus::where('school_id', auth()->user()->school_id)
            ->select('id', 'number', 'current_latitude', 'current_longitude', 'location_updated_at', 'status')
            ->where('status', 'active')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $buses,
        ]);
    }

    // Canteen
    public function getMenus()
    {
        $menus = CanteenMenu::where('school_id', auth()->user()->school_id)
            ->latest('date')
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $menus,
        ]);
    }

    public function getReservations()
    {
        $reservations = MealReservation::whereHas('student', function ($q) {
                $q->where('school_id', auth()->user()->school_id);
            })
            ->with(['student', 'menu'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    public function getCanteenStats()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_reservations' => MealReservation::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->count(),
            'today_reservations' => MealReservation::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->whereDate('created_at', today())->count(),
            'weekly_reservations' => MealReservation::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Analytics
    public function getAnalytics()
    {
        $schoolId = auth()->user()->school_id;

        $analytics = [
            'students_growth' => $this->getStudentsGrowth($schoolId),
            'attendance_trends' => $this->getAttendanceTrends($schoolId),
            'grade_distribution' => $this->getGradeDistribution($schoolId),
            'revenue_trends' => $this->getRevenueTrends($schoolId),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    public function getPredictions()
    {
        // AI/ML predictions - placeholder
        $predictions = [
            'enrollment_forecast' => [],
            'revenue_forecast' => [],
            'at_risk_students' => [],
        ];

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    public function getReports(Request $request)
    {
        $type = $request->input('type', 'all');

        $reports = [
            'academic' => $this->getAcademicReport(),
            'financial' => $this->getFinancialReport(),
            'attendance' => $this->getAttendanceReport(),
        ];

        return response()->json([
            'success' => true,
            'data' => $type === 'all' ? $reports : ($reports[$type] ?? []),
        ]);
    }

    // Messaging
    public function getAllConversations()
    {
        $conversations = Conversation::where('school_id', auth()->user()->school_id)
            ->withCount('messages')
            ->with(['createdBy', 'participants.user'])
            ->latest('updated_at')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    public function getMessageStats()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total_conversations' => Conversation::where('school_id', $schoolId)->count(),
            'active_conversations' => Conversation::where('school_id', $schoolId)
                ->where('updated_at', '>=', now()->subDays(7))
                ->count(),
            'messages_by_type' => Conversation::where('school_id', $schoolId)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Events
    public function getEvents()
    {
        $events = Event::where('school_id', auth()->user()->school_id)
            ->with(['createdBy'])
            ->withCount('participants')
            ->latest('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function createEvent(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'nullable|string',
            'type' => 'required|in:academic,sport,cultural,other',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['created_by'] = auth()->id();

        $event = Event::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.event_created'),
            'data' => $event,
        ], 201);
    }

    // Documents & Certificates
    public function getDocuments()
    {
        $documents = Document::where('school_id', auth()->user()->school_id)
            ->with(['uploadedBy', 'student'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    public function getCertificates()
    {
        $certificates = Certificate::where('school_id', auth()->user()->school_id)
            ->with(['student'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $certificates,
        ]);
    }

    // System
    public function getLogs()
    {
        // System logs - placeholder
        return response()->json([
            'success' => true,
            'data' => [],
        ]);
    }

    public function backup()
    {
        // Database backup logic - placeholder
        return response()->json([
            'success' => true,
            'message' => __('messages.backup_created'),
        ]);
    }

    public function getSettings()
    {
        $settings = auth()->user()->school->settings ?? [];

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $school->update(['settings' => $validated['settings']]);

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_updated'),
            'data' => $school->settings,
        ]);
    }

    // Helper Methods
    private function calculateOverallAttendanceRate($schoolId)
    {
        $total = Attendance::whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->count();

        if ($total === 0) return 0;

        $present = Attendance::whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->where('status', 'present')->count();

        return round(($present / $total) * 100, 2);
    }

    private function getStudentsGrowth($schoolId)
    {
        return Student::where('school_id', $schoolId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();
    }

    private function getAttendanceTrends($schoolId)
    {
        return Attendance::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->selectRaw('DATE(date) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();
    }

    private function getGradeDistribution($schoolId)
    {
        return Grade::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
            ->selectRaw('CASE
                WHEN grade >= 18 THEN "Excellent"
                WHEN grade >= 16 THEN "Very Good"
                WHEN grade >= 14 THEN "Good"
                WHEN grade >= 12 THEN "Average"
                WHEN grade >= 10 THEN "Pass"
                ELSE "Fail"
            END as category, COUNT(*) as count')
            ->groupBy('category')
            ->get();
    }

    private function getRevenueTrends($schoolId)
    {
        return Payment::where('school_id', $schoolId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();
    }

    private function getAcademicReport()
    {
        $schoolId = auth()->user()->school_id;

        return [
            'total_students' => Student::where('school_id', $schoolId)->count(),
            'average_grade' => Grade::whereHas('student', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })->avg('grade'),
            'top_performers' => Student::where('school_id', $schoolId)
                ->withAvg('grades', 'grade')
                ->orderBy('grades_avg_grade', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    private function getFinancialReport()
    {
        $schoolId = auth()->user()->school_id;

        return [
            'total_revenue' => Payment::where('school_id', $schoolId)->sum('amount'),
            'pending_amount' => Invoice::where('school_id', $schoolId)
                ->where('status', '!=', 'paid')
                ->sum(DB::raw('total_amount - paid_amount')),
            'monthly_breakdown' => $this->getRevenueTrends($schoolId),
        ];
    }

    private function getAttendanceReport()
    {
        $schoolId = auth()->user()->school_id;

        return [
            'overall_rate' => $this->calculateOverallAttendanceRate($schoolId),
            'by_class' => ClassSection::where('school_id', $schoolId)
                ->get()
                ->map(function ($class) {
                    $total = Attendance::where('class_section_id', $class->id)->count();
                    $present = Attendance::where('class_section_id', $class->id)
                        ->where('status', 'present')
                        ->count();

                    return [
                        'class' => $class->name,
                        'rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
                    ];
                }),
        ];
    }
}
