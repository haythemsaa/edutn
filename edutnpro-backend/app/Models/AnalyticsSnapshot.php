<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\*;

class AnalyticsSnapshot extends Model
{
    protected $fillable = [
    'school_id',
    'snapshot_date',
    'total_students',
    'present_students',
    'absent_students',
    'attendance_rate',
    'average_grade',
    'new_enrollments',
    'withdrawals',
    'revenue',
    'expenses',
    'additional_metrics'
];

    protected $casts = [
    'snapshot_date' => 'date',
    'total_students' => 'integer',
    'present_students' => 'integer',
    'absent_students' => 'integer',
    'attendance_rate' => 'decimal:2',
    'average_grade' => 'decimal:2',
    'new_enrollments' => 'integer',
    'withdrawals' => 'integer',
    'revenue' => 'decimal:2',
    'expenses' => 'decimal:2',
    'additional_metrics' => 'array'
];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
