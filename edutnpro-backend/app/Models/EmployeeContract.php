<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeContract extends Model
{
    protected $fillable = [
        'teacher_id', 'school_id', 'contract_number', 'contract_type', 'position', 'position_ar',
        'start_date', 'end_date', 'salary', 'currency', 'payment_frequency', 'weekly_hours',
        'responsibilities', 'responsibilities_ar', 'benefits', 'allowances', 'status',
        'signed_date', 'file_path', 'termination_reason', 'termination_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'termination_date' => 'date',
        'salary' => 'decimal:2',
        'allowances' => 'array',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}