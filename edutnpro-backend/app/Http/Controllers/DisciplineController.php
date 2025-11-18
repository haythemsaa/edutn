<?php

namespace App\Http\Controllers;

use App\Models\DisciplineIncident;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DisciplineController extends Controller
{
    public function index()
    {
        $incidents = DisciplineIncident::where('school_id', auth()->user()->school_id)
            ->with(['student', 'reportedBy', 'sanctions'])
            ->orderBy('incident_date', 'desc')
            ->orderBy('incident_time', 'desc')
            ->paginate(20);

        return view('discipline.index', compact('incidents'));
    }

    public function create()
    {
        $students = Student::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('discipline.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'incident_type' => 'required|in:behavioral,academic,attendance,violence,bullying,theft,vandalism,insubordination,other',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'severity' => 'required|in:minor,moderate,serious,critical',
            'witnesses' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'action_taken_ar' => 'nullable|string',
            'status' => 'nullable|in:reported,investigating,resolved,closed',
            'parent_notified' => 'nullable|boolean',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['reported_by'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'reported';
        $validated['parent_notified'] = $request->boolean('parent_notified');

        if ($validated['parent_notified']) {
            $validated['parent_notified_at'] = now();
        }

        $incident = DisciplineIncident::create($validated);

        return redirect()->route('discipline.show', $incident)
            ->with('success', __('messages.incident_created'));
    }

    public function show(DisciplineIncident $discipline)
    {
        $this->authorize('view', $discipline);

        $discipline->load(['student', 'reportedBy', 'sanctions']);

        return view('discipline.show', compact('discipline'));
    }

    public function edit(DisciplineIncident $discipline)
    {
        $this->authorize('update', $discipline);

        $students = Student::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('discipline.edit', compact('discipline', 'students'));
    }

    public function update(Request $request, DisciplineIncident $discipline)
    {
        $this->authorize('update', $discipline);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'title' => 'required|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'nullable|string',
            'incident_type' => 'required|in:behavioral,academic,attendance,violence,bullying,theft,vandalism,insubordination,other',
            'incident_date' => 'required|date',
            'incident_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'severity' => 'required|in:minor,moderate,serious,critical',
            'witnesses' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'action_taken_ar' => 'nullable|string',
            'status' => 'nullable|in:reported,investigating,resolved,closed',
            'parent_notified' => 'nullable|boolean',
        ]);

        $validated['parent_notified'] = $request->boolean('parent_notified');

        if ($validated['parent_notified'] && !$discipline->parent_notified) {
            $validated['parent_notified_at'] = now();
        }

        $discipline->update($validated);

        return redirect()->route('discipline.show', $discipline)
            ->with('success', __('messages.incident_updated'));
    }

    public function destroy(DisciplineIncident $discipline)
    {
        $this->authorize('delete', $discipline);

        $discipline->delete();

        return redirect()->route('discipline.index')
            ->with('success', __('messages.incident_deleted'));
    }

    public function studentHistory($studentId)
    {
        $student = Student::findOrFail($studentId);
        $this->authorize('view', $student);

        $incidents = DisciplineIncident::where('student_id', $studentId)
            ->with(['reportedBy', 'sanctions'])
            ->orderBy('incident_date', 'desc')
            ->get();

        return view('discipline.student-history', compact('student', 'incidents'));
    }

    public function statistics()
    {
        $schoolId = auth()->user()->school_id;

        $stats = [
            'total' => DisciplineIncident::where('school_id', $schoolId)->count(),
            'by_severity' => DisciplineIncident::where('school_id', $schoolId)
                ->selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity'),
            'by_type' => DisciplineIncident::where('school_id', $schoolId)
                ->selectRaw('incident_type, COUNT(*) as count')
                ->groupBy('incident_type')
                ->pluck('count', 'incident_type'),
            'by_status' => DisciplineIncident::where('school_id', $schoolId)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'recent' => DisciplineIncident::where('school_id', $schoolId)
                ->where('incident_date', '>=', now()->subDays(30))
                ->count(),
        ];

        return view('discipline.statistics', compact('stats'));
    }
}
