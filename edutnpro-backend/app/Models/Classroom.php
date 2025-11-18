<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = [
        'school_id', 'name', 'name_ar', 'building', 'floor', 'capacity', 'type',
        'equipment', 'has_projector', 'has_computer', 'has_ac', 'is_accessible', 'is_available',
    ];

    protected $casts = [
        'equipment' => 'array',
        'has_projector' => 'boolean',
        'has_computer' => 'boolean',
        'has_ac' => 'boolean',
        'is_accessible' => 'boolean',
        'is_available' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }
}