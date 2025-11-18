<?php

namespace App\Http\Controllers;

use App\Models\EmployeeContract;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeContractController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeContract::where('school_id', auth()->user()->school_id)
            ->with('teacher');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by contract type
        if ($request->filled('contract_type')) {
            $query->where('contract_type', $request->contract_type);
        }

        // Filter by teacher
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filter expiring soon
        if ($request->boolean('expiring_soon')) {
            $query->whereDate('end_date', '<=', now()->addDays(30))
                ->whereDate('end_date', '>=', now())
                ->where('status', 'active');
        }

        $contracts = $query->orderBy('end_date', 'desc')->paginate(20);

        return view('employee-contracts.index', compact('contracts'));
    }

    public function create()
    {
        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('employee-contracts.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'contract_number' => 'nullable|string|max:100|unique:employee_contracts',
            'contract_type' => 'required|in:CDI,CDD,temporary,internship,part_time',
            'position' => 'required|string|max:255',
            'position_ar' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'salary' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_frequency' => 'nullable|in:monthly,bi_weekly,weekly,hourly',
            'weekly_hours' => 'nullable|numeric|min:0|max:168',
            'responsibilities' => 'nullable|string',
            'responsibilities_ar' => 'nullable|string',
            'benefits' => 'nullable|string',
            'allowances' => 'nullable|array',
            'status' => 'nullable|in:draft,active,expired,terminated',
            'signed_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $validated['school_id'] = auth()->user()->school_id;
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['currency'] = $validated['currency'] ?? 'TND';
        $validated['payment_frequency'] = $validated['payment_frequency'] ?? 'monthly';

        // Generate contract number if not provided
        if (empty($validated['contract_number'])) {
            $validated['contract_number'] = 'CONT-' . date('Y') . '-' . str_pad(
                EmployeeContract::where('school_id', auth()->user()->school_id)->count() + 1,
                4,
                '0',
                STR_PAD_LEFT
            );
        }

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('contracts', 'public');
        }

        $contract = EmployeeContract::create($validated);

        return redirect()->route('employee-contracts.show', $contract)
            ->with('success', __('messages.contract_created'));
    }

    public function show(EmployeeContract $employeeContract)
    {
        $this->authorize('view', $employeeContract);
        $employeeContract->load('teacher');
        return view('employee-contracts.show', compact('employeeContract'));
    }

    public function edit(EmployeeContract $employeeContract)
    {
        $this->authorize('update', $employeeContract);

        $teachers = Teacher::where('school_id', auth()->user()->school_id)
            ->orderBy('last_name')
            ->get();

        return view('employee-contracts.edit', compact('employeeContract', 'teachers'));
    }

    public function update(Request $request, EmployeeContract $employeeContract)
    {
        $this->authorize('update', $employeeContract);

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'contract_number' => 'nullable|string|max:100|unique:employee_contracts,contract_number,' . $employeeContract->id,
            'contract_type' => 'required|in:CDI,CDD,temporary,internship,part_time',
            'position' => 'required|string|max:255',
            'position_ar' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'salary' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'payment_frequency' => 'nullable|in:monthly,bi_weekly,weekly,hourly',
            'weekly_hours' => 'nullable|numeric|min:0|max:168',
            'responsibilities' => 'nullable|string',
            'responsibilities_ar' => 'nullable|string',
            'benefits' => 'nullable|string',
            'allowances' => 'nullable|array',
            'status' => 'nullable|in:draft,active,expired,terminated',
            'signed_date' => 'nullable|date',
            'termination_reason' => 'nullable|string',
            'termination_date' => 'nullable|date',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        if ($request->hasFile('file')) {
            if ($employeeContract->file_path) {
                Storage::disk('public')->delete($employeeContract->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('contracts', 'public');
        }

        $employeeContract->update($validated);

        return redirect()->route('employee-contracts.show', $employeeContract)
            ->with('success', __('messages.contract_updated'));
    }

    public function destroy(EmployeeContract $employeeContract)
    {
        $this->authorize('delete', $employeeContract);

        if ($employeeContract->file_path) {
            Storage::disk('public')->delete($employeeContract->file_path);
        }

        $employeeContract->delete();

        return redirect()->route('employee-contracts.index')
            ->with('success', __('messages.contract_deleted'));
    }

    public function activate(EmployeeContract $employeeContract)
    {
        $this->authorize('update', $employeeContract);

        $employeeContract->update([
            'status' => 'active',
            'signed_date' => $employeeContract->signed_date ?? now(),
        ]);

        return back()->with('success', __('messages.contract_activated'));
    }

    public function terminate(Request $request, EmployeeContract $employeeContract)
    {
        $this->authorize('update', $employeeContract);

        $validated = $request->validate([
            'termination_reason' => 'required|string',
            'termination_date' => 'required|date',
        ]);

        $employeeContract->update([
            'status' => 'terminated',
            'termination_reason' => $validated['termination_reason'],
            'termination_date' => $validated['termination_date'],
        ]);

        return back()->with('success', __('messages.contract_terminated'));
    }

    public function renew(EmployeeContract $employeeContract)
    {
        $this->authorize('create', EmployeeContract::class);

        // Create new contract based on current one
        $newContract = $employeeContract->replicate();
        $newContract->contract_number = 'CONT-' . date('Y') . '-' . str_pad(
            EmployeeContract::where('school_id', auth()->user()->school_id)->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
        $newContract->start_date = $employeeContract->end_date?->addDay() ?? now();
        $newContract->end_date = $newContract->start_date->copy()->addYear();
        $newContract->status = 'draft';
        $newContract->signed_date = null;
        $newContract->file_path = null;
        $newContract->save();

        // Mark old contract as expired
        $employeeContract->update(['status' => 'expired']);

        return redirect()->route('employee-contracts.edit', $newContract)
            ->with('success', __('messages.contract_renewed'));
    }

    public function expiringContracts()
    {
        $contracts = EmployeeContract::where('school_id', auth()->user()->school_id)
            ->where('status', 'active')
            ->whereDate('end_date', '<=', now()->addDays(60))
            ->whereDate('end_date', '>=', now())
            ->with('teacher')
            ->orderBy('end_date', 'asc')
            ->get();

        return view('employee-contracts.expiring', compact('contracts'));
    }

    public function downloadDocument(EmployeeContract $employeeContract)
    {
        $this->authorize('view', $employeeContract);

        if (!$employeeContract->file_path || !Storage::disk('public')->exists($employeeContract->file_path)) {
            return back()->with('error', __('messages.file_not_found'));
        }

        return Storage::disk('public')->download($employeeContract->file_path);
    }
}
