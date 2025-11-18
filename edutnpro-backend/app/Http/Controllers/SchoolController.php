<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::paginate(20);
        return view('schools.index', compact('schools'));
    }

    public function create()
    {
        return view('schools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_fr' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:schools,code',
            'logo' => 'nullable|image|max:2048',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:schools,email',
            'website' => 'nullable|url|max:255',
            'ministry_approval_number' => 'nullable|string|max:100',
            'school_type' => 'required|in:public,private',
            'education_level' => 'required|in:primary,middle,secondary,all',
            'capacity' => 'required|integer|min:0',
            'director_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('schools', 'public');
        }

        // Set default active status
        $validated['is_active'] = $request->has('is_active') ? true : false;

        School::create($validated);

        return redirect()->route('schools.index')->with('success', 'Établissement créé avec succès!');
    }

    public function show(School $school)
    {
        $school->load(['students', 'teachers', 'academicYears']);
        return view('schools.show', compact('school'));
    }

    public function edit(School $school)
    {
        return view('schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_fr' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:schools,code,' . $school->id,
            'logo' => 'nullable|image|max:2048',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:schools,email,' . $school->id,
            'website' => 'nullable|url|max:255',
            'ministry_approval_number' => 'nullable|string|max:100',
            'school_type' => 'required|in:public,private',
            'education_level' => 'required|in:primary,middle,secondary,all',
            'capacity' => 'required|integer|min:0',
            'director_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($school->logo && \Storage::disk('public')->exists($school->logo)) {
                \Storage::disk('public')->delete($school->logo);
            }
            $validated['logo'] = $request->file('logo')->store('schools', 'public');
        }

        // Set active status
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $school->update($validated);

        return redirect()->route('schools.index')->with('success', 'Établissement modifié avec succès!');
    }

    public function destroy(School $school)
    {
        try {
            // Check if school has students or teachers
            if ($school->students()->count() > 0 || $school->teachers()->count() > 0) {
                return redirect()->route('schools.index')
                    ->with('error', 'Impossible de supprimer cet établissement car il contient des élèves ou des enseignants.');
            }

            // Delete logo if exists
            if ($school->logo && \Storage::disk('public')->exists($school->logo)) {
                \Storage::disk('public')->delete($school->logo);
            }

            // Delete school record
            $school->delete();

            return redirect()->route('schools.index')->with('success', 'Établissement supprimé avec succès!');
        } catch (\Exception $e) {
            return redirect()->route('schools.index')->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}
