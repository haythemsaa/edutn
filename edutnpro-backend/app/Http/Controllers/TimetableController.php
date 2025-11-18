<?php

namespace App\Http\Controllers;

use App\Models\Timetable;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Classroom;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    public function index()
    {
        $classSections = ClassSection::where('school_id', auth()->user()->school_id)->get();

        $timetables = Timetable::whereHas('classSection', function ($q) {
                $q->where('school_id', auth()->user()->school_id);
            })
            ->with(['classSection', 'subject', 'teacher', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('class_section_id');

        return view('timetables.index', compact('timetables', 'classSections'));
    }

    public function create()
    {
        $classSections = ClassSection::where('school_id', auth()->user()->school_id)->get();
        $subjects = Subject::where('school_id', auth()->user()->school_id)->get();
        $teachers = Teacher::where('school_id', auth()->user()->school_id)->where('status', 'active')->get();
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)->get();

        return view('timetables.create', compact('classSections', 'subjects', 'teachers', 'classrooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $validated['school_id'] = auth()->user()->school_id;

        Timetable::create($validated);

        return redirect()->route('timetables.index')
            ->with('success', __('messages.timetable_created'));
    }

    public function show(Timetable $timetable)
    {
        $this->authorize('view', $timetable);

        $timetable->load(['classSection', 'subject', 'teacher', 'classroom']);

        return view('timetables.show', compact('timetable'));
    }

    public function edit(Timetable $timetable)
    {
        $this->authorize('update', $timetable);

        $classSections = ClassSection::where('school_id', auth()->user()->school_id)->get();
        $subjects = Subject::where('school_id', auth()->user()->school_id)->get();
        $teachers = Teacher::where('school_id', auth()->user()->school_id)->where('status', 'active')->get();
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)->get();

        return view('timetables.edit', compact('timetable', 'classSections', 'subjects', 'teachers', 'classrooms'));
    }

    public function update(Request $request, Timetable $timetable)
    {
        $this->authorize('update', $timetable);

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'day_of_week' => 'required|integer|min:1|max:7',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $timetable->update($validated);

        return redirect()->route('timetables.index')
            ->with('success', __('messages.timetable_updated'));
    }

    public function destroy(Timetable $timetable)
    {
        $this->authorize('delete', $timetable);

        $timetable->delete();

        return redirect()->route('timetables.index')
            ->with('success', __('messages.timetable_deleted'));
    }
}
