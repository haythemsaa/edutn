<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\Attendance;
use App\Models\Timetable;
use App\Models\Assignment;
use App\Models\DisciplineIncident;
use App\Models\Bus;
use App\Models\CanteenMenu;
use App\Models\MealReservation;
use App\Models\Document;
use App\Models\Certificate;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Appointment;
use App\Models\Event;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
{
    // Children Management
    public function getChildren()
    {
        $children = auth()->user()->children()
            ->with(['classSection', 'school'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $children,
        ]);
    }

    public function getChildDetails(Student $student)
    {
        $this->authorize('view', $student);

        $student->load(['classSection', 'school', 'grades.subject', 'attendances']);

        return response()->json([
            'success' => true,
            'data' => $student,
        ]);
    }

    // Grades & Report Cards
    public function getGrades(Student $student)
    {
        $this->authorize('view', $student);

        $grades = $student->grades()
            ->with(['subject', 'teacher'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $grades,
        ]);
    }

    public function getReportCards(Student $student)
    {
        $this->authorize('view', $student);

        $reportCards = ReportCard::where('student_id', $student->id)
            ->with('classSection')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reportCards,
        ]);
    }

    public function getReportCard(Student $student, $term)
    {
        $this->authorize('view', $student);

        $reportCard = ReportCard::where('student_id', $student->id)
            ->where('term', $term)
            ->with(['classSection', 'grades.subject'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $reportCard,
        ]);
    }

    // Attendance
    public function getAttendance(Student $student)
    {
        $this->authorize('view', $student);

        $attendances = $student->attendances()
            ->with('subject')
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    public function justifyAbsence(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'justification' => 'required|string',
            'proof' => 'nullable|file|max:5120',
        ]);

        $attendance = Attendance::findOrFail($validated['attendance_id']);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('justifications', 'public');
        }

        $attendance->update([
            'justification' => $validated['justification'],
            'justification_proof' => $proofPath,
            'justified' => true,
            'justified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.absence_justified'),
        ]);
    }

    // Timetable
    public function getTimetable(Student $student)
    {
        $this->authorize('view', $student);

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

    // Assignments
    public function getAssignments(Student $student)
    {
        $this->authorize('view', $student);

        $assignments = Assignment::where('class_section_id', $student->class_section_id)
            ->with(['subject', 'teacher', 'submissions' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assignments,
        ]);
    }

    public function getAssignment(Assignment $assignment)
    {
        $assignment->load(['subject', 'teacher', 'submissions']);

        return response()->json([
            'success' => true,
            'data' => $assignment,
        ]);
    }

    // Discipline
    public function getDiscipline(Student $student)
    {
        $this->authorize('view', $student);

        $incidents = DisciplineIncident::where('student_id', $student->id)
            ->with(['reportedBy', 'sanction'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $incidents,
        ]);
    }

    // Transport
    public function getTransport(Student $student)
    {
        $this->authorize('view', $student);

        $transport = $student->busRoute()->with('bus')->first();

        return response()->json([
            'success' => true,
            'data' => $transport,
        ]);
    }

    public function trackBus(Bus $bus)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'bus' => $bus,
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

    public function getMealReservations(Student $student)
    {
        $this->authorize('view', $student);

        $reservations = MealReservation::where('student_id', $student->id)
            ->with('menu')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reservations,
        ]);
    }

    // Documents
    public function getDocuments(Student $student)
    {
        $this->authorize('view', $student);

        $documents = Document::where('student_id', $student->id)
            ->with('uploadedBy')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
        ]);
    }

    public function downloadDocument(Document $document)
    {
        $this->authorize('view', $document);

        return response()->download(storage_path('app/public/' . $document->file_path));
    }

    // Certificates
    public function getCertificates(Student $student)
    {
        $this->authorize('view', $student);

        $certificates = Certificate::where('student_id', $student->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $certificates,
        ]);
    }

    public function requestCertificate(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'type' => 'required|in:enrollment,conduct,achievement,completion',
            'purpose' => 'nullable|string',
        ]);

        $certificate = Certificate::create([
            'student_id' => $student->id,
            'school_id' => $student->school_id,
            'type' => $validated['type'],
            'purpose' => $validated['purpose'] ?? null,
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.certificate_requested'),
            'data' => $certificate,
        ]);
    }

    // Invoices & Payments
    public function getInvoices()
    {
        $invoices = Invoice::whereIn('student_id', auth()->user()->children->pluck('id'))
            ->with('student')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    public function payInvoice(Request $request, Invoice $invoice)
    {
        $this->authorize('pay', $invoice);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,card,transfer,online',
            'transaction_id' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'student_id' => $invoice->student_id,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'transaction_id' => $validated['transaction_id'] ?? null,
                'paid_by' => auth()->id(),
                'school_id' => $invoice->school_id,
            ]);

            // Update invoice
            $invoice->paid_amount += $validated['amount'];
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'partial';
            }
            $invoice->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('messages.payment_success'),
                'data' => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('messages.payment_error'),
            ], 500);
        }
    }

    public function getPayments()
    {
        $payments = Payment::whereIn('student_id', auth()->user()->children->pluck('id'))
            ->with(['invoice', 'student'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    // Appointments
    public function getAppointments()
    {
        $appointments = Appointment::where('requested_by', auth()->id())
            ->orWhereHas('participants', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['requestedBy', 'withUser', 'participants'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $appointments,
        ]);
    }

    public function requestAppointment(Request $request)
    {
        $validated = $request->validate([
            'with_user_id' => 'required|exists:users,id',
            'student_id' => 'nullable|exists:students,id',
            'date' => 'required|date|after:now',
            'purpose' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['requested_by'] = auth()->id();
        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = 'pending';

        $appointment = Appointment::create($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.appointment_requested'),
            'data' => $appointment,
        ]);
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return response()->json([
            'success' => true,
            'message' => __('messages.appointment_updated'),
            'data' => $appointment,
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

    public function registerEvent(Request $request, Event $event)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        if (!$event->participants()->where('student_id', $validated['student_id'])->exists()) {
            $event->participants()->create([
                'student_id' => $validated['student_id'],
                'registered_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.event_registered'),
        ]);
    }

    // Conversations & Messages
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
                'type' => 'parent_admin',
                'school_id' => auth()->user()->school_id,
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
            'type' => 'nullable|in:text,file,image,video,audio',
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

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id === auth()->id()) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.notification_read'),
        ]);
    }
}
