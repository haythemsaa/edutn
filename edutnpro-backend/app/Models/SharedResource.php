<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharedResource extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'subject_id',
        'study_group_id',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'type',
        'file_path',
        'file_url',
        'file_size',
        'mime_type',
        'tags',
        'visibility',
        'downloads_count',
        'views_count',
        'average_rating',
        'is_verified',
    ];

    protected $casts = [
        'tags' => 'array',
        'file_size' => 'integer',
        'downloads_count' => 'integer',
        'views_count' => 'integer',
        'average_rating' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function studyGroup(): BelongsTo
    {
        return $this->belongsTo(StudyGroup::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ResourceRating::class, 'resource_id');
    }

    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function updateAverageRating(): void
    {
        $average = $this->ratings()->avg('rating');
        $this->update(['average_rating' => $average]);
    }
}
