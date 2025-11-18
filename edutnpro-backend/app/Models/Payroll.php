<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'teacher_id', 'school_id', 'payroll_number', 'month', 'year',
        'base_salary', 'allowances', 'bonuses', 'overtime', 'gross_salary',
        'tax', 'social_security', 'deductions', 'net_salary',
        'worked_days', 'absent_days', 'overtime_hours', 'notes',
        'status', 'payment_date', 'payment_method', 'file_path',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'overtime' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax' => 'decimal:2',
        'social_security' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
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