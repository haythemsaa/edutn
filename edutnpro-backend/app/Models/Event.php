<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'school_id', 'title', 'title_ar', 'description', 'description_ar',
        'event_type', 'start_date', 'end_date', 'location', 'location_ar',
        'organizer_id', 'target_audience', 'max_participants', 'registered_count',
        'is_public', 'requires_registration', 'status', 'cover_image', 'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'target_audience' => 'array',
        'is_public' => 'boolean',
        'requires_registration' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}