<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::where('school_id', auth()->user()->school_id)
            ->with(['teacher', 'student', 'parent']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('scheduled_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('scheduled_at', '<=', $request->end_date);
        }

        // Show only upcoming appointments
        if ($request->boolean('upcoming_only')) {
            $query->where('scheduled_at', '>=', now())
                ->whereIn('status', ['pending', 'confirmed']);
        }

        $appointments = $query->orderBy('scheduled_at', 'desc')->paginate(20);

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')->get();
        $students = Student::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')->get();

        return view('appointments.create', compact('teachers', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'student_id' => 'nullable|exists:students,id',
            'parent_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'type' => 'required|in:parent_teacher,academic_review,behavior_discussion,progress_update,general,other',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url',
            'meeting_type' => 'nullable|in:in_person,online,phone',
            'notes' => 'nullable|string',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = 'pending';
        $validated['duration_minutes'] = $validated['duration_minutes'] ?? 30;
        $validated['meeting_type'] = $validated['meeting_type'] ?? 'in_person';

        // Auto-assign parent if student is selected
        if ($request->filled('student_id') && !$request->filled('parent_id')) {
            $student = Student::find($request->student_id);
            if ($student && $student->parent_id) {
                $validated['parent_id'] = $student->parent_id;
            }
        }

        $appointment = Appointment::create($validated);

        // Send notification to participants
        // event(new AppointmentCreated($appointment));

        return redirect()->route('appointments.show', $appointment)
            ->with('success', __('messages.appointment_created'));
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        $appointment->load(['teacher', 'student', 'parent']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        // Only allow editing pending or confirmed appointments
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->with('error', __('messages.cannot_edit_completed_appointment'));
        }

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')->get();
        $students = Student::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')->get();

        return view('appointments.edit', compact('appointment', 'teachers', 'students'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        // Only allow updating pending or confirmed appointments
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->with('error', __('messages.cannot_edit_completed_appointment'));
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'student_id' => 'nullable|exists:students,id',
            'parent_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'type' => 'required|in:parent_teacher,academic_review,behavior_discussion,progress_update,general,other',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:15|max:180',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url',
            'meeting_type' => 'nullable|in:in_person,online,phone',
            'notes' => 'nullable|string',
        ]);

        $appointment->update($validated);

        // Send notification about changes
        // event(new AppointmentUpdated($appointment));

        return redirect()->route('appointments.show', $appointment)
            ->with('success', __('messages.appointment_updated'));
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        // Only allow deleting pending appointments
        if ($appointment->status !== 'pending') {
            return back()->with('error', __('messages.cannot_delete_confirmed_appointment'));
        }

        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', __('messages.appointment_deleted'));
    }

    public function confirm(Appointment $appointment)
    {
        $this->authorize('confirm', $appointment);

        if ($appointment->status !== 'pending') {
            return back()->with('error', __('messages.appointment_already_processed'));
        }

        $appointment->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // Send confirmation notification
        // event(new AppointmentConfirmed($appointment));

        return back()->with('success', __('messages.appointment_confirmed'));
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->with('error', __('messages.appointment_already_processed'));
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        $appointment->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
        ]);

        // Send cancellation notification
        // event(new AppointmentCancelled($appointment));

        return back()->with('success', __('messages.appointment_cancelled'));
    }

    public function complete(Request $request, Appointment $appointment)
    {
        $this->authorize('complete', $appointment);

        if ($appointment->status !== 'confirmed') {
            return back()->with('error', __('messages.appointment_not_confirmed'));
        }

        $validated = $request->validate([
            'outcome' => 'nullable|string',
        ]);

        $appointment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'outcome' => $validated['outcome'] ?? null,
        ]);

        return back()->with('success', __('messages.appointment_completed'));
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return back()->with('error', __('messages.cannot_reschedule_appointment'));
        }

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $appointment->update([
            'scheduled_at' => $validated['scheduled_at'],
            'notes' => $validated['notes'] ?? $appointment->notes,
            'status' => 'pending', // Reset to pending after rescheduling
            'confirmed_at' => null,
        ]);

        // Send rescheduling notification
        // event(new AppointmentRescheduled($appointment));

        return back()->with('success', __('messages.appointment_rescheduled'));
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $appointments = Appointment::where('school_id', auth()->user()->school_id)
            ->whereYear('scheduled_at', $year)
            ->whereMonth('scheduled_at', $month)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with(['teacher', 'student', 'parent'])
            ->orderBy('scheduled_at', 'asc')
            ->get();

        return view('appointments.calendar', compact('appointments', 'month', 'year'));
    }

    public function myAppointments(Request $request)
    {
        $user = auth()->user();
        $query = Appointment::where('school_id', $user->school_id);

        // Filter based on user role
        if ($user->hasRole('teacher')) {
            $query->where('teacher_id', $user->teacher->id);
        } elseif ($user->hasRole('parent')) {
            $query->where('parent_id', $user->id);
        }

        // Filter by upcoming or past
        if ($request->get('filter') === 'upcoming') {
            $query->where('scheduled_at', '>=', now())
                ->whereIn('status', ['pending', 'confirmed']);
        } elseif ($request->get('filter') === 'past') {
            $query->where(function ($q) {
                $q->where('scheduled_at', '<', now())
                  ->orWhereIn('status', ['completed', 'cancelled']);
            });
        }

        $appointments = $query->with(['teacher', 'student', 'parent'])
            ->orderBy('scheduled_at', 'desc')
            ->paginate(15);

        return view('appointments.my-appointments', compact('appointments'));
    }

    public function availableSlots(Request $request, Teacher $teacher)
    {
        $date = $request->get('date', now()->toDateString());

        // Get existing appointments for this teacher on this date
        $existingAppointments = Appointment::where('teacher_id', $teacher->id)
            ->whereDate('scheduled_at', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['scheduled_at', 'duration_minutes']);

        // Generate available slots (e.g., 8:00 AM to 5:00 PM, 30-minute slots)
        $slots = [];
        $start = 8; // 8 AM
        $end = 17; // 5 PM

        for ($hour = $start; $hour < $end; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                $slotTime = sprintf('%02d:%02d', $hour, $minute);
                $slotDateTime = $date . ' ' . $slotTime;

                // Check if slot is available
                $isAvailable = true;
                foreach ($existingAppointments as $existing) {
                    $existingStart = $existing->scheduled_at;
                    $existingEnd = $existingStart->copy()->addMinutes($existing->duration_minutes);
                    $currentSlot = \Carbon\Carbon::parse($slotDateTime);

                    if ($currentSlot->between($existingStart, $existingEnd)) {
                        $isAvailable = false;
                        break;
                    }
                }

                $slots[] = [
                    'time' => $slotTime,
                    'datetime' => $slotDateTime,
                    'available' => $isAvailable,
                ];
            }
        }

        return response()->json(['slots' => $slots]);
    }
}
