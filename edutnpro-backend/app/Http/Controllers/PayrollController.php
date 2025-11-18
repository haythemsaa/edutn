<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::where('school_id', auth()->user()->school_id)
            ->with('teacher')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        return view('payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('payrolls.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'payroll_number' => 'nullable|string|max:50|unique:payrolls',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'overtime' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'social_security' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'worked_days' => 'nullable|integer|min:0|max:31',
            'absent_days' => 'nullable|integer|min:0|max:31',
            'overtime_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,approved,paid',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:bank_transfer,check,cash',
            'file' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = $validated['status'] ?? 'draft';

        // Calculate salaries
        $gross = ($validated['base_salary'] ?? 0) + ($validated['allowances'] ?? 0) +
                 ($validated['bonuses'] ?? 0) + ($validated['overtime'] ?? 0);
        $validated['gross_salary'] = $gross;

        $total_deductions = ($validated['tax'] ?? 0) + ($validated['social_security'] ?? 0) +
                           ($validated['deductions'] ?? 0);
        $validated['net_salary'] = $gross - $total_deductions;

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('payrolls', 'public');
        }

        $payroll = Payroll::create($validated);

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', __('messages.payroll_created'));
    }

    public function show(Payroll $payroll)
    {
        $this->authorize('view', $payroll);
        $payroll->load('teacher');
        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $this->authorize('update', $payroll);

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('payrolls.edit', compact('payroll', 'teachers'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $this->authorize('update', $payroll);

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'payroll_number' => 'nullable|string|max:50|unique:payrolls,payroll_number,' . $payroll->id,
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'overtime' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'social_security' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'worked_days' => 'nullable|integer|min:0|max:31',
            'absent_days' => 'nullable|integer|min:0|max:31',
            'overtime_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:draft,approved,paid',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|in:bank_transfer,check,cash',
            'file' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        // Recalculate salaries
        $gross = ($validated['base_salary'] ?? 0) + ($validated['allowances'] ?? 0) +
                 ($validated['bonuses'] ?? 0) + ($validated['overtime'] ?? 0);
        $validated['gross_salary'] = $gross;

        $total_deductions = ($validated['tax'] ?? 0) + ($validated['social_security'] ?? 0) +
                           ($validated['deductions'] ?? 0);
        $validated['net_salary'] = $gross - $total_deductions;

        if ($request->hasFile('file')) {
            if ($payroll->file_path) {
                Storage::disk('public')->delete($payroll->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('payrolls', 'public');
        }

        $payroll->update($validated);

        return redirect()->route('payrolls.show', $payroll)
            ->with('success', __('messages.payroll_updated'));
    }

    public function destroy(Payroll $payroll)
    {
        $this->authorize('delete', $payroll);

        if ($payroll->file_path) {
            Storage::disk('public')->delete($payroll->file_path);
        }

        $payroll->delete();

        return redirect()->route('payrolls.index')
            ->with('success', __('messages.payroll_deleted'));
    }

    public function approve(Payroll $payroll)
    {
        $this->authorize('update', $payroll);
        $payroll->update(['status' => 'approved']);
        return back()->with('success', __('messages.payroll_approved'));
    }

    public function markAsPaid(Payroll $payroll)
    {
        $this->authorize('update', $payroll);
        $payroll->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);
        return back()->with('success', __('messages.payroll_marked_paid'));
    }
}
