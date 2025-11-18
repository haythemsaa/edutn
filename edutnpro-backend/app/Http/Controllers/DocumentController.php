<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with(['uploadedBy', 'student'])
            ->when(auth()->user()->hasRole('parent'), function ($query) {
                $query->whereHas('student', function ($q) {
                    $q->whereIn('id', auth()->user()->children->pluck('id'));
                });
            })
            ->when(auth()->user()->hasRole('student'), function ($query) {
                $query->where('student_id', auth()->user()->student_id);
            })
            ->latest()
            ->paginate(20);

        return view('documents.index', compact('documents'));
    }

    public function create()
    {
        return view('documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:certificate,report,identity,medical,other',
            'student_id' => 'nullable|exists:students,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('documents', 'public');
            $validated['file_path'] = $path;
            $validated['file_name'] = $request->file('file')->getClientOriginalName();
            $validated['file_size'] = $request->file('file')->getSize();
        }

        $validated['uploaded_by'] = auth()->id();
        $validated['school_id'] = auth()->user()->school_id;

        Document::create($validated);

        return redirect()->route('documents.index')
            ->with('success', __('messages.document_created'));
    }

    public function show(Document $document)
    {
        $this->authorize('view', $document);
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        $this->authorize('update', $document);
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:certificate,report,identity,medical,other',
            'student_id' => 'nullable|exists:students,id',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            $path = $request->file('file')->store('documents', 'public');
            $validated['file_path'] = $path;
            $validated['file_name'] = $request->file('file')->getClientOriginalName();
            $validated['file_size'] = $request->file('file')->getSize();
        }

        $document->update($validated);

        return redirect()->route('documents.index')
            ->with('success', __('messages.document_updated'));
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', __('messages.document_deleted'));
    }

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }
}
