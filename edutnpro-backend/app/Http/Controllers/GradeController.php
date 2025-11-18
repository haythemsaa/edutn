<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::with(['student', 'subject', 'term'])->latest()->paginate(20);
        return view('grades.index', compact('grades'));
    }

    public function create()
    {
        $students = Student::where('status', 'active')->get();
        $subjects = Subject::where('is_active', true)->get();
        $terms = Term::all();
        return view('grades.create', compact('students', 'subjects', 'terms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'term_id' => 'required|exists:terms,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'grade_type' => 'required|in:continuous,exam,final',
            'score' => 'required|numeric|min:0|max:20',
            'max_score' => 'required|numeric|min:0',
            'date' => 'required|date',
            'comment' => 'nullable|string',
        ]);

        // Set teacher_id to current user's teacher record if not provided
        if (!$validated['teacher_id'] && auth()->user()->teacher) {
            $validated['teacher_id'] = auth()->user()->teacher->id;
        }

        Grade::create($validated);

        return redirect()->route('grades.index')->with('success', 'Note ajoutée avec succès!');
    }

    public function show(Grade $grade)
    {
        $grade->load(['student', 'subject', 'term', 'teacher']);
        return view('grades.show', compact('grade'));
    }

    public function edit(Grade $grade)
    {
        $students = Student::where('status', 'active')->get();
        $subjects = Subject::where('is_active', true)->get();
        $terms = Term::all();
        return view('grades.edit', compact('grade', 'students', 'subjects', 'terms'));
    }

    public function update(Request $request, Grade $grade)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'term_id' => 'required|exists:terms,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'grade_type' => 'required|in:continuous,exam,final',
            'score' => 'required|numeric|min:0|max:20',
            'max_score' => 'required|numeric|min:0',
            'date' => 'required|date',
            'comment' => 'nullable|string',
        ]);

        // Set teacher_id to current user's teacher record if not provided
        if (!$validated['teacher_id'] && auth()->user()->teacher) {
            $validated['teacher_id'] = auth()->user()->teacher->id;
        }

        $grade->update($validated);

        return redirect()->route('grades.index')->with('success', 'Note modifiée avec succès!');
    }

    public function destroy(Grade $grade)
    {
        try {
            $grade->delete();
            return redirect()->route('grades.index')->with('success', 'Note supprimée avec succès!');
        } catch (\Exception $e) {
            return redirect()->route('grades.index')->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}
