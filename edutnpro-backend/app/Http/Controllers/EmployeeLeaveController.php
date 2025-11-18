<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeave;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeLeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeLeave::where('school_id', auth()->user()->school_id)
            ->with(['teacher', 'approvedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by leave type
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Show only pending for approval dashboard
        if ($request->boolean('pending_only')) {
            $query->where('status', 'pending');
        }

        $leaves = $query->orderBy('start_date', 'desc')->paginate(20);

        return view('employee-leaves.index', compact('leaves'));
    }

    public function create()
    {
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('employee-leaves.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'leave_type' => 'required|in:annual,sick,maternity,paternity,unpaid,emergency,bereavement,study',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'reason_ar' => 'nullable|string',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'is_paid' => 'nullable|boolean',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = 'pending';

        // Calculate total days
        $leave = new EmployeeLeave($validated);
        $validated['total_days'] = $leave->calculateTotalDays();

        // Handle medical certificate upload
        if ($request->hasFile('medical_certificate')) {
            $validated['medical_certificate'] = $request->file('medical_certificate')
                ->store('leaves/medical', 'public');
        }

        // Handle supporting documents
        if ($request->hasFile('supporting_documents')) {
            $documents = [];
            foreach ($request->file('supporting_documents') as $file) {
                $documents[] = $file->store('leaves/documents', 'public');
            }
            $validated['supporting_documents'] = $documents;
        }

        // Set is_paid based on leave type
        if (!isset($validated['is_paid'])) {
            $paidTypes = ['annual', 'sick', 'maternity', 'paternity', 'bereavement'];
            $validated['is_paid'] = in_array($validated['leave_type'], $paidTypes);
        }

        $leave = EmployeeLeave::create($validated);

        return redirect()->route('employee-leaves.show', $leave)
            ->with('success', __('messages.leave_request_created'));
    }

    public function show(EmployeeLeave $employeeLeave)
    {
        $this->authorize('view', $employeeLeave);
        $employeeLeave->load(['teacher', 'approvedBy']);
        return view('employee-leaves.show', compact('employeeLeave'));
    }

    public function edit(EmployeeLeave $employeeLeave)
    {
        $this->authorize('update', $employeeLeave);

        // Only allow editing pending requests
        if ($employeeLeave->status !== 'pending') {
            return back()->with('error', __('messages.cannot_edit_processed_leave'));
        }

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('employee-leaves.edit', compact('employeeLeave', 'teachers'));
    }

    public function update(Request $request, EmployeeLeave $employeeLeave)
    {
        $this->authorize('update', $employeeLeave);

        // Only allow updating pending requests
        if ($employeeLeave->status !== 'pending') {
            return back()->with('error', __('messages.cannot_edit_processed_leave'));
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'leave_type' => 'required|in:annual,sick,maternity,paternity,unpaid,emergency,bereavement,study',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'reason_ar' => 'nullable|string',
            'medical_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'supporting_documents' => 'nullable|array',
            'supporting_documents.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'is_paid' => 'nullable|boolean',
        ]);

        // Recalculate total days
        $tempLeave = new EmployeeLeave($validated);
        $validated['total_days'] = $tempLeave->calculateTotalDays();

        // Handle medical certificate upload
        if ($request->hasFile('medical_certificate')) {
            if ($employeeLeave->medical_certificate) {
                Storage::disk('public')->delete($employeeLeave->medical_certificate);
            }
            $validated['medical_certificate'] = $request->file('medical_certificate')
                ->store('leaves/medical', 'public');
        }

        // Handle supporting documents
        if ($request->hasFile('supporting_documents')) {
            if ($employeeLeave->supporting_documents) {
                foreach ($employeeLeave->supporting_documents as $doc) {
                    Storage::disk('public')->delete($doc);
                }
            }
            $documents = [];
            foreach ($request->file('supporting_documents') as $file) {
                $documents[] = $file->store('leaves/documents', 'public');
            }
            $validated['supporting_documents'] = $documents;
        }

        $employeeLeave->update($validated);

        return redirect()->route('employee-leaves.show', $employeeLeave)
            ->with('success', __('messages.leave_request_updated'));
    }

    public function destroy(EmployeeLeave $employeeLeave)
    {
        $this->authorize('delete', $employeeLeave);

        // Only allow deleting pending or rejected requests
        if (!in_array($employeeLeave->status, ['pending', 'rejected'])) {
            return back()->with('error', __('messages.cannot_delete_approved_leave'));
        }

        // Delete associated files
        if ($employeeLeave->medical_certificate) {
            Storage::disk('public')->delete($employeeLeave->medical_certificate);
        }
        if ($employeeLeave->supporting_documents) {
            foreach ($employeeLeave->supporting_documents as $doc) {
                Storage::disk('public')->delete($doc);
            }
        }

        $employeeLeave->delete();

        return redirect()->route('employee-leaves.index')
            ->with('success', __('messages.leave_request_deleted'));
    }

    public function approve(Request $request, EmployeeLeave $employeeLeave)
    {
        $this->authorize('approve', $employeeLeave);

        if ($employeeLeave->status !== 'pending') {
            return back()->with('error', __('messages.leave_already_processed'));
        }

        $validated = $request->validate([
            'approval_notes' => 'nullable|string',
        ]);

        $employeeLeave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return back()->with('success', __('messages.leave_approved'));
    }

    public function reject(Request $request, EmployeeLeave $employeeLeave)
    {
        $this->authorize('approve', $employeeLeave);

        if ($employeeLeave->status !== 'pending') {
            return back()->with('error', __('messages.leave_already_processed'));
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $employeeLeave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', __('messages.leave_rejected'));
    }

    public function cancel(EmployeeLeave $employeeLeave)
    {
        $this->authorize('update', $employeeLeave);

        // Only allow cancelling approved leaves that haven't started yet
        if ($employeeLeave->status !== 'approved' || $employeeLeave->start_date->lt(now())) {
            return back()->with('error', __('messages.cannot_cancel_leave'));
        }

        $employeeLeave->update(['status' => 'cancelled']);

        return back()->with('success', __('messages.leave_cancelled'));
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $leaves = EmployeeLeave::where('school_id', auth()->user()->school_id)
            ->where('status', 'approved')
            ->whereYear('start_date', '<=', $year)
            ->whereYear('end_date', '>=', $year)
            ->whereMonth('start_date', '<=', $month)
            ->whereMonth('end_date', '>=', $month)
            ->with('teacher')
            ->get();

        return view('employee-leaves.calendar', compact('leaves', 'month', 'year'));
    }

    public function leaveBalance(Teacher $teacher)
    {
        $this->authorize('viewBalance', EmployeeLeave::class);

        $currentYear = now()->year;

        // Calculate annual leave used
        $annualUsed = EmployeeLeave::where('teacher_id', $teacher->id)
            ->where('leave_type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        // Calculate sick leave used
        $sickUsed = EmployeeLeave::where('teacher_id', $teacher->id)
            ->where('leave_type', 'sick')
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        // Default allowances (can be customized per teacher)
        $annualAllowance = 30; // 30 days per year
        $sickAllowance = 15;   // 15 days per year

        $balance = [
            'annual' => [
                'allowance' => $annualAllowance,
                'used' => $annualUsed,
                'remaining' => $annualAllowance - $annualUsed,
            ],
            'sick' => [
                'allowance' => $sickAllowance,
                'used' => $sickUsed,
                'remaining' => $sickAllowance - $sickUsed,
            ],
        ];

        return view('employee-leaves.balance', compact('teacher', 'balance'));
    }

    public function downloadDocument(EmployeeLeave $employeeLeave, $type)
    {
        $this->authorize('view', $employeeLeave);

        $path = null;
        if ($type === 'medical' && $employeeLeave->medical_certificate) {
            $path = $employeeLeave->medical_certificate;
        }

        if (!$path || !Storage::disk('public')->exists($path)) {
            return back()->with('error', __('messages.file_not_found'));
        }

        return Storage::disk('public')->download($path);
    }
}
