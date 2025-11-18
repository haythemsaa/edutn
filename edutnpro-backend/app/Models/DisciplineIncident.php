<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplineIncident extends Model
{
    protected $fillable = [
        'student_id', 'school_id', 'reported_by', 'title', 'title_ar',
        'description', 'description_ar', 'incident_type', 'incident_date',
        'incident_time', 'location', 'severity', 'witnesses', 'action_taken',
        'action_taken_ar', 'status', 'parent_notified', 'parent_notified_at',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'parent_notified' => 'boolean',
        'parent_notified_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'reported_by');
    }

    public function sanctions(): HasMany
    {
        return $this->hasMany(Sanction::class);
    }
}