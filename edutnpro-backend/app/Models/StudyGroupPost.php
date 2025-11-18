<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyGroupPost extends Model
{
    protected $fillable = [
        'study_group_id', 'student_id', 'parent_id', 'content', 'attachments',
        'is_pinned', 'is_announcement', 'likes_count',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_pinned' => 'boolean',
        'is_announcement' => 'boolean',
        'likes_count' => 'integer',
    ];

    public function studyGroup(): BelongsTo { return $this->belongsTo(StudyGroup::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function parent(): BelongsTo { return $this->belongsTo(StudyGroupPost::class, 'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(StudyGroupPost::class, 'parent_id'); }
}
