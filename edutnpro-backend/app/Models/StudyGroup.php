<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudyGroup extends Model
{
    protected $fillable = [
        'school_id',
        'creator_id',
        'subject_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'image',
        'privacy',
        'max_members',
        'is_active',
        'meeting_schedule',
        'meeting_link',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meeting_schedule' => 'array',
        'max_members' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'creator_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'study_group_members')
            ->withPivot('role', 'status', 'joined_at')
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(StudyGroupPost::class);
    }

    public function sharedResources(): HasMany
    {
        return $this->hasMany(SharedResource::class);
    }

    public function isFull(): bool
    {
        return $this->members()->where('status', 'active')->count() >= $this->max_members;
    }

    public function isMember(Student $student): bool
    {
        return $this->members()
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->exists();
    }

    public function isAdmin(Student $student): bool
    {
        return $this->members()
            ->where('student_id', $student->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    public function canJoin(Student $student): bool
    {
        if ($this->isFull()) {
            return false;
        }

        if ($this->privacy === 'private' || $this->privacy === 'invite_only') {
            return false;
        }

        return !$this->isMember($student);
    }
}
