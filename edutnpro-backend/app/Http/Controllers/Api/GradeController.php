<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::with(['student', 'subject', 'term', 'teacher']);

        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        $grades = $query->latest()->paginate(50);

        return response()->json($grades);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'term_id' => 'required|exists:terms,id',
            'grade_type' => 'required|in:continuous,exam,final',
            'score' => 'required|numeric|min:0|max:20',
            'date' => 'required|date',
            'comment' => 'nullable|string',
        ]);

        $grade = Grade::create(array_merge($validated, [
            'teacher_id' => auth()->user()->teacher?->id,
            'max_score' => 20.00,
        ]));

        return response()->json($grade->load(['student', 'subject', 'term']), 201);
    }

    public function show(Grade $grade)
    {
        $grade->load(['student', 'subject', 'term', 'teacher']);
        return response()->json($grade);
    }
}