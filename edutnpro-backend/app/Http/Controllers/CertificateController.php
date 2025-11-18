<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::where('school_id', auth()->user()->school_id)
            ->with(['student', 'issuedBy']);

        // Filter by certificate type
        if ($request->filled('certificate_type')) {
            $query->where('certificate_type', $request->certificate_type);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('issued_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('issued_date', '<=', $request->end_date);
        }

        $certificates = $query->orderBy('issued_date', 'desc')->paginate(20);

        return view('certificates.index', compact('certificates'));
    }

    public function create()
    {
        $students = Student::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        $templates = $this->getAvailableTemplates();

        return view('certificates.create', compact('students', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'certificate_type' => 'required|in:enrollment,completion,achievement,attendance,conduct,transcript,custom',
            'title' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'issued_date' => 'required|date',
            'academic_year' => 'required|string|max:9',
            'template' => 'nullable|string',
            'valid_until' => 'nullable|date|after:issued_date',
            'metadata' => 'nullable|array',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['issued_by'] = auth()->id();
        $validated['status'] = 'draft';

        // Generate certificate number
        $certificateModel = new Certificate($validated);
        $validated['certificate_number'] = $certificateModel->generateCertificateNumber($validated['certificate_type']);

        // Set default title if not provided
        if (empty($validated['title'])) {
            $validated['title'] = $this->getDefaultTitle($validated['certificate_type']);
        }
        if (empty($validated['title_ar'])) {
            $validated['title_ar'] = $this->getDefaultTitleAr($validated['certificate_type']);
        }

        // Generate digital signature (hash)
        $validated['digital_signature'] = hash('sha256',
            $validated['certificate_number'] .
            $validated['student_id'] .
            $validated['issued_date'] .
            config('app.key')
        );

        $certificate = Certificate::create($validated);

        return redirect()->route('certificates.show', $certificate)
            ->with('success', __('messages.certificate_created'));
    }

    public function show(Certificate $certificate)
    {
        $this->authorize('view', $certificate);
        $certificate->load(['student', 'issuedBy']);
        return view('certificates.show', compact('certificate'));
    }

    public function edit(Certificate $certificate)
    {
        $this->authorize('update', $certificate);

        // Only allow editing draft certificates
        if ($certificate->status !== 'draft') {
            return back()->with('error', __('messages.cannot_edit_issued_certificate'));
        }

        $students = Student::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        $templates = $this->getAvailableTemplates();

        return view('certificates.edit', compact('certificate', 'students', 'templates'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $this->authorize('update', $certificate);

        // Only allow updating draft certificates
        if ($certificate->status !== 'draft') {
            return back()->with('error', __('messages.cannot_edit_issued_certificate'));
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'certificate_type' => 'required|in:enrollment,completion,achievement,attendance,conduct,transcript,custom',
            'title' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'issued_date' => 'required|date',
            'academic_year' => 'required|string|max:9',
            'template' => 'nullable|string',
            'valid_until' => 'nullable|date|after:issued_date',
            'metadata' => 'nullable|array',
        ]);

        $certificate->update($validated);

        return redirect()->route('certificates.show', $certificate)
            ->with('success', __('messages.certificate_updated'));
    }

    public function destroy(Certificate $certificate)
    {
        $this->authorize('delete', $certificate);

        // Only allow deleting draft certificates
        if ($certificate->status !== 'draft') {
            return back()->with('error', __('messages.cannot_delete_issued_certificate'));
        }

        if ($certificate->file_path) {
            Storage::disk('public')->delete($certificate->file_path);
        }

        $certificate->delete();

        return redirect()->route('certificates.index')
            ->with('success', __('messages.certificate_deleted'));
    }

    public function generate(Certificate $certificate)
    {
        $this->authorize('update', $certificate);

        // Only generate for draft certificates
        if ($certificate->status !== 'draft') {
            return back()->with('error', __('messages.certificate_already_generated'));
        }

        // Here you would integrate with a PDF generation library (e.g., DomPDF, TCPDF)
        // For now, we'll just mark it as generated

        $pdfContent = $this->generatePDF($certificate);
        $filename = 'certificates/' . $certificate->certificate_number . '.pdf';
        Storage::disk('public')->put($filename, $pdfContent);

        $certificate->update([
            'file_path' => $filename,
            'status' => 'issued',
        ]);

        return back()->with('success', __('messages.certificate_generated'));
    }

    public function issue(Certificate $certificate)
    {
        $this->authorize('update', $certificate);

        if ($certificate->status !== 'draft') {
            return back()->with('error', __('messages.certificate_already_issued'));
        }

        // Generate PDF if not already generated
        if (!$certificate->file_path) {
            $pdfContent = $this->generatePDF($certificate);
            $filename = 'certificates/' . $certificate->certificate_number . '.pdf';
            Storage::disk('public')->put($filename, $pdfContent);
            $certificate->file_path = $filename;
        }

        $certificate->update(['status' => 'issued']);

        // Here you could send email notification to student/parent
        // event(new CertificateIssued($certificate));

        return back()->with('success', __('messages.certificate_issued'));
    }

    public function revoke(Request $request, Certificate $certificate)
    {
        $this->authorize('revoke', $certificate);

        if ($certificate->status !== 'issued') {
            return back()->with('error', __('messages.certificate_not_issued'));
        }

        $validated = $request->validate([
            'revocation_reason' => 'required|string',
        ]);

        $certificate->update([
            'status' => 'revoked',
            'revocation_reason' => $validated['revocation_reason'],
        ]);

        return back()->with('success', __('messages.certificate_revoked'));
    }

    public function download(Certificate $certificate)
    {
        $this->authorize('view', $certificate);

        if (!$certificate->file_path || !Storage::disk('public')->exists($certificate->file_path)) {
            return back()->with('error', __('messages.certificate_not_generated'));
        }

        // Track download
        $certificate->update(['downloaded_at' => now()]);

        return Storage::disk('public')->download(
            $certificate->file_path,
            $certificate->certificate_number . '.pdf'
        );
    }

    public function verify(Request $request, Certificate $certificate)
    {
        // Public verification endpoint
        $code = $request->get('code');

        if ($certificate->certificate_number !== $code) {
            return view('certificates.verify', [
                'valid' => false,
                'message' => __('messages.certificate_verification_failed'),
            ]);
        }

        $valid = $certificate->isValid();

        return view('certificates.verify', [
            'valid' => $valid,
            'certificate' => $certificate->load(['student', 'school']),
            'message' => $valid
                ? __('messages.certificate_valid')
                : __('messages.certificate_invalid'),
        ]);
    }

    public function bulkGenerate(Request $request)
    {
        $this->authorize('bulkGenerate', Certificate::class);

        $validated = $request->validate([
            'certificate_type' => 'required|in:enrollment,completion,achievement,attendance,conduct,transcript',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'academic_year' => 'required|string|max:9',
            'issued_date' => 'required|date',
        ]);

        $created = 0;
        $certificateModel = new Certificate();

        foreach ($validated['student_ids'] as $studentId) {
            $certificate = Certificate::create([
                'student_id' => $studentId,
                'school_id' => auth()->user()->school_id,
                'certificate_type' => $validated['certificate_type'],
                'certificate_number' => $certificateModel->generateCertificateNumber($validated['certificate_type']),
                'title' => $this->getDefaultTitle($validated['certificate_type']),
                'title_ar' => $this->getDefaultTitleAr($validated['certificate_type']),
                'issued_date' => $validated['issued_date'],
                'issued_by' => auth()->id(),
                'academic_year' => $validated['academic_year'],
                'status' => 'draft',
                'digital_signature' => hash('sha256',
                    $certificateModel->generateCertificateNumber($validated['certificate_type']) .
                    $studentId .
                    $validated['issued_date'] .
                    config('app.key')
                ),
            ]);

            // Generate PDF
            $pdfContent = $this->generatePDF($certificate);
            $filename = 'certificates/' . $certificate->certificate_number . '.pdf';
            Storage::disk('public')->put($filename, $pdfContent);

            $certificate->update([
                'file_path' => $filename,
                'status' => 'issued',
            ]);

            $created++;
        }

        return back()->with('success', __('messages.certificates_generated_count', ['count' => $created]));
    }

    private function getAvailableTemplates(): array
    {
        return [
            'default' => __('templates.default'),
            'formal' => __('templates.formal'),
            'modern' => __('templates.modern'),
            'classic' => __('templates.classic'),
        ];
    }

    private function getDefaultTitle(string $type): string
    {
        return match ($type) {
            'enrollment' => 'Certificate of Enrollment',
            'completion' => 'Certificate of Completion',
            'achievement' => 'Certificate of Achievement',
            'attendance' => 'Certificate of Attendance',
            'conduct' => 'Certificate of Good Conduct',
            'transcript' => 'Academic Transcript',
            default => 'Certificate',
        };
    }

    private function getDefaultTitleAr(string $type): string
    {
        return match ($type) {
            'enrollment' => 'شهادة تسجيل',
            'completion' => 'شهادة إتمام',
            'achievement' => 'شهادة تحصيل',
            'attendance' => 'شهادة حضور',
            'conduct' => 'شهادة حسن سيرة وسلوك',
            'transcript' => 'كشف درجات',
            default => 'شهادة',
        };
    }

    private function generatePDF(Certificate $certificate): string
    {
        // This is a placeholder. You would integrate with a PDF library here
        // For example: using DomPDF, TCPDF, or Laravel Snappy

        // Example structure:
        // $pdf = PDF::loadView('certificates.pdf.template', compact('certificate'));
        // return $pdf->output();

        // For now, return placeholder content
        return "PDF content for certificate: {$certificate->certificate_number}";
    }
}
