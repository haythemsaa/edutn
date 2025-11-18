<?php

namespace App\Http\Controllers;

use App\Models\ClassDiary;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClassDiaryController extends Controller
{
    public function index(Request $request)
    {
        $query = ClassDiary::whereHas('classSection', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->with(['classSection', 'teacher', 'subject']);

        // Filter by class section
        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->class_section_id);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('lesson_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('lesson_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $diaries = $query->orderBy('lesson_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        $classSections = ClassSection::where('school_id', auth()->user()->school_id)
            ->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('class-diaries.index', compact('diaries', 'classSections', 'subjects'));
    }

    public function create()
    {
        $classSections = ClassSection::where('school_id', auth()->user()->school_id)
            ->orderBy('name')->get();
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('class-diaries.create', compact('classSections', 'teachers', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'lesson_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'lesson_title' => 'required|string|max:255',
            'lesson_title_ar' => 'nullable|string|max:255',
            'lesson_content' => 'required|string',
            'lesson_content_ar' => 'nullable|string',
            'objectives' => 'nullable|string',
            'objectives_ar' => 'nullable|string',
            'homework' => 'nullable|string',
            'homework_ar' => 'nullable|string',
            'students_present' => 'nullable|integer|min:0',
            'students_absent' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240',
        ]);

        $validated['status'] = $validated['status'] ?? 'draft';

        // Handle attachments upload
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $attachments[] = [
                    'path' => $file->store('class-diaries', 'public'),
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
            $validated['attachments'] = $attachments;
        }

        $diary = ClassDiary::create($validated);

        return redirect()->route('class-diaries.show', $diary)
            ->with('success', __('messages.diary_entry_created'));
    }

    public function show(ClassDiary $classDiary)
    {
        $this->authorize('view', $classDiary);
        $classDiary->load(['classSection', 'teacher', 'subject']);
        return view('class-diaries.show', compact('classDiary'));
    }

    public function edit(ClassDiary $classDiary)
    {
        $this->authorize('update', $classDiary);

        $classSections = ClassSection::where('school_id', auth()->user()->school_id)
            ->orderBy('name')->get();
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('class-diaries.edit', compact('classDiary', 'classSections', 'teachers', 'subjects'));
    }

    public function update(Request $request, ClassDiary $classDiary)
    {
        $this->authorize('update', $classDiary);

        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'teacher_id' => 'required|exists:teachers,id',
            'subject_id' => 'required|exists:subjects,id',
            'lesson_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'lesson_title' => 'required|string|max:255',
            'lesson_title_ar' => 'nullable|string|max:255',
            'lesson_content' => 'required|string',
            'lesson_content_ar' => 'nullable|string',
            'objectives' => 'nullable|string',
            'objectives_ar' => 'nullable|string',
            'homework' => 'nullable|string',
            'homework_ar' => 'nullable|string',
            'students_present' => 'nullable|integer|min:0',
            'students_absent' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,published',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png|max:10240',
            'remove_attachments' => 'nullable|array',
        ]);

        // Handle removing old attachments
        if ($request->filled('remove_attachments')) {
            $currentAttachments = $classDiary->attachments ?? [];
            $remainingAttachments = [];

            foreach ($currentAttachments as $index => $attachment) {
                if (in_array($index, $request->remove_attachments)) {
                    Storage::disk('public')->delete($attachment['path']);
                } else {
                    $remainingAttachments[] = $attachment;
                }
            }

            $validated['attachments'] = $remainingAttachments;
        } else {
            $validated['attachments'] = $classDiary->attachments ?? [];
        }

        // Handle new attachments upload
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $validated['attachments'][] = [
                    'path' => $file->store('class-diaries', 'public'),
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $classDiary->update($validated);

        return redirect()->route('class-diaries.show', $classDiary)
            ->with('success', __('messages.diary_entry_updated'));
    }

    public function destroy(ClassDiary $classDiary)
    {
        $this->authorize('delete', $classDiary);

        // Delete all attachments
        if ($classDiary->attachments) {
            foreach ($classDiary->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $classDiary->delete();

        return redirect()->route('class-diaries.index')
            ->with('success', __('messages.diary_entry_deleted'));
    }

    public function publish(ClassDiary $classDiary)
    {
        $this->authorize('update', $classDiary);

        $classDiary->update(['status' => 'published']);

        return back()->with('success', __('messages.diary_entry_published'));
    }

    public function unpublish(ClassDiary $classDiary)
    {
        $this->authorize('update', $classDiary);

        $classDiary->update(['status' => 'draft']);

        return back()->with('success', __('messages.diary_entry_unpublished'));
    }

    public function byClassSection(ClassSection $classSection)
    {
        $this->authorize('view', $classSection);

        $diaries = ClassDiary::where('class_section_id', $classSection->id)
            ->where('status', 'published')
            ->with(['teacher', 'subject'])
            ->orderBy('lesson_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(20);

        return view('class-diaries.by-class', compact('diaries', 'classSection'));
    }

    public function exportWeekly(ClassSection $classSection, Request $request)
    {
        $this->authorize('view', $classSection);

        $startDate = $request->get('start_date', now()->startOfWeek());
        $endDate = $request->get('end_date', now()->endOfWeek());

        $diaries = ClassDiary::where('class_section_id', $classSection->id)
            ->whereDate('lesson_date', '>=', $startDate)
            ->whereDate('lesson_date', '<=', $endDate)
            ->with(['teacher', 'subject'])
            ->orderBy('lesson_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Return as PDF (would need a PDF library like DomPDF)
        return view('class-diaries.export-weekly', compact('diaries', 'classSection', 'startDate', 'endDate'));
    }

    public function exportMonthly(ClassSection $classSection, Request $request)
    {
        $this->authorize('view', $classSection);

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $diaries = ClassDiary::where('class_section_id', $classSection->id)
            ->whereMonth('lesson_date', $month)
            ->whereYear('lesson_date', $year)
            ->with(['teacher', 'subject'])
            ->orderBy('lesson_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('class-diaries.export-monthly', compact('diaries', 'classSection', 'month', 'year'));
    }

    public function downloadAttachment(ClassDiary $classDiary, $index)
    {
        $this->authorize('view', $classDiary);

        $attachments = $classDiary->attachments ?? [];

        if (!isset($attachments[$index])) {
            return back()->with('error', __('messages.file_not_found'));
        }

        $attachment = $attachments[$index];

        if (!Storage::disk('public')->exists($attachment['path'])) {
            return back()->with('error', __('messages.file_not_found'));
        }

        return Storage::disk('public')->download(
            $attachment['path'],
            $attachment['name'] ?? basename($attachment['path'])
        );
    }

    public function parentView(ClassSection $classSection)
    {
        // Public view for parents to see published diary entries
        $diaries = ClassDiary::where('class_section_id', $classSection->id)
            ->where('status', 'published')
            ->with(['teacher', 'subject'])
            ->orderBy('lesson_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('class-diaries.parent-view', compact('diaries', 'classSection'));
    }
}
