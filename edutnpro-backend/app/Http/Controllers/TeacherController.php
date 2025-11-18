<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\School;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teachers = Teacher::with(['school', 'user'])->paginate(20);
        return view('teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::where('is_active', true)->get();
        return view('teachers.create', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'first_name_ar' => 'nullable|string|max:255',
            'last_name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'school_id' => 'required|exists:schools,id',
            'employee_number' => 'required|string|max:50|unique:teachers,employee_number',
            'specialization' => 'nullable|string|max:255',
            'qualifications' => 'nullable|string',
            'hire_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract,substitute',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Create user account
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt('password'), // Default password
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        // Assign teacher role
        $user->assignRole('teacher');

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('teachers', 'public');
        }

        // Create teacher record
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'school_id' => $validated['school_id'],
            'employee_number' => $validated['employee_number'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'first_name_ar' => $validated['first_name_ar'] ?? null,
            'last_name_ar' => $validated['last_name_ar'] ?? null,
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'address' => $validated['address'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'qualifications' => $validated['qualifications'] ?? null,
            'hire_date' => $validated['hire_date'],
            'employment_type' => $validated['employment_type'],
            'status' => 'active',
            'photo' => $photoPath,
        ]);

        return redirect()->route('teachers.index')->with('success', 'Enseignant créé avec succès!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        $teacher->load(['school', 'user', 'grades']);
        return view('teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        $schools = School::where('is_active', true)->get();
        return view('teachers.edit', compact('teacher', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'first_name_ar' => 'nullable|string|max:255',
            'last_name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'school_id' => 'required|exists:schools,id',
            'employee_number' => 'required|string|max:50|unique:teachers,employee_number,' . $teacher->id,
            'specialization' => 'nullable|string|max:255',
            'qualifications' => 'nullable|string',
            'hire_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract,substitute',
            'status' => 'required|in:active,inactive,on_leave,terminated',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Update user account
        $teacher->user->update([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($teacher->photo && \Storage::disk('public')->exists($teacher->photo)) {
                \Storage::disk('public')->delete($teacher->photo);
            }
            $validated['photo'] = $request->file('photo')->store('teachers', 'public');
        }

        // Update teacher record
        $teacher->update([
            'school_id' => $validated['school_id'],
            'employee_number' => $validated['employee_number'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'first_name_ar' => $validated['first_name_ar'] ?? null,
            'last_name_ar' => $validated['last_name_ar'] ?? null,
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'address' => $validated['address'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'qualifications' => $validated['qualifications'] ?? null,
            'hire_date' => $validated['hire_date'],
            'employment_type' => $validated['employment_type'],
            'status' => $validated['status'],
            'photo' => $validated['photo'] ?? $teacher->photo,
        ]);

        return redirect()->route('teachers.index')->with('success', 'Enseignant modifié avec succès!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        try {
            // Delete photo if exists
            if ($teacher->photo && \Storage::disk('public')->exists($teacher->photo)) {
                \Storage::disk('public')->delete($teacher->photo);
            }

            // Store user for deletion after teacher
            $user = $teacher->user;

            // Delete teacher record
            $teacher->delete();

            // Delete associated user account
            if ($user) {
                $user->delete();
            }

            return redirect()->route('teachers.index')->with('success', 'Enseignant supprimé avec succès!');
        } catch (\Exception $e) {
            return redirect()->route('teachers.index')->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}
