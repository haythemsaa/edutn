<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Attendance;
use App\Models\Timetable;
use App\Models\ClassDiary;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\OnlineClass;
use App\Models\DisciplineIncident;
use App\Models\Sanction;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Appointment;
use App\Models\EmployeeContract;
use App\Models\EmployeeLeave;
use App\Models\Payroll;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherApiController extends Controller
{
    // Profile
    public function getProfile()
    {
        $teacher = auth()->user()->teacher()
            ->with(['school', 'subjects', 'classSections'])
            ->first();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $teacher,
        ]);
    }

    // Classes
    public function getMyClasses()
    {
        $teacher = auth()->user()->teacher;

        $classes = ClassSection::whereHas('teachers', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->withCount('students')
            ->with('classLevel')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    public function getClassDetails(ClassSection $classSection)
    {
        $classSection->load(['classLevel', 'students', 'timetables']);

        return response()->json([
            'success' => true,
            'data' => $classSection,
        ]);
    }

    public function getClassStudents(ClassSection $classSection)
    {
        $students = $classSection->students()->with('parents')->get();

        return response()->json([
            'success' => true,
            'data' => $students,
        ]);
    }

    // Timetable
    public function getMyTimetable()
    {
        $teacher = auth()->user()->teacher;

        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->with(['subject', 'classSection', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return response()->json([
            'success' => true,
            'data' => $timetable,
        ]);
    }

    // Grades
    public function getClassGrades(ClassSection $classSection)
    {
        $teacher = auth()->user()->teacher;

        $grades = Grade::where('class_section_id', $classSection->id)
            ->where('teacher_id', $teacher->id)
            ->with(['student', 'subject'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $grades,
        ]);
    }

    public function addGrade(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'term' => 'required|in:1,2,3',
            'type' => 'required|in:exam,quiz,homework,participation',
            'grade' => 'required|numeric|min:0|max:20',
            'coefficient' => 'nullable|numeric|min:1|max:5',
            'notes' => 'nullable|string',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $validated['school_id'] = $teacher->school_id;

        $grade = Grade::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.grade_added'),
            'data' => $grade->load(['student', 'subject']),
        ]);
    }

    public function updateGrade(Request $request, Grade $grade)
    {
        $this->authorize('update', $grade);

        $validated = $request->validate([
            'grade' => 'nullable|numeric|min:0|max:20',
            'coefficient' => 'nullable|numeric|min:1|max:5',
            'notes' => 'nullable|string',
        ]);

        $grade->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.grade_updated'),
            'data' => $grade,
        ]);
    }

    public function deleteGrade(Grade $grade)
    {
        $this->authorize('delete', $grade);

        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.grade_deleted'),
        ]);
    }

    // Attendance
    public function getAttendance(ClassSection $classSection)
    {
        $attendances = Attendance::where('class_section_id', $classSection->id)
            ->with(['student', 'subject'])
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    public function takeAttendance(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['attendances'] as $attendance) {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $attendance['student_id'],
                        'class_section_id' => $validated['class_section_id'],
                        'subject_id' => $validated['subject_id'] ?? null,
                        'date' => $validated['date'],
                    ],
                    [
                        'status' => $attendance['status'],
                        'marked_by' => auth()->id(),
                        'school_id' => $teacher->school_id,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.attendance_saved'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
            ], 500);
        }
    }

    public function scanQRAttendance(Request $request)
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
        ]);

        // Decode QR data (student_id, class_section_id, timestamp)
        $data = json_decode(base64_decode($validated['qr_data']), true);

        if (!$data || !isset($data['student_id'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.invalid_qr'),
            ], 400);
        }

        $teacher = auth()->user()->teacher;

        $attendance = Attendance::create([
            'student_id' => $data['student_id'],
            'class_section_id' => $data['class_section_id'],
            'date' => now()->toDateString(),
            'status' => 'present',
            'marked_by' => auth()->id(),
            'school_id' => $teacher->school_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.attendance_marked'),
            'data' => $attendance->load('student'),
        ]);
    }

    // Class Diary
    public function getMyDiary()
    {
        $teacher = auth()->user()->teacher;

        $diary = ClassDiary::where('teacher_id', $teacher->id)
            ->with(['classSection', 'subject'])
            ->latest('date')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $diary,
        ]);
    }

    public function createDiaryEntry(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'lesson_title' => 'required|string|max:255',
            'content' => 'required|string',
            'homework' => 'nullable|string',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $validated['school_id'] = $teacher->school_id;

        $diary = ClassDiary::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.diary_created'),
            'data' => $diary,
        ]);
    }

    public function updateDiaryEntry(Request $request, ClassDiary $diary)
    {
        $this->authorize('update', $diary);

        $validated = $request->validate([
            'lesson_title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'homework' => 'nullable|string',
        ]);

        $diary->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.diary_updated'),
            'data' => $diary,
        ]);
    }

    // Assignments
    public function getMyAssignments()
    {
        $teacher = auth()->user()->teacher;

        $assignments = Assignment::where('teacher_id', $teacher->id)
            ->withCount(['submissions', 'submissions as graded_count' => function ($query) {
                $query->where('status', 'graded');
            }])
            ->with(['classSection', 'subject'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    public function createAssignment(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date|after:now',
            'total_points' => 'nullable|numeric|min:0',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $validated['school_id'] = $teacher->school_id;

        $assignment = Assignment::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.assignment_created'),
            'data' => $assignment,
        ]);
    }

    public function updateAssignment(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'total_points' => 'nullable|numeric|min:0',
        ]);

        $assignment->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.assignment_updated'),
            'data' => $assignment,
        ]);
    }

    public function deleteAssignment(Assignment $assignment)
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.assignment_deleted'),
        ]);
    }

    public function getSubmissions(Assignment $assignment)
    {
        $submissions = $assignment->submissions()
            ->with('student')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $submissions,
        ]);
    }

    public function gradeSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'points' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'points' => $validated['points'],
            'feedback' => $validated['feedback'] ?? null,
            'status' => 'graded',
            'graded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.submission_graded'),
            'data' => $submission,
        ]);
    }

    // Online Classes
    public function getOnlineClasses()
    {
        $teacher = auth()->user()->teacher;

        $classes = OnlineClass::where('teacher_id', $teacher->id)
            ->with(['classSection', 'subject'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    public function createOnlineClass(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration' => 'required|integer|min:15',
            'platform' => 'required|in:zoom,meet,teams,jitsi',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $validated['school_id'] = $teacher->school_id;
        $validated['meeting_link'] = $this->generateMeetingLink($validated['platform']);
        $validated['status'] = 'scheduled';

        $onlineClass = OnlineClass::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.online_class_created'),
            'data' => $onlineClass,
        ]);
    }

    public function startClass(OnlineClass $class)
    {
        $this->authorize('update', $class);

        $class->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.class_started'),
            'data' => $class,
        ]);
    }

    public function endClass(OnlineClass $class)
    {
        $this->authorize('update', $class);

        $class->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.class_ended'),
            'data' => $class,
        ]);
    }

    // Discipline
    public function getDisciplineIncidents()
    {
        $teacher = auth()->user()->teacher;

        $incidents = DisciplineIncident::where('reported_by', auth()->id())
            ->with(['student', 'sanction'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $incidents,
        ]);
    }

    public function reportIncident(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:behavior,academic,attendance,other',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'date' => 'required|date',
        ]);

        $validated['reported_by'] = auth()->id();
        $validated['school_id'] = $teacher->school_id;

        $incident = DisciplineIncident::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.incident_reported'),
            'data' => $incident,
        ]);
    }

    public function addSanction(Request $request, DisciplineIncident $incident)
    {
        $validated = $request->validate([
            'type' => 'required|in:warning,detention,suspension,expulsion',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        $validated['discipline_incident_id'] = $incident->id;
        $validated['issued_by'] = auth()->id();
        $validated['school_id'] = auth()->user()->teacher->school_id;

        $sanction = Sanction::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.sanction_added'),
            'data' => $sanction,
        ]);
    }

    // Analytics
    public function getClassAnalytics(ClassSection $classSection)
    {
        $stats = [
            'total_students' => $classSection->students()->count(),
            'average_grade' => Grade::where('class_section_id', $classSection->id)->avg('grade') ?? 0,
            'attendance_rate' => $this->calculateAttendanceRate($classSection->id),
            'assignments_completion' => $this->calculateAssignmentCompletion($classSection->id),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getStudentAnalytics(Student $student)
    {
        $stats = [
            'average_grade' => Grade::where('student_id', $student->id)->avg('grade') ?? 0,
            'attendance_rate' => $this->calculateStudentAttendanceRate($student->id),
            'assignments_completed' => AssignmentSubmission::where('student_id', $student->id)
                ->where('status', 'graded')
                ->count(),
            'total_assignments' => Assignment::where('class_section_id', $student->class_section_id)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Messages & Conversations
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

    public function createConversation(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'participants' => 'required|array',
            'participants.*' => 'exists:users,id',
            'message' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'subject' => $validated['subject'],
                'type' => 'group',
                'school_id' => auth()->user()->teacher->school_id,
                'created_by' => auth()->id(),
            ]);

            foreach (array_merge($validated['participants'], [auth()->id()]) as $userId) {
                $conversation->participants()->create(['user_id' => $userId]);
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'content' => $validated['message'],
                'type' => 'text',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.conversation_created'),
                'data' => $conversation->load('participants.user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('messages.error_occurred'),
            ], 500);
        }
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

    // Appointments
    public function getAppointments()
    {
        $appointments = Appointment::where('with_user_id', auth()->id())
            ->orWhere('requested_by', auth()->id())
            ->with(['requestedBy', 'withUser'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    public function confirmAppointment(Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $appointment->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'message' => __('messages.appointment_confirmed'),
            'data' => $appointment,
        ]);
    }

    // HR (Personal)
    public function getMyContracts()
    {
        $teacher = auth()->user()->teacher;

        $contracts = EmployeeContract::where('employee_id', $teacher->id)
            ->where('employee_type', 'teacher')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $contracts,
        ]);
    }

    public function getMyLeaves()
    {
        $teacher = auth()->user()->teacher;

        $leaves = EmployeeLeave::where('employee_id', $teacher->id)
            ->where('employee_type', 'teacher')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $leaves,
        ]);
    }

    public function requestLeave(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'type' => 'required|in:sick,vacation,personal,maternity,paternity',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'reason' => 'required|string',
            'document' => 'nullable|file|max:5120',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('leave-documents', 'public');
        }

        $leave = EmployeeLeave::create([
            'employee_id' => $teacher->id,
            'employee_type' => 'teacher',
            'school_id' => $teacher->school_id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.leave_requested'),
            'data' => $leave,
        ]);
    }

    public function getMyPayrolls()
    {
        $teacher = auth()->user()->teacher;

        $payrolls = Payroll::where('employee_id', $teacher->id)
            ->where('employee_type', 'teacher')
            ->latest('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payrolls,
        ]);
    }

    // Documents
    public function getDocuments()
    {
        $documents = Document::where('uploaded_by', auth()->id())
            ->orWhere(function ($query) {
                $query->whereNull('student_id'); // School-wide documents
            })
            ->with('uploadedBy')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    public function uploadDocument(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:lesson,exam,report,other',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
        ]);

        $path = $request->file('file')->store('documents', 'public');

        $document = Document::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'file_path' => $path,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_size' => $request->file('file')->getSize(),
            'uploaded_by' => auth()->id(),
            'school_id' => $teacher->school_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.document_uploaded'),
            'data' => $document,
        ]);
    }

    // Helper Methods
    private function generateMeetingLink($platform)
    {
        // This is a placeholder - integrate with actual video conferencing APIs
        return "https://{$platform}.example.com/meeting/" . uniqid();
    }

    private function calculateAttendanceRate($classSectionId)
    {
        $total = Attendance::where('class_section_id', $classSectionId)->count();
        if ($total === 0) return 0;

        $present = Attendance::where('class_section_id', $classSectionId)
            ->where('status', 'present')
            ->count();

        return round(($present / $total) * 100, 2);
    }

    private function calculateStudentAttendanceRate($studentId)
    {
        $total = Attendance::where('student_id', $studentId)->count();
        if ($total === 0) return 0;

        $present = Attendance::where('student_id', $studentId)
            ->where('status', 'present')
            ->count();

        return round(($present / $total) * 100, 2);
    }

    private function calculateAssignmentCompletion($classSectionId)
    {
        $totalAssignments = Assignment::where('class_section_id', $classSectionId)->count();
        if ($totalAssignments === 0) return 0;

        $completedSubmissions = AssignmentSubmission::whereHas('assignment', function ($query) use ($classSectionId) {
                $query->where('class_section_id', $classSectionId);
            })
            ->where('status', 'graded')
            ->count();

        $students = Student::where('class_section_id', $classSectionId)->count();
        $totalExpected = $totalAssignments * $students;

        if ($totalExpected === 0) return 0;

        return round(($completedSubmissions / $totalExpected) * 100, 2);
    }
}
