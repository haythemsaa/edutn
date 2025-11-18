<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Models\ReportCardItem;
use App\Models\Student;
use App\Models\ClassSection;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportCardController extends Controller
{
    public function index()
    {
        $reportCards = ReportCard::where('school_id', auth()->user()->school_id)
            ->with(['student', 'classSection'])
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->paginate(20);

        return view('report-cards.index', compact('reportCards'));
    }

    public function create()
    {
        $students = Student::where('school_id', auth()->user()->school_id)
            ->with('classSection')
            ->orderBy('last_name')
            ->get();

        $classSections = ClassSection::where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        return view('report-cards.create', compact('students', 'classSections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'academic_year' => 'required|string|max:9',
            'term' => 'required|in:1,2,3',
            'total_average' => 'nullable|numeric|min:0|max:20',
            'class_rank' => 'nullable|integer|min:1',
            'total_students' => 'nullable|integer|min:1',
            'total_absences' => 'nullable|integer|min:0',
            'general_appreciation' => 'nullable|string',
            'general_appreciation_ar' => 'nullable|string',
            'conduct_comment' => 'nullable|string',
            'conduct_comment_ar' => 'nullable|string',
            'status' => 'nullable|in:draft,published,sent',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = $validated['status'] ?? 'draft';

        $reportCard = ReportCard::create($validated);

        return redirect()->route('report-cards.show', $reportCard)
            ->with('success', __('messages.report_card_created'));
    }

    public function show(ReportCard $reportCard)
    {
        $this->authorize('view', $reportCard);

        $reportCard->load(['student', 'classSection', 'items.subject']);

        return view('report-cards.show', compact('reportCard'));
    }

    public function edit(ReportCard $reportCard)
    {
        $this->authorize('update', $reportCard);

        $students = Student::where('school_id', auth()->user()->school_id)
            ->with('classSection')
            ->orderBy('last_name')
            ->get();

        $classSections = ClassSection::where('school_id', auth()->user()->school_id)
            ->orderBy('name')
            ->get();

        return view('report-cards.edit', compact('reportCard', 'students', 'classSections'));
    }

    public function update(Request $request, ReportCard $reportCard)
    {
        $this->authorize('update', $reportCard);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_section_id' => 'required|exists:class_sections,id',
            'academic_year' => 'required|string|max:9',
            'term' => 'required|in:1,2,3',
            'total_average' => 'nullable|numeric|min:0|max:20',
            'class_rank' => 'nullable|integer|min:1',
            'total_students' => 'nullable|integer|min:1',
            'total_absences' => 'nullable|integer|min:0',
            'general_appreciation' => 'nullable|string',
            'general_appreciation_ar' => 'nullable|string',
            'conduct_comment' => 'nullable|string',
            'conduct_comment_ar' => 'nullable|string',
            'status' => 'nullable|in:draft,published,sent',
        ]);

        $reportCard->update($validated);

        return redirect()->route('report-cards.show', $reportCard)
            ->with('success', __('messages.report_card_updated'));
    }

    public function destroy(ReportCard $reportCard)
    {
        $this->authorize('delete', $reportCard);

        $reportCard->delete();

        return redirect()->route('report-cards.index')
            ->with('success', __('messages.report_card_deleted'));
    }

    public function addItem(Request $request, ReportCard $reportCard)
    {
        $this->authorize('update', $reportCard);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade' => 'required|numeric|min:0|max:20',
            'class_average' => 'nullable|numeric|min:0|max:20',
            'highest_grade' => 'nullable|numeric|min:0|max:20',
            'lowest_grade' => 'nullable|numeric|min:0|max:20',
            'coefficient' => 'nullable|numeric|min:1|max:10',
            'teacher_comment' => 'nullable|string',
            'teacher_comment_ar' => 'nullable|string',
            'appreciation' => 'nullable|in:excellent,very_good,good,average,insufficient',
        ]);

        $validated['coefficient'] = $validated['coefficient'] ?? 1;
        $validated['weighted_grade'] = $validated['grade'] * $validated['coefficient'];

        $reportCard->items()->create($validated);

        // Recalculate total average
        $this->recalculateAverage($reportCard);

        return back()->with('success', __('messages.item_added'));
    }

    public function updateItem(Request $request, ReportCard $reportCard, ReportCardItem $item)
    {
        $this->authorize('update', $reportCard);

        if ($item->report_card_id !== $reportCard->id) {
            abort(404);
        }

        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:20',
            'class_average' => 'nullable|numeric|min:0|max:20',
            'highest_grade' => 'nullable|numeric|min:0|max:20',
            'lowest_grade' => 'nullable|numeric|min:0|max:20',
            'coefficient' => 'nullable|numeric|min:1|max:10',
            'teacher_comment' => 'nullable|string',
            'teacher_comment_ar' => 'nullable|string',
            'appreciation' => 'nullable|in:excellent,very_good,good,average,insufficient',
        ]);

        $validated['coefficient'] = $validated['coefficient'] ?? 1;
        $validated['weighted_grade'] = $validated['grade'] * $validated['coefficient'];

        $item->update($validated);

        // Recalculate total average
        $this->recalculateAverage($reportCard);

        return back()->with('success', __('messages.item_updated'));
    }

    public function deleteItem(ReportCard $reportCard, ReportCardItem $item)
    {
        $this->authorize('update', $reportCard);

        if ($item->report_card_id !== $reportCard->id) {
            abort(404);
        }

        $item->delete();

        // Recalculate total average
        $this->recalculateAverage($reportCard);

        return back()->with('success', __('messages.item_deleted'));
    }

    public function publish(ReportCard $reportCard)
    {
        $this->authorize('update', $reportCard);

        if ($reportCard->status === 'published' || $reportCard->status === 'sent') {
            return back()->with('error', __('messages.already_published'));
        }

        $reportCard->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', __('messages.report_card_published'));
    }

    public function send(ReportCard $reportCard)
    {
        $this->authorize('update', $reportCard);

        if ($reportCard->status !== 'published') {
            return back()->with('error', __('messages.must_be_published_first'));
        }

        $reportCard->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // TODO: Send notification to parent

        return back()->with('success', __('messages.report_card_sent'));
    }

    public function download(ReportCard $reportCard)
    {
        $this->authorize('view', $reportCard);

        // TODO: Generate PDF

        return back()->with('info', __('messages.pdf_generation_coming_soon'));
    }

    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'academic_year' => 'required|string|max:9',
            'term' => 'required|in:1,2,3',
        ]);

        $classSection = ClassSection::findOrFail($validated['class_section_id']);
        $this->authorize('view', $classSection);

        $students = Student::where('class_section_id', $classSection->id)->get();

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                // Check if report card already exists
                $exists = ReportCard::where('student_id', $student->id)
                    ->where('academic_year', $validated['academic_year'])
                    ->where('term', $validated['term'])
                    ->exists();

                if (!$exists) {
                    ReportCard::create([
                        'student_id' => $student->id,
                        'class_section_id' => $classSection->id,
                        'school_id' => auth()->user()->school_id,
                        'academic_year' => $validated['academic_year'],
                        'term' => $validated['term'],
                        'status' => 'draft',
                        'total_students' => $students->count(),
                    ]);
                }
            }
            DB::commit();

            return back()->with('success', __('messages.report_cards_generated', ['count' => $students->count()]));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', __('messages.generation_failed'));
        }
    }

    private function recalculateAverage(ReportCard $reportCard)
    {
        $items = $reportCard->items;

        if ($items->isEmpty()) {
            $reportCard->update(['total_average' => null]);
            return;
        }

        $totalWeighted = $items->sum('weighted_grade');
        $totalCoefficients = $items->sum('coefficient');

        $average = $totalCoefficients > 0 ? $totalWeighted / $totalCoefficients : 0;

        $reportCard->update(['total_average' => round($average, 2)]);
    }
}
