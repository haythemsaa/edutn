<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassSection;
use App\Models\Subject;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Assignment::with(['classSection', 'subject', 'teacher'])
            ->where('school_id', $user->school_id);

        if ($user->hasRole('teacher')) {
            $query->where('teacher_id', $user->teacher->id);
        }

        $assignments = $query->latest()->paginate(20);

        return view('assignments.index', compact('assignments'));
    }

    public function create()
    {
        $classSections = ClassSection::where('school_id', auth()->user()->school_id)->get();
        $subjects = Subject::where('school_id', auth()->user()->school_id)->get();

        return view('assignments.create', compact('classSections', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date|after:now',
            'total_points' => 'nullable|numeric|min:0',
        ]);

        $validated['teacher_id'] = auth()->user()->teacher->id ?? null;
        $validated['school_id'] = auth()->user()->school_id;

        $assignment = Assignment::create($validated);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', __('messages.assignment_created'));
    }

    public function show(Assignment $assignment)
    {
        $this->authorize('view', $assignment);

        $assignment->load(['classSection', 'subject', 'teacher']);

        $submissions = $assignment->submissions()
            ->with('student')
            ->latest()
            ->paginate(50);

        $stats = [
            'total_students' => $assignment->classSection->students()->count(),
            'submitted' => $assignment->submissions()->count(),
            'graded' => $assignment->submissions()->where('status', 'graded')->count(),
            'pending' => $assignment->submissions()->where('status', 'submitted')->count(),
        ];

        return view('assignments.show', compact('assignment', 'submissions', 'stats'));
    }

    public function edit(Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $classSections = ClassSection::where('school_id', auth()->user()->school_id)->get();
        $subjects = Subject::where('school_id', auth()->user()->school_id)->get();

        return view('assignments.edit', compact('assignment', 'classSections', 'subjects'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'total_points' => 'nullable|numeric|min:0',
        ]);

        $assignment->update($validated);

        return redirect()->route('assignments.show', $assignment)
            ->with('success', __('messages.assignment_updated'));
    }

    public function destroy(Assignment $assignment)
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', __('messages.assignment_deleted'));
    }

    public function submissions(Assignment $assignment)
    {
        $this->authorize('view', $assignment);

        $submissions = $assignment->submissions()
            ->with('student')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $submissions,
        ]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt,zip|max:10240',
        ]);

        $student = auth()->user()->student;

        if (!$student) {
            return back()->with('error', __('messages.student_not_found'));
        }

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

        return back()->with('success', __('messages.assignment_submitted'));
    }
}
