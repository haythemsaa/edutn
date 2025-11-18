<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with(['school', 'class', 'user'])->paginate(20);
        return view('students.index', compact('students'));
    }

    public function create()
    {
        $schools = School::where('is_active', true)->get();
        $classes = ClassRoom::all();
        return view('students.create', compact('schools', 'classes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'nullable|exists:classes,id',
            'registration_number' => 'required|unique:students',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole('student');

        // Create student profile
        $student = Student::create(array_merge($validated, [
            'user_id' => $user->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]));

        return redirect()->route('students.index')->with('success', 'Élève créé avec succès!');
    }

    public function show(Student $student)
    {
        $student->load(['school', 'class', 'user', 'grades.subject']);
        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $schools = School::where('is_active', true)->get();
        $classes = ClassRoom::all();
        return view('students.edit', compact('student', 'schools', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'nullable|exists:classes,id',
            'status' => 'required|in:active,inactive,transferred,graduated',
        ]);

        $student->update($validated);
        $student->user->update([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
        ]);

        return redirect()->route('students.index')->with('success', 'Élève modifié avec succès!');
    }

    public function destroy(Student $student)
    {
        $student->user->delete(); // Cascade delete student
        return redirect()->route('students.index')->with('success', 'Élève supprimé avec succès!');
    }
}