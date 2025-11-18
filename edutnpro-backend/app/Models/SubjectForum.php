<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectForum extends Model
{
    protected $fillable = [
        'school_id',
        'subject_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'is_active',
        'moderation_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'moderation_enabled' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class, 'forum_id');
    }
}
