<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Classroom;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;

class ClassSectionController extends Controller
{
    public function index()
    {
        $classSections = ClassSection::where('school_id', auth()->user()->school_id)
            ->with(['classroom', 'classTeacher'])
            ->withCount('reportCards')
            ->orderBy('grade_level')
            ->orderBy('section')
            ->paginate(20);

        return view('class-sections.index', compact('classSections'));
    }

    public function create()
    {
        $classrooms = Classroom::where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('class-sections.create', compact('classrooms', 'teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'classroom_id' => 'nullable|exists:classrooms,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'grade_level' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'class_teacher_id' => 'nullable|exists:teachers,id',
            'academic_year' => 'required|string|max:9',
            'max_students' => 'nullable|integer|min:1|max:100',
            'shift' => 'nullable|in:morning,afternoon,evening',
            'status' => 'nullable|in:active,inactive,archived',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['current_students'] = 0;
        $validated['status'] = $validated['status'] ?? 'active';

        $classSection = ClassSection::create($validated);

        return redirect()->route('class-sections.show', $classSection)
            ->with('success', __('messages.class_section_created'));
    }

    public function show(ClassSection $classSection)
    {
        $this->authorize('view', $classSection);

        $classSection->load(['classroom', 'classTeacher', 'classDiaries', 'reportCards', 'timetables']);

        $students = Student::where('class_section_id', $classSection->id)
            ->orderBy('last_name')
            ->paginate(20);

        return view('class-sections.show', compact('classSection', 'students'));
    }

    public function edit(ClassSection $classSection)
    {
        $this->authorize('update', $classSection);

        $classrooms = Classroom::where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('class-sections.edit', compact('classSection', 'classrooms', 'teachers'));
    }

    public function update(Request $request, ClassSection $classSection)
    {
        $this->authorize('update', $classSection);

        $validated = $request->validate([
            'classroom_id' => 'nullable|exists:classrooms,id',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'grade_level' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'class_teacher_id' => 'nullable|exists:teachers,id',
            'academic_year' => 'required|string|max:9',
            'max_students' => 'nullable|integer|min:1|max:100',
            'shift' => 'nullable|in:morning,afternoon,evening',
            'status' => 'nullable|in:active,inactive,archived',
        ]);

        $classSection->update($validated);

        return redirect()->route('class-sections.show', $classSection)
            ->with('success', __('messages.class_section_updated'));
    }

    public function destroy(ClassSection $classSection)
    {
        $this->authorize('delete', $classSection);

        $studentCount = Student::where('class_section_id', $classSection->id)->count();
        if ($studentCount > 0) {
            return back()->with('error', __('messages.cannot_delete_class_has_students'));
        }

        $classSection->delete();

        return redirect()->route('class-sections.index')
            ->with('success', __('messages.class_section_deleted'));
    }

    public function students(ClassSection $classSection)
    {
        $this->authorize('view', $classSection);

        $students = Student::where('class_section_id', $classSection->id)
            ->orderBy('last_name')
            ->get();

        return view('class-sections.students', compact('classSection', 'students'));
    }

    public function updateStudentCount(ClassSection $classSection)
    {
        $count = Student::where('class_section_id', $classSection->id)->count();
        $classSection->update(['current_students' => $count]);

        return response()->json(['success' => true, 'count' => $count]);
    }

    public function archive(ClassSection $classSection)
    {
        $this->authorize('update', $classSection);

        $classSection->update(['status' => 'archived']);

        return back()->with('success', __('messages.class_section_archived'));
    }

    public function activate(ClassSection $classSection)
    {
        $this->authorize('update', $classSection);

        $classSection->update(['status' => 'active']);

        return back()->with('success', __('messages.class_section_activated'));
    }
}
