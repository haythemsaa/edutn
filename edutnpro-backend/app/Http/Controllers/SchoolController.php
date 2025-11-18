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
        return redirect()->route('schools.index')->with('success', 'Fonctionnalité en cours de développement');
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
        return redirect()->route('schools.index')->with('success', 'Fonctionnalité en cours de développement');
    }

    public function destroy(School $school)
    {
        return redirect()->route('schools.index')->with('success', 'Fonctionnalité en cours de développement');
    }
}
