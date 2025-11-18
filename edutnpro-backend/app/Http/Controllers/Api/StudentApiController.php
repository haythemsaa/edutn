<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Attendance;
use App\Models\Timetable;
use App\Models\ClassDiary;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\BookLoan;
use App\Models\Book;
use App\Models\Bus;
use App\Models\CanteenMenu;
use App\Models\MealReservation;
use App\Models\Event;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Document;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentApiController extends Controller
{
    // Profile
    public function getProfile()
    {
        $student = auth()->user()->student()
            ->with(['classSection.classLevel', 'school', 'parents'])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $student,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $student = auth()->user()->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.profile_updated'),
            'data' => $student,
        ]);
    }

    // Grades & Report Cards
    public function getGrades()
    {
        $student = auth()->user()->student;

        $grades = Grade::where('student_id', $student->id)
            ->with(['subject', 'teacher'])
            ->latest()
            ->get()
            ->groupBy('term');

        return response()->json([
            'success' => true,
            'data' => $grades,
        ]);
    }

    public function getReportCards()
    {
        $student = auth()->user()->student;

        $reportCards = ReportCard::where('student_id', $student->id)
            ->with('classSection')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reportCards,
        ]);
    }

    public function getReportCard($term)
    {
        $student = auth()->user()->student;

        $reportCard = ReportCard::where('student_id', $student->id)
            ->where('term', $term)
            ->with(['classSection', 'grades.subject'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $reportCard,
        ]);
    }

    // Timetable
    public function getTimetable()
    {
        $student = auth()->user()->student;

        $timetable = Timetable::where('class_section_id', $student->class_section_id)
            ->with(['subject', 'teacher', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return response()->json([
            'success' => true,
            'data' => $timetable,
        ]);
    }

    // Class Diary
    public function getClassDiary()
    {
        $student = auth()->user()->student;

        $diary = ClassDiary::where('class_section_id', $student->class_section_id)
            ->with(['subject', 'teacher'])
            ->latest('date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $diary,
        ]);
    }

    public function getDiaryEntry(ClassDiary $diary)
    {
        $diary->load(['subject', 'teacher']);

        return response()->json([
            'success' => true,
            'data' => $diary,
        ]);
    }

    // Assignments
    public function getAssignments()
    {
        $student = auth()->user()->student;

        $assignments = Assignment::where('class_section_id', $student->class_section_id)
            ->with(['subject', 'teacher', 'submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->latest()
            ->get()
            ->map(function ($assignment) use ($student) {
                $submission = $assignment->submissions->first();
                $assignment->my_submission = $submission;
                $assignment->is_submitted = $submission !== null;
                $assignment->is_late = $submission && $submission->submitted_at > $assignment->due_date;
                unset($assignment->submissions);
                return $assignment;
            });

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    public function getAssignment(Assignment $assignment)
    {
        $student = auth()->user()->student;

        $assignment->load([
            'subject',
            'teacher',
            'submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $assignment,
        ]);
    }

    public function submitAssignment(Request $request, Assignment $assignment)
    {
        $student = auth()->user()->student;

        $validated = $request->validate([
            'content' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt,zip|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignments', 'public');
        }

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'content' => $validated['content'],
                'file_path' => $filePath,
                'submitted_at' => now(),
                'status' => now() > $assignment->due_date ? 'late' : 'submitted',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.assignment_submitted'),
            'data' => $submission,
        ]);
    }

    // Attendance
    public function getAttendance()
    {
        $student = auth()->user()->student;

        $attendances = Attendance::where('student_id', $student->id)
            ->with('subject')
            ->latest()
            ->paginate(30);

        $stats = [
            'total' => Attendance::where('student_id', $student->id)->count(),
            'present' => Attendance::where('student_id', $student->id)->where('status', 'present')->count(),
            'absent' => Attendance::where('student_id', $student->id)->where('status', 'absent')->count(),
            'late' => Attendance::where('student_id', $student->id)->where('status', 'late')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $attendances,
            'stats' => $stats,
        ]);
    }

    // Gamification
    public function getMyBadges()
    {
        $student = auth()->user()->student;

        $badges = StudentBadge::where('student_id', $student->id)
            ->with('badge')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $badges,
        ]);
    }

    public function getMyPoints()
    {
        $student = auth()->user()->student;

        return response()->json([
            'success' => true,
            'data' => [
                'points' => $student->points ?? 0,
                'level' => $student->level ?? 1,
                'rank' => $student->rank ?? 0,
            ],
        ]);
    }

    public function getLeaderboard()
    {
        $leaderboard = Student::where('school_id', auth()->user()->school_id)
            ->orderBy('points', 'desc')
            ->take(50)
            ->get(['id', 'first_name', 'last_name', 'points', 'level'])
            ->map(function ($student, $index) {
                $student->rank = $index + 1;
                return $student;
            });

        return response()->json([
            'success' => true,
            'data' => $leaderboard,
        ]);
    }

    public function getAchievements()
    {
        $student = auth()->user()->student;

        $achievements = [
            'total_badges' => StudentBadge::where('student_id', $student->id)->count(),
            'assignments_completed' => AssignmentSubmission::where('student_id', $student->id)
                ->where('status', 'graded')
                ->count(),
            'perfect_attendance_days' => Attendance::where('student_id', $student->id)
                ->where('status', 'present')
                ->count(),
            'average_grade' => Grade::where('student_id', $student->id)->avg('grade') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    // Library
    public function getMyLoans()
    {
        $student = auth()->user()->student;

        $loans = BookLoan::where('student_id', $student->id)
            ->with('book')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $loans,
        ]);
    }

    public function searchBooks(Request $request)
    {
        $query = $request->input('query');

        $books = Book::where('school_id', auth()->user()->school_id)
            ->when($query, function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('author', 'like', "%{$query}%")
                    ->orWhere('isbn', 'like', "%{$query}%");
            })
            ->where('status', 'available')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $books,
        ]);
    }

    public function scanBook(Request $request, Book $book)
    {
        $student = auth()->user()->student;

        $validated = $request->validate([
            'action' => 'required|in:borrow,return',
        ]);

        if ($validated['action'] === 'borrow') {
            if ($book->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.book_not_available'),
                ], 400);
            }

            $loan = BookLoan::create([
                'book_id' => $book->id,
                'student_id' => $student->id,
                'borrowed_at' => now(),
                'due_date' => now()->addDays(14),
                'status' => 'borrowed',
            ]);

            $book->update(['status' => 'borrowed']);

            return response()->json([
                'success' => true,
                'message' => __('messages.book_borrowed'),
                'data' => $loan,
            ]);
        } else {
            $loan = BookLoan::where('book_id', $book->id)
                ->where('student_id', $student->id)
                ->where('status', 'borrowed')
                ->firstOrFail();

            $loan->update([
                'returned_at' => now(),
                'status' => 'returned',
            ]);

            $book->update(['status' => 'available']);

            return response()->json([
                'success' => true,
                'message' => __('messages.book_returned'),
                'data' => $loan,
            ]);
        }
    }

    // Transport
    public function getTransport()
    {
        $student = auth()->user()->student;

        $transport = $student->busRoute()->with('bus')->first();

        return response()->json([
            'success' => true,
            'data' => $transport,
        ]);
    }

    public function trackMyBus()
    {
        $student = auth()->user()->student;
        $busRoute = $student->busRoute;

        if (!$busRoute) {
            return response()->json([
                'success' => false,
                'message' => __('messages.no_bus_assigned'),
            ], 404);
        }

        $bus = $busRoute->bus;

        return response()->json([
            'success' => true,
            'data' => [
                'bus' => $bus,
                'route' => $busRoute,
                'latitude' => $bus->current_latitude,
                'longitude' => $bus->current_longitude,
                'last_update' => $bus->location_updated_at,
            ],
        ]);
    }

    // Canteen
    public function getMenus()
    {
        $menus = CanteenMenu::where('school_id', auth()->user()->school_id)
            ->where('date', '>=', now()->startOfWeek())
            ->where('date', '<=', now()->endOfWeek())
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $menus,
        ]);
    }

    public function getMyReservations()
    {
        $student = auth()->user()->student;

        $reservations = MealReservation::where('student_id', $student->id)
            ->with('menu')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    // Events
    public function getEvents()
    {
        $events = Event::where('school_id', auth()->user()->school_id)
            ->where('date', '>=', now())
            ->with('createdBy')
            ->latest('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    // Messages
    public function getConversations()
    {
        $conversations = Conversation::whereHas('participants', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['participants.user', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->latest('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    public function getMessages(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with('sender')
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $this->authorize('participate', $conversation);

        $validated = $request->validate([
            'content' => 'required|string',
            'type' => 'nullable|in:text,file,image',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'content' => $validated['content'],
            'type' => $validated['type'] ?? 'text',
        ]);

        $conversation->touch();

        return response()->json([
            'success' => true,
            'message' => __('messages.message_sent'),
            'data' => $message->load('sender'),
        ]);
    }

    // Documents
    public function getDocuments()
    {
        $student = auth()->user()->student;

        $documents = Document::where('student_id', $student->id)
            ->with('uploadedBy')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    // Notifications
    public function getNotifications()
    {
        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }
}
