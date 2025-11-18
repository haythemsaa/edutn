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
        // À implémenter
        return redirect()->route('teachers.index')->with('success', 'Fonctionnalité en cours de développement');
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
        // À implémenter
        return redirect()->route('teachers.index')->with('success', 'Fonctionnalité en cours de développement');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        // À implémenter
        return redirect()->route('teachers.index')->with('success', 'Fonctionnalité en cours de développement');
    }
}
