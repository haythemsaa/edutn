<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeave extends Model
{
    protected $fillable = [
        'teacher_id', 'school_id', 'leave_type', 'start_date', 'end_date', 'total_days',
        'reason', 'reason_ar', 'approved_by', 'status', 'approved_at', 'approval_notes',
        'rejection_reason', 'medical_certificate', 'supporting_documents', 'is_paid',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'supporting_documents' => 'array',
        'is_paid' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function calculateTotalDays(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        // Count only working days (exclude weekends)
        $totalDays = 0;
        $current = $this->start_date->copy();

        while ($current->lte($this->end_date)) {
            // Skip Fridays and Saturdays (Tunisia weekend)
            if (!in_array($current->dayOfWeek, [5, 6])) {
                $totalDays++;
            }
            $current->addDay();
        }

        return $totalDays;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isActive(): bool
    {
        $now = now();
        return $this->isApproved() &&
               $this->start_date->lte($now) &&
               $this->end_date->gte($now);
    }
}