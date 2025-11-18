<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::where('school_id', auth()->user()->school_id)
            ->with('teachers')
            ->withCount('grades')
            ->paginate(20);

        return view('subjects.index', compact('subjects'));
    }

    public function create()
    {
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->where('status', 'active')
            ->get();

        return view('subjects.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subjects,code',
            'description' => 'nullable|string',
            'coefficient' => 'nullable|numeric|min:1|max:10',
            'teachers' => 'nullable|array',
            'teachers.*' => 'exists:teachers,id',
        ]);

        $validated['school_id'] = auth()->user()->school_id;

        $subject = Subject::create($validated);

        if (!empty($validated['teachers'])) {
            $subject->teachers()->attach($validated['teachers']);
        }

        return redirect()->route('subjects.index')
            ->with('success', __('messages.subject_created'));
    }

    public function show(Subject $subject)
    {
        $this->authorize('view', $subject);

        $subject->load(['teachers', 'grades.student']);

        return view('subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        $this->authorize('update', $subject);

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->where('status', 'active')
            ->get();

        return view('subjects.edit', compact('subject', 'teachers'));
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorize('update', $subject);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'coefficient' => 'nullable|numeric|min:1|max:10',
            'teachers' => 'nullable|array',
            'teachers.*' => 'exists:teachers,id',
        ]);

        $subject->update($validated);

        if (isset($validated['teachers'])) {
            $subject->teachers()->sync($validated['teachers']);
        }

        return redirect()->route('subjects.index')
            ->with('success', __('messages.subject_updated'));
    }

    public function destroy(Subject $subject)
    {
        $this->authorize('delete', $subject);

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', __('messages.subject_deleted'));
    }
}
