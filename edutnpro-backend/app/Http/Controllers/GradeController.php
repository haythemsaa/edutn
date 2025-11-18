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
        return redirect()->route('grades.index')->with('success', 'Fonctionnalité en cours de développement');
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
        return redirect()->route('grades.index')->with('success', 'Fonctionnalité en cours de développement');
    }

    public function destroy(Grade $grade)
    {
        return redirect()->route('grades.index')->with('success', 'Fonctionnalité en cours de développement');
    }
}
