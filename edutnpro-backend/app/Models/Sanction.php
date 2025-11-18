<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanction extends Model
{
    protected $fillable = [
        'discipline_incident_id', 'student_id', 'school_id', 'assigned_by',
        'sanction_type', 'title', 'title_ar', 'description', 'description_ar',
        'start_date', 'end_date', 'duration_days', 'conditions', 'conditions_ar',
        'status', 'notes', 'parent_acknowledged', 'parent_acknowledged_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'parent_acknowledged' => 'boolean',
        'parent_acknowledged_at' => 'datetime',
    ];

    public function disciplineIncident(): BelongsTo
    {
        return $this->belongsTo(DisciplineIncident::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'assigned_by');
    }
}