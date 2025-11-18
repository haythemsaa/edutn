<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)
            ->withCount(['classSections', 'timetableEntries'])
            ->orderBy('building')
            ->orderBy('name')
            ->paginate(20);

        return view('classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('classrooms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|integer|min:0|max:20',
            'capacity' => 'nullable|integer|min:1|max:500',
            'type' => 'nullable|in:classroom,laboratory,library,gym,auditorium,computer_lab,art_room,music_room',
            'equipment' => 'nullable|array',
            'has_projector' => 'nullable|boolean',
            'has_computer' => 'nullable|boolean',
            'has_ac' => 'nullable|boolean',
            'is_accessible' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['has_projector'] = $request->boolean('has_projector');
        $validated['has_computer'] = $request->boolean('has_computer');
        $validated['has_ac'] = $request->boolean('has_ac');
        $validated['is_accessible'] = $request->boolean('is_accessible');
        $validated['is_available'] = $request->boolean('is_available', true);

        Classroom::create($validated);

        return redirect()->route('classrooms.index')
            ->with('success', __('messages.classroom_created'));
    }

    public function show(Classroom $classroom)
    {
        $this->authorize('view', $classroom);

        $classroom->load(['classSections', 'timetableEntries.subject', 'timetableEntries.teacher']);

        return view('classrooms.show', compact('classroom'));
    }

    public function edit(Classroom $classroom)
    {
        $this->authorize('update', $classroom);

        return view('classrooms.edit', compact('classroom'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $this->authorize('update', $classroom);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|integer|min:0|max:20',
            'capacity' => 'nullable|integer|min:1|max:500',
            'type' => 'nullable|in:classroom,laboratory,library,gym,auditorium,computer_lab,art_room,music_room',
            'equipment' => 'nullable|array',
            'has_projector' => 'nullable|boolean',
            'has_computer' => 'nullable|boolean',
            'has_ac' => 'nullable|boolean',
            'is_accessible' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
        ]);

        $validated['has_projector'] = $request->boolean('has_projector');
        $validated['has_computer'] = $request->boolean('has_computer');
        $validated['has_ac'] = $request->boolean('has_ac');
        $validated['is_accessible'] = $request->boolean('is_accessible');
        $validated['is_available'] = $request->boolean('is_available');

        $classroom->update($validated);

        return redirect()->route('classrooms.index')
            ->with('success', __('messages.classroom_updated'));
    }

    public function destroy(Classroom $classroom)
    {
        $this->authorize('delete', $classroom);

        $classroom->delete();

        return redirect()->route('classrooms.index')
            ->with('success', __('messages.classroom_deleted'));
    }
}
